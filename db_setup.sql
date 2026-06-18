CREATE TABLE IF NOT EXISTS `saris_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sync_mode` ENUM('manual', 'automatic') NOT NULL DEFAULT 'manual',
  `sync_interval` ENUM('10min', '30min', '1hr', '1day') NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_regnumber` VARCHAR(100) UNIQUE,
  `student_fullname` VARCHAR(255),
  `student_email` VARCHAR(255),
  `student_phone` VARCHAR(50),
  `student_programme` VARCHAR(255),
  `student_entryyear` VARCHAR(20),
  `student_studyyear` INT,
  `student_intake` VARCHAR(100),
  `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `sync_attempts` INT NOT NULL DEFAULT 0,
  `sync_error` TEXT NULL,
  `sync_locked_at` DATETIME NULL,
  `synced_at` DATETIME NULL,
  `zerp_customer_code` VARCHAR(10) NULL,
  `customer_synced_at` DATETIME NULL,
  `branch_synced_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_students_zerp_customer` (`zerp_customer_code`),
  KEY `idx_students_sync_status` (`sync_status`, `sync_attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(255),
  `invoice_reference_number` VARCHAR(100) UNIQUE,
  `student_regnumber` VARCHAR(100),
  `invoice_amount` DECIMAL(15,2),
  `invoice_amount_type` VARCHAR(50),
  `invoice_desciption` TEXT,
  `invoice_date` DATETIME,
  `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `sync_attempts` INT NOT NULL DEFAULT 0,
  `sync_error` TEXT NULL,
  `sync_locked_at` DATETIME NULL,
  `synced_at` DATETIME NULL,
  `zerp_invoice_no` INT NULL,
  `zerp_invoice_reference` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_invoices_zerp_invoice` (`zerp_invoice_no`),
  KEY `idx_invoices_sync_status` (`sync_status`, `sync_attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(255),
  `student_regnumber` VARCHAR(100),
  `payment_desciption` TEXT,
  `payment_amount` DECIMAL(15,2),
  `payment_amount_type` VARCHAR(50),
  `payment_currency` VARCHAR(10),
  `payment_receipt_number` VARCHAR(100),
  `payment_transaction_ref` VARCHAR(100),
  `payment_date` DATETIME,
  `payment_reference_number` VARCHAR(100),
  `payment_source` VARCHAR(100),
  `sync_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `sync_attempts` INT NOT NULL DEFAULT 0,
  `sync_error` TEXT NULL,
  `sync_locked_at` DATETIME NULL,
  `synced_at` DATETIME NULL,
  `zerp_receipt_no` INT NULL,
  `zerp_invoice_no` INT NULL,
  `allocation_synced_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_payments_receipt_number` (`payment_receipt_number`),
  UNIQUE KEY `uq_payments_composite` (`payment_transaction_ref`, `student_regnumber`, `payment_date`, `payment_amount`),
  UNIQUE KEY `uq_payments_zerp_receipt` (`zerp_receipt_no`),
  KEY `idx_payments_sync_status` (`sync_status`, `sync_attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `saris_sync_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sync_status` VARCHAR(20) NOT NULL,
  `message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `saris_settings` (`id`, `sync_mode`, `sync_interval`)
VALUES (1, 'manual', NULL)
ON DUPLICATE KEY UPDATE `id` = `id`;

UPDATE `modules`
SET `sequence` = `sequence` + 1
WHERE `sequence` > 8
  AND NOT EXISTS (
    SELECT 1 FROM (
      SELECT `modulelink` FROM `modules` WHERE `modulelink` = 'SARIS' LIMIT 1
    ) AS existing_saris_module
  );

ALTER TABLE modules MODIFY COLUMN reportlink VARCHAR(6);

INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`)
SELECT `secroleid`, 'SARIS', 'saris', 'SARIS Integration', 9
FROM (SELECT DISTINCT `secroleid` FROM `modules`) AS roles
WHERE NOT EXISTS (
  SELECT 1 FROM `modules` m
  WHERE m.`secroleid` = roles.`secroleid`
    AND m.`modulelink` = 'SARIS'
);

INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`)
SELECT `secroleid`, 'SARIS', 'Transactions', 'Settings', '/SARIS_Settings.php', 1
FROM (SELECT DISTINCT `secroleid` FROM `modules`) AS roles
WHERE NOT EXISTS (
  SELECT 1 FROM `menuitems` mi
  WHERE mi.`secroleid` = roles.`secroleid`
    AND mi.`modulelink` = 'SARIS'
    AND mi.`url` = '/SARIS_Settings.php'
);

INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`)
SELECT `secroleid`, 'SARIS', 'Transactions', 'Students', '/SARIS_Students.php', 2
FROM (SELECT DISTINCT `secroleid` FROM `modules`) AS roles
WHERE NOT EXISTS (
  SELECT 1 FROM `menuitems` mi
  WHERE mi.`secroleid` = roles.`secroleid`
    AND mi.`modulelink` = 'SARIS'
    AND mi.`url` = '/SARIS_Students.php'
);

INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`)
SELECT `secroleid`, 'SARIS', 'Transactions', 'Invoices', '/SARIS_Invoices.php', 3
FROM (SELECT DISTINCT `secroleid` FROM `modules`) AS roles
WHERE NOT EXISTS (
  SELECT 1 FROM `menuitems` mi
  WHERE mi.`secroleid` = roles.`secroleid`
    AND mi.`modulelink` = 'SARIS'
    AND mi.`url` = '/SARIS_Invoices.php'
);

INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`)
SELECT `secroleid`, 'SARIS', 'Transactions', 'Payments', '/SARIS_Payments.php', 4
FROM (SELECT DISTINCT `secroleid` FROM `modules`) AS roles
WHERE NOT EXISTS (
  SELECT 1 FROM `menuitems` mi
  WHERE mi.`secroleid` = roles.`secroleid`
    AND mi.`modulelink` = 'SARIS'
    AND mi.`url` = '/SARIS_Payments.php'
);

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
('SARIS_Settings.php', 15, 'SARIS Integration settings'),
('SARIS_Students.php', 15, 'SARIS Integration students sync and listing'),
('SARIS_Invoices.php', 15, 'SARIS Integration invoices listing'),
('SARIS_Payments.php', 15, 'SARIS Integration payments sync and listing')
ON DUPLICATE KEY UPDATE
  `pagesecurity` = VALUES(`pagesecurity`),
  `description` = VALUES(`description`);

-- Crontab examples:
-- 10 min: */10 * * * * php /Users/user/Documents/Projects2/zerp-backend/saris_cron.php
-- 30 min: */30 * * * * php /Users/user/Documents/Projects2/zerp-backend/saris_cron.php
-- 1 hr: 0 * * * * php /Users/user/Documents/Projects2/zerp-backend/saris_cron.php
-- 1 day: 0 2 * * * php /Users/user/Documents/Projects2/zerp-backend/saris_cron.php
