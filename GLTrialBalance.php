<?php

/* Through deviousness and cunning, this system allows trial balances for
 * any date range that recalculates the p & l balances and shows the balance
 * sheets as at the end of the period selected - so first off need to show
 * the input of criteria screen
 */

require(__DIR__ . '/includes/session.php');
use Dompdf\Dompdf;

include(__DIR__ . '/includes/SetDomPDFOptions.php');

$Title = __('Trial Balance');

// Architect Workspace UI: Core assets
$ExtraHeadContent = '
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root {
		--db-primary: #059669; /* Specific user requirement */
		--db-secondary: #6b7280;
		--db-danger: #ef4444;
		--db-surface-alt: #f9fafb;
		--db-border: #e5e7eb;
		--db-text-main: #111827;
		--db-text-muted: #6b7280;
		--db-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
		--db-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
	}
	html, body { width: 100% !important; max-width: 100vw !important; overflow-x: hidden !important; }
	#body_wrap_wrapper, .canvas { width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; overflow: hidden !important; }
	
	body { font-family: "Inter", sans-serif !important; background-color: var(--db-surface-alt) !important; color: var(--db-text-main) !important; margin: 0; padding: 0; }
	
	.db-page { width: 100% !important; max-width: 100vw !important; overflow-x: hidden !important; box-sizing: border-box !important; padding: 20px; }
	.db-centered-container { width: 100% !important; max-width: 1350px !important; margin: 0 auto; box-sizing: border-box !important; }
	
	/* Page Header */
	.db-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
	.db-header-left { display: flex; flex-direction: column; }
	.db-page-title { font-size: 1.5rem; font-weight: 700; color: var(--db-text-main); display: flex; align-items: center; gap: 10px; margin: 0; }
	.db-page-subtitle { font-size: 0.875rem; color: var(--db-text-muted); margin-top: 4px; }
	
	/* Layout & Cards */
	.db-main-layout { display: grid; gap: 24px; box-sizing: border-box !important; min-width: 0 !important; }
	.db-card { background: #ffffff; border-radius: 12px; box-shadow: var(--db-shadow-sm); border: 1px solid var(--db-border); overflow: hidden; width: 100% !important; box-sizing: border-box !important; margin-bottom: 24px; min-width: 0 !important; }
	.db-card-header { padding: 20px 24px; border-bottom: 1px solid var(--db-border); display: flex; justify-content: space-between; align-items: center; background: #ffffff; }
	.db-card-title { font-size: 1.15rem; font-weight: 600; color: var(--db-text-main); margin: 0; }
	.db-card-body { padding: 24px; }
	
	/* Forms */
	.db-form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px; }
	.db-form-group { display: flex; flex-direction: column; gap: 6px; }
	.db-label { font-size: 0.875rem; font-weight: 500; color: #374151; }
	.db-input, .db-select, input[type="text"], input[type="date"], select { 
		width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; 
		padding: 10px 14px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-size: 0.9rem !important; transition: border-color 0.15s ease !important; background: #fff !important; 
	}
	.db-input:focus, .db-select:focus, input[type="text"]:focus, select:focus { border-color: var(--db-primary) !important; outline: none !important; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1) !important; }
	.fieldhelp { font-size: 0.75rem; color: var(--db-text-muted); margin-top: 4px; }
	
	/* Buttons */
	.db-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 10px 18px !important; border-radius: 6px !important; font-weight: 600 !important; font-size: 0.875rem !important; transition: all 0.2s ease !important; border: none !important; cursor: pointer !important; text-decoration: none !important; }
	.db-btn-primary { background: var(--db-primary) !important; color: #ffffff !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-btn-primary:hover { background: #047857 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; }
	.db-btn-secondary { background: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-btn-secondary:hover { background: #f3f4f6 !important; color: #111827 !important; }
	
	/* Enhanced Tables */
	.db-table-wrap { 
		width: 100% !important; 
		overflow-x: auto !important; 
		overflow-y: auto !important; 
		max-height: 78vh !important; 
		-webkit-overflow-scrolling: touch; 
		box-sizing: border-box !important; 
		border-radius: 8px; 
		border: 1px solid var(--db-border); 
		background: #fff; 
		margin-top: 15px; 
	}
	.monochromatic-table { width: 100%; border-collapse: collapse; text-align: left; }
	.monochromatic-table th, .monochromatic-table thead td { 
		background: #f9fafb !important; 
		color: #4b5563 !important; 
		padding: 16px 24px !important; 
		font-weight: 600 !important; 
		font-size: 0.85rem !important; 
		text-transform: uppercase !important; 
		letter-spacing: 0.05em !important; 
		border-bottom: 2px solid var(--db-border) !important; 
		position: sticky !important; 
		top: 0 !important; 
		z-index: 10 !important; 
	}
	.monochromatic-table td { padding: 16px 24px !important; border-bottom: 1px solid var(--db-border) !important; font-size: 0.9rem !important; color: #111827 !important; vertical-align: middle; }
	.monochromatic-table tr:last-child td { border-bottom: none !important; }
	.monochromatic-table tr:nth-child(even) { background-color: #f9fafb !important; }
	.monochromatic-table .number { text-align: right !important; }
	
	/* Responsiveness */
	@media (max-width: 1024px) {
		#header, #footer { display: none !important; } /* Caging legacy elements */
	}
	@media (max-width: 768px) {
		.db-page { padding: 12px; }
		.db-card-header { padding: 14px 16px; }
		.db-card-body { padding: 16px; }
		.db-btn { width: 100% !important; justify-content: center !important; margin-bottom: 8px; }
		
		.monochromatic-table, .monochromatic-table thead, .monochromatic-table tbody, .monochromatic-table th, .monochromatic-table td, .monochromatic-table tr { display: block !important; width: 100% !important; }
		.monochromatic-table thead tr { display: none !important; }
		.monochromatic-table tr { border: 1px solid var(--db-border) !important; border-radius: 8px !important; margin-bottom: 12px !important; background: #fff !important; }
		.monochromatic-table td { border: none !important; display: flex !important; justify-content: space-between !important; padding: 10px 14px !important; text-align: right !important; border-bottom: 1px solid #f3f4f6 !important; }
		.monochromatic-table tr:nth-child(even) { background-color: #fff !important; }
		.monochromatic-table td::before { content: attr(data-label); font-weight: 600 !important; color: #6b7280 !important; text-align: left !important; flex: 1; padding-right: 12px; }
	}
</style>
';

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/AccountSectionsDef.php'); //this reads in the Accounts Sections array

// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['Period'])) {
	$_POST['Period'] = $_GET['Period'];
}
if (isset($_GET['SelectedBudget'])) {
	$_POST['SelectedBudget'] = $_GET['SelectedBudget'];
}
if (!isset($_POST['SelectedBudget'])) {
	$_POST['SelectedBudget'] = 0;
}

if (isset($_POST['PeriodFrom']) and isset($_POST['PeriodTo']) and $_POST['PeriodFrom'] > $_POST['PeriodTo']) {

	prnMsg(__('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
	$_POST['NewReport'] = __('Select A Different Period');
}

if (isset($_POST['PrintPDF']) or isset($_POST['View']) or isset($_POST['Spreadsheet'])) {

	if (isset($_POST['Period']) && $_POST['Period'] != '') {
		$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
	}

	$PeriodToDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));
	$PeriodFromDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodFrom']));
	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
		$HTML .= '<style>
			body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #ffffff; color: #334155; margin: 20px; font-size: 11px; line-height: 1.4; }
			.report-header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #059669; }
			.report-header h2 { margin: 0; font-size: 16px; color: #0f172a; font-weight: bold; }
			.report-header h3 { margin: 5px 0 2px 0; font-size: 12px; color: #059669; font-weight: bold; text-transform: uppercase; }
			.report-header p { margin: 0; color: #64748b; font-size: 10px; }
			.db-table-wrap { width: 100%; margin-top: 10px; }
			.monochromatic-table { width: 100%; border-collapse: collapse; text-align: left; }
			.monochromatic-table th { 
				background-color: #059669; 
				color: #ffffff; 
				padding: 6px 8px; 
				font-weight: bold; 
				font-size: 10px; 
				text-transform: uppercase;
				border: none;
			}
			.monochromatic-table td { padding: 4px 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; color: #334155; vertical-align: middle; }
			.monochromatic-table tr:nth-child(even) td { background-color: #f8fafc; }
			.monochromatic-table .number { text-align: right; }
			.total_row td { font-weight: bold; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; color: #0f172a; background-color: #f1f5f9 !important; font-size: 11px; }
			.check_totals_row td { background-color: #ecfdf5 !important; color: #064e3b; font-size: 12px; border-top: 2px solid #10b981; border-bottom: 3px double #10b981; }
		</style>';
		$HTML .= '</head><body>';
		
		$HTML .= '<div class="report-header">
					<h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2>
					<h3>' . __('Trial Balance') . '</h3>
					<p>' . __('Period: ') . $PeriodFromDate . __(' - ') . $PeriodToDate . '</p>
				  </div>';
	} else {
		$HTML .= '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
		$HTML .= '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		$HTML .= '<input type="hidden" name="PeriodFrom" value="' . $_POST['PeriodFrom'] . '" />';
		$HTML .= '<input type="hidden" name="PeriodTo" value="' . $_POST['PeriodTo'] . '" />';
		$HTML .= '<input type="hidden" name="SelectedBudget" value="' . $_POST['SelectedBudget'] . '" />';
	}

	$HTML .= '<div class="db-table-wrap"><table class="monochromatic-table">
				<thead>';
	if (!isset($_POST['PrintPDF'])) {
		$HTML .= '	<tr>
						<th colspan="4" style="text-align:center; padding: 15px;">
							<div style="font-size: 1.4rem; font-weight: 700; color: #111827; margin-bottom: 5px; text-transform: uppercase;">' . $_SESSION['CompanyRecord']['coyname'] . '</div>
							<b style="font-size: 1.2rem; color: #059669; text-transform: uppercase;">' . __('Trial Balance') . '</b><br>
							<span style="font-weight: normal; font-size: 0.9rem; color: #4b5563;">' . __('Period: ') . $PeriodFromDate . __(' - ') . $PeriodToDate . '</span>
						</th>
					</tr>';
	}
	$HTML .= '		<tr>
						<th>' . __('Account Code') . '</th>
						<th>' . __('Account Name') . '</th>
						<th class="number">' . __('Debit (TZS)') . '</th>
						<th class="number">' . __('Credit (TZS)') . '</th>
					</tr>
				</thead>
				<tbody>';

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	/* Firstly get the account totals for this period */
	$ThisMonthSQL = "SELECT account,
							SUM(amount) AS monthtotal
						FROM gltrans
						WHERE periodno='" . $_POST['PeriodTo'] . "'
						GROUP BY account";
	$ThisMonthResult = DB_query($ThisMonthSQL);
	$ThisMonthArray = array();

	while ($ThisMonthRow = DB_fetch_array($ThisMonthResult)) {
		$ThisMonthArray[$ThisMonthRow['account']] = $ThisMonthRow['monthtotal'];
	}

	/* Then get this periods cumulative P&L accounts */
	$ThisPeriodPLSQL = "SELECT account,
								SUM(amount) AS periodtotal
						FROM gltrans
						INNER JOIN chartmaster
							ON gltrans.account=chartmaster.accountcode
						INNER JOIN accountgroups
							ON chartmaster.group_=accountgroups.groupname
						WHERE periodno<='" . $_POST['PeriodTo'] . "'
							AND periodno>='" . $_POST['PeriodFrom'] . "'
							AND pandl=1
						GROUP BY account";
	$ThisPeriodPLResult = DB_query($ThisPeriodPLSQL);
	$ThisPeriodArray = array();

	while ($ThisPeriodPLRow = DB_fetch_array($ThisPeriodPLResult)) {
		$ThisPeriodArray[$ThisPeriodPLRow['account']] = $ThisPeriodPLRow['periodtotal'];
	}

	/* Then get this periods cumulative BS accounts */
	$ThisPeriodBSSQL = "SELECT account,
								SUM(amount) AS periodtotal
						FROM gltrans
						INNER JOIN chartmaster
							ON gltrans.account=chartmaster.accountcode
						INNER JOIN accountgroups
							ON chartmaster.group_=accountgroups.groupname
						WHERE periodno<='" . $_POST['PeriodTo'] . "'
							AND pandl=0
						GROUP BY account";
	$ThisPeriodBSResult = DB_query($ThisPeriodBSSQL);

	while ($ThisPeriodBSRow = DB_fetch_array($ThisPeriodBSResult)) {
		$ThisPeriodArray[$ThisPeriodBSRow['account']] = $ThisPeriodBSRow['periodtotal'];
	}

	/* Get the retained earnings amount */
	$RetainedEarningsSQL = "SELECT SUM(amount) AS retainedearnings
							FROM gltrans
							INNER JOIN chartmaster
								ON gltrans.account=chartmaster.accountcode
							INNER JOIN accountgroups
								ON chartmaster.group_=accountgroups.groupname
							WHERE periodno<'" . $_POST['PeriodFrom'] . "'
								AND pandl=1";
	$RetainedEarningsResult = DB_query($RetainedEarningsSQL);
	$RetainedEarningsRow = DB_fetch_array($RetainedEarningsResult);

	/* Get the month budget */
	$ThisMonthBudgetSQL = "SELECT account, SUM(amount) AS monthbudget FROM glbudgetdetails WHERE period='" . $_POST['PeriodTo'] . "' AND headerid='" . $_POST['SelectedBudget'] . "' GROUP BY account";
	$ThisMonthBudgetResult = DB_query($ThisMonthBudgetSQL);
	$ThisMonthBudgetArray = array();
	while ($Row = DB_fetch_array($ThisMonthBudgetResult)) {
		$ThisMonthBudgetArray[$Row['account']] = $Row['monthbudget'];
	}

	/* Get the period budget */
	$ThisPeriodBudgetSQL = "SELECT account, SUM(amount) AS periodbudget FROM glbudgetdetails WHERE period>='" . $_POST['PeriodFrom'] . "' AND period<='" . $_POST['PeriodTo'] . "' AND headerid='" . $_POST['SelectedBudget'] . "' GROUP BY account";
	$ThisPeriodBudgetResult = DB_query($ThisPeriodBudgetSQL);
	$ThisPeriodBudgetArray = array();
	while ($Row = DB_fetch_array($ThisPeriodBudgetResult)) {
		$ThisPeriodBudgetArray[$Row['account']] = $Row['periodbudget'];
	}

	// Get all account codes
	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.group_,
					group_,
					accountname,
					pandl
			FROM chartmaster
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode=chartmaster.accountcode
				AND glaccountusers.userid='" . $_SESSION['UserID'] . "'
				AND glaccountusers.canview=1
			INNER JOIN accountgroups
				ON accountgroups.groupname=chartmaster.group_
			ORDER BY accountgroups.sequenceintb,
					accountgroups.groupname,
					chartmaster.accountcode";
	$AccountListResult = DB_query($SQL);
	$AccountListRow = DB_fetch_array($AccountListResult);

	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row">
				<td>' . $AccountListRow['group_'] . '</td>
				<td colspan="3"></td>
			</tr>';

	$LastGroup = $AccountListRow['group_'];
	$LastGroupName = $AccountListRow['group_'];

	if (!isset($ThisMonthArray[$AccountListRow['accountcode']])) {
		$ThisMonthArray[$AccountListRow['accountcode']] = 0;
	}
	if (!isset($ThisPeriodArray[$AccountListRow['accountcode']])) {
		$ThisPeriodArray[$AccountListRow['accountcode']] = 0;
	}
	if ($_SESSION['CompanyRecord']['retainedearnings'] == $AccountListRow['accountcode']) {
		$ThisPeriodArray[$AccountListRow['accountcode']] += $RetainedEarningsRow['retainedearnings'];
	}

	$MonthActual = $ThisMonthArray[$AccountListRow['accountcode']];
	$PeriodActual = $ThisPeriodArray[$AccountListRow['accountcode']];
	
	$MonthBudget = $ThisMonthBudgetArray[$AccountListRow['accountcode']] ?? 0;
	$PeriodBudget = $ThisPeriodBudgetArray[$AccountListRow['accountcode']] ?? 0;

	$MonthDebit = ($MonthActual > 0) ? $MonthActual : 0;
	$MonthCredit = ($MonthActual < 0) ? -$MonthActual : 0;
	$PeriodDebit = ($PeriodActual > 0) ? $PeriodActual : 0;
	$PeriodCredit = ($PeriodActual < 0) ? -$PeriodActual : 0;

	$MonthDebitStr = ($MonthDebit != 0) ? locale_number_format($MonthDebit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
	$MonthCreditStr = ($MonthCredit != 0) ? locale_number_format($MonthCredit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
	$MonthBudgetStr = ($MonthBudget != 0) ? locale_number_format($MonthBudget, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
	$PeriodDebitStr = ($PeriodDebit != 0) ? locale_number_format($PeriodDebit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
	$PeriodCreditStr = ($PeriodCredit != 0) ? locale_number_format($PeriodCredit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
	$PeriodBudgetStr = ($PeriodBudget != 0) ? locale_number_format($PeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) : '-';

	$HTML .= '<tr class="striped_row">
				<td><a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . $_POST['PeriodFrom'] . '&amp;PeriodTo=' . $_POST['PeriodTo'] . '&amp;Account=' . $AccountListRow['accountcode'] . '&amp;Show=Yes">' . $AccountListRow['accountcode'] . '</a></td>
				<td>' . $AccountListRow['accountname'] . '</td>
				<td class="number">' . $PeriodDebitStr . '</td>
				<td class="number">' . $PeriodCreditStr . '</td>
			</tr>';

	$MonthDebitGroupTotal = $MonthDebit;
	$MonthCreditGroupTotal = $MonthCredit;
	$MonthBudgetGroupTotal = $MonthBudget;
	$PeriodDebitGroupTotal = $PeriodDebit;
	$PeriodCreditGroupTotal = $PeriodCredit;
	$PeriodBudgetGroupTotal = $PeriodBudget;

	$CumulativeMonthDebitGroupTotal = 0;
	$CumulativeMonthCreditGroupTotal = 0;
	$CumulativeMonthBudgetGroupTotal = 0;
	$CumulativePeriodDebitGroupTotal = 0;
	$CumulativePeriodCreditGroupTotal = 0;
	$CumulativePeriodBudgetGroupTotal = 0;

	while ($AccountListRow = DB_fetch_array($AccountListResult)) {
		if (!isset($ThisMonthArray[$AccountListRow['accountcode']])) {
			$ThisMonthArray[$AccountListRow['accountcode']] = 0;
		}
		if (!isset($ThisPeriodArray[$AccountListRow['accountcode']])) {
			$ThisPeriodArray[$AccountListRow['accountcode']] = 0;
		}
		if ($_SESSION['CompanyRecord']['retainedearnings'] == $AccountListRow['accountcode']) {
			$ThisPeriodArray[$AccountListRow['accountcode']] += $RetainedEarningsRow['retainedearnings'];
		}
		if ($AccountListRow['group_'] != $LastGroup) {
			$HTML .= '<tr>
						<td></td>
					</tr>';
			$HTML .= '<tr class="total_row">
						<td>' . __('Total') . '</td>
						<td>' . $LastGroupName . '</td>
						<td class="number">' . ($PeriodDebitGroupTotal != 0 ? locale_number_format($PeriodDebitGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
						<td class="number">' . ($PeriodCreditGroupTotal != 0 ? locale_number_format($PeriodCreditGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
					</tr>';
			$HTML .= '<tr>
						<td></td>
					</tr>';

			$HTML .= '<tr>
						<td></td>
					</tr>';
			$HTML .= '<tr class="total_row">
						<td>' . $AccountListRow['group_'] . '</td>
						<td colspan="3"></td>
					</tr>';

			$LastGroup = $AccountListRow['group_'];
			$LastGroupName = $AccountListRow['group_'];

			$CumulativeMonthDebitGroupTotal += $MonthDebitGroupTotal;
			$CumulativeMonthCreditGroupTotal += $MonthCreditGroupTotal;
			$CumulativeMonthBudgetGroupTotal += $MonthBudgetGroupTotal;
			$CumulativePeriodDebitGroupTotal += $PeriodDebitGroupTotal;
			$CumulativePeriodCreditGroupTotal += $PeriodCreditGroupTotal;
			$CumulativePeriodBudgetGroupTotal += $PeriodBudgetGroupTotal;

			$MonthDebitGroupTotal = 0;
			$MonthCreditGroupTotal = 0;
			$MonthBudgetGroupTotal = 0;
			$PeriodDebitGroupTotal = 0;
			$PeriodCreditGroupTotal = 0;
			$PeriodBudgetGroupTotal = 0;

		}

		$MonthActual = $ThisMonthArray[$AccountListRow['accountcode']];
		$PeriodActual = $ThisPeriodArray[$AccountListRow['accountcode']];
		
		$MonthBudget = $ThisMonthBudgetArray[$AccountListRow['accountcode']] ?? 0;
		$PeriodBudget = $ThisPeriodBudgetArray[$AccountListRow['accountcode']] ?? 0;

		$MonthDebit = ($MonthActual > 0) ? $MonthActual : 0;
		$MonthCredit = ($MonthActual < 0) ? -$MonthActual : 0;
		$PeriodDebit = ($PeriodActual > 0) ? $PeriodActual : 0;
		$PeriodCredit = ($PeriodActual < 0) ? -$PeriodActual : 0;

		$MonthDebitStr = ($MonthDebit != 0) ? locale_number_format($MonthDebit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
		$MonthCreditStr = ($MonthCredit != 0) ? locale_number_format($MonthCredit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
		$MonthBudgetStr = ($MonthBudget != 0) ? locale_number_format($MonthBudget, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
		$PeriodDebitStr = ($PeriodDebit != 0) ? locale_number_format($PeriodDebit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
		$PeriodCreditStr = ($PeriodCredit != 0) ? locale_number_format($PeriodCredit, $_SESSION['CompanyRecord']['decimalplaces']) : '-';
		$PeriodBudgetStr = ($PeriodBudget != 0) ? locale_number_format($PeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']) : '-';

		$HTML .= '<tr class="striped_row">
					<td><a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . $_POST['PeriodFrom'] . '&amp;PeriodTo=' . $_POST['PeriodTo'] . '&amp;Account=' . $AccountListRow['accountcode'] . '&amp;Show=Yes">' . $AccountListRow['accountcode'] . '</a></td>
					<td>' . $AccountListRow['accountname'] . '</td>
					<td class="number">' . $PeriodDebitStr . '</td>
					<td class="number">' . $PeriodCreditStr . '</td>
				</tr>';
		$MonthDebitGroupTotal += $MonthDebit;
		$MonthCreditGroupTotal += $MonthCredit;
		$MonthBudgetGroupTotal += $MonthBudget;
		$PeriodDebitGroupTotal += $PeriodDebit;
		$PeriodCreditGroupTotal += $PeriodCredit;
		$PeriodBudgetGroupTotal += $PeriodBudget;
	}
	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row">
				<td>' . __('Total') . '</td>
				<td>' . $LastGroupName . '</td>
				<td class="number">' . ($PeriodDebitGroupTotal != 0 ? locale_number_format($PeriodDebitGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
				<td class="number">' . ($PeriodCreditGroupTotal != 0 ? locale_number_format($PeriodCreditGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) : '-') . '</td>
			</tr>';
	$HTML .= '<tr>
				<td></td>
			</tr>';

	$CumulativeMonthDebitGroupTotal += $MonthDebitGroupTotal;
	$CumulativeMonthCreditGroupTotal += $MonthCreditGroupTotal;
	$CumulativeMonthBudgetGroupTotal += $MonthBudgetGroupTotal;
	$CumulativePeriodDebitGroupTotal += $PeriodDebitGroupTotal;
	$CumulativePeriodCreditGroupTotal += $PeriodCreditGroupTotal;
	$CumulativePeriodBudgetGroupTotal += $PeriodBudgetGroupTotal;

	$HTML .= '<tr>
				<td></td>
			</tr>';
	$HTML .= '<tr class="total_row check_totals_row">
				<td>' . __('Totals') . '</td>
				<td></td>
				<td class="number">' . locale_number_format($CumulativePeriodDebitGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CumulativePeriodCreditGroupTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	$HTML .= '<tr>
				<td></td>
			</tr>';

	$HTML .= '</tbody></table></div>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>';
	} else {
		$HTML .= '</form>
				<div class="centre" style="margin-top:20px;">
					<form><input type="submit" name="close" class="db-btn db-btn-secondary" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body></html>';

	if (isset($_POST['PrintPDF'])) {
		$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
		$DomPDF->set_option('isHtml5ParserEnabled', true);
		$DomPDF->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$DomPDF->render();

		// Output the generated PDF to Browser
		$DomPDF->stream($_SESSION['DatabaseName'] . '_GLTrialBalance_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$File = 'GLTrialBalance-' . date('Y-m-d'). '.' . 'xlsx';

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
				.number { text-align: right; }
				.total_row td { font-weight: bold; border-top: 2px solid #000; }
			</style></head><body>' . $Table . '</body></html>';
		} else {
			$SpreadsheetHTML = '<html><head><meta charset="UTF-8"></head><body><table><tbody></tbody></table></body></html>';
		}
		$spreadsheet = $reader->loadFromString($SpreadsheetHTML);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
	} else {
		$Title = __('General Ledger Trial Balance');
		include(__DIR__ . '/includes/header.php');
		echo '<div class="db-page">
				<div class="db-centered-container">
					<div class="db-page-header">
						<div class="db-header-left">
							<h1 class="db-page-title"><i class="fas fa-balance-scale"></i> ' . __('Trial Balance Report') . '</h1>
						</div>
					</div>
					<div class="db-card">
						<div class="db-card-body" style="overflow-x: auto;">';
		echo $HTML;
		echo '			</div>
					</div>
				</div>
			</div>';
		include(__DIR__ . '/includes/footer.php');
	}

} else {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';
	include(__DIR__ . '/includes/header.php');
	echo '<div class="db-page">
			<div class="db-centered-container">
				<div class="db-page-header">
					<div class="db-header-left">
						<h1 class="db-page-title"><i class="fas fa-balance-scale"></i> ' . __('Trial Balance Report') . '</h1>
					</div>
				</div>
				<div class="db-main-layout">';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = date('Y-m-d', mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, date('Y')));
		$FromDate = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, date('Y')));
	} else {
		$DefaultFromDate = date('Y-m-d', mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, date('Y') - 1));
		$FromDate = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, date('Y') - 1));
	}
	/*GetPeriod function creates periods if need be the return value is not used */
	$NotUsedPeriodNo = GetPeriod($FromDate);

	/*Show a form to allow input of criteria for TB to show */
	echo '<div class="db-card">
			<div class="db-card-header"><h3 class="db-card-title">', __('Input criteria for Trial Balance'), '</h3></div>
			<div class="db-card-body">
				<div class="db-form-row">
					<div class="db-form-group">
						<label class="db-label" for="PeriodFrom">', __('Select Period From'), '</label>
						<select class="db-select" name="PeriodFrom" autofocus="autofocus">';
	$NextYear = date('Y-m-d', strtotime('+1 Year'));
	$SQL = "SELECT periodno,
					lastdate_in_period
				FROM periods
				WHERE lastdate_in_period < '" . $NextYear . "'
				ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) and $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			} else {
				echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			} else {
				echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
			}
		}
	}
	echo '</select>
		<div class="fieldhelp">', __('Select the starting period for this report'), '</div>
	</div>';

	if (!isset($_POST['PeriodTo']) or $_POST['PeriodTo'] == '') {
		$DefaultPeriodTo = GetPeriod(date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') + 1, 0, date('Y'))));
	} else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	echo '<div class="db-form-group">
			<label class="db-label" for="PeriodTo">', __('Select Period To'), '</label>
			<select class="db-select" name="PeriodTo">';

	DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultPeriodTo) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		<div class="fieldhelp">', __('Select the end period for this report'), '</div>
	</div></div>';

	echo '<h3 class="db-card-title" style="margin: 15px 0; text-align: center;">', __('OR'), '</h3><div class="db-form-row">';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<div class="db-form-group">
			<label class="db-label" for="Period">', __('Select Period'), '</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')), '
			<div class="fieldhelp">', __('Select a predefined period from this list. If a selection is made here it will override anything selected in the From and To options above.'), '</div>
		</div>';

	if (!isset($_POST['SelectedBudget'])) {
		$_POST['SelectedBudget'] = 0;
	}
	echo '<div class="db-form-group">
			<label class="db-label" for="SelectedBudget">', __('Budget Source'), '</label>
			<select class="db-select" name="SelectedBudget">';
	$Buds = DB_query("SELECT id, name FROM glbudgetheaders");
	echo '<option value="0">', __('None'), '</option>';
	while ($Bud = DB_fetch_array($Buds)) {
		if ($_POST['SelectedBudget'] == $Bud['id']) {
			echo '<option selected="selected" value="', $Bud['id'], '">', $Bud['name'], '</option>';
		} else {
			echo '<option value="', $Bud['id'], '">', $Bud['name'], '</option>';
		}
	}
	echo '</select>
		<div class="fieldhelp">', __('Select the budget to compare against'), '</div>
	</div>';

	echo '</div></div> <!-- close db-form-row and db-card-body -->
	</div>'; // close db-card

	echo '<div class="centre" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
				<button type="submit" name="PrintPDF" class="db-btn db-btn-secondary" title="Produce PDF Report" value="' . __('Print PDF') . '"><i class="fas fa-file-pdf"></i> ' . __('Print PDF') . '</button>
				<button type="submit" name="View" class="db-btn db-btn-primary" title="View Report" value="' . __('View') . '"><i class="fas fa-eye"></i> ' . __('View') . '</button>
				<button type="submit" name="Spreadsheet" class="db-btn db-btn-secondary" title="Spreadsheet" value="' . __('Spreadsheet') . '"><i class="fas fa-file-excel"></i> ' . __('Spreadsheet') . '</button>
		</div>';

	echo '</form>
		</div></div></div>'; // end main layout, center container, page
	include(__DIR__ . '/includes/footer.php');
}
