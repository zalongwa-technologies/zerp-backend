<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Students');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/SARISIntegration.php');

$settings = saris_get_settings();

if (isset($_POST['SyncStudents'])) {
	$startDate = isset($_POST['StartDate']) ? $_POST['StartDate'] : '';
	$endDate = isset($_POST['EndDate']) ? $_POST['EndDate'] : '';
	if (!saris_validate_date_range($startDate, $endDate)) {
		prnMsg(__('Please enter a valid date range. End Date must be greater than or equal to Start Date.'), 'error');
	} else {
		try {
			$stats = saris_full_sync(new SARISAPIClient(), $startDate, $endDate);
			prnMsg(__('Sync completed.') . ' ' . __('Invoices') . ': ' . $stats['invoices'] . ', ' . __('Students') . ': ' . $stats['students'] . ', ' . __('Payments') . ': ' . $stats['payments'] . '.', 'success');
		} catch (Exception $e) {
			prnMsg(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), 'error');
		}
	}
}

$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$totalRows = saris_count_rows('students');
$students = saris_list_students($perPage, $offset);

echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Students') . '</h1><p class="db-page-subtitle">' . __('Student records imported from SARIS') . '</p></div>';
saris_render_tabs('Students');

if ($settings['sync_mode'] === 'manual') {
	$defaultStart = saris_default_invoice_start_date();
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" class="noPrint" style="margin-bottom:24px;">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="db-card"><div class="db-card-header"><h3 class="db-card-title">' . __('Sync Students') . '</h3></div><div class="db-card-body"><div class="db-grid db-grid-3" style="gap:16px;">';
	echo '<div class="db-form-group"><label class="db-form-label">' . __('Start Date') . '</label><input class="db-form-input" type="date" name="StartDate" value="' . $defaultStart . '" required="required" /></div>';
	echo '<div class="db-form-group"><label class="db-form-label">' . __('End Date') . '</label><input class="db-form-input" type="date" name="EndDate" value="' . date('Y-m-d') . '" required="required" /></div>';
	echo '<div class="db-form-group" style="display:flex;align-items:flex-end;"><button class="db-btn db-btn-primary" type="submit" name="SyncStudents">' . __('Sync Students') . '</button></div>';
	echo '</div></div></div></form>';
}

echo '<div class="db-card"><div class="db-table-wrapper"><table class="db-table"><thead><tr>';
$columns = ['ID', 'Reg Number', 'Full Name', 'Email', 'Phone', 'Programme', 'Entry Year', 'Study Year', 'Intake', 'Created At'];
foreach ($columns as $column) {
	echo '<th>' . __($column) . '</th>';
}
echo '</tr></thead><tbody>';
foreach ($students as $row) {
	echo '<tr>';
	foreach (['id', 'student_regnumber', 'student_fullname', 'student_email', 'student_phone', 'student_programme', 'student_entryyear', 'student_studyyear', 'student_intake', 'created_at'] as $key) {
		echo '<td>' . htmlspecialchars((string)$row[$key], ENT_QUOTES, 'UTF-8') . '</td>';
	}
	echo '</tr>';
}
echo '</tbody></table></div></div>';
saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF']);
echo '</div>';

include(__DIR__ . '/includes/footer.php');
?>
