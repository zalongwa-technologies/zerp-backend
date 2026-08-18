<?php

/* Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes. */

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Inquiry');// Screen identification.
$ViewTopic = 'ARInquiries';// Filename's id in ManualContents.php's TOC.
$BookMark = 'CustomerInquiry';// Anchor's id in the manual's html document.

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
include_once(__DIR__ . '/includes/UIComponents.php');

echo '<div class="db-page">';

if (isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = ConvertSQLDate($_POST['TransAfterDate']);
}

if (isset($_GET['FromDate'])) {
	if (Is_Date($_GET['FromDate'])) {
		$_POST['TransAfterDate'] = $_GET['FromDate'];
	} elseif (EnsureSQLDateFormat($_GET['FromDate'])) {
		$_POST['TransAfterDate'] = ConvertSQLDate($_GET['FromDate']);
	}
}

// always figure out the SQL required from the inputs available

if (!isset($_GET['CustomerID']) and !isset($_SESSION['CustomerID'])) {
	prnMsg(__('To display the enquiry a customer must first be selected from the customer selection screen'), 'info');
	echo '<br /><div class="centre"><a href="', $RootPath, '/SelectCustomer.php">', __('Select a Customer to Inquire On'), '</a></div>';
	include(__DIR__ . '/includes/footer.php');
	exit();
} else {
	if (isset($_GET['CustomerID'])) {
		$_SESSION['CustomerID'] = stripslashes($_GET['CustomerID']);
	}
	$CustomerID = $_SESSION['CustomerID'];
}
//Check if the users have proper authority
if ($_SESSION['SalesmanLogin'] !=  '') {
	$ViewAllowed = false;
	$SQL = "SELECT salesman FROM custbranch WHERE debtorno = '" . $CustomerID . "'";
	$ErrMsg = __('Failed to retrieve sales data');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result)>0) {
		while($MyRow = DB_fetch_array($Result)) {
			if ($_SESSION['SalesmanLogin'] == $MyRow['salesman']){
				$ViewAllowed = true;
			}
		}
	} else {
		prnMsg(__('There is no salesman data set for this debtor'),'error');
		include(__DIR__ . '/includes/footer.php');
		exit();
	}
	if (!$ViewAllowed){
		prnMsg(__('You have no authority to review this data'),'error');
		include(__DIR__ . '/includes/footer.php');
		exit();
	}
}


if (isset($_GET['Status'])) {
	if (is_numeric($_GET['Status'])) {
		$_POST['Status'] = $_GET['Status'];
	}
} elseif (isset($_POST['Status'])) {
	if ($_POST['Status'] == '' or $_POST['Status'] == 1 or $_POST['Status'] == 0) {
		$Status = $_POST['Status'];
	} else {
		prnMsg(__('The balance status should be all or zero balance or not zero balance'), 'error');
		include(__DIR__ . '/includes/footer.php');
		exit();
	}
} else {
	$_POST['Status'] = '';
}

if (!isset($_POST['TransAfterDate']) OR !Is_Date($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') - $_SESSION['NumberOfMonthMustBeShown'], date('d'), date('Y')));
}

$SQL = "SELECT debtorsmaster.name,
		currencies.currency,
		currencies.decimalplaces,
		paymentterms.terms,
		debtorsmaster.creditlimit,
		holdreasons.dissallowinvoices,
		holdreasons.reasondescription,
		SUM(debtortrans.balance) AS balance,
		SUM(CASE WHEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) > 0 THEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) ELSE 0 END) AS total_invoices,
		SUM(CASE WHEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) < 0 THEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) ELSE 0 END) AS total_receipts,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
			THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN debtortrans.balance ELSE 0 END
		END) AS due,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN debtortrans.balance ELSE 0 END
		END) AS overdue2
		FROM debtorsmaster,
	 			paymentterms,
	 			holdreasons,
	 			currencies,
	 			debtortrans
		WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
	 		AND debtorsmaster.currcode = currencies.currabrev
	 		AND debtorsmaster.holdreason = holdreasons.reasoncode
	 		AND debtorsmaster.debtorno = '" . $CustomerID . "'
	 		AND debtorsmaster.debtorno = debtortrans.debtorno
			GROUP BY debtorsmaster.name,
			currencies.currency,
			currencies.decimalplaces,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";
