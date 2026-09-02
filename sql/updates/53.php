<?php

// Fix 1: Drop the broken all-nullable composite key that prevented correct duplicate checking
$sql = "SELECT COUNT(*) FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'payments' 
        AND index_name = 'uq_payments_composite'";
$result = DB_query($sql);
$row = DB_fetch_row($result);
if ($row[0] > 0) {
    DB_query("ALTER TABLE payments DROP INDEX uq_payments_composite");
}

// Fix 2: Add an index on student_regnumber to payments to prevent full table scans during sync
$sql = "SELECT COUNT(*) FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'payments' 
        AND index_name = 'idx_payments_student_regnumber'";
$result = DB_query($sql);
$row = DB_fetch_row($result);
if ($row[0] == 0) {
    DB_query("ALTER TABLE payments ADD INDEX idx_payments_student_regnumber (student_regnumber)");
}

if ($_SESSION['Updates']['Errors'] == 0) {
    UpdateDBNo(basename(__FILE__, '.php'), __('Fix SARIS payments table indexes for production'));
}
?>
