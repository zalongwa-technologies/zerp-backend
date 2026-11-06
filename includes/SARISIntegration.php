<?php

require_once(__DIR__ . '/ZERPSync.php');

// Progress logging for CLI runs only — no-op under the web SAPI so browser-
// triggered syncs (SARIS_Students.php, SARIS_Payments.php) are unaffected.
function saris_cli_log($message) {
	if (PHP_SAPI === 'cli') {
		echo '[' . date('H:i:s') . '] ' . $message . "\n";
		@flush();
	}
}

// Retrieve a value from the saris_api_settings table, with an optional default.
function getSarisSetting($key, $default = null) {
	try {
		$row = saris_fetch_one(
			'SELECT setting_value FROM saris_api_settings WHERE setting_key = ?',
			[$key]
		);
		if ($row !== null && $row['setting_value'] !== null) {
			return $row['setting_value'];
		}
	} catch (Exception $e) {
		// table does not exist yet (pre-migration); fall through to default
	}
	return $default;
}

class SARISAPIClient {
	private $timeout = 120;
	// Bounds only the connect/handshake phase. Without this, a connection that
	// never completes (server not responding, network black-holing packets)
	// burns the entire $timeout before curl gives up, instead of failing fast
	// so the retry loop can move on.
	private $connectTimeout = 15;
	private $settings = null; // lazy-loaded from saris_api_settings on first use

	// Token is cached in static properties (not $_SESSION) so it is:
	// 1. Safe under cron — no real HTTP session required
	// 2. Per-process only — no cross-user token leakage in concurrent web requests
	// $_SESSION is kept as a secondary warm-start cache for web page reloads.
	private static $cachedToken = null;
	private static $cachedTokenExpiresAt = 0;

	private function buildAuthAttempts() {
		$clientId = $this->cfg('client_id');
		$clientSecret = $this->cfg('client_secret');

		return [
			[
				'label' => 'oauth-form-with-scope',
				'body' => http_build_query([
					'client_id'     => $clientId,
					'client_secret' => $clientSecret,
					'grant_type'    => 'client_credentials',
					'scope'         => 'SARIS',
				]),
				'headers' => [
					'Content-Type: application/x-www-form-urlencoded',
					'Accept: application/json',
				],
			],
			[
				'label' => 'oauth-json-with-scope',
				'body' => json_encode([
					'client_id'     => $clientId,
					'client_secret' => $clientSecret,
					'grant_type'    => 'client_credentials',
					'scope'         => 'SARIS',
				]),
				'headers' => [
					'Content-Type: application/json',
					'Accept: application/json',
				],
			],
			[
				'label' => 'oauth-form-no-scope',
				'body' => http_build_query([
					'client_id'     => $clientId,
					'client_secret' => $clientSecret,
					'grant_type'    => 'client_credentials',
				]),
				'headers' => [
					'Content-Type: application/x-www-form-urlencoded',
					'Accept: application/json',
				],
			],
		];
	}

	private function responseSnippet($body) {
		$snippet = trim(preg_replace('/\s+/', ' ', substr((string)$body, 0, 240)));
		return $snippet;
	}

	private function isConnectPhaseTimeout($errno, $error, $httpCode, $connectTime, $appConnectTime) {
		if ($errno !== CURLE_OPERATION_TIMEDOUT || $httpCode > 0) {
			return false;
		}

		$message = strtolower(trim((string)$error));
		foreach ([
			'failed to connect',
			'could not connect',
			'couldn\'t connect',
			'connection timed out',
		] as $needle) {
			if ($message !== '' && strpos($message, $needle) !== false) {
				return true;
			}
		}

		// If TLS/app-connect never completed, treat the timeout as transport-level.
		if ((float)$appConnectTime <= 0) {
			return true;
		}

		// A timeout very close to the connect timeout is still in the connect phase.
		return (float)$connectTime > 0 && (float)$connectTime < $this->timeout;
	}

	private function shouldRetryTransportFailure($errno, $error, $httpCode, $connectTime, $appConnectTime) {
		if ($this->isConnectPhaseTimeout($errno, $error, $httpCode, $connectTime, $appConnectTime)) {
			return true;
		}

		return in_array($errno, [
			CURLE_COULDNT_CONNECT,
			CURLE_COULDNT_RESOLVE_HOST,
			CURLE_COULDNT_RESOLVE_PROXY,
			CURLE_SEND_ERROR,
			CURLE_RECV_ERROR,
			CURLE_GOT_NOTHING,
			CURLE_SSL_CONNECT_ERROR,
		], true);
	}

	private function extractTokenPayload(array $response) {
		$candidates = [
			$response['results'] ?? null,
			$response['data'] ?? null,
			$response,
		];

		foreach ($candidates as $candidate) {
			if (!is_array($candidate)) {
				continue;
			}

			$token = '';
			foreach (['access_token', 'token', 'bearer_token'] as $tokenKey) {
				if (!empty($candidate[$tokenKey]) && is_string($candidate[$tokenKey])) {
					$token = trim($candidate[$tokenKey]);
					break;
				}
			}

			if ($token === '') {
				continue;
			}

			$expiresIn = 3600;
			foreach (['expires_in', 'expires', 'expiresIn'] as $expiryKey) {
				if (isset($candidate[$expiryKey]) && is_numeric($candidate[$expiryKey])) {
					$expiresIn = (int)$candidate[$expiryKey];
					break;
				}
			}

			$tokenType = '';
			if (!empty($candidate['token_type']) && is_string($candidate['token_type'])) {
				$tokenType = strtolower(trim($candidate['token_type']));
			} elseif (!empty($candidate['type']) && is_string($candidate['type'])) {
				$tokenType = strtolower(trim($candidate['type']));
			}

			return [
				'token' => $token,
				'expires_in' => max(60, $expiresIn),
				'token_type' => $tokenType,
			];
		}

		return null;
	}

	private function isSuccessfulAuthResponse(array $response) {
		if (!empty($response['success'])) {
			return true;
		}
		if (isset($response['statusCode']) && (int)$response['statusCode'] === 200) {
			return true;
		}
		if (isset($response['code']) && (int)$response['code'] === 200) {
			return true;
		}
		if (isset($response['status']) && is_string($response['status'])) {
			$status = strtolower(trim($response['status']));
			if (in_array($status, ['success', 'ok'], true)) {
				return true;
			}
		}

		return $this->extractTokenPayload($response) !== null;
	}

	private function loadSettings() {
		if ($this->settings !== null) {
			return;
		}
		$this->settings = [
			'base_url'         => getSarisSetting('saris_base_url',         'https://saris.iae.ac.tz'),
			'client_id'        => getSarisSetting('saris_client_id',        ''),
			'client_secret'    => getSarisSetting('saris_client_secret',    ''),
			'token_endpoint'   => getSarisSetting('saris_token_endpoint',   '/api/v1/login'),
			'student_endpoint' => getSarisSetting('saris_student_endpoint', '/api/v1/students'),
			'invoice_endpoint' => getSarisSetting('saris_invoice_endpoint', '/api/v1/invoices'),
			'payment_endpoint' => getSarisSetting('saris_payment_endpoint', '/api/v1/payments'),
		];
	}

	private function cfg($key) {
		$this->loadSettings();
		return isset($this->settings[$key]) ? $this->settings[$key] : '';
	}

