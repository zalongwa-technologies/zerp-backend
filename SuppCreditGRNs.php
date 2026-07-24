<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include_once(__DIR__ . '/includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

if (!isset($_SESSION['SuppTrans'])){
	$Title = __('Enter Supplier Credit Note Against Goods Received');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include(__DIR__ . '/includes/header.php');
	prnMsg(__('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier credit note must be clicked on'),'info');
	echo '<br />
		<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select A Supplier to Enter a Transaction For') . '</a>';
	include(__DIR__ . '/includes/footer.php');
	exit();
	/*It all stops here if there aint no supplier selected and credit note initiated ie $_SESSION['SuppTrans'] started off*/
}

$Title = __('Enter Supplier Credit Note Against Goods Received');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include(__DIR__ . '/includes/header.php');

if (isset($_POST['Show_Since'])){$_POST['Show_Since'] = ConvertSQLDate($_POST['Show_Since']);}

echo '<div class="db-page">';
	echo '<style>
		.db-aside-btn {
			width: 100%;
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 10px 12px;
			border-radius: var(--radius-md);
			border: 1px solid transparent;
			background: transparent;
			color: var(--text-body);
			font-size: 0.875rem;
			font-weight: 500;
			cursor: pointer;
			transition: all var(--transition-fast);
			text-align: left;
		}
		.db-aside-btn:hover {
			background: var(--primary-soft);
			color: var(--primary);
			border-color: var(--primary-subtle);
		}
		.db-aside-btn i {
			width: 20px;
			text-align: center;
			color: var(--primary);
			font-size: 1rem;
		}
		.registry-table { width: 100%; border-collapse: separate; border-spacing: 0; }
		.registry-table th { background: #064e3b; padding: 12px 15px; text-align: left; font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: #fff; letter-spacing: 1px; }
		.registry-table td { padding: 12px 15px; font-size: 0.88rem; color: var(--text-body); border-bottom: 1px solid var(--border-soft); }
		.registry-table tr:nth-child(even) td { background: var(--bg-workspace); }
		.registry-table tr:hover td { background: var(--primary-soft) !important; }
		.db-field { margin-bottom: var(--space-4); }
		.db-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
	</style>';

	echo '<div class="db-page-header">
		<div>
			<h2 class="db-page-title"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="db-title-icon"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> ' . $Title . '</h2>
			<p class="db-page-subtitle">' . __('Selecting Goods Received for') . ' <span class="val-bold">' . $_SESSION['SuppTrans']->SupplierID . ' - ' . $_SESSION['SuppTrans']->SupplierName . '</span></p>
		</div>
		<div class="db-header-actions">
			<a href="' . $RootPath . '/SupplierCredit.php" class="db-btn db-btn-secondary">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path></svg>
				' . __('Back to Credit Note') . '
			</a>
		</div>
	</div>';

/*If the user hit the Add to Credit Note button then process this first before showing all GRNs on the credit note otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGRNToTrans'])){

	$InputError=false;

	$Complete = false;
        // Validate Credit Quantity to prevent from credit quantity more than quantity invoiced
	if (!is_numeric(filter_number_format($_POST['This_QuantityCredited']))
		or ($_POST['Prev_QuantityInv'] - filter_number_format($_POST['This_QuantityCredited']))<0){

		$InputError = true;
		prnMsg(__('The credit quantity is not numeric or the quantity to credit is more that quantity invoiced') . '. ' . __('The goods received cannot be credited by this quantity'),'error');
		}

	if (!is_numeric(filter_number_format($_POST['ChgPrice']))
		or filter_number_format($_POST['ChgPrice'])<0){

		$InputError = true;
		prnMsg(__('The price charged in the suppliers currency is either not numeric or negative') . '. ' . __('The goods received cannot be credited at this price'),'error');
	}

	if ($InputError==false){

		$_SESSION['SuppTrans']->Add_GRN_To_Trans($_POST['GRNNumber'],
												$_POST['PODetailItem'],
												$_POST['ItemCode'],
												$_POST['ItemDescription'],
												$_POST['QtyRecd'],
												$_POST['Prev_QuantityInv'],
												filter_number_format($_POST['This_QuantityCredited']),
												$_POST['OrderPrice'],
												filter_number_format($_POST['ChgPrice']),
												$Complete,
												$_POST['StdCostUnit'],
												$_POST['ShiptRef'],
												$_POST['JobRef'],
												$_POST['GLCode'],
												$_POST['PONo'],
												$_POST['AssetID'],
												0,
												$_POST['DecimalPlaces'],
												$_POST['GRNBatchNo'],
												$_SESSION['SuppTrans']->SuppReference);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);

}

/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<div class="db-bottom-layout">';

// --- SIDEBAR START ---
echo '<aside class="db-col-aside">';

// Card 1: Supplier Context
echo '<div class="db-card" style="margin-bottom: var(--space-4);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-user-tag db-icon-green"></i> ' . __('Supplier Context') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-4);">
			<div style="font-size: 1.1rem; font-weight: 700; color: var(--db-primary);">' . $_SESSION['SuppTrans']->SupplierName . '</div>
			<div style="font-family: monospace; color: var(--text-muted); margin-bottom: var(--space-3);">[' . $_SESSION['SuppTrans']->SupplierID . ']</div>
			<div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 4px;">
				<div><span class="db-muted">' . __('Currency') . ':</span> <span class="val-bold">' . $_SESSION['SuppTrans']->CurrCode . '</span></div>
			</div>
		</div>
	</div>';

// Calculate Totals for Sidebar
$TotalValueCharged = 0;
foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
	$TotalValueCharged += ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);
}

// Card 2: Live Summary
echo '<div class="db-card" style="position: sticky; top: var(--space-4);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-calculator"></i> ' . __('Credit Summary') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-4);">
			<div style="display: flex; flex-direction: column; gap: var(--space-3);">
				<div style="display: flex; justify-content: space-between;">
					<span class="db-muted">' . __('Goods Credited') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalValueCharged, $_SESSION['SuppTrans']->CurrDecimalPlaces) . '</span>
				</div>
				<div style="margin: var(--space-2) 0; height: 1px; background: var(--border-soft);"></div>
				<div style="display: flex; justify-content: space-between; font-size: 1.2rem; color: var(--db-primary);">
					<span class="val-bold">' . __('Grand Total') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalValueCharged, $_SESSION['SuppTrans']->CurrDecimalPlaces) . ' ' . $_SESSION['SuppTrans']->CurrCode . '</span>
				</div>
			</div>
			<div style="margin-top: var(--space-6);">
				<a href="' . $RootPath . '/SupplierCredit.php" class="db-btn db-btn-primary" style="width: 100%; height: 44px; justify-content: center; font-size: 1rem;">
					<i class="fas fa-arrow-left"></i> ' . __('Back to Credit Note') . '
				</a>
			</div>
		</div>
	</div>';

echo '</aside>';
// --- SIDEBAR END ---

// --- MAIN CONTENT START ---
echo '<main class="db-col-main" style="flex: 1; min-width: 0;">';

echo '<div class="db-card">
		<div class="db-card-header">
			<h3 class="db-card-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> ' . __('Credits Against Goods Received Selected') . '</h3>
		</div>
		<div class="db-card-body" style="padding: 0;">';

if (count($_SESSION['SuppTrans']->GRNs) > 0) {
	echo '<table class="registry-table">
			<thead>
			<tr>
				<th>' . __('GRN') . '</th>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Description') . '</th>
				<th class="number">' . __('Quantity Credited') . '</th>
				<th class="number">' . __('Price Credited') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
				<th class="number">' . __('Line Value') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>';

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		if ($EnteredGRN->ChgPrice > 1) {
			$DisplayPrice = locale_number_format($EnteredGRN->ChgPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces);
		} else {
			$DisplayPrice = locale_number_format($EnteredGRN->ChgPrice,4);
		}

		echo '<tr>
				<td>' . $EnteredGRN->GRNNo . '</td>
				<td>' . $EnteredGRN->ItemCode . '</td>
				<td>' . $EnteredGRN->ItemDescription . '</td>
				<td class="number">' . locale_number_format($EnteredGRN->This_QuantityInv,$EnteredGRN->DecimalPlaces) . '</td>
				<td class="number">' . $DisplayPrice . '</td>
				<td class="number">' . locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredGRN->GRNNo . '" style="color:#ef4444;"><i class="fas fa-times"></i></a></td>
			</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<div style="padding: var(--space-6); text-align: center; color: var(--text-muted);">' . __('No goods received records selected for credit yet.') . '</div>';
}

echo '  </div>
	  </div>';

/* Now get all the GRNs for this supplier from the database
after the date entered */
if (!isset($_POST['Show_Since'])){
	$_POST['Show_Since'] =  date($_SESSION['DefaultDateFormat'],mktime(0,0,0,date('m')-2,date('d'),date('Y')));
}

$SQL = "SELECT grnno,
			purchorderdetails.orderno,
			purchorderdetails.unitprice,
			purchorderdetails.actprice,
			grns.itemcode,
			grns.deliverydate,
			grns.itemdescription,
			grns.qtyrecd,
			grns.quantityinv,
			purchorderdetails.stdcostunit,
			purchorderdetails.assetid,
			stockmaster.decimalplaces
		FROM grns INNER JOIN purchorderdetails
		ON grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster
		ON purchorderdetails.itemcode=stockmaster.stockid
		WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
		AND grns.deliverydate >= '" . FormatDateForSQL($_POST['Show_Since']) . "'
		ORDER BY grns.grnno";
$GRNResults = DB_query($SQL);

if (DB_num_rows($GRNResults)==0){
	prnMsg(__('There are no goods received records for') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . __('since') . ' ' . $_POST['Show_Since'] . '<br /> ' . __('To enter a credit against goods received') . ', ' . __('the goods must first be received using the link below to select purchase orders to receive'),'info');
	echo '<div style="margin-top: 15px; text-align: center; margin-bottom: 20px;">
		<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SupplierID=' . $_SESSION['SuppTrans']->SupplierID . '" class="db-btn db-btn-primary">' . __('Select Purchase Orders to Receive') . '</a>
	</div>';
}

echo '<div class="db-card" style="margin-top: var(--space-6);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-filter"></i> ' . __('Search Options') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-6);">
			<div style="display: flex; align-items: flex-end; gap: var(--space-4);">
				<div class="db-field" style="margin-bottom: 0; flex: 1;">
					<label class="db-label">' . __('Show Goods Received Since') . '</label>
					<input name="Show_Since" maxlength="11" size="12" type="date" value="' . FormatDateForSQL($_POST['Show_Since']) . '" style="width: 100%;" />
				</div>
				<button type="submit" name="FindGRNs" class="db-btn db-btn-secondary" style="height: var(--space-10);">' . __('Display GRNs') . '</button>
			</div>
		</div>
	  </div>';

if (DB_num_rows($GRNResults)>0){
	echo '<div class="db-card" style="margin-top: var(--space-6);">
			<div class="db-card-header">
				<h3 class="db-card-title"><i class="fas fa-list"></i> ' . __('Outstanding Goods Received') . '</h3>
			</div>
			<div class="db-card-body" style="padding: 0;">
				<table class="registry-table">
					<thead>
						<tr>
							<th>' . __('Select') . '</th>
							<th>' . __('Order') . '</th>
							<th>' . __('Item Code') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Delivered') . '</th>
							<th class="number">' . __('Total Received') . '</th>
							<th class="number">' . __('Qty Invoiced') . '</th>
							<th class="number">' . __('Qty Yet Invoice') . '</th>
							<th class="number">' . __('Price') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
							<th class="number">' . __('Line Value') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
						</tr>
					</thead>
					<tbody>';

	while ($MyRow=DB_fetch_array($GRNResults)){

		$GRNAlreadyOnCredit = false;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			if ($EnteredGRN->GRNNo == $MyRow['grnno']) {
				$GRNAlreadyOnCredit = true;
			}
		}
		if ($GRNAlreadyOnCredit == false){

			if ($MyRow['actprice']<>0){
				$Price = $MyRow['actprice'];
			} else {
				$Price = $MyRow['unitprice'];
			}
			if ($MyRow['decimalplaces']==''){
				$MyRow['decimalplaces'] =2;
			}

			if ($Price > 1) {
				$DisplayPrice = locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces);
			} else {
				$DisplayPrice = locale_number_format($Price,4);
			}

			echo '<tr>
					<td><button type="submit" name="GRNNo" value="' . $MyRow['grnno'] . '" class="db-btn db-btn-secondary" style="padding: 4px 8px; font-size: 0.75rem;">' . $MyRow['grnno'] . '</button></td>
					<td>' . $MyRow['orderno'] . '</td>
					<td>' . $MyRow['itemcode'] . '</td>
					<td>' . $MyRow['itemdescription'] . '</td>
					<td>' . ConvertSQLDate($MyRow['deliverydate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyrecd'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['quantityinv'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyrecd'] - $MyRow['quantityinv'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . $DisplayPrice . '</td>
					<td class="number">' . locale_number_format($Price*($MyRow['qtyrecd'] - $MyRow['quantityinv']),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';
		}
	} // end loop.

	echo '</tbody></table>
		  </div>
		</div>';
}

