<?php
// CLI runner for an on-demand SARIS sync, mirroring the "Sync Students" /
// "Sync Payments" buttons but run from the terminal so it isn't bound by
// browser/web-server request timeouts, and prints live progress + errors
// as they happen (see saris_cli_log() in includes/SARISIntegration.php).
//
// Usage:
//   php saris_sync_cli.php --from=2026-07-01 --to=2026-07-01
//   php saris_sync_cli.php --from=2026-07-01 --to=2026-07-01 --stage=payments
//
// --stage=full     (default) invoices + students + payments + ZERP posting,
//                  same as the "Sync Students" button (saris_full_sync_with_zerp).
// --stage=payments payments only + ZERP posting, same as the "Sync Payments"
//                  button (saris_sync_payments + saris_run_zerp_sync).

if (PHP_SAPI !== 'cli') {
	die("This script can only be run from the command line.\n");
}

$options = getopt('', ['from:', 'to:', 'stage::']);
$startDate = $options['from'] ?? null;
$endDate = $options['to'] ?? null;
$stage = $options['stage'] ?? 'full';

if (!$startDate || !$endDate || !in_array($stage, ['full', 'payments'], true)) {
	echo "Usage: php saris_sync_cli.php --from=YYYY-MM-DD --to=YYYY-MM-DD [--stage=full|payments]\n";
	exit(1);
}

$PathPrefix = __DIR__ . '/';
include($PathPrefix . 'config.php');

$AllowAnyone = true;
$PageSecurity = 15;
$_SESSION['DatabaseName'] = $DefaultDatabase;
$_SERVER['HTTP_HOST'] = $Host;
$_SERVER['REMOTE_ADDR'] = isset($REMOTE_ADDR) ? $REMOTE_ADDR : '127.0.0.1';

include($PathPrefix . 'includes/session.php');
include($PathPrefix . 'includes/SARISIntegration.php');

set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);

if (!saris_validate_date_range($startDate, $endDate)) {
	echo "Invalid date range. Use YYYY-MM-DD for both --from and --to, with --to >= --from.\n";
	exit(1);
}

saris_cli_log("Starting manual-cli-{$stage} sync: {$startDate} -> {$endDate}");

$historyRunId = saris_sync_run_id();
$historyStartedAt = date('Y-m-d H:i:s');
$historyStartedTimer = microtime(true);

try {
	if ($stage === 'payments') {
		$count = saris_sync_payments(new SARISAPIClient(), $startDate, $endDate);
		$zerpStats = saris_run_zerp_sync();
		$sarisStats = ['payments' => $count];
	} else {
		$result = saris_full_sync_with_zerp(new SARISAPIClient(), $startDate, $endDate);
		$sarisStats = $result['saris'];
		$zerpStats = $result['zerp'];
	}

	$message = 'Sync completed.';
	foreach ($sarisStats as $key => $value) {
		$message .= ' ' . ucfirst($key) . ': ' . $value . '.';
	}
	if ($zerpStats['enabled']) {
		$message .= ' ZERP posted — Students: ' . $zerpStats['students_synced']
			. ', Invoices: ' . $zerpStats['invoices_synced']
			. ', Payments: ' . $zerpStats['payments_synced']
			. ', Partial: ' . $zerpStats['partial']
			. ', Failed: ' . $zerpStats['failed'] . '.';
	}

	$historyStatus = saris_sync_history_status($zerpStats);
	saris_record_sync_history([
		'run_id' => $historyRunId,
		'trigger_type' => 'manual-cli-' . $stage,
		'sync_status' => $historyStatus,
		'date_from' => $startDate,
		'date_to' => $endDate,
		'iterations' => 1,
		'saris' => $sarisStats,
		'zerp' => $zerpStats,
		'message' => $message,
		'started_at' => $historyStartedAt,
		'completed_at' => date('Y-m-d H:i:s'),
		'duration_seconds' => microtime(true) - $historyStartedTimer,
	]);

	saris_cli_log($message);
	echo "\nDone.\n";
} catch (Throwable $e) {
	$message = $e->getMessage();
	saris_record_sync_history([
		'run_id' => $historyRunId,
		'trigger_type' => 'manual-cli-' . $stage,
		'sync_status' => 'error',
		'date_from' => $startDate,
		'date_to' => $endDate,
		'iterations' => 1,
		'error_summary' => [['record_type' => 'run', 'count' => 1, 'message' => $message]],
		'message' => $message,
		'started_at' => $historyStartedAt,
		'completed_at' => date('Y-m-d H:i:s'),
		'duration_seconds' => microtime(true) - $historyStartedTimer,
	]);
	saris_cli_log('FAILED: ' . $message);
	echo $e . "\n";
	exit(1);
}
