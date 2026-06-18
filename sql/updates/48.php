<?php
// UPDATE THE DATABASE BY ADDING TABLES AND ALTERING TABLE COLUMNS IN ORDER TO
// FACILITATE SARIS AND webERP Systems Integration
// by Juma Lungo <babamadende@gmail.com>, Tanzania
// 18 June 2026

// 1. delete foreign key constraints (Bottom-Up-Drop aka BUD)
//
// SQL query to list foreign key constraints on debtorsmaster.debtorno:

//     SELECT 
//         TABLE_NAME AS 'Referencing Table', 
//         COLUMN_NAME AS 'Foreign Key Column', 
//         CONSTRAINT_NAME AS 'Constraint Name'
//     FROM 
//         information_schema.KEY_COLUMN_USAGE
//     WHERE 
//         REFERENCED_TABLE_NAME = 'debtorsmaster' 
//         AND REFERENCED_COLUMN_NAME = 'debtorno'
//         AND TABLE_SCHEMA = DATABASE(); -- Limits results to your current webERP database
// +-----------------------------+--------------------+------------------------------------+
// | Referencing Table           | Foreign Key Column | Constraint Name                    |
// +-----------------------------+--------------------+------------------------------------+
// | custbranch                  | debtorno           | custbranch_ibfk_1                  |
// | orderdeliverydifferenceslog | debtorno           | orderdeliverydifferenceslog_ibfk_2 |
// | custitem                    | debtorno           | custitem_ibfk_2                    |
// +-----------------------------+--------------------+------------------------------------+

// 1.1 Drop all debtorno FKs
// -- Drop all debtorno FKs
// ALTER TABLE contracts                   DROP FOREIGN KEY contracts_ibfk_1;
// ALTER TABLE custbranch                  DROP FOREIGN KEY custbranch_ibfk_1;
// ALTER TABLE custitem                    DROP FOREIGN KEY custitem_ibfk_2;
// ALTER TABLE orderdeliverydifferenceslog DROP FOREIGN KEY orderdeliverydifferenceslog_ibfk_2;
// ALTER TABLE recurringsalesorders        DROP FOREIGN KEY recurringsalesorders_ibfk_1;
// ALTER TABLE salesorders                 DROP FOREIGN KEY salesorders_ibfk_1;

// DropConstraint($Table, $Constraint)
DropConstraint('contracts', 'contracts_ibfk_1');
DropConstraint('custbranch', 'custbranch_ibfk_1');
DropConstraint('custitem', 'custitem_ibfk_2');
DropConstraint('orderdeliverydifferenceslog', 'orderdeliverydifferenceslog_ibfk_2');
DropConstraint('recurringsalesorders', 'recurringsalesorders_ibfk_1');
DropConstraint('salesorders', 'salesorders_ibfk_1');

//2. Listdown all tables with field debtorno that will be needed to be changed fron char(10) to char(32)
// SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
// FROM information_schema.COLUMNS
// WHERE TABLE_SCHEMA = 'zerp_backend'
// AND COLUMN_NAME = 'debtorno'
// ORDER BY CHARACTER_MAXIMUM_LENGTH, TABLE_NAME;
// +-----------------------------+-------------+--------------------------+
// | TABLE_NAME                  | COLUMN_NAME | CHARACTER_MAXIMUM_LENGTH |
// +-----------------------------+-------------+--------------------------+
// | contracts                   | debtorno    |                       10 |
// | custbranch                  | debtorno    |                       10 |
// | custcontacts                | debtorno    |                       10 |
// | custitem                    | debtorno    |                       10 |
// | custnotes                   | debtorno    |                       10 |
// | debtorsmaster               | debtorno    |                       10 |
// | debtortrans                 | debtorno    |                       10 |
// | orderdeliverydifferenceslog | debtorno    |                       10 |
// | prices                      | debtorno    |                       10 |
// | recurringsalesorders        | debtorno    |                       10 |
// | salesorders                 | debtorno    |                       10 |
// | sellthroughsupport          | debtorno    |                       10 |
// | stockmoves                  | debtorno    |                       10 |
// +-----------------------------+-------------+--------------------------+

// 3. Alter the two remaining tables by changing "debtorno" parent, child and grandchild foreign key columns (bottom-up order, same as BUD for fk constraints)
// -- Alter the two remaining tables
// ALTER TABLE contracts     MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE custbranch MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE custcontacts      MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE custnotes MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE orderdeliverydifferenceslog      MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE debtortrans MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE prices MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE recurringsalesorders      MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE salesorders MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE sellthroughsupport      MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE stockmoves MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE custitem      MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE debtorsmaster MODIFY COLUMN debtorno VARCHAR(32) NOT NULL;
// ALTER TABLE custbranch           MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE contracts            MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE recurringsalesorders MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE prices               MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE salesorders          MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE debtortrans          MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE www_users            MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;
// ALTER TABLE stockmoves           MODIFY COLUMN branchcode VARCHAR(32) NOT NULL;

// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('debtorno', 'debtorsmaster', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'contracts', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'custbranch', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'custcontacts', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'custitem', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'custnotes', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'debtortrans', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'orderdeliverydifferenceslog', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'prices', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'recurringsalesorders', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'salesorders', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'sellthroughsupport', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('debtorno', 'stockmoves', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'custbranch', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'contracts', 'VARCHAR(34)', ' NOT NULL ', '', '32');
ChangeColumnSize('branchcode', 'recurringsalesorders', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'prices', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'salesorders', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'debtortrans', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'www_users', 'VARCHAR(34)', ' NOT NULL ', '', '34');
ChangeColumnSize('branchcode', 'stockmoves', 'VARCHAR(34)', ' NOT NULL ', '', '34');


// 3. Recreate all FKs (Top-Down-Add aka TDA)
// -- Recreate all FKs
// ALTER TABLE custbranch                  ADD CONSTRAINT custbranch_ibfk_1                  FOREIGN KEY (debtorno) REFERENCES debtorsmaster(debtorno);
// ALTER TABLE custitem                    ADD CONSTRAINT custitem_ibfk_2                    FOREIGN KEY (debtorno) REFERENCES debtorsmaster(debtorno);
// ALTER TABLE orderdeliverydifferenceslog ADD CONSTRAINT orderdeliverydifferenceslog_ibfk_2 FOREIGN KEY (debtorno) REFERENCES debtorsmaster(debtorno);
// ALTER TABLE contracts                   ADD CONSTRAINT contracts_ibfk_1                   FOREIGN KEY (debtorno, branchcode) REFERENCES custbranch(debtorno, branchcode);
// ALTER TABLE recurringsalesorders        ADD CONSTRAINT recurringsalesorders_ibfk_1        FOREIGN KEY (debtorno, branchcode) REFERENCES custbranch(debtorno, branchcode);
// ALTER TABLE salesorders                 ADD CONSTRAINT salesorders_ibfk_1                 FOREIGN KEY (debtorno, branchcode) REFERENCES custbranch(debtorno, branchcode);

// AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField)
AddConstraint('custbranch', 'custbranch_ibfk_1', 'debtorno', 'debtorsmaster', 'debtorno');
AddConstraint('custitem', 'custitem_ibfk_2', 'debtorno', 'debtorsmaster', 'debtorno');
AddConstraint('orderdeliverydifferenceslog', 'orderdeliverydifferenceslog_ibfk_2', 'debtorno', 'debtorsmaster', 'debtorno');
AddConstraint('contracts', 'contracts_ibfk_1', 'debtorno,branchcode', 'custbranch', 'debtorno,branchcode');
AddConstraint('recurringsalesorders', 'recurringsalesorders_ibfk_1', 'debtorno,branchcode', 'custbranch', 'debtorno,branchcode');
AddConstraint('salesorders', 'salesorders_ibfk_1', 'debtorno,branchcode', 'custbranch', 'debtorno,branchcode');



// -- Final verify debtorno
// SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
// FROM information_schema.COLUMNS
// WHERE TABLE_SCHEMA = 'zerp_backend'
// AND COLUMN_NAME = 'debtorno'
// ORDER BY CHARACTER_MAXIMUM_LENGTH, TABLE_NAME;

// -- Final verify branchcode
// SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
// FROM information_schema.COLUMNS
// WHERE TABLE_SCHEMA = 'zerp_backend'
// AND COLUMN_NAME = 'branchcode'
// ORDER BY CHARACTER_MAXIMUM_LENGTH, TABLE_NAME;

// 4. Create the SARIS integration and synchronization tables.
// CreateTable() checks whether each table already exists before running its SQL.

CreateTable('saris_settings', "CREATE TABLE `saris_settings` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`sync_mode` ENUM('manual', 'automatic') NOT NULL DEFAULT 'manual',
	`sync_interval` ENUM('10min', '30min', '1hr', '1day') NULL,
	`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)");

CreateTable('students', "CREATE TABLE `students` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`student_regnumber` VARCHAR(100) NULL,
	`student_fullname` VARCHAR(255) NULL,
	`student_email` VARCHAR(255) NULL,
	`student_phone` VARCHAR(50) NULL,
	`student_programme` VARCHAR(255) NULL,
	`student_entryyear` VARCHAR(20) NULL,
	`student_studyyear` INT NULL,
	`student_intake` VARCHAR(100) NULL,
	`sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
	`sync_attempts` INT NOT NULL DEFAULT 0,
	`sync_error` TEXT NULL,
	`sync_locked_at` DATETIME NULL,
	`synced_at` DATETIME NULL,
	`zerp_customer_code` VARCHAR(10) NULL,
	`customer_synced_at` DATETIME NULL,
	`branch_synced_at` DATETIME NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `student_regnumber` (`student_regnumber`),
	UNIQUE KEY `uq_students_zerp_customer` (`zerp_customer_code`),
	KEY `idx_students_sync_status` (`sync_status`, `sync_attempts`)
)");

