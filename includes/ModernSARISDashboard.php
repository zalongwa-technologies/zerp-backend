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
$chk = DB_query("SHOW TABLES LIKE 'students'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM students");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalStudents = (int)$row['count'];
    }
    $res = DB_query("SELECT student_regnumber, student_fullname FROM students ORDER BY created_at DESC LIMIT 5");
    if (DB_error_no() == 0 && $res) {
        while ($row = DB_fetch_assoc($res)) {
            $recentStudents[] = $row;
        }
    }
}

$chk = DB_query("SHOW TABLES LIKE 'invoices'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM invoices");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalInvoices = (int)$row['count'];
    }
}

$chk = DB_query("SHOW TABLES LIKE 'payments'");
if (DB_num_rows($chk) > 0) {
    $res = DB_query("SELECT COUNT(*) as count FROM payments");
    if (DB_error_no() == 0 && $res) {
        $row = DB_fetch_assoc($res);
        $totalPayments = (int)$row['count'];
    }
    $res = DB_query("SELECT p.payment_receipt_number, COALESCE(s.student_fullname, p.student_name, p.student_regnumber) as student_name, p.payment_amount, p.payment_date 
                     FROM payments p
                     LEFT JOIN students s ON p.student_regnumber = s.student_regnumber
                     ORDER BY p.payment_date DESC LIMIT 5");
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
            <button type="button" class="db-btn db-btn-secondary" id="dashManualSyncBtn" onclick="runManualSync(event, this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.13 15.57a9 9 0 1 0 3.1-10.15L2.5 8"></path></svg>
                <?= __('Manual Sync') ?>
            </button>
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
                                    <span style="font-weight: 600; font-size: 0.85rem;"><?= htmlspecialchars($student['student_fullname']) ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($student['student_regnumber']) ?></span>
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
                                <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars((string)$payment['payment_receipt_number']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars((string)$payment['student_name']) ?></td>
                                <td style="font-weight: 700;">TZS <?= number_format($payment['payment_amount'], 2) ?></td>
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

<!-- Modern Modal for Sync Results -->
<div id="syncResultModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--surface-color, #fff); width: 90%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; animation: modalPop 0.3s ease-out forwards;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color, #e5e7eb); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-color, #111827); display: flex; align-items: center; gap: 8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?= __('Sync Completed') ?>
            </h3>
        </div>
        <div style="padding: 24px; color: var(--text-muted, #4b5563); font-size: 0.95rem; line-height: 1.6;" id="syncResultBody">
            <!-- Content injected via JS -->
        </div>
        <div style="padding: 16px 24px; background: var(--bg-color, #f9fafb); border-top: 1px solid var(--border-color, #e5e7eb); text-align: right;">
            <button type="button" class="db-btn db-btn-primary" onclick="closeSyncModal()">
                <?= __('Close & Refresh') ?>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
function runManualSync(event, btn) {
    if (event) event.preventDefault();
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="4.93" x2="19.07" y2="7.76"></line></svg> Syncing...';
    btn.disabled = true;
    
    fetch('<?= $RootPath ?>/saris_cron.php')
    .then(res => res.text())
    .then(text => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        // Show modern modal
        document.getElementById('syncResultBody').innerText = text.trim();
        document.getElementById('syncResultModal').style.display = 'flex';
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        document.getElementById('syncResultBody').innerText = 'An error occurred during sync. Please try again.';
        document.getElementById('syncResultBody').style.color = '#ef4444';
        document.getElementById('syncResultModal').style.display = 'flex';
    });
}

function closeSyncModal() {
    document.getElementById('syncResultModal').style.display = 'none';
    window.location.reload();
}
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
