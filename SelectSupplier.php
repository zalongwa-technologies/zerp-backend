<?php

/* Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier */

require(__DIR__ . '/includes/session.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_GET['SupplierID'])) {
	$_SESSION['SupplierID']=$_GET['SupplierID'];
}
if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}
// only get geocode information if integration is on, and supplier has been selected
if ($_SESSION['geocode_integration'] == 1 AND isset($_SESSION['SupplierID'])) {
	$SQL = "SELECT * FROM geocode_param";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SQL = "SELECT suppliers.supplierid,
				suppliers.lat,
				suppliers.lng,
				suppliers.suppname,
				suppliers.address1,
				suppliers.address2,
				suppliers.address3,
				suppliers.address4
			FROM suppliers
			WHERE suppliers.supplierid = '" . $_SESSION['SupplierID'] . "'
			ORDER BY suppliers.supplierid";
	$Result2 = DB_query($SQL);
	$MyRow2 = DB_fetch_array($Result2);
	if ($MyRow && $MyRow2) {
		$lat = $MyRow2['lat'];
		$lng = $MyRow2['lng'];
		$suppname = $MyRow2['suppname'];
		$address1 = $MyRow2['address1'];
		$address2 = $MyRow2['address2'];
		$address3 = $MyRow2['address3'];
		$address4 = $MyRow2['address4'];
		$map_height = $MyRow['map_height'];
		$map_width = $MyRow['map_width'];
		$ExtraHeadContent = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>' . "\n";
		$ExtraHeadContent .= '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>' . "\n";
	}
}

$Title = __('Search Suppliers');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SelectSupplier';
include(__DIR__ . '/includes/header.php');

echo '<div class="db-page" style="max-width: 1400px; margin: 0 auto;">
		<div class="db-page-header">
			<div>
				<h1 class="db-page-title">' . $Title . '</h1>
				<p class="db-page-subtitle">' . __('Select a supplier to manage their account, orders, and transactions') . '</p>
			</div>
			<div class="db-page-actions">
				<a href="' . $RootPath . '/Suppliers.php" class="db-btn db-btn-primary">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line></svg>
					' . __('Add New Supplier') . '
				</a>
			</div>
		</div>';

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['SortBy'])) {
	$parts = explode('|', $_POST['SortBy']);
	$_POST['Sort'] = $parts[0];
	$_POST['Dir'] = $parts[1];
}
$allowedSorts = ['supplierid', 'suppname', 'currcode', 'address1'];
$sort = isset($_POST['Sort']) && in_array($_POST['Sort'], $allowedSorts) ? $_POST['Sort'] : 'suppname';
$dir = isset($_POST['Dir']) && $_POST['Dir'] === 'DESC' ? 'DESC' : 'ASC';
$_POST['Sort'] = $sort;
$_POST['Dir'] = $dir;

if (isset($_POST['Search'])
	OR isset($_POST['Go'])
	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])
	OR isset($_POST['SortBy'])) {

	if (mb_strlen($_POST['Keywords']) > 0 AND mb_strlen($_POST['SupplierCode']) > 0) {
		prnMsg( __('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
	}
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4,
					telephone,
					email,
					url
				FROM suppliers
				ORDER BY " . $sort . " " . $dir;
	} else {
		if (mb_strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						WHERE suppname " . LIKE . " '" . $SearchString . "'
						ORDER BY " . $sort . " " . $dir;
		} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						WHERE supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
						ORDER BY " . $sort . " " . $dir;
		}
	} //one of keywords or SupplierCode was more than a zero length string
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_row($Result);
		$SingleSupplierReturned = $MyRow[0];
	}
	if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
		$_SESSION['SupplierID'] = $SingleSupplierReturned;
		unset($_POST['Keywords']);
		unset($_POST['SupplierCode']);
		unset($_POST['Search']);
	}
} //end of if search

