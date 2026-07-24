<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Asset objects called Assets - containing details of all asset additions on a supplier invoice
Asset additions are posted to the debit of fixed asset category cost account if the creditors GL link is on */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include_once(__DIR__ . '/includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

if (!isset($_SESSION['SuppTrans'])){
	$Title = __('Fixed Asset Charges or Credits');
	$ViewTopic = 'FixedAssets';
	$BookMark = 'AssetInvoices';
	include(__DIR__ . '/includes/header.php');
	prnMsg(__('Fixed asset additions or credits are entered against supplier invoices or credit notes respectively') . '. ' . __('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>';
	include(__DIR__ . '/includes/footer.php');
	exit();
}

$Title = __('Fixed Asset Charges or Credits');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetInvoices';

include(__DIR__ . '/includes/header.php');

if (isset($_POST['AddAssetToInvoice'])){

	$InputError = false;
	if ($_POST['AssetID'] == ''){
		if ($_POST['AssetSelection']==''){
			$InputError = true;
			prnMsg(__('A valid asset must be either selected from the list or entered'),'error');
		} else {
			$_POST['AssetID'] = $_POST['AssetSelection'];
		}
	} else {
		$Result = DB_query("SELECT assetid FROM fixedassets WHERE assetid='" . $_POST['AssetID'] . "'");
		if (DB_num_rows($Result)==0) {
			prnMsg(__('The asset ID entered manually is not a valid fixed asset. If you do not know the asset reference, select it from the list'),'error');
			$InputError = true;
			unset($_POST['AssetID']);
		}
	}

	if (!is_numeric(filter_number_format($_POST['Amount']))){
		prnMsg(__('The amount entered is not numeric. This fixed asset cannot be added to the invoice'),'error');
		$InputError = true;
		unset($_POST['Amount']);
	}

	if ($InputError == false){
		$_SESSION['SuppTrans']->Add_Asset_To_Trans($_POST['AssetID'],
													filter_number_format($_POST['Amount']));
		unset($_POST['AssetID']);
		unset($_POST['Amount']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_Asset_From_Trans($_GET['Delete']);
}

$BackUrl = ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice') ? '/SupplierInvoice.php' : '/SupplierCredit.php';
$BackLabel = ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice') ? __('Back to Invoice') : __('Back to Credit Note');

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
			<p class="db-page-subtitle">' . ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice' ? __('Fixed Asset Charges for') : __('Fixed Asset Credits for')) . ' <span class="val-bold">' . $_SESSION['SuppTrans']->SupplierID . ' - ' . $_SESSION['SuppTrans']->SupplierName . '</span></p>
		</div>
		<div class="db-header-actions">
			<a href="' . $RootPath . $BackUrl . '" class="db-btn db-btn-secondary">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path></svg>
				' . $BackLabel . '
			</a>
		</div>
	</div>';

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
$TotalAssetValue = 0;
foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){
	$TotalAssetValue +=  $EnteredAsset->Amount;
}

// Card 2: Live Summary
echo '<div class="db-card" style="position: sticky; top: var(--space-4);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-calculator"></i> ' . __('Charges Summary') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-4);">
			<div style="display: flex; flex-direction: column; gap: var(--space-3);">
				<div style="display: flex; justify-content: space-between;">
					<span class="db-muted">' . __('Asset Charges') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalAssetValue, $_SESSION['SuppTrans']->CurrDecimalPlaces) . '</span>
				</div>
				<div style="margin: var(--space-2) 0; height: 1px; background: var(--border-soft);"></div>
				<div style="display: flex; justify-content: space-between; font-size: 1.2rem; color: var(--db-primary);">
					<span class="val-bold">' . __('Grand Total') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalAssetValue, $_SESSION['SuppTrans']->CurrDecimalPlaces) . ' ' . $_SESSION['SuppTrans']->CurrCode . '</span>
				</div>
			</div>
			<div style="margin-top: var(--space-6);">
				<a href="' . $RootPath . $BackUrl . '" class="db-btn db-btn-primary" style="width: 100%; height: 44px; justify-content: center; font-size: 1rem;">
					<i class="fas fa-arrow-left"></i> ' . $BackLabel . '
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
			<h3 class="db-card-title"><i class="fas fa-monument"></i> ' . ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice' ? __('Fixed Assets on Invoice') : __('Fixed Asset credits on Credit Note')) . '</h3>
		</div>
		<div class="db-card-body" style="padding: 0;">';

if (count($_SESSION['SuppTrans']->Assets) > 0) {
	echo '<table class="registry-table">
			<thead>
			<tr>
				<th>' . __('Asset ID') . '</th>
				<th>' . __('Description') . '</th>
				<th class="number">' . __('Amount') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>';

	foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){
		echo '<tr>
				<td>' . $EnteredAsset->AssetID . '</td>
				<td>' . $EnteredAsset->Description . '</td>
				<td class="number">' . locale_number_format($EnteredAsset->Amount, $_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredAsset->Counter . '" style="color:#ef4444;"><i class="fas fa-times"></i></a></td>
			</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<div style="padding: var(--space-6); text-align: center; color: var(--text-muted);">' . __('No fixed asset charges or credits selected yet.') . '</div>';
}

echo '  </div>
	  </div>';

// Form parameters
if (!isset($_POST['AssetID'])) {
	$_POST['AssetID']='';
}
if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}

