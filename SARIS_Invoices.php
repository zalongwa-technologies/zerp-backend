<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Invoices');
include(__DIR__ . '/includes/SARISIntegration.php');

$searchTerm = isset($_GET['Search']) ? trim($_GET['Search']) : '';
$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$sort = isset($_GET['Sort']) ? $_GET['Sort'] : 'invoice_date';
$dir = isset($_GET['Dir']) && strtoupper($_GET['Dir']) === 'ASC' ? 'ASC' : 'DESC';

$totalRows = saris_count_rows('invoices', $searchTerm);
$result = saris_list_invoices($perPage, $offset, $searchTerm, $sort, $dir);

$extraParams = $searchTerm !== '' ? 'Search=' . urlencode($searchTerm) : '';
if ($extraParams !== '') {
	$extraParams .= '&Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
} else {
	$extraParams = 'Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	ob_start();
	if (count($result) === 0) {
		echo '<tr><td colspan="12" style="text-align:center;padding:60px;color:#6b7280;">';
		echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
		echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
	} else {
		foreach ($result as $row) {
			echo '<tr>';
			foreach (['id', 'student_name', 'invoice_reference_number', 'student_regnumber', 'invoice_amount', 'invoice_amount_type', 'invoice_desciption', 'invoice_date', 'sync_status', 'zerp_invoice_no', 'sync_error', 'created_at'] as $key) {
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

include(__DIR__ . '/includes/header.php');
echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Invoices') . '</h1><p class="db-page-subtitle">' . __('Invoice records imported from SARIS') . '</p></div>';
saris_render_tabs('Invoices', 'Invoices', $searchTerm);


echo '<div class="db-card"><div class="db-table-wrapper" style="overflow-x:auto;width:100%;-webkit-overflow-scrolling:touch;max-height:70vh;"><table class="db-table"><thead><tr>';
$columns = [
	'ID' => 'id',
	'Student Name' => 'student_name',
	'Invoice Reference Number' => 'invoice_reference_number',
	'Reg Number' => 'student_regnumber',
	'Amount' => 'invoice_amount',
	'Amount Type' => 'invoice_amount_type',
	'Description' => 'invoice_desciption',
	'Invoice Date' => 'invoice_date',
	'Sync Status' => 'sync_status',
	'ZERP Invoice' => 'zerp_invoice_no',
	'Sync Error' => null,
	'Created At' => 'created_at'
];
foreach ($columns as $label => $col) {
	if ($col === null) {
		echo '<th>' . __($label) . '</th>';
	} else {
		saris_render_sort_header($label, $col, $sort, $dir);
	}
}
echo '</tr></thead><tbody>';
if (count($result) === 0) {
	echo '<tr><td colspan="12" style="text-align:center;padding:60px;color:#6b7280;">';
	echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
	echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
} else {
	foreach ($result as $row) {
		echo '<tr>';
		foreach (['id', 'student_name', 'invoice_reference_number', 'student_regnumber', 'invoice_amount', 'invoice_amount_type', 'invoice_desciption', 'invoice_date', 'sync_status', 'zerp_invoice_no', 'sync_error', 'created_at'] as $key) {
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
