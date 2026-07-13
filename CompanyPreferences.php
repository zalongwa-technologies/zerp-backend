<?php

// Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
$Title = __('Company Preferences');

// Simplified styling
$ExtraHeadContent = '
<style>
	.db-page { padding: 20px; font-family: "Inter", sans-serif; background: #f9fafb; min-height: 100vh; }
	.simple-header { margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
	.simple-title { font-size: 1.5rem; color: #111827; margin: 0; }
	.db-card { background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
	.db-card-header { background: #f3f4f6; padding: 16px 20px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151; font-size: 1.1rem; border-top-left-radius: 8px; border-top-right-radius: 8px; }
	.db-card-body { padding: 20px; }
	.form-group { margin-bottom: 16px; }
	.db-form-label { display: block; font-weight: 600; margin-bottom: 6px; color: #4b5563; font-size: 0.9rem; }
	.db-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
	.db-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
	.field-help { font-size: 0.8rem; color: #6b7280; display: block; margin-top: 4px; }
	.save-btn { background: #059669; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
	.save-btn:hover { background: #047857; }
	.gl-account-search { margin-bottom: 8px; }
	.card-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
	@media (max-width: 768px) { .card-grid-2 { grid-template-columns: 1fr; } }
</style>
<script>
	function filterGLAccountSelect(searchInput) {
		const select = document.getElementById(searchInput.dataset.target);
		if (!select) return;
		const query = searchInput.value.trim().toLowerCase();
		Array.from(select.options).forEach(option => {
			const optionText = (option.dataset.search || option.textContent).toLowerCase();
			option.hidden = option.value !== \'\' && !option.selected && query !== \'\' && !optionText.includes(query);
		});
	}
	document.addEventListener(\'DOMContentLoaded\', () => {
		document.querySelectorAll(\'.gl-account-search\').forEach(input => {
			input.addEventListener(\'input\', () => filterGLAccountSelect(input));
		});
	});
</script>';

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');

// Helper for modernized GL Selects
function ModernGLSelect($Name, $Value, $Filter, $Label, $Help) {
    if ($Filter == 'ALL') { $Where = ''; } 
    elseif ($Filter == 'BS') { $Where = 'WHERE accountgroups.pandl=0'; } 
    elseif ($Filter == 'P&L') { $Where = 'WHERE accountgroups.pandl=1'; } 
    else { $Where = ''; }

    $SQL = "SELECT chartmaster.accountcode, chartmaster.accountname FROM chartmaster
            INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
            INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode
                AND glaccountusers.userid='" . $_SESSION['UserID'] . "' " . $Where . "
            ORDER BY chartmaster.accountcode";
    $Result = DB_query($SQL);
    
    $SelectID = 'GLSelect_' . preg_replace('/[^A-Za-z0-9_-]/', '', $Name);
    $HTML = '<div class="form-group"><label class="db-form-label">' . $Label . '</label>';
    $HTML .= '<input type="search" class="db-input gl-account-search" data-target="' . $SelectID . '" placeholder="' . __('Search account code or name') . '" autocomplete="off" />';
    $HTML .= '<select id="' . $SelectID . '" name="' . $Name . '" class="db-input">';
    $HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
    while ($Row = DB_fetch_array($Result)) {
        $Selected = ($Row['accountcode'] == $Value) ? 'selected="selected"' : '';
        $OptionText = $Row['accountcode'] . ' - ' . $Row['accountname'];
        $HTML .= '<option ' . $Selected . ' value="' . htmlspecialchars($Row['accountcode'], ENT_QUOTES, 'UTF-8') . '" data-search="' . htmlspecialchars($OptionText, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($OptionText, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    $HTML .= '</select><span class="field-help">' . $Help . '</span></div>';
    return $HTML;
}

$InputError = 0;
$Errors = array();
$i = 1;

if (isset($_POST['submit'])) {
	if (mb_strlen($_POST['CoyName']) > 50 OR mb_strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(__('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}

	if (mb_strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(__('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError != 1) {
		$CompanyFileHandler = fopen($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
		$Contents = "<?php\n\n";
		$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_POST['CoyName'] . "';\n";
		$Contents.= "?>";
		if (!fwrite($CompanyFileHandler, $Contents)) {
			fclose($CompanyFileHandler);
			echo '<div class="error">' . __('Cannot write to the Companies.php file') . '</div>';
		}
		fclose($CompanyFileHandler);

		$SQL = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
									companynumber = '" . $_POST['CompanyNumber'] . "',
									gstno='" . $_POST['GSTNo'] . "',
									regoffice1='" . $_POST['RegOffice1'] . "',
									regoffice2='" . $_POST['RegOffice2'] . "',
									regoffice3='" . $_POST['RegOffice3'] . "',
									regoffice4='" . $_POST['RegOffice4'] . "',
									regoffice5='" . $_POST['RegOffice5'] . "',
									regoffice6='" . $_POST['RegOffice6'] . "',
									telephone='" . $_POST['Telephone'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									currencydefault='" . $_POST['CurrencyDefault'] . "',
									debtorsact='" . $_POST['DebtorsAct'] . "',
									pytdiscountact='" . $_POST['PytDiscountAct'] . "',
									creditorsact='" . $_POST['CreditorsAct'] . "',
									payrollact='" . $_POST['PayrollAct'] . "',
									grnact='" . $_POST['GRNAct'] . "',
									commissionsact='" . $_POST['CommAct'] . "',
									salesexchangediffact='" . $_POST['SalesExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
									currencyexchangediffact='" . $_POST['CurrencyExchangeDiffAct'] . "',
									unrealizedcurrencydiffact='" . $_POST['UnrealizedCurrencyDiffAct'] . "',
									retainedearnings='" . $_POST['RetainedEarnings'] . "',
									gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
									gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
									gllink_stock='" . $_POST['GLLink_Stock'] ."',
									freightact='" . $_POST['FreightAct'] . "'
								WHERE coycode=1";

		$ErrMsg =  __('The company preferences could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);
        
		prnMsg( __('Company preferences have been successfully updated. These single-setup details are now active across the system.'),'success');

		/* Alter the exchange rates in the currencies table */
		$SQL="SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$NewCurrencyRate=$MyRow[0];

		$SQL="UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
		$ErrMsg =  __('Could not update the currency rates');
		$Result = DB_query($SQL, $ErrMsg);

		$ForceConfigReload = true; 
		include(__DIR__ . '/includes/GetConfig.php');
		$ForceConfigReload = false;
	} else {
		prnMsg( __('Validation failed') . ', ' . __('no updates or deletes took place'),'warn');
	}
}

if (!isset($_POST['submit'])) {
    $sql = "SELECT coyname, companynumber, gstno, regoffice1, regoffice2, regoffice3, regoffice4, regoffice5, regoffice6, telephone, fax, email, currencydefault, debtorsact, pytdiscountact, creditorsact, payrollact, grnact, commissionsact, salesexchangediffact, purchasesexchangediffact, currencyexchangediffact, unrealizedcurrencydiffact, retainedearnings, gllink_debtors, gllink_creditors, gllink_stock, freightact FROM companies WHERE coycode=1";
    $ErrMsg = __('The company preferences could not be retrieved');
    $Result = DB_query($sql, $ErrMsg);
    if (DB_num_rows($Result) > 0) {
        $myrow = DB_fetch_array($Result);
        $_POST['CoyName'] = $myrow['coyname'];
        $_POST['CompanyNumber'] = $myrow['companynumber'];
        $_POST['GSTNo'] = $myrow['gstno'];
        $_POST['RegOffice1'] = $myrow['regoffice1'];
        $_POST['RegOffice2'] = $myrow['regoffice2'];
        $_POST['RegOffice3'] = $myrow['regoffice3'];
        $_POST['RegOffice4'] = $myrow['regoffice4'];
        $_POST['RegOffice5'] = $myrow['regoffice5'];
        $_POST['RegOffice6'] = $myrow['regoffice6'];
        $_POST['Telephone'] = $myrow['telephone'];
        $_POST['Fax'] = $myrow['fax'];
        $_POST['Email'] = $myrow['email'];
        $_POST['CurrencyDefault'] = $myrow['currencydefault'];
        $_POST['DebtorsAct'] = $myrow['debtorsact'];
        $_POST['PytDiscountAct'] = $myrow['pytdiscountact'];
        $_POST['CreditorsAct'] = $myrow['creditorsact'];
        $_POST['PayrollAct'] = $myrow['payrollact'];
        $_POST['GRNAct'] = $myrow['grnact'];
        $_POST['CommAct'] = $myrow['commissionsact'];
        $_POST['SalesExchangeDiffAct'] = $myrow['salesexchangediffact'];
        $_POST['PurchasesExchangeDiffAct'] = $myrow['purchasesexchangediffact'];
        $_POST['CurrencyExchangeDiffAct'] = $myrow['currencyexchangediffact'];
        $_POST['UnrealizedCurrencyDiffAct'] = $myrow['unrealizedcurrencydiffact'];
        $_POST['RetainedEarnings'] = $myrow['retainedearnings'];
        $_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
        $_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];
        $_POST['GLLink_Stock'] = $myrow['gllink_stock'];
        $_POST['FreightAct'] = $myrow['freightact'];
    } else {
        // Fallback for new empty instances
        $fields = array('CoyName', 'CompanyNumber', 'GSTNo', 'RegOffice1', 'RegOffice2', 'RegOffice3', 'RegOffice4', 'RegOffice5', 'RegOffice6', 'Telephone', 'Fax', 'Email', 'CurrencyDefault', 'DebtorsAct', 'PytDiscountAct', 'CreditorsAct', 'PayrollAct', 'GRNAct', 'CommAct', 'SalesExchangeDiffAct', 'PurchasesExchangeDiffAct', 'CurrencyExchangeDiffAct', 'UnrealizedCurrencyDiffAct', 'RetainedEarnings', 'GLLink_Debtors', 'GLLink_Creditors', 'GLLink_Stock', 'FreightAct');
        foreach ($fields as $field) {
            $_POST[$field] = '';
        }
    }
}

echo '<div class="db-page">
    <div class="simple-header">
        <h1 class="simple-title">' . $Title . '</h1>
        <button type="submit" form="main-form" name="submit" class="save-btn">' . __('Save Preferences') . '</button>
    </div>

    <form id="main-form" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />

        <!-- Identity Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('Company Identity & Legal') . '</div>
            <div class="db-card-body card-grid-2">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="db-form-label">' . __('Full Business Name') . '</label>
                    <input type="text" name="CoyName" required="required" maxlength="50" value="' . htmlspecialchars((string)$_POST['CoyName'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                    <span class="field-help">' . __('Enter the name as it should appear on invoices and reports.') . '</span>
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Official Business Number') . '</label>
                    <input type="text" name="CompanyNumber" maxlength="20" value="' . htmlspecialchars((string)$_POST['CompanyNumber'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Tax Reference / TIN') . '</label>
                    <input type="text" name="GSTNo" maxlength="20" value="' . htmlspecialchars((string)$_POST['GSTNo'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('Registered Office & Presence') . '</div>
            <div class="db-card-body card-grid-2">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="db-form-label">' . __('Registered Address') . '</label>
                    <input type="text" name="RegOffice1" value="' . htmlspecialchars((string)$_POST['RegOffice1'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Street / Building') . '" style="margin-bottom:8px;" />
                    <input type="text" name="RegOffice2" value="' . htmlspecialchars((string)$_POST['RegOffice2'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Area / District') . '" style="margin-bottom:8px;" />
                    <input type="text" name="RegOffice3" value="' . htmlspecialchars((string)$_POST['RegOffice3'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Additional Info') . '" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('City') . '</label>
                    <input type="text" name="RegOffice4" value="' . htmlspecialchars((string)$_POST['RegOffice4'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('State / Region') . '</label>
                    <input type="text" name="RegOffice5" value="' . htmlspecialchars((string)$_POST['RegOffice5'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Postal Code') . '</label>
                    <input type="text" name="RegOffice6" value="' . htmlspecialchars((string)$_POST['RegOffice6'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Official Email') . '</label>
                    <input type="email" name="Email" value="' . htmlspecialchars((string)$_POST['Email'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Main Telephone') . '</label>
                    <input type="tel" name="Telephone" value="' . htmlspecialchars((string)$_POST['Telephone'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('Official Fax') . '</label>
                    <input type="text" name="Fax" value="' . htmlspecialchars((string)$_POST['Fax'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                </div>
            </div>
        </div>

        <!-- Finance Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('Financial Baseline') . '</div>
            <div class="db-card-body card-grid-2">';
                $SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
                $currResult = DB_query($SQL);
                echo '<div class="form-group"><label class="db-form-label">' . __('Base Reporting Currency') . '</label><select name="CurrencyDefault" class="db-input">';
                while ($currRow = DB_fetch_array($currResult)) {
                    $selected = ($currRow['currabrev'] == $_POST['CurrencyDefault']) ? 'selected="selected"' : '';
                    echo '<option ' . $selected . ' value="' . $currRow['currabrev'] . '">' . $currRow['currency'] . '</option>';
                }
                echo '</select><span class="field-help">' . __('The main currency for financial statements.') . '</span></div>';
                echo ModernGLSelect('RetainedEarnings', (string)$_POST['RetainedEarnings'], 'BS', __('Retained Earnings (Clearance)'), __('Accumulated profits from previous years.'));
                echo ModernGLSelect('FreightAct', (string)$_POST['FreightAct'], 'P&L', __('Freight Integration Act'), __('Account for freight recoveries/charges.'));
echo '      </div>
        </div>

        <!-- Accounts Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('Control Ledger Accounts') . '</div>
            <div class="db-card-body card-grid-2">';
                echo ModernGLSelect('DebtorsAct', (string)$_POST['DebtorsAct'], 'BS', __('Debtors Control'), __('Accounts Receivable master account.'));
                echo ModernGLSelect('CreditorsAct', (string)$_POST['CreditorsAct'], 'BS', __('Creditors Control'), __('Accounts Payable master account.'));
                echo ModernGLSelect('PayrollAct', (string)$_POST['PayrollAct'], 'BS', __('Payroll Net Clearance'), __('Temporary account for payroll liabilities.'));
                echo ModernGLSelect('GRNAct', (string)$_POST['GRNAct'], 'BS', __('Goods Received (GRN) Suspense'), __('Suspense for un-invoiced goods received.'));
                echo ModernGLSelect('CommAct', (string)$_POST['CommAct'], 'BS', __('Commissions Accrual'), __('Accrued liabilities for sales commissions.'));
echo '      </div>
        </div>

        <!-- Variances Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('Exchange & Variations') . '</div>
            <div class="db-card-body card-grid-2">';
                echo ModernGLSelect('SalesExchangeDiffAct', (string)$_POST['SalesExchangeDiffAct'], 'P&L', __('Sales Exchange Diff'), __('Forex gains/losses on customer payments.'));
                echo ModernGLSelect('PurchasesExchangeDiffAct', (string)$_POST['PurchasesExchangeDiffAct'], 'P&L', __('Purchases Exchange Diff'), __('Forex gains/losses on supplier payments.'));
                echo ModernGLSelect('CurrencyExchangeDiffAct', (string)$_POST['CurrencyExchangeDiffAct'], 'P&L', __('Cash/Bank Currency Diff'), __('Forex gains/losses on multi-currency transfers.'));
                echo ModernGLSelect('UnrealizedCurrencyDiffAct', (string)$_POST['UnrealizedCurrencyDiffAct'], 'BS', __('Unrealized Forex P&L'), __('Year-end revaluation differences.'));
                echo ModernGLSelect('PytDiscountAct', (string)$_POST['PytDiscountAct'], 'P&L', __('Settlement Discounts'), __('Customer discounts for early payment.'));
echo '      </div>
        </div>

        <!-- Workflow Section -->
        <div class="db-card">
            <div class="db-card-header">' . __('GL Integrity Workflow') . '</div>
            <div class="db-card-body card-grid-2">
                <div class="form-group">
                    <label class="db-form-label">' . __('AR Integration Mode') . '</label>
                    <select name="GLLink_Debtors" class="db-input">
                        <option value="0" ' . (isset($_POST['GLLink_Debtors']) && $_POST['GLLink_Debtors'] == 0 ? 'selected' : '') . '>' . __('Disconnected (Manual Ledger)') . '</option>
                        <option value="1" ' . (isset($_POST['GLLink_Debtors']) && $_POST['GLLink_Debtors'] == 1 ? 'selected' : '') . '>' . __('Integrated (Real-time Posting)') . '</option>
                    </select>
                    <span class="field-help">' . __('Post customer transactions to GL immediately.') . '</span>
                </div>
                <div class="form-group">
                    <label class="db-form-label">' . __('AP Integration Mode') . '</label>
                    <select name="GLLink_Creditors" class="db-input">
                        <option value="0" ' . (isset($_POST['GLLink_Creditors']) && $_POST['GLLink_Creditors'] == 0 ? 'selected' : '') . '>' . __('Disconnected (Manual Ledger)') . '</option>
                        <option value="1" ' . (isset($_POST['GLLink_Creditors']) && $_POST['GLLink_Creditors'] == 1 ? 'selected' : '') . '>' . __('Integrated (Real-time Posting)') . '</option>
                    </select>
                    <span class="field-help">' . __('Post supplier transactions to GL immediately.') . '</span>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="db-form-label">' . __('Inventory Integration Mode') . '</label>
                    <select name="GLLink_Stock" class="db-input">
                        <option value="0" ' . (isset($_POST['GLLink_Stock']) && $_POST['GLLink_Stock'] == 0 ? 'selected' : '') . '>' . __('Passive Tracking') . '</option>
                        <option value="1" ' . (isset($_POST['GLLink_Stock']) && $_POST['GLLink_Stock'] == 1 ? 'selected' : '') . '>' . __('Asset Tracking (GL Integrated)') . '</option>
                    </select>
                    <span class="field-help">' . __('Automatic journals for stock moves, adjustments, and COGS.') . '</span>
                </div>
            </div>
        </div>

    </form>
</div>';

include(__DIR__ . '/includes/footer.php');
?>
