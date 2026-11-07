<?php

declare(strict_types=1);

require_once(__DIR__ . '/ReceivablesApi.php');

function receivablesJson(int $status, array $body): never
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
		throw new ReceivablesApiException(405, 'METHOD_NOT_ALLOWED', 'Only GET requests are supported');
	}
	$root = dirname(__DIR__, 2) . '/';
	if (!is_file($root . 'config.php')) throw new ReceivablesApiException(500, 'API_NOT_CONFIGURED', 'The application is not configured');
	require($root . 'config.php');
	$https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
	if (($ODOO_PAYABLES_API_REQUIRE_HTTPS ?? true) && $https !== 'on' && $https !== '1') {
		throw new ReceivablesApiException(400, 'HTTPS_REQUIRED', 'The receivables API requires HTTPS');
	}
	$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
	ReceivablesRequest::authenticate($authorization, $ODOO_PAYABLES_API_TOKEN ?? null);
	$filters = ReceivablesRequest::parse($_GET);
	if (!in_array($DBType ?? '', ['mysqli', 'mariadb'], true)) throw new RuntimeException('The receivables API requires the mysqli database driver');
	$database = $ODOO_PAYABLES_API_DATABASE ?? $DefaultDatabase ?? null;
	if (!is_string($database) || !preg_match('/^[A-Za-z0-9_]+$/', $database)) throw new ReceivablesApiException(500, 'API_NOT_CONFIGURED', 'The receivables API database is not configured');
	mysqli_report(MYSQLI_REPORT_OFF);
	$port = filter_var($DBPort ?? 3306, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
	if ($port === false) throw new ReceivablesApiException(500, 'API_NOT_CONFIGURED', 'The receivables API database port is invalid');
	$db = @new mysqli($Host, $DBUser, $DBPassword, $database, $port);
	if ($db->connect_errno !== 0) throw new RuntimeException('Database connection failed');
	if (!$db->set_charset($DBCharset ?? 'utf8mb4')) throw new RuntimeException('Database character set configuration failed');
	$repository = new ReceivablesRepository($db);
	if ($filters['customer_id'] !== null && !$repository->customerExists($filters['customer_id'])) throw new ReceivablesApiException(404, 'CUSTOMER_NOT_FOUND', 'The requested customer does not exist');
	if ($filters['invoice_id'] !== null && !$repository->invoiceExists($filters['invoice_id'])) throw new ReceivablesApiException(404, 'INVOICE_NOT_FOUND', 'The requested invoice does not exist');
	$result = $repository->export($filters);
	$totalPages = $result['total'] === 0 ? 0 : (int)ceil($result['total'] / $filters['page_size']);
	receivablesJson(200, [
		'success' => true, 'generated_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
		'filters' => array_diff_key($filters, ['page' => true, 'page_size' => true]),
		'pagination' => ['page' => $filters['page'], 'page_size' => $filters['page_size'], 'total_records' => $result['total'], 'total_pages' => $totalPages, 'has_next' => $filters['page'] < $totalPages],
		'data' => $result['data'],
	]);
} catch (ReceivablesApiException $e) {
	receivablesJson($e->status, ['success' => false, 'error' => ['code' => $e->errorCode, 'message' => $e->getMessage()]]);
} catch (Throwable $e) {
	error_log('ZERP receivables API error: ' . $e->getMessage());
	receivablesJson(500, ['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => 'An unexpected server error occurred']]);
}
