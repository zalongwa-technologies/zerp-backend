<?php
/* CSV format for NMB Bank (Tanzania) */

// Trim the line
$LineText = trim($LineText);
if (empty($LineText)) return;

// Skip header row if necessary
static $isFirstRowNMB = true;
if ($isFirstRowNMB) {
    $isFirstRowNMB = false;
    return; // Skip the first line assuming it's headers
}

$LineArray = str_getcsv($LineText);

// Verify this is a transaction line
// NOTE: These indices might need adjusting based on NMB's exact CSV layout.
$dateStr = isset($LineArray[0]) ? trim($LineArray[0]) : '';
$descStr = isset($LineArray[1]) ? trim($LineArray[1]) : '';
$amountStr = isset($LineArray[2]) ? trim($LineArray[2]) : '0';

// Clean up amount (remove commas)
$amount = doubleval(str_replace(',', '', $amountStr));

if ($dateStr != '' && $amount != 0) {
    $parsedDate = date('Y-m-d', strtotime(str_replace('/', '-', $dateStr)));
    
    $i++;
    $_SESSION['Trans'][$i] = new BankTrans($parsedDate, $amount);
    $_SESSION['Trans'][$i]->Description = $descStr;
}
?>
