<?php

/* Inventory Transfer - Bulk Dispatch */

require(__DIR__ . '/includes/session.php');

$Title = __('Inventory Location Transfer Shipment');
$BookMark = "LocationTransfers";
$ViewTopic = "Inventory";
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/StockFunctions.php');
include_once(__DIR__ . '/includes/UIComponents.php');

$LinesCounter = 0;

if (isset($_POST['Submit'])){
/*Trap any errors in input */

	$InputError = false; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$Result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'");
	if (DB_num_rows($Result)!=0){
		$InputError = true;
		$ErrorMessage = __('This transaction has already been entered') . '. ' . __('Please start over now') . '<br />';
		unset($_POST['submit']);
	}  else {
	  if (isset($_FILES['SelectedTransferFile']) && $_FILES['SelectedTransferFile']['name']) { //start file processing
	  	//initialize
	   	$InputError = false;
		$ErrorMessage='';
		//get file handle
		$FileHandle = fopen($_FILES['SelectedTransferFile']['tmp_name'], 'r');
		$TotalItems=0;
		//loop through file rows
		while ( ($MyRow = fgetcsv($FileHandle, 10000, ',')) !== false ) {

			if (count($MyRow) != 2){
				prnMsg(__('File contains') . ' '. count($MyRow) . ' ' . __('columns, but only 2 columns are expected. The comma separated file should have just two columns the first for the item code and the second for the quantity to transfer'),'error');
				fclose($FileHandle);
				include(__DIR__ . '/includes/footer.php');
				exit();
			}

			// cleanup the data (csv files often import with empty strings and such)
			$StockID='';
			$Quantity=0;
			for ($i=0; $i<count($MyRow);$i++) {
				switch ($i) {
					case 0:
						$StockID = trim(mb_strtoupper($MyRow[$i]));
						$Result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $StockID . "'");
						$StockIDCheck = DB_fetch_row($Result);
						if ($StockIDCheck[0]==0){
							$InputError = true;
							$ErrorMessage .= __('The part code entered of'). ' ' . $StockID . ' '. __('is not set up in the database') . '. ' . __('Only valid parts can be entered for transfers'). '<br />';
						}
						break;
					case 1:
						$Quantity = filter_number_format($MyRow[$i]);
						if (!is_numeric($Quantity)){
						   $InputError = true;
						   $ErrorMessage .= __('The quantity entered for'). ' ' . $StockID . ' ' . __('of') . $Quantity . ' '. __('is not numeric.') . __('The quantity entered for transfers is expected to be numeric');
						}
						break;
				} // end switch statement
				if ($_SESSION['ProhibitNegativeStock']==1){
					$InTransitQuantity = GetItemQtyInTransitFromLocation($StockID, $_POST['FromStockLocation']);
					// Only if stock exists at this location
					$Result = DB_query("SELECT quantity
										FROM locstock
										WHERE stockid='" . $StockID . "'
										AND loccode='".$_POST['FromStockLocation']."'");
					$CheckStockRow = DB_fetch_array($Result);
					if (($CheckStockRow['quantity']-$InTransitQuantity) < $Quantity){
						$InputError = true;
						$ErrorMessage .= __('The item'). ' ' . $StockID . ' ' . __('does not have enough stock available (') . ' ' . $CheckStockRow['quantity'] . ')' . ' ' . __('The quantity required to transfer was') .  ' ' . $Quantity . '.<br />';
					}
				}
			} // end for loop through the columns on the row being processed
			if ($StockID!='' AND $Quantity!=0){
				$_POST['StockID' . $TotalItems] = $StockID;
				$_POST['StockQTY' . $TotalItems] = $Quantity;
				$StockID='';
				$Quantity=0;
				$TotalItems++;
			}
		  } //end while there are lines in the CSV file
		  $_POST['LinesCounter']=$TotalItems;
	   } //end if there is a CSV file to import
		  else { // process the manually input lines
			$ErrorMessage='';

			if (isset($_POST['ClearAll'])){
				$_POST['LinesCounter'] = 0;
			}
			$StockIDAccQty = array(); //set an array to hold all items' quantity
			for ($i=0; $i < $_POST['LinesCounter']; $i++){
				if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
					$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
					$Result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'");
					$MyRow = DB_fetch_row($Result);
					if ($MyRow[0]==0){
						$InputError = true;
						$ErrorMessage .= __('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. __('is not set up in the database') . '. ' . __('Only valid parts can be entered for transfers'). '<br />';
					}
					DB_free_result( $Result );
					if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
						$InputError = true;
						$ErrorMessage .= __('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. __('for part code'). ' ' . $_POST['StockID' . $i] . ' '. __('is not numeric') . '. ' . __('The quantity entered for transfers is expected to be numeric') . '<br />';
					}
					if (filter_number_format($_POST['StockQTY' . $i]) <= 0){
						$InputError = true;
						$ErrorMessage .= __('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . __('is less than or equal to 0') . '. ' . __('Please correct this or remove the item') . '<br />';
					}
					if ($_SESSION['ProhibitNegativeStock']==1){
						$InTransitQuantity = GetItemQtyInTransitFromLocation($_POST['StockID' . $i], $_POST['FromStockLocation']);
						// Only if stock exists at this location
						$Result = DB_query("SELECT quantity
											FROM locstock
											WHERE stockid='" . $_POST['StockID' . $i] . "'
											AND loccode='".$_POST['FromStockLocation']."'");

						$MyRow = DB_fetch_array($Result);
						if (($MyRow['quantity']-$InTransitQuantity) < filter_number_format($_POST['StockQTY' . $i])){
							$InputError = true;
							$ErrorMessage .= __('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. __('does not have enough stock available for transfer.') . '.<br />';
						}
					}
					// Check the accumulated quantity for each item
					if (isset($StockIDAccQty[$_POST['StockID'.$i]])){
						$StockIDAccQty[$_POST['StockID'.$i]] += filter_number_format($_POST['StockQTY' . $i]);
						if ($MyRow[0] < $StockIDAccQty[$_POST['StockID'.$i]]){
							$InputError = true;
							$ErrorMessage .=__('The part code entered of'). ' ' . $_POST['StockID'.$i] . ' '.__('does not have enough stock available for transter due to accumulated quantity is over quantity on hand.') . '<br />';
						}
					} else {
						$StockIDAccQty[$_POST['StockID'.$i]] = filter_number_format($_POST['StockQTY' . $i]);
					} //end of accumulated check

					$TotalItems++;
				}
			}//for all LinesCounter
		}

		if ($TotalItems == 0){
			$InputError = true;
			$ErrorMessage .= __('You must enter at least 1 Stock Item to transfer') . '<br />';
		}

	/*Ship location and Receive location are different */
		if ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
			$InputError=true;
			$ErrorMessage .= __('The transfer must have a different location to receive into and location sent from');
		}
	 } //end if the transfer is not a duplicated
}

if (isset($_POST['Submit']) AND $InputError==false){

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to BEGIN Location Transfer transaction');

	DB_Txn_Begin();

	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if ($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_query($DecimalsSql);
			$DecimalRow = DB_fetch_array($DecimalResult);
			$SQL = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . date('Y-m-d H-i-s') . "',
							'" . $_POST['FromStockLocation']  ."',
							'" . $_POST['ToStockLocation'] . "')";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$ResultLocShip = DB_query($SQL, $ErrMsg);
			
			// === AUTO RECEIVE LOGIC FOR MODAL (Instant Transfer) ===
			if (isset($_POST['modal'])) {
				$qty = round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']);
				
				// 1. Update locstock for FROM
				$SQL = "UPDATE locstock SET quantity = quantity - " . $qty . " WHERE stockid='" . $_POST['StockID' . $i] . "' AND loccode='" . $_POST['FromStockLocation'] . "'";
				DB_query($SQL, $ErrMsg);
				
				// 2. Update locstock for TO
				$SQL = "UPDATE locstock SET quantity = quantity + " . $qty . " WHERE stockid='" . $_POST['StockID' . $i] . "' AND loccode='" . $_POST['ToStockLocation'] . "'";
				DB_query($SQL, $ErrMsg);
				
				// 3. Mark loctransfers as received
				$SQL = "UPDATE loctransfers SET recqty = shipqty WHERE reference='" . $_POST['Trf_ID'] . "' AND stockid='" . $_POST['StockID' . $i] . "'";
				DB_query($SQL, $ErrMsg);
				
				// 4. Create stockmoves
				$SQL = "SELECT quantity FROM locstock WHERE stockid='" . $_POST['StockID' . $i] . "' AND loccode='" . $_POST['FromStockLocation'] . "'";
				$QOHResult = DB_query($SQL);
				$QOHRow = DB_fetch_row($QOHResult);
				$NewQOHFrom = $QOHRow[0];
				
				$SQL = "SELECT quantity FROM locstock WHERE stockid='" . $_POST['StockID' . $i] . "' AND loccode='" . $_POST['ToStockLocation'] . "'";
				$QOHResult = DB_query($SQL);
				$QOHRow = DB_fetch_row($QOHResult);
				$NewQOHTo = $QOHRow[0];

				$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));
				
				$SQL = "INSERT INTO stockmoves (stockid, type, transno, loccode, trandate, userid, prd, reference, qty, newqoh) 
						VALUES ('" . $_POST['StockID' . $i] . "', 16, '" . $_POST['Trf_ID'] . "', '" . $_POST['FromStockLocation'] . "', '" . date('Y-m-d') . "', '" . $_SESSION['UserID'] . "', " . $PeriodNo . ", 'To " . $_POST['ToStockLocation'] . "', " . -$qty . ", " . $NewQOHFrom . ")";
				DB_query($SQL, $ErrMsg);
				
				$SQL = "INSERT INTO stockmoves (stockid, type, transno, loccode, trandate, userid, prd, reference, qty, newqoh) 
						VALUES ('" . $_POST['StockID' . $i] . "', 16, '" . $_POST['Trf_ID'] . "', '" . $_POST['ToStockLocation'] . "', '" . date('Y-m-d') . "', '" . $_SESSION['UserID'] . "', " . $PeriodNo . ", 'From " . $_POST['FromStockLocation'] . "', " . $qty . ", " . $NewQOHTo . ")";
				DB_query($SQL, $ErrMsg);
			}
			// === END AUTO RECEIVE ===

		}
	}

	DB_Txn_Commit();

	$transferSuccess = true;
	$successTrfID = $_POST['Trf_ID'];
	
	// Clear the form data so a new transfer can be started immediately
	unset($_POST['Trf_ID']);
	unset($_POST['Submit']);
	
}

