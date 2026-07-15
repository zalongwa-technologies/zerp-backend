<?php

//	Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.
/*
	The country field is unneeded because the country_code is included inside the currency_code (firsts two letters).
*/

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'Setup';
$BookMark = 'Currencies';
$Title = __('Currencies Maintenance');
include(__DIR__ . '/includes/header.php');

// Inject premium Architect styles
echo '<style>
    :root {
        --primary: hsl(145, 63%, 38%);
        --primary-hover: hsl(145, 63%, 32%);
        --primary-dark: hsl(145, 45%, 22%);
        --primary-light: hsl(145, 40%, 95%);
        --primary-glow: hsla(145, 63%, 38%, 0.15);
        --page-padding: 30px;
        --border-color: #e5e7eb;
        --radius: 12px;
        --text-main: #111827;
        --text-muted: #6b7280;
    }
    .db-page {
        padding: var(--page-padding);
        max-width: 1400px;
        margin: 0 auto;
        font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    /* Header Section */
    .premium-header { 
        margin-bottom: 24px; 
        padding: 24px; 
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        gap: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .premium-header-title h1 {
        font-size: 1.75rem; 
        font-weight: 800; 
        color: var(--text-main); 
        margin: 0; 
        letter-spacing: -0.5px; /* Fixed extreme overlap issue */
        line-height: 1.2;
    }
    .premium-header-title .breadcrumbs {
        font-size: 0.75rem; 
        font-weight: 700; 
        color: var(--primary); 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        margin-bottom: 8px; 
        display: flex; 
        align-items: center; 
        gap: 8px;
    }

    /* Layout */
    .db-bottom-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        align-items: flex-start;
        padding-bottom: 50px;
    }
    .db-col-aside {
        flex: 1 1 350px;
        min-width: 320px;
        max-width: 450px;
    }
    .db-col-main {
        flex: 2 1 600px;
        min-width: 0; /* Prevents flex children from overflowing */
    }

    /* Cards */
    .arch-card { 
        background: #ffffff; 
        border-radius: var(--radius); 
        border: 1px solid var(--border-color); 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
    }
    .arch-card-header { 
        background: #f9fafb; 
        border-bottom: 1px solid var(--border-color); 
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    .arch-card-title {
        font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin:0;
        display: flex; align-items: center; gap: 10px;
    }

    /* Buttons */
    .arch-btn {
        display: inline-flex; align-items: center; gap: 8px; justify-content: center;
        padding: 10px 20px; border-radius: 8px;
        background: var(--primary); color: #ffffff; border: none;
        font-weight: 600; font-size: 0.9rem; cursor: pointer;
        transition: all 0.2s ease; text-decoration: none;
    }
    .arch-btn:hover { background: var(--primary-hover); transform: translateY(-1px); }
    .arch-btn-secondary { background: #ffffff; color: var(--text-main); border: 1px solid var(--border-color); }
    .arch-btn-secondary:hover { background: #f3f4f6; }
    .arch-btn-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .arch-btn-danger:hover { background: #fecaca; }

    /* Currency List */
    .currency-list-container {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }
    .currency-list-item {
        padding: 16px 24px; border-bottom: 1px solid var(--border-color); 
        transition: all 0.2s; cursor: pointer; display: flex; align-items: center; gap: 16px; 
        text-decoration: none; color: inherit; background: #ffffff;
    }
    .currency-list-item:last-child { border-bottom: none; }
    .currency-list-item:hover { background: #f9fafb; }
    .currency-list-item.active { background: var(--primary-light); border-left: 4px solid var(--primary); padding-left: 20px; }
    
    .arch-badge { padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }

    /* Form Grid */
    .arch-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        padding: 24px;
    }
    .arch-form-field { display: flex; flex-direction: column; gap: 8px; }
    .arch-form-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
    .arch-form-input { 
        width: 100%; height: 44px; border-radius: 8px; border: 1px solid var(--border-color); 
        padding: 0 16px; font-size: 0.95rem; transition: all 0.2s; color: var(--text-main); background: #ffffff;
        box-sizing: border-box;
    }
    .arch-form-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px var(--primary-glow); }
    .arch-form-input:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

    /* Functional Currency Summary */
    .summary-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #ffffff;
        padding: 32px 40px;
        border-radius: var(--radius);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        box-shadow: 0 10px 15px -3px var(--primary-glow);
        position: relative;
        overflow: hidden;
    }
    .summary-icon { position: absolute; right: -20px; top: -30px; font-size: 12rem; opacity: 0.08; transform: rotate(-15deg); }

    @media (max-width: 992px) {
        .db-bottom-layout { flex-direction: column; }
        .db-col-aside, .db-col-main { width: 100%; max-width: none; }
        .db-col-aside { order: 2; }
        .db-col-main { order: 1; }
        .summary-card { flex-direction: column; align-items: flex-start; gap: 24px; padding: 24px; }
        .premium-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .currency-list-container { max-height: 400px; }
    }
</style>';

echo '<div class="db-page">
		<header class="premium-header">
			<div class="premium-header-title">
                <div class="breadcrumbs">
                    <i class="fas fa-globe-africa"></i> ' . __('Internationalization') . ' <i class="fas fa-chevron-right" style="font-size: 0.6rem; opacity: 0.5;"></i> ' . __('Core Setup') . '
                </div>
                <h1>' . $Title . '</h1>
            </div>
            <div>
                <a href="' . $RootPath . '/SelectOrderItems.php" class="arch-btn arch-btn-secondary">
                    <i class="fas fa-arrow-left"></i> ' . __('Back to Orders') . '
                </a>
            </div>
		</header>';

include_once(__DIR__ . '/includes/CountriesArray.php');
include_once(__DIR__ . '/includes/CurrenciesArray.php');
include_once(__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_GET['SelectedCurrency'])) {
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])) {
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include(__DIR__ . '/includes/GetConfig.php');
$ForceConfigReload = false;

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];
$Errors = array();

