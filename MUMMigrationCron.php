<?php

// Check if running from CLI
$IsCommandLine = (php_sapi_name() === 'cli');
if (!$IsCommandLine) {
    die("This script can only be run from the command line.");
}

// Define variables necessary to bypass normal session login and auth
$AllowAnyone = true;
$PageSecurity = 15;
$_SESSION['DatabaseName'] = 'weberpdemo'; // Replace with your actual DB name if different
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// We need to set the PathPrefix because this is run from CLI
$PathPrefix = __DIR__ . '/';
include($PathPrefix . 'includes/session.php');
include($PathPrefix . 'includes/MUMAPIClient.php');
include($PathPrefix . 'includes/MUMSyncStudents.php');
include($PathPrefix . 'includes/MUMSyncInvoices.php');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

// Hardcoded for testing. User should update these credentials or load from config.
$clientId = 'YOUR_CLIENT_ID_HERE';
$clientSecret = 'YOUR_CLIENT_SECRET_HERE';

function ensureMUMSchemaCLI() {
    global $db;
    $columnsToCheck = [
        'student_programme' => 'VARCHAR(100) DEFAULT NULL',
        'student_entryyear' => 'VARCHAR(20) DEFAULT NULL',
        'student_studyyear' => 'INT DEFAULT NULL',
        'student_intake' => 'VARCHAR(50) DEFAULT NULL'
    ];
    foreach ($columnsToCheck as $col => $def) {
        $result = DB_query("SHOW COLUMNS FROM debtorsmaster LIKE '$col'", $db);
        if (DB_num_rows($result) == 0) {
            DB_query("ALTER TABLE debtorsmaster ADD COLUMN $col $def", $db);
        }
    }
    $result = DB_query("SHOW COLUMNS FROM debtortrans LIKE 'invoice_amount_type'", $db);
    if (DB_num_rows($result) == 0) {
        DB_query("ALTER TABLE debtortrans ADD COLUMN invoice_amount_type VARCHAR(20) DEFAULT NULL", $db);
    }
}

echo "Starting automated MUM Migration Cron...\n";
ensureMUMSchemaCLI();

$apiClient = new MUMAPIClient($clientId, $clientSecret);

// Fetch 'Yesterday's' data for the daily cron
$dateYesterday = date('Y-m-d', strtotime('-1 day'));
$params = [
    'invoice_date' => $dateYesterday
];

echo "Fetching invoices for date: $dateYesterday\n";

try {
    $response = $apiClient->getAllInvoices($params);
    if (isset($response['statusCode']) && $response['statusCode'] == 200 && isset($response['data']) && is_array($response['data'])) {
        $invoices = $response['data'];
        echo "Found " . count($invoices) . " invoices.\n";
        
        $uniqueStudents = [];
        foreach ($invoices as $inv) {
            $uniqueStudents[$inv['student_regnumber']] = true;
        }
        
        echo "Syncing " . count($uniqueStudents) . " students...\n";
        $studentCount = 0;
        foreach (array_keys($uniqueStudents) as $regNumber) {
            $res = syncStudentToZERP($apiClient, $regNumber);
            if ($res['status']) $studentCount++;
        }
        
        echo "Syncing invoices...\n";
        $invoiceCount = 0;
        foreach ($invoices as $inv) {
            $res = syncInvoiceToZERP($apiClient, $inv);
            if ($res['status']) $invoiceCount++;
        }
        
        echo "Cron Completed Successfully. Students synced: $studentCount, Invoices synced: $invoiceCount\n";
    } else {
        echo "No invoices found for $dateYesterday or API error.\n";
    }
} catch (Exception $e) {
    echo "Cron Error: " . $e->getMessage() . "\n";
    $apiClient->logError("Cron Execution Error: " . $e->getMessage());
}

?>
