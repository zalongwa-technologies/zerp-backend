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
	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
	$BalanceDate = ConvertSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));

	$ThisYearRetainedEarningsRow = DB_fetch_array(DB_query("SELECT ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS retainedearnings FROM gltrans INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname WHERE periodno<='" . $_POST['PeriodTo'] . "' AND pandl=1"));
	$LastYearRetainedEarningsRow = DB_fetch_array(DB_query("SELECT ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS retainedearnings FROM gltrans INNER JOIN chartmaster ON gltrans.account=chartmaster.accountcode INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname WHERE periodno<='" . ($_POST['PeriodTo'] - 12) . "' AND pandl=1"));

	$AccountListResult = DB_query("SELECT sectionid, sectionname, sectioninaccounts, parentgroupname, chartmaster.accountcode, group_, accountname, pandl FROM chartmaster INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1 INNER JOIN accountgroups ON accountgroups.groupname=chartmaster.group_ INNER JOIN accountsection ON accountsection.sectionid=accountgroups.sectioninaccounts WHERE pandl=0 ORDER BY sequenceintb, group_, accountcode");
	
    $ResultActual = DB_query("SELECT account, ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal FROM gltrans WHERE periodno<='" . $_POST['PeriodTo'] . "' GROUP BY account");
	while ($R = DB_fetch_array($ResultActual)) $ThisYearActuals[$R['account']] = $R['accounttotal'];
	$ResultLY = DB_query("SELECT account, ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal FROM gltrans WHERE periodno<='" . ($_POST['PeriodTo'] - 12) . "' GROUP BY account");
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
					<p>' . __('As at') . ' ' . $BalanceDate . '</p>
				  </div>';
    } else {
		$HTML .= '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
		$HTML .= '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		$HTML .= '<input type="hidden" name="PeriodTo" value="' . $_POST['PeriodTo'] . '" />';
        $HTML .= '<input type="hidden" name="ShowDetail" value="' . $_POST['ShowDetail'] . '" />';
        if (isset($_POST['ShowZeroBalance'])) {
            $HTML .= '<input type="hidden" name="ShowZeroBalance" value="1" />';
        }
    }

	$HTML .= '<div class="db-table-wrap"><table class="monochromatic-table">
				<thead>';
	if (!isset($_POST['PrintPDF'])) {
		$HTML .= '	<tr>
						<th colspan="4" style="background:#f1f5f9; color:#111827; text-align:center;">
							<b>' . $Title . ' ' . __('As at') . ' ' . $BalanceDate . '</b>
						</th>
					</tr>';
	}
	$HTML .= '		<tr>';
	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<th>' . __('Account') . '</th><th>' . __('Account Name') . '</th><th class="number">' . $BalanceDate . '</th><th class="number">' . __('Last Year') . '</th>';
	} else {
		$HTML .= '<th colspan="2"></th><th class="number">' . $BalanceDate . '</th><th class="number">' . __('Last Year') . '</th>';
	}
	$HTML .= '		</tr>
				</thead>
				<tbody>';

	$Section = ''; $SectionBalance = 0; $SectionBalanceLY = 0; $LYCheckTotal = 0; $CheckTotal = 0; $ActGrp = ''; $Level = 0; $ParentGroups = array(); $ParentGroups[$Level] = ''; $GroupTotal = array(0); $LYGroupTotal = array(0);

	while ($MyRow = DB_fetch_array($AccountListResult)) {
		$AccountBalance = $ThisYearActuals[$MyRow['accountcode']] ?? 0;
		$LYAccountBalance = $LastYearActuals[$MyRow['accountcode']] ?? 0;
		if ($MyRow['accountcode'] == $RetainedEarningsAct) { $AccountBalance = $ThisYearRetainedEarningsRow['retainedearnings']; $LYAccountBalance = $LastYearRetainedEarningsRow['retainedearnings']; }

		if ($MyRow['group_'] != $ActGrp and $ActGrp != '') {
			if ($MyRow['parentgroupname'] != $ActGrp) {
				while ($MyRow['group_'] != $ParentGroups[$Level] and $Level > 0) {
					$lbl = str_repeat('&nbsp;&nbsp;', $Level) . $ParentGroups[$Level];
					$HTML .= '<tr><td colspan="2"><i>' . $lbl . '</i></td><td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
					$GroupTotal[$Level] = 0; $LYGroupTotal[$Level] = 0; $ParentGroups[$Level] = ''; $Level--;
				}
				$HTML .= '<tr class="total_row"><td colspan="2">' . $ParentGroups[$Level] . '</td><td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
				$GroupTotal[$Level] = 0; $LYGroupTotal[$Level] = 0; $ParentGroups[$Level] = '';
			}
		}

		if ($MyRow['sectionid'] != $Section) {
			if ($Section != '') {
				$HTML .= '<tr class="section_row"><td colspan="2">' . $Sections[$Section] . '</td><td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
			}
			$SectionBalanceLY = 0; $SectionBalance = 0; $Section = $MyRow['sectionid'];
			if ($_POST['ShowDetail'] == 'Detailed') $HTML .= '<tr style="background:#f8fafc;"><td colspan="4"><b>' . $Sections[$MyRow['sectionid']] . '</b></td></tr>';
		}

		if ($MyRow['group_'] != $ActGrp) {
			if ($ActGrp != '' and $MyRow['parentgroupname'] == $ActGrp) {
				$Level++;
				if (!isset($GroupTotal[$Level])) $GroupTotal[$Level] = 0;
				if (!isset($LYGroupTotal[$Level])) $LYGroupTotal[$Level] = 0;
			}
			$ActGrp = $MyRow['group_']; $ParentGroups[$Level] = $MyRow['group_'];
			if ($_POST['ShowDetail'] == 'Detailed') $HTML .= '<tr><td colspan="4" style="padding-left:' . (20*$Level) . 'px; font-weight:700;">' . $MyRow['group_'] . '</td></tr>';
		}

		$SectionBalanceLY+= $LYAccountBalance; $SectionBalance+= $AccountBalance;
		for ($i = 0;$i <= $Level;$i++) { $LYGroupTotal[$i]+= $LYAccountBalance; $GroupTotal[$i]+= $AccountBalance; }
		$LYCheckTotal+= $LYAccountBalance; $CheckTotal+= $AccountBalance;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance']) or (round($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) != 0 or round($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) != 0)) {
				$HTML .= '<tr style="border-bottom:1px solid #f1f5f9; opacity:0.85;">
                    <td style="padding-left:' . (25+($Level*15)) . 'px;">' . $MyRow['accountcode'] . '</td>
                    <td>' . htmlspecialchars($MyRow['accountname']) . '</td>
                    <td class="number">' . locale_number_format($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
                    <td class="number">' . locale_number_format($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
                </tr>';
			}
		}
		$Group = $MyRow['group_']; $SectionInAccounts = $MyRow['sectioninaccounts'];
	}
	
	while ($Group != $ParentGroups[$Level] and $Level > 0) {
		$HTML .= '<tr><td colspan="2"><i>' . $ParentGroups[$Level] . '</i></td><td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
		$Level--;
	}
	$HTML .= '<tr class="total_row"><td colspan="2">' . $ParentGroups[$Level] . '</td><td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
	$HTML .= '<tr class="section_row"><td colspan="2">' . $Sections[$Section] . '</td><td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
	$HTML .= '<tr class="check_totals_row"><td colspan="2">' . __('Check Total') . '</td><td class="number">' . locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td class="number">' . locale_number_format($LYCheckTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
	$HTML .= '</tbody></table></div>';

	if (isset($_POST['PrintPDF'])) {
		$DomPDF = new Dompdf($DomPDFOptions); $DomPDF->loadHtml($HTML); $DomPDF->setPaper($_SESSION['PageSize'], 'portrait'); $DomPDF->render();
		$DomPDF->stream($_SESSION['DatabaseName'] . '_Balance_Sheet_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
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
				.number { text-align: right; }
				.total_row td { font-weight: bold; border-top: 1px solid #000; }
				.section_row td { font-weight: bold; background-color: #d1fae5; color: #065f46; }
				.check_totals_row td { font-weight: bold; background-color: #ecfdf5; color: #064e3b; }
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
    .db-page { width: 100% !important; max-width: 100vw !important; overflow-x: hidden !important; box-sizing: border-box !important; padding: 20px; }
	.db-centered-container { width: 100% !important; max-width: 1350px !important; margin: 0 auto; box-sizing: border-box !important; }
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
                        <input type="hidden" name="PeriodTo" value="' . $_POST['PeriodTo'] . '" />
                        <input type="hidden" name="ShowDetail" value="' . $_POST['ShowDetail'] . '" />
                        <input type="hidden" name="ShowZeroBalance" value="' . (isset($_POST['ShowZeroBalance']) ? 1 : 0) . '" />
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