if (isset($_POST['submit'])) {
	$InputError = 0;
	$i=1;

	$SQL="SELECT count(currabrev) FROM currencies WHERE currabrev='".$_POST['Abbreviation']."'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);

	if ($MyRow[0]!= 0 AND !isset($SelectedCurrency)) {
		$InputError = 1;
		prnMsg( __('The currency already exists in the database'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}

	if (!is_numeric(filter_number_format($_POST['ExchangeRate']))) {
		$InputError = 1;
		prnMsg(__('The exchange rate must be numeric'),'error');
		$Errors[$i] = 'ExchangeRate';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DecimalPlaces']))) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency must be numeric'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	} elseif (filter_number_format($_POST['DecimalPlaces'])<0) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency must be positive or zero'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	} elseif (filter_number_format($_POST['DecimalPlaces'])>4) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency is expected to be 4 or less'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	}

	if (mb_strlen($_POST['Country']) > 50) {
		$InputError = 1;
		prnMsg(__('The currency country must be 50 characters or less long'),'error');
		$Errors[$i] = 'Country';
		$i++;
	}
	if (mb_strlen($_POST['HundredsName']) > 15) {
		$InputError = 1;
		prnMsg(__('The hundredths name must be 15 characters or less long'),'error');
		$Errors[$i] = 'HundredsName';
		$i++;
	}
	if (($FunctionalCurrency !=  '') AND (isset($SelectedCurrency) AND $SelectedCurrency==$FunctionalCurrency)) {
		$_POST['ExchangeRate'] = 1;
	}

	if (isset($SelectedCurrency) AND $InputError != 1) {
		$SQLOldRate = "SELECT rate FROM currencies WHERE currabrev = '" . $SelectedCurrency . "'";
		$ResultOldRate = DB_query($SQLOldRate);
		$MyRow = DB_fetch_row($ResultOldRate);
		$OldRate = $MyRow[0];

		$SQL = "UPDATE currencies SET	country='". $_POST['Country']. "',
										hundredsname='" . $_POST['HundredsName'] . "',
										decimalplaces='" . filter_number_format($_POST['DecimalPlaces']) . "',
										rate='" .filter_number_format($_POST['ExchangeRate']) . "',
										webcart='" .$_POST['webcart'] . "'
					WHERE currabrev = '" . $SelectedCurrency . "'";
		$Msg = __('The currency definition record has been updated');
		$NewRate = $_POST['ExchangeRate'];

	} elseif ($InputError != 1) {
		$SQL = "INSERT INTO currencies (currency, currabrev, country, hundredsname, decimalplaces, rate, webcart) 
                VALUES ('" . $CurrencyName[$_POST['Abbreviation']] . "', '" . $_POST['Abbreviation'] . "', '" . $_POST['Country'] . "', '" . $_POST['HundredsName'] .  "', '" . filter_number_format($_POST['DecimalPlaces']) . "', '" . filter_number_format($_POST['ExchangeRate']) . "', '" . $_POST['webcart'] . "')";
		$Msg = __('The currency definition record has been added');
	}

	DB_Txn_Begin();
	$Result = DB_query($SQL);
	if ($InputError!= 1) prnMsg($Msg,'success');

	if (isset($SelectedCurrency) AND $InputError != 1) {
		AdjustBankAccountsDueToCurrencyExchangeRate($SelectedCurrency, $OldRate, $NewRate);
	}
	DB_Txn_Commit();

	unset($SelectedCurrency);
	unset($_POST['Country']);
	unset($_POST['HundredsName']);
	unset($_POST['DecimalPlaces']);
	unset($_POST['ExchangeRate']);
	unset($_POST['Abbreviation']);
	unset($_POST['webcart']);

} elseif (isset($_GET['delete'])) {
	$SQL= "SELECT COUNT(*) FROM debtorsmaster WHERE currcode = '" . $SelectedCurrency . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(__('Cannot delete this currency because customer accounts have been created referring to this currency'),'warn');
	} else {
		$SQL= "SELECT COUNT(*) FROM suppliers WHERE suppliers.currcode = '".$SelectedCurrency."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(__('Cannot delete this currency because supplier accounts have been created referring to this currency'),'warn');
		} else {
			$SQL= "SELECT COUNT(*) FROM banktrans WHERE currcode = '" . $SelectedCurrency . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				prnMsg(__('Cannot delete this currency because there are bank transactions that use this currency'),'warn');
			} elseif ($FunctionalCurrency==$SelectedCurrency) {
				prnMsg(__('Cannot delete this currency because it is the functional currency of the company'),'warn');
			} else {
				$SQL= "SELECT COUNT(*) FROM bankaccounts WHERE currcode = '" . $SelectedCurrency . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					prnMsg(__('Cannot delete this currency because there are bank accounts that use this currency'),'warn');
				} else {
					$SQL="DELETE FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
					$Result = DB_query($SQL);
					prnMsg(__('The currency definition record has been deleted'),'success');
				}
			}
		}
	}
}

