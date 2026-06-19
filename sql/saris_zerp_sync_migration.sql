-- Apply once to databases which already contain the SARIS staging tables.

ALTER TABLE `students`
  ADD COLUMN `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  ADD COLUMN `sync_attempts` INT NOT NULL DEFAULT 0,
  ADD COLUMN `sync_error` TEXT NULL,
  ADD COLUMN `sync_locked_at` DATETIME NULL,
  ADD COLUMN `synced_at` DATETIME NULL,
  ADD COLUMN `zerp_customer_code` VARCHAR(10) NULL,
  ADD COLUMN `customer_synced_at` DATETIME NULL,
  ADD COLUMN `branch_synced_at` DATETIME NULL,
  ADD UNIQUE KEY `uq_students_zerp_customer` (`zerp_customer_code`),
  ADD KEY `idx_students_sync_status` (`sync_status`, `sync_attempts`);

ALTER TABLE `invoices`
  ADD COLUMN `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  ADD COLUMN `sync_attempts` INT NOT NULL DEFAULT 0,
  ADD COLUMN `sync_error` TEXT NULL,
  ADD COLUMN `sync_locked_at` DATETIME NULL,
  ADD COLUMN `synced_at` DATETIME NULL,
  ADD COLUMN `zerp_invoice_no` INT NULL,
  ADD COLUMN `zerp_invoice_reference` VARCHAR(50) NULL,
  ADD UNIQUE KEY `uq_invoices_zerp_invoice` (`zerp_invoice_no`),
  ADD KEY `idx_invoices_sync_status` (`sync_status`, `sync_attempts`);

ALTER TABLE `payments`
  ADD COLUMN `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  ADD COLUMN `sync_attempts` INT NOT NULL DEFAULT 0,
  ADD COLUMN `sync_error` TEXT NULL,
  ADD COLUMN `sync_locked_at` DATETIME NULL,
  ADD COLUMN `synced_at` DATETIME NULL,
  ADD COLUMN `zerp_receipt_no` INT NULL,
  ADD COLUMN `zerp_invoice_no` INT NULL,
  ADD COLUMN `allocation_synced_at` DATETIME NULL,
  ADD UNIQUE KEY `uq_payments_zerp_receipt` (`zerp_receipt_no`),
  ADD KEY `idx_payments_sync_status` (`sync_status`, `sync_attempts`);

CREATE TABLE IF NOT EXISTS `zerp_sync_log` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
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
  KEY `idx_zerp_sync_log_run` (`run_id`),
  KEY `idx_zerp_sync_log_record` (`record_type`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
