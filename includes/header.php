<?php

// echo the html header and page title

// Variables which should be defined in the page this file is included with, before the inclusion of this header.php:
// $Language
// $Title
// various $_SESSION items: Theme, DefaultDateFormat, Timeout, ShowPageHelp, ShowFieldHelp, FontSize, UsersRealName, etc...

/// @todo there are any more global variables use in this script than those 3... are we sure it would work if
///       called within a function?
global $Language;
global $Title;
global $LanguagesArray;
global $RootPath;

//if (!isset($RootPath)) {
//	$RootPath = dirname(htmlspecialchars(basename(__FILE__)));
//	if ($RootPath == '/' or $RootPath == "\\") {
//		$RootPath = '';
//	}
//}

$ScriptName = basename($_SERVER['SCRIPT_NAME']);

if (!isset($ViewTopic)) {$ViewTopic = 'Contents';}
if (!isset($BookMark)) {$BookMark = '';}

/// @todo should we move this to session.php?
if (isset($_GET['Theme'])) {
	$_SESSION['Theme'] = $_GET['Theme'];
	$SQL = "UPDATE www_users SET theme='" . $_GET['Theme'] . "' WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
}

if (isset($_SESSION['Language']) && isset($LanguagesArray[$_SESSION['Language']]) && $LanguagesArray[$_SESSION['Language']]['Direction'] == 'rtl' and mb_substr($_SESSION['Theme'], -4) != '-rtl') {
	$_SESSION['Theme'] = $_SESSION['Theme'] . '-rtl';
}

if (!headers_sent()) {
	header('cache-control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
} else {
	trigger_error('Page output started before header file was included, this should not happen');
}

echo "<!DOCTYPE html>\n";

/// @todo handle better the case where $Language is not in xx-YY format (full spec is at https://www.rfc-editor.org/rfc/rfc5646.html)
echo '<html lang="' , str_replace('_', '-', substr($Language ?? 'en_GB', 0, 5)) , '">
<head>
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8; cache-control: no-cache, no-store, must-revalidate; Pragma: no-cache" />
	<title>', __('webERP'), ' - ', $Title, '</title>
	<link rel="icon" href="', $RootPath, '/favicon.ico" type="image/x-icon" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/styles.css?version=1.7" rel="stylesheet" type="text/css" media="screen" />
	<link href="', $RootPath, '/css/print.css?version=1.3" rel="stylesheet" type="text/css" media="print" />
	<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '	<script async src="', $RootPath, '/javascripts/MiscFunctions.js?version=1.0"></script>' , "\n";
echo '	<script async src="', $RootPath, '/javascripts/manual.js"></script>' , "\n";
echo '	<script>
		localStorage.setItem("DateFormat", "', $_SESSION['DefaultDateFormat'], '");
		localStorage.setItem("Theme", "', $_SESSION['Theme'], '");
	</script>' , "\n";

if (isset($_SESSION['Timeout'])) {
	echo '	<meta http-equiv="refresh" content="' . (60 * $_SESSION['Timeout']) . ';url=' . $RootPath . '/Logout.php" />', "\n";
}

if ($_SESSION['ShowPageHelp'] == 0) {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_off.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
} else {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_on.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
}

if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_off.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
} else {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_on.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
}

/// @todo should we move this to index.php?
if (isset($_GET['FontSize'])) {
	$SQL = "UPDATE www_users
				SET fontsize='" . $_GET['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	switch ($_GET['FontSize']) {
		case 0:
			$_SESSION['ScreenFontSize'] = '0';
			$_SESSION['FontSize'] = '0.667rem';
		break;
		case 1:
			$_SESSION['ScreenFontSize'] = '1';
			$_SESSION['FontSize'] = '0.833rem';
		break;
		case 2:
			$_SESSION['ScreenFontSize'] = '2';
			$_SESSION['FontSize'] = '1rem';
		break;
		default:
			$_SESSION['ScreenFontSize'] = '1';
			$_SESSION['FontSize'] = '0.833rem';
	}
}

echo '	<style>
		body {
			font-size: ', $_SESSION['FontSize'], ';
		}
	</style>';

if (isset($ExtraHeadContent)) {
	echo "\n" . $ExtraHeadContent;
}

echo "\n</head>\n";

echo '<body onload="initial();' . ($BodyOnLoad ?? '') . '">' . "\n";

