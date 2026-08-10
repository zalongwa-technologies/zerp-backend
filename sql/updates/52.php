<?php

// Create dedicated settings table for SARIS-IAE REST API v1.0 configuration,
// seed default endpoint paths, and register the admin settings page in the menu.

CreateTable('saris_api_settings', "CREATE TABLE `saris_api_settings` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`setting_key` VARCHAR(100) NOT NULL,
	`setting_value` TEXT,
	`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_saris_api_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed defaults — ON DUPLICATE KEY with no-op so existing values are never overwritten
$_SARISDefaults = [
	'saris_base_url'         => 'https://saris.iae.ac.tz',
	'saris_token_endpoint'   => '/api/v1/login',
	'saris_student_endpoint' => '/api/v1/students',
	'saris_invoice_endpoint' => '/api/v1/invoices',
	'saris_payment_endpoint' => '/api/v1/payments',
	'saris_client_id'        => '',
	'saris_client_secret'    => '',
];
foreach ($_SARISDefaults as $_k => $_v) {
	DB_query(
		"INSERT INTO saris_api_settings (setting_key, setting_value)
		VALUES ('" . DB_escape_string($_k) . "', '" . DB_escape_string($_v) . "')
		ON DUPLICATE KEY UPDATE setting_key = setting_key"
	);
}
unset($_SARISDefaults, $_k, $_v);

NewMenuItem('SARIS', 'Transactions', __('API Configuration'), '/SARIS_APIConfig.php', 2);
NewScript('SARIS_APIConfig.php', 15);
UpdateField('scripts', 'description', 'SARIS API Configuration — OAuth2 endpoints and credentials', "script='SARIS_APIConfig.php'");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Add SARIS-IAE API v1.0 configuration table and admin settings page')
	);
}