if (isset($_SESSION['SupplierID'])) {
	// A supplier is selected
	$SupplierName = '';
	$SQL = "SELECT suppliers.suppname
			FROM suppliers
			WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$MyRow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $MyRow[0];
	}
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . (isset($_SESSION['FormID']) ? htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8') : '') . '" />
		<input type="hidden" name="Sort" value="' . htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') . '" />
		<input type="hidden" name="Dir" value="' . htmlspecialchars($dir, ENT_QUOTES, 'UTF-8') . '" />
		<div>'; // Removed db-bottom-layout, using simple div container

$RecordsPerPage = 10;

// ACTIVE SUPPLIER (If selected)
if (isset($_SESSION['SupplierID'])) {
	echo '<div class="db-card" style="margin-bottom: 20px; background: var(--primary-soft); border: 1px solid var(--primary-light);">
			<div class="db-card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
				<div style="display: flex; align-items: center; gap: 16px;">
					<div style="width: 48px; height: 48px; background: var(--db-primary); color: white; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(var(--db-primary-rgb), 0.3);">
						<i class="fas fa-check"></i>
					</div>
					<div>
						<div style="font-size: 0.75rem; text-transform: uppercase; color: var(--db-primary); font-weight: 700; margin-bottom: 4px; opacity: 0.8;">' . __('Active Supplier') . '</div>
						<div class="db-font-bold text-primary" style="font-size: 1.2rem; line-height: 1.2;">' . $SupplierName . '</div>
						<div style="font-family: monospace; font-size: 0.85rem; margin-top: 2px; color: var(--text-muted);">[' . $_SESSION['SupplierID'] . ']</div>
					</div>
				</div>
				<button type="submit" name="Select" value="" class="db-btn db-btn-secondary" style="padding: 8px 16px;"><i class="fas fa-times" style="margin-right: 6px;"></i> ' . __('Clear Selection') . '</button>
			</div>
		  </div>';

	echo '<div class="db-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
			
			<!-- Inquiries & Reports -->
			<div class="db-card" style="transition: transform 0.2s, box-shadow 0.2s; cursor: default;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'var(--shadow-md)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'var(--shadow-sm)\';">
				<div class="db-card-header" style="border-bottom: 1px solid var(--border-soft); padding: var(--space-4); background: rgba(var(--db-primary-rgb), 0.02);">
					<h3 class="db-card-title"><i class="fas fa-file-alt" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Inquiries & Reports') . '</h3>
				</div>
				<div class="db-card-body" style="padding: var(--space-3);">
					<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px;">
						<li><a href="' . $RootPath . '/SupplierInquiry.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Account Inquiry') . '</a></li>
						<li><a href="' . $RootPath . '/SupplierGRNAndInvoiceInquiry.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '&SupplierName='.urlencode($SupplierName).'" class="db-menu-link">' . __('Delivery & GRN Inquiry') . '</a></li>
						<li><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Outstanding Purchase Orders') . '</a></li>
						<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('All Purchase Orders') . '</a></li>
						<li><a href="' . $RootPath . '/ShiptsList.php?SupplierID=' . $_SESSION['SupplierID'] . '&SupplierName=' . urlencode($SupplierName) . '" class="db-menu-link">' . __('Open Shipments') . '</a></li>
						<li><a href="' . $RootPath . '/Shipt_Select.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Search/Manage Shipments') . '</a></li>
						<li><a href="' . $RootPath . '/SuppPriceList.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Supplier Price List') . '</a></li>
					</ul>
				</div>
			</div>

			<!-- Transactions -->
			<div class="db-card" style="transition: transform 0.2s, box-shadow 0.2s; cursor: default;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'var(--shadow-md)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'var(--shadow-sm)\';">
				<div class="db-card-header" style="border-bottom: 1px solid var(--border-soft); padding: var(--space-4); background: rgba(var(--db-primary-rgb), 0.02);">
					<h3 class="db-card-title"><i class="fas fa-exchange-alt" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Transactions') . '</h3>
				</div>
				<div class="db-card-body" style="padding: var(--space-3);">
					<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px;">
						<li><a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('New Purchase Order') . '</a></li>
						<li><a href="' . $RootPath . '/SupplierInvoice.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Enter Supplier Invoice') . '</a></li>
						<li><a href="' . $RootPath . '/SupplierCredit.php?New=true&SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Enter Credit Note') . '</a></li>
						<li><a href="' . $RootPath . '/Payments.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Enter Payment / Receipt') . '</a></li>
						<li><a href="' . $RootPath . '/ReverseGRN.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Reverse GRN') . '</a></li>
					</ul>
				</div>
			</div>

			<!-- Maintenance -->
			<div class="db-card" style="transition: transform 0.2s, box-shadow 0.2s; cursor: default;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'var(--shadow-md)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'var(--shadow-sm)\';">
				<div class="db-card-header" style="border-bottom: 1px solid var(--border-soft); padding: var(--space-4); background: rgba(var(--db-primary-rgb), 0.02);">
					<h3 class="db-card-title"><i class="fas fa-tools" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Maintenance') . '</h3>
				</div>
				<div class="db-card-body" style="padding: var(--space-3);">
					<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px;">
						<li><a href="' . $RootPath . '/Suppliers.php" class="db-menu-link">' . __('Add New Supplier') . '</a></li>
						<li><a href="' . $RootPath . '/Suppliers.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Modify/Delete Supplier') . '</a></li>
						<li><a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Manage Contacts') . '</a></li>
						<li><a href="' . $RootPath . '/SellThroughSupport.php?SupplierID=' . $_SESSION['SupplierID'] . '" class="db-menu-link">' . __('Sell Through Support') . '</a></li>
						<li><a href="' . $RootPath . '/Shipments.php?NewShipment=Yes" class="db-menu-link">' . __('New Shipment Setup') . '</a></li>
						<li><a href="' . $RootPath . '/SuppLoginSetup.php" class="db-menu-link">' . __('Login Configuration') . '</a></li>
					</ul>
				</div>
			</div>
		</div>';
}

// TOP SEARCH BAR AND RESULTS COMBINED
echo '<div class="db-card" style="margin-bottom: var(--space-6);">';

// Search Header Section
echo '<div style="background: linear-gradient(145deg, var(--surface) 0%, var(--surface-alt) 100%); padding: var(--space-4); border-bottom: 1px solid var(--border-soft); border-radius: var(--radius-md) var(--radius-md) 0 0;">
		<div style="display: flex; flex-wrap: wrap; gap: var(--space-4); align-items: flex-end;">
			<div class="db-form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
				<label class="db-label" style="font-weight: 600;"><i class="fas fa-building" style="margin-right: 6px; color: var(--text-muted);"></i>' . __('Supplier Name') . '</label>
				<input type="text" name="Keywords" class="db-input" value="' . (isset($_POST['Keywords']) ? $_POST['Keywords'] : '') . '" placeholder="' . __('Keywords...') . '" />
			</div>
			<div class="db-form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
				<label class="db-label" style="font-weight: 600;"><i class="fas fa-barcode" style="margin-right: 6px; color: var(--text-muted);"></i>' . __('Supplier Code') . '</label>
				<input type="text" name="SupplierCode" class="db-input" value="' . (isset($_POST['SupplierCode']) ? $_POST['SupplierCode'] : '') . '" placeholder="' . __('Code...') . '" />
			</div>
			<div style="flex: 0 0 auto;">
				<button type="submit" name="Search" class="db-btn db-btn-primary" style="height: 42px; padding: 0 32px; font-weight: 600; font-size: 1rem; box-shadow: 0 4px 12px rgba(var(--db-primary-rgb), 0.2);">
					<i class="fas fa-search" style="margin-right: 8px;"></i> ' . __('Search Now') . '
				</button>
			</div>
		</div>
	  </div>';

if (!isset($_SESSION['SupplierID']) && !isset($_POST['Search'])) {
	// Empty State inside the unified card
	echo '<div class="db-card-body" style="min-height: 400px; display: flex; align-items: center; justify-content: center; text-align: center;">
			<div>
				<div style="width: 100px; height: 100px; background: var(--surface-alt); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--db-text-muted); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
					<i class="fas fa-search" style="font-size: 3rem; opacity: 0.3;"></i>
				</div>
				<h3 class="db-font-bold" style="color: var(--text-main); margin-bottom: 12px; font-size: 1.5rem;">' . __('Find a Supplier') . '</h3>
				<p style="max-width: 400px; margin: 0 auto; color: var(--text-muted); font-size: 1.1rem; line-height: 1.5;">' . __('Use the search form above to find and select a supplier to manage their account, orders, and transactions.') . '</p>
			</div>
		  </div>';
}

if (isset($_POST['Search'])) {
	$ListCount = DB_num_rows($Result);
	$ListPageMax = ceil($ListCount / $RecordsPerPage);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	
	echo '<input type="hidden" name="Search" value="' . __('Search Now') . '" />';

	// View Toggle Header inside unified card
	echo '<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border-soft); background: var(--surface);">
			<h3 class="db-card-title" style="margin: 0; font-size: 1.25rem;"><i class="fas fa-list" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Search Results') . ' <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: normal; margin-left: 8px;">(' . $ListCount . ' ' . __('found') . ')</span></h3>
		  </div>';

	// TABLE VIEW CONTAINER inside unified card
	echo '<style>
		.saris-table-wrapper {
			position: relative;
			overflow-x: auto;
			background-color: #fcfcfc;
		}
		.saris-table {
			width: 100%;
			font-size: 0.875rem;
			text-align: left;
			color: #4b5563;
			border-collapse: collapse;
		}
		.saris-table thead {
			background-color: #f3f4f6;
			border-bottom: 1px solid #d1d5db;
			color: #374151;
		}
		.saris-table th {
			padding: 12px 24px;
			font-weight: 500;
			white-space: nowrap;
		}
		.saris-table tbody tr {
			background-color: #fcfcfc;
			border-bottom: 1px solid #e5e7eb;
			transition: background-color 0.15s ease;
		}
		.saris-table tbody tr:hover {
			background-color: #f3f4f6;
		}
		.saris-table td {
			padding: 16px 24px;
			white-space: nowrap;
		}
	</style>';
	$renderSortHeader = function($label, $column) use ($sort, $dir) {
		if ($column === null) {
			return '<th style="padding:12px 24px;font-weight:500;white-space:nowrap;">' . __($label) . '</th>';
		}
		$isCurrent = $column === $sort;
		$nextDir = $isCurrent && $dir === 'ASC' ? 'DESC' : 'ASC';
		$icon = '';
		if ($isCurrent) {
			$icon = $dir === 'ASC' ? ' &uarr;' : ' &darr;';
		}
		return '<th style="position:sticky;top:0;background-color:#f3f4f6;z-index:10;box-shadow:0 1px 2px rgba(0,0,0,0.1);padding:0;font-weight:500;white-space:nowrap;">
					<button type="submit" name="SortBy" value="' . $column . '|' . $nextDir . '" style="background:transparent;border:none;width:100%;height:100%;padding:12px 24px;text-align:left;font-weight:inherit;color:inherit;cursor:pointer;font-family:inherit;font-size:inherit;white-space:nowrap;">' . __($label) . $icon . '</button>
				</th>';
	};

	echo '<div id="view-table-container">
			<div class="saris-table-wrapper" style="width:100%;-webkit-overflow-scrolling:touch;">
				<table class="saris-table">
					<thead>
						<tr>
							' . $renderSortHeader('Select', null) . '
							' . $renderSortHeader('Code', 'supplierid') . '
							' . $renderSortHeader('Supplier Name', 'suppname') . '
							' . $renderSortHeader('Currency', 'currcode') . '
							' . $renderSortHeader('Address', 'address1') . '
							' . $renderSortHeader('Contact Info', null) . '
						</tr>
					</thead>
					<tbody>';

	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $RecordsPerPage);
	}
	while (($MyRow = DB_fetch_array($Result)) AND ($RowIndex <> $RecordsPerPage)) {
		echo '<tr>
				<td style="width: 80px;">
					<button type="submit" name="Select" value="'.$MyRow['supplierid'].'" class="db-btn db-btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-md);">' . __('Select') . '</button>
				</td>
				<td><span class="ref-badge" style="font-size: 0.85rem; padding: 4px 8px;">' . $MyRow['supplierid'] . '</span></td>
				<td><div class="cust-name" style="font-weight: 600; font-size: 1rem; color: var(--text-main);">' . $MyRow['suppname'] . '</div></td>
				<td><span class="tag" style="background: var(--surface-alt); border: 1px solid var(--border-soft);">' . $MyRow['currcode'] . '</span></td>
				<td>
					<div style="font-size: 0.85rem; color: var(--text-main); white-space: nowrap;">
						' . $MyRow['address1'] . (empty($MyRow['address2']) ? '' : ', ' . $MyRow['address2']) . 
						(empty($MyRow['address3']) ? '' : ' <span style="color: var(--text-muted);"> | ' . $MyRow['address3'] . '</span>') .
						(empty($MyRow['address4']) ? '' : ' <span style="color: var(--text-muted);">' . $MyRow['address4'] . '</span>') . '
					</div>
				</td>
				<td>
					<div style="display: flex; flex-direction: row; gap: 12px; font-size: 0.85rem; white-space: nowrap;">
						' . (empty($MyRow['telephone']) ? '' : '<span style="color: var(--text-main);"><i class="fas fa-phone-alt" style="margin-right: 6px; color: var(--text-muted);"></i>' . $MyRow['telephone'] . '</span>') . '
						' . (empty($MyRow['email']) ? '' : '<a href="mailto:'.$MyRow['email'].'" style="color: var(--primary); display: inline-flex; align-items: center; transition: color 0.2s;"><i class="fas fa-envelope" style="margin-right: 6px;"></i>' . $MyRow['email'] . '</a>') . '
						' . (empty($MyRow['url']) ? '' : '<a href="'.$MyRow['url'].'" target="_blank" style="color: var(--primary); display: inline-flex; align-items: center; transition: color 0.2s;"><i class="fas fa-globe" style="margin-right: 6px;"></i>' . __('Website') . '</a>') . '
					</div>
				</td>
			</tr>';
		$RowIndex = $RowIndex + 1;
	}
	echo '</tbody></table></div></div>';
	

	// Pagination moved to the bottom of the unified card
	if ($ListPageMax > 1) {
		echo '<div class="noPrint" style="padding: 16px 20px; display:flex; justify-content:flex-end; border-top: 1px solid var(--border-soft);">';
		echo '<div style="display:inline-flex;border-radius:6px;box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);">';
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';

		if ($_POST['PageOffset'] > 1) {
			echo '<button type="submit" name="Previous" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-right:none;background:#f9fafb;color:#4b5563;border-radius:6px 0 0 6px;text-decoration:none;cursor:pointer;">
					<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
				  </button>';
		} else {
			echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-right:none;background:#f3f4f6;color:#9ca3af;border-radius:6px 0 0 6px;">
					<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
				  </span>';
		}

		echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 16px;border:1px solid #d1d5db;background:#f9fafb;color:#4b5563;font-size:14px;font-weight:500;">' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . '</span>';

		if ($_POST['PageOffset'] < $ListPageMax) {
			echo '<button type="submit" name="Next" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-left:none;background:#f9fafb;color:#4b5563;border-radius:0 6px 6px 0;text-decoration:none;cursor:pointer;">
					<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
				  </button>';
		} else {
			echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #d1d5db;border-left:none;background:#f3f4f6;color:#9ca3af;border-radius:0 6px 6px 0;">
					<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
				  </span>';
		}
		echo '</div></div>';
	}

}
echo '</div>'; // End UNIFIED SEARCH CARD
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		if (!isset($lat) || $lat == 0) {
			echo '<div class="db-alert db-alert-info" style="margin-top: var(--space-6);">' . __('Mapping is enabled, but no Mapping data to display for this Supplier.') . '</div>';
		} else {

			echo '<div class="db-card" style="margin-top: var(--space-6);">
					<div class="db-card-header">
						<h3 class="db-card-title"><i class="fas fa-map-marker-alt" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Supplier Location Mapping') . '</h3>
					</div>

					<div class="db-card-body" style="padding: 0;">
						<div class="centre" id="map" style="width: 100%; height: ' . $map_height . 'px; border-radius: 0 0 var(--radius-lg) var(--radius-lg);"></div>
					</div>
				</div>';
			
			// OpenStreetMap with Leaflet
			echo '<script>
			var map = L.map(\'map\').setView([' . $lat . ', ' . $lng . '], 13);
			
			L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
				attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
				maxZoom: 19
			}).addTo(map);
			
			var marker = L.marker([' . $lat . ', ' . $lng . ']).addTo(map);
			marker.bindPopup(\'<div style="font-family: var(--font-family);"><b style="color: var(--text-main); font-size: 1.1rem; display: block; margin-bottom: 8px;">\' + ' . json_encode(htmlspecialchars($suppname, ENT_QUOTES, 'UTF-8')) . ' + \'</b>\' + 
				' . json_encode(htmlspecialchars($address1, ENT_QUOTES, 'UTF-8')) . ' + \'<br>\' + 
				' . json_encode(htmlspecialchars($address2, ENT_QUOTES, 'UTF-8')) . ' + \'<br>\' + 
				' . json_encode(htmlspecialchars($address3, ENT_QUOTES, 'UTF-8')) . ' + \'<br>\' + 
				' . json_encode(htmlspecialchars($address4, ENT_QUOTES, 'UTF-8')) . ' + \'</div>\').openPopup();
			</script>';
		}
	}
	// Extended Info only if selected in Configuration
	if ($_SESSION['Extended_SupplierInfo'] == 1) {
		if ($_SESSION['SupplierID'] != '') {
			$SQL = "SELECT suppliers.suppname,
							suppliers.lastpaid,
							suppliers.lastpaiddate,
							suppliersince,
							currencies.decimalplaces AS currdecimalplaces
					FROM suppliers INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
					WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
			$DataResult = DB_query($SQL);
			$MyRow = DB_fetch_array($DataResult);
			// Select some more data about the supplier
			$SQL = "SELECT SUM(ovamount) AS total FROM supptrans WHERE supplierno = '" . $_SESSION['SupplierID'] . "' AND (type = '20' OR type='21')";
			$Total1Result = DB_query($SQL);
			$Row = DB_fetch_array($Total1Result);

			echo '<div class="db-card" style="margin-top: var(--space-6);">
					<div class="db-card-header" style="background: linear-gradient(90deg, rgba(var(--db-primary-rgb), 0.05) 0%, transparent 100%); border-bottom: 1px solid var(--border-soft);">
						<h3 class="db-card-title"><i class="fas fa-chart-line" style="margin-right: 8px; color: var(--db-primary);"></i> ' . __('Supplier Extended Insights') . '</h3>
					</div>

					<div class="db-table-wrapper">
						<table class="db-table">
							<tbody>
								<tr>
									<td class="val-bold" style="width: 250px;; color: var(--text-muted);"><i class="fas fa-calendar-alt" style="margin-right: 8px; opacity: 0.7;"></i> ' . __('Supplier Since') . '</td>
									<td style="font-weight: 500;">' . ConvertSQLDate($MyRow['suppliersince']) . '</td>
								</tr>
								<tr>
									<td class="val-bold" style="color: var(--text-muted);"><i class="fas fa-history" style="margin-right: 8px; opacity: 0.7;"></i> ' . __('Last Payment Activity') . '</td>
									<td>' . ($MyRow['lastpaiddate'] == 0 ? '<span style="color: var(--text-muted); font-style: italic;">' . __('No payments recorded') . '</span>' : '<strong style="color: var(--text-main); font-size: 1.1rem;">' . locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']) . '</strong> ' . __('on') . ' ' . ConvertSQLDate($MyRow['lastpaiddate'])) . '</td>
								</tr>
								<tr>
									<td class="val-bold" style="color: var(--text-muted);"><i class="fas fa-coins" style="margin-right: 8px; opacity: 0.7;"></i> ' . __('Total Cumulative Spend') . '</td>
									<td style="color: var(--primary); font-weight: 800; font-size: 1.25rem;">' . locale_number_format($Row['total'], $MyRow['currdecimalplaces']) . '</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>';
		}
	}
}
echo '	</div>
</form>'; // End div container
echo '</div>'; // End db-page


include(__DIR__ . '/includes/footer.php');