	private function baseUrl() {
		return rtrim($this->cfg('base_url'), '/');
	}

	private function ensureCredentials() {
		if ($this->cfg('client_id') === '' || $this->cfg('client_secret') === '') {
			throw new Exception('SARIS API credentials are not configured. Go to SARIS → API Configuration to set the Client ID and Client Secret.');
		}
	}

	// Fetch (or return cached) OAuth2 bearer token.
	public function getToken() {
		// 1. In-memory static cache (works for both CLI cron and web)
		if (self::$cachedToken !== null && time() < self::$cachedTokenExpiresAt) {
			return self::$cachedToken;
		}
		// 2. Session warm-start cache (web page reloads only)
		if (!empty($_SESSION['SARISAPI_TOKEN'])
			&& !empty($_SESSION['SARISAPI_TOKEN_EXPIRES_AT'])
			&& time() < (int)$_SESSION['SARISAPI_TOKEN_EXPIRES_AT']
		) {
			self::$cachedToken = $_SESSION['SARISAPI_TOKEN'];
			self::$cachedTokenExpiresAt = (int)$_SESSION['SARISAPI_TOKEN_EXPIRES_AT'];
			return self::$cachedToken;
		}
		return $this->authenticate();
	}

	private function authenticate() {
		$this->ensureCredentials();
		$url = $this->baseUrl() . $this->cfg('token_endpoint');
		$attempts = $this->buildAuthAttempts();
		// SARIS's endpoint has been observed as intermittently unreachable (connect
		// hangs) rather than consistently down — one variant in a cycle can hang
		// while another connects fine seconds later. Now that a hung connection
		// only costs $connectTimeout (not the old 120s), it's cheap to run the
		// whole variant cycle more than once before giving up.
		$maxPasses = 2;
		$totalAttempts = $maxPasses * count($attempts);
		$result = false;
		$error = '';
		$httpCode = 0;
		$lastVariant = '';
		$attemptNum = 0;
		for ($pass = 1; $pass <= $maxPasses; $pass++) {
			for ($i = 0; $i < count($attempts); $i++) {
				$attemptNum++;
				$request = $attempts[$i];
				$lastVariant = $request['label'];
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request['body']);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $request['headers']);
				curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
				saris_cli_log("Authenticating with SARIS (attempt {$attemptNum}/{$totalAttempts}, {$request['label']})...");
				$requestStarted = microtime(true);
				$result = curl_exec($ch);
				$error = curl_error($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				$elapsed = round(microtime(true) - $requestStarted, 1);
				if ($result !== false) {
					if ($httpCode >= 200 && $httpCode < 300) {
						saris_cli_log("Authentication response received (HTTP {$httpCode}, {$elapsed}s, {$request['label']})");
						break 2;
					}
					$snippet = $this->responseSnippet($result);
					$detail = $snippet !== '' ? " Body: {$snippet}" : '';
					saris_cli_log("Auth attempt {$attemptNum} returned HTTP {$httpCode} in {$elapsed}s ({$request['label']}).{$detail}");
					if ($attemptNum < $totalAttempts) sleep(2 * $pass);
					continue;
				}
				saris_cli_log("Auth attempt {$attemptNum} failed after {$elapsed}s ({$request['label']}): {$error}");
				if ($attemptNum < $totalAttempts) sleep(2 * $pass);
			}
		}

		if ($result === false) {
			throw new Exception('Could not connect to SARIS token endpoint: ' . $error);
		}
		$response = json_decode($result, true);
		$tokenPayload = is_array($response) ? $this->extractTokenPayload($response) : null;
		// Primary success shape: {success:true, results:{access_token, expires_in, ...}}
		// Also accept alternate envelopes used by related integrations.
		if (!is_array($response) || !$this->isSuccessfulAuthResponse($response) || $tokenPayload === null) {
			$msg = isset($response['message']) ? $response['message'] : 'Unexpected response (HTTP ' . $httpCode . ')';
			$bodySnippet = $this->responseSnippet($result);
			if ($bodySnippet !== '') {
				$msg .= ' Body: ' . $bodySnippet;
			}
			throw new Exception('SARIS authentication failed (' . $lastVariant . '): ' . $msg);
		}
		$expiresIn = $tokenPayload['expires_in'];
		$expiresAt = time() + $expiresIn - 30;
		// Store in both static cache and session
		self::$cachedToken = $tokenPayload['token'];
		self::$cachedTokenExpiresAt = $expiresAt;
		$_SESSION['SARISAPI_TOKEN'] = $tokenPayload['token'];
		$_SESSION['SARISAPI_TOKEN_EXPIRES_AT'] = $expiresAt;
		saris_cli_log('Authenticated with SARIS token; expires in ' . $expiresIn . 's');
		return self::$cachedToken;
	}

	// Authenticated GET. On INVALID_TOKEN, refreshes once and retries.
	public function get($endpointUrl, $params = [], $isRetry = false) {
		$url = $endpointUrl;
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		$token = $this->getToken();
		$result = false;
		$error = '';
		$errno = 0;
		$httpCode = 0;
		for ($attempt = 1; $attempt <= 3; $attempt++) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $token,
				'Accept: application/json',
			]);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
			saris_cli_log("GET {$url} (attempt {$attempt}/3)...");
			$requestStarted = microtime(true);
			$result = curl_exec($ch);
			$error = curl_error($ch);
			$errno = curl_errno($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
			$appConnectTime = curl_getinfo($ch, CURLINFO_APPCONNECT_TIME);
			curl_close($ch);
			$elapsed = round(microtime(true) - $requestStarted, 1);
			if ($result !== false) {
				saris_cli_log("  -> HTTP {$httpCode} in {$elapsed}s");
				break;
			}
			saris_cli_log("  -> failed after {$elapsed}s: {$error}");
			// Retry only transient transport failures. Once the request fully
			// connected and timed out waiting on the server response, retries
			// just multiply the wait without changing the outcome.
			if (!$this->shouldRetryTransportFailure($errno, $error, $httpCode, $connectTime, $appConnectTime)) break;
			if ($attempt < 3) sleep(5 * $attempt);
		}

		if ($result === false) {
			throw new Exception('SARIS API request failed: ' . $error);
		}
		$decoded = json_decode($result, true);
		if (!is_array($decoded)) {
			throw new Exception('SARIS API returned invalid JSON (HTTP ' . $httpCode . ')');
		}

		if (empty($decoded['success'])) {
			$errorCode = isset($decoded['results']['error_code']) ? $decoded['results']['error_code'] : '';
			$message   = isset($decoded['message'])              ? $decoded['message']              : 'Unknown error';

			if ($errorCode === 'INVALID_TOKEN' && !$isRetry) {
				// Token expired mid-request — clear both caches, re-authenticate, retry once
				self::$cachedToken = null;
				self::$cachedTokenExpiresAt = 0;
				unset($_SESSION['SARISAPI_TOKEN'], $_SESSION['SARISAPI_TOKEN_EXPIRES_AT']);
				$this->authenticate();
				return $this->get($endpointUrl, $params, true);
			}
			if ($errorCode === 'NO_DATA_FOUND') {
				return ['success' => true, 'message' => 'No data found', 'results' => ['count' => 0, 'data' => []]];
			}
			error_log('SARIS API [' . $errorCode . '] ' . $endpointUrl . ': ' . $message);
			throw new Exception('SARIS API error [' . $errorCode . ']: ' . $message);
		}
		return $decoded;
	}

	public function getStudents(array $filters = []) {
		return $this->get($this->baseUrl() . $this->cfg('student_endpoint'), $filters);
	}

	public function getInvoices(array $filters = []) {
		return $this->get($this->baseUrl() . $this->cfg('invoice_endpoint'), $filters);
	}

	public function getPayments(array $filters = []) {
		return $this->get($this->baseUrl() . $this->cfg('payment_endpoint'), $filters);
	}

	// Backward-compatible wrappers for any code that still calls the old method names
	public function getAllInvoices($dateFrom, $dateTo) {
		return $this->getInvoices(['date_from' => $dateFrom, 'date_to' => $dateTo, 'limit' => 50]);
	}

	public function getStudentInfo($regNumber) {
		$response = $this->getStudents(['regno' => $regNumber, 'limit' => 1]);
		$data = saris_iae_extract($response);
		if (empty($data)) {
			return ['statusCode' => 404, 'data' => null];
		}
		return ['success' => true, 'data' => saris_map_student($data[0])];
	}

	public function getAllPayments($dateFrom, $dateTo) {
		return $this->getPayments(['date_from' => $dateFrom, 'date_to' => $dateTo, 'limit' => 50]);
	}
}

