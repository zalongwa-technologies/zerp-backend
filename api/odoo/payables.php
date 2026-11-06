<?php

declare(strict_types=1);

require_once(__DIR__ . '/PayablesApi.php');

function payablesJson(int $status, array $body): never
{
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	header('X-Content-Type-Options: nosniff');
	echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
	exit;
}

try {
	if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
		header('Allow: GET');
		throw new PayablesApiException(405, 'METHOD_NOT_ALLOWED', 'Only GET requests are supported');
	}

	$root = dirname(__DIR__, 2) . '/';
	if (!is_file($root . 'config.php')) {
		throw new PayablesApiException(500, 'API_NOT_CONFIGURED', 'The application is not configured');
	}
	require($root . 'config.php');
	$https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
	if (($ODOO_PAYABLES_API_REQUIRE_HTTPS ?? true) && $https !== 'on' && $https !== '1') {
		throw new PayablesApiException(400, 'HTTPS_REQUIRED', 'The payables API requires HTTPS');
	}
	$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
	PayablesRequest::authenticate($authorization, $ODOO_PAYABLES_API_TOKEN ?? null);
	$filters = PayablesRequest::parse($_GET);

	if (!in_array($DBType ?? '', ['mysqli', 'mariadb'], true)) {
		throw new RuntimeException('The payables API requires the mysqli database driver');
	}
	$database = $ODOO_PAYABLES_API_DATABASE ?? $DefaultDatabase ?? null;
	if (!is_string($database) || !preg_match('/^[A-Za-z0-9_]+$/', $database)) {
		throw new PayablesApiException(500, 'API_NOT_CONFIGURED', 'The payables API database is not configured');
	}
	mysqli_report(MYSQLI_REPORT_OFF);
	$db = @new mysqli($Host, $DBUser, $DBPassword, $database, $DBPort ?? 3306);
	if ($db->connect_errno !== 0) throw new RuntimeException('Database connection failed');
	if (!$db->set_charset($DBCharset ?? 'utf8mb4')) throw new RuntimeException('Database character set configuration failed');

	$repository = new PayablesRepository($db);
	if ($filters['supplier_id'] !== null && !$repository->supplierExists($filters['supplier_id'])) {
		throw new PayablesApiException(404, 'SUPPLIER_NOT_FOUND', 'The requested supplier does not exist');
	}
	$result = $repository->export($filters);
	$totalPages = $result['total'] === 0 ? 0 : (int)ceil($result['total'] / $filters['page_size']);
	payablesJson(200, [
		'success' => true,
		'generated_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
		'filters' => [
			'supplier_id' => $filters['supplier_id'], 'from_date' => $filters['from_date'], 'to_date' => $filters['to_date'],
			'transaction_types' => $filters['transaction_types'], 'include_void' => $filters['include_void'], 'include_settled' => $filters['include_settled'],
		],
		'pagination' => ['page' => $filters['page'], 'page_size' => $filters['page_size'], 'total_records' => $result['total'], 'total_pages' => $totalPages, 'has_next' => $filters['page'] < $totalPages],
		'data' => $result['data'],
	]);
} catch (PayablesApiException $e) {
	payablesJson($e->status, ['success' => false, 'error' => ['code' => $e->errorCode, 'message' => $e->getMessage()]]);
} catch (Throwable $e) {
	error_log('ZERP payables API error: ' . $e->getMessage());
	payablesJson(500, ['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => 'An unexpected server error occurred']]);
}
