<?php

if (!isset($IsIncluded)) {
	require(__DIR__ . '/includes/session.php');
}
use Dompdf\Dompdf;
include_once(__DIR__ . '/includes/SetDomPDFOptions.php');
include_once(__DIR__ . '/includes/SQL_CommonFunctions.php');
include_once(__DIR__ . '/includes/AccountSectionsDef.php');
include_once(__DIR__ . '/includes/CurrenciesArray.php');

$Title = __('Balance Sheet');
$Title2 = __('Statement of Financial Position');
$ViewTopic = 'GeneralLedger';
$BookMark = 'BalanceSheet';

if (isset($_GET['PeriodTo'])) $_POST['PeriodTo'] = $_GET['PeriodTo'];
if (isset($_GET['ShowDetail'])) $_POST['ShowDetail'] = $_GET['ShowDetail'];
if (isset($_GET['ShowZeroBalance'])) $_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];

if (isset($_POST['PrintPDF']) or isset($_POST['View']) or isset($_POST['Spreadsheet'])) {
	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'] ?? 0;
	$DecimalPlaces = isset($_SESSION['CompanyRecord']['decimalplaces']) ? (int)$_SESSION['CompanyRecord']['decimalplaces'] : 2;
	$PeriodTo = isset($_POST['PeriodTo']) ? (int)$_POST['PeriodTo'] : 0;
	$ShowDetail = $_POST['ShowDetail'] ?? 'Detailed';
	
	$ThisYearSQLDate = EndDateSQLFromPeriodNo($PeriodTo);
	$BalanceDate = !empty($ThisYearSQLDate) ? ConvertSQLDate($ThisYearSQLDate) : '';
	
	$LastYearPeriod = $PeriodTo - 12;
	$LastYearSQLDate = EndDateSQLFromPeriodNo($LastYearPeriod);
	$LastYearBalanceDate = !empty($LastYearSQLDate) ? ConvertSQLDate($LastYearSQLDate) : '';
	
	$ThisYearTimestamp = !empty($ThisYearSQLDate) ? strtotime($ThisYearSQLDate) : false;
	$ThisYear = ($ThisYearTimestamp !== false) ? (int)date('Y', $ThisYearTimestamp) : (int)date('Y');
	
	$LastYearTimestamp = !empty($LastYearSQLDate) ? strtotime($LastYearSQLDate) : false;
	$LastYear = ($LastYearTimestamp !== false) ? (int)date('Y', $LastYearTimestamp) : ($ThisYear - 1);

	$ThisYearRetainedEarningsRow = DB_fetch_array(DB_query("SELECT ROUND(SUM(amount), " . $DecimalPlaces . " +1) AS retainedearnings FROM gltrans INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname WHERE periodno<='" . $PeriodTo . "' AND pandl=1"));
	$LastYearRetainedEarningsRow = DB_fetch_array(DB_query("SELECT ROUND(SUM(amount), " . $DecimalPlaces . " +1) AS retainedearnings FROM gltrans INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname WHERE periodno<='" . $LastYearPeriod . "' AND pandl=1"));

	$AccountListResult = DB_query("SELECT sectionid, sectionname, sectioninaccounts, parentgroupname, chartmaster.accountcode, group_, accountname, pandl FROM chartmaster INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1 INNER JOIN accountgroups ON accountgroups.groupname=chartmaster.group_ INNER JOIN accountsection ON accountsection.sectionid=accountgroups.sectioninaccounts WHERE pandl=0 ORDER BY sequenceintb, group_, accountcode");
	
    $ResultActual = DB_query("SELECT account, ROUND(SUM(amount), " . $DecimalPlaces . " +1) AS accounttotal FROM gltrans WHERE periodno<='" . $PeriodTo . "' GROUP BY account");
	while ($R = DB_fetch_array($ResultActual)) $ThisYearActuals[$R['account']] = $R['accounttotal'];
	$ResultLY = DB_query("SELECT account, ROUND(SUM(amount), " . $DecimalPlaces . " +1) AS accounttotal FROM gltrans WHERE periodno<='" . $LastYearPeriod . "' GROUP BY account");
	while ($R = DB_fetch_array($ResultLY)) $LastYearActuals[$R['account']] = $R['accounttotal'];

	$HTML = '';
	if (isset($_POST['PrintPDF'])) { 
        $HTML .= '<html><head><link href="css/reports.css" rel="stylesheet" type="text/css" />'; 
        $HTML .= '<style>
			body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #ffffff; color: #334155; margin: 0; padding: 0; }
			.report-header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #059669; }
			.report-header h2 { margin: 0; font-size: 18pt; color: #0f172a; font-weight: bold; letter-spacing: 0.5px; }
			.report-header h3 { margin: 8px 0 4px 0; font-size: 11pt; color: #059669; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; }
			.report-header p { margin: 0; color: #64748b; font-size: 10pt; }
			.db-table-wrap { width: 100%; margin-top: 10px; }
			.monochromatic-table { width: 100%; border-collapse: collapse; text-align: left; }
			.monochromatic-table th { 
				background-color: #059669; 
				color: #ffffff; 
				padding: 10px 12px; 
				font-weight: bold; 
				font-size: 9pt; 
				text-transform: uppercase;
				letter-spacing: 0.5px;
				border: none;
			}
			.monochromatic-table td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 9pt; color: #334155; }
			.monochromatic-table tr:nth-child(even) td { background-color: #f8fafc; }
			.monochromatic-table .number { text-align: right; }
			.total_row td { font-weight: bold; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; color: #0f172a; background-color: #f1f5f9 !important; font-size: 9.5pt; }
			.check_totals_row td { background-color: #ecfdf5 !important; color: #064e3b; font-size: 10pt; border-top: 2px solid #10b981; border-bottom: 3px double #10b981; }
            .section_row td { font-weight: bold; background-color: #d1fae5 !important; color: #065f46; font-size: 10pt; text-transform: uppercase; border-bottom: 2px solid #10b981; }
		</style>';
		$HTML .= '</head><body>';
		
		$HTML .= '<div class="report-header">
					<h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2>
					<h3>' . $Title . '</h3>
					<p>' . __('As at') . ' ' . date('d M Y') . '</p>
				  </div>';
    } else {
		$HTML .= '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
		$HTML .= '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		$HTML .= '<input type="hidden" name="PeriodTo" value="' . $PeriodTo . '" />';
        $HTML .= '<input type="hidden" name="ShowDetail" value="' . $ShowDetail . '" />';
        if (!empty($_POST['ShowZeroBalance'])) {
            $HTML .= '<input type="hidden" name="ShowZeroBalance" value="1" />';
        }
    }

    if (!function_exists('formatMoneyBS')) {
        function formatMoneyBS($amount) {
            if (abs($amount) < 0.005) return locale_number_format(0, 2);
            return locale_number_format((float)$amount, 2);
        }
    }

    $bsData = [
        'assets' => [
            'non_current' => [],
            'current' => []
        ],
        'equity' => [
            'none' => []
        ],
        'liabilities' => [
            'non_current' => [],
            'current' => []
        ]
    ];

    $retainedEarningsProcessed = false;
	while ($MyRow = DB_fetch_array($AccountListResult)) {
		$AccountBalance = $ThisYearActuals[$MyRow['accountcode']] ?? 0;
		$AccountBalanceLY = $LastYearActuals[$MyRow['accountcode']] ?? 0;
		if ($MyRow['accountcode'] == $RetainedEarningsAct) { 
            $AccountBalance += $ThisYearRetainedEarningsRow['retainedearnings']; 
            $AccountBalanceLY += $LastYearRetainedEarningsRow['retainedearnings']; 
            $retainedEarningsProcessed = true;
        }

        $sectionId = $MyRow['sectionid'];
        $type = '';
        $sub = '';
        
        if ($sectionId == 10) { // Fixed Assets
            $type = 'assets';
            $sub = 'non_current';
        } elseif ($sectionId == 15 || $sectionId == 20 || $sectionId == 25) { // Inventory, Current Assets, Cash & Equivalents
            $type = 'assets';
            $sub = 'current';
        } elseif ($sectionId == 50) { // Equity
            $type = 'equity';
            $sub = 'none';
        } elseif ($sectionId == 35) { // Long Term Liabilities
            $type = 'liabilities';
            $sub = 'non_current';
        } elseif ($sectionId == 30) { // Current Liabilities
            $type = 'liabilities';
            $sub = 'current';
        }
        
        if ($type != '') {
            if ($ShowDetail == 'Detailed') {
                $key = $MyRow['accountcode'];
                $label = $MyRow['accountcode'] . ' - ' . $MyRow['accountname'];
            } else {
                $key = $MyRow['group_'];
                $label = $MyRow['group_'];
            }
            
            if (!isset($bsData[$type][$sub][$key])) {
                $bsData[$type][$sub][$key] = [
                    'label' => $label,
                    'amount' => 0.0,
                    'ly_amount' => 0.0
                ];
            }
            
            if ($type === 'assets') {
                $bsData[$type][$sub][$key]['amount'] += $AccountBalance;
                $bsData[$type][$sub][$key]['ly_amount'] += $AccountBalanceLY;
            } else {
                $bsData[$type][$sub][$key]['amount'] -= $AccountBalance;
                $bsData[$type][$sub][$key]['ly_amount'] -= $AccountBalanceLY;
            }
        }
    }

    // Defensive injection: If Retained Earnings was skipped by the loop query (e.g. pandl config or permissions)
    // we must manually roll up the P&L into Equity for the Balance Sheet to balance.
    if (!$retainedEarningsProcessed) {
        $type = 'equity';
        $sub = 'none';
        $key = 'retained_earnings_fallback';
        $label = __('Retained Earnings');
        if ($ShowDetail == 'Detailed') {
            $key = $RetainedEarningsAct;
            $label = $RetainedEarningsAct . ' - ' . __('Retained Earnings');
        }
        
        if (!isset($bsData[$type][$sub][$key])) {
            $bsData[$type][$sub][$key] = [
                'label' => $label,
                'amount' => 0.0,
                'ly_amount' => 0.0
            ];
        }
        
        $AccountBalance = $ThisYearActuals[$RetainedEarningsAct] ?? 0;
        $AccountBalance += $ThisYearRetainedEarningsRow['retainedearnings'];
        $bsData[$type][$sub][$key]['amount'] -= $AccountBalance;

        $AccountBalanceLY = $LastYearActuals[$RetainedEarningsAct] ?? 0;
        $AccountBalanceLY += $LastYearRetainedEarningsRow['retainedearnings'];
        $bsData[$type][$sub][$key]['ly_amount'] -= $AccountBalanceLY;
    }

    // CALCULATIONS
    $totalNonCurrentAssets = array_sum(array_column($bsData['assets']['non_current'], 'amount'));
    $totalNonCurrentAssetsLY = array_sum(array_column($bsData['assets']['non_current'], 'ly_amount'));
    $totalCurrentAssets = array_sum(array_column($bsData['assets']['current'], 'amount'));
    $totalCurrentAssetsLY = array_sum(array_column($bsData['assets']['current'], 'ly_amount'));
    $totalAssets = $totalNonCurrentAssets + $totalCurrentAssets;
    $totalAssetsLY = $totalNonCurrentAssetsLY + $totalCurrentAssetsLY;

    $totalEquity = array_sum(array_column($bsData['equity']['none'], 'amount'));
    $totalEquityLY = array_sum(array_column($bsData['equity']['none'], 'ly_amount'));

    $totalNonCurrentLiabilities = array_sum(array_column($bsData['liabilities']['non_current'], 'amount'));
    $totalNonCurrentLiabilitiesLY = array_sum(array_column($bsData['liabilities']['non_current'], 'ly_amount'));
    $totalCurrentLiabilities = array_sum(array_column($bsData['liabilities']['current'], 'amount'));
    $totalCurrentLiabilitiesLY = array_sum(array_column($bsData['liabilities']['current'], 'ly_amount'));
    $totalLiabilities = $totalNonCurrentLiabilities + $totalCurrentLiabilities;
    $totalLiabilitiesLY = $totalNonCurrentLiabilitiesLY + $totalCurrentLiabilitiesLY;

    $totalEquityAndLiabilities = $totalEquity + $totalLiabilities;
    $totalEquityAndLiabilitiesLY = $totalEquityLY + $totalLiabilitiesLY;
    $balanceDiff = abs($totalAssets - $totalEquityAndLiabilities);
    $balanceDiffLY = abs($totalAssetsLY - $totalEquityAndLiabilitiesLY);

    // RENDERING
    //demo
    $HTML = '';
    
    if (isset($_POST['PrintPDF'])) {
        $HTML .= '<!DOCTYPE html><html><head><style>';
    } else {
        $HTML .= '<style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");';
    }

    $HTML .= '
        .balance-sheet-report { table-layout: fixed; width: 100% !important; margin: 0 auto; border-collapse: separate; border-spacing: 0; font-family: "Inter", Helvetica, sans-serif; background: transparent; }
        .balance-sheet-report th, .balance-sheet-report td { white-space: normal !important; word-wrap: break-word; }
        .report-title { text-align: center; padding: 15px 0 20px; border: none !important; line-height: 1.3; }
        .section-header { font-weight: 700; font-size: 0.95rem; padding: 10px 12px; text-transform: uppercase; background-color: #d1fae5 !important; color: #065f46; border-bottom: 2px solid #10b981; margin-top: 15px; }
        .subsection-header { font-weight: 600; padding: 8px 0 6px 12px; color: #374151; font-size: 0.875rem; border-bottom: 1px solid #cbd5e1; }
        .item-label { padding: 8px 0 8px 12px; color: #111827; font-size: 0.825rem; border: none !important; font-weight: 400; border-bottom: 1px solid #e5e7eb !important; }
        .amount-col1 { text-align: right; padding: 8px 12px 8px 0; color: #111827; font-size: 0.825rem; border: none !important; font-weight: 500; font-variant-numeric: tabular-nums; border-bottom: 1px solid #e5e7eb !important; }
        .amount-col2 { text-align: right; padding: 8px 12px 8px 0; color: #6b7280; font-size: 0.825rem; border: none !important; font-weight: 500; font-variant-numeric: tabular-nums; border-bottom: 1px solid #e5e7eb !important; }
        .item-row { transition: all 0.2s ease; cursor: default; }
        .item-row:nth-child(even) td { background-color: #f9fafb !important; }
        .item-row:hover td { background-color: #f3f4f6 !important; }
        .total-label { padding: 10px 0 10px 12px; font-weight: 700; color: #111827; border: none !important; font-size: 0.875rem; border-top: 1.5px solid #cbd5e1 !important; }
        .total-amount-col1 { text-align: right; padding: 10px 12px 10px 0; font-weight: 700; color: #111827; border: none !important; font-size: 0.875rem; border-top: 1.5px solid #cbd5e1 !important; font-variant-numeric: tabular-nums; }
        .total-amount-col2 { text-align: right; padding: 10px 12px 10px 0; font-weight: 700; color: #6b7280; border: none !important; font-size: 0.875rem; border-top: 1.5px solid #cbd5e1 !important; font-variant-numeric: tabular-nums; }
        .grand-total-label { padding: 12px 0 12px 12px; font-weight: 800; font-size: 0.95rem; color: #064e3b; background-color: #d1fae5 !important; text-transform: uppercase; border: none !important; border-top: 2px double #10b981 !important; }
        .grand-total-amount-col1 { text-align: right; padding: 12px 12px 12px 0; font-weight: 800; font-size: 0.95rem; color: #064e3b; background-color: #d1fae5 !important; border: none !important; border-top: 2px double #10b981 !important; font-variant-numeric: tabular-nums; }
        .grand-total-amount-col2 { text-align: right; padding: 12px 12px 12px 0; font-weight: 800; font-size: 0.95rem; color: #064e3b; background-color: #d1fae5 !important; border: none !important; border-top: 2px double #10b981 !important; font-variant-numeric: tabular-nums; }
        .balance-warning { color: #b91c1c; font-weight: 600; text-align: center; padding: 15px; background: #fef2f2; border: 1px solid #fca5a5; margin-top: 35px; font-size: 1rem; }
        .balance-sheet-report td { background: transparent; }
        .report-wrapper { padding: 20px; background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 800px; margin: 15px auto; box-sizing: border-box; }
    </style>';

    if (isset($_POST['PrintPDF'])) {
        $HTML .= '</head><body>';
    }

    $HTML .= '<div class="report-wrapper"><table class="balance-sheet-report">
    <colgroup>
        <col style="width: 60%;">
        <col style="width: 20%;">
        <col style="width: 20%;">
    </colgroup>';
    
    // Title
    $HTML .= '<tr>
		<th colspan="3" class="report-title">
			<div style="font-size: 1.4rem; font-weight: 700; color: #111827; margin-bottom: 5px; text-transform: uppercase;">' . $_SESSION['CompanyRecord']['coyname'] . '</div>
			<b style="font-size: 1.2rem; color: #059669; text-transform: uppercase;">' . __('Balance Sheet') . '</b><br>
			<span style="font-weight: normal; font-size: 0.9rem; color: #4b5563;">' . __('As at') . ' ' . $BalanceDate . '</span>
		</th>
	</tr>';

	// Column Headers
	$FormattedBalanceDate = ($ThisYearTimestamp !== false) ? date('d.m.Y', $ThisYearTimestamp) : str_replace(['/', '-'], '.', $BalanceDate);
	$HTML .= '<tr>
		<th style="text-align: left; padding: 10px 0 10px 20px; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #374151;"></th>
		<th style="text-align: right; padding: 10px 20px 10px 0; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #374151;">' . $FormattedBalanceDate . '<br><span style="font-size: 0.85em; color: #6b7280;">TZS</span></th>
		<th style="text-align: right; padding: 10px 20px 10px 0; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #6b7280;">31.12.' . $LastYear . '<br><span style="font-size: 0.85em;">TZS</span></th>
	</tr>';

    // ASSETS
    $HTML .= '<tr><td colspan="3" class="section-header">' . __('ASSETS') . '</td></tr>';
    
    $HTML .= '<tr><td colspan="3" class="subsection-header">' . __('Non-current assets') . '</td></tr>';
    foreach($bsData['assets']['non_current'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        $HTML .= '<tr class="item-row"><td class="item-label">' . $item['label'] . '</td><td class="amount-col1">' . formatMoneyBS($item['amount']) . '</td><td class="amount-col2">' . formatMoneyBS($item['ly_amount']) . '</td></tr>';
    }
    $HTML .= '<tr><td class="total-label">' . __('Total non-current assets') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalNonCurrentAssets) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalNonCurrentAssetsLY) . '</td></tr>';

    $HTML .= '<tr><td colspan="3" class="subsection-header">' . __('Current Assets') . '</td></tr>';
    foreach($bsData['assets']['current'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        $HTML .= '<tr class="item-row"><td class="item-label">' . $item['label'] . '</td><td class="amount-col1">' . formatMoneyBS($item['amount']) . '</td><td class="amount-col2">' . formatMoneyBS($item['ly_amount']) . '</td></tr>';
    }
    $HTML .= '<tr><td class="total-label">' . __('Total current assets') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalCurrentAssets) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalCurrentAssetsLY) . '</td></tr>';

    $HTML .= '<tr><td class="grand-total-label" style="padding-left: 20px;">' . __('Total assets') . '</td><td class="grand-total-amount-col1">' . formatMoneyBS($totalAssets) . '</td><td class="grand-total-amount-col2">' . formatMoneyBS($totalAssetsLY) . '</td></tr>';

    // SPACE
    $HTML .= '<tr><td colspan="3">&nbsp;</td></tr>';

    // EQUITY AND LIABILITIES
    $HTML .= '<tr><td colspan="3" class="section-header">' . __('EQUITY AND LIABILITIES') . '</td></tr>';

    // EQUITY
    $HTML .= '<tr><td colspan="3" class="subsection-header">' . __('Equity') . '</td></tr>';
    foreach($bsData['equity']['none'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        $HTML .= '<tr class="item-row"><td class="item-label">' . $item['label'] . '</td><td class="amount-col1">' . formatMoneyBS($item['amount']) . '</td><td class="amount-col2">' . formatMoneyBS($item['ly_amount']) . '</td></tr>';
    }
    $HTML .= '<tr><td class="total-label">' . __('Total equity') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalEquity) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalEquityLY) . '</td></tr>';

    // LIABILITIES
    $HTML .= '<tr><td colspan="3" class="subsection-header">' . __('Liabilities') . '</td></tr>';
    
    $HTML .= '<tr><td colspan="3" class="subsection-header" style="padding-left: 20px;">' . __('Non-current liabilities') . '</td></tr>';
    foreach($bsData['liabilities']['non_current'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        $HTML .= '<tr class="item-row"><td class="item-label" style="padding-left: 40px;">' . $item['label'] . '</td><td class="amount-col1">' . formatMoneyBS($item['amount']) . '</td><td class="amount-col2">' . formatMoneyBS($item['ly_amount']) . '</td></tr>';
    }
    $HTML .= '<tr><td class="total-label" style="padding-left: 20px;">' . __('Total non-current liabilities') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalNonCurrentLiabilities) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalNonCurrentLiabilitiesLY) . '</td></tr>';

    $HTML .= '<tr><td colspan="3" class="subsection-header" style="padding-left: 20px;">' . __('Current liabilities') . '</td></tr>';
    foreach($bsData['liabilities']['current'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        $HTML .= '<tr class="item-row"><td class="item-label" style="padding-left: 40px;">' . $item['label'] . '</td><td class="amount-col1">' . formatMoneyBS($item['amount']) . '</td><td class="amount-col2">' . formatMoneyBS($item['ly_amount']) . '</td></tr>';
    }
    $HTML .= '<tr><td class="total-label" style="padding-left: 20px;">' . __('Total current liabilities') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalCurrentLiabilities) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalCurrentLiabilitiesLY) . '</td></tr>';

    $HTML .= '<tr><td class="total-label" style="padding-left: 20px;">' . __('Total liabilities') . '</td><td class="total-amount-col1">' . formatMoneyBS($totalLiabilities) . '</td><td class="total-amount-col2">' . formatMoneyBS($totalLiabilitiesLY) . '</td></tr>';

    $HTML .= '<tr><td class="grand-total-label">' . __('Total Equity and liabilities') . '</td><td class="grand-total-amount-col1">' . formatMoneyBS($totalEquityAndLiabilities) . '</td><td class="grand-total-amount-col2">' . formatMoneyBS($totalEquityAndLiabilitiesLY) . '</td></tr>';

    $HTML .= '</table></div>';

    if ($balanceDiff > 0.01 || $balanceDiffLY > 0.01) {
        $HTML .= '<div class="balance-warning">' . __('WARNING: The Balance Sheet does not balance! Difference: ') . formatMoneyBS($balanceDiff) . ' / ' . formatMoneyBS($balanceDiffLY) . '</div>';
    }

	if (isset($_POST['PrintPDF'])) {
        $HTML .= '</body></html>';
        $DomPDF = new Dompdf($DomPDFOptions); 
        $DomPDF->loadHtml($HTML); 
        $DomPDF->setPaper($_SESSION['PageSize'], 'portrait'); 
        $DomPDF->render();
        $DomPDF->stream($_SESSION['DatabaseName'] . '_Balance_Sheet_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
        exit;
	} elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$File = 'GLBalanceSheet-' . date('Y-m-d'). '.' . 'xlsx';
		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$SpreadsheetHTML = '';
		if (preg_match('/<table[\s\S]*?<\/table>/i', $HTML, $m)) {
			$Table = $m[0];
			$Table = preg_replace('/&(?![a-zA-Z#][a-zA-Z0-9]*;)/', '&amp;', $Table);
			// Inject basic styling for the spreadsheet parser
			$SpreadsheetHTML = '<html><head><meta charset="UTF-8"><style>
				th { font-weight: bold; background-color: #f3f4f6; color: #111827; text-align: left; border: 1px solid #d1d5db; }
				td { border: 1px solid #d1d5db; }
				.amount-col1, .amount-col2 { text-align: right; }
				.total-label { font-weight: bold; border-top: 1px solid #000; }
				.total-amount-col1, .total-amount-col2 { font-weight: bold; background-color: #ecfdf5; color: #064e3b; border-top: 1px solid #000; }
				.section-header { font-weight: bold; background-color: #d1fae5; color: #065f46; }
				.grand-total-label, .grand-total-amount-col1, .grand-total-amount-col2 { font-weight: bold; background-color: #d1fae5; color: #064e3b; }
			</style></head><body>' . $Table . '</body></html>';
		} else {
			$SpreadsheetHTML = '<html><head><meta charset="UTF-8"></head><body><table><tbody></tbody></table></body></html>';
		}
		$spreadsheet = $reader->loadFromString($SpreadsheetHTML);
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
	} else {
		$Title = __('Balance Sheet Report'); 
        include(__DIR__ . '/includes/header.php');
		echo '
<style>
    .db-page { max-width: 100vw !important; box-sizing: border-box !important; padding: 20px; }
	.db-centered-container { max-width: 1350px !important; margin: 0 auto; box-sizing: border-box !important; }
	.db-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
	.db-header-left { display: flex; flex-direction: column; }
	.db-page-title { font-size: 1.5rem; font-weight: 700; color: var(--db-text-main); display: flex; align-items: center; gap: 10px; margin: 0; }
    .db-card { background: #ffffff; border-radius: 12px; box-shadow: var(--db-shadow-sm); border: 1px solid var(--db-border); overflow: hidden; width: 100% !important; box-sizing: border-box !important; margin-bottom: 24px; min-width: 0 !important; }
	.db-card-body { padding: 24px; }
	.db-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 10px 18px !important; border-radius: 6px !important; font-weight: 600 !important; font-size: 0.875rem !important; transition: all 0.2s ease !important; border: none !important; cursor: pointer !important; text-decoration: none !important; }
	.db-btn-primary { background: var(--db-primary) !important; color: #ffffff !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-btn-secondary { background: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-table-wrap { width: 100% !important; overflow-x: auto !important; overflow-y: auto !important; max-height: 78vh !important; -webkit-overflow-scrolling: touch; box-sizing: border-box !important; border-radius: 8px; border: 1px solid var(--db-border); background: #fff; margin-top: 15px; }
	.monochromatic-table { width: 100%; border-collapse: collapse; text-align: left; }
	.monochromatic-table th, .monochromatic-table thead td { background: #f9fafb !important; color: #4b5563 !important; padding: 16px 24px !important; font-weight: 600 !important; font-size: 0.85rem !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border-bottom: 2px solid var(--db-border) !important; position: sticky !important; top: 0 !important; z-index: 10 !important; }
	.monochromatic-table td { padding: 16px 24px !important; border-bottom: 1px solid var(--db-border) !important; font-size: 0.9rem !important; color: #111827 !important; vertical-align: middle; }
	.monochromatic-table tr:nth-child(even) { background-color: #f9fafb !important; }
	.monochromatic-table .number { text-align: right !important; }
    .total_row td { font-weight: bold !important; border-top: 1px solid #94a3b8 !important; border-bottom: 1px solid #94a3b8 !important; background-color: #f1f5f9 !important; }
    .section_row td { font-weight: bold !important; background-color: #d1fae5 !important; color: #065f46 !important; text-transform: uppercase !important; border-bottom: 2px solid #10b981 !important; }
    .check_totals_row td { background-color: #ecfdf5 !important; color: #064e3b !important; font-weight: bold !important; border-top: 2px solid #10b981 !important; border-bottom: 3px double #10b981 !important; }
</style>
<div class="db-page">
    <div class="db-centered-container">
        <div class="db-page-header">
            <div class="db-header-left">
                <h1 class="db-page-title"><i class="fas fa-file-invoice-dollar"></i> ' . __('Balance Sheet Report') . '</h1>
            </div>
        </div>
        <div class="db-card">
            <div class="db-card-body" style="overflow-x: auto;">
                ' . $HTML . '
                </form>
                <div class="centre" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
                    <form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" target="_blank">
                        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                        <input type="hidden" name="PeriodTo" value="' . $PeriodTo . '" />
                        <input type="hidden" name="ShowDetail" value="' . $ShowDetail . '" />
                        <input type="hidden" name="ShowZeroBalance" value="' . (empty($_POST['ShowZeroBalance']) ? 0 : 1) . '" />
                        <button type="submit" name="PrintPDF" class="db-btn db-btn-secondary" title="Produce PDF Report"><i class="fas fa-file-pdf"></i> ' . __('Print PDF') . '</button>
                        <button type="submit" name="Spreadsheet" class="db-btn db-btn-secondary" title="Spreadsheet"><i class="fas fa-file-excel"></i> ' . __('Spreadsheet') . '</button>
                    </form>
                    <form><button type="submit" name="close" class="db-btn db-btn-secondary" onclick="window.close()"><i class="fas fa-times"></i> ' . __('Close') . '</button></form>
                </div>
            </div>
        </div>
    </div>
</div>';
		include(__DIR__ . '/includes/footer.php');
	}

} else {
	include(__DIR__ . '/includes/header.php');
	echo '<style>
        :root { --db-primary: hsl(145, 63%, 38%); --db-primary-dark: hsl(145, 45%, 22%); --db-primary-soft: hsl(145, 40%, 95%); --db-bg: hsl(210, 20%, 97%); --db-border: hsl(210, 14%, 89%); }
        .db-page { background: var(--db-bg); min-height: 100vh; padding: 2rem; font-family: "Inter", sans-serif; }
        .db-card { background: #fff; border-radius: 12px; border: 1px solid var(--db-border); box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; overflow: hidden; }
        .db-card-header { padding: 1rem; border-bottom: 1px solid var(--db-border); }
        .db-card-title { font-size: 0.8rem; font-weight: 800; color: var(--db-primary-dark); text-transform: uppercase; margin:0; }
        .db-card-body { padding: 1.5rem; }
        .db-field { margin-bottom: 1.25rem; }
        .db-label { font-size: 0.7rem; font-weight: 800; color: var(--db-primary-dark); text-transform: uppercase; margin-bottom: 0.4rem; display: block; }
        .db-select { padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--db-border); font-size: 0.8rem; width: 100%; background:#fdfdfd; }
        .db-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer; border: none; transition: 0.2s; width: 100%; margin-top: 10px; }
        .db-btn-primary { background: var(--db-primary); color: #fff; }
    </style>';

    echo '<div class="db-page"><div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title">' . __('Balance Sheet Generator') . '</h3></div>
            <div class="db-card-body">
                <form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">
                <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                <div class="db-field"><label class="db-label">Balance As At Date</label><select name="PeriodTo" class="db-select">';
                $Pers = DB_query("SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC");
                while($R = DB_fetch_array($Pers)) echo '<option value="'.$R['periodno'].'">'.ConvertSQLDate($R['lastdate_in_period']).'</option>';
                echo '</select></div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="db-field"><label class="db-label">Report Type</label><select name="ShowDetail" class="db-select"><option value="Summary">Summary Only</option><option selected value="Detailed">Detailed Trial Balance</option></select></div>
                    <div class="db-field" style="display:flex; align-items:center; gap:10px; margin-top:2rem;"><input type="checkbox" name="ShowZeroBalance" id="szb" /> <label class="db-label" for="szb" style="margin:0;">Show Zero Balances</label></div>
                </div>

                <button type="submit" name="PrintPDF" class="db-btn db-btn-primary">Generate Official PDF</button>
                <button type="submit" name="View" class="db-btn db-btn-primary" style="background:var(--db-primary-soft); color:var(--db-primary);">View Balance Sheet Online</button>
                </form>
            </div>
        </div></div>';
	include(__DIR__ . '/includes/footer.php');
}
?>
