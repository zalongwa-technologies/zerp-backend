<?php
// CLI runner for posting already-staged SARIS records into ZERP XML-RPC only.
//
// Usage:
//   php zerp_sync_cli.php

if (PHP_SAPI !== 'cli') {
	die("This script can only be run from the command line.\n");
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

saris_cli_log('Starting zerp-only sync');

try {
	$zerpStats = saris_run_zerp_sync();
	$message = 'ZERP sync completed.';
	if (!empty($zerpStats['enabled'])) {
		$message .= ' Students: ' . $zerpStats['students_synced']
			. '. Invoices: ' . $zerpStats['invoices_synced']
			. '. Payments: ' . $zerpStats['payments_synced']
			. '. Partial: ' . $zerpStats['partial']
			. '. Failed: ' . $zerpStats['failed'] . '.';
	}
	saris_cli_log($message);
	print_r($zerpStats);
	echo "\nDone.\n";
} catch (Throwable $e) {
	saris_cli_log('FAILED: ' . $e->getMessage());
	echo $e . "\n";
	exit(1);
}
