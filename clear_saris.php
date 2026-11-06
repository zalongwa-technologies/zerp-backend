<?php
$PageSecurity = 15;
$_POST['DatabaseName'] = 'zerp_10TZ120093';
$AllowCompanySelectionBox = 'hide';
include('includes/session.php');
DB_query("DELETE FROM saris_payments");
DB_query("DELETE FROM saris_invoices");
DB_query("DELETE FROM saris_students");
DB_query("DELETE FROM debtortrans");
DB_query("DELETE FROM banktrans");
DB_query("DELETE FROM systypes WHERE typeid = 12 OR typeid = 10 OR typeid = 11");
DB_query("DELETE FROM salesorderdetails");
DB_query("DELETE FROM salesorders");
DB_query("DELETE FROM debtorsmaster");
DB_query("DELETE FROM custbranch");
DB_query("UPDATE systypes SET typeno = 0 WHERE typeid = 12");
echo "Done\n";
?>
