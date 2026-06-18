<?php

if (php_sapi_name() !== 'cli') {
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

try {
	$settings = saris_get_settings();
	if ($settings['sync_mode'] !== 'automatic') {
		echo "SARIS automatic sync is disabled.\n";
		exit(0);
	}

	$startDate = saris_default_invoice_start_date();
	$endDate = date('Y-m-d');
	$result = saris_full_sync_with_zerp(new SARISAPIClient(), $startDate, $endDate);
	$stats = $result['saris'];
	$message = 'Automatic sync completed from ' . $startDate . ' to ' . $endDate . '. Invoices: ' . $stats['invoices'] . ', Students: ' . $stats['students'] . ', Payments: ' . $stats['payments'] . '.';
	if ($result['zerp']['enabled']) {
		$message .= ' ZERP posted — Students: ' . $result['zerp']['students_synced']
			. ', Invoices: ' . $result['zerp']['invoices_synced']
			. ', Payments: ' . $result['zerp']['payments_synced']
			. ', Partial: ' . $result['zerp']['partial']
			. ', Failed: ' . $result['zerp']['failed'] . '.';
	}
	saris_log_sync($message, 'success');
	echo $message . "\n";
} catch (Exception $e) {
	$message = 'Automatic sync failed: ' . $e->getMessage();
	saris_log_sync($message, 'error');
	echo $message . "\n";
	exit(1);
}

?>