CreateTable('invoices', "CREATE TABLE `invoices` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`student_name` VARCHAR(255) NULL,
	`invoice_reference_number` VARCHAR(100) NULL,
	`student_regnumber` VARCHAR(100) NULL,
	`invoice_amount` DECIMAL(15,2) NULL,
	`invoice_amount_type` VARCHAR(50) NULL,
	`invoice_desciption` TEXT NULL,
	`invoice_date` DATETIME NULL,
	`sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
	`sync_attempts` INT NOT NULL DEFAULT 0,
	`sync_error` TEXT NULL,
	`sync_locked_at` DATETIME NULL,
	`synced_at` DATETIME NULL,
	`zerp_invoice_no` INT NULL,
	`zerp_invoice_reference` VARCHAR(50) NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `invoice_reference_number` (`invoice_reference_number`),
	UNIQUE KEY `uq_invoices_zerp_invoice` (`zerp_invoice_no`),
	KEY `idx_invoices_sync_status` (`sync_status`, `sync_attempts`)
)");

CreateTable('payments', "CREATE TABLE `payments` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`student_name` VARCHAR(255) NULL,
	`student_regnumber` VARCHAR(100) NULL,
	`payment_desciption` TEXT NULL,
	`payment_amount` DECIMAL(15,2) NULL,
	`payment_amount_type` VARCHAR(50) NULL,
	`payment_currency` VARCHAR(10) NULL,
	`payment_receipt_number` VARCHAR(100) NULL,
	`payment_transaction_ref` VARCHAR(100) NULL,
	`payment_date` DATETIME NULL,
	`payment_reference_number` VARCHAR(100) NULL,
	`payment_source` VARCHAR(100) NULL,
	`sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
	`sync_attempts` INT NOT NULL DEFAULT 0,
	`sync_error` TEXT NULL,
	`sync_locked_at` DATETIME NULL,
	`synced_at` DATETIME NULL,
	`zerp_receipt_no` INT NULL,
	`zerp_invoice_no` INT NULL,
	`allocation_synced_at` DATETIME NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_payments_receipt_number` (`payment_receipt_number`),
	UNIQUE KEY `uq_payments_composite` (`payment_transaction_ref`, `student_regnumber`, `payment_date`, `payment_amount`),
	UNIQUE KEY `uq_payments_zerp_receipt` (`zerp_receipt_no`),
	KEY `idx_payments_sync_status` (`sync_status`, `sync_attempts`)
)");

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

CreateTable('saris_sync_log', "CREATE TABLE `saris_sync_log` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`sync_status` VARCHAR(20) NOT NULL,
	`message` TEXT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)");

// 5. Insert the default SARIS synchronization setting.
InsertRecord(
	'saris_settings',
	array('id'),
	array(1),
	array('id', 'sync_mode'),
	array(1, 'manual')
);

// 6. Expand modules.reportlink to hold the SARIS report link.
ChangeColumnSize('reportlink', 'modules', 'VARCHAR(6)', ' NOT NULL ', '', '6');

// 7. Register the SARIS module and its menu items for every security role.
// NewModule() and NewMenuItem() are idempotent and perform the role-specific inserts.
NewModule('SARIS', 'saris', __('SARIS Integration'), 9);

NewMenuItem('SARIS', 'Transactions', __('Settings'), '/SARIS_Settings.php', 1);
NewMenuItem('SARIS', 'Transactions', __('Students'), '/SARIS_Students.php', 2);
NewMenuItem('SARIS', 'Transactions', __('Invoices'), '/SARIS_Invoices.php', 3);
NewMenuItem('SARIS', 'Transactions', __('Payments'), '/SARIS_Payments.php', 4);

// 8. Register the SARIS pages and retain the descriptions from db_setup.sql.
NewScript('SARIS_Settings.php', 15);
NewScript('SARIS_Students.php', 15);
NewScript('SARIS_Invoices.php', 15);
NewScript('SARIS_Payments.php', 15);

UpdateField('scripts', 'pagesecurity', 15, "script='SARIS_Settings.php'");
UpdateField('scripts', 'description', 'SARIS Integration settings', "script='SARIS_Settings.php'");
UpdateField('scripts', 'pagesecurity', 15, "script='SARIS_Students.php'");
UpdateField('scripts', 'description', 'SARIS Integration students sync and listing', "script='SARIS_Students.php'");
UpdateField('scripts', 'pagesecurity', 15, "script='SARIS_Invoices.php'");
UpdateField('scripts', 'description', 'SARIS Integration invoices listing', "script='SARIS_Invoices.php'");
UpdateField('scripts', 'pagesecurity', 15, "script='SARIS_Payments.php'");
UpdateField('scripts', 'description', 'SARIS Integration payments sync and listing', "script='SARIS_Payments.php'");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Increase customer code sizes and add SARIS-to-ZERP integration tables')
	);
}
