<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Payments');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/SARISIntegration.php');

$settings = saris_get_settings();

if (isset($_POST['SyncPayments'])) {
	$startDate = isset($_POST['StartDate']) ? $_POST['StartDate'] : '';
	$endDate = isset($_POST['EndDate']) ? $_POST['EndDate'] : '';
	if (!saris_validate_date_range($startDate, $endDate)) {
		prnMsg(__('Please enter a valid date range. End Date must be greater than or equal to Start Date.'), 'error');
	} else {
		try {
			$count = saris_sync_payments(new SARISAPIClient(), $startDate, $endDate);
			prnMsg(__('Payments sync completed.') . ' ' . __('Payments') . ': ' . $count . '.', 'success');
		} catch (Exception $e) {
			prnMsg(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), 'error');
		}
	}
}

$searchTerm = isset($_GET['Search']) ? trim($_GET['Search']) : '';
$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$sort = isset($_GET['Sort']) ? $_GET['Sort'] : 'payment_date';
$dir = isset($_GET['Dir']) && strtoupper($_GET['Dir']) === 'ASC' ? 'ASC' : 'DESC';

$totalRows = saris_count_rows('payments', $searchTerm);
$result = saris_list_payments($perPage, $offset, $searchTerm, $sort, $dir);

$extraParams = $searchTerm !== '' ? 'Search=' . urlencode($searchTerm) : '';
if ($extraParams !== '') {
	$extraParams .= '&Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
} else {
	$extraParams = 'Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	ob_start();
	if (count($result) === 0) {
		echo '<tr><td colspan="13" style="text-align:center;padding:60px;color:#6b7280;">';
		echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
		echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
	} else {
		foreach ($result as $row) {
			echo '<tr>';
			foreach (['id', 'student_name', 'student_regnumber', 'payment_desciption', 'payment_amount', 'payment_amount_type', 'payment_currency', 'payment_receipt_number', 'payment_transaction_ref', 'payment_date', 'payment_reference_number', 'payment_source', 'created_at'] as $key) {
				echo '<td>' . htmlspecialchars((string)$row[$key], ENT_QUOTES, 'UTF-8') . '</td>';
			}
			echo '</tr>';
		}
	}
	$tbody = ob_get_clean();

	ob_start();
	saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF'], $extraParams);
	$pagination = ob_get_clean();

	header('Content-Type: application/json');
	echo json_encode(['tbody' => $tbody, 'pagination' => $pagination]);
	exit;
}

echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Payments') . '</h1><p class="db-page-subtitle">' . __('Payment records imported from SARIS') . '</p></div>';
saris_render_tabs('Payments', 'Payments', $searchTerm);

if ($settings['sync_mode'] === 'manual') {
	$defaultStart = saris_default_payment_start_date();
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" class="noPrint" style="margin-bottom:24px;">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="db-card"><div class="db-card-header"><h3 class="db-card-title">' . __('Sync Payments') . '</h3></div><div class="db-card-body"><div class="db-grid db-grid-3" style="gap:16px;">';
	echo '<div class="db-form-group"><label class="db-form-label">' . __('Start Date') . '</label><input class="db-form-input" type="date" name="StartDate" value="' . $defaultStart . '" required="required" /></div>';
	echo '<div class="db-form-group"><label class="db-form-label">' . __('End Date') . '</label><input class="db-form-input" type="date" name="EndDate" value="' . date('Y-m-d') . '" required="required" /></div>';
	echo '<div class="db-form-group" style="display:flex;align-items:flex-end;"><button class="db-btn db-btn-primary" type="submit" name="SyncPayments">' . __('Sync Payments') . '</button></div>';
	echo '</div></div></div></form>';
}


echo '<div class="db-card"><div class="db-table-wrapper" style="overflow-x:auto;width:100%;-webkit-overflow-scrolling:touch;max-height:70vh;"><table class="db-table"><thead><tr>';
$columns = [
	'ID' => 'id',
	'Student Name' => 'student_name',
	'Reg Number' => 'student_regnumber',
	'Description' => 'payment_desciption',
	'Amount' => 'payment_amount',
	'Amount Type' => 'payment_amount_type',
	'Currency' => 'payment_currency',
	'Receipt Number' => 'payment_receipt_number',
	'Transaction Ref' => 'payment_transaction_ref',
	'Payment Date' => 'payment_date',
	'Reference Number' => 'payment_reference_number',
	'Source' => 'payment_source',
	'Created At' => 'created_at'
];
foreach ($columns as $label => $col) {
	saris_render_sort_header($label, $col, $sort, $dir);
}
echo '</tr></thead><tbody>';
if (count($result) === 0) {
	echo '<tr><td colspan="13" style="text-align:center;padding:60px;color:#6b7280;">';
	echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
	echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
} else {
	foreach ($result as $row) {
		echo '<tr>';
		foreach (['id', 'student_name', 'student_regnumber', 'payment_desciption', 'payment_amount', 'payment_amount_type', 'payment_currency', 'payment_receipt_number', 'payment_transaction_ref', 'payment_date', 'payment_reference_number', 'payment_source', 'created_at'] as $key) {
			echo '<td>' . htmlspecialchars((string)$row[$key], ENT_QUOTES, 'UTF-8') . '</td>';
		}
		echo '</tr>';
	}
}
echo '</tbody></table></div></div>';
saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF'], $extraParams);
echo '</div>';

saris_render_scripts($_SERVER['PHP_SELF']);

include(__DIR__ . '/includes/footer.php');
?>