if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=''){

	$SQL = "SELECT grnno,
					grns.grnbatch,
					grns.podetailitem,
					purchorderdetails.orderno,
					purchorderdetails.unitprice,
					purchorderdetails.actprice,
					purchorderdetails.glcode,
					grns.itemcode,
					grns.deliverydate,
					grns.itemdescription,
					grns.quantityinv,
					grns.qtyrecd,
					grns.qtyrecd - grns.quantityinv
					AS qtyostdg,
					purchorderdetails.stdcostunit,
					purchorderdetails.shiptref,
					purchorderdetails.jobref,
					shipments.closed,
					purchorderdetails.assetid,
					stockmaster.decimalplaces
			FROM grns INNER JOIN purchorderdetails
			ON grns.podetailitem=purchorderdetails.podetailitem
			LEFT JOIN shipments ON purchorderdetails.shiptref=shipments.shiptref
			LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
			WHERE grns.grnno='" .$_POST['GRNNo'] . "'";

	$GRNEntryResult = DB_query($SQL);
	$MyRow = DB_fetch_array($GRNEntryResult);

	if ($MyRow['actprice']<>0){
		$Price = $MyRow['actprice'];
	} else {
		$Price = $MyRow['unitprice'];
	}
	if ($MyRow['decimalplaces']==''){
		$MyRow['decimalplaces'] =2;
	}
	if ($Price > 1) {
		$DisplayPrice = locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	} else {
		$DisplayPrice = locale_number_format($Price,4);
	}

	echo '<div class="db-card" style="margin-top: var(--space-6);">';
	echo '	<div class="db-card-header">
				<h3 class="db-card-title"><i class="fas fa-plus-circle"></i> ' . __('GRN Selected For Adding To Suppliers Credit Note') . '</h3>
			</div>';
	echo '	<div class="db-card-body" style="padding: var(--space-6);">';

	if ($MyRow['closed']==1){ /*Shipment is closed so warn the user */
		echo '<input type="hidden" name="ShiptRef" value="" />';
		prnMsg(__('Unfortunately the shipment that this purchase order line item was allocated to has been closed') . ' - ' . __('if you add this item to the transaction then no shipments will not be updated') . '. ' . __('If you wish to allocate the order line item to a different shipment the order must be modified first'),'error');
	} else {
		echo '<input type="hidden" name="ShiptRef" value="' . $MyRow['shiptref'] . '" />';
	}

	echo '	<div class="db-grid db-grid-3">
				<div class="db-field">
					<label class="db-label">' . __('GRN Number') . '</label>
					<div class="val-bold" style="padding: 10px 0;">' . $_POST['GRNNo'] . '</div>
				</div>
				<div class="db-field" style="grid-column: span 2;">
					<label class="db-label">' . __('Item') . '</label>
					<div style="padding: 10px 0;">' . $MyRow['itemcode'] . ' - ' . $MyRow['itemdescription'] . '</div>
				</div>
				<div class="db-field">
					<label class="db-label">' . __('Quantity Outstanding') . '</label>
					<div class="val-bold" style="padding: 10px 0;">' . locale_number_format($MyRow['qtyostdg'],$MyRow['decimalplaces']) . '</div>
				</div>
				<div class="db-field">
					<label class="db-label">' . __('Quantity Credited') . '</label>
					<input type="text" class="number" name="This_QuantityCredited" value="' . locale_number_format($MyRow['qtyostdg'],$MyRow['decimalplaces']) . '" style="width: 100%;" />
				</div>
				<div class="db-field">
					<label class="db-label">' . __('Supplier Price') . '</label>
					<div style="padding: 10px 0;">' . $DisplayPrice . ' ' . $_SESSION['SuppTrans']->CurrCode . '</div>
				</div>
				<div class="db-field">
					<label class="db-label">' . __('Credit Price') . '</label>
					<input type="text" class="number" name="ChgPrice" value="' . locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" style="width: 100%;" />
				</div>
			</div>';

	echo '	<div style="margin-top: var(--space-6); text-align: right;">
				<button type="submit" name="AddGRNToTrans" class="db-btn db-btn-primary">' . __('Add to Credit Note') . '</button>
			</div>
		  </div>
		</div>';

	echo '<input type="hidden" name="GRNNumber" value="' . $_POST['GRNNo'] . '" />';
	echo '<input type="hidden" name="ItemCode" value="' . $MyRow['itemcode'] . '" />';
	echo '<input type="hidden" name="ItemDescription" value="' . $MyRow['itemdescription'] . '" />';
	echo '<input type="hidden" name="QtyRecd" value="' . $MyRow['qtyrecd'] . '" />';
	echo '<input type="hidden" name="Prev_QuantityInv" value="' . $MyRow['quantityinv'] . '" />';
	echo '<input type="hidden" name="OrderPrice" value="' . $MyRow['unitprice'] . '" />';
	echo '<input type="hidden" name="StdCostUnit" value="' . $MyRow['stdcostunit'] . '" />';

	echo '<input type="hidden" name="JobRef" value="' . $MyRow['jobref'] . '" />';
	echo '<input type="hidden" name="GLCode" value="' . $MyRow['glcode'] . '" />';
	echo '<input type="hidden" name="PODetailItem" value="' . $MyRow['podetailitem'] . '" />';
	echo '<input type="hidden" name="PONo" value="' . $MyRow['orderno'] . '" />';
	echo '<input type="hidden" name="AssetID" value="' . $MyRow['assetid'] . '" />';
	echo '<input type="hidden" name="DecimalPlaces" value="' . $MyRow['decimalplaces'] . '" />';
	echo '<input type="hidden" name="GRNBatchNo" value="' . $MyRow['grnbatch'] . '" />';
}

echo '</main>
      </div><!-- .db-bottom-layout -->
      </form>
      </div><!-- .db-page -->';

include(__DIR__ . '/includes/footer.php');