function saris_extract_records($response) {
	if (isset($response['data']) && is_array($response['data'])) {
		return $response['data'];
	}
	if (is_array($response)) {
		return $response;
	}
	return [];
}

// Extract data array from IAE v1 response: {success, results:{count, data:[...]}}
function saris_iae_extract(array $response) {
	if (!empty($response['success'])
		&& isset($response['results']['data'])
		&& is_array($response['results']['data'])
	) {
		return $response['results']['data'];
	}
	return [];
}

// Map IAE v1 /students record fields to internal column names.
function saris_map_student(array $raw) {
	$classes = (!empty($raw['classes']) && is_array($raw['classes'])) ? $raw['classes'][0] : [];
	return [
		'student_regnumber' => isset($raw['RegNo'])            ? $raw['RegNo']            : null,
		'student_fullname'  => isset($raw['Name'])             ? $raw['Name']             : null,
		'student_email'     => isset($raw['Email'])            ? $raw['Email']            : null,
		'student_phone'     => isset($raw['Phone'])            ? $raw['Phone']            : null,
		'student_programme' => isset($raw['ProgrammeofStudy']) ? $raw['ProgrammeofStudy'] : null,
		'student_entryyear' => isset($raw['EntryYear'])        ? $raw['EntryYear']        : null,
		'student_studyyear' => isset($classes['YearOfStudy'])  ? $classes['YearOfStudy']  : null,
		'student_intake'    => isset($classes['AYear'])        ? $classes['AYear']        : null,
	];
}

// Map IAE v1 /invoices record fields to internal column names.
// Key names shown here are best-guess from the API spec; update if the live
// response uses different casing or snake_case variants.
function saris_map_invoice(array $raw) {
	return [
		'student_name'             => isset($raw['StudentName'])  ? $raw['StudentName']  : (isset($raw['student_name'])  ? $raw['student_name']  : null),
		'invoice_reference_number' => isset($raw['InvoiceId'])    ? $raw['InvoiceId']    : (isset($raw['invoice_id'])    ? $raw['invoice_id']    : (isset($raw['control_number']) ? $raw['control_number'] : null)),
		'student_regnumber'        => isset($raw['StudentId'])     ? $raw['StudentId']    : (isset($raw['student_id'])    ? $raw['student_id']    : null),
		'invoice_amount'           => isset($raw['Amount'])        ? $raw['Amount']       : (isset($raw['amount'])        ? $raw['amount']        : 0),
		'invoice_amount_type'      => isset($raw['AmountType'])   ? $raw['AmountType']   : (isset($raw['amount_type'])   ? $raw['amount_type']   : null),
		'invoice_desciption'       => isset($raw['FeeName'])      ? $raw['FeeName']      : (isset($raw['fee_name'])      ? $raw['fee_name']      : null),
		'invoice_date'             => isset($raw['InvoiceDate'])  ? $raw['InvoiceDate']  : (isset($raw['invoice_date'])  ? $raw['invoice_date']  : (isset($raw['date']) ? $raw['date'] : null)),
	];
}

// Map IAE v1 /payments record fields to internal column names.
function saris_map_payment(array $raw) {
    return [
            'student_name'             => isset($raw['StudentName'])   ? $raw['StudentName']   : (isset($raw['student_name'])   ? $raw['student_name']   : null),
            'student_regnumber'        => isset($raw['StudentId'])      ? $raw['StudentId']      : (isset($raw['student_id'])      ? $raw['student_id']      : null),
            'payment_desciption'       => isset($raw['Description'])   ? $raw['Description']   : (isset($raw['description'])    ? $raw['description']    : null),
            'payment_amount'           => isset($raw['Amount'])        ? $raw['Amount']        : (isset($raw['amount'])        ? $raw['amount']        : 0),
            'payment_amount_type'      => isset($raw['AmountType'])    ? $raw['AmountType']    : (isset($raw['amount_type'])    ? $raw['amount_type']    : (isset($raw['fee_category']) ? $raw['fee_category'] : null)),
            'payment_currency'         => isset($raw['Currency'])      ? $raw['Currency']      : (isset($raw['currency'])      ? $raw['currency']      : null),
            'payment_receipt_number'   => isset($raw['ReceiptNumber']) ? $raw['ReceiptNumber'] : (isset($raw['receipt_number']) ? $raw['receipt_number'] : null),
            'payment_transaction_ref'  => isset($raw['ControlNumber']) ? $raw['ControlNumber'] : (isset($raw['control_number']) ? $raw['control_number'] : (isset($raw['transaction_ref']) ? $raw['transaction_ref'] : null)),
            'payment_date'             => isset($raw['PaymentDate'])   ? $raw['PaymentDate']   : (isset($raw['payment_date'])   ? $raw['payment_date']   : (isset($raw['date']) ? $raw['date'] : null)),
		'payment_reference_number' => isset($raw['InvoiceNumber']) ? $raw['InvoiceNumber'] : (isset($raw['invoice_number']) ? $raw['invoice_number'] : null),
		'payment_source'           => isset($raw['Source'])        ? $raw['Source']        : (isset($raw['source'])        ? $raw['source']        : null),
	];
}

function saris_sql_decimal($value) {
	return is_numeric($value) ? (float)$value : 0;
}

function saris_sql_int($value) {
	return is_numeric($value) ? (int)$value : 'NULL';
}

function saris_sql_datetime($value) {
	if ($value === null || $value === '') {
		return 'NULL';
	}
	$timestamp = strtotime($value);
	if ($timestamp === false) {
		return 'NULL';
	}
	return "'" . date('Y-m-d H:i:s', $timestamp) . "'";
}

function saris_validate_date_range($startDate, $endDate) {
	$start = DateTime::createFromFormat('Y-m-d', $startDate);
	$end = DateTime::createFromFormat('Y-m-d', $endDate);
	return $start && $end && $start->format('Y-m-d') === $startDate && $end->format('Y-m-d') === $endDate && $end >= $start;
}

