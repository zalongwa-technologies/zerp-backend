<?php

/* Selection of customer - from where all customer related maintenance, transactions and inquiries start */

require(__DIR__ . '/includes/session.php');

$Title = __('Search Customers');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'SelectCustomer';
$ExtraHeadContent = '
<style>
	.card-v2 { background: var(--surface); border-radius: 24px; border: 1px solid var(--border-soft); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 24px; transition: transform 0.2s, box-shadow 0.2s; }
	.card-v2:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08); }
	.card-header-v2 { background: #f9fafb; border-bottom: 1px solid var(--surface-alt); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
	.card-header-v2 h3 { font-size: 1rem; font-weight: 850; color: #064e3b; margin: 0; text-transform: uppercase; letter-spacing: 1.5px; display: flex; align-items: center; }
	
	/* Architect Layout Engine */
	.architect-grid { display: grid !important; gap: 24px !important; }
	.architect-grid-6 { grid-template-columns: repeat(6, 1fr) !important; }
	.architect-grid-4 { grid-template-columns: repeat(4, 1fr) !important; }
	.architect-grid-3 { grid-template-columns: repeat(3, 1fr) !important; }
	.architect-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
	
	/* Action Tiles */
	.db-action-tile { 
		display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
		padding: 24px 16px; border-radius: 20px; background: var(--surface); border: 1.5px solid var(--surface-alt);
		text-decoration: none; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
	}
	.db-action-tile:hover { background: #f0fdf4; border-color: #059669; transform: scale(1.05); }
	
	.db-action-icon { 
		width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; 
		background: var(--surface-alt); color: var(--text-body); transition: all 0.2s;
	}
	.db-action-tile:hover .db-action-icon { transform: rotate(-5deg); }
	
	.db-icon-blue { background: #eff6ff; color: #3b82f6; }
	.db-icon-green { background: #f0fdf4; color: #059669; }
	.db-icon-red { background: #fef2f2; color: #dc2626; }
	.db-icon-neutral { background: var(--surface-alt); color: var(--text-muted); }
	
	.db-action-text { font-size: 0.8rem; font-weight: 700; color: #1e293b; text-align: center; }
	
	/* Badges & UI Elements */
	.db-badge { padding: 5px 12px; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
	.db-badge-success { background: #dcfce7; color: #166534; }
	.db-badge-info { background: var(--info-bg); color: #075985; }
	
	.db-btn { 
		display: inline-flex; align-items: center; justify-content: center; gap: 8px;
		padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 0.85rem;
		border: none; cursor: pointer; transition: all 0.2s;
	}
	.db-btn-primary { background: #059669; color: var(--surface); }
	.db-btn-primary:hover { background: #065f46; transform: translateY(-1px); }
	.db-btn-outline { background: transparent; border: 1.5px solid #e2e8f0; color: var(--text-body); }
	.db-btn-outline:hover { background: var(--surface-alt); border-color: var(--border); }
	.db-btn-small { padding: 6px 12px; font-size: 0.75rem; }

	.db-ref { font-family: "JetBrains Mono", monospace; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 4px; border: 1px solid #d1fae5; }
	.db-info-list { display: flex; flex-direction: column; gap: 8px; }
	.db-info-item { display: flex; align-items: center; gap: 10px; color: var(--text-body); }
	.db-muted { color: #94a3b8; }
	.db-link { color: #059669; text-decoration: none; font-weight: 600; }
	.db-link:hover { text-decoration: underline; }

	.db-table-wrapper { position: relative; overflow-x: auto; background-color: var(--surface); box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-radius: 8px; border: 1px solid var(--border-soft); margin-bottom: 1rem; max-width: 100%; }
        .db-table { width: 100%; font-size: 0.875rem; text-align: left; color: var(--text-body); border-collapse: collapse; white-space: nowrap; }
        .db-table thead { background-color: var(--surface-alt); border-bottom: 1px solid var(--border); color: var(--text-main); }
        .db-table th { padding: 12px 24px; font-weight: 500; white-space: nowrap; }
        .db-table tbody tr { background-color: var(--surface); border-bottom: 1px solid var(--border-soft); transition: background-color 0.15s ease; }
        .db-table tbody tr:hover { background-color: var(--surface-alt); }
        .db-table td { padding: 16px 24px; white-space: nowrap; font-weight: 500; color: var(--text-main); }
        .db-table th.number, .db-table td.number { text-align: right; }
	
	/* Responsive Adjustments */
	@media (max-width: 1200px) { .db-grid-6 { grid-template-columns: repeat(3, 1fr); } }
	@media (max-width: 768px) { 
		.db-grid-6 { grid-template-columns: repeat(2, 1fr); } 
		.db-grid-4, .db-grid-3 { grid-template-columns: repeat(2, 1fr); }
		.premium-header { padding: 30px; flex-direction: column; align-items: flex-start; gap: 20px; }
	}
</style>';

include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include_once(__DIR__ . '/includes/UIComponents.php');

echo '<div class="db-page">
		<div class="premium-header">
			<div>
				<div style="font-size: 0.72rem; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; text-transform: lowercase; letter-spacing: 1px;">
					<a href="index.php" class="breadcrumb-item"><i class="fas fa-home"></i> ' . __('home') . '</a>
					<i class="fas fa-chevron-right breadcrumb-separator"></i>
					<a href="index.php?Application=AR" class="breadcrumb-item">' . __('receivables') . '</a>
					<i class="fas fa-chevron-right breadcrumb-separator"></i>
					<span style="color: #064e3b; opacity: 0.9;">' . __('customer search') . '</span>
				</div>
				<div>
					<h1 style="font-size: 2.5rem; font-weight: 950; letter-spacing: -2px; color: #064e3b; margin: 0; line-height: 1;">' . $Title . '</h1>
					<p style="font-size: 1.1rem; margin-top: 12px; color: #065f46; font-weight: 500; opacity: 0.8;">' . __('Identify and manage customer accounts') . '</p>
				</div>
			</div>
			<div class="db-header-actions">
				<a href="' . $RootPath . '/Customers.php" class="db-btn db-btn-primary">
					<i class="fas fa-plus"></i> ' . __('Add New Customer') . '
				</a>
			</div>
		</div>';

if (isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
}
if (!isset($_SESSION['CustomerID'])) {
	$_SESSION['CustomerID'] = '';
}
if (isset($_GET['Area'])) {
	$_POST['Area'] = $_GET['Area'];
	$_POST['Search'] = 'Search';
	$_POST['Keywords'] = '';
	$_POST['CustCode'] = '';
	$_POST['CustPhone'] = '';
	$_POST['CustAdd'] = '';
	$_POST['CustType'] = '';
}
if (!isset($_SESSION['CustomerType'])) {
	$_SESSION['CustomerType'] = '';
}
if (isset($_POST['JustSelectedACustomer'])) {
	if (isset($_POST['SubmitCustomerSelection'])) {
		foreach ($_POST['SubmitCustomerSelection'] as $CustomerID => $BranchCode)
			$_SESSION['CustomerID'] = $CustomerID;
		$_SESSION['BranchCode'] = $BranchCode;
	} elseif (!isset($_POST['Search'])) {
		prnMsg(__('Unable to identify the selected customer'), 'error');
	}
}

$Msg = '';

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}

}

if (isset($_POST['Search']) or isset($_POST['CSV']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	unset($_POST['JustSelectedACustomer']);
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}
	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno,
				custbranch.email
			FROM debtorsmaster
			LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN debtortype
				ON debtorsmaster.typeid = debtortype.typeid";
	if (isset($_POST['SmartSearch']) && mb_strlen($_POST['SmartSearch']) > 0) {
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['SmartSearch'])));
		$SQL .= " WHERE (debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
						OR debtorsmaster.debtorno " . LIKE . " '%" . $SearchKeywords . "%'
						OR custbranch.phoneno " . LIKE . " '%" . $SearchKeywords . "%'
						OR debtorsmaster.address1 " . LIKE . " '%" . $SearchKeywords . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $SearchKeywords . "%'
						OR debtorsmaster.address3 " . LIKE . " '%" . $SearchKeywords . "%'
						OR debtorsmaster.address4 " . LIKE . " '%" . $SearchKeywords . "%')";

		if (isset($_POST['CustType']) && $_POST['CustType'] != 'ALL') {
			$SQL .= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
		}
		if (isset($_POST['Area']) && $_POST['Area'] != 'ALL') {
			$SQL .= " AND custbranch.area = '" . $_POST['Area'] . "'";
		}
	} else {
		$Keywords = mb_strtoupper($_POST['Keywords'] ?? '');
		$CustCode = mb_strtoupper($_POST['CustCode'] ?? '');
		$CustPhone = $_POST['CustPhone'] ?? '';
		$CustAdd = $_POST['CustAdd'] ?? '';
		$CustType = $_POST['CustType'] ?? 'ALL';
		$Area = $_POST['Area'] ?? 'ALL';

		$SQL .= " WHERE debtorsmaster.name " . LIKE . " '%" . $Keywords . "%'
				AND debtorsmaster.debtorno " . LIKE . " '%" . $CustCode . "%'
				AND (custbranch.phoneno " . LIKE . " '%" . $CustPhone . "%' OR custbranch.phoneno IS NULL)
				AND (debtorsmaster.address1 " . LIKE . " '%" . $CustAdd . "%'
					OR debtorsmaster.address2 " . LIKE . " '%" . $CustAdd . "%'
					OR debtorsmaster.address3 " . LIKE . " '%" . $CustAdd . "%'
					OR debtorsmaster.address4 " . LIKE . " '%" . $CustAdd . "%')";

		if ($CustType != 'ALL') {
			$SQL .= " AND debtortype.typename = '" . $CustType . "'";
		}
		if ($Area != 'ALL') {
			$SQL .= " AND custbranch.area = '" . $Area . "'";
		}
	}

	if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$SQL .= " ORDER BY debtorsmaster.name";

	$SearchResult = DB_query($SQL);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No customers were identified matching the search criteria'), 'warn');
	}
}

if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '' and !isset($_POST['Search']) and !isset($_POST['CSV'])) {
	$SQL = "SELECT debtorsmaster.name,
					custbranch.brname,
					custbranch.phoneno
			FROM debtorsmaster
			INNER JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			
	if (isset($_SESSION['BranchCode']) && $_SESSION['BranchCode'] != '') {
		$SQL .= " AND custbranch.branchcode = '" . $_SESSION['BranchCode'] . "'";
	}
	$SQL .= " LIMIT 1";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CustomerName = $MyRow['name'] ?? '';
	$BranchName = $MyRow['brname'] ?? '';
	$PhoneNo = $MyRow['phoneno'] ?? '';

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="width:48px; height:48px; border-radius:12px; background-color:var(--primary-soft); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					</div>
					<div>
						<h3 style="margin:0; font-size:1.25rem; font-weight:600; color:var(--text-main);">' . $CustomerName . '</h3>
						<div style="margin-top:4px; font-size:0.875rem; color:var(--text-muted); display:flex; gap:12px; align-items:center;">
							<span>' . __('Account') . ': <strong style="color:var(--text-main);">#' . stripslashes($_SESSION['CustomerID']) . '</strong></span>
							<span style="width:4px; height:4px; border-radius:50%; background-color:var(--border);"></span>
							<span>' . $BranchName . '</span>
							<span style="width:4px; height:4px; border-radius:50%; background-color:var(--border);"></span>
							<span>' . $PhoneNo . '</span>
						</div>
					</div>
				</div>
				<div>
					<span style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; background-color:#dcfce7; color:#166534; border:1px solid #bbf7d0;">
						<span style="width:6px; height:6px; border-radius:50%; background-color:#16a34a; margin-right:6px;"></span>
						' . __('Active Selection') . '
					</span>
				</div>
			</div>

			<div style="padding:24px;">
				<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px;">
					<a href="' . $RootPath . '/SelectOrderItems.php?NewOrder=Yes&SelectedCustomer=' . urlencode($_SESSION['CustomerID']) . '" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'#bfdbfe\'; this.style.backgroundColor=\'#f0fdf4\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('New Order') . '</span>
					</a>

					<a href="' . $RootPath . '/CustomerReceipt.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '&NewReceipt=Yes&Type=Customer" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'#bbf7d0\'; this.style.backgroundColor=\'#f0fdf4\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('Receipt') . '</span>
					</a>
					<a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'#e2e8f0\'; this.style.backgroundColor=\'var(--surface-alt)\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:var(--surface-alt); color:var(--text-body); display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('Inquiry') . '</span>
					</a>
					<a href="' . $RootPath . '/Customers.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'#e2e8f0\'; this.style.backgroundColor=\'var(--surface-alt)\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:var(--surface-alt); color:var(--text-body); display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('Edit Profile') . '</span>
					</a>
					<a href="' . $RootPath . '/CustomerAccount.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'#e2e8f0\'; this.style.backgroundColor=\'var(--surface-alt)\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:var(--surface-alt); color:var(--text-body); display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('Statement') . '</span>
					</a>
					<a href="' . $RootPath . '/CounterSales.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&amp;BranchNo=' . ($_SESSION['BranchCode'] ?? '') . '" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; background-color:var(--surface); border:1px solid var(--border-soft); border-radius:8px; text-decoration:none; color:var(--text-main); transition:all 0.2s;" onmouseover="this.style.borderColor=\'var(--border)\'; this.style.backgroundColor=\'#fef2f2\';" onmouseout="this.style.borderColor=\'var(--border-soft)\'; this.style.backgroundColor=\'var(--surface)\';">
						<div style="width:40px; height:40px; border-radius:8px; background-color:var(--danger-bg); color:var(--danger); display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect></svg>
						</div>
						<span style="font-size:0.875rem; font-weight:500;">' . __('POS') . '</span>
					</a>
				</div>
			</div>
		</div>';
}

// Search Card
echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
		<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:16px 24px;">
			<h3 style="margin:0; font-size:1.1rem; font-weight:600; color:var(--text-main); display:flex; align-items:center;">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px; color:var(--primary);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				' . __('Find Customer') . '
			</h3>
		</div>
		<div style="padding:24px;">
			<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />

				<div style="display:flex; gap:16px; margin-bottom:24px;">
					<div style="flex:1; position:relative;">
						<input type="text" name="SmartSearch" style="width:100%; height:48px; padding-left:16px; font-size:1rem; border:1px solid var(--border); border-radius:8px; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" placeholder="' . __('Search by Name, Code, Phone, or Address...') . '" ' . (isset($_POST['SmartSearch']) ? 'value="' . $_POST['SmartSearch'] . '"' : '') . ' autofocus />
					</div>
					<button type="submit" name="Search" class="db-btn db-btn-primary" style="height:48px; min-width:140px; border-radius:8px; font-size:1rem;">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
						' . __('Search Now') . '
					</button>
				</div>

				<details style="margin-top:16px; background-color:var(--surface-alt); border:1px solid var(--border-soft); border-radius:8px; padding:16px;">
					<summary style="font-size:0.875rem; font-weight:500; color:var(--text-body); cursor:pointer; list-style:none; display:flex; align-items:center;">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;"><path d="M4 21v-7"></path><path d="M4 10V3"></path><path d="M12 21v-9"></path><path d="M12 8V3"></path><path d="M20 21v-5"></path><path d="M20 12V3"></path><path d="M1 14h6"></path><path d="M9 8h6"></path><path d="M17 16h6"></path></svg>
						' . __('Advanced Filtering Options') . '
					</summary>
					<div class="architect-grid architect-grid-4" style="margin-top:20px; gap:16px;">
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Description Keywords') . '</label>
							<input type="text" name="Keywords" value="' . (isset($_POST['Keywords']) ? $_POST['Keywords'] : '') . '" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" />
						</div>
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Customer Code') . '</label>
							<input type="text" name="CustCode" value="' . (isset($_POST['CustCode']) ? $_POST['CustCode'] : '') . '" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" />
						</div>
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Phone Number') . '</label>
							<input type="text" name="CustPhone" value="' . (isset($_POST['CustPhone']) ? $_POST['CustPhone'] : '') . '" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" />
						</div>
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Address Extract') . '</label>
							<input type="text" name="CustAdd" value="' . (isset($_POST['CustAdd']) ? $_POST['CustAdd'] : '') . '" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" />
						</div>
					</div>
					<div class="architect-grid architect-grid-2" style="margin-top:16px; gap:16px;">
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Customer Type') . '</label>';
$Result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
echo '						<select name="CustType" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none; background-color:var(--surface);" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';">
								<option value="ALL">' . __('Any Type') . '</option>';
while ($MyRow = DB_fetch_array($Result2)) {
	$selected = (isset($_POST['CustType']) AND $_POST['CustType'] == $MyRow['typename']) ? 'selected="selected"' : '';
	echo '					<option ' . $selected . ' value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
}
echo '						</select>
						</div>
						<div style="display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Sales Area') . '</label>';
$Result2 = DB_query("SELECT areacode, areadescription FROM areas");
echo '						<select name="Area" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none; background-color:var(--surface);" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';">
								<option value="ALL">' . __('Any Area') . '</option>';
while ($MyRow = DB_fetch_array($Result2)) {
	$selected = (isset($_POST['Area']) AND $_POST['Area'] == $MyRow['areacode']) ? 'selected="selected"' : '';
	echo '					<option ' . $selected . ' value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
}
echo '						</select>
						</div>
					</div>
				</details>
			</form>
		</div>
	</div>';

if (isset($SearchResult)) {
	$ListCount = DB_num_rows($SearchResult);
	$DisplayRecordsMax = 8;
	$ListPageMax = ceil($ListCount / $DisplayRecordsMax);

	if (!isset($_POST['CSV']) && $ListCount > 0) {
		if (isset($_POST['Next']) && $_POST['PageOffset'] < $ListPageMax)
			$_POST['PageOffset']++;
		if (isset($_POST['Previous']) && $_POST['PageOffset'] > 1)
			$_POST['PageOffset']--;

		echo '<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />
				<input type="hidden" name="SmartSearch" value="' . (isset($_POST['SmartSearch']) ? htmlspecialchars($_POST['SmartSearch'], ENT_QUOTES, 'UTF-8') : '') . '" />
				<input type="hidden" name="Keywords" value="' . (isset($_POST['Keywords']) ? htmlspecialchars($_POST['Keywords'], ENT_QUOTES, 'UTF-8') : '') . '" />
				<input type="hidden" name="CustCode" value="' . (isset($_POST['CustCode']) ? htmlspecialchars($_POST['CustCode'], ENT_QUOTES, 'UTF-8') : '') . '" />
				<input type="hidden" name="CustPhone" value="' . (isset($_POST['CustPhone']) ? htmlspecialchars($_POST['CustPhone'], ENT_QUOTES, 'UTF-8') : '') . '" />
				<input type="hidden" name="CustAdd" value="' . (isset($_POST['CustAdd']) ? htmlspecialchars($_POST['CustAdd'], ENT_QUOTES, 'UTF-8') : '') . '" />
				<input type="hidden" name="CustType" value="' . (isset($_POST['CustType']) ? htmlspecialchars($_POST['CustType'], ENT_QUOTES, 'UTF-8') : 'ALL') . '" />
				<input type="hidden" name="Area" value="' . (isset($_POST['Area']) ? htmlspecialchars($_POST['Area'], ENT_QUOTES, 'UTF-8') : 'ALL') . '" />';



		$columns = [__('Customer'), __('Code'), __('Phone'), __('Branch'), __('Type'), __('Action')];
		$dataRows = [];
		DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $DisplayRecordsMax);
		$RowIndex = 0;
		while (($MyRow = DB_fetch_array($SearchResult)) and ($RowIndex <> $DisplayRecordsMax)) {
			$actionHtml = '
				<div style="display: flex; gap: 8px;">
					<button type="submit" name="SubmitCustomerSelection[' . htmlspecialchars($MyRow['debtorno'], ENT_QUOTES, 'UTF-8') . ']" value="' . htmlspecialchars($MyRow['branchcode'], ENT_QUOTES, 'UTF-8') . '" class="db-btn db-btn-primary db-btn-small">
						' . __('Select') . '
					</button>
					<a href="SelectOrderItems.php?NewOrder=Yes&SelectedCustomer=' . urlencode($MyRow['debtorno']) . '" class="db-btn db-btn-outline db-btn-small">
						' . __('Order') . '
					</a>
					<a href="CustomerInquiry.php?CustomerID=' . urlencode($MyRow['debtorno']) . '" class="db-btn db-btn-outline db-btn-small">
						' . __('Inquiry') . '
					</a>
				</div>';

			$dataRows[] = [
				'<span style="font-weight: 700;">' . htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8') . '</span>',
				'<span class="db-ref">#' . $MyRow['debtorno'] . '</span>',
				$MyRow['phoneno'],
				htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8'),
				'<span class="db-badge db-badge-info">' . $MyRow['typename'] . '</span>',
				$actionHtml
			];
			$RowIndex++;
		}
		echo '<div style="margin-top: var(--space-4);">';
		render_modern_table($columns, $dataRows);
		echo '</div>';
		
		if ($ListPageMax > 1) {
			render_modern_pagination_form($ListCount, $_POST['PageOffset'], $DisplayRecordsMax);
		}

		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />
			  </form>';
	}
}

// Geocode Mapping
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '' && $_SESSION['geocode_integration'] == 1) {
	$SQL = "SELECT * FROM geocode_param";
	$ResMap = DB_query($SQL);
	if (DB_num_rows($ResMap) > 0) {
		$MapRow = DB_fetch_array($ResMap);
		$SQL = "SELECT lat, lng, brname, braddress1, braddress2, braddress3, braddress4 
				FROM custbranch 
				WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
		if (isset($_SESSION['BranchCode']) && $_SESSION['BranchCode'] != '') {
			$SQL .= " AND branchcode = '" . $_SESSION['BranchCode'] . "'";
		}
		$SQL .= " LIMIT 1";
		$ResBranch = DB_query($SQL);
		$BRow = DB_fetch_array($ResBranch);

		if ($BRow && $BRow['lat'] != 0) {
			echo '<div class="card-v2" style="margin-top: var(--space-6);">
					<div class="card-header-v2">
						<h3>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle; margin-right:8px; color:var(--primary);"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							' . __('Visual Location') . '
						</h3>
					</div>
					<div class="db-card-body">
						<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
						<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
						<div id="map" style="height:' . $MapRow['map_height'] . 'px; width: 100%; border-radius: var(--radius-md);"></div>
						<script>
							var map = L.map(\'map\').setView([' . $BRow['lat'] . ', ' . $BRow['lng'] . '], 14);
							L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\').addTo(map);
							L.marker([' . $BRow['lat'] . ', ' . $BRow['lng'] . ']).addTo(map).bindPopup(\'<b>' . htmlspecialchars($BRow['brname'], ENT_QUOTES, 'UTF-8') . '</b>\').openPopup();
						</script>
					</div>
				</div>';
		}
	}
}

// Extended Customer Info
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '' && $_SESSION['Extended_CustomerInfo'] == 1) {
	$SQL = "SELECT clientsince, lastpaid, lastpaiddate, currencies.decimalplaces, currencies.currency
			FROM debtorsmaster INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
	$DataRes = DB_query($SQL);
	$MyRow = DB_fetch_array($DataRes);

	$SQL = "SELECT SUM(ovamount+ovgst) as total FROM debtortrans WHERE debtorno = '" . $_SESSION['CustomerID'] . "' AND type != 12";
	$TotalRes = DB_query($SQL);
	$TRow = DB_fetch_array($TotalRes);

	echo '<div class="architect-grid architect-grid-2" style="margin-top: var(--space-6);">
			<div class="card-v2">
				<div class="card-header-v2">
					<h3>' . __('Account Insights') . '</h3>
				</div>
				<div class="db-card-body">
					<div class="db-info-list">';
	if ($MyRow['lastpaiddate'] != 0) {
		echo '<div class="db-info-item"><span>' . __('Last Payment') . ':</span> <b>' . ConvertSQLDate($MyRow['lastpaiddate']) . '</b></div>';
		echo '<div class="db-info-item"><span>' . __('Amount') . ':</span> <b>' . $MyRow['currency'] . ' ' . locale_number_format($MyRow['lastpaid'], $MyRow['decimalplaces']) . '</b></div>';
	}
	echo '<div class="db-info-item"><span>' . __('Member Since') . ':</span> <b>' . ConvertSQLDate($MyRow['clientsince']) . '</b></div>';
	echo '<div class="db-info-item"><span>' . __('Lifetime Value') . ':</span> <b style="color: var(--success);">' . $MyRow['currency'] . ' ' . locale_number_format($TRow['total'], $MyRow['decimalplaces']) . '</b></div>';
	echo '</div></div></div>';

	// Customer Contacts Card
	$SQL = "SELECT * FROM custcontacts WHERE debtorno='" . $_SESSION['CustomerID'] . "' ORDER BY contid";
	$ConRes = DB_query($SQL);
	echo '<div class="card-v2">
			<div class="card-header-v2">
				<h3>' . __('Key Contacts') . '</h3>
				<a href="' . $RootPath . '/AddCustomerContacts.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '" class="db-btn db-btn-outline db-btn-small">' . __('Manage') . '</a>
			</div>
			<div class="db-card-body">';
	if (DB_num_rows($ConRes) > 0) {
		$columns = [__('Name'), __('Role'), __('Email')];
		$dataRows = [];
		while ($CR = DB_fetch_array($ConRes)) {
			$dataRows[] = [
				htmlspecialchars($CR[2], ENT_QUOTES, 'UTF-8'),
				htmlspecialchars($CR[3], ENT_QUOTES, 'UTF-8'),
				'<a href="mailto:' . htmlspecialchars($CR[6], ENT_QUOTES, 'UTF-8') . '" class="db-link">' . htmlspecialchars($CR[6], ENT_QUOTES, 'UTF-8') . '</a>'
			];
		}
		render_modern_table($columns, $dataRows);
	} else {
		echo '<p class="db-muted" style="text-align:center;">' . __('No contacts listed') . '</p>';
	}
	echo '</div></div></div>';

	// Notes Section
	$SQL = "SELECT * FROM custnotes WHERE debtorno='" . $_SESSION['CustomerID'] . "' ORDER BY date DESC LIMIT 5";
	$NoteRes = DB_query($SQL);
	echo '<div class="card-v2" style="margin-top: var(--space-6);">
			<div class="card-header-v2">
				<h3>' . __('Customer Notes') . '</h3>
				<a href="' . $RootPath . '/AddCustomerNotes.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '" class="db-btn db-btn-outline db-btn-small">' . __('Add Note') . '</a>
			</div>
			<div class="db-card-body">';
	if (DB_num_rows($NoteRes) > 0) {
		$columns = [__('Date'), __('Priority'), __('Note')];
		$dataRows = [];
		while ($NR = DB_fetch_array($NoteRes)) {
			$dataRows[] = [
				ConvertSQLDate($NR['date']),
				htmlspecialchars($NR['priority'], ENT_QUOTES, 'UTF-8'),
				htmlspecialchars($NR['note'], ENT_QUOTES, 'UTF-8')
			];
		}
		render_modern_table($columns, $dataRows);
	} else {
		echo '<p class="db-muted" style="text-align:center;">' . __('No recent notes') . '</p>';
	}
	echo '</div></div>';
}

echo '</div>'; // Close .db-page
include(__DIR__ . '/includes/footer.php');
?>