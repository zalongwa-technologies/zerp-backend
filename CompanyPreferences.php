<?php

// Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
$Title = __('Company Preferences');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';

// Inject premium styles for the Architect workspace
$ExtraHeadContent = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
	.ScriptTitle { display: none !important; }
	.MainBody { padding: 0 !important; gap: 0 !important; background: transparent !important; }
	.db-page { padding: var(--space-8) var(--space-6); background: var(--bg-main); min-height: 100vh; font-family: "Inter", sans-serif; }
	
	.premium-header { margin-bottom: 30px; padding: 0 10px; }
	
	.db-card { 
		background: #ffffff; 
		border-radius: 16px; 
		border: 1px solid #e5e7eb; 
		box-shadow: var(--shadow-sm);
		overflow: hidden;
        height: auto;
	}
	.db-card-header { 
		background: #f9fafb; 
		border-bottom: 1px solid #f3f4f6; 
		padding: 24px 30px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.db-card-title {
		font-size: 1rem;
		font-weight: 850;
		color: #064e3b;
		margin: 0;
		display: flex;
		align-items: center;
		gap: 8px;
		text-transform: uppercase;
		letter-spacing: 1px;
	}
    .db-card-body { padding: 40px; }
	
	.architect-btn {
		display: inline-flex; align-items: center; gap: 10px;
		padding: 12px 28px; border-radius: 8px;
		background: #059669; color: #ffffff; border: none;
		font-weight: 700; font-size: 0.85rem; text-decoration: none;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
		cursor: pointer;
	}
	.architect-btn:hover { background: #065f46; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3); }
	.architect-btn i { color: #ffffff !important; }

    .db-form-label {
        font-size: 0.75rem; 
        text-transform: uppercase; 
        font-weight: 900; 
        letter-spacing: 1.2px; 
        color: #064e3b; 
        display: block; 
        margin-bottom: 10px;
    }
    .db-input {
        width: 100%; border-radius: 8px; height: 50px; font-weight: 600; border: 1px solid #d1fae5;
        padding: 0 20px; box-sizing: border-box; background: #ffffff; font-family: inherit; font-size: 0.95rem;
    }
    .db-input:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1); }
    .gl-account-picker { display: flex; flex-direction: column; gap: 8px; }
    .gl-account-search {
        height: 42px;
        font-size: 0.85rem;
        background: #f8fffb;
    }
    
    .db-bottom-layout { 
        display: flex;
        flex-direction: column;
        align-items: center; 
    }
    
    .db-form-container {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    .db-section {
        margin-bottom: 16px;
    }

    .card-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .field-help { font-size: 0.8rem; color: #6b7280; margin-top: 8px; font-weight: 500; display: block; line-height: 1.5; }

    .save-btn-container {
        display: flex; justify-content: flex-end; gap: 16px;
        padding-top: 20px; border-top: 1px solid #f3f4f6; margin-top: 20px;
    }

    @media (max-width: 768px) {
        .db-page { padding: var(--space-2) var(--space-1); }
        .db-card-body { padding: 25px 20px; }
        .architect-btn { width: 100%; justify-content: center; }
    }
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
    $HTML = '<div><label class="db-form-label">' . $Label . '</label><div class="gl-account-picker">';
    $HTML .= '<input type="search" class="db-input gl-account-search" data-target="' . $SelectID . '" placeholder="' . __('Search account code or name') . '" autocomplete="off" />';
    $HTML .= '<select id="' . $SelectID . '" name="' . $Name . '" class="db-input">';
    $HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
    while ($Row = DB_fetch_array($Result)) {
        $Selected = ($Row['accountcode'] == $Value) ? 'selected="selected"' : '';
        $OptionText = $Row['accountcode'] . ' - ' . $Row['accountname'];
        $HTML .= '<option ' . $Selected . ' value="' . htmlspecialchars($Row['accountcode'], ENT_QUOTES, 'UTF-8') . '" data-search="' . htmlspecialchars($OptionText, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($OptionText, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    $HTML .= '</select></div><span class="field-help">' . $Help . '</span></div>';
    return $HTML;
}

// initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i = 1;


if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

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

		// Sanitize all string inputs before writing to DB or files
		$safe = [];
		$string_keys = ['CoyName','CompanyNumber','GSTNo','RegOffice1','RegOffice2','RegOffice3',
						'RegOffice4','RegOffice5','RegOffice6','Telephone','Fax','Email',
						'CurrencyDefault','DebtorsAct','PytDiscountAct','CreditorsAct','PayrollAct',
						'GRNAct','CommAct','SalesExchangeDiffAct','PurchasesExchangeDiffAct',
						'CurrencyExchangeDiffAct','UnrealizedCurrencyDiffAct','RetainedEarnings',
						'GLLink_Debtors','GLLink_Creditors','GLLink_Stock','FreightAct'];
		foreach ($string_keys as $k) {
			$safe[$k] = DB_escape_string(isset($_POST[$k]) ? (string)$_POST[$k] : '');
		}

		$CompanyFileHandler = fopen($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
		$Contents = "<?php\n\n";
		$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . addslashes($_POST['CoyName']) . "';\n";
		$Contents.= "?>";

		if (!fwrite($CompanyFileHandler, $Contents)) {
			fclose($CompanyFileHandler);
			echo '<div class="error">' . __('Cannot write to the Companies.php file') . '</div>';
		}
		//close file
		fclose($CompanyFileHandler);

		$SQL = "UPDATE companies SET coyname='" . $safe['CoyName'] . "',
									companynumber = '" . $safe['CompanyNumber'] . "',
									gstno='" . $safe['GSTNo'] . "',
									regoffice1='" . $safe['RegOffice1'] . "',
									regoffice2='" . $safe['RegOffice2'] . "',
									regoffice3='" . $safe['RegOffice3'] . "',
									regoffice4='" . $safe['RegOffice4'] . "',
									regoffice5='" . $safe['RegOffice5'] . "',
									regoffice6='" . $safe['RegOffice6'] . "',
									telephone='" . $safe['Telephone'] . "',
									fax='" . $safe['Fax'] . "',
									email='" . $safe['Email'] . "',
									currencydefault='" . $safe['CurrencyDefault'] . "',
									debtorsact='" . $safe['DebtorsAct'] . "',
									pytdiscountact='" . $safe['PytDiscountAct'] . "',
									creditorsact='" . $safe['CreditorsAct'] . "',
									payrollact='" . $safe['PayrollAct'] . "',
									grnact='" . $safe['GRNAct'] . "',
									commissionsact='" . $safe['CommAct'] . "',
									salesexchangediffact='" . $safe['SalesExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $safe['PurchasesExchangeDiffAct'] . "',
									currencyexchangediffact='" . $safe['CurrencyExchangeDiffAct'] . "',
									unrealizedcurrencydiffact='" . $safe['UnrealizedCurrencyDiffAct'] . "',
									retainedearnings='" . $safe['RetainedEarnings'] . "',
									gllink_debtors='" . $safe['GLLink_Debtors'] . "',
									gllink_creditors='" . $safe['GLLink_Creditors'] . "',
									gllink_stock='" . $safe['GLLink_Stock'] ."',
									freightact='" . $safe['FreightAct'] . "'
								WHERE coycode=1";

			$ErrMsg =  __('The company preferences could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( __('Company preferences updated'),'success');

			/* Alter the exchange rates in the currencies table */

			/* Get default currency rate */
			$SQL="SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			$NewCurrencyRate=$MyRow[0];

			/* Set new rates */
			$SQL="UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
			$ErrMsg =  __('Could not update the currency rates');
			$Result = DB_query($SQL, $ErrMsg);

			/* End of update currencies */

			$ForceConfigReload = true; // Required to force a load even if stored in the session vars
			include(__DIR__ . '/includes/GetConfig.php');
			$ForceConfigReload = false;

	} else {
		prnMsg( __('Validation failed') . ', ' . __('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

// Always re-fetch current values from DB to safely populate the form
// This ensures the form renders correctly both on first load AND after a save
$SQL = "SELECT * FROM companies WHERE coycode=1";
$Result = DB_query($SQL);
if (DB_num_rows($Result) > 0) {
	$MyRow = DB_fetch_array($Result);
	$_POST['CoyName'] = $MyRow['coyname'] ?? '';
	$_POST['CompanyNumber'] = $MyRow['companynumber'] ?? '';
	$_POST['GSTNo'] = $MyRow['gstno'] ?? '';
	$_POST['RegOffice1'] = $MyRow['regoffice1'] ?? '';
	$_POST['RegOffice2'] = $MyRow['regoffice2'] ?? '';
	$_POST['RegOffice3'] = $MyRow['regoffice3'] ?? '';
	$_POST['RegOffice4'] = $MyRow['regoffice4'] ?? '';
	$_POST['RegOffice5'] = $MyRow['regoffice5'] ?? '';
	$_POST['RegOffice6'] = $MyRow['regoffice6'] ?? '';
	$_POST['Telephone'] = $MyRow['telephone'] ?? '';
	$_POST['Fax'] = $MyRow['fax'] ?? '';
	$_POST['Email'] = $MyRow['email'] ?? '';
	$_POST['CurrencyDefault'] = $MyRow['currencydefault'] ?? '';
	$_POST['DebtorsAct'] = $MyRow['debtorsact'] ?? '';
	$_POST['PytDiscountAct'] = $MyRow['pytdiscountact'] ?? '';
	$_POST['CreditorsAct'] = $MyRow['creditorsact'] ?? '';
	$_POST['PayrollAct'] = $MyRow['payrollact'] ?? '';
	$_POST['GRNAct'] = $MyRow['grnact'] ?? '';
	$_POST['CommAct'] = $MyRow['commissionsact'] ?? '';
	$_POST['SalesExchangeDiffAct'] = $MyRow['salesexchangediffact'] ?? '';
	$_POST['PurchasesExchangeDiffAct'] = $MyRow['purchasesexchangediffact'] ?? '';
	$_POST['CurrencyExchangeDiffAct'] = $MyRow['currencyexchangediffact'] ?? '';
	$_POST['UnrealizedCurrencyDiffAct'] = $MyRow['unrealizedcurrencydiffact'] ?? '';
	$_POST['RetainedEarnings'] = $MyRow['retainedearnings'] ?? '';
	$_POST['GLLink_Debtors'] = $MyRow['gllink_debtors'] ?? 0;
	$_POST['GLLink_Creditors'] = $MyRow['gllink_creditors'] ?? 0;
	$_POST['GLLink_Stock'] = $MyRow['gllink_stock'] ?? 0;
	$_POST['FreightAct'] = $MyRow['freightact'] ?? '';
}

// Ensure all expected POST variables are set as strings to avoid deprecation warnings
$expected_keys = [
	'CoyName', 'CompanyNumber', 'GSTNo', 'RegOffice1', 'RegOffice2', 'RegOffice3', 'RegOffice4',
	'RegOffice5', 'RegOffice6', 'Telephone', 'Fax', 'Email', 'CurrencyDefault', 'DebtorsAct',
	'PytDiscountAct', 'CreditorsAct', 'PayrollAct', 'GRNAct', 'CommAct', 'SalesExchangeDiffAct',
	'PurchasesExchangeDiffAct', 'CurrencyExchangeDiffAct', 'UnrealizedCurrencyDiffAct',
	'RetainedEarnings', 'GLLink_Debtors', 'GLLink_Creditors', 'GLLink_Stock', 'FreightAct'
];

foreach ($expected_keys as $key) {
	if (!isset($_POST[$key])) {
		$_POST[$key] = '';
	} else {
		$_POST[$key] = (string)$_POST[$key];
	}
}

/* Render Layout */
echo '<div class="db-page">
		<div class="premium-header">
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<div>
					<div style="font-size: 0.75rem; font-weight: 800; color: #6b7280; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.6;">
						<i class="fas fa-home"></i> ' . __('Setup') . ' <i class="fas fa-chevron-right" style="font-size: 0.6rem;"></i> ' . __('Company Profile') . '
					</div>
					<h1 style="font-size: 2.2rem; font-weight: 950; letter-spacing: -1.5px; color: #064e3b; margin: 0; line-height: 1;">' . $Title . '</h1>
				</div>
                <div>
                     <button type="submit" form="main-form" name="submit" class="architect-btn">
                        <i class="fas fa-cloud-upload-alt"></i> ' . __('Save All Preferences') . '
                    </button>
                </div>
			</div>
		</div>';

echo '<form id="main-form" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
        
        <div class="db-bottom-layout">
            <div class="db-form-container">

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-building"></i> ' . __('Company Identity & Legal') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">
                            <div>
                                <label class="db-form-label">' . __('Full Business Name') . '</label>
                                <input type="text" name="CoyName" required="required" maxlength="50" value="' . htmlspecialchars($_POST['CoyName'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                                <span class="field-help">' . __('Enter the name as it should appear on invoices and reports.') . '</span>
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Official Business Number') . '</label>
                                <input type="text" name="CompanyNumber" maxlength="20" value="' . htmlspecialchars($_POST['CompanyNumber'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Tax Reference / TIN') . '</label>
                                <input type="text" name="GSTNo" maxlength="20" value="' . htmlspecialchars($_POST['GSTNo'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-envelope-open-text"></i> ' . __('Registered Office & Presence') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">
                            <div>
                                <label class="db-form-label">' . __('Registered Address') . '</label>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <input type="text" name="RegOffice1" value="' . htmlspecialchars($_POST['RegOffice1'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Street / Building') . '" />
                                    <input type="text" name="RegOffice2" value="' . htmlspecialchars($_POST['RegOffice2'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Area / District') . '" />
                                    <input type="text" name="RegOffice3" value="' . htmlspecialchars($_POST['RegOffice3'], ENT_QUOTES, 'UTF-8') . '" class="db-input" placeholder="' . __('Additional Info') . '" />
                                </div>
                            </div>
                            <div>
                                <label class="db-form-label">' . __('City') . '</label>
                                <input type="text" name="RegOffice4" value="' . htmlspecialchars($_POST['RegOffice4'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('State / Region') . '</label>
                                <input type="text" name="RegOffice5" value="' . htmlspecialchars($_POST['RegOffice5'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Postal Code') . '</label>
                                <input type="text" name="RegOffice6" value="' . htmlspecialchars($_POST['RegOffice6'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Official Email') . '</label>
                                <input type="email" name="Email" value="' . htmlspecialchars($_POST['Email'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Main Telephone') . '</label>
                                <input type="tel" name="Telephone" value="' . htmlspecialchars($_POST['Telephone'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Official Fax') . '</label>
                                <input type="text" name="Fax" value="' . htmlspecialchars($_POST['Fax'], ENT_QUOTES, 'UTF-8') . '" class="db-input" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-piggy-bank"></i> ' . __('Financial Baseline') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">';
                            $SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
                            $currResult = DB_query($SQL);
                            echo '<div><label class="db-form-label">' . __('Base Reporting Currency') . '</label><select name="CurrencyDefault" class="db-input">';
                            while ($currRow = DB_fetch_array($currResult)) {
                                $selected = ($currRow['currabrev'] == $_POST['CurrencyDefault']) ? 'selected="selected"' : '';
                                echo '<option ' . $selected . ' value="' . $currRow['currabrev'] . '">' . $currRow['currency'] . '</option>';
                            }
                            echo '</select><span class="field-help">' . __('The main currency for financial statements.') . '</span></div>';
                            
                            echo ModernGLSelect('RetainedEarnings', $_POST['RetainedEarnings'], 'BS', __('Retained Earnings (Clearance)'), __('Accumulated profits from previous years.'));
                            echo ModernGLSelect('FreightAct', $_POST['FreightAct'], 'P&L', __('Freight Integration Act'), __('Account for freight recoveries/charges.'));
echo '                  </div>
                    </div>
                </div>

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-network-wired"></i> ' . __('Control Ledger Accounts') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">';
                            echo ModernGLSelect('DebtorsAct', $_POST['DebtorsAct'], 'BS', __('Debtors Control'), __('Accounts Receivable master account.'));
                            echo ModernGLSelect('CreditorsAct', $_POST['CreditorsAct'], 'BS', __('Creditors Control'), __('Accounts Payable master account.'));
                            echo ModernGLSelect('PayrollAct', $_POST['PayrollAct'], 'BS', __('Payroll Net Clearance'), __('Temporary account for payroll liabilities.'));
                            echo ModernGLSelect('GRNAct', $_POST['GRNAct'], 'BS', __('Goods Received (GRN) Suspense'), __('Suspense for un-invoiced goods received.'));
                            echo ModernGLSelect('CommAct', $_POST['CommAct'], 'BS', __('Commissions Accrual'), __('Accrued liabilities for sales commissions.'));
echo '                  </div>
                    </div>
                </div>

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-chart-line"></i> ' . __('Exchange & Variations') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">';
                            echo ModernGLSelect('SalesExchangeDiffAct', $_POST['SalesExchangeDiffAct'], 'P&L', __('Sales Exchange Diff'), __('Forex gains/losses on customer payments.'));
                            echo ModernGLSelect('PurchasesExchangeDiffAct', $_POST['PurchasesExchangeDiffAct'], 'P&L', __('Purchases Exchange Diff'), __('Forex gains/losses on supplier payments.'));
                            echo ModernGLSelect('CurrencyExchangeDiffAct', $_POST['CurrencyExchangeDiffAct'], 'P&L', __('Cash/Bank Currency Diff'), __('Forex gains/losses on multi-currency transfers.'));
                            echo ModernGLSelect('UnrealizedCurrencyDiffAct', $_POST['UnrealizedCurrencyDiffAct'], 'BS', __('Unrealized Forex P&L'), __('Year-end revaluation differences.'));
                            echo ModernGLSelect('PytDiscountAct', $_POST['PytDiscountAct'], 'P&L', __('Settlement Discounts'), __('Customer discounts for early payment.'));
echo '                  </div>
                    </div>
                </div>

                <div class="db-section db-card">
                    <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-cog"></i> ' . __('GL Integrity Workflow') . '</h3></div>
                    <div class="db-card-body">
                        <div class="card-grid-2">
                            <div>
                                <label class="db-form-label">' . __('AR Integration Mode') . '</label>
                                <select name="GLLink_Debtors" class="db-input">
                                    <option value="0" ' . ($_POST['GLLink_Debtors'] == 0 ? 'selected' : '') . '>' . __('Disconnected (Manual Ledger)') . '</option>
                                    <option value="1" ' . ($_POST['GLLink_Debtors'] == 1 ? 'selected' : '') . '>' . __('Integrated (Real-time Posting)') . '</option>
                                </select>
                                <span class="field-help">' . __('Post customer transactions to GL immediately.') . '</span>
                            </div>
                            <div>
                                <label class="db-form-label">' . __('AP Integration Mode') . '</label>
                                <select name="GLLink_Creditors" class="db-input">
                                    <option value="0" ' . ($_POST['GLLink_Creditors'] == 0 ? 'selected' : '') . '>' . __('Disconnected (Manual Ledger)') . '</option>
                                    <option value="1" ' . ($_POST['GLLink_Creditors'] == 1 ? 'selected' : '') . '>' . __('Integrated (Real-time Posting)') . '</option>
                                </select>
                                <span class="field-help">' . __('Post supplier transactions to GL immediately.') . '</span>
                            </div>
                            <div>
                                <label class="db-form-label">' . __('Inventory Integration Mode') . '</label>
                                <select name="GLLink_Stock" class="db-input">
                                    <option value="0" ' . ($_POST['GLLink_Stock'] == 0 ? 'selected' : '') . '>' . __('Passive Tracking') . '</option>
                                    <option value="1" ' . ($_POST['GLLink_Stock'] == 1 ? 'selected' : '') . '>' . __('Asset Tracking (GL Integrated)') . '</option>
                                </select>
                                <span class="field-help">' . __('Automatic journals for stock moves, adjustments, and COGS.') . '</span>
                            </div>
                        </div>
                        <div class="save-btn-container">
                            <button type="submit" name="submit" class="architect-btn" style="font-size: 1.1rem; padding: 12px 32px; border-radius: 12px; margin-top: 10px;">
                                <i class="fas fa-save"></i> ' . __('Save All Preferences') . '
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>';


include(__DIR__ . '/includes/footer.php');