function saris_pdo() {
	static $pdo = null;
	static $lastDb = null;
	global $Host, $DBUser, $DBPassword, $DBPort;
	$databaseName = isset($_SESSION['DatabaseName']) ? $_SESSION['DatabaseName'] : '';

	if ($pdo instanceof PDO && $lastDb === $databaseName) {
		return $pdo;
	}
	$lastDb = $databaseName;

	if ($databaseName === '') {
		throw new Exception('No ZERP database is selected.');
	}
	$dsn = 'mysql:host=' . $Host . ';dbname=' . $databaseName . ';charset=utf8mb4';
	if (!empty($DBPort)) {
		$dsn = 'mysql:host=' . $Host . ';port=' . $DBPort . ';dbname=' . $databaseName . ';charset=utf8mb4';
	}
	$pdo = new PDO($dsn, $DBUser, $DBPassword, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false
	]);
	return $pdo;
}

function saris_fetch_one($sql, $params = []) {
	$stmt = saris_pdo()->prepare($sql);
	$stmt->execute($params);
	$row = $stmt->fetch();
	return $row ? $row : null;
}

function saris_fetch_all($sql, $params = []) {
	$stmt = saris_pdo()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

function saris_execute($sql, $params = []) {
	$stmt = saris_pdo()->prepare($sql);
	$stmt->execute($params);
	return $stmt;
}

function saris_get_settings() {
	$row = saris_fetch_one("SELECT sync_mode, sync_interval, updated_at FROM saris_settings WHERE id = ?", [1]);
	if ($row === null) {
		return ['sync_mode' => 'manual', 'sync_interval' => null, 'updated_at' => null];
	}
	return $row;
}

function saris_save_settings($syncMode, $syncInterval) {
	$syncMode = $syncMode === 'automatic' ? 'automatic' : 'manual';
	if ($syncMode === 'manual') {
		$syncInterval = null;
	} elseif (!in_array($syncInterval, ['10min', '30min', '1hr', '1day'], true)) {
		$syncInterval = '1hr';
	}
	saris_execute(
		"INSERT INTO saris_settings (id, sync_mode, sync_interval)
		VALUES (1, ?, ?)
		ON DUPLICATE KEY UPDATE sync_mode = VALUES(sync_mode), sync_interval = VALUES(sync_interval)",
		[$syncMode, $syncInterval]
	);
}

function saris_default_invoice_start_date() {
	$row = saris_fetch_one("SELECT DATE(MAX(invoice_date)) AS max_date FROM invoices");
	return !empty($row['max_date']) ? $row['max_date'] : date('Y-m-d');
}

function saris_default_payment_start_date() {
	$row = saris_fetch_one("SELECT DATE(MAX(payment_date)) AS max_date FROM payments");
	return !empty($row['max_date']) ? $row['max_date'] : date('Y-m-d');
}

function saris_upsert_invoice($invoice) {
	// Fix #10: Reject zero/null amounts at staging time so they never become
	// permanently-failed ZERP records that pollute the error log.
	$amount = saris_sql_decimal($invoice['invoice_amount'] ?? 0);
	if ($amount <= 0) {
		error_log('SARIS: Skipping invoice ' . ($invoice['invoice_reference_number'] ?? '?') . ' — amount is zero or missing.');
		return;
	}
	saris_execute(
		"INSERT INTO invoices (
			student_name, invoice_reference_number, student_regnumber, invoice_amount,
			invoice_amount_type, invoice_desciption, invoice_date
		) VALUES (?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			student_name = VALUES(student_name),
			student_regnumber = VALUES(student_regnumber),
			invoice_amount_type = VALUES(invoice_amount_type),
			invoice_desciption = VALUES(invoice_desciption),
			invoice_date = VALUES(invoice_date),
			-- Fix #8: If the amount changed on a record already in ZERP, reset it
			-- to pending so the corrected value gets re-posted. Safe because the
			-- idempotency guard in ZERPSync checks ZERP directly before re-posting.
			invoice_amount = VALUES(invoice_amount),
			sync_status = IF(
				sync_status = 'synced' AND invoice_amount != VALUES(invoice_amount),
				'pending',
				sync_status
			),
			-- Fix #9: Reset attempt counter alongside status so re-queued records
			-- are never blocked by the old attempt ceiling.
			sync_attempts = IF(
				sync_status = 'synced' AND invoice_amount != VALUES(invoice_amount),
				0,
				sync_attempts
			)",
		[
			$invoice['student_name'] ?? null,
			$invoice['invoice_reference_number'] ?? null,
			$invoice['student_regnumber'] ?? null,
			$amount,
			$invoice['invoice_amount_type'] ?? null,
			$invoice['invoice_desciption'] ?? null,
			saris_normalize_datetime($invoice['invoice_date'] ?? null)
		]
	);
}

function saris_upsert_student($student) {
	saris_execute(
		"INSERT INTO students (
			student_regnumber, student_fullname, student_email, student_phone,
			student_programme, student_entryyear, student_studyyear, student_intake
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			student_fullname = VALUES(student_fullname),
			student_email = VALUES(student_email),
			student_phone = VALUES(student_phone),
			student_programme = VALUES(student_programme),
			student_entryyear = VALUES(student_entryyear),
			student_studyyear = VALUES(student_studyyear),
			student_intake = VALUES(student_intake)",
		[
			$student['student_regnumber'] ?? null,
			$student['student_fullname'] ?? null,
			$student['student_email'] ?? null,
			$student['student_phone'] ?? null,
			$student['student_programme'] ?? null,
			$student['student_entryyear'] ?? null,
			is_numeric($student['student_studyyear'] ?? null) ? (int)$student['student_studyyear'] : null,
			$student['student_intake'] ?? null
		]
	);
}

function saris_upsert_payment($payment) {
	// Fix #10: Reject zero/null amounts before staging.
	$amount = saris_sql_decimal($payment['payment_amount'] ?? 0);
	if ($amount <= 0) {
		error_log('SARIS: Skipping payment ' . ($payment['payment_receipt_number'] ?? '?') . ' — amount is zero or missing.');
		return;
	}
	// Fix #3: Ensure receipt_number is never NULL — fall back to transaction_ref
	// or a deterministic hash so the unique key always fires and prevents duplicates.
	$receiptNumber = !empty($payment['payment_receipt_number'])
		? $payment['payment_receipt_number']
		: (!empty($payment['payment_transaction_ref'])
			? 'TXN-' . $payment['payment_transaction_ref']
			: 'SARIS-' . substr(hash('sha256',
				($payment['student_regnumber'] ?? '') . '|' .
				($payment['payment_date'] ?? '') . '|' .
				$amount
			), 0, 16));
	saris_execute(
		"INSERT INTO payments (
			student_name, student_regnumber, payment_desciption, payment_amount,
			payment_amount_type, payment_currency, payment_receipt_number,
			payment_transaction_ref, payment_date, payment_reference_number, payment_source
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			student_name = VALUES(student_name),
			student_regnumber = VALUES(student_regnumber),
			payment_desciption = VALUES(payment_desciption),
			payment_amount_type = VALUES(payment_amount_type),
			payment_currency = VALUES(payment_currency),
			payment_transaction_ref = VALUES(payment_transaction_ref),
			payment_date = VALUES(payment_date),
			payment_reference_number = VALUES(payment_reference_number),
			payment_source = VALUES(payment_source),
			-- Fix #8: Re-queue if the amount was corrected in SARIS
			payment_amount = VALUES(payment_amount),
			sync_status = IF(
				sync_status = 'synced' AND payment_amount != VALUES(payment_amount),
				'pending',
				sync_status
			),
			-- Fix #9: Reset attempt counter when re-queuing
			sync_attempts = IF(
				sync_status = 'synced' AND payment_amount != VALUES(payment_amount),
				0,
				sync_attempts
			)",
		[
			$payment['student_name'] ?? null,
			$payment['student_regnumber'] ?? null,
			$payment['payment_desciption'] ?? null,
			$amount,
			$payment['payment_amount_type'] ?? null,
			$payment['payment_currency'] ?? null,
			$receiptNumber,
			$payment['payment_transaction_ref'] ?? null,
			saris_normalize_datetime($payment['payment_date'] ?? null),
			$payment['payment_reference_number'] ?? null,
			$payment['payment_source'] ?? null
		]
	);
}

// Split a date range into <= 30-day windows so a wide manual sync doesn't
// force the SARIS server to compute one huge query that exceeds our timeout.
function saris_date_chunks($startDate, $endDate, $maxDays = 30) {
	$chunks = [];
	$start = new DateTime($startDate);
	$end = new DateTime($endDate);
	while ($start <= $end) {
		$chunkEnd = (clone $start)->modify('+' . ($maxDays - 1) . ' days');
		if ($chunkEnd > $end) {
			$chunkEnd = clone $end;
		}
		$chunks[] = [$start->format('Y-m-d'), $chunkEnd->format('Y-m-d')];
		$start = (clone $chunkEnd)->modify('+1 day');
	}
	return $chunks;
}

function saris_sync_payments($client, $startDate, $endDate) {
	$count    = 0;
	$pageSize = 50;
	$chunks = saris_date_chunks($startDate, $endDate);
	$chunkIndex = 0;
	foreach ($chunks as [$chunkFrom, $chunkTo]) {
		$chunkIndex++;
		saris_cli_log("Payments chunk {$chunkIndex}/" . count($chunks) . ": {$chunkFrom} -> {$chunkTo}");
		$offset = 0;
		do {
			$response = $client->getPayments([
				'date_from'               => $chunkFrom,
				'date_to'                 => $chunkTo,
				'include_invoice_details' => 'false', // reduce payload when detail is not needed
				'limit'                   => $pageSize,
				'offset'                  => $offset,
			]);
			$batch = saris_iae_extract($response);
			foreach ($batch as $raw) {
				saris_upsert_payment(saris_map_payment($raw));
				$count++;
			}
			saris_cli_log("  payments page offset={$offset}: " . count($batch) . ' record(s), ' . $count . ' total so far');
			$offset += $pageSize;
		} while (count($batch) === $pageSize);
	}
	return $count;
}

function saris_full_sync($client, $startDate, $endDate) {
	$stats = [
		'invoices'         => 0,
		'students'         => 0,
		'students_skipped' => 0,
		'payments'         => 0,
	];
	$regNumbers = [];
	$offset     = 0;
	$pageSize   = 50;

	saris_cli_log("== Invoices: {$startDate} -> {$endDate} ==");
	// Paginated invoice fetch — replaced direct DB queries with IAE v1 API calls
	do {
		$response = $client->getInvoices([
			'date_from' => $startDate,
			'date_to'   => $endDate,
			'limit'     => $pageSize,
			'offset'    => $offset,
		]);
		$batch = saris_iae_extract($response);
		foreach ($batch as $raw) {
			$invoice = saris_map_invoice($raw);
			saris_upsert_invoice($invoice);
			$stats['invoices']++;
			if (!empty($invoice['student_regnumber'])) {
				$regNumbers[$invoice['student_regnumber']] = true;
			}
		}
		saris_cli_log("  invoices page offset={$offset}: " . count($batch) . ' record(s), ' . $stats['invoices'] . ' total so far');
		$offset += $pageSize;
	} while (count($batch) === $pageSize);

	saris_cli_log('== Students: ' . count($regNumbers) . ' unique registration number(s) ==');
	// Per-student lookup using the new /students endpoint
	$studentIndex = 0;
	foreach (array_keys($regNumbers) as $regNo) {
		$studentIndex++;
		saris_cli_log("  student {$studentIndex}/" . count($regNumbers) . ": {$regNo}");
		$response = $client->getStudents(['regno' => $regNo, 'limit' => 1]);
		$batch = saris_iae_extract($response);
		if (empty($batch)) {
			$stats['students_skipped']++;
			saris_cli_log("    not found, skipped");
			continue;
		}
		$student = saris_map_student($batch[0]);
		if (!empty($student['student_regnumber'])) {
			saris_upsert_student($student);
			$stats['students']++;
		} else {
			$stats['students_skipped']++;
			saris_cli_log("    no regnumber in response, skipped");
		}
	}

	saris_cli_log("== Payments: {$startDate} -> {$endDate} ==");
	$stats['payments'] = saris_sync_payments($client, $startDate, $endDate);
	return $stats;
}

function saris_zerp_config() {
	global $ZERP_EndPoint, $ZERP_Username, $ZERP_Password, $ZERP_BankAccount;
	global $ZERP_SalesArea, $ZERP_SalesPerson, $ZERP_SalesType, $ZERP_ShipVia;
	global $ZERP_SyncEnabled, $ZERP_DefaultBranch, $ZERP_Currency, $ZERP_PaymentMethod;
	global $ZERP_DefaultLocation, $ZERP_BranchArea, $ZERP_TaxGroup, $ZERP_XMLRPC_Timeout;
	global $ZERP_PaymentTerms, $ZERP_HoldReason, $ZERP_CustomerType, $ZERP_InvoicePartCode;
	global $ZERP_InvoiceLocation, $ZERP_InvoiceRate, $ZERP_SyncMaxAttempts, $ZERP_SyncBatchSize;
	global $DEFAULT_PRODUCT_ID;
	$legacyLocation = isset($ZERP_SalesArea) ? $ZERP_SalesArea : '';

	return [
		'enabled' => isset($ZERP_SyncEnabled) ? (bool)$ZERP_SyncEnabled : false,
		'url' => isset($ZERP_EndPoint) ? $ZERP_EndPoint : '',
		'username' => isset($ZERP_Username) ? $ZERP_Username : '',
		'password' => isset($ZERP_Password) ? $ZERP_Password : '',
		'timeout' => isset($ZERP_XMLRPC_Timeout) ? (int)$ZERP_XMLRPC_Timeout : 60,
		'branch_code' => isset($ZERP_DefaultBranch) ? $ZERP_DefaultBranch : 'MAIN',
		'currency' => isset($ZERP_Currency) ? $ZERP_Currency : 'TZS',
		'sales_type' => isset($ZERP_SalesType) ? $ZERP_SalesType : '1',
		'payment_terms' => isset($ZERP_PaymentTerms) ? $ZERP_PaymentTerms : '1',
		'hold_reason' => isset($ZERP_HoldReason) ? (int)$ZERP_HoldReason : 1,
		'customer_type' => isset($ZERP_CustomerType) ? (int)$ZERP_CustomerType : 1,
		'branch_area' => isset($ZERP_BranchArea) ? $ZERP_BranchArea : '1',
		'salesperson' => isset($ZERP_SalesPerson) ? $ZERP_SalesPerson : '1',
		'default_location' => isset($ZERP_DefaultLocation) ? $ZERP_DefaultLocation : $legacyLocation,
		'tax_group' => isset($ZERP_TaxGroup) ? (int)$ZERP_TaxGroup : 1,
		'ship_via' => isset($ZERP_ShipVia) ? (int)$ZERP_ShipVia : 1,
		'invoice_part_code' => isset($ZERP_InvoicePartCode)
			? $ZERP_InvoicePartCode
			: (isset($DEFAULT_PRODUCT_ID) ? $DEFAULT_PRODUCT_ID : ''),
		'invoice_location' => isset($ZERP_InvoiceLocation) ? $ZERP_InvoiceLocation : $legacyLocation,
		'invoice_rate' => isset($ZERP_InvoiceRate) ? (float)$ZERP_InvoiceRate : 1,
		'bank_account' => isset($ZERP_BankAccount) ? $ZERP_BankAccount : '',
		'payment_method' => isset($ZERP_PaymentMethod) ? $ZERP_PaymentMethod : '1',
		'max_attempts' => isset($ZERP_SyncMaxAttempts) ? (int)$ZERP_SyncMaxAttempts : 5,
		'batch_size' => isset($ZERP_SyncBatchSize) ? (int)$ZERP_SyncBatchSize : 100,
	];
}

function saris_run_zerp_sync() {
	$config = saris_zerp_config();
	if (!$config['enabled']) {
		return [
			'enabled' => false,
			'students_synced' => 0,
			'invoices_synced' => 0,
			'payments_synced' => 0,
			'partial' => 0,
			'failed' => 0,
			'skipped' => 0,
		];
	}

	foreach ([
		'url', 'username', 'password', 'branch_code', 'currency', 'sales_type', 'payment_terms',
		'branch_area', 'salesperson', 'default_location', 'invoice_part_code', 'invoice_location',
		'bank_account', 'payment_method'
	] as $required) {
		if (trim((string)$config[$required]) === '') {
			throw new RuntimeException('ZERP synchronization is enabled, but configuration "' . $required . '" is empty.');
		}
	}

	$client = new ZERPXMLRPCClient($config);
	$sync = new ZERPSync(saris_pdo(), $client, $config);
	$result = $sync->run();
	$result['enabled'] = true;
	return $result;
}

function saris_full_sync_with_zerp($client, $startDate, $endDate) {
	$sarisStats = saris_full_sync($client, $startDate, $endDate);
	$zerpStats = saris_run_zerp_sync();
	return [
		'saris' => $sarisStats,
		'zerp' => $zerpStats,
	];
}

function saris_sync_run_id() {
	$bytes = random_bytes(16);
	$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
	$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
	$hex = bin2hex($bytes);
	return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
		. '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

function saris_sync_history_status($zerpStats) {
	if (!is_array($zerpStats) || empty($zerpStats['enabled'])) {
		return 'success';
	}
	if ((int)($zerpStats['failed'] ?? 0) > 0) {
		return 'failed';
	}
	if ((int)($zerpStats['partial'] ?? 0) > 0) {
		return 'partial';
	}
	return 'success';
}

function saris_record_sync_history($history) {
	$saris = isset($history['saris']) && is_array($history['saris']) ? $history['saris'] : [];
	$zerp = isset($history['zerp']) && is_array($history['zerp']) ? $history['zerp'] : [];
	$errorSummary = $history['error_summary'] ?? ($zerp['error_summary'] ?? []);
	if (is_array($errorSummary)) {
		$errorSummary = json_encode($errorSummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	saris_execute(
		"INSERT INTO saris_sync_log (
			run_id, trigger_type, sync_status, date_from, date_to, iterations,
			saris_invoices, saris_students, saris_payments, zerp_enabled,
			zerp_students, zerp_invoices, zerp_payments, zerp_partial,
			zerp_failed, zerp_skipped, error_summary, message, started_at,
			completed_at, duration_seconds, created_at
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
		[
			$history['run_id'] ?? saris_sync_run_id(),
			$history['trigger_type'] ?? 'automatic',
			$history['sync_status'] ?? 'success',
			$history['date_from'] ?? null,
			$history['date_to'] ?? null,
			(int)($history['iterations'] ?? 0),
			(int)($saris['invoices'] ?? 0),
			(int)($saris['students'] ?? 0),
			(int)($saris['payments'] ?? 0),
			!empty($zerp['enabled']) ? 1 : 0,
			(int)($zerp['students_synced'] ?? 0),
			(int)($zerp['invoices_synced'] ?? 0),
			(int)($zerp['payments_synced'] ?? 0),
			(int)($zerp['partial'] ?? 0),
			(int)($zerp['failed'] ?? 0),
			(int)($zerp['skipped'] ?? 0),
			$errorSummary ?: null,
			$history['message'] ?? null,
			$history['started_at'] ?? null,
			$history['completed_at'] ?? date('Y-m-d H:i:s'),
			isset($history['duration_seconds']) ? round((float)$history['duration_seconds'], 3) : null,
		]
	);
}

function saris_log_sync($message, $status = 'success') {
	saris_record_sync_history([
		'sync_status' => $status,
		'message' => $message,
	]);
}

function saris_normalize_datetime($value) {
	if ($value === null || $value === '') {
		return null;
	}
	$timestamp = strtotime($value);
	return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
}

function saris_count_rows($table, $searchTerm = '') {
	$allowedTables = ['students', 'invoices', 'payments'];
	if (!in_array($table, $allowedTables, true)) {
		throw new Exception('Invalid SARIS table requested.');
	}
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		if ($table === 'students') {
			$where = " WHERE student_fullname LIKE ? OR student_regnumber LIKE ?";
			$params = ["%$searchTerm%", "%$searchTerm%"];
		} elseif ($table === 'invoices') {
			$where = " WHERE student_name LIKE ? OR invoice_reference_number LIKE ? OR student_regnumber LIKE ?";
			$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
		} elseif ($table === 'payments') {
			$where = " WHERE student_name LIKE ? OR student_regnumber LIKE ? OR payment_receipt_number LIKE ? OR payment_transaction_ref LIKE ?";
			$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
		}
	}
	$row = saris_fetch_one("SELECT COUNT(*) AS total FROM `" . $table . "`" . $where, $params);
	return (int)$row['total'];
}

function saris_list_students($limit, $offset, $searchTerm = '', $sort = 'student_regnumber', $dir = 'ASC') {
	$allowedSorts = ['id', 'student_regnumber', 'student_fullname', 'student_email', 'student_phone', 'student_programme', 'student_entryyear', 'student_studyyear', 'student_intake', 'sync_status', 'zerp_customer_code', 'created_at'];
	if (!in_array($sort, $allowedSorts, true)) {
		$sort = 'student_regnumber';
	}
	$dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		$where = "WHERE student_fullname LIKE ? OR student_regnumber LIKE ? ";
		$params = ["%$searchTerm%", "%$searchTerm%"];
	}
	$params[] = (int)$limit;
	$params[] = (int)$offset;
	return saris_fetch_all(
		"SELECT id, student_regnumber, student_fullname, student_email, student_phone,
			student_programme, student_entryyear, student_studyyear, student_intake,
			sync_status, zerp_customer_code, sync_error, created_at
		FROM students
		$where
		ORDER BY `$sort` $dir
		LIMIT ? OFFSET ?",
		$params
	);
}

function saris_list_invoices($limit, $offset, $searchTerm = '', $sort = 'invoice_date', $dir = 'DESC') {
	$allowedSorts = ['id', 'student_name', 'invoice_reference_number', 'student_regnumber', 'invoice_amount', 'invoice_amount_type', 'invoice_desciption', 'invoice_date', 'sync_status', 'zerp_invoice_no', 'created_at'];
	if (!in_array($sort, $allowedSorts, true)) {
		$sort = 'invoice_date';
	}
	$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		$where = "WHERE student_name LIKE ? OR invoice_reference_number LIKE ? OR student_regnumber LIKE ? ";
		$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
	}
	$params[] = (int)$limit;
	$params[] = (int)$offset;
	return saris_fetch_all(
		"SELECT id, student_name, invoice_reference_number, student_regnumber, invoice_amount,
			invoice_amount_type, invoice_desciption, invoice_date, sync_status,
			zerp_invoice_no, sync_error, created_at
		FROM invoices
		$where
		ORDER BY `$sort` $dir, id DESC
		LIMIT ? OFFSET ?",
		$params
	);
}

function saris_list_payments($limit, $offset, $searchTerm = '', $sort = 'payment_date', $dir = 'DESC') {
	$allowedSorts = ['id', 'student_name', 'student_regnumber', 'payment_desciption', 'payment_amount', 'payment_amount_type', 'payment_currency', 'payment_receipt_number', 'payment_transaction_ref', 'payment_date', 'payment_reference_number', 'payment_source', 'sync_status', 'zerp_receipt_no', 'zerp_invoice_no', 'allocation_synced_at', 'created_at'];
	if (!in_array($sort, $allowedSorts, true)) {
		$sort = 'payment_date';
	}
	$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		$where = "WHERE student_name LIKE ? OR student_regnumber LIKE ? OR payment_receipt_number LIKE ? OR payment_transaction_ref LIKE ? ";
		$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
	}
	$params[] = (int)$limit;
	$params[] = (int)$offset;
	return saris_fetch_all(
		"SELECT id, student_name, student_regnumber, payment_desciption, payment_amount,
			payment_amount_type, payment_currency, payment_receipt_number, payment_transaction_ref,
			payment_date, payment_reference_number, payment_source, sync_status,
			zerp_receipt_no, zerp_invoice_no, allocation_synced_at, sync_error, created_at
		FROM payments
		$where
		ORDER BY `$sort` $dir, id DESC
		LIMIT ? OFFSET ?",
		$params
	);
}