echo '<div class="help-bubble" id="help-bubble">
		<link rel="stylesheet" type="text/css" href="'. $RootPath . '/doc/Manual/css/manual.css" />
		<div class="help-header" id="help-header">
			<div id="help_exit" class="close_button" onclick="CloseHelp()" title="', __('Close this window'), '">X</div>
		</div>
		<div class="help-content" id="help-content"></div>
	</div>';
	
echo '
	<div id="mask">
		<div id="dialog"></div>
	</div>
	<dialog id="logoutDialog" style="margin: auto; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
		<div id="DialogContainer">
			<h3 id="LogoutDialogHeader">', __('Confirm Logout'), '</h3>
			<p id="LogoutDialogText">', __('Are you sure you wish to logout?'), '</p>
			<div id="DialogButtonContainer">
				<button id="cancelLogout">', __('Cancel'), '</button>
				<button id="confirmLogout">', __('Logout'), '</button>
			</div>
		</div>
	</dialog>
';

if (!isset($NoMenu) || $NoMenu != 1) {
	echo '<div class="dashboard-container">
			<div id="SidebarMask" class="sidebar-mask"></div>';
} else {
	echo '<div class="dashboard-container-standalone">';
}

// Icon map for sidebar modules
$moduleIcons = [
    'Dashboard'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
    'Sales'           => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
    'Receivables'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
    'Purchases'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
    'Payables'        => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
    'Inventory'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
    'Manufacturing'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>',
    'General Ledger'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
    'Asset Manager'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
    'SARIS Integration' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"></path><path d="M4 12h16"></path><path d="M4 17h16"></path><circle cx="7" cy="7" r="1"></circle><circle cx="17" cy="12" r="1"></circle><circle cx="7" cy="17" r="1"></circle></svg>',
    'Petty Cash'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
    'Setup'           => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>',
    'Utilities'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
];

if (!isset($NoMenu) || $NoMenu != 1) {
	echo '<nav class="ModuleList">
			<ul>';

	$dashboardActiveClass = (!isset($_GET['Application']) || $_GET['Application'] == 'Dashboard') ? 'class="ModuleSelected"' : '';
	$dashIcon = $moduleIcons['Dashboard'];
	echo '<li ' . $dashboardActiveClass . '><a href="' . $RootPath . '/index.php?Application=Dashboard"><span class="nav-icon">' . $dashIcon . '</span><span class="nav-label">' . __('Dashboard') . '</span></a></li>';

	if (isset($ModuleLink)) {
		for ($i=0; $i < count($ModuleLink); $i++) {
			if (in_array($ModuleLink[$i], $_SESSION['ModulesEnabled']) OR (isset($_SESSION['ModulesEnabled'][$i]) AND $_SESSION['ModulesEnabled'][$i] == '1')) {
				$activeClass = (isset($_SESSION['Module']) AND $ModuleLink[$i] == $_SESSION['Module']) ? 'class="ModuleSelected"' : '';
				$icon = $moduleIcons[$ModuleList[$i]] ?? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>';
				echo '<li ' . $activeClass . '><a href="' . $RootPath . '/index.php?Application=' . urlencode($ModuleLink[$i]) . '"><span class="nav-icon">' . $icon . '</span><span class="nav-label">' . $ModuleList[$i] . '</span></a></li>';
			}
		}
	}

	echo '	</ul>
		</nav>';
}


echo '<div class="dashboard-content">';

if (!isset($NoMenu) || $NoMenu != 1) {
	echo '<header class="noPrint">
			<button id="SidebarToggle" class="SidebarToggle" aria-label="Toggle Navigation">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
			</button>';

	$CompanyLogo = '';
	if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.png')) {
		$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.png';
	} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg')) {
		$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg';
	} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg')) {
		$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpg';
	} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.gif')) {
		$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.gif';
	}

	echo '<div id="AppIcon"><a href="' . $RootPath . '/index.php"></a></div>';

	if (isset($_SESSION['AllowedPageSecurityTokens']) && is_array($_SESSION['AllowedPageSecurityTokens']) && count($_SESSION['AllowedPageSecurityTokens']) > 1) {
		echo '<div id="ActionIcon">
				<select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
		echo '<option value=""><i>', __('Commonly used scripts'), '</i></option>';
		if (isset($_SESSION['Favourites'])) {
			foreach ($_SESSION['Favourites'] as $Url => $Caption) {
				echo '<option value="', $Url, '">', __($Caption), '</option>';
			}
		}
		echo '</select>
			</div>';
	}

	echo '<div id="Info">
			<a class="FontSize" data-title="', __('Change the settings for'), ' ', $_SESSION['UsersRealName'], '" href="', $RootPath, '/UserSettings.php">
				<span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span> ', $_SESSION['UsersRealName'], '
			</a>
		</div>';

	echo '<div id="ExitIcon">
			<a data-title="', __('Logout'), '" href="#" id="logoutLink">
				<span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span>
			</a>
		</div>';

	echo '</header>';
}

