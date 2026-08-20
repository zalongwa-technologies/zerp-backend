<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include(__DIR__ . '/includes/SetDomPDFOptions.php');
use Dompdf\Options;

$Title = __('Stock Location Transfer Docket Error');

if (isset($_POST['TransferNo'])) {
	$_GET['TransferNo'] = $_POST['TransferNo'];
}

if (isset($_GET['TransferNo'])) {

	$ErrMsg = __('An error occurred retrieving the items on the transfer') . '.' . '<p>' . __('This page must be called with a location transfer reference number') . '.';
	$SQL = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.description,
			   loctransfers.shipqty,
			   loctransfers.recqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname,
			   stockmaster.decimalplaces
		FROM loctransfers
		INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
		INNER JOIN locations ON loctransfers.shiploc=locations.loccode
		INNER JOIN locations AS locationsrec ON loctransfers.recloc = locationsrec.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		INNER JOIN locationusers as locationusersrec ON locationusersrec.loccode=locationsrec.loccode AND locationusersrec.userid='" . $_SESSION['UserID'] . "' AND locationusersrec.canview=1
		WHERE loctransfers.reference='" . $_GET['TransferNo'] . "'";

	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {

		include ('includes/header.php');
		prnMsg(__('The transfer reference selected does not appear to be set up') . ' - ' . __('enter the items to be transferred first'), 'error');
		include ('includes/footer.php');
		exit();
	}

	// Prepare data for HTML template
	$transfers = [];
	while ($row = DB_fetch_array($Result)) {
		$transfers[] = $row;
	}

	// Compose HTML for PDF

	$HTML = '
<style>
	body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 10px; }
	.header { border-bottom: 2px solid #239d58; padding-bottom: 20px; margin-bottom: 30px; }
	.header table { width: 100%; border: none; }
	.header td { border: none; padding: 0; vertical-align: top; }
	.logo { max-height: 60px; }
	h1 { color: #239d58; font-size: 20pt; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 1px; text-align: right; }
	.meta-info { text-align: right; font-size: 10pt; color: #555; line-height: 1.6; }
	.meta-info strong { color: #222; }
	
	.locations-wrap { margin-bottom: 30px; width: 100%; }
	.locations-table { width: 100%; border: none; border-collapse: separate; border-spacing: 20px 0; margin-left: -20px; margin-right: -20px; }
	.locations-table td { border: none; padding: 0; width: 50%; vertical-align: top; }
	.loc-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
	.loc-title { font-size: 8pt; text-transform: uppercase; color: #239d58; font-weight: bold; margin-bottom: 5px; letter-spacing: 0.5px; }
	.loc-name { font-size: 12pt; font-weight: bold; color: #333; }
	
	.items-table { border-collapse: collapse; width: 100%; margin-top: 20px; }
	.items-table th { background-color: #239d58; color: #ffffff; text-transform: uppercase; font-size: 9pt; letter-spacing: 0.5px; padding: 12px; text-align: left; border: none; }
	.items-table td { border-bottom: 1px solid #eee; padding: 12px; color: #444; border-left: none; border-right: none; }
	.items-table tr:last-child td { border-bottom: 2px solid #239d58; }
	.items-table tr:nth-child(even) { background-color: #fbfbfb; }
	.qty-cell { text-align: right; font-weight: bold; color: #239d58; }
	
	.footer { margin-top: 50px; text-align: center; font-size: 9pt; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
</style>
';
	
	$HTML .= '<div class="header">
		<table>
			<tr>
				<td style="width: 50%;"><img class="logo" src="' . $_SESSION['LogoFile'] . '" /></td>
				<td style="width: 50%;">
					<h1>' . __('Transfer Docket') . '</h1>
					<div class="meta-info">
						<strong>' . __('Reference #') . ':</strong> ' . htmlspecialchars($_GET['TransferNo']) . '<br>
						<strong>' . __('Date') . ':</strong> ' . htmlspecialchars($transfers[0]['shipdate']) . '
					</div>
				</td>
			</tr>
		</table>
	</div>';
	
	$HTML .= '<div class="locations-wrap">
		<table class="locations-table">
			<tr>
				<td>
					<div class="loc-box">
						<div class="loc-title">' . __('Origin Location') . '</div>
						<div class="loc-name">' . htmlspecialchars($transfers[0]['shiplocname']) . '</div>
					</div>
				</td>
				<td>
					<div class="loc-box">
						<div class="loc-title">' . __('Destination Location') . '</div>
						<div class="loc-name">' . htmlspecialchars($transfers[0]['reclocname']) . '</div>
					</div>
				</td>
			</tr>
		</table>
	</div>';

	$HTML .= '<table class="items-table">
		<thead>
			<tr>
				<th>' . __('Stock ID') . '</th>
				<th>' . __('Description') . '</th>
				<th style="text-align:right;">' . __('Ship Qty') . '</th>
				<th style="text-align:right;">' . __('Receive Qty') . '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($transfers as $item) {
		$HTML .= '<tr>
			<td><strong>' . htmlspecialchars($item['stockid']) . '</strong></td>
			<td>' . htmlspecialchars($item['description']) . '</td>
			<td class="qty-cell">' . locale_number_format($item['shipqty'], $item['decimalplaces']) . '</td>
			<td class="qty-cell">' . locale_number_format($item['recqty'], $item['decimalplaces']) . '</td>
		</tr>';
	}

	$HTML .= '</tbody></table>';
	$HTML .= '<div class="footer">' . __('Generated by ZERP Inventory Management') . '</div>';


	// Generate PDF using DomPDF
	// Setup DomPDF
	$FileName = $_SESSION['DatabaseName'] . '_StockLocTransfer_' . date('Y-m-d H-m-s') . '.pdf';
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$DomPDF->render();

	// Clear any accidental output that could corrupt the PDF stream
	if (ob_get_length()) {
		ob_end_clean();
	}

	// Output the generated PDF to Browser
	$DomPDF->stream($FileName, array("Attachment" => false));

} else {

	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Reprint transfer docket') . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Transfer Docket Criteria'), '</legend>';
	echo '<fieldset>
			<field>
				<label for="TransferNo">' . __('Transfer docket to reprint') . '</label>
				<input type="text" class="number" size="10" name="TransferNo" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print') . '" />
		</div>';
	echo '</form>';

	echo '<form method="post" action="' . $RootPath . '/PDFShipLabel.php">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Type" value="Transfer" />';
	echo '<fieldset>
			<field>
				<label for="ORD">' . __('Transfer docket to reprint Shipping Labels') . '</label>
				<input type="text" class="number" size="10" name="ORD" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print Shipping Labels') . '" />
		</div>';
	echo '</fieldset>';
	echo '</form>';

	include ('includes/footer.php');
	exit();
}
