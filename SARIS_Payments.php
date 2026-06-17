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

$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$totalRows = saris_count_rows('payments');
$result = saris_list_payments($perPage, $offset);

echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Payments') . '</h1><p class="db-page-subtitle">' . __('Payment records imported from SARIS') . '</p></div>';
saris_render_tabs('Payments');

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

echo '<div class="db-card"><div class="db-table-wrapper"><table class="db-table"><thead><tr>';
$columns = ['ID', 'Student Name', 'Reg Number', 'Description', 'Amount', 'Amount Type', 'Currency', 'Receipt Number', 'Transaction Ref', 'Payment Date', 'Reference Number', 'Source', 'Created At'];
foreach ($columns as $column) {
	echo '<th>' . __($column) . '</th>';
}
echo '</tr></thead><tbody>';
foreach ($result as $row) {
	echo '<tr>';
	foreach (['id', 'student_name', 'student_regnumber', 'payment_desciption', 'payment_amount', 'payment_amount_type', 'payment_currency', 'payment_receipt_number', 'payment_transaction_ref', 'payment_date', 'payment_reference_number', 'payment_source', 'created_at'] as $key) {
		echo '<td>' . htmlspecialchars((string)$row[$key], ENT_QUOTES, 'UTF-8') . '</td>';
	}
	echo '</tr>';
}
echo '</tbody></table></div></div>';
saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF']);
echo '</div>';

include(__DIR__ . '/includes/footer.php');
?>
