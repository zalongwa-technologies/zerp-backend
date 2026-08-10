<?php
// CLI diagnostic: show recent sync history and any records currently stuck
// in a failed/partial/processing state, with their stored error message.
// Usage: php saris_sync_status.php

$PathPrefix = __DIR__ . '/';
include($PathPrefix . 'config.php');

$AllowAnyone = true;
$PageSecurity = 15;
$_SESSION['DatabaseName'] = $DefaultDatabase;
$_SERVER['HTTP_HOST'] = $Host;
$_SERVER['REMOTE_ADDR'] = isset($REMOTE_ADDR) ? $REMOTE_ADDR : '127.0.0.1';

include($PathPrefix . 'includes/session.php');
include($PathPrefix . 'includes/SARISIntegration.php');

$pdo = saris_pdo();

echo "== Last 5 sync runs (saris_sync_log) ==\n";
$stmt = $pdo->query(
	"SELECT run_id, trigger_type, sync_status, date_from, date_to,
	        saris_students, zerp_students, zerp_failed, zerp_partial,
	        duration_seconds, message, error_summary, created_at
	 FROM saris_sync_log
	 ORDER BY id DESC
	 LIMIT 5"
);
foreach ($stmt->fetchAll() as $row) {
	echo "---\n";
	echo "run_id: {$row['run_id']}  trigger: {$row['trigger_type']}  status: {$row['sync_status']}  at: {$row['created_at']}\n";
	echo "range: {$row['date_from']} -> {$row['date_to']}  duration: {$row['duration_seconds']}s\n";
	echo "students found: {$row['saris_students']}  zerp students synced: {$row['zerp_students']}  failed: {$row['zerp_failed']}  partial: {$row['zerp_partial']}\n";
	echo "message: {$row['message']}\n";
	if (!empty($row['error_summary'])) {
		echo "error_summary: {$row['error_summary']}\n";
	}
}

foreach (['students', 'invoices', 'payments'] as $table) {
	echo "\n== {$table} currently failed/partial/processing ==\n";
	$stmt = $pdo->query(
		"SELECT id, sync_status, sync_attempts, sync_locked_at, sync_error
		 FROM `{$table}`
		 WHERE sync_status IN ('failed', 'partial', 'processing')
		 ORDER BY id DESC
		 LIMIT 20"
	);
	$rows = $stmt->fetchAll();
	if (!$rows) {
		echo "(none)\n";
		continue;
	}
	foreach ($rows as $row) {
		echo "id={$row['id']} status={$row['sync_status']} attempts={$row['sync_attempts']} locked_at={$row['sync_locked_at']}\n";
		if (!empty($row['sync_error'])) {
			echo "  error: {$row['sync_error']}\n";
		}
	}
}