prnMsg(__('If you know the code enter it in the Asset ID input box, otherwise select the asset from the list below. Only assets with no cost will show in the list'), 'info');

echo '<div class="db-card" style="margin-top: var(--space-6);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-plus-circle"></i> ' . __('Add Fixed Asset Charge') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-6);">
			<div class="db-grid db-grid-3">
				<div class="db-field">
					<label class="db-label" for="AssetID">' . __('Asset ID') . '</label>
					<div style="display: flex; gap: 8px;">
						<input class="integer" maxlength="6" name="AssetID" pattern="[^-]{1,5}" placeholder="' . __('Positive integer') . '" style="width: 100%;" type="text" value="' . $_POST['AssetID'] . '" />
						<a href="' . $RootPath . '/FixedAssetItems.php" target="_blank" class="db-btn db-btn-secondary" style="white-space: nowrap;"><i class="fas fa-plus"></i> ' . __('New Asset') . '</a>
					</div>
				</div>
				
				<div class="db-field" style="grid-column: span 2;">
					<label class="db-label" for="AssetSelection"><b>' . __('OR') . '</b> ' . __('Select from list') . '</label>
					<select name="AssetSelection" style="width: 100%;">';

$SQL = "SELECT assetid, description
		FROM fixedassets
		WHERE cost=0
		ORDER BY assetid DESC";

$Result = DB_query($SQL);

echo '<option value="">' . __('-- Select Asset --') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	$selected = (isset($_POST['AssetSelection']) AND $MyRow['assetid']==$_POST['AssetSelection']) ? 'selected="selected"' : '';
	echo '<option ' . $selected . ' value="' . $MyRow['assetid'] . '">' . $MyRow['assetid'] . ' - ' . $MyRow['description']  . '</option>';
}

echo '				</select>
				</div>
			</div>
			
			<div class="db-grid db-grid-3" style="margin-top: var(--space-4);">
				<div class="db-field">
					<label class="db-label" for="Amount">' . __('Amount') . '</label>
					<div style="display: flex; align-items: center; gap: 8px;">
						<input type="text" class="number" pattern="(?!^-?0[,.]0*$).{1,11}" title="" name="Amount" style="width: 100%;" value="' .  locale_number_format($_POST['Amount'],$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
						<span class="db-muted">' . $_SESSION['SuppTrans']->CurrCode . '</span>
					</div>
				</div>
			</div>
			
			<div style="margin-top: var(--space-6); text-align: right;">
				<button type="submit" name="AddAssetToInvoice" class="db-btn db-btn-primary">' . __('Enter Fixed Asset') . '</button>
			</div>
		</div>
	  </div>';

echo '</main>
      </div><!-- .db-bottom-layout -->
      </form>
      </div><!-- .db-page -->';

include(__DIR__ . '/includes/footer.php');