function saris_count_sync_history($searchTerm = '') {
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		$where = " WHERE run_id LIKE ? OR trigger_type LIKE ? OR sync_status LIKE ? OR message LIKE ? OR error_summary LIKE ?";
		$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
	}
	$row = saris_fetch_one("SELECT COUNT(*) AS total FROM saris_sync_log" . $where, $params);
	return (int)$row['total'];
}

function saris_list_sync_history($limit, $offset, $searchTerm = '', $sort = 'created_at', $dir = 'DESC') {
	$allowedSorts = [
		'id', 'trigger_type', 'sync_status', 'date_from', 'date_to', 'iterations',
		'saris_invoices', 'saris_students', 'saris_payments', 'zerp_students',
		'zerp_invoices', 'zerp_payments', 'zerp_partial', 'zerp_failed',
		'duration_seconds', 'created_at'
	];
	if (!in_array($sort, $allowedSorts, true)) {
		$sort = 'created_at';
	}
	$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
	$where = '';
	$params = [];
	if ($searchTerm !== '') {
		$where = "WHERE run_id LIKE ? OR trigger_type LIKE ? OR sync_status LIKE ? OR message LIKE ? OR error_summary LIKE ? ";
		$params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
	}
	$params[] = (int)$limit;
	$params[] = (int)$offset;
	return saris_fetch_all(
		"SELECT id, run_id, trigger_type, sync_status, date_from, date_to, iterations,
			saris_invoices, saris_students, saris_payments, zerp_enabled,
			zerp_students, zerp_invoices, zerp_payments, zerp_partial,
			zerp_failed, zerp_skipped, error_summary, message, started_at,
			completed_at, duration_seconds, created_at
		FROM saris_sync_log
		$where
		ORDER BY `$sort` $dir, id DESC
		LIMIT ? OFFSET ?",
		$params
	);
}

