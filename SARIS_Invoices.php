<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Invoices');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/SARISIntegration.php');

$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$totalRows = saris_count_rows('invoices');
$result = saris_list_invoices($perPage, $offset);

echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Invoices') . '</h1><p class="db-page-subtitle">' . __('Invoice records imported from SARIS') . '</p></div>';
saris_render_tabs('Invoices');
echo '<div class="db-card"><div class="db-table-wrapper"><table class="db-table"><thead><tr>';
$columns = ['ID', 'Student Name', 'Invoice Reference Number', 'Reg Number', 'Amount', 'Amount Type', 'Description', 'Invoice Date', 'Created At'];
foreach ($columns as $column) {
	echo '<th>' . __($column) . '</th>';
}
echo '</tr></thead><tbody>';
foreach ($result as $row) {
	echo '<tr>';
	foreach (['id', 'student_name', 'invoice_reference_number', 'student_regnumber', 'invoice_amount', 'invoice_amount_type', 'invoice_desciption', 'invoice_date', 'created_at'] as $key) {
		echo '<td>' . htmlspecialchars((string)$row[$key], ENT_QUOTES, 'UTF-8') . '</td>';
	}
	echo '</tr>';
}
echo '</tbody></table></div></div>';
saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF']);
echo '</div>';

include(__DIR__ . '/includes/footer.php');
?>
