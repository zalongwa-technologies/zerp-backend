<?php
/* Shows customer account/statement on screen rather than PDF. */

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Account');// Screen identification.
$ViewTopic = 'ARInquiries';// Filename in ManualContents.php's TOC.
$BookMark = 'CustomerAccount';// Anchor's id in the manual's html document.

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

if (isset($_POST['TransAfterDate'])) {$_POST['TransAfterDate'] = ConvertSQLDate($_POST['TransAfterDate']);}

// always figure out the SQL required from the inputs available

if (!isset($_GET['CustomerID']) and !isset($_SESSION['CustomerID'])) {
	prnMsg(__('To display the account a customer must first be selected from the customer selection screen'), 'info');
	echo '<br /><div class="centre"><a href="', $RootPath, '/SelectCustomer.php">', __('Select a Customer Account to Display'), '</a></div>';
	include(__DIR__ . '/includes/footer.php');
	exit();
} else {
	if (isset($_GET['CustomerID'])) {
		$_SESSION['CustomerID'] = stripslashes($_GET['CustomerID']);
	}
	$CustomerID = $_SESSION['CustomerID'];
}
//Check if the users have proper authority
if ($_SESSION['SalesmanLogin'] != '') {
	$ViewAllowed = false;
	$SQL = "SELECT salesman FROM custbranch WHERE debtorno = '" . $CustomerID . "'";
	$ErrMsg = __('Failed to retrieve sales data');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result)>0) {
		while($MyRow = DB_fetch_array($Result)) {
			if ($_SESSION['SalesmanLogin'] == $MyRow['salesman']) {
				$ViewAllowed = true;
			}
		}
	} else {
		prnMsg(__('There is no salesman data set for this customer'),'error');
		include(__DIR__ . '/includes/footer.php');
		exit();
	}
	if (!$ViewAllowed) {
		prnMsg(__('You have no authority to review this customer account'),'error');
		include(__DIR__ . '/includes/footer.php');
		exit();
	}
}


if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') - $_SESSION['NumberOfMonthMustBeShown'], date('d'), date('Y')));
}

$Transactions = array();

/*now get all the settled transactions which were allocated this month */
$ErrMsg = __('There was a problem retrieving the transactions that were settled over the course of the last month for'). ' ' . $CustomerID . ' ' . __('from the database');
if ($_SESSION['Show_Settled_LastMonth']==1) {
	$SQL = "SELECT DISTINCT debtortrans.id,
						debtortrans.type,
						systypes.typename,
						debtortrans.branchcode,
						debtortrans.reference,
						debtortrans.invtext,
						debtortrans.order_,
						debtortrans.transno,
						debtortrans.trandate,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst AS totalamount,
						debtortrans.alloc,
						debtortrans.balance AS balance,
						debtortrans.settled
				FROM debtortrans INNER JOIN systypes
					ON debtortrans.type=systypes.typeid
				INNER JOIN custallocns
					ON (debtortrans.id=custallocns.transid_allocfrom
						OR debtortrans.id=custallocns.transid_allocto)
				WHERE custallocns.datealloc >='" . FormatDateForSQL($_POST['TransAfterDate']) . "'
				AND debtortrans.debtorno='" . $CustomerID . "'
				AND debtortrans.settled=1
				ORDER BY debtortrans.id";
	$SetldTrans=DB_query($SQL, $ErrMsg);
	$NumberOfRecordsReturned = DB_num_rows($SetldTrans);
	while ($MyRow=DB_fetch_array($SetldTrans)) {
		$Transactions[] =  $MyRow;
	}
} else {
	$NumberOfRecordsReturned=0;
}

/*now get all the outstanding transaction ie Settled=0 */
$ErrMsg =  __('There was a problem retrieving the outstanding transactions for') . ' ' .	$CustomerID . ' '. __('from the database') . '.';
$SQL = "SELECT debtortrans.id,
			debtortrans.type,
			systypes.typename,
			debtortrans.branchcode,
			debtortrans.reference,
			debtortrans.invtext,
			debtortrans.order_,
			debtortrans.transno,
			debtortrans.trandate,
			debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst as totalamount,
			debtortrans.alloc,
			debtortrans.balance as balance,
			debtortrans.settled
		FROM debtortrans INNER JOIN systypes
			ON debtortrans.type=systypes.typeid
		WHERE debtortrans.debtorno='" . $CustomerID . "'
		AND debtortrans.settled=0";
if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " ORDER BY debtortrans.id";

$OstdgTrans=DB_query($SQL, $ErrMsg);
while ($MyRow=DB_fetch_array($OstdgTrans)) {
	$Transactions[] =  $MyRow;
}

