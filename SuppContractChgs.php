<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Contract objects - containing details of all contract charges
Contract charges are posted to the debit of Work In Progress (based on the account specified in the stock category record of the contract item
This is cleared against the cost of the contract as originally costed - when the contract is closed and any difference is taken to the price variance on the contract */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include_once(__DIR__ . '/includes/DefineSuppTransClass.php');

/* Session started here for password checking and authorisation level check */
require(__DIR__ . '/includes/session.php');

if (!isset($_SESSION['SuppTrans'])){
	$Title = __('Contract Charges or Credits');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include(__DIR__ . '/includes/header.php');
	prnMsg(__('Contract charges or credits are entered against supplier invoices or credit notes respectively. To enter supplier transactions the supplier must first be selected from the supplier selection screen, then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>';
	include(__DIR__ . '/includes/footer.php');
	exit();
}

$Title = __('Contract Charges or Credits');
$ViewTopic = 'AccountsPayable';
$BookMark = '';

include(__DIR__ . '/includes/header.php');

if (isset($_POST['AddContractChgToInvoice'])){

	$InputError = false;
	if ($_POST['ContractRef'] == ''){
		$_POST['ContractRef'] = $_POST['ContractSelection'];
	} else {
		$Result = DB_query("SELECT contractref FROM contracts
							WHERE status=2
							AND contractref='" . $_POST['ContractRef'] . "'");
		if (DB_num_rows($Result)==0){
			prnMsg(__('The contract reference entered does not exist as a customer ordered contract. This contract cannot be charged to'),'error');
			$InputError =true;
		} //end if the contract ref entered is not a valid contract
	}//end if a contract ref was entered manually
	
	if (!is_numeric(filter_number_format($_POST['Amount']))){
		prnMsg(__('The amount entered is not numeric. This contract charge cannot be added to the invoice'),'error');
		$InputError = true;
	}

	if ($InputError == false){
		$AnticipatedCost = isset($_POST['AnticipatedCost']) ? 1 : 0;
		$_SESSION['SuppTrans']->Add_Contract_To_Trans($_POST['ContractRef'],
														filter_number_format($_POST['Amount']),
														$_POST['Narrative'],
														$AnticipatedCost);
		unset($_POST['ContractRef']);
		unset($_POST['Amount']);
		unset($_POST['Narrative']);
	}
}

if (isset($_GET['Delete'])){
	$_SESSION['SuppTrans']->Remove_Contract_From_Trans($_GET['Delete']);
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
			<p class="db-page-subtitle">' . ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice' ? __('Contract Charges for') : __('Contract Credits for')) . ' <span class="val-bold">' . $_SESSION['SuppTrans']->SupplierID . ' - ' . $_SESSION['SuppTrans']->SupplierName . '</span></p>
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
$TotalContractsValue = 0;
foreach ($_SESSION['SuppTrans']->Contracts as $EnteredContract){
	$TotalContractsValue += $EnteredContract->Amount;
}

// Card 2: Live Summary
echo '<div class="db-card" style="position: sticky; top: var(--space-4);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-calculator"></i> ' . __('Charges Summary') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-4);">
			<div style="display: flex; flex-direction: column; gap: var(--space-3);">
				<div style="display: flex; justify-content: space-between;">
					<span class="db-muted">' . __('Contract Charges') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalContractsValue, $_SESSION['CompanyRecord']['decimalplaces']) . '</span>
				</div>
				<div style="margin: var(--space-2) 0; height: 1px; background: var(--border-soft);"></div>
				<div style="display: flex; justify-content: space-between; font-size: 1.2rem; color: var(--db-primary);">
					<span class="val-bold">' . __('Grand Total') . ':</span>
					<span class="val-bold">' . locale_number_format($TotalContractsValue, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $_SESSION['SuppTrans']->CurrCode . '</span>
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
			<h3 class="db-card-title"><i class="fas fa-file-contract"></i> ' . ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice' ? __('Contract Charges on Invoice') : __('Contract Credits on Credit Note')) . '</h3>
		</div>
		<div class="db-card-body" style="padding: 0;">';

if (count($_SESSION['SuppTrans']->Contracts) > 0) {
	echo '<table class="registry-table">
			<thead>
			<tr>
				<th>' . __('Contract') . '</th>
				<th class="number">' . __('Amount') . ' (' . $_SESSION['SuppTrans']->CurrCode . ')</th>
				<th>' . __('Narrative') . '</th>
				<th>' . __('Anticipated') . '</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>';

	foreach ($_SESSION['SuppTrans']->Contracts as $EnteredContract){
		$AnticipatedCost = ($EnteredContract->AnticipatedCost == true) ? __('Yes') : __('No');
		echo '<tr>
				<td>' . $EnteredContract->ContractRef . '</td>
				<td class="number">' . locale_number_format($EnteredContract->Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . htmlspecialchars($EnteredContract->Narrative, ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . $AnticipatedCost . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredContract->Counter . '" style="color:#ef4444;"><i class="fas fa-times"></i></a></td>
			</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<div style="padding: var(--space-6); text-align: center; color: var(--text-muted);">' . __('No contract charges or credits selected yet.') . '</div>';
}

echo '  </div>
	  </div>';

// Form parameters
if (!isset($_POST['ContractRef'])) {
	$_POST['ContractRef']='';
}
if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
if (!isset($_POST['Narrative'])) {
	$_POST['Narrative']='';
}

echo '<div class="db-card" style="margin-top: var(--space-6);">
		<div class="db-card-header">
			<h3 class="db-card-title"><i class="fas fa-plus-circle"></i> ' . __('Add Contract Charge') . '</h3>
		</div>
		<div class="db-card-body" style="padding: var(--space-6);">
			<div class="db-grid db-grid-3">
				<div class="db-field">
					<label class="db-label" for="ContractRef">' . __('Contract Reference') . '</label>
					<input name="ContractRef" style="width: 100%;" maxlength="20" value="' .  $_POST['ContractRef'] . '" />
				</div>
				
				<div class="db-field" style="grid-column: span 2;">
					<label class="db-label" for="ContractSelection"><b>' . __('OR') . '</b> ' . __('Select from list') . '</label>
					<select name="ContractSelection" style="width: 100%;">';

$SQL = "SELECT contractref, name
		FROM contracts INNER JOIN debtorsmaster
		ON contracts.debtorno=debtorsmaster.debtorno
		WHERE status=2"; //only show customer ordered contracts

$Result = DB_query($SQL);

echo '<option value="">' . __('-- Select Contract --') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	$selected = (isset($_POST['ContractSelection']) and $MyRow['contractref']==$_POST['ContractSelection']) ? 'selected="selected"' : '';
	echo '<option ' . $selected . ' value="' . $MyRow['contractref'] . '">' . $MyRow['contractref'] . ' - ' . $MyRow['name'] . '</option>';
}

echo '				</select>
				</div>
			</div>
			
			<div class="db-field" style="margin-top: var(--space-4);">
				<label class="db-label" for="Narrative">' . __('Narrative') . '</label>
				<input type="text" name="Narrative" style="width: 100%;" maxlength="40" value="' .  $_POST['Narrative'] . '" />
			</div>
			
			<div class="db-grid db-grid-3" style="margin-top: var(--space-4);">
				<div class="db-field">
					<label class="db-label" for="Amount">' . __('Amount') . '</label>
					<div style="display: flex; align-items: center; gap: 8px;">
						<input type="text" class="number" pattern="(?!^[-]?0[.,]0*$).{1,11}" title="" placeholder="'.__('Non zero amount').'" name="Amount" style="width: 100%;" value="' .  locale_number_format($_POST['Amount'],$_SESSION['CompanyRecord']['decimalplaces']) . '" />
						<span class="db-muted">' . $_SESSION['SuppTrans']->CurrCode . '</span>
					</div>
				</div>
				
				<div class="db-field" style="display: flex; align-items: center; padding-top: var(--space-6);">
					<label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 500; color: var(--text-body); cursor: pointer;">';
					
if (isset($_POST['AnticipatedCost']) AND $_POST['AnticipatedCost']==1){
	echo '				<input type="checkbox" name="AnticipatedCost" checked />';
} else {
	echo '				<input type="checkbox" name="AnticipatedCost" />';
}

echo '					' . __('Anticipated Cost') . '
					</label>
				</div>
			</div>
			
			<div style="margin-top: var(--space-6); text-align: right;">
				<button type="submit" name="AddContractChgToInvoice" class="db-btn db-btn-primary">' . __('Enter Contract Charge') . '</button>
			</div>
		</div>
	  </div>';

echo '</main>
      </div><!-- .db-bottom-layout -->
      </form>
      </div><!-- .db-page -->';

include(__DIR__ . '/includes/footer.php');
