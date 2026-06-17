<?php

class SARISAPIClient {
	private $baseUrl = 'https://star.mum.ac.tz';
	private $clientId = '';
	private $clientSecret = '';
	private $timeout = 60;

	public function __construct() {
		$SARIS_API_BASE_URL = '';
		$SARIS_API_CLIENT_ID = '';
		$SARIS_API_CLIENT_SECRET = '';
		$configFile = __DIR__ . '/../config.php';
		if (file_exists($configFile)) {
			include($configFile);
		}
		if ($SARIS_API_BASE_URL !== '') {
			$this->baseUrl = rtrim($SARIS_API_BASE_URL, '/');
		}
		$this->clientId = $SARIS_API_CLIENT_ID;
		$this->clientSecret = $SARIS_API_CLIENT_SECRET;
	}

	private function ensureCredentials() {
		if ($this->clientId === '' || $this->clientSecret === '') {
			throw new Exception('SARIS API credentials are missing in config.php.');
		}
	}

	private function authenticate() {
		$this->ensureCredentials();
		$url = $this->baseUrl . '/api_erp/v1/auth';
		$payload = json_encode([
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret
		]);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		$result = curl_exec($ch);
		$error = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($result === false) {
			throw new Exception('Could not authenticate with SARIS: ' . $error);
		}
		$response = json_decode($result, true);
		if ($httpCode !== 200 || !is_array($response) || (isset($response['statusCode']) && (int)$response['statusCode'] !== 200) || empty($response['token'])) {
			throw new Exception('SARIS authentication failed with HTTP ' . $httpCode . '.');
		}

		$_SESSION['SARISAPI_TOKEN'] = $response['token'];
		$_SESSION['SARISAPI_TOKEN_EXPIRES_AT'] = time() + (isset($response['expires_in']) ? (int)$response['expires_in'] : 900) - 30;
		return $_SESSION['SARISAPI_TOKEN'];
	}

	private function getToken() {
		if (empty($_SESSION['SARISAPI_TOKEN']) || empty($_SESSION['SARISAPI_TOKEN_EXPIRES_AT']) || time() >= (int)$_SESSION['SARISAPI_TOKEN_EXPIRES_AT']) {
			return $this->authenticate();
		}
		return $_SESSION['SARISAPI_TOKEN'];
	}

	private function request($endpoint, $params = []) {
		$url = $this->baseUrl . $endpoint;
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->getToken(),
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		$result = curl_exec($ch);
		$error = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($result === false) {
			throw new Exception('SARIS API request failed: ' . $error);
		}
		$decoded = json_decode($result, true);
		if ($httpCode === 404) {
			return ['statusCode' => 404, 'data' => null];
		}
		if ($httpCode !== 200) {
			throw new Exception('SARIS API returned HTTP ' . $httpCode . ' for ' . $endpoint . '.');
		}
		if (!is_array($decoded)) {
			throw new Exception('SARIS API returned an invalid JSON response.');
		}
		if (isset($decoded['statusCode']) && (int)$decoded['statusCode'] === 404) {
			return ['statusCode' => 404, 'data' => null];
		}
		if (isset($decoded['statusCode']) && (int)$decoded['statusCode'] !== 200) {
			$message = isset($decoded['message']) ? $decoded['message'] : 'Unexpected SARIS API status.';
			throw new Exception($message);
		}
		return $decoded;
	}

	public function getAllInvoices($dateFrom, $dateTo) {
		return $this->request('/api_erp/v1/get_all_invoices', [
			'invoice_date_from' => $dateFrom,
			'invoice_date_to' => $dateTo
		]);
	}

	public function getStudentInfo($regNumber) {
		return $this->request('/api_erp/v1/get_student_info', ['reg_number' => $regNumber]);
	}