function saris_render_tabs($active, $searchContext = null, $searchTerm = '') {
	global $RootPath;
	echo '<style>
		.saris-table-wrapper {
			position: relative;
			overflow-x: auto;
			background-color: #fcfcfc;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05);
			border-radius: 8px;
			border: 1px solid #e5e7eb;
			margin-bottom: 1rem;
		}
		.saris-table {
			width: 100%;
			font-size: 0.875rem;
			text-align: left;
			color: #4b5563;
			border-collapse: collapse;
		}
		.saris-table thead {
			background-color: #f3f4f6;
			border-bottom: 1px solid #d1d5db;
			color: #374151;
		}
		.saris-table th {
			padding: 12px 24px;
			font-weight: 500;
			white-space: nowrap;
		}
		.saris-table tbody tr {
			background-color: #fcfcfc;
			border-bottom: 1px solid #e5e7eb;
			transition: background-color 0.15s ease;
		}
		.saris-table tbody tr:hover {
			background-color: #f3f4f6;
		}
		.saris-table td {
			padding: 16px 24px;
			white-space: nowrap;
		}
		.saris-checkbox-cell {
			padding: 16px !important;
			width: 16px;
			text-align: center;
		}
		.saris-checkbox-cell input[type="checkbox"] {
			width: 16px;
			height: 16px;
			cursor: pointer;
		}
	</style>';
	$tabs = [
		'Settings'          => '/SARIS_Settings.php',
		'API Configuration' => '/SARIS_APIConfig.php',
		'Bank Mappings'     => '/SarisBankAccountMappings.php',
		'Students'          => '/SARIS_Students.php',
		'Invoices'          => '/SARIS_Invoices.php',
		'Payments'          => '/SARIS_Payments.php',
		'Sync History'      => '/SARIS_SyncHistory.php',
	];
	echo '<div class="noPrint" style="display:flex;gap:16px;flex-wrap:wrap;justify-content:space-between;align-items:center;margin:0 0 24px 0;">';
	echo '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
	foreach ($tabs as $caption => $url) {
		$class = $caption === $active ? 'db-btn db-btn-primary' : 'db-btn db-btn-secondary';
		echo '<a class="' . $class . '" href="' . $RootPath . $url . '">' . __($caption) . '</a>';
	}
	echo '</div>';

	if ($searchContext !== null) {
		echo '<form id="saris-search-form" method="get" action="" style="display:flex;gap:8px;align-items:center;">';
		echo '<input type="text" id="saris-search-input" class="db-form-input" name="Search" placeholder="' . __('Search ' . $searchContext . '...') . '" value="' . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . '" style="min-width:250px;" />';
		echo '<button class="db-btn db-btn-primary" type="submit">' . __('Search') . '</button>';
		echo '<a id="saris-clear-btn" class="db-btn db-btn-secondary" href="?" style="' . ($searchTerm === '' ? 'display:none;' : '') . '">' . __('Clear') . '</a>';
		echo '</form>';
	}
	echo '</div>';
}