// ALWAYS execute the following block to render the form (no 'else')
{

	//Get next Inventory Transfer Shipment Reference Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if (!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16);
	}

	

	$isModal = isset($_GET['modal']) || isset($_POST['modal']);
	if (!$isModal) {
		echo '<div class="db-page">
				<div class="db-page-header">
					<div class="db-header-left">
						<div class="db-page-title">
							<i class="fas fa-shipping-fast"></i> ' . $Title . '
						</div>
						<div class="db-page-subtitle">' . __('Move items between locations') . '</div>
					</div>
					<div class="db-header-actions">
						<a href="' . $RootPath . '/StockLocTransfer.php" class="db-btn db-btn-secondary">
							<i class="fas fa-sync"></i> ' . __('Reset Form') . '
						</a>
					</div>
				</div>';
	} else {
		echo '<div class="db-page" style="padding: 0;">';
	}


	if (isset($transferSuccess) && $transferSuccess) {
		echo '<div style="background: #e8f5e9; border-left: 4px solid #239d58; padding: 16px; margin-bottom: 20px; border-radius: 4px;">';
		echo '<div style="color: #239d58; font-weight: bold; margin-bottom: 8px;"><i class="fas fa-check-circle"></i> ' . __('The inventory transfer records have been created successfully') . '</div>';
		echo '<a href="'.$RootPath.'/PDFStockLocTransfer.php?TransferNo=' . htmlspecialchars($successTrfID, ENT_QUOTES, 'UTF-8') . '" target="_blank" class="db-btn db-btn-primary" style="display: inline-block; margin-top: 8px;">';
		echo '<i class="fas fa-print"></i> ' . __('Print the Transfer Docket');
		echo '</a>';
		echo '</div>';
	}
	
	if (isset($InputError) and $InputError == true) {
		echo '<div class="db-status-bar db-status-danger" style="margin-bottom: 20px;">
				<div class="db-status-icon"><i class="fas fa-exclamation-circle"></i></div>
				<div class="db-status-text">' . $ErrorMessage . '</div>
			  </div>';
	}

	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . ($_SESSION['FormID'] ?? '') . '" />';
	if ($isModal) {
		echo '<input type="hidden" name="modal" value="1" />';
	}
	
	
