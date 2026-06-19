<?php

// Repair existing SARIS staging tables which predate the ZERP synchronization
// metadata. Update 48 originally used CreateTable(), which intentionally skips
// tables that already exist and therefore did not add these columns.

CreateTable('zerp_sync_log', "CREATE TABLE `zerp_sync_log` (
	`id` BIGINT NOT NULL AUTO_INCREMENT,
	`run_id` VARCHAR(36) NOT NULL,
	`record_type` VARCHAR(20) NOT NULL,
	`record_id` INT NULL,
	`source_reference` VARCHAR(100) NULL,
	`xmlrpc_method` VARCHAR(100) NULL,
	`attempt_number` INT NOT NULL DEFAULT 0,
	`sync_status` VARCHAR(20) NOT NULL,
	`fault_code` VARCHAR(20) NULL,
	`message` TEXT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_zerp_sync_log_run` (`run_id`),
	KEY `idx_zerp_sync_log_record` (`record_type`, `record_id`)
)");

AddColumn('sync_status', 'students', 'VARCHAR(20)', 'NOT NULL', 'pending', 'student_intake');
AddColumn('sync_attempts', 'students', 'INT', 'NOT NULL', '0', 'sync_status');
AddColumn('sync_error', 'students', 'text', 'NULL', '', 'sync_attempts');
AddColumn('sync_locked_at', 'students', 'DATETIME', 'NULL', '', 'sync_error');
AddColumn('synced_at', 'students', 'DATETIME', 'NULL', '', 'sync_locked_at');
AddColumn('zerp_customer_code', 'students', 'VARCHAR(10)', 'NULL', '', 'synced_at');
AddColumn('customer_synced_at', 'students', 'DATETIME', 'NULL', '', 'zerp_customer_code');
AddColumn('branch_synced_at', 'students', 'DATETIME', 'NULL', '', 'customer_synced_at');
AddIndex(array('sync_status', 'sync_attempts'), 'students', 'idx_students_sync_status');

AddColumn('sync_status', 'invoices', 'VARCHAR(20)', 'NOT NULL', 'pending', 'invoice_date');
AddColumn('sync_attempts', 'invoices', 'INT', 'NOT NULL', '0', 'sync_status');
AddColumn('sync_error', 'invoices', 'text', 'NULL', '', 'sync_attempts');
AddColumn('sync_locked_at', 'invoices', 'DATETIME', 'NULL', '', 'sync_error');
AddColumn('synced_at', 'invoices', 'DATETIME', 'NULL', '', 'sync_locked_at');
AddColumn('zerp_invoice_no', 'invoices', 'INT', 'NULL', '', 'synced_at');
AddColumn('zerp_invoice_reference', 'invoices', 'VARCHAR(50)', 'NULL', '', 'zerp_invoice_no');
AddIndex(array('sync_status', 'sync_attempts'), 'invoices', 'idx_invoices_sync_status');

AddColumn('sync_status', 'payments', 'VARCHAR(20)', 'NOT NULL', 'pending', 'payment_source');
AddColumn('sync_attempts', 'payments', 'INT', 'NOT NULL', '0', 'sync_status');
AddColumn('sync_error', 'payments', 'text', 'NULL', '', 'sync_attempts');
AddColumn('sync_locked_at', 'payments', 'DATETIME', 'NULL', '', 'sync_error');
AddColumn('synced_at', 'payments', 'DATETIME', 'NULL', '', 'sync_locked_at');
AddColumn('zerp_receipt_no', 'payments', 'INT', 'NULL', '', 'synced_at');
AddColumn('zerp_invoice_no', 'payments', 'INT', 'NULL', '', 'zerp_receipt_no');
AddColumn('allocation_synced_at', 'payments', 'DATETIME', 'NULL', '', 'zerp_invoice_no');
AddIndex(array('sync_status', 'sync_attempts'), 'payments', 'idx_payments_sync_status');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Add missing ZERP synchronization metadata to existing SARIS tables')
	);
}