$NumberOfRecordsReturned += DB_num_rows($OstdgTrans);

$SQL = "SELECT debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			currencies.currency,
			currencies.decimalplaces,
			paymentterms.terms,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription,
			SUM(debtortrans.balance) AS balance,
			SUM(CASE WHEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) > 0 THEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) ELSE 0 END) AS total_invoices,
			SUM(CASE WHEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) < 0 THEN (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) ELSE 0 END) AS total_receipts,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >=
				paymentterms.daysbeforedue
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1', 'MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= 0
				THEN debtortrans.balance
				ELSE 0 END
			END) AS due,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
				AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >=
				(paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') .")) >= " . $_SESSION['PastDueDays1'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			END) AS overdue1,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
				AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue +
				" . $_SESSION['PastDueDays2'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " .
				interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . "))
				>= " . $_SESSION['PastDueDays2'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			END) AS overdue2
		FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
		INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
		INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
		LEFT JOIN debtortrans
			ON debtorsmaster.debtorno = debtortrans.debtorno
		WHERE
			debtorsmaster.debtorno = '" . $CustomerID . "'";
if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " GROUP BY
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			currencies.decimalplaces,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";
$ErrMsg = __('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);

$CustomerRecord = DB_fetch_array($CustomerResult);

	echo '<div class="db-page-header">
			<div>
				<h2 class="db-page-title">' . $Title . '</h2>
				<p class="db-page-subtitle">' . __('Account statement and outstanding balance summary') . '</p>
			</div>
			<div class="db-header-actions">
				<a href="' . $RootPath . '/SelectCustomer.php" class="db-btn db-btn-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:8px;"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
					' . __('Select Customer') . '
				</a>
				<a href="' . $RootPath . '/PrintCustStatements.php?FromCust=' . $CustomerID . '&ToCust=' . $CustomerID . '&PrintPDF=Yes&EmailOrPrint=print&TransAfterDate=' . FormatDateForSQL($_POST['TransAfterDate']) . '" target="_blank" class="db-btn db-btn-primary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:8px;"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
					' . __('Print Statement') . '
				</a>
			</div>
		</div>';

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05),0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<h3 style="margin:0; font-size:1.15rem; font-weight:700; color:var(--text-main); display:flex; align-items:center;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle; margin-right:8px; color:var(--primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					' . $CustomerRecord['name'] . ' (' . stripslashes($CustomerID) . ')
				</h3>
				<div class="db-header-actions">';
	if ($CustomerRecord['dissallowinvoices'] != 0) {
		echo '		<span style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; background-color:var(--danger-bg); color:var(--danger); border:1px solid var(--border);">' . __('ACCOUNT ON HOLD') . '</span>';
	}
	echo '			<span style="display:inline-flex; align-items:center; padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; background-color:var(--info-bg); color:var(--info); border:1px solid var(--border);">' . $CustomerRecord['currency'] . '</span>
				</div>
			</div>
			<div style="padding:24px;">
				<div class="db-grid db-grid-2">
					<div class="db-field">
						<label class="db-label">' . __('Billing Address') . '</label>
						<div class="db-field-value" style="white-space: pre-wrap;">' .
							$CustomerRecord['address1'] .
							($CustomerRecord['address2'] != '' ? "\n" . $CustomerRecord['address2'] : '') .
							($CustomerRecord['address3'] != '' ? "\n" . $CustomerRecord['address3'] : '') .
							"\n" . $CustomerRecord['address4'] .
							"\n" . $CustomerRecord['address5'] . ' ' . $CustomerRecord['address6'] .
						'</div>
					</div>
					<div>
						<div class="db-grid db-grid-2">
							<div class="db-field">
								<label class="db-label">' . __('Payment Terms') . '</label>
								<div class="db-field-value">' . $CustomerRecord['terms'] . '</div>
							</div>
							<div class="db-field">
								<label class="db-label">' . __('Credit Limit') . '</label>
								<div class="db-field-value">' . locale_number_format($CustomerRecord['creditlimit'], 0) . '</div>
							</div>
							<div class="db-field">
								<label class="db-label">' . __('Credit Status') . '</label>
								<div class="db-field-value">' . $CustomerRecord['reasondescription'] . '</div>
							</div>
							<div class="db-field">
								<label class="db-label">' . __('Current Balance') . '</label>
								<div class="db-field-value" style="font-weight: 700; color: var(--primary);">' . locale_number_format($CustomerRecord['balance'], $CustomerRecord['decimalplaces']) . '</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05),0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden; margin-top:24px;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<h3 style="margin:0; font-size:1.15rem; font-weight:700; color:var(--text-main); display:flex; align-items:center;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle; margin-right:8px; color:var(--primary);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
					' . __('Statement Filters') . '
				</h3>
			</div>
			<div style="padding:24px;">
				<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">
					<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
					<div style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap; background-color:var(--surface-alt); padding:20px; border-radius:12px; border:1px solid var(--border-soft);">
						<div style="display:flex; flex-direction:column; gap:6px;">
							<label style="font-size:0.875rem; font-weight:600; color:var(--text-main); margin:0;">' . __('Transactions After') . '</label>
							<input type="date" name="TransAfterDate" required="required" value="' . FormatDateForSQL($_POST['TransAfterDate']) . '" style="padding:10px 16px; border-radius:8px; border:1px solid var(--border); font-size:0.95rem; color:var(--text-main); background-color:var(--surface); outline:none; box-shadow:inset 0 1px 2px rgba(0,0,0,0.05); min-width:220px;" onfocus="this.style.borderColor=\'var(--primary)\'; this.style.boxShadow=\'0 0 0 3px var(--primary-soft)\';" onblur="this.style.borderColor=\'var(--border)\'; this.style.boxShadow=\'inset 0 1px 2px rgba(0,0,0,0.05)\';" />
						</div>
						<div style="display:flex; align-items:flex-end;">
							<button type="submit" name="Refresh Inquiry" style="padding:10px 24px; border-radius:8px; font-weight:600; font-size:0.95rem; border:none; background-color:var(--primary); color:white; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.1); transition:all 0.2s; display:flex; align-items:center; gap:8px;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 1px 3px rgba(0,0,0,0.1)\';">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
								' . __('Refresh Inquiry') . '
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>';

