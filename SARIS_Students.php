<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Students');
include(__DIR__ . '/includes/SARISIntegration.php');

$settings = saris_get_settings();

if (isset($_POST['SyncStudents'])) {
	$startDate = isset($_POST['StartDate']) ? $_POST['StartDate'] : '';
	$endDate = isset($_POST['EndDate']) ? $_POST['EndDate'] : '';
	if (!saris_validate_date_range($startDate, $endDate)) {
		prnMsg(__('Please enter a valid date range. End Date must be greater than or equal to Start Date.'), 'error');
	} else {
		$historyRunId = saris_sync_run_id();
		$historyStartedAt = date('Y-m-d H:i:s');
		$historyStartedTimer = microtime(true);
		try {
			$result = saris_full_sync_with_zerp(new SARISAPIClient(), $startDate, $endDate);
			$stats = $result['saris'];
			$message = __('Sync completed.') . ' ' . __('Invoices') . ': ' . $stats['invoices']
				. ', ' . __('Students') . ': ' . $stats['students']
				. ', ' . __('Payments') . ': ' . $stats['payments'] . '.';
			if ($result['zerp']['enabled']) {
				$message .= ' ' . __('ZERP posted') . ' — ' . __('Students') . ': ' . $result['zerp']['students_synced']
					. ', ' . __('Invoices') . ': ' . $result['zerp']['invoices_synced']
					. ', ' . __('Payments') . ': ' . $result['zerp']['payments_synced']
					. ', ' . __('Partial') . ': ' . $result['zerp']['partial']
					. ', ' . __('Failed') . ': ' . $result['zerp']['failed'] . '.';
			}
			$historyStatus = saris_sync_history_status($result['zerp']);
			saris_record_sync_history([
				'run_id' => $historyRunId,
				'trigger_type' => 'manual-full',
				'sync_status' => $historyStatus,
				'date_from' => $startDate,
				'date_to' => $endDate,
				'iterations' => 1,
				'saris' => $stats,
				'zerp' => $result['zerp'],
				'message' => $message,
				'started_at' => $historyStartedAt,
				'completed_at' => date('Y-m-d H:i:s'),
				'duration_seconds' => microtime(true) - $historyStartedTimer,
			]);
			prnMsg(
				__('Synchronization completed and was saved to history.')
				. ' <a href="SARIS_SyncHistory.php">' . __('View Sync History') . '</a>',
				$historyStatus === 'success' ? 'success' : 'warn'
			);
		} catch (Exception $e) {
			saris_record_sync_history([
				'run_id' => $historyRunId,
				'trigger_type' => 'manual-full',
				'sync_status' => 'error',
				'date_from' => $startDate,
				'date_to' => $endDate,
				'iterations' => 1,
				'error_summary' => [['record_type' => 'run', 'count' => 1, 'message' => $e->getMessage()]],
				'message' => $e->getMessage(),
				'started_at' => $historyStartedAt,
				'completed_at' => date('Y-m-d H:i:s'),
				'duration_seconds' => microtime(true) - $historyStartedTimer,
			]);
			prnMsg(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), 'error');
		}
	}
}

$searchTerm = isset($_GET['Search']) ? trim($_GET['Search']) : '';
$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$sort = isset($_GET['Sort']) ? $_GET['Sort'] : 'student_regnumber';
$dir = isset($_GET['Dir']) && strtoupper($_GET['Dir']) === 'DESC' ? 'DESC' : 'ASC';

$totalRows = saris_count_rows('students', $searchTerm);
$students = saris_list_students($perPage, $offset, $searchTerm, $sort, $dir);

$extraParams = $searchTerm !== '' ? 'Search=' . urlencode($searchTerm) : '';
if ($extraParams !== '') {
	$extraParams .= '&Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
} else {
	$extraParams = 'Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	ob_start();
	if (count($students) === 0) {
		echo '<tr><td colspan="14" style="text-align:center;padding:60px;color:#6b7280;">';
		echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
		echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
	} else {
		foreach ($students as $row) {
			echo '<tr>';
			echo '<td class="saris-checkbox-cell"><input type="checkbox" name="selected_ids[]" value="' . (int)$row['id'] . '"></td>';
			foreach (['id', 'student_regnumber', 'student_fullname', 'student_email', 'student_phone', 'student_programme', 'student_entryyear', 'student_studyyear', 'student_intake', 'sync_status', 'zerp_customer_code', 'sync_error', 'created_at'] as $key) {
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
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Students') . '</h1><p class="db-page-subtitle">' . __('Student records imported from SARIS') . '</p></div>';
saris_render_tabs('Students', 'Students', $searchTerm);

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


echo '<div class="db-card"><div class="saris-table-wrapper" style="overflow-x:auto;width:100%;-webkit-overflow-scrolling:touch;"><table class="saris-table"><thead><tr>';
$columns = [
	'ID' => 'id',
	'Reg Number' => 'student_regnumber',
	'Full Name' => 'student_fullname',
	'Email' => 'student_email',
	'Phone' => 'student_phone',
	'Programme' => 'student_programme',
	'Entry Year' => 'student_entryyear',
	'Study Year' => 'student_studyyear',
	'Intake' => 'student_intake',
	'Sync Status' => 'sync_status',
	'ZERP Customer' => 'zerp_customer_code',
	'Sync Error' => null,
	'Created At' => 'created_at'
];
echo '<th class="saris-checkbox-cell"><input type="checkbox" onclick="var checkboxes = document.querySelectorAll(\'.saris-table tbody input[type=checkbox]\'); for(var i=0; i<checkboxes.length; i++) checkboxes[i].checked = this.checked;"></th>';
foreach ($columns as $label => $col) {
	if ($col === null) {
		echo '<th>' . __($label) . '</th>';
	} else {
		saris_render_sort_header($label, $col, $sort, $dir);
	}
}
echo '</tr></thead><tbody>';
if (count($students) === 0) {
	echo '<tr><td colspan="14" style="text-align:center;padding:60px;color:#6b7280;">';
	echo '<svg style="width:64px;height:64px;margin:0 auto 16px;color:#d1d5db;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
	echo '<div style="font-size:16px;">' . __('No records found matching your search criteria.') . '</div></td></tr>';
} else {
	foreach ($students as $row) {
		echo '<tr>';
		echo '<td class="saris-checkbox-cell"><input type="checkbox" name="selected_ids[]" value="' . (int)$row['id'] . '"></td>';
		foreach (['id', 'student_regnumber', 'student_fullname', 'student_email', 'student_phone', 'student_programme', 'student_entryyear', 'student_studyyear', 'student_intake', 'sync_status', 'zerp_customer_code', 'sync_error', 'created_at'] as $key) {
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
