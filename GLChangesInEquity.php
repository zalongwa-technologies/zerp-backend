<?php
/* Statement of Changes in Equity */

require(__DIR__ . '/includes/session.php');
use Dompdf\Dompdf;
include(__DIR__ . '/includes/SetDomPDFOptions.php');

$Title = __('Statement of Changes in Equity');
$ViewTopic = 'GeneralLedger';
$BookMark = 'ChangesInEquity';

// Styling matching Trial Balance and Balance Sheet
$ExtraHeadContent = '
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root {
		--db-primary: #059669;
		--db-secondary: #6b7280;
		--db-surface-alt: #f9fafb;
		--db-border: #e5e7eb;
		--db-text-main: #111827;
		--db-text-muted: #6b7280;
		--db-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
	}
	html, body { width: 100% !important; max-width: 100vw !important; overflow-x: hidden !important; }
	body { font-family: "Inter", sans-serif !important; background-color: var(--db-surface-alt) !important; color: var(--db-text-main) !important; margin: 0; padding: 0; }
	.db-page { width: 100% !important; max-width: 100vw !important; overflow-x: hidden !important; box-sizing: border-box !important; padding: 20px; }
	.db-centered-container { width: 100% !important; max-width: 1350px !important; margin: 0 auto; box-sizing: border-box !important; }
	.db-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
	.db-page-title { font-size: 1.5rem; font-weight: 700; color: var(--db-text-main); display: flex; align-items: center; gap: 10px; margin: 0; }
	.db-card { background: #ffffff; border-radius: 12px; box-shadow: var(--db-shadow-sm); border: 1px solid var(--db-border); overflow: hidden; width: 100% !important; box-sizing: border-box !important; margin-bottom: 24px; }
	.db-card-header { padding: 20px 24px; border-bottom: 1px solid var(--db-border); display: flex; justify-content: space-between; align-items: center; background: #ffffff; }
	.db-card-title { font-size: 1.15rem; font-weight: 600; color: var(--db-text-main); margin: 0; }
	.db-card-body { padding: 24px; }
	.db-form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px; }
	.db-form-group { display: flex; flex-direction: column; gap: 6px; }
	.db-label { font-size: 0.875rem; font-weight: 500; color: #374151; }
	.db-select { padding: 10px 14px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-size: 0.9rem !important; transition: border-color 0.15s ease !important; background: #fff !important; }
	.db-select:focus { border-color: var(--db-primary) !important; outline: none !important; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1) !important; }
	.db-btn { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 10px 18px !important; border-radius: 6px !important; font-weight: 600 !important; font-size: 0.875rem !important; transition: all 0.2s ease !important; border: none !important; cursor: pointer !important; text-decoration: none !important; }
	.db-btn-primary { background: var(--db-primary) !important; color: #ffffff !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-btn-primary:hover { background: #047857 !important; transform: translateY(-1px); }
	.db-btn-secondary { background: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
	.db-btn-secondary:hover { background: #f3f4f6 !important; color: #111827 !important; }
	.db-table-wrap { width: 100% !important; overflow-x: auto !important; max-height: 78vh !important; -webkit-overflow-scrolling: touch; box-sizing: border-box !important; border-radius: 8px; border: 1px solid var(--db-border); background: #fff; margin-top: 15px; }
	.monochromatic-table { width: 100%; border-collapse: collapse; text-align: left; }
	.monochromatic-table th { background: #f9fafb !important; color: #4b5563 !important; padding: 16px 24px !important; font-weight: 600 !important; font-size: 0.85rem !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border-bottom: 2px solid var(--db-border) !important; position: sticky !important; top: 0 !important; z-index: 10 !important; }
	.monochromatic-table td { padding: 16px 24px !important; border-bottom: 1px solid var(--db-border) !important; font-size: 0.9rem !important; color: #111827 !important; vertical-align: middle; }
	.monochromatic-table tr:nth-child(even) { background-color: #f9fafb !important; }
	.monochromatic-table .number { text-align: right !important; }
	.total_row td { font-weight: bold !important; border-top: 2px solid #94a3b8 !important; border-bottom: 2px solid #94a3b8 !important; background-color: #f1f5f9 !important; }
</style>
';

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_POST['PeriodFrom'])) {
	$PeriodFrom = $_POST['PeriodFrom'];
} elseif (isset($_GET['PeriodFrom'])) {
	$PeriodFrom = $_GET['PeriodFrom'];
} else {
	$PeriodFrom = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
}

if (isset($_POST['PeriodTo'])) {
	$PeriodTo = $_POST['PeriodTo'];
} elseif (isset($_GET['PeriodTo'])) {
	$PeriodTo = $_GET['PeriodTo'];
} else {
	$PeriodTo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
}

if ($PeriodFrom > $PeriodTo) {
	prnMsg(__('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
	$_POST['Show'] = false;
}

if (isset($_POST['PrintPDF']) or isset($_POST['Show']) or isset($_POST['Spreadsheet'])) {

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
	
	// Get Net Profit for the Period (P&L accounts)
	$SQL = "SELECT SUM(amount) AS netprofit 
			FROM gltrans 
			INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode 
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname 
			WHERE periodno>='" . $PeriodFrom . "' AND periodno<='" . $PeriodTo . "' 
			AND pandl=1";
	$NetProfitResult = DB_query($SQL);
	$NetProfitRow = DB_fetch_array($NetProfitResult);
	$NetProfit = -($NetProfitRow['netprofit'] ?? 0); // Credits are positive income

	// Get Prior Years Retained Earnings (up to PeriodFrom - 1)
	$SQL = "SELECT SUM(amount) AS priorretained 
			FROM gltrans 
			INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode 
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname 
			WHERE periodno<'" . $PeriodFrom . "' 
			AND pandl=1";
	$PriorRetainedResult = DB_query($SQL);
	$PriorRetainedRow = DB_fetch_array($PriorRetainedResult);
	$PriorRetained = -($PriorRetainedRow['priorretained'] ?? 0);

	// Get Equity Accounts 
	// In webERP, Equity section is usually 50 in accountsection.
	$SQL = "SELECT accountcode, accountname 
			FROM chartmaster 
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname 
			WHERE accountgroups.sectioninaccounts=50
			ORDER BY accountcode";
	$EquityAccountsResult = DB_query($SQL);

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
					<h3>' . __('Statement of Changes in Equity') . '</h3>
					<p>' . __('From') . ' ' . MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($PeriodFrom)) . ' ' . __('to') . ' ' . MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($PeriodTo)) . '</p>
				  </div>';
	}
	if (!isset($_POST['PrintPDF']) && !isset($_POST['Spreadsheet'])) {
		$HTML .= '<div class="db-table-wrap">';
	}

	$HTML .= '<table class="monochromatic-table">
				<thead>
					<tr>
						<th>' . __('Account') . '</th>
						<th class="number">' . __('Opening Balance') . '</th>
						<th class="number">' . __('Net Profit / (Loss)') . '</th>
						<th class="number">' . __('Other Movements') . '</th>
						<th class="number">' . __('Closing Balance') . '</th>
					</tr>
				</thead>
				<tbody>';

	$TotalOpening = 0;
	$TotalNetProfit = 0;
	$TotalMovements = 0;
	$TotalClosing = 0;

	while ($MyRow = DB_fetch_array($EquityAccountsResult)) {
		$AccountCode = $MyRow['accountcode'];
		$AccountName = $MyRow['accountname'];

		// Calculate Opening Balance for this account (transactions prior to PeriodFrom)
		$SQL = "SELECT SUM(amount) AS opening FROM gltrans WHERE account='" . $AccountCode . "' AND periodno<'" . $PeriodFrom . "'";
		$OpeningResult = DB_query($SQL);
		$OpeningRow = DB_fetch_array($OpeningResult);
		$OpeningBalance = -($OpeningRow['opening'] ?? 0); // In equity, credits are positive balances

		if ($AccountCode == $RetainedEarningsAct) {
			$OpeningBalance += $PriorRetained;
		}

		// Calculate Other Period Movements for this account (transactions during the period)
		$SQL = "SELECT SUM(amount) AS movement FROM gltrans WHERE account='" . $AccountCode . "' AND periodno>='" . $PeriodFrom . "' AND periodno<='" . $PeriodTo . "'";
		$MovementResult = DB_query($SQL);
		$MovementRow = DB_fetch_array($MovementResult);
		$PeriodMovement = -($MovementRow['movement'] ?? 0);

		$PeriodProfit = 0;
		if ($AccountCode == $RetainedEarningsAct) {
			$PeriodProfit = $NetProfit;
		}

		$ClosingBalance = $OpeningBalance + $PeriodProfit + $PeriodMovement;

		if (round($OpeningBalance, 2) != 0 || round($PeriodProfit, 2) != 0 || round($PeriodMovement, 2) != 0 || round($ClosingBalance, 2) != 0) {
			$HTML .= '<tr>
						<td>' . $AccountCode . ' - ' . htmlspecialchars($AccountName) . '</td>
						<td class="number">' . locale_number_format($OpeningBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($PeriodProfit, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($PeriodMovement, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number"><strong>' . locale_number_format($ClosingBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</strong></td>
					</tr>';
			
			$TotalOpening += $OpeningBalance;
			$TotalNetProfit += $PeriodProfit;
			$TotalMovements += $PeriodMovement;
			$TotalClosing += $ClosingBalance;
		}
	}

	$HTML .= '<tr class="total_row">
				<td>' . __('Total Equity') . '</td>
				<td class="number">' . locale_number_format($TotalOpening, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($TotalNetProfit, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($TotalMovements, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($TotalClosing, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';

	$HTML .= '</tbody></table>';
	if (!isset($_POST['PrintPDF']) && !isset($_POST['Spreadsheet'])) {
		$HTML .= '</div>';
	}

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</body></html>';
		$DomPDF = new Dompdf($DomPDFOptions);
		$DomPDF->loadHtml($HTML);
		$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');
		$DomPDF->render();
		$DomPDF->stream($_SESSION['DatabaseName'] . '_StatementOfChangesInEquity_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
		exit;
	} elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="StatementOfChangesInEquity-' . date('Y-m-d') . '.ods"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$SpreadsheetHTML = '<html><head><meta charset="UTF-8"></head><body>' . $HTML . '</body></html>';
		$spreadsheet = $reader->loadFromString($SpreadsheetHTML);
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
		$writer->save('php://output');
		exit;
	}

	include(__DIR__ . '/includes/header.php');
	echo $ExtraHeadContent;
	
	echo '<div class="db-page">
			<div class="db-centered-container">
				<div class="db-page-header">
					<div class="db-header-left">
						<h1 class="db-page-title"><i class="fas fa-chart-pie"></i> ' . $Title . '</h1>
					</div>
				</div>
				<div class="db-card">
					<div class="db-card-body">
						' . $HTML . '
						<div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
							<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" target="_blank">
								<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
								<input type="hidden" name="PeriodFrom" value="' . $PeriodFrom . '" />
								<input type="hidden" name="PeriodTo" value="' . $PeriodTo . '" />
								<button type="submit" name="PrintPDF" class="db-btn db-btn-secondary"><i class="fas fa-file-pdf"></i> ' . __('Print PDF') . '</button>
								<button type="submit" name="Spreadsheet" class="db-btn db-btn-secondary"><i class="fas fa-file-excel"></i> ' . __('Spreadsheet') . '</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		  </div>';

	include(__DIR__ . '/includes/footer.php');
	exit;
}

// Show Criteria Form
include(__DIR__ . '/includes/header.php');
echo $ExtraHeadContent;

echo '<div class="db-page">
		<div class="db-centered-container" style="max-width: 800px !important;">
			<div class="db-page-header">
				<div class="db-header-left">
					<h1 class="db-page-title"><i class="fas fa-chart-pie"></i> ' . $Title . '</h1>
					<p class="db-page-subtitle">' . __('Generate the statutory Statement of Changes in Equity report') . '</p>
				</div>
			</div>
			
			<div class="db-card">
				<div class="db-card-header">
					<h3 class="db-card-title"><i class="fas fa-filter"></i> ' . __('Report Criteria') . '</h3>
				</div>
				<div class="db-card-body">
					<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">
						<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
						
						<div class="db-form-row">
							<div class="db-form-group">
								<label class="db-label">' . __('Select Period From:') . '</label>
								<select name="PeriodFrom" class="db-select">';
								$periodsql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
								$periodresult = DB_query($periodsql);
								while ($myrow = DB_fetch_array($periodresult)) {
									if ($myrow['periodno'] == $PeriodFrom) {
										echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
									} else {
										echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
									}
								}
echo '							</select>
							</div>
							
							<div class="db-form-group">
								<label class="db-label">' . __('Select Period To:') . '</label>
								<select name="PeriodTo" class="db-select">';
								$periodresult = DB_query($periodsql);
								while ($myrow = DB_fetch_array($periodresult)) {
									if ($myrow['periodno'] == $PeriodTo) {
										echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
									} else {
										echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
									}
								}
echo '							</select>
							</div>
						</div>

						<div style="display: flex; justify-content: center; margin-top: 24px;">
							<button type="submit" name="Show" class="db-btn db-btn-primary"><i class="fas fa-eye"></i> ' . __('Show Statement of Changes in Equity') . '</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	  </div>';

include(__DIR__ . '/includes/footer.php');
?>