function saris_render_pagination($totalRows, $page, $perPage, $baseUrl, $extraParams = '') {
	$totalPages = max(1, (int)ceil($totalRows / $perPage));
	if ($totalPages <= 1) {
		echo '<div id="saris-pagination-container"></div>';
		return;
	}
	
	echo '<div id="saris-pagination-container" class="noPrint" style="display:flex;justify-content:flex-end;margin-top:16px;">';
	echo '<div style="display:inline-flex;border-radius:6px;box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);">';
	
	$page = (int)$page;
	
	// Prev Button
	if ($page > 1) {
		$prevUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '?Page=' . ($page - 1);
		if ($extraParams !== '') $prevUrl .= '&amp;' . htmlspecialchars($extraParams, ENT_QUOTES, 'UTF-8');
		echo '<a class="saris-page-link" href="' . $prevUrl . '" data-page="' . ($page - 1) . '" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-right:none;background:#f9fafb;color:#4b5563;border-radius:6px 0 0 6px;text-decoration:none;"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg></a>';
	} else {
		echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-right:none;background:#f3f4f6;color:#9ca3af;border-radius:6px 0 0 6px;"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg></span>';
	}
	
	// Page Info
	echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 16px;border:1px solid #d1d5db;background:#f9fafb;color:#4b5563;font-size:14px;font-weight:500;">' . $page . ' ' . __('of') . ' ' . $totalPages . '</span>';
	
	// Next Button
	if ($page < $totalPages) {
		$nextUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '?Page=' . ($page + 1);
		if ($extraParams !== '') $nextUrl .= '&amp;' . htmlspecialchars($extraParams, ENT_QUOTES, 'UTF-8');
		echo '<a class="saris-page-link" href="' . $nextUrl . '" data-page="' . ($page + 1) . '" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-left:none;background:#f9fafb;color:#4b5563;border-radius:0 6px 6px 0;text-decoration:none;"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg></a>';
	} else {
		echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-left:none;background:#f3f4f6;color:#9ca3af;border-radius:0 6px 6px 0;"><svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg></span>';
	}
	
	echo '</div></div>';
}

