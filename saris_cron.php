<?php

// if (php_sapi_name() !== 'cli') {
// 	die("This script can only be run from the command line.\n");
// }

$PathPrefix = __DIR__ . '/';
include($PathPrefix . 'config.php');

$AllowAnyone = true;
$PageSecurity = 15;
$_SESSION['DatabaseName'] = $DefaultDatabase;
$_SERVER['HTTP_HOST'] = $Host;
$_SERVER['REMOTE_ADDR'] = isset($REMOTE_ADDR) ? $REMOTE_ADDR : '127.0.0.1';

include($PathPrefix . 'includes/session.php');
include($PathPrefix . 'includes/SARISIntegration.php');

if (PHP_SAPI === 'cli') {
	set_time_limit(0);
}

$historyRunId = saris_sync_run_id();
$historyStartedAt = date('Y-m-d H:i:s');
$historyStartedTimer = microtime(true);
$startDate = null;
$endDate = null;
$iterations = 0;
$stats = [];
$zerpStats = [];

try {
	$settings = saris_get_settings();
	if ($settings['sync_mode'] !== 'automatic') {
		echo "SARIS automatic sync is disabled.\n";
		exit(0);
	}

	$startDate = isset($SARIS_AUTO_SYNC_START_DATE) && saris_validate_date_range(
		$SARIS_AUTO_SYNC_START_DATE,
		date('Y-m-d')
	)
		? $SARIS_AUTO_SYNC_START_DATE
		: '2025-10-01';
	$endDate = date('Y-m-d');
	$sarisClient = new SARISAPIClient();
	$stats = [
		'invoices' => 0,
		'students' => 0,
		'students_skipped' => 0,
		'payments' => 0,
	];
	$iterations = 0;
	$currentDate = new DateTimeImmutable($startDate);
	$lastDate = new DateTimeImmutable($endDate);

	while ($currentDate < $lastDate) {
		$nextDate = $currentDate->modify('+1 day');
		$dailyStats = saris_full_sync(
			$sarisClient,
			$currentDate->format('Y-m-d'),
			$nextDate->format('Y-m-d')
		);
		foreach ($stats as $key => $value) {
			$stats[$key] += isset($dailyStats[$key]) ? (int)$dailyStats[$key] : 0;
		}
		$iterations++;
		$currentDate = $nextDate;
	}

	$zerpStats = saris_run_zerp_sync();
	$message = 'Automatic sync completed in ' . $iterations . ' daily iteration(s) from '
		. $startDate . ' to ' . $endDate . '. Invoices: ' . $stats['invoices']
		. ', Students: ' . $stats['students'] . ', Payments: ' . $stats['payments'] . '.';
	if ($zerpStats['enabled']) {
		$message .= ' ZERP posted — Students: ' . $zerpStats['students_synced']
			. ', Invoices: ' . $zerpStats['invoices_synced']
			. ', Payments: ' . $zerpStats['payments_synced']
			. ', Partial: ' . $zerpStats['partial']
			. ', Failed: ' . $zerpStats['failed'] . '.';
		if (!empty($zerpStats['error_summary'])) {
			$errors = [];
			foreach ($zerpStats['error_summary'] as $error) {
				$errors[] = ucfirst($error['record_type']) . ' (' . $error['count'] . '): ' . $error['message'];
			}
			$message .= ' Errors: ' . implode(' | ', $errors);
		}
	}
	saris_record_sync_history([
		'run_id' => $historyRunId,
		'trigger_type' => 'automatic',
		'sync_status' => saris_sync_history_status($zerpStats),
		'date_from' => $startDate,
		'date_to' => $endDate,
		'iterations' => $iterations,
		'saris' => $stats,
		'zerp' => $zerpStats,
		'message' => $message,
		'started_at' => $historyStartedAt,
		'completed_at' => date('Y-m-d H:i:s'),
		'duration_seconds' => microtime(true) - $historyStartedTimer,
	]);
	echo $message . "\n";
} catch (Exception $e) {
	$message = 'Automatic sync failed: ' . $e->getMessage();
	saris_record_sync_history([
		'run_id' => $historyRunId,
		'trigger_type' => 'automatic',
		'sync_status' => 'error',
		'date_from' => $startDate,
		'date_to' => $endDate,
		'iterations' => $iterations,
		'saris' => $stats,
		'zerp' => $zerpStats,
		'error_summary' => [['record_type' => 'run', 'count' => 1, 'message' => $e->getMessage()]],
		'message' => $message,
		'started_at' => $historyStartedAt,
		'completed_at' => date('Y-m-d H:i:s'),
		'duration_seconds' => microtime(true) - $historyStartedTimer,
	]);
	echo $message . "\n";
	exit(1);
}

?>
