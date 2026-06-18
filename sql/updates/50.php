<?php

// Store structured SARIS and ZERP synchronization statistics and expose them
// through a permanent history page.

AddColumn('run_id', 'saris_sync_log', 'VARCHAR(36)', 'NULL', '', 'id');
AddColumn('trigger_type', 'saris_sync_log', 'VARCHAR(20)', 'NOT NULL', 'automatic', 'run_id');
AddColumn('date_from', 'saris_sync_log', 'DATE', 'NULL', '', 'sync_status');
AddColumn('date_to', 'saris_sync_log', 'DATE', 'NULL', '', 'date_from');
AddColumn('iterations', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'date_to');
AddColumn('saris_invoices', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'iterations');
AddColumn('saris_students', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'saris_invoices');
AddColumn('saris_payments', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'saris_students');
AddColumn('zerp_enabled', 'saris_sync_log', 'TINYINT(1)', 'NOT NULL', '0', 'saris_payments');
AddColumn('zerp_students', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_enabled');
AddColumn('zerp_invoices', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_students');
AddColumn('zerp_payments', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_invoices');
AddColumn('zerp_partial', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_payments');
AddColumn('zerp_failed', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_partial');
AddColumn('zerp_skipped', 'saris_sync_log', 'INT', 'NOT NULL', '0', 'zerp_failed');
AddColumn('error_summary', 'saris_sync_log', 'text', 'NULL', '', 'zerp_skipped');
AddColumn('started_at', 'saris_sync_log', 'DATETIME', 'NULL', '', 'message');
AddColumn('completed_at', 'saris_sync_log', 'DATETIME', 'NULL', '', 'started_at');
AddColumn('duration_seconds', 'saris_sync_log', 'DECIMAL(12,3)', 'NULL', '', 'completed_at');

AddIndex(array('run_id'), 'saris_sync_log', 'idx_saris_sync_log_run');
AddIndex(array('created_at'), 'saris_sync_log', 'idx_saris_sync_log_created');

NewMenuItem('SARIS', 'Transactions', __('Sync History'), '/SARIS_SyncHistory.php', 5);
NewScript('SARIS_SyncHistory.php', 15);
UpdateField('scripts', 'description', 'SARIS and ZERP synchronization history', "script='SARIS_SyncHistory.php'");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(
		basename(__FILE__, '.php'),
		__('Add structured SARIS-to-ZERP synchronization history')
	);
}