$ErrMsg = __('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($CustomerResult) == 0) {

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = true;

	$SQL = "SELECT debtorsmaster.name,
					debtorsmaster.currcode,
					currencies.currency,
					currencies.decimalplaces,
					paymentterms.terms,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription
			FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
			WHERE debtorsmaster.debtorno = '" . $CustomerID . "'";
	$ErrMsg = __('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL, $ErrMsg);

} else {
	$NIL_BALANCE = false;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE == true) {
	$CustomerRecord['balance'] = 0;
	$CustomerRecord['total_invoices'] = 0;
	$CustomerRecord['total_receipts'] = 0;
	$CustomerRecord['due'] = 0;
	$CustomerRecord['overdue1'] = 0;
	$CustomerRecord['overdue2'] = 0;
}

	echo '<div class="db-page-header">
			<div>
				<h2 class="db-page-title">' . $Title . '</h2>
				<p class="db-page-subtitle">' . __('View detailed transaction history and account balance') . '</p>
			</div>
			<div class="db-header-actions">
				<a href="' . $RootPath . '/SelectCustomer.php" class="db-btn db-btn-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:8px;"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
					' . __('Select Customer') . '
				</a>
			</div>
		</div>';

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<div style="display:flex; align-items:center; gap:16px;">
					<div style="width:48px; height:48px; border-radius:12px; background-color:var(--primary-soft); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					</div>
					<div>
						<h3 style="margin:0; font-size:1.25rem; font-weight:600; color:var(--text-main);">' . $CustomerRecord['name'] . '</h3>
						<div style="margin-top:4px; font-size:0.875rem; color:var(--text-muted); display:flex; gap:12px; align-items:center;">
							<span>' . __('Account') . ': <strong style="color:var(--text-main);">#' . stripslashes($CustomerID) . '</strong></span>
						</div>
					</div>
				</div>
				<div style="display:flex; gap:8px;">';
	if ($CustomerRecord['dissallowinvoices'] != 0) {
		echo '		<span style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; background-color:var(--danger-bg); color:var(--danger); border:1px solid var(--border);">' . __('ACCOUNT ON HOLD') . '</span>';
	}
	echo '			<span style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; background-color:var(--info-bg); color:var(--info); border:1px solid var(--info-bg);">' . $CustomerRecord['currency'] . '</span>
				</div>
			</div>
			<div style="padding:24px;">
				<div class="db-grid db-grid-4" style="margin-bottom:24px;">
					<div style="display:flex; flex-direction:column; gap:4px;">
						<label style="font-size:0.875rem; font-weight:500; color:var(--text-muted);">' . __('Payment Terms') . '</label>
						<div style="font-size:1rem; color:var(--text-main); font-weight:500;">' . $CustomerRecord['terms'] . '</div>
					</div>
					<div style="display:flex; flex-direction:column; gap:4px;">
						<label style="font-size:0.875rem; font-weight:500; color:var(--text-muted);">' . __('Credit Limit') . '</label>
						<div style="font-size:1rem; color:var(--text-main); font-weight:500;">' . locale_number_format($CustomerRecord['creditlimit'], 0) . '</div>
					</div>
					<div style="display:flex; flex-direction:column; gap:4px;">
						<label style="font-size:0.875rem; font-weight:500; color:var(--text-muted);">' . __('Credit Status') . '</label>
						<div style="font-size:1rem; color:var(--text-main); font-weight:500;">' . $CustomerRecord['reasondescription'] . '</div>
					</div>
					<div style="display:flex; flex-direction:column; gap:4px;">
						<label style="font-size:0.875rem; font-weight:500; color:var(--text-muted);">' . __('Total Balance') . '</label>
						<div style="font-size:1.125rem; font-weight:700; color:var(--primary);">' . locale_number_format($CustomerRecord['balance'], $CustomerRecord['decimalplaces']) . '</div>
					</div>
				</div>';

	$balCols = [
		__('Total Invoices'), __('Total Receipts'), __('Current'), __('Now Due'),
		$_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' Days',
		'> ' . $_SESSION['PastDueDays2'] . ' Days'
	];
	$balRows = [
		[
			locale_number_format($CustomerRecord['total_invoices'] ?? 0, $CustomerRecord['decimalplaces']),
			locale_number_format(abs($CustomerRecord['total_receipts'] ?? 0), $CustomerRecord['decimalplaces']),
			locale_number_format(($CustomerRecord['balance'] - $CustomerRecord['due']), $CustomerRecord['decimalplaces']),
			locale_number_format(($CustomerRecord['due'] - $CustomerRecord['overdue1']), $CustomerRecord['decimalplaces']),
			locale_number_format(($CustomerRecord['overdue1'] - $CustomerRecord['overdue2']), $CustomerRecord['decimalplaces']),
			'<span style="color:var(--danger);font-weight:600;">' . locale_number_format($CustomerRecord['overdue2'], $CustomerRecord['decimalplaces']) . '</span>'
		]
	];
	render_modern_table($balCols, $balRows, false);

	echo '		</div>
		</div>';

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:16px 24px;">
				<h3 style="margin:0; font-size:1.1rem; font-weight:600; color:var(--text-main); display:flex; align-items:center;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px; color:var(--primary);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
					' . __('Inquiry Filters') . '
				</h3>
			</div>
			<div style="padding:24px;">
				<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">
					<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
					<div style="display:flex; gap:16px; align-items:flex-end;">
						<div style="flex:1; display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Transactions After') . '</label>
							<input required="required" type="date" name="TransAfterDate" value="' . FormatDateForSQL($_POST['TransAfterDate']) . '" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none;" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';" />
						</div>
						<div style="flex:1; display:flex; flex-direction:column; gap:8px;">
							<label style="font-size:0.875rem; font-weight:500; color:var(--text-main);">' . __('Balance Status') . '</label>
							<select name="Status" style="padding:10px 12px; border:1px solid var(--border); border-radius:6px; outline:none; background-color:var(--surface);" onfocus="this.style.borderColor=\'var(--primary)\';" onblur="this.style.borderColor=\'var(--border)\';">
								<option ' . ($_POST['Status'] == '' ? 'selected="selected"' : '') . ' value="">' . __('All') . '</option>
								<option ' . ($_POST['Status'] == '1' ? 'selected="selected"' : '') . ' value="1">' . __('Invoices not fully allocated') . '</option>
								<option ' . ($_POST['Status'] == '0' ? 'selected="selected"' : '') . ' value="0">' . __('Invoices fully allocated') . '</option>
							</select>
						</div>
						<div style="flex:1; display:flex; align-items:flex-end;">
							<button type="submit" name="Refresh Inquiry" class="db-btn db-btn-primary" style="height:44px; width:100%; border-radius:6px; font-size:1rem;">' . __('Refresh Inquiry') . '</button>
						</div>
					</div>
				</form>
			</div>
		</div>';

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

// Calculate opening balance - sum of all transactions settled before the TransAfterDate
$SQL = "SELECT SUM(ovamount + ovgst + ovfreight + ovdiscount) AS open_balance 
		FROM debtortrans 
		WHERE debtorno = '" . $CustomerID . "' 
		AND trandate < '" . $DateAfterCriteria . "'
		AND settled = 1
		AND id NOT IN (
			SELECT DISTINCT transid_allocfrom FROM custallocns WHERE datealloc >= '" . $DateAfterCriteria . "'
			UNION
			SELECT DISTINCT transid_allocto FROM custallocns WHERE datealloc >= '" . $DateAfterCriteria . "'
		)";
$OpenBalResult = DB_query($SQL, $ErrMsg);
$OpenBalRow = DB_fetch_array($OpenBalResult);
$RunningBalance = $OpenBalRow['open_balance'] ?? 0;

$SQL = "SELECT DISTINCT systypes.typename,
				debtortrans.id,
				debtortrans.type,
				debtortrans.transno,
				debtortrans.branchcode,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				salesorders.customerref,
				debtortrans.rate,
				(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) AS totalamount,
				debtortrans.alloc AS allocated,
				debtortrans.settled
			FROM debtortrans
			INNER JOIN systypes
				ON debtortrans.type = systypes.typeid
			LEFT JOIN salesorders
				ON salesorders.orderno=debtortrans.order_
			LEFT JOIN custallocns
				ON (debtortrans.id=custallocns.transid_allocfrom OR debtortrans.id=custallocns.transid_allocto)
			WHERE debtortrans.debtorno = '" . $CustomerID . "'
				AND (debtortrans.trandate >= '" . $DateAfterCriteria . "' 
					 OR debtortrans.settled = 0
					 OR (debtortrans.settled = 1 AND custallocns.datealloc >= '" . $DateAfterCriteria . "'))";

if ($_POST['Status'] == '1') {
	$SQL .= " AND debtortrans.settled = 0";
} elseif ($_POST['Status'] == '0') {
	$SQL .= " AND debtortrans.settled = 1";
}

$SQL .= " ORDER BY debtortrans.trandate,
					debtortrans.id";
$ErrMsg = __('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL, $ErrMsg);

/* Show a table of the invoices returned by the SQL. */

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:16px 24px;">
				<h3 style="margin:0; font-size:1.1rem; font-weight:600; color:var(--text-main); display:flex; align-items:center;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px; color:var(--primary);"><path d="M2 17V3a2 2 0 0 1 2-2h13.2a2 2 0 0 1 2 2v14m-12 4h12l1-4H3l1 4Z"></path></svg>
					' . __('Transaction History') . '
				</h3>
			</div>
			<div style="padding:0;">
				<div style="width:100%; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
						<thead>
							<tr style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft);">
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('Type') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('No') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('Date') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('Branch') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('Reference') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">' . __('Comments') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Charges') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Credits') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Allocated') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Outstanding') . '</th>
								<th style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Running Balance') . '</th>
								<th class="noPrint" style="padding:12px 24px; font-size:0.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:right;">' . __('Actions') . '</th>
							</tr>
						</thead>
						<tbody>';
	
	if (DB_num_rows($TransResult) == 0) {
		echo '<tr><td colspan="12" style="padding:24px; text-align:center; color:var(--text-muted); font-size:0.875rem;">' . __('No transactions found for the selected period.') . '</td></tr>';
	} else {

		echo '<tr style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); font-weight:600; font-size:0.875rem; color:var(--text-main);">
				<td colspan="6" style="padding:12px 24px;">' . __('Opening Balance') . '</td>
				<td style="padding:12px 24px;">&nbsp;</td>
				<td style="padding:12px 24px;">&nbsp;</td>
				<td style="padding:12px 24px;">&nbsp;</td>
				<td style="padding:12px 24px;">&nbsp;</td>
				<td style="padding:12px 24px; text-align:right; font-weight:700; color:var(--text-main);">' . locale_number_format($RunningBalance, $CustomerRecord['decimalplaces']) . '</td>
				<td class="noPrint" style="padding:12px 24px;">&nbsp;</td>
			</tr>';

	while ($MyRow = DB_fetch_array($TransResult)) {

		$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

		if ($_SESSION['InvoicePortraitFormat'] == 1) { //Invoice/credits in portrait
			$Orientation = 'portrait';
		} else { //produce pdfs in landscape
			$Orientation = 'landscape';
		}

		// Define badge classes based on transaction type
		$BadgeClass = 'db-badge-secondary';
		if ($MyRow['type'] == 10) $BadgeClass = 'db-badge-success'; // Invoice
		if ($MyRow['type'] == 11) $BadgeClass = 'db-badge-danger';  // Credit Note
		if ($MyRow['type'] == 12) $BadgeClass = 'db-badge-info';    // Receipt

		$Actions = '';

		/* if the user is allowed to create credits for invoices */
		if (in_array($_SESSION['PageSecurityArray']['Credit_Invoice.php'], $_SESSION['AllowedPageSecurityTokens']) and $MyRow['type'] == 10) {
			$Actions .= '<a href="' . $RootPath . '/Credit_Invoice.php?InvoiceNumber=' . $MyRow['transno'] . '" title="' . __('Credit') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"></path></svg>
						</a>';
		}

		$TransTypeStr = 'Invoice';
		if ($MyRow['type'] == 11) $TransTypeStr = 'Credit';
		if ($MyRow['type'] == 12) $TransTypeStr = 'Receipt';

		// Standard View (HTML) Action
		if ($MyRow['type'] == 12) { // Receipt
			$Actions .= '<a href="' . $RootPath . '/TRAReceipt.php?BatchNumber=' . $MyRow['transno'] . '" title="' . __('View Receipt') . '" target="_blank" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						</a>';
		} else {
			$Actions .= '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=' . $TransTypeStr . '&amp;DebtorNo=' . $CustomerID . '&View=Yes" title="' . __('View Dashboard') . '" target="_blank" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						</a>';
		}

		// PDF / Download Action
		if ($MyRow['type'] == 12) { // Receipt
			$Actions .= '<a href="' . $RootPath . '/TRAReceipt.php?BatchNumber=' . $MyRow['transno'] . '&amp;Download=True" title="' . __('Download PDF') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
						</a>';
		} else {
			$Actions .= '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=' . $TransTypeStr . '&amp;DebtorNo=' . $CustomerID . '&amp;PrintPDF=True&amp;Download=True&amp;orientation=' . $Orientation . '" title="' . __('Download PDF') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
						</a>';
		}

		// Email Action
		$Actions .= '<a href="' . $RootPath . '/EmailCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=' . $TransTypeStr . '&amp;DebtorNo=' . $CustomerID . '" title="' . __('Email') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
					</a>';

		// GL Action - if allowed
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			$Actions .= '<a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&amp;TransNo=' . $MyRow['transno'] . '" title="' . __('GL') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
						</a>';
		}

		// Allocation Action for receipts and credits
		if (($MyRow['type'] == 12 or $MyRow['type'] == 11) and $MyRow['totalamount'] < 0) {
			$Actions .= '<a href="' . $RootPath . '/CustomerAllocations.php?AllocTrans=' . $MyRow['id'] . '" title="' . __('Allocation') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20"></path></svg>
						</a>';
		}
		
		$RunningBalance += $MyRow['totalamount'];

		echo '<tr style="border-bottom:1px solid var(--border-soft); transition:background-color 0.2s; cursor:default;" onmouseover="this.style.backgroundColor=\'var(--surface-alt)\'" onmouseout="this.style.backgroundColor=\'transparent\'">
				<td style="padding:16px 24px;"><span class="db-badge ' . $BadgeClass . '">' . __($MyRow['typename']) . '</span></td>
				<td style="padding:16px 24px; font-weight:500;"><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '" target="_blank" style="color:var(--primary); text-decoration:none;">' . $MyRow['transno'] . '</a></td>
				<td style="padding:16px 24px;">' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td style="padding:16px 24px;">' . $MyRow['branchcode'] . '</td>
				<td style="padding:16px 24px;">' . $MyRow['reference'] . '</td>
				<td style="padding:16px 24px; min-width:200px; white-space:nowrap;">' . $MyRow['invtext'] . '</td>
				<td style="padding:16px 24px; text-align:right;">' . ($MyRow['totalamount'] > 0 ? locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']) : '&nbsp;') . '</td>
				<td style="padding:16px 24px; text-align:right;">' . ($MyRow['totalamount'] < 0 ? locale_number_format(abs($MyRow['totalamount']), $CustomerRecord['decimalplaces']) : '&nbsp;') . '</td>
				<td style="padding:16px 24px; text-align:right;">' . locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']) . '</td>
				<td style="padding:16px 24px; text-align:right;">' . locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']) . '</td>
				<td style="padding:16px 24px; text-align:right; font-weight:600; color:var(--text-main);">' . locale_number_format($RunningBalance, $CustomerRecord['decimalplaces']) . '</td>
				<td class="noPrint" style="padding:16px 24px;">
					<div style="display:flex; gap:8px; justify-content:flex-end;">
						' . $Actions . '
					</div>
				</td>
			</tr>';

	} //end of while loop
} // end of else

	echo '</tbody></table></div></div></div></div>'; // Close table, table-wrapper, card-body, card, db-page
	include(__DIR__ . '/includes/footer.php');