// Start Dashboard Layout
echo '<div class="db-bottom-layout">
        <aside class="db-col-aside">
            <div class="arch-card" style="position: sticky; top: 100px;">
                <div class="arch-card-header">
                    <h3 class="arch-card-title"><i class="fas fa-list-ul" style="color:var(--primary);"></i> ' . __('All Currencies') . '</h3>
                </div>
                <div class="currency-list-container">';

    $SQL = "SELECT currabrev, country, hundredsname, rate, decimalplaces, webcart FROM currencies ORDER BY currabrev";
    $Result = DB_query($SQL);
    
    while ($MyRow = DB_fetch_array($Result)) {
        $isActive = (isset($SelectedCurrency) && $SelectedCurrency == $MyRow['currabrev']) ? 'active' : '';
        $isFunctional = ($MyRow['currabrev'] == $FunctionalCurrency);
        
        $ImageFile = mb_strtoupper($MyRow['currabrev']) . '.gif';
        if (!file_exists('images/flags/' . $ImageFile)) $ImageFile = 'blank.gif';

        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedCurrency=' . $MyRow['currabrev'] . '" class="currency-list-item ' . $isActive . '">
                <img alt="" src="' . $RootPath . '/images/flags/' . $ImageFile . '" style="width:24px; border-radius:3px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                <div style="flex:1;">
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 2px;">' . $MyRow['currabrev'] . ' ' . ($isFunctional ? '<span class="arch-badge" style="background:var(--primary-light); color:var(--primary-dark); font-size:0.6rem;">BASE</span>' : '') . '</div>
                    <div style="font-size: 0.8rem; color:var(--text-muted);">' . $CurrencyName[$MyRow['currabrev']] . '</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 700; font-size: 0.9rem; font-family: monospace; color: var(--text-main);">' . locale_number_format($MyRow['rate'], 'Variable') . '</div>
                </div>
              </a>';
    }

    echo '      </div>
            </div>
        </aside>

        <main class="db-col-main">
            <!-- Functional Currency Summary Card -->
            <div class="summary-card">
                <i class="fas fa-coins summary-icon"></i>
                <div style="position: relative; z-index: 2;">
                    <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.9; margin-bottom: 8px;">System Functional Currency</div>
                    <h2 style="font-size: 2.5rem; font-weight: 800; margin: 0; color: #ffffff; line-height: 1;">' . $FunctionalCurrency . '</h2>
                    <div style="font-size: 1.05rem; font-weight: 500; opacity: 0.9; margin-top: 8px;">' . $CurrencyName[$FunctionalCurrency] . '</div>
                </div>
                <div style="text-align: right; position: relative; z-index: 2;">
                    <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; opacity: 0.9;">Base Exchange Rate</div>
                    <div style="font-size: 2rem; font-weight: 800; font-family: monospace;">1.0000</div>
                </div>
            </div>';

    if (!isset($_GET['delete'])) {
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

        if (isset($SelectedCurrency) AND $SelectedCurrency != '') {
            $SQL = "SELECT currabrev, country, hundredsname, decimalplaces, rate, webcart FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
            $Result = DB_query($SQL);
            $MyRow = DB_fetch_array($Result);

            $_POST['Abbreviation'] = $MyRow['currabrev'];
            $_POST['Country'] = $MyRow['country'];
            $_POST['HundredsName'] = $MyRow['hundredsname'];
            $_POST['ExchangeRate'] = locale_number_format($MyRow['rate'], 'Variable');
            $_POST['DecimalPlaces'] = locale_number_format($MyRow['decimalplaces'], 0);
            $_POST['webcart'] = $MyRow['webcart'];

            echo '<input type="hidden" name="SelectedCurrency" value="' . $SelectedCurrency . '" />';
            echo '<input type="hidden" name="Abbreviation" value="' . $_POST['Abbreviation'] . '" />';

            $formTitle = __('Currency Maintenance');
            $formSubtitle = __('Updating') . ' <span style="color:var(--primary); font-weight:700;">' . $_POST['Abbreviation'] . '</span>';
            $formIcon = 'fas fa-edit';
        } else {
            if (!isset($_POST['Abbreviation'])) $_POST['Abbreviation'] = '';
            if (!isset($_POST['Country'])) $_POST['Country'] = '';
            if (!isset($_POST['HundredsName'])) $_POST['HundredsName'] = '';
            if (!isset($_POST['DecimalPlaces'])) $_POST['DecimalPlaces'] = 2;
            if (!isset($_POST['ExchangeRate'])) $_POST['ExchangeRate'] = 1;
            if (!isset($_POST['webcart'])) $_POST['webcart'] = 1;
            
            $formTitle = __('Add New Currency');
            $formSubtitle = __('Register a new international currency');
            $formIcon = 'fas fa-plus-circle';
        }

        echo '<div class="arch-card">
                <div class="arch-card-header">
                    <div>
                        <h3 class="arch-card-title"><i class="' . $formIcon . '" style="color:var(--primary);"></i> ' . $formTitle . '</h3>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">' . $formSubtitle . '</div>
                    </div>
                    ' . (isset($SelectedCurrency) ? '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" class="arch-btn arch-btn-danger">
                        <i class="fas fa-times"></i> ' . __('Exit Editor') . '
                    </a>' : '') . '
                </div>
                <div style="padding: 0;">
                    <div class="arch-form-grid">';

        if (isset($SelectedCurrency)) {
            echo '<div class="arch-form-field">
                    <label class="arch-form-label">' . __('ISO 4217 Currency Code') . '</label>
                    <input type="text" class="arch-form-input" value="' . $_POST['Abbreviation'] . '" disabled />
                </div>';
        } else {
            echo '<div class="arch-form-field">
                    <label class="arch-form-label">' . __('Select Currency') . '</label>
                    <select name="Abbreviation" class="arch-form-input" autofocus>';
            foreach ($CurrencyName as $CurrencyCode => $CurrencyNameTxt) {
                echo '<option value="' . $CurrencyCode . '">' . $CurrencyCode . ' - ' . $CurrencyNameTxt . '</option>';
            }
            echo '	</select>
                </div>';
        }

        echo '<div class="arch-form-field">
                <label class="arch-form-label">' . __('Country Name') . '</label>';
        if ($_POST['Abbreviation'] != $FunctionalCurrency) {
            echo '<input type="text" name="Country" class="arch-form-input" required maxlength="50" value="' . $_POST['Country'] . '" />';
        } else {
            echo '<input type="text" class="arch-form-input" value="' . $_POST['Country'] . '" disabled />';
            echo '<input type="hidden" name="Country" value="' . $_POST['Country'] . '" />';
        }
        echo '</div>';

        echo '<div class="arch-form-field">
                <label class="arch-form-label">' . __('Hundredths Name') . '</label>
                <input type="text" name="HundredsName" class="arch-form-input" required maxlength="15" value="' . $_POST['HundredsName'] . '" />
            </div>';

        echo '<div class="arch-form-field">
                <label class="arch-form-label">' . __('Decimal Places') . '</label>
                <input type="number" name="DecimalPlaces" class="arch-form-input" required min="0" max="4" value="' . $_POST['DecimalPlaces'] . '" />
            </div>';

        echo '<div class="arch-form-field">
                <label class="arch-form-label">' . __('Exchange Rate') . '</label>';
        if ($_POST['Abbreviation'] != $FunctionalCurrency) {
            echo '<input type="text" name="ExchangeRate" class="arch-form-input number" required value="' . $_POST['ExchangeRate'] . '" />';
        } else {
            echo '<input type="text" class="arch-form-input" value="' . $_POST['ExchangeRate'] . '" disabled />';
            echo '<input type="hidden" name="ExchangeRate" value="' . $_POST['ExchangeRate'] . '" />';
        }
        echo '</div>';

        echo '<div class="arch-form-field">
                <label class="arch-form-label">' . __('Show in webSHOP') . '</label>
                <select name="webcart" class="arch-form-input">
                    <option value="1" ' . (($_POST['webcart'] == 1) ? 'selected' : '') . '>' . __('Yes') . '</option>
                    <option value="0" ' . (($_POST['webcart'] == 0) ? 'selected' : '') . '>' . __('No') . '</option>
                </select>
            </div>
        </div></div>'; // End grid

        echo '<div style="padding: 24px; background: #f9fafb; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;" class="arch-form-footer">
                <button type="submit" name="submit" class="arch-btn">
                    <i class="fas fa-check"></i> ' . (isset($SelectedCurrency) ? __('Update Currency') : __('Create Currency')) . '
                </button>
            </div>
        </div></form>';
    }

    echo '</main></div>'; // End db-bottom-layout
    echo '</div>'; // End db-page

include(__DIR__ . '/includes/footer.php');
