<?php
/* Modern SARIS Integration Dashboard - High-fidelity overview for the SARIS module */

// 1. Data Fetching
// In case the tables do not exist yet (if module is freshly installed), handle gracefully
$totalStudents = 0;
$totalInvoices = 0;
$totalPayments = 0;
$recentStudents = [];
$recentPayments = [];

// Check tables and get stats
$chk = DB_query("SHOW TABLES LIKE 'saris_students'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM saris_students");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalStudents = (int)$row['count'];
    }
    $res = DB_query("SELECT * FROM saris_students ORDER BY created_at DESC LIMIT 5");
    if (DB_error_no() == 0 && $res) {
        while ($row = DB_fetch_assoc($res)) {
            $recentStudents[] = $row;
        }
    }
}

$chk = DB_query("SHOW TABLES LIKE 'saris_invoices'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM saris_invoices");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalInvoices = (int)$row['count'];
    }
}

$chk = DB_query("SHOW TABLES LIKE 'saris_payments'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM saris_payments");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalPayments = (int)$row['count'];
    }
    $res = DB_query("SELECT * FROM saris_payments ORDER BY payment_date DESC LIMIT 5");
    if (DB_error_no() == 0 && $res) {
        while ($row = DB_fetch_assoc($res)) {
            $recentPayments[] = $row;
        }
    }
}
?>

<style>
.db-action-btn-row:hover span {
    color: var(--text-inverse) !important;
}
</style>

<div class="db-page">
    <!-- Header -->
    <div class="db-page-header">
        <div>
            <h2 class="db-page-title"><?= __('SARIS Integration') ?></h2>
            <p class="db-page-subtitle"><?= date('l, d F Y') ?> &mdash; <?= __('External System Synchronization Dashboard') ?></p>
        </div>
        <div class="db-header-actions">
            <a href="<?= $RootPath ?>/SARIS_Settings.php" class="db-btn db-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <?= __('Settings') ?>
            </a>
            <a href="<?= $RootPath ?>/saris_cron.php" class="db-btn db-btn-secondary" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.13 15.57a9 9 0 1 0 3.1-10.15L2.5 8"></path></svg>
                <?= __('Manual Sync') ?>
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="db-kpi-row">
        <div class="db-kpi-card">
            <div class="db-kpi-icon db-icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-label"><?= __('Total Students') ?></span>
                <span class="db-kpi-value"><?= number_format($totalStudents) ?></span>
                <span class="db-kpi-trend db-trend-neutral"><?= __('Synced Records') ?></span>
            </div>
        </div>
        <div class="db-kpi-card">
            <div class="db-kpi-icon db-icon-warn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-label"><?= __('Total Invoices') ?></span>
                <span class="db-kpi-value"><?= number_format($totalInvoices) ?></span>
                <span class="db-kpi-trend db-trend-neutral"><?= __('Synced Records') ?></span>
            </div>
        </div>
        <div class="db-kpi-card">
            <div class="db-kpi-icon db-icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h10M7 11h10M7 7h10"/></svg>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-label"><?= __('Total Payments') ?></span>
                <span class="db-kpi-value"><?= number_format($totalPayments) ?></span>
                <span class="db-kpi-trend db-trend-neutral"><?= __('Synced Records') ?></span>
            </div>
        </div>
    </div>

    <!-- Layout -->
    <div class="db-row-2col">
        <!-- Quick Links (Sidebar equivalent) -->
        <div class="db-card p-0" style="background: transparent; box-shadow: none;">
            <div style="display: grid; gap: 16px;">
                <div class="db-card">
                    <h3 class="db-card-title"><?= __('Module Navigation') ?></h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
                        <a href="<?= $RootPath ?>/SARIS_Students.php" class="db-action-btn-row">
                            <span class="db-action-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                            <div style="display: flex; flex-direction: column;">
                                <span class="db-action-label"><?= __('Students') ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= __('View & manage student records') ?></span>
                            </div>
                        </a>
                        <a href="<?= $RootPath ?>/SARIS_Invoices.php" class="db-action-btn-row">
                            <span class="db-action-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span>
                            <div style="display: flex; flex-direction: column;">
                                <span class="db-action-label"><?= __('Invoices') ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= __('Review synchronized billing') ?></span>
                            </div>
                        </a>
                        <a href="<?= $RootPath ?>/SARIS_Payments.php" class="db-action-btn-row">
                            <span class="db-action-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h10M7 11h10M7 7h10"/></svg></span>
                            <div style="display: flex; flex-direction: column;">
                                <span class="db-action-label"><?= __('Payments') ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= __('Review synchronized transactions') ?></span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="db-card">
                    <h3 class="db-card-title"><?= __('Recent Students') ?></h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-4); margin-top: 16px;">
                        <?php if (empty($recentStudents)): ?>
                            <p class="db-empty" style="text-align: center; color: var(--text-muted); margin: 20px 0;"><?= __('No students found') ?></p>
                        <?php else: ?>
                            <?php foreach ($recentStudents as $student): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-soft); padding-bottom: 8px;">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600; font-size: 0.85rem;"><?= htmlspecialchars($student['name']) ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($student['reg_number']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <div class="db-card">
            <h3 class="db-card-title"><?= __('Recent Sync Payments') ?></h3>
            <div class="db-table-wrapper" style="margin-top: 16px;">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th><?= __('Receipt') ?></th>
                            <th><?= __('Student') ?></th>
                            <th><?= __('Amount') ?></th>
                            <th><?= __('Date') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);"><?= __('No recent payments found') ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($payment['receipt_number']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($payment['student_name']) ?></td>
                                <td style="font-weight: 700;">TZS <?= number_format($payment['amount'], 2) ?></td>
                                <td style="color: var(--text-muted);"><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 16px; text-align: center;">
                <a href="<?= $RootPath ?>/SARIS_Payments.php" class="db-link"><?= __('View all payments') ?> →</a>
            </div>
        </div>
    </div>
</div>