	public function getAllPayments($dateFrom, $dateTo) {
		return $this->request('/api_erp/v1/get_all_payments', [
			'payment_date_from' => $dateFrom,
			'payment_date_to' => $dateTo
		]);
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
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	global $Host, $DBUser, $DBPassword, $DBPort;
	$databaseName = isset($_SESSION['DatabaseName']) ? $_SESSION['DatabaseName'] : '';
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
	saris_execute(
		"INSERT INTO invoices (
			student_name, invoice_reference_number, student_regnumber, invoice_amount,
			invoice_amount_type, invoice_desciption, invoice_date
		) VALUES (?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			student_name = VALUES(student_name),
			student_regnumber = VALUES(student_regnumber),
			invoice_amount = VALUES(invoice_amount),
			invoice_amount_type = VALUES(invoice_amount_type),
			invoice_desciption = VALUES(invoice_desciption),
			invoice_date = VALUES(invoice_date)",
		[
			$invoice['student_name'] ?? null,
			$invoice['invoice_reference_number'] ?? null,
			$invoice['student_regnumber'] ?? null,
			saris_sql_decimal($invoice['invoice_amount'] ?? 0),
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
			payment_amount = VALUES(payment_amount),
			payment_amount_type = VALUES(payment_amount_type),
			payment_currency = VALUES(payment_currency),
			payment_transaction_ref = VALUES(payment_transaction_ref),
			payment_date = VALUES(payment_date),
			payment_reference_number = VALUES(payment_reference_number),
			payment_source = VALUES(payment_source)",
		[
			$payment['student_name'] ?? null,
			$payment['student_regnumber'] ?? null,
			$payment['payment_desciption'] ?? null,
			saris_sql_decimal($payment['payment_amount'] ?? 0),
			$payment['payment_amount_type'] ?? null,
			$payment['payment_currency'] ?? null,
			$payment['payment_receipt_number'] ?? null,
			$payment['payment_transaction_ref'] ?? null,
			saris_normalize_datetime($payment['payment_date'] ?? null),
			$payment['payment_reference_number'] ?? null,
			$payment['payment_source'] ?? null
		]
	);
}

function saris_sync_payments($client, $startDate, $endDate) {
	$response = $client->getAllPayments($startDate, $endDate);
	$payments = saris_extract_records($response);
	$count = 0;
	foreach ($payments as $payment) {
		saris_upsert_payment($payment);
		$count++;
	}
	return $count;
}

function saris_full_sync($client, $startDate, $endDate) {
	$stats = [
		'invoices' => 0,
		'students' => 0,
		'students_skipped' => 0,
		'payments' => 0
	];

	$response = $client->getAllInvoices($startDate, $endDate);
	$invoices = saris_extract_records($response);
	$regNumbers = [];
	foreach ($invoices as $invoice) {
		saris_upsert_invoice($invoice);
		$stats['invoices']++;
		if (!empty($invoice['student_regnumber'])) {
			$regNumbers[$invoice['student_regnumber']] = true;
		}
	}

	foreach (array_keys($regNumbers) as $regNumber) {
		$response = $client->getStudentInfo($regNumber);
		if (isset($response['statusCode']) && (int)$response['statusCode'] === 404) {
			$stats['students_skipped']++;
			continue;
		}
		$student = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
		if (is_array($student) && !empty($student['student_regnumber'])) {
			saris_upsert_student($student);
			$stats['students']++;
		}
	}

	$stats['payments'] = saris_sync_payments($client, $startDate, $endDate);
	return $stats;
}

function saris_log_sync($message, $status = 'success') {
	saris_execute(
		"INSERT INTO saris_sync_log (sync_status, message, created_at) VALUES (?, ?, NOW())",
		[$status, $message]
	);
}

function saris_normalize_datetime($value) {
	if ($value === null || $value === '') {
		return null;
	}
	$timestamp = strtotime($value);
	return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
}

function saris_count_rows($table) {
	$allowedTables = ['students', 'invoices', 'payments'];
	if (!in_array($table, $allowedTables, true)) {
		throw new Exception('Invalid SARIS table requested.');
	}
	$row = saris_fetch_one("SELECT COUNT(*) AS total FROM `" . $table . "`");
	return (int)$row['total'];
}

function saris_list_students($limit, $offset) {
	return saris_fetch_all(
		"SELECT id, student_regnumber, student_fullname, student_email, student_phone,
			student_programme, student_entryyear, student_studyyear, student_intake, created_at
		FROM students
		ORDER BY student_regnumber
		LIMIT ? OFFSET ?",
		[(int)$limit, (int)$offset]
	);
}

function saris_list_invoices($limit, $offset) {
	return saris_fetch_all(
		"SELECT id, student_name, invoice_reference_number, student_regnumber, invoice_amount,
			invoice_amount_type, invoice_desciption, invoice_date, created_at
		FROM invoices
		ORDER BY invoice_date DESC, id DESC
		LIMIT ? OFFSET ?",
		[(int)$limit, (int)$offset]
	);
}

function saris_list_payments($limit, $offset) {
	return saris_fetch_all(
		"SELECT id, student_name, student_regnumber, payment_desciption, payment_amount,
			payment_amount_type, payment_currency, payment_receipt_number, payment_transaction_ref,
			payment_date, payment_reference_number, payment_source, created_at
		FROM payments
		ORDER BY payment_date DESC, id DESC
		LIMIT ? OFFSET ?",
		[(int)$limit, (int)$offset]
	);
}

function saris_render_tabs($active) {
	global $RootPath;
	$tabs = [
		'Settings' => '/SARIS_Settings.php',
		'Students' => '/SARIS_Students.php',
		'Invoices' => '/SARIS_Invoices.php',
		'Payments' => '/SARIS_Payments.php'
	];
	echo '<div class="noPrint" style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 20px 0;">';
	foreach ($tabs as $caption => $url) {
		$class = $caption === $active ? 'db-btn db-btn-primary' : 'db-btn db-btn-secondary';
		echo '<a class="' . $class . '" href="' . $RootPath . $url . '">' . __($caption) . '</a>';
	}
	echo '</div>';
}

function saris_render_pagination($totalRows, $page, $perPage, $baseUrl) {
	$totalPages = max(1, (int)ceil($totalRows / $perPage));
	if ($totalPages <= 1) {
		return;
	}
	echo '<div class="noPrint" style="display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-top:16px;">';
	for ($i = 1; $i <= $totalPages; $i++) {
		$class = $i === $page ? 'db-btn db-btn-primary' : 'db-btn db-btn-secondary';
		echo '<a class="' . $class . '" href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '?Page=' . $i . '">' . $i . '</a>';
	}
	echo '</div>';
}

?>
