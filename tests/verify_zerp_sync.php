<?php

require_once(__DIR__ . '/../includes/ZERPSync.php');

function verify_zerp_sync($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "FAIL: " . $message . PHP_EOL);
		exit(1);
	}
}

verify_zerp_sync(ZERPSync::customerCode('STU123') === 'STU123', 'Short customer code changed.');

$customerCode = ZERPSync::customerCode('MUM/2026/000123');
verify_zerp_sync(strlen($customerCode) === 10, 'Generated customer code exceeds the ZERP schema.');
verify_zerp_sync(
	$customerCode === ZERPSync::customerCode('MUM/2026/000123'),
	'Generated customer code is not deterministic.'
);
verify_zerp_sync(
	$customerCode !== ZERPSync::customerCode('MUM/2026/000124'),
	'Different registration numbers generated the same test customer code.'
);

$sourceReference = str_repeat('INV-2026-', 10);
$remoteReference = ZERPSync::remoteReference($sourceReference);
verify_zerp_sync(strlen($remoteReference) === 50, 'Generated invoice reference exceeds debtortrans.reference.');
verify_zerp_sync(
	$remoteReference === ZERPSync::remoteReference($sourceReference),
	'Generated invoice reference is not deterministic.'
);

echo "SARIS-to-ZERP deterministic mapping checks passed." . PHP_EOL;

?>
