<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Sync History');
include(__DIR__ . '/includes/SARISIntegration.php');

function saris_history_escape($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function saris_history_error_text($row) {
	if (empty($row['error_summary'])) {
		return '';
	}
	$errors = json_decode($row['error_summary'], true);
	if (!is_array($errors)) {
		return (string)$row['error_summary'];
	}
	$messages = [];
	foreach ($errors as $error) {
		if (!is_array($error)) {
			continue;
		}
		$type = isset($error['record_type']) ? ucfirst((string)$error['record_type']) : __('Error');
		$count = isset($error['count']) ? ' (' . (int)$error['count'] . ')' : '';
		$message = isset($error['message']) ? (string)$error['message'] : '';
		$messages[] = $type . $count . ($message !== '' ? ': ' . $message : '');
	}
	return implode(' | ', $messages);
}

function saris_render_history_rows($history) {
	if (count($history) === 0) {
		echo '<tr><td colspan="12" style="text-align:center;padding:60px;color:#6b7280;">'
			. __('No synchronization history was found.') . '</td></tr>';
		return;
	}

	foreach ($history as $row) {
		$status = strtolower((string)$row['sync_status']);
		$statusStyles = [
			'success' => 'background:#dcfce7;color:#166534;',
			'partial' => 'background:#fef3c7;color:#92400e;',
			'failed' => 'background:#fee2e2;color:#991b1b;',
			'error' => 'background:#fee2e2;color:#991b1b;',
		];
		$statusStyle = $statusStyles[$status] ?? 'background:#f1f5f9;color:#475569;';
		$dateRange = ($row['date_from'] ?: '—') . ' → ' . ($row['date_to'] ?: '—');
		$errorText = saris_history_error_text($row);
		$message = $errorText !== '' ? $errorText : (string)$row['message'];
		$duration = $row['duration_seconds'] === null
			? '—'
			: number_format((float)$row['duration_seconds'], 3) . 's';

		echo '<tr>';
		echo '<td>' . (int)$row['id'] . '</td>';
		echo '<td style="white-space:nowrap;">' . saris_history_escape($row['created_at']) . '</td>';
		echo '<td><div>' . saris_history_escape($row['trigger_type']) . '</div>'
			. '<small title="' . saris_history_escape($row['run_id']) . '">'
			. saris_history_escape(substr((string)$row['run_id'], 0, 8)) . '</small></td>';
		echo '<td><span style="display:inline-block;padding:4px 9px;border-radius:999px;font-weight:700;'
			. $statusStyle . '">' . saris_history_escape(ucfirst($status)) . '</span></td>';
		echo '<td style="white-space:nowrap;">' . saris_history_escape($dateRange)
			. '<br><small>' . (int)$row['iterations'] . ' ' . __('iteration(s)') . '</small></td>';
		echo '<td><strong>I:</strong> ' . (int)$row['saris_invoices']
			. '<br><strong>S:</strong> ' . (int)$row['saris_students']
			. '<br><strong>P:</strong> ' . (int)$row['saris_payments'] . '</td>';
		echo '<td><strong>S:</strong> ' . (int)$row['zerp_students']
			. '<br><strong>I:</strong> ' . (int)$row['zerp_invoices']
			. '<br><strong>P:</strong> ' . (int)$row['zerp_payments'] . '</td>';
		echo '<td>' . (int)$row['zerp_partial'] . '</td>';
		echo '<td>' . (int)$row['zerp_failed'] . '</td>';
		echo '<td>' . (int)$row['zerp_skipped'] . '</td>';
		echo '<td style="white-space:nowrap;">' . saris_history_escape($duration) . '</td>';
		echo '<td style="min-width:320px;white-space:normal;">' . saris_history_escape($message ?: '—') . '</td>';
		echo '</tr>';
	}
}

$searchTerm = isset($_GET['Search']) ? trim($_GET['Search']) : '';
$page = isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$sort = isset($_GET['Sort']) ? $_GET['Sort'] : 'created_at';
$dir = isset($_GET['Dir']) && strtoupper($_GET['Dir']) === 'ASC' ? 'ASC' : 'DESC';

$totalRows = saris_count_sync_history($searchTerm);
$history = saris_list_sync_history($perPage, $offset, $searchTerm, $sort, $dir);
$extraParams = 'Sort=' . urlencode($sort) . '&Dir=' . urlencode($dir);
if ($searchTerm !== '') {
	$extraParams = 'Search=' . urlencode($searchTerm) . '&' . $extraParams;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	ob_start();
	saris_render_history_rows($history);
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
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('Synchronization History') . '</h1>'
	. '<p class="db-page-subtitle">' . __('Permanent statistics for manual and automatic SARIS-to-ZERP runs') . '</p></div>';
saris_render_tabs('Sync History', 'History', $searchTerm);

echo '<div class="db-card"><div class="db-table-wrapper" style="overflow-x:auto;width:100%;'
	. '-webkit-overflow-scrolling:touch;max-height:70vh;"><table class="db-table"><thead><tr>';
$columns = [
	'ID' => 'id',
	'Recorded At' => 'created_at',
	'Trigger / Run' => 'trigger_type',
	'Status' => 'sync_status',
	'Date Range' => 'date_from',
	'SARIS I / S / P' => null,
	'ZERP S / I / P' => null,
	'Partial' => 'zerp_partial',
	'Failed' => 'zerp_failed',
	'Skipped' => null,
	'Duration' => 'duration_seconds',
	'Message / Errors' => null,
];
foreach ($columns as $label => $column) {
	if ($column === null) {
		echo '<th>' . __($label) . '</th>';
	} else {
		saris_render_sort_header($label, $column, $sort, $dir);
	}
}
echo '</tr></thead><tbody>';
saris_render_history_rows($history);
echo '</tbody></table></div></div>';
saris_render_pagination($totalRows, $page, $perPage, $_SERVER['PHP_SELF'], $extraParams);
echo '</div>';

saris_render_scripts($_SERVER['PHP_SELF']);
include(__DIR__ . '/includes/footer.php');
?>