// Single unified layout
	echo '<div style="display: flex; flex-direction: column; gap: 20px;">';
	
	if ($isModal) {
		$modalStockID = isset($_GET['StockID']) ? $_GET['StockID'] : (isset($_POST['StockID0']) ? $_POST['StockID0'] : '');
		
		// Query the item's current stock levels across locations
		$LocStockSql = "SELECT locations.locationname, locstock.quantity 
						FROM locstock 
						INNER JOIN locations ON locstock.loccode=locations.loccode 
						WHERE locstock.stockid='" . $modalStockID . "' AND locstock.quantity > 0";
		$LocStockResult = DB_query($LocStockSql);
		
		// Current Stock Card
		echo '<div style="background: var(--surface); padding: 16px; border-radius: 8px; border: 1px solid var(--border-soft); margin-bottom: 20px;">';
		echo '<div style="font-weight: 500; color: var(--text-main); margin-bottom: 12px; font-size: 1rem;">
				<i class="fas fa-boxes" style="margin-right: 8px; color: var(--primary);"></i>' . __('Current Stock Levels for Item Code') . ': <span style="font-family: monospace; font-weight: 700;">' . htmlspecialchars($modalStockID, ENT_QUOTES, 'UTF-8') . '</span>
			  </div>';
		
		$stockData = [];
		if (DB_num_rows($LocStockResult) > 0) {
			while ($locRow = DB_fetch_array($LocStockResult)) {
				$stockData[] = [
					'location' => htmlspecialchars($locRow['locationname'], ENT_QUOTES, 'UTF-8'),
					'quantity' => '<div style="text-align:right;font-weight:700;' . ($locRow['quantity'] > 0 ? 'color:var(--primary);' : 'color:var(--rose);') . '">' . filter_number_format($locRow['quantity']) . '</div>'
				];
			}
		}
		
		$columns = [__('Location'), __('Quantity On Hand')];
		render_modern_table($columns, $stockData, false, ['emptyMessage' => __('No stock locations found for this item.')]);
		echo '</div>'; // End Current Stock Card
		
		// Ultra-simplified modal UI
		echo '<div style="background: var(--surface-alt); padding: 24px; border-radius: 8px; border: 1px solid var(--border-soft);">';

		// Add a flex/grid container for the inputs so they sit in a single row
		echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end;">';
		
		echo '<input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" />';
		echo '<input type="hidden" name="LinesCounter" value="1" />';
		echo '<input type="hidden" name="StockID0" value="' . htmlspecialchars($modalStockID, ENT_QUOTES, 'UTF-8') . '" />';
		
		echo '<div class="db-form-group">
				<label class="db-label">' . __('From Location') . ':</label>
				<select name="FromStockLocation" id="FromStockLocation" class="db-select">';
				$sql = "SELECT loccode, locationname FROM locations";
				$resultStkLocs = DB_query($sql);
				while ($myrow=DB_fetch_array($resultStkLocs)){
					if (isset($_POST['FromStockLocation']) AND $_POST['FromStockLocation']==$myrow['loccode']){
						echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					} else {
						echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					}
				}
		echo '	</select>
			  </div>';

		echo '<div class="db-form-group">
				<label class="db-label">' . __('To Location') . ':</label>
				<select name="ToStockLocation" id="ToStockLocation" class="db-select">';
				DB_data_seek($resultStkLocs,0);
				while ($myrow=DB_fetch_array($resultStkLocs)){
					if (isset($_POST['ToStockLocation']) AND $_POST['ToStockLocation']==$myrow['loccode']){
						echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					} else {
						echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					}
				}
		echo '	</select>
			  </div>';
			  
		echo '<div class="db-form-group">
				<label class="db-label">' . __('Quantity to Transfer') . ':</label>
				<input type="number" name="StockQTY0" class="db-input" value="1" min="1" step="any" required />
			  </div>';

		echo '<div style="display: flex; justify-content: flex-end; margin-top: 10px;">
				<button type="submit" name="Submit" class="db-btn db-btn-primary" style="padding: 12px 30px; font-weight: 700;">
					<i class="fas fa-check-double"></i> ' . __('Transfer Items') . '
				</button>
			  </div>';
			  
		echo '</div>'; // End simplified modal UI

	} else {
		// --- Standard Full UI (Not Modal) ---

		// Configuration Section
		echo '<div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; background: var(--surface-alt); padding: 16px; border-radius: 8px; border: 1px solid var(--border-soft);">';
		
		echo '<input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" />';
		echo '<div class="db-form-group" style="flex: 1; min-width: 200px;">
				<label class="db-label">' . __('From Location') . ':</label>
				<select name="FromStockLocation" id="FromStockLocation" class="db-select">';
				$sql = "SELECT loccode, locationname FROM locations";
				$resultStkLocs = DB_query($sql);
				while ($myrow=DB_fetch_array($resultStkLocs)){
					if (isset($_POST['FromStockLocation']) AND $_POST['FromStockLocation']==$myrow['loccode']){
						echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					} else {
						echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					}
				}
		echo '	</select>
			  </div>';

		echo '<div class="db-form-group" style="flex: 1; min-width: 200px;">
				<label class="db-label">' . __('To Location') . ':</label>
				<select name="ToStockLocation" id="ToStockLocation" class="db-select">';
				DB_data_seek($resultStkLocs,0);
				while ($myrow=DB_fetch_array($resultStkLocs)){
					if (isset($_POST['ToStockLocation']) AND $_POST['ToStockLocation']==$myrow['loccode']){
						echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					} else {
						echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					}
				}
		echo '	</select>
			  </div>';
			  
		echo '<div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
				<label class="db-label" style="margin-bottom:0;">' . __('Transfer #') . ':</label>
				<div class="db-badge db-badge-primary" style="font-family: monospace; font-size: 1rem;">#' . $Trf_ID . '</div>
			  </div>';
			  
		echo '</div>'; // End Configuration Section

		// Add Items Section
		echo '<div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
				<div class="db-form-group" style="flex: 2; min-width: 250px; position: relative;">
					<label class="db-label">' . __('Item Code or Description') . '</label>
					<div class="db-input-group">
						<span class="db-input-group-text"><i class="fas fa-search"></i></span>
						<input type="text" id="ItemSearch" class="db-input" placeholder="' . __('Start typing to search...') . '" autocomplete="off" />
					</div>
					<div id="SearchResults" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--surface); border: 1px solid var(--border-soft); box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 8px; z-index: 100; max-height: 250px; overflow-y: auto; margin-top: 4px;"></div>
					
					<div id="PendingItemInfo" class="db-alert db-alert-info" style="display: none; margin-top: 16px; padding: 10px; font-size: 0.8rem;">
						<div id="PendingItemText" style="word-break: break-all;"></div>
					</div>
				</div>
				
				<div class="db-form-group" style="flex: 1; min-width: 100px;">
					<label class="db-label">' . __('Quantity') . '</label>
					<input type="number" id="QuickQty" class="db-input" value="1" min="1" step="any" />
				</div>
				
				<button type="button" id="AddItemBtn" class="db-btn db-btn-primary" style="height: 42px;">
					<i class="fas fa-plus"></i> ' . __('Add to List') . '
				</button>
			  </div>';

		// Transfer List Table (UIComponents style)
		echo '<div style="margin-top: 10px;">';
		echo '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
				<div style="font-weight: 500; color: var(--text-main); font-size: 1rem;">
					<i class="fas fa-list-ul" style="margin-right: 8px; color: var(--primary);"></i>' . __('Transfer List') . '
				</div>
				<span class="db-badge" id="ItemCountBadge" style="font-size: 0.75rem;">' . $LinesCounter . ' ' . __('Items') . '</span>
			  </div>';

		// The modern table layout from UIComponents
		echo '<div style="position:relative;overflow-x:auto;background-color:var(--surface);box-shadow:0 1px 2px rgba(0,0,0,0.05);border-radius:8px;border:1px solid var(--border-soft);margin-bottom:1rem;max-width:100%;">';
		echo '<table style="width:100%;font-size:0.875rem;text-align:left;color:var(--text-body);border-collapse:collapse;" id="TransferTable">';
		echo '<thead style="background-color:var(--surface-alt);border-bottom:1px solid var(--border);color:var(--text-main);">';
		echo '<tr>';
		echo '<th scope="col" style="padding:12px 24px;font-weight:500;width:150px;">' . __('Item Code') . '</th>';
		echo '<th scope="col" style="padding:12px 24px;font-weight:500;">' . __('Description') . '</th>';
		echo '<th scope="col" style="padding:12px 24px;font-weight:500;text-align:right;width:100px;">' . __('Qty') . '</th>';
		echo '<th scope="col" style="padding:12px 24px;font-weight:500;text-align:center;width:80px;">' . __('Action') . '</th>';
		echo '</tr>';
		echo '</thead>';
		
		echo '<tbody id="TransferListBody">';
		
		// Pre-populate if post data exists
		if (isset($_POST['LinesCounter'])){
			for ($i=0; $i < $_POST['LinesCounter']; $i++){
				if (isset($_POST['StockID' . $i]) && $_POST['StockID' . $i] != ''){
					echo '<tr style="background-color:var(--surface);border-bottom:1px solid var(--border-soft);transition:background-color 0.15s ease;" onmouseover="this.style.backgroundColor=\'var(--surface-alt)\';" onmouseout="this.style.backgroundColor=\'var(--surface)\';">
							<th scope="row" style="padding:16px 24px;font-weight:500;color:#111827;">' . $_POST['StockID' . $i] . '<input type="hidden" name="StockID' . $LinesCounter . '" value="' . $_POST['StockID' . $i] . '" /></th>
							<td style="padding:16px 24px;color:var(--text-muted);"><small>' . __('Imported or manual entry') . '</small></td>
							<td style="padding:16px 24px;text-align:right;font-weight:500;color:var(--primary);">' . $_POST['StockQTY' . $i] . '<input type="hidden" name="StockQTY' . $LinesCounter . '" value="' . $_POST['StockQTY' . $i] . '" /></td>
							<td style="padding:16px 24px;text-align:center;">
								<button type="button" class="db-btn db-btn-sm db-btn-danger" onclick="removeRow(this)">
									<i class="fas fa-trash-alt"></i> ' . __('Remove') . '
								</button>
							</td>
						  </tr>';
					$LinesCounter++;
				}
			}
		}
		
		echo '</tbody>';
		echo '</table>';
		echo '</div>'; // End Table Wrapper

		echo '<div id="EmptyState" style="' . ($LinesCounter > 0 ? 'display: none;' : '') . ' text-align: center; padding: 40px; border: 2px dashed var(--border); border-radius: var(--radius-md); margin-top: -10px; margin-bottom: 20px;">
				<div style="width: 48px; height: 48px; background: var(--bg-workspace); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
					<i class="fas fa-box-open" style="font-size: 1.5rem; opacity: 0.3;"></i>
				</div>
				<h3 style="font-weight: 500; color: var(--text-main); margin-bottom: 4px; font-size: 1rem;">' . __('List is empty') . '</h3>
				<p style="max-width: 250px; margin: 0 auto; line-height: 1.4; font-size: 0.85rem; color: var(--text-muted);">' . __('Start by adding items above.') . '</p>
			  </div>';

		// Footer Actions
		echo '<div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-top: 10px;">
				<label class="db-checkbox-container" style="color: var(--text-muted); font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
					<input type="checkbox" name="ClearAll" />
					<span>' . __('Clear list after transfer') . '</span>
				</label>
				<input type="hidden" name="LinesCounter" id="LinesCounter" value="' . $LinesCounter . '" />
				<button type="submit" name="Submit" class="db-btn db-btn-primary" style="padding: 12px 30px; font-weight: 700;">
					<i class="fas fa-check-double"></i> ' . __('Transfer Items') . '
				</button>
			  </div>';

		echo '</div>'; // End Full UI layout
	}

	echo '</div>'; // End column flex

	// CSV Bulk Import - Only show if not in modal
	if (!$isModal) {
		echo '<div class="db-card" style="margin-top: 32px;">
				<div class="db-card-header" style="background: var(--surface-alt);">
					<div class="db-card-title" style="font-size: 0.85rem;"><i class="fas fa-file-csv"></i> ' . __('CSV Bulk Import') . '</div>
				</div>
				<div class="db-card-body" style="padding: 24px;">
					<div style="display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap;">
						<div style="flex: 1; min-width: 250px;">
							<label class="db-label" style="margin-bottom: 8px;">' . __('Select CSV File') . ':</label>
							<div style="position: relative;">
								<input name="SelectedTransferFile" type="file" id="CSVFile" class="db-input" style="padding: 8px 12px; font-size: 0.85rem;" />
							</div>
							<div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">
								<i class="fas fa-info-circle"></i> ' . __('Required format') . ': <code>[Item Code], [Quantity]</code>
							</div>
						</div>
						<button type="submit" name="Submit" class="db-btn db-btn-secondary" style="height: 42px;">
							<i class="fas fa-upload"></i> ' . __('Upload Items') . '
						</button>
					</div>
				</div>
			</div>';
	}

	echo '</form>
		</div>'; // End db-page

	echo '<style>
	.db-btn-danger {
		background: #ef4444;
		color: #ffffff;
		border-color: #ef4444;
	}
	.db-btn-danger:hover {
		background: #dc2626;
		border-color: #dc2626;
		color: #ffffff;
		box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
		transform: translateY(-1px);
	}
	.db-search-item {
		padding: 12px 16px;
		cursor: pointer;
		border-bottom: 1px solid var(--border-soft);
		transition: all 0.2s;
	}
	.db-search-item:hover {
		background: var(--surface-alt);
		padding-left: 20px;
	}
	</style>';

	if (!$isModal) {
		echo '<script>
		let selectedItem = null;

		document.addEventListener("DOMContentLoaded", function() {
			const itemSearch = document.getElementById("ItemSearch");
			const searchResults = document.getElementById("SearchResults");
			const addItemBtn = document.getElementById("AddItemBtn");
			const quickQty = document.getElementById("QuickQty");
			const transferBody = document.getElementById("TransferListBody");
			const emptyState = document.getElementById("EmptyState");
			const linesCounter = document.getElementById("LinesCounter");

			itemSearch.addEventListener("input", function() {
				const query = this.value.trim();
				if (query.length < 2) {
					searchResults.style.display = "none";
					return;
				}

				fetch("StockSearch_Ajax.php?term=" + encodeURIComponent(query))
					.then(response => response.json())
					.then(data => {
						searchResults.innerHTML = "";
						if (data.length > 0) {
							data.forEach(item => {
								const div = document.createElement("div");
								div.className = "db-search-item";
								div.innerHTML = `<strong>${item.id}</strong> - ${item.description}`;
								div.onclick = function() {
									selectedItem = item;
									itemSearch.value = item.id;
									document.getElementById("PendingItemText").innerHTML = `<strong>${item.id}</strong>: ${item.description}`;
									document.getElementById("PendingItemInfo").style.display = "flex";
									searchResults.style.display = "none";
									quickQty.focus();
								};
								searchResults.appendChild(div);
							});
							searchResults.style.display = "block";
						} else {
							searchResults.style.display = "none";
						}
					});
			});

			addItemBtn.addEventListener("click", function() {
				if (!selectedItem) {
					alert("' . __('Please select an item first') . '");
					itemSearch.focus();
					return;
				}

				const qty = parseFloat(quickQty.value);
				if (isNaN(qty) || qty <= 0) {
					alert("' . __('Please enter a valid quantity') . '");
					quickQty.focus();
					return;
				}

				const idx = parseInt(linesCounter.value);
				const row = document.createElement("tr");
				row.style = "background-color:var(--surface);border-bottom:1px solid var(--border-soft);transition:background-color 0.15s ease;";
				row.onmouseover = function() { this.style.backgroundColor="var(--surface-alt)"; };
				row.onmouseout = function() { this.style.backgroundColor="var(--surface)"; };
				
				row.innerHTML = `
					<th scope="row" style="padding:16px 24px;font-weight:500;color:#111827;">${selectedItem.id}<input type="hidden" name="StockID${idx}" value="${selectedItem.id}" /></th>
					<td style="padding:16px 24px;">${selectedItem.description}</td>
					<td style="padding:16px 24px;text-align:right;font-weight:500;color:var(--primary);">${qty}<input type="hidden" name="StockQTY${idx}" value="${qty}" /></td>
					<td style="padding:16px 24px;text-align:center;">
						<button type="button" class="db-btn db-btn-sm db-btn-danger" onclick="removeRow(this)">
							<i class="fas fa-trash-alt"></i> ' . __('Remove') . '
						</button>
					</td>
				`;

				transferBody.appendChild(row);
				linesCounter.value = idx + 1;
				document.getElementById("ItemCountBadge").innerText = `${idx + 1} ' . __('Items') . '`;
				emptyState.style.display = "none";

				// Reset
				selectedItem = null;
				itemSearch.value = "";
				quickQty.value = "1";
				document.getElementById("PendingItemInfo").style.display = "none";
				itemSearch.focus();
			});

			document.addEventListener("click", function(e) {
				if (!itemSearch.contains(e.target) && !searchResults.contains(e.target)) {
					searchResults.style.display = "none";
				}
			});
		});

		function removeRow(btn) {
			const row = btn.closest("tr");
			row.remove();
			renumberRows();
		}

		function renumberRows() {
			const rows = document.querySelectorAll("#TransferListBody tr");
			const counter = document.getElementById("LinesCounter");
			rows.forEach((row, i) => {
				const idInput = row.querySelector("input[name^=\'StockID\']");
				const qtyInput = row.querySelector("input[name^=\'StockQTY\']");
				idInput.name = `StockID${i}`;
				qtyInput.name = `StockQTY${i}`;
			});
			counter.value = rows.length;
			document.getElementById("ItemCountBadge").innerText = `${rows.length} ' . __('Items') . '`;
			if (rows.length === 0) {
				document.getElementById("EmptyState").style.display = "block";
			}
		}
		</script>';
	}

	if (!$isModal) {
		include(__DIR__ . '/includes/footer.php');
	} else {
		echo '</body></html>';
	}
}