function saris_render_sort_header($label, $column, $currentSort, $currentDir) {
	$isCurrent = $column === $currentSort;
	$nextDir = $isCurrent && $currentDir === 'ASC' ? 'DESC' : 'ASC';
	$icon = '';
	if ($isCurrent) {
		$icon = $currentDir === 'ASC' ? ' &uarr;' : ' &darr;';
	}
	echo '<th style="position:sticky;top:0;background-color:#f8f9fa;z-index:10;box-shadow:0 1px 2px rgba(0,0,0,0.1);"><a href="?Sort=' . $column . '&amp;Dir=' . $nextDir . '" class="saris-sort-link" data-sort="' . $column . '" data-dir="' . $nextDir . '" style="color:inherit;text-decoration:none;display:block;">' . __($label) . $icon . '</a></th>';
}

function saris_render_scripts($baseUrl) {
	echo '<script>
	document.addEventListener("DOMContentLoaded", function() {
		var searchInput = document.getElementById("saris-search-input");
		var searchForm = document.getElementById("saris-search-form");
		var clearBtn = document.getElementById("saris-clear-btn");
		var tableBody = document.querySelector(".saris-table tbody");
		
		var currentSort = new URLSearchParams(window.location.search).get("Sort") || "";
		var currentDir = new URLSearchParams(window.location.search).get("Dir") || "";
		var currentPage = new URLSearchParams(window.location.search).get("Page") || 1;
		var timer = null;

		function loadData(search, page, sort, dir, pushState) {
			var url = "' . $baseUrl . '?ajax=1&Search=" + encodeURIComponent(search) + "&Page=" + page;
			if (sort) url += "&Sort=" + encodeURIComponent(sort) + "&Dir=" + encodeURIComponent(dir);
			
			fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
			.then(res => res.json())
			.then(data => {
				tableBody.innerHTML = data.tbody;
				var existingPagination = document.getElementById("saris-pagination-container");
				if (existingPagination) {
					existingPagination.outerHTML = data.pagination;
				}
				
				if (clearBtn) {
					clearBtn.style.display = search ? "inline-flex" : "none";
				}
				
				document.querySelectorAll(".saris-sort-link").forEach(function(link) {
					var col = link.getAttribute("data-sort");
					var text = link.innerText.replace(/ ↑| ↓/g, "");
					var nextDir = "ASC";
					if (col === sort) {
						nextDir = dir === "ASC" ? "DESC" : "ASC";
						text += dir === "ASC" ? " ↑" : " ↓";
					}
					link.setAttribute("data-dir", nextDir);
					var newUrl = "?Sort=" + col + "&Dir=" + nextDir;
					if (search) newUrl += "&Search=" + encodeURIComponent(search);
					link.setAttribute("href", newUrl);
					link.innerText = text;
				});

				if (pushState) {
					var stateUrl = "?Page=" + page;
					if (search) stateUrl += "&Search=" + encodeURIComponent(search);
					if (sort) stateUrl += "&Sort=" + encodeURIComponent(sort) + "&Dir=" + encodeURIComponent(dir);
					window.history.pushState({search: search, page: page, sort: sort, dir: dir}, "", stateUrl);
				}
			});
		}

		if (searchInput) {
			searchInput.addEventListener("keyup", function(e) {
				clearTimeout(timer);
				timer = setTimeout(function() {
					currentPage = 1;
					loadData(searchInput.value, currentPage, currentSort, currentDir, true);
				}, 300);
			});
		}

		if (searchForm) {
			searchForm.addEventListener("submit", function(e) {
				e.preventDefault();
				clearTimeout(timer);
				currentPage = 1;
				loadData(searchInput.value, currentPage, currentSort, currentDir, true);
			});
		}

		document.addEventListener("click", function(e) {
			var pageLink = e.target.closest(".saris-page-link");
			if (pageLink) {
				e.preventDefault();
				currentPage = pageLink.getAttribute("data-page");
				loadData(searchInput ? searchInput.value : "", currentPage, currentSort, currentDir, true);
			}

			var sortLink = e.target.closest(".saris-sort-link");
			if (sortLink) {
				e.preventDefault();
				currentSort = sortLink.getAttribute("data-sort");
				currentDir = sortLink.getAttribute("data-dir");
				currentPage = 1;
				loadData(searchInput ? searchInput.value : "", currentPage, currentSort, currentDir, true);
			}
		});

		window.addEventListener("popstate", function(e) {
			if (e.state) {
				if (searchInput) searchInput.value = e.state.search || "";
				currentPage = e.state.page || 1;
				currentSort = e.state.sort || "";
				currentDir = e.state.dir || "";
				loadData(searchInput ? searchInput.value : "", currentPage, currentSort, currentDir, false);
			}
		});
	});
	</script>';
}
?>
