<?php

$PageSecurity = 15; // Setup security level
include('includes/session.php');
$Title = _('MUM API Data Migration');
include('includes/header.php');

include('includes/MUMAPIClient.php');
include('includes/MUMSyncStudents.php');
include('includes/MUMSyncInvoices.php');
include('includes/SQL_CommonFunctions.inc'); // For GetNextTransNo, GetPeriod

function getSARISAPICredentialsFromConfig($configFile) {
    $SARIS_API_CLIENT_ID = '';
    $SARIS_API_CLIENT_SECRET = '';

    if (file_exists(__DIR__ . '/config.php')) {
        include(__DIR__ . '/config.php');
    } elseif (file_exists(__DIR__ . '/' . $configFile)) {
        include(__DIR__ . '/' . $configFile);
    }

    return [
        'client_id' => $SARIS_API_CLIENT_ID,
        'client_secret' => $SARIS_API_CLIENT_SECRET
    ];
}

// Modern ZERP Styles
echo '<style>
    .migration-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); padding: 24px; margin: 20px auto; max-width: 800px; }
    .stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; text-align: center; flex: 1; margin: 0 10px; }
    .stat-number { font-size: 24px; font-weight: bold; color: #059669; }
    .stat-label { font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
    .stats-container { display: flex; justify-content: space-between; margin-top: 20px; }
    .db-btn-primary { background: #059669; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; transition: background 0.2s; }
    .db-btn-primary:hover { background: #047857; }
</style>';

echo '<div class="migration-card">';
echo '<h2 style="color: #1e293b; margin-top: 0; display: flex; align-items: center;"><i class="fas fa-sync-alt" style="margin-right: 12px; color: #059669;"></i> ' . $Title . '</h2>';
echo '<p style="color: #64748b;">Pull historical data from the MUM API safely into ZERP. Use small date ranges (1 month) to avoid timeouts.</p>';

$sarisAPICredentials = getSARISAPICredentialsFromConfig('config.distrib.php');
$clientId = $sarisAPICredentials['client_id'];
$clientSecret = $sarisAPICredentials['client_secret'];

function ensureMUMSchema() {
    global $db;
    
    // Check and add columns for debtorsmaster
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
    
    // Check and add columns for debtortrans
    $result = DB_query("SHOW COLUMNS FROM debtortrans LIKE 'invoice_amount_type'", $db);
    if (DB_num_rows($result) == 0) {
        DB_query("ALTER TABLE debtortrans ADD COLUMN invoice_amount_type VARCHAR(20) DEFAULT NULL", $db);
    }
}

// Ensure schema is updated before any operation
ensureMUMSchema();

if (isset($_POST['ProcessMigration'])) {
    $dateFrom = $_POST['DateFrom'];
    $dateTo = $_POST['DateTo'];

    if (!Is_Date($dateFrom) || !Is_Date($dateTo)) {
        prnMsg(_('Invalid date format entered.'), 'error');
    } else {
        $apiClient = new MUMAPIClient($clientId, $clientSecret);
        
        $stats = [
            'students_inserted' => 0,
            'students_updated' => 0,
            'invoices_inserted' => 0,
            'invoices_skipped' => 0,
            'errors' => 0
        ];

        try {
            $params = [
                'invoice_date_from' => FormatDateForSQL($dateFrom),
                'invoice_date_to' => FormatDateForSQL($dateTo)
            ];
            
            prnMsg("Fetching invoices from API...", 'info');
            $response = $apiClient->getAllInvoices($params);
            
            if (isset($response['statusCode']) && $response['statusCode'] == 200 && isset($response['data']) && is_array($response['data'])) {
                $invoices = $response['data'];
                
                // Extract unique students
                $uniqueStudents = [];
                foreach ($invoices as $inv) {
                    $uniqueStudents[$inv['student_regnumber']] = true;
                }
                
                // Sync Students First
                foreach (array_keys($uniqueStudents) as $regNumber) {
                    $res = syncStudentToZERP($apiClient, $regNumber);
                    if ($res['status']) {
                        if ($res['action'] == 'inserted') $stats['students_inserted']++;
                        if ($res['action'] == 'updated') $stats['students_updated']++;
                    } else {
                        $stats['errors']++;
                    }
                }
                
                // Sync Invoices
                foreach ($invoices as $inv) {
                    $res = syncInvoiceToZERP($apiClient, $inv);
                    if ($res['status']) {
                        if ($res['action'] == 'inserted') $stats['invoices_inserted']++;
                        if ($res['action'] == 'skipped') $stats['invoices_skipped']++;
                    } else {
                        $stats['errors']++;
                    }
                }
                
                prnMsg("Migration completed for the selected date range. Please review the statistics below.", 'success');
                
                // Show Stats
                echo '<div class="stats-container">';
                echo '<div class="stat-box"><div class="stat-number">' . $stats['students_inserted'] . '</div><div class="stat-label">New Students</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $stats['students_updated'] . '</div><div class="stat-label">Updated Students</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $stats['invoices_inserted'] . '</div><div class="stat-label">New Invoices</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $stats['invoices_skipped'] . '</div><div class="stat-label">Skipped (Exists)</div></div>';
                echo '<div class="stat-box"><div class="stat-number" style="color: #dc2626;">' . $stats['errors'] . '</div><div class="stat-label">Errors</div></div>';
                echo '</div><br/>';
                if ($stats['errors'] > 0) {
                    prnMsg("Some items failed to sync. Please check api_migration_error.log for details.", 'warn');
                }

            } else {
                prnMsg("Failed to fetch invoices or no invoices found.", 'warn');
            }
        } catch (Exception $e) {
            prnMsg("Error during migration: " . $e->getMessage(), 'error');
        }
    }
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection" style="width: 100%; max-width: 500px; margin: 0 auto; border: none;">';
echo '<tr><td style="padding: 10px;">' . _('Date From') . ':</td>
      <td style="padding: 10px;"><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DateFrom" required="required" value="' . date($_SESSION['DefaultDateFormat'], strtotime('-1 month')) . '" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;" /></td></tr>';
echo '<tr><td style="padding: 10px;">' . _('Date To') . ':</td>
      <td style="padding: 10px;"><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DateTo" required="required" value="' . date($_SESSION['DefaultDateFormat']) . '" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;" /></td></tr>';
echo '</table><br />';

echo '<div class="centre" style="margin-top: 20px;">
        <button type="submit" name="ProcessMigration" class="db-btn-primary"><i class="fas fa-play"></i> ' . _('Start Migration') . '</button>
      </div>';

echo '</form>';
echo '</div>'; // end migration-card

include('includes/footer.php');
?>