/* Show a table of the invoices returned by the SQL. */

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05),0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden; margin-top:24px;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<h3 style="margin:0; font-size:1.15rem; font-weight:700; color:var(--text-main); display:flex; align-items:center;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle; margin-right:8px; color:var(--primary);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-10l5 5 5-5m-5 5V3"></path></svg>
					' . __('Account Statement Transactions') . '
				</h3>
			</div>
			<div style="padding:24px;">
				<div class="db-table-wrapper">
					<table class="db-table" style="white-space:nowrap;">
						<thead>
							<tr>
								<th>' . __('Type') . '</th>
								<th>' . __('No') . '</th>
								<th>' . __('Date') . '</th>
								<th>' . __('Branch') . '</th>
								<th>' . __('Reference') . '</th>
								<th>' . __('Comments') . '</th>
								<th class="number">' . __('Charges') . '</th>
								<th class="number">' . __('Credits') . '</th>
								<th class="number">' . __('Allocated') . '</th>
								<th class="number">' . __('Balance') . '</th>
								<th class="noPrint">' . __('Actions') . '</th>
							</tr>
						</thead>
						<tbody>';

$OutstandingOrSettled = '';
if ($_SESSION['InvoicePortraitFormat'] == 1) { //Invoice/credits in portrait
	$Orientation = 'portrait';
} else { //produce pdfs in landscape
	$Orientation = 'landscape';
}
foreach ($Transactions as $MyRow) {

	if ($MyRow['settled']==1 AND $OutstandingOrSettled=='') {
		echo '<tr style="background: var(--surface-alt); font-weight: 700;">
				<td colspan="11">' . __('Settled Transactions Since') . ' ' . $_POST['TransAfterDate'] . '</td>
			</tr>';
		$OutstandingOrSettled='Settled';
	} elseif (($OutstandingOrSettled=='Settled' OR $OutstandingOrSettled=='') AND $MyRow['settled']==0) {
		echo '<tr style="background: var(--surface-alt); font-weight: 700;">
				<td colspan="11">' . __('Outstanding Transactions') . '</td>
			</tr>';
		$OutstandingOrSettled='Outstanding';
	}

	$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

	if ($MyRow['type']==10) { //its an invoice
		echo '<tr>
			<td>' . __($MyRow['typename']) . '</td>
			<td>' . $MyRow['transno'] . '</td>
			<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
			<td>' . $MyRow['branchcode'] . '</td>
			<td>' . $MyRow['reference'] . '</td>
			<td style="width:200px; white-space:nowrap;">' . $MyRow['invtext'] . '</td>
			<td class="number">' . locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']) . '</td>
			<td class="number noPrint">
				<div class="db-action-group" style="justify-content: flex-end;">
					<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Invoice&View=Yes" title="' . __('HTML') . '" target="_blank" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
					</a>
					<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Invoice&amp;PrintPDF=True&orientation=' . $Orientation . '" title="' . __('PDF') . '" target="_blank" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
					</a>
					<a href="' . $RootPath . '/EmailCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Invoice" title="' . __('Email') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
					</a>
				</div>
			</td>
		</tr>';

	} elseif ($MyRow['type'] == 11) {
		echo '<tr>
				<td>' . __($MyRow['typename']) . '</td>
				<td>' . $MyRow['transno'] . '</td>
				<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . $MyRow['branchcode'] . '</td>
				<td>' . $MyRow['reference'] . '</td>
				<td style="width:200px; white-space:nowrap;">' . $MyRow['invtext'] . '</td>
				<td>&nbsp;</td>
				<td class="number">' . locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number noPrint">
					<div class="db-action-group" style="justify-content: flex-end;">
						<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Credit" title="' . __('HTML') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						</a>
						<a href="' . $RootPath . '/' . $PrintCustomerTransactionScript . '?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Credit&amp;PrintPDF=True" title="' . __('PDF') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
						</a>
						<a href="' . $RootPath . '/EmailCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Credit" title="' . __('Email') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
						</a>
						<a href="' . $RootPath . '/CustomerAllocations.php?AllocTrans=' . $MyRow['id'] . '" title="' . __('Allocation') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20"></path></svg>
						</a>
					</div>
				</td>
			</tr>';

	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] < 0) {
		echo '<tr>
				<td>' . __($MyRow['typename']) . '</td>
				<td>' . $MyRow['transno'] . '</td>
				<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . $MyRow['branchcode'] . '</td>
				<td>' . $MyRow['reference'] . '</td>
				<td style="width:200px; white-space:nowrap;">' . $MyRow['invtext'] . '</td>
				<td>&nbsp;</td>
				<td class="number">' . locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number noPrint">
					<div class="db-action-group" style="justify-content: flex-end;">
						<a href="' . $RootPath . '/CustomerAllocations.php?AllocTrans=' . $MyRow['id'] . '" title="' . __('Allocation') . '" class="db-btn db-btn-secondary" style="padding: 4px 8px;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20"></path></svg>
						</a>
					</div>
				</td>
			</tr>';

	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] > 0) {
		echo '<tr>
				<td>' . __($MyRow['typename']) . '</td>
				<td>' . $MyRow['transno'] . '</td>
				<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . $MyRow['branchcode'] . '</td>
				<td>' . $MyRow['reference'] . '</td>
				<td style="width:200px; white-space:nowrap;">' . $MyRow['invtext'] . '</td>
				<td class="number">' . locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']) . '</td>
				<td>&nbsp;</td>
				<td class="number">' . locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']) . '</td>
				<td class="number noPrint">&nbsp;</td>
			</tr>';
	}
}

	echo '</tbody></table></div></div></div>'; // Close db-table, db-table-wrapper, db-card-body, card-v2

	echo '<div style="background-color:var(--surface); border:1px solid var(--border-soft); border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05),0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom:24px; overflow:hidden; margin-top:24px;">
			<div style="background-color:var(--surface-alt); border-bottom:1px solid var(--border-soft); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
				<h3 style="margin:0; font-size:1.15rem; font-weight:700; color:var(--text-main); display:flex; align-items:center;">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle; margin-right:8px; color:var(--primary);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
					' . __('Aging Summary') . '
				</h3>
			</div>
			<div style="padding:24px;">
				<div class="db-table-wrapper">
					<table class="db-table" style="white-space:nowrap;">
						<thead>
							<tr>
								<th class="number">' . __('Total Balance') . '</th>
								<th class="number">' . __('Current') . '</th>
								<th class="number">' . __('Now Due') . '</th>
								<th class="number">' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' Days</th>
								<th class="number">' . __('Over') . ' ' . $_SESSION['PastDueDays2'] . ' Days</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="number">' . locale_number_format($CustomerRecord['balance'], $CustomerRecord['decimalplaces']) . '</td>
								<td class="number">' . locale_number_format(($CustomerRecord['balance'] - $CustomerRecord['due']), $CustomerRecord['decimalplaces']) . '</td>
								<td class="number">' . locale_number_format(($CustomerRecord['due'] - $CustomerRecord['overdue1']), $CustomerRecord['decimalplaces']) . '</td>
								<td class="number">' . locale_number_format(($CustomerRecord['overdue1'] - $CustomerRecord['overdue2']), $CustomerRecord['decimalplaces']) . '</td>
								<td class="number text-danger">' . locale_number_format($CustomerRecord['overdue2'], $CustomerRecord['decimalplaces']) . '</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>';

		echo '</div>'; // Close db-page
include(__DIR__ . '/includes/footer.php');