echo '<section class="MainBody">';

if (!isset($NoMenu) || $NoMenu != 1) {
	echo '<div class="ScriptTitle">';
	
	// Start with Home
	echo '<a href="' . $RootPath . '/index.php"><i class="fa-solid fa-house" style="font-size: 0.8rem;"></i> ' . __('Home') . '</a>';
	
	if ($ScriptName != 'index.php' && $ScriptName != 'Dashboard.php') {
		// Module Name
		if (isset($_SESSION['Module'])) {
			$SQL = "SELECT modulename FROM modules WHERE modulelink='" . $_SESSION['Module'] . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) > 0) {
				$MyRow = DB_fetch_array($Result);
				$ModuleName = __($MyRow['modulename']);
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/index.php?Application=' . $_SESSION['Module'] . '">' . $ModuleName . '</a>';
			}
		}
		
		// Further Navigation Flow mapping
		// Parse identifier
		$id_val = '';
		if (isset($_GET['identifier'])) {
			$id_val = $_GET['identifier'];
		} elseif (isset($_POST['identifier'])) {
			$id_val = $_POST['identifier'];
		}
		
		$customerName = '';
		$customerNo = '';
		if (!empty($id_val) && isset($_SESSION['Items' . $id_val])) {
			$cart_obj = $_SESSION['Items' . $id_val];
			if (is_object($cart_obj) && get_class($cart_obj) !== '__PHP_Incomplete_Class') {
				$customerName = $cart_obj->CustomerName ?? '';
				$customerNo = $cart_obj->DebtorNo ?? '';
			}
		}
		
		// If we are in the Sales flow:
		if (isset($_SESSION['Module']) && $_SESSION['Module'] == 'Sales') {
			if ($ScriptName == 'SelectCustomer.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select Customer') . '</span>';
			} elseif ($ScriptName == 'SelectOrderItems.php') {
				if (!empty($customerNo)) {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/SelectCustomer.php?identifier=' . urlencode($id_val) . '">' . __('Select Customer') . '</a>';
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					$cust_disp = !empty($customerName) ? htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') : $customerNo;
					echo '<span class="active-page">' . __('Order Lines') . ' (' . $cust_disp . ')</span>';
				} else {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<span class="active-page">' . __('Select Customer') . '</span>';
				}
			} elseif ($ScriptName == 'DeliveryDetails.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectOrderItems.php?identifier=' . urlencode($id_val) . '">' . __('Order Lines') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Delivery Details') . '</span>';
			} elseif ($ScriptName == 'ConfirmDispatch_Invoice.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectOrderItems.php?identifier=' . urlencode($id_val) . '">' . __('Order Lines') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/DeliveryDetails.php?identifier=' . urlencode($id_val) . '">' . __('Delivery Details') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Confirm Dispatch') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'AR') {
			// Receivables Module Flow
			if ($ScriptName == 'SelectCustomer.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select Customer') . '</span>';
			} elseif ($ScriptName == 'Customers.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Customer Maintenance') . '</span>';
			} elseif ($ScriptName == 'CustomerInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Customer Transactions') . '</span>';
			} elseif ($ScriptName == 'CustomerAccount.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Customer Statement') . '</span>';
			} elseif ($ScriptName == 'AddCustomerContacts.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Customer Contacts') . '</span>';
			} elseif ($ScriptName == 'AddCustomerNotes.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Customer Notes') . '</span>';
			} elseif ($ScriptName == 'SelectCreditItems.php') {
				$creditName = '';
				$creditNo = '';
				if (!empty($id_val) && isset($_SESSION['CreditItems' . $id_val])) {
					$cart_obj = $_SESSION['CreditItems' . $id_val];
					if (is_object($cart_obj) && get_class($cart_obj) !== '__PHP_Incomplete_Class') {
						$creditName = $cart_obj->CustomerName ?? '';
						$creditNo = $cart_obj->DebtorNo ?? '';
					}
				}
				if (!empty($creditNo)) {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/SelectCustomer.php?identifier=' . urlencode($id_val) . '">' . __('Select Customer') . '</a>';
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					$cust_disp = !empty($creditName) ? htmlspecialchars($creditName, ENT_QUOTES, 'UTF-8') : $creditNo;
					echo '<span class="active-page">' . __('Credit Items') . ' (' . $cust_disp . ')</span>';
				} else {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<span class="active-page">' . __('Select Customer') . '</span>';
				}
			} elseif ($ScriptName == 'CreditItemsControlled.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php?identifier=' . urlencode($id_val) . '">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCreditItems.php?identifier=' . urlencode($id_val) . '">' . __('Credit Items') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Controlled Items') . '</span>';
			} elseif ($ScriptName == 'CustomerReceipt.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php?identifier=' . urlencode($id_val) . '">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Enter Receipts') . '</span>';
			} elseif ($ScriptName == 'CustomerAllocations.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectCustomer.php?identifier=' . urlencode($id_val) . '">' . __('Select Customer') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Allocate Receipts') . '</span>';
			} elseif ($ScriptName == 'SelectSalesOrder.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select Order to Invoice') . '</span>';
			} elseif ($ScriptName == 'ConfirmDispatch_Invoice.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSalesOrder.php?identifier=' . urlencode($id_val) . '">' . __('Select Order to Invoice') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Confirm Invoicing') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'PO') {
			// Purchases Module Flow
			if ($ScriptName == 'PO_Header.php') {
				$suppName = '';
				$suppNo = '';
				if (!empty($id_val) && isset($_SESSION['PO' . $id_val])) {
					$po_obj = $_SESSION['PO' . $id_val];
					if (is_object($po_obj) && get_class($po_obj) !== '__PHP_Incomplete_Class') {
						$suppName = $po_obj->SupplierName ?? '';
						$suppNo = $po_obj->SupplierID ?? '';
					}
				}
				
				if (!empty($_SESSION['ExistingOrder'])) {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php">' . __('Purchase Orders') . '</a>';
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<span class="active-page">' . __('Edit Purchase Order') . ' (' . $_SESSION['ExistingOrder'] . ')</span>';
				} else {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					$active_text = __('New Purchase Order');
					if (!empty($suppNo)) {
						$supp_disp = !empty($suppName) ? htmlspecialchars($suppName, ENT_QUOTES, 'UTF-8') : $suppNo;
						$active_text .= ' (' . $supp_disp . ')';
					}
					echo '<span class="active-page">' . $active_text . '</span>';
				}
			} elseif ($ScriptName == 'PO_Items.php') {
				$suppName = '';
				$suppNo = '';
				if (!empty($id_val) && isset($_SESSION['PO' . $id_val])) {
					$po_obj = $_SESSION['PO' . $id_val];
					if (is_object($po_obj) && get_class($po_obj) !== '__PHP_Incomplete_Class') {
						$suppName = $po_obj->SupplierName ?? '';
						$suppNo = $po_obj->SupplierID ?? '';
					}
				}
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				if (!empty($_SESSION['ExistingOrder'])) {
					echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php">' . __('Purchase Orders') . '</a>';
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/PO_Header.php?identifier=' . urlencode($id_val) . '">' . __('Edit Purchase Order') . '</a>';
				} else {
					echo '<a href="' . $RootPath . '/PO_Header.php?identifier=' . urlencode($id_val) . '">' . __('New Purchase Order') . '</a>';
				}
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				$active_text = __('PO Items');
				if (!empty($suppNo)) {
					$supp_disp = !empty($suppName) ? htmlspecialchars($suppName, ENT_QUOTES, 'UTF-8') : $suppNo;
					$active_text .= ' (' . $supp_disp . ')';
				}
				echo '<span class="active-page">' . $active_text . '</span>';
			} elseif ($ScriptName == 'PO_SelectOSPurchOrder.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Orders') . '</span>';
			} elseif ($ScriptName == 'PO_SelectPurchOrder.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Order Inquiry') . '</span>';
			} elseif ($ScriptName == 'PO_OrderDetails.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . __('Purchase Order Inquiry') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Order Details') . '</span>';
			} elseif ($ScriptName == 'PO_AuthoriseMyOrders.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Orders to Authorise') . '</span>';
			} elseif ($ScriptName == 'OffersReceived.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SupplierTenderCreate.php">' . __('Tenders') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Process Tenders and Offers') . '</span>';
			} elseif ($ScriptName == 'SupplierTenders.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SupplierTenderCreate.php">' . __('Tenders') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Tender Offers') . '</span>';
			} elseif ($ScriptName == 'SupplierTenderCreate.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Tenders') . '</span>';
			} elseif ($ScriptName == 'PurchaseByPrefSupplier.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Order Grid Entry') . '</span>';
			} elseif ($ScriptName == 'SupplierPriceList.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Maintain Supplier Price Lists') . '</span>';
			} elseif ($ScriptName == 'POReport.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Order Detail Or Summary Inquiries') . '</span>';
			} elseif ($ScriptName == 'POFinancialPlanning.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchase Orders Financial Planning') . '</span>';
			} elseif ($ScriptName == 'PurchasesReport.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Purchases from Suppliers') . '</span>';
			} elseif ($ScriptName == 'SuppPriceList.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Price List') . '</span>';
			} elseif ($ScriptName == 'Shipt_Select.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select A Shipment') . '</span>';
			} elseif ($ScriptName == 'SelectSupplier.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Shipment Entry') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'AP') {
			
			if ($ScriptName == 'SelectSupplier.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select Supplier') . '</span>';
			} elseif ($ScriptName == 'Suppliers.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Add Supplier') . '</span>';
			} elseif ($ScriptName == 'Factors.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Maintain Factor Companies') . '</span>';
			} elseif ($ScriptName == 'SupplierContacts.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Contacts') . '</span>';
			} elseif ($ScriptName == 'SupplierCredit.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Credit Note') . '</span>';
			} elseif ($ScriptName == 'SupplierInvoice.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Enter Supplier Invoice') . '</span>';
			} elseif ($ScriptName == 'Payments.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Enter Payments') . '</span>';
			} elseif ($ScriptName == 'PaymentAllocations.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Allocate Payments') . '</span>';
			} elseif ($ScriptName == 'SupplierAllocations.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Allocations') . '</span>';
			} elseif ($ScriptName == 'AgedSuppliers.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Aged Supplier Report') . '</span>';
			} elseif ($ScriptName == 'PDFSuppTransListing.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('List Daily Transactions') . '</span>';
			} elseif ($ScriptName == 'OutstandingGRNs.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Outstanding GRNs Report') . '</span>';
			} elseif ($ScriptName == 'SuppPaymentRun.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Payment Run Report') . '</span>';
			} elseif ($ScriptName == 'PDFRemittanceAdvice.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Remittance Advices') . '</span>';
			} elseif ($ScriptName == 'SupplierBalsAtPeriodEnd.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Balances At A Prior Month End') . '</span>';
			} elseif ($ScriptName == 'SupplierTransInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Transaction Inquiries') . '</span>';
			} elseif ($ScriptName == 'SuppWhereAlloc.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Where Allocated Inquiry') . '</span>';
			} elseif ($ScriptName == 'SupplierInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select Supplier') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Supplier Inquiry') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'stock') {
			
			if ($ScriptName == 'SelectProduct.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select An Item') . '</span>';
			} elseif ($ScriptName == 'Stocks.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				if (isset($_GET['StockID']) || isset($_POST['StockID'])) {
					echo '<a href="' . $RootPath . '/SelectProduct.php">' . __('Select An Item') . '</a>';
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				}
				echo '<span class="active-page">' . __('Item Maintenance') . '</span>';
			} elseif ($ScriptName == 'StockStatus.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectProduct.php">' . __('Select An Item') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Inventory Item Status') . '</span>';
			} elseif ($ScriptName == 'StockMovements.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectProduct.php">' . __('Select An Item') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Inventory Item Movements') . '</span>';
			} elseif ($ScriptName == 'StockUsage.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectProduct.php">' . __('Select An Item') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Inventory Item Usage') . '</span>';
			} elseif ($ScriptName == 'StockCostUpdate.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectProduct.php">' . __('Select An Item') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Update Item Cost') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'manuf') {
			if ($ScriptName == 'SelectWorkOrder.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select A Work Order') . '</span>';
			} elseif ($ScriptName == 'WorkOrderStatus.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Work Order Status') . '</span>';
			} elseif ($ScriptName == 'WorkOrderIssue.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Issue Materials') . '</span>';
			} elseif ($ScriptName == 'WorkOrderReceive.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Receive Work Order') . '</span>';
			} elseif ($ScriptName == 'WorkOrderCosting.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Work Order Costing') . '</span>';
			} elseif ($ScriptName == 'WOSerialNos.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Work Order Serial Numbers') . '</span>';
			} elseif ($ScriptName == 'WorkOrderEntry.php') {
				if (isset($_REQUEST['WO'])) {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select A Work Order') . '</a>';
				}
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Work Order Entry') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'GL') {
			if ($ScriptName == 'SelectGLAccount.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Account Inquiry') . '</span>';
			} elseif ($ScriptName == 'GLAccountInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectGLAccount.php">' . __('Account Inquiry') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Account Transactions') . '</span>';
			} elseif ($ScriptName == 'GLTransInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('GL Transaction Inquiry') . '</span>';
			} elseif ($ScriptName == 'GLJournalInquiry.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('General Ledger Journal Inquiry') . '</span>';
			} elseif ($ScriptName == 'BankReconciliation.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Bank Account Reconciliation') . '</span>';
			} elseif ($ScriptName == 'GLProfit_Loss.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Profit and Loss Statement') . '</span>';
			} elseif ($ScriptName == 'GLBalanceSheet.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Balance Sheet') . '</span>';
			} elseif ($ScriptName == 'GLTrialBalance.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Trial Balance') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'FA') {
			if ($ScriptName == 'SelectAsset.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Select an Asset') . '</span>';
			} elseif ($ScriptName == 'FixedAssetItems.php') {
				if (isset($_REQUEST['AssetID']) || isset($_GET['AssetID']) || isset($_POST['AssetID'])) {
					echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
					echo '<a href="' . $RootPath . '/SelectAsset.php">' . __('Select an Asset') . '</a>';
				}
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Asset Maintenance') . '</span>';
			} elseif ($ScriptName == 'FixedAssetTransfer.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<a href="' . $RootPath . '/SelectAsset.php">' . __('Select an Asset') . '</a>';
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Change Asset Location') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'system') {
			if ($ScriptName == 'WWW_Users.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('User Maintenance') . '</span>';
			} elseif ($ScriptName == 'CompanyPreferences.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Company Preferences') . '</span>';
			} elseif ($ScriptName == 'SystemParameters.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('System Parameters') . '</span>';
			} elseif ($ScriptName == 'Currencies.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('Currency Maintenance') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'PC') {
			echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
			echo '<span class="active-page">' . $Title . '</span>';
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'SARIS') {
			if ($ScriptName == 'SARIS_Settings.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('SARIS Settings') . '</span>';
			} elseif ($ScriptName == 'SARIS_Invoices.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('SARIS Invoices') . '</span>';
			} elseif ($ScriptName == 'SARIS_Payments.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('SARIS Payments') . '</span>';
			} elseif ($ScriptName == 'SARIS_Students.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('SARIS Students') . '</span>';
			} elseif ($ScriptName == 'SARIS_SyncHistory.php') {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . __('SARIS Sync History') . '</span>';
			} else {
				echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
				echo '<span class="active-page">' . $Title . '</span>';
			}
		} elseif (isset($_SESSION['Module']) && $_SESSION['Module'] == 'Utilities') {
			echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
			echo '<span class="active-page">' . $Title . '</span>';
		} else {
			// Fallback for other modules
			echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
			echo '<span class="active-page">' . $Title . '</span>';
		}
		
	} elseif ($ScriptName == 'Dashboard.php') {
		echo '<span class="separator"><i class="fa-solid fa-chevron-right"></i></span>';
		echo '<span class="active-page">' . __('Dashboard') . '</span>';
	}
	
	echo '</div>';
}

echo '<div id="MessageContainerHead"></div>';

?>
	<script>
		const sidebarToggle = document.getElementById("SidebarToggle");
		const sidebarMask = document.getElementById("SidebarMask");

		function toggleSidebar() {
			if (window.innerWidth > 1024) {
				document.body.classList.toggle("sidebar-collapsed");
			} else {
				document.body.classList.toggle("sidebar-active");
			}
		}

		sidebarToggle.onclick = toggleSidebar;
		sidebarMask.onclick = function() {
			document.body.classList.remove("sidebar-active");
		};
	</script>
<?php?>
