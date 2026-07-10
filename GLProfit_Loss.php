<?php

if (!isset($IsIncluded)) {
	require(__DIR__ . '/includes/session.php');
}
use Dompdf\Dompdf;
include_once(__DIR__ . '/includes/SetDomPDFOptions.php');
include_once(__DIR__ . '/includes/SQL_CommonFunctions.php');
include_once(__DIR__ . '/includes/AccountSectionsDef.php');
include_once(__DIR__ . '/includes/CurrenciesArray.php');

$Title = __('Profit and Loss');
$ViewTopic = 'GeneralLedger';
$BookMark = 'ProfitAndLoss';

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	if (!isset($_POST['SelectedBudget'])) $_POST['SelectedBudget'] = 0;
	if (!isset($_POST['ShowDetail'])) $_POST['ShowDetail'] = 'Summary';
	if (isset($_POST['Period']) and $_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
	}
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;
	$PeriodToDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));
	$PeriodFromDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodFrom']));

	$ThisYearSQLDate = EndDateSQLFromPeriodNo($_POST['PeriodTo']);
	$ThisYearTimestamp = !empty($ThisYearSQLDate) ? strtotime($ThisYearSQLDate) : false;
	$FormattedPeriodToDate = ($ThisYearTimestamp !== false) ? date('d.m.Y', $ThisYearTimestamp) : date('d.m.Y');
	$ThisYear = ($ThisYearTimestamp !== false) ? (int)date('Y', $ThisYearTimestamp) : (int)date('Y');
	
	$LastYearPeriod = $_POST['PeriodTo'] - 12;
	$LastYearSQLDate = EndDateSQLFromPeriodNo($LastYearPeriod);
	$LastYearTimestamp = !empty($LastYearSQLDate) ? strtotime($LastYearSQLDate) : false;
	$LastYear = ($LastYearTimestamp !== false) ? (int)date('Y', $LastYearTimestamp) : ($ThisYear - 1);

    // --- DATA MAPPING FUNCTION ---
    if (!function_exists('mapAccountToProfitLossCategory')) {
        function mapAccountToProfitLossCategory($accountName, $groupName) {
            $acc = strtolower($accountName);
            $grp = strtolower($groupName);
            $combined = $acc . ' ' . $grp;

            // REVENUE (SALES)
            if (strpos($combined, 'non-exchange') !== false || strpos($combined, 'grant') !== false || strpos($combined, 'donation') !== false || strpos($combined, 'subvention') !== false) {
                return ['type' => 'revenue', 'key' => 'non_exchange_transactions'];
            }
            if (strpos($combined, 'exchange') !== false || strpos($combined, 'sale') !== false || strpos($combined, 'fee') !== false || strpos($combined, 'tuition') !== false || strpos($combined, 'revenue') !== false || strpos($combined, 'income') !== false) {
                return ['type' => 'revenue', 'key' => 'exchange_transactions'];
            }

            // EXPENSES
            if (strpos($combined, 'admin') !== false || strpos($combined, 'office') !== false || strpos($combined, 'travel') !== false || strpos($combined, 'communication') !== false) {
                return ['type' => 'expenses', 'key' => 'administrative_expenditure'];
            }
            if (strpos($combined, 'wage') !== false || strpos($combined, 'salary') !== false || strpos($combined, 'staff') !== false || strpos($combined, 'benefit') !== false || strpos($combined, 'allowance') !== false || strpos($combined, 'compensation') !== false) {
                return ['type' => 'expenses', 'key' => 'wages_salaries_employee_benefits'];
            }
            if (strpos($combined, 'depreciation') !== false || strpos($combined, 'amortization') !== false || strpos($combined, 'ppe') !== false) {
                return ['type' => 'expenses', 'key' => 'depreciation_of_ppe'];
            }
            
            // Fallbacks - default any other P&L account to Other operating expenses
            return ['type' => 'expenses', 'key' => 'other_operating_expenses'];
        }
    }

    if (!function_exists('formatMoneyPL')) {
        function formatMoneyPL($amount) {
            if (abs($amount) < 0.005) return locale_number_format(0, 2);
            return locale_number_format((float)$amount, 2);
        }
    }

    $plData = [
        'revenue' => [
            'non_exchange_transactions' => ['label' => 'Revenue from Non-exchange Transactions', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
            'exchange_transactions' => ['label' => 'Revenue from Exchange Transactions', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
        ],
        'expenses' => [
            'administrative_expenditure' => ['label' => 'Administrative expenditure', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
            'wages_salaries_employee_benefits' => ['label' => 'Wages, salaries & employee benefits', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
            'other_operating_expenses' => ['label' => 'Other operating expenses', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
            'depreciation_of_ppe' => ['label' => 'Depreciation of PPE', 'amount' => 0.0, 'ly_amount' => 0.0, 'accounts' => []],
        ]
    ];

    // --- DATA FETCHING ---
    $AccountListResult = DB_query("SELECT sectionid, sectionname, parentgroupname, chartmaster.group_, chartmaster.accountcode, accountname, pandl 
                                   FROM chartmaster 
                                   INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1 
                                   INNER JOIN accountgroups ON accountgroups.groupname=chartmaster.group_ 
                                   INNER JOIN accountsection ON accountsection.sectionid=accountgroups.sectioninaccounts 
                                   WHERE pandl=1 ORDER BY sequenceintb, group_, accountcode");

    $ThisYearRes = DB_query("SELECT account, ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal FROM gltrans WHERE periodno>='" . $_POST['PeriodFrom'] . "' AND periodno<='" . $_POST['PeriodTo'] . "' GROUP BY account");
    $ThisYearActuals = [];
    while ($R = DB_fetch_array($ThisYearRes)) $ThisYearActuals[$R['account']] = $R['accounttotal'];

    $PeriodFromLY = $_POST['PeriodFrom'] - 12;
    $PeriodToLY = $_POST['PeriodTo'] - 12;
    $LastYearRes = DB_query("SELECT account, ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal FROM gltrans WHERE periodno>='" . $PeriodFromLY . "' AND periodno<='" . $PeriodToLY . "' GROUP BY account");
    $LastYearActuals = [];
    while ($R = DB_fetch_array($LastYearRes)) $LastYearActuals[$R['account']] = $R['accounttotal'];

    while ($MyRow = DB_fetch_array($AccountListResult)) {
        $AccountBalance = $ThisYearActuals[$MyRow['accountcode']] ?? 0;
        $AccountBalanceLY = $LastYearActuals[$MyRow['accountcode']] ?? 0;
        $ShowZero = !empty($_POST['ShowZeroBalance']);
        if ($ShowZero || abs($AccountBalance) > 0.005 || abs($AccountBalanceLY) > 0.005) {
            $map = mapAccountToProfitLossCategory($MyRow['accountname'], $MyRow['group_']);
            // Standard accounting: Revenue is typically a credit balance (negative in raw data)
            // Expenses are typically debit balances (positive in raw data)
            // To display both as positive numbers on the report:
            $val = 0;
            $valLY = 0;
            if ($map['type'] === 'revenue') {
                $val = -$AccountBalance; 
                $valLY = -$AccountBalanceLY;
            } else {
                $val = $AccountBalance;
                $valLY = $AccountBalanceLY;
            }
            $plData[$map['type']][$map['key']]['amount'] += $val;
            $plData[$map['type']][$map['key']]['ly_amount'] += $valLY;
            
            // Store individual accounts for Detailed view
            $plData[$map['type']][$map['key']]['accounts'][] = [
                'code' => $MyRow['accountcode'],
                'name' => $MyRow['accountname'],
                'amount' => $val,
                'ly_amount' => $valLY
            ];
        }
    }

    // --- CALCULATIONS ---
    $totalRevenue = array_sum(array_column($plData['revenue'], 'amount'));
    $totalRevenueLY = array_sum(array_column($plData['revenue'], 'ly_amount'));
    $totalExpenses = array_sum(array_column($plData['expenses'], 'amount'));
    $totalExpensesLY = array_sum(array_column($plData['expenses'], 'ly_amount'));
    $surplusDeficit = $totalRevenue - $totalExpenses;
    $surplusDeficitLY = $totalRevenueLY - $totalExpensesLY;

    // --- HTML / CSS RENDERING ---
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
        .balance-sheet-report td { background: transparent; }
        .report-wrapper { padding: 20px; background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 800px; margin: 15px auto; box-sizing: border-box; }
        
        .db-page { max-width: 100vw !important; box-sizing: border-box !important; padding: 20px; background: hsl(210, 20%, 97%); min-height: 100vh; }
        .db-centered-container { max-width: 1350px !important; margin: 0 auto; box-sizing: border-box !important; }
        .db-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; font-family: "Inter", Helvetica, sans-serif; }
        .db-page-title { font-size: 1.5rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; margin: 0; }
        .db-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 10px 18px !important; border-radius: 6px !important; font-weight: 600 !important; font-size: 0.875rem !important; transition: all 0.2s ease !important; border: none !important; cursor: pointer !important; text-decoration: none !important; font-family: "Inter", Helvetica, sans-serif; }
        .db-btn-secondary { background: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
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
			<b style="font-size: 1.2rem; color: #059669; text-transform: uppercase;">' . __('Statement of Financial Performance') . '</b><br>
			<span style="font-weight: normal; font-size: 0.9rem; color: #4b5563;">' . __('For the Period') . ' ' . $PeriodFromDate . ' ' . __('to') . ' ' . $PeriodToDate . '</span>
		</th>
	</tr>';

	// Column Headers
	$HTML .= '<tr>
		<th style="text-align: left; padding: 10px 0 10px 20px; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #374151;"></th>
		<th style="text-align: right; padding: 10px 20px 10px 0; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #374151;">' . $FormattedPeriodToDate . '<br><span style="font-size: 0.85em; color: #6b7280;">TZS</span></th>
		<th style="text-align: right; padding: 10px 20px 10px 0; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #6b7280;">31.12.' . $LastYear . '<br><span style="font-size: 0.85em;">TZS</span></th>
	</tr>';

    // REVENUE
    $HTML .= '<tr><td colspan="3" class="section-header">' . __('REVENUE') . '</td></tr>';
    foreach($plData['revenue'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        if ($_POST['ShowDetail'] == 'Detailed' && !empty($item['accounts'])) {
            $HTML .= '<tr class="item-row"><td class="item-label" style="font-weight: 700; background-color:#f1f5f9;">' . __($item['label']) . '</td><td class="amount-col1" style="background-color:#f1f5f9;"></td><td class="amount-col2" style="background-color:#f1f5f9;"></td></tr>';
            foreach($item['accounts'] as $acc) {
                if (empty($_POST['ShowZeroBalance']) && abs($acc['amount']) < 0.005 && abs($acc['ly_amount']) < 0.005) {
                    continue;
                }
                $HTML .= '<tr class="item-row"><td class="item-label" style="padding-left: 40px; font-size: 0.75rem; color:#475569;">' . $acc['code'] . ' - ' . $acc['name'] . '</td><td class="amount-col1" style="font-size: 0.75rem; color:#475569;">' . formatMoneyPL($acc['amount']) . '</td><td class="amount-col2" style="font-size: 0.75rem; color:#475569;">' . formatMoneyPL($acc['ly_amount']) . '</td></tr>';
            }
            $HTML .= '<tr class="item-row"><td class="item-label" style="text-align: right; font-style: italic; font-size: 0.8rem;">' . __('Subtotal') . ' ' . __($item['label']) . '</td><td class="amount-col1" style="font-weight: 600; border-bottom: 2px solid #e2e8f0 !important;">' . formatMoneyPL($item['amount']) . '</td><td class="amount-col2" style="font-weight: 600; border-bottom: 2px solid #e2e8f0 !important;">' . formatMoneyPL($item['ly_amount']) . '</td></tr>';
        } else {
            $HTML .= '<tr class="item-row"><td class="item-label">' . __($item['label']) . '</td><td class="amount-col1">' . formatMoneyPL($item['amount']) . '</td><td class="amount-col2">' . formatMoneyPL($item['ly_amount']) . '</td></tr>';
        }
    }
    $HTML .= '<tr><td class="total-label">' . __('Total revenue') . '</td><td class="total-amount-col1">' . formatMoneyPL($totalRevenue) . '</td><td class="total-amount-col2">' . formatMoneyPL($totalRevenueLY) . '</td></tr>';

    // EXPENSES
    $HTML .= '<tr><td colspan="3" class="section-header">' . __('EXPENSES') . '</td></tr>';
    foreach($plData['expenses'] as $item) {
        if (empty($_POST['ShowZeroBalance']) && abs($item['amount']) < 0.005 && abs($item['ly_amount']) < 0.005) {
            continue;
        }
        if ($_POST['ShowDetail'] == 'Detailed' && !empty($item['accounts'])) {
            $HTML .= '<tr class="item-row"><td class="item-label" style="font-weight: 700; background-color:#f1f5f9;">' . __($item['label']) . '</td><td class="amount-col1" style="background-color:#f1f5f9;"></td><td class="amount-col2" style="background-color:#f1f5f9;"></td></tr>';
            foreach($item['accounts'] as $acc) {
                if (empty($_POST['ShowZeroBalance']) && abs($acc['amount']) < 0.005 && abs($acc['ly_amount']) < 0.005) {
                    continue;
                }
                $HTML .= '<tr class="item-row"><td class="item-label" style="padding-left: 40px; font-size: 0.75rem; color:#475569;">' . $acc['code'] . ' - ' . $acc['name'] . '</td><td class="amount-col1" style="font-size: 0.75rem; color:#475569;">' . formatMoneyPL($acc['amount']) . '</td><td class="amount-col2" style="font-size: 0.75rem; color:#475569;">' . formatMoneyPL($acc['ly_amount']) . '</td></tr>';
            }
            $HTML .= '<tr class="item-row"><td class="item-label" style="text-align: right; font-style: italic; font-size: 0.8rem;">' . __('Subtotal') . ' ' . __($item['label']) . '</td><td class="amount-col1" style="font-weight: 600; border-bottom: 2px solid #e2e8f0 !important;">' . formatMoneyPL($item['amount']) . '</td><td class="amount-col2" style="font-weight: 600; border-bottom: 2px solid #e2e8f0 !important;">' . formatMoneyPL($item['ly_amount']) . '</td></tr>';
        } else {
            $HTML .= '<tr class="item-row"><td class="item-label">' . __($item['label']) . '</td><td class="amount-col1">' . formatMoneyPL($item['amount']) . '</td><td class="amount-col2">' . formatMoneyPL($item['ly_amount']) . '</td></tr>';
        }
    }
    $HTML .= '<tr><td class="total-label">' . __('Total expenses') . '</td><td class="total-amount-col1">' . formatMoneyPL($totalExpenses) . '</td><td class="total-amount-col2">' . formatMoneyPL($totalExpensesLY) . '</td></tr>';

    // SURPLUS (DEFICIT)
    $HTML .= '<tr><td class="grand-total-label">' . __('Surplus (Deficit) for the period') . '</td><td class="grand-total-amount-col1">' . formatMoneyPL($surplusDeficit) . '</td><td class="grand-total-amount-col2">' . formatMoneyPL($surplusDeficitLY) . '</td></tr>';

    $HTML .= '</table></div>';

    if (isset($_POST['PrintPDF'])) {
        $HTML .= '</body></html>';
        $DomPDF = new Dompdf($DomPDFOptions); 
        $DomPDF->loadHtml($HTML); 
        $DomPDF->setPaper($_SESSION['PageSize'], 'portrait'); 
        $DomPDF->render();
        $DomPDF->stream($_SESSION['DatabaseName'] . '_Profit_Loss_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
        exit;
    } else {
        $Title = __('Financial Statement View'); include(__DIR__ . '/includes/header.php');
        echo $HTML;
        echo '<div class="centre" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
                <form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">
                    <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                    <input type="hidden" name="PeriodFrom" value="' . $_POST['PeriodFrom'] . '" />
                    <input type="hidden" name="PeriodTo" value="' . $_POST['PeriodTo'] . '" />
                    <input type="hidden" name="SelectedBudget" value="' . $_POST['SelectedBudget'] . '" />
                    <input type="hidden" name="ShowDetail" value="' . $_POST['ShowDetail'] . '" />
                    <input type="hidden" name="ShowZeroBalance" value="' . (empty($_POST['ShowZeroBalance']) ? 0 : 1) . '" />
                    <button type="submit" name="PrintPDF" class="db-btn db-btn-secondary" title="Produce PDF Report"><i class="fas fa-file-pdf" style="color:#ef4444;"></i> ' . __('Print PDF') . '</button>
                </form>
                <form><button type="submit" name="close" class="db-btn db-btn-secondary" onclick="window.close()"><i class="fas fa-times"></i> ' . __('Close') . '</button></form>
              </div>';
        include(__DIR__ . '/includes/footer.php');
    }


} else {
    // SETUP PAGE (Architect v3 Card)
	include(__DIR__ . '/includes/header.php');
	echo '<style>
        :root { --primary: hsl(145, 63%, 38%); --primary-dark: hsl(145, 45%, 22%); --primary-soft: hsl(145, 40%, 95%); --bg: hsl(210, 20%, 97%); --border: #e2e8f0; }
        .aw-page { background: var(--bg); min-height: 100vh; padding: 2rem; font-family: "Inter", sans-serif; display: flex; align-items: flex-start; justify-content: center; }
        .aw-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%; max-width: 600px; overflow: hidden; }
        .aw-card-header { padding: 1.5rem; border-bottom: 1px solid var(--border); background: #fff; }
        .aw-card-title { font-size: 1rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin:0; display: flex; align-items: center; gap: 0.75rem; }
        .aw-card-body { padding: 2rem; }
        .aw-field { margin-bottom: 1.5rem; }
        .aw-label { font-size: 0.75rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
        .aw-select { padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; width: 100%; transition: border-color 0.2s; }
        .aw-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px var(--primary-soft); }
        .aw-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.85rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; border: none; transition: 0.2s; width: 100%; margin-top: 10px; }
        .aw-btn-primary { background: var(--primary); color: #fff; }
        .aw-btn-primary:hover { background: hsl(145, 63%, 32%); transform: translateY(-1px); }
    </style>';

    echo '<div class="aw-page"><div class="aw-card">
            <div class="aw-card-header"><h3 class="aw-card-title"><i class="fas fa-file-invoice-dollar"></i> ' . __('Profit and Loss Statement') . '</h3></div>
            <div class="aw-card-body">
                <form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
                <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                    <div class="aw-field"><label class="aw-label">' . __('From Period') . '</label><select name="PeriodFrom" class="aw-select">';
                    $Pers = DB_query("SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC");
                    while($R = DB_fetch_array($Pers)) echo '<option value="'.$R['periodno'].'">'.ConvertSQLDate($R['lastdate_in_period']).'</option>';
                    echo '</select></div>
                    <div class="aw-field"><label class="aw-label">' . __('To Period') . '</label><select name="PeriodTo" class="aw-select">';
                    DB_data_seek($Pers, 0); while($R = DB_fetch_array($Pers)) echo '<option value="'.$R['periodno'].'">'.ConvertSQLDate($R['lastdate_in_period']).'</option>';
                    echo '</select></div>
                </div>

                <div class="aw-field"><label class="aw-label">' . __('Budget Source') . '</label><select name="SelectedBudget" class="aw-select">';
                $Buds = DB_query("SELECT id, name FROM glbudgetheaders");
                while($R = DB_fetch_array($Buds)) echo '<option value="'.$R['id'].'">'.$R['name'].'</option>';
                echo '</select></div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                    <div class="aw-field"><label class="aw-label">' . __('Detail Level') . '</label><select name="ShowDetail" class="aw-select"><option selected value="Summary">' . __('Summary Only') . '</option><option value="Detailed">' . __('Full Details') . '</option></select></div>
                    <div class="aw-field" style="display:flex; align-items:center; gap:10px; margin-top:1.5rem;"><input type="checkbox" name="ShowZeroBalance" id="szb" /> <label class="aw-label" for="szb" style="margin:0;">' . __('Show Zero Balances') . '</label></div>
                </div>

                <button type="submit" name="View" class="aw-btn aw-btn-primary"><i class="fas fa-eye" style="margin-right:8px;"></i> ' . __('View Statement Online') . '</button>
                <button type="submit" name="PrintPDF" class="aw-btn aw-btn-outline" style="margin-top:1rem;"><i class="fas fa-file-pdf" style="margin-right:8px; color:#ef4444;"></i> ' . __('Download Official PDF') . '</button>
                </form>
            </div>
        </div></div>';
	include(__DIR__ . '/includes/footer.php');
}
?>
