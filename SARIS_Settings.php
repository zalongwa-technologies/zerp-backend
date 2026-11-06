<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS Integration - Settings');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/SARISIntegration.php');

if (isset($_POST['SaveSettings'])) {
	$syncMode = isset($_POST['SyncMode']) ? $_POST['SyncMode'] : 'manual';
	$syncInterval = isset($_POST['SyncInterval']) ? $_POST['SyncInterval'] : null;
	saris_save_settings($syncMode, $syncInterval);
	prnMsg(__('SARIS settings saved successfully.'), 'success');
}

$settings = saris_get_settings();

echo '<div class="db-page">';
echo '<div class="db-page-header"><h1 class="db-page-title">' . __('SARIS Integration') . '</h1><p class="db-page-subtitle">' . __('External ERP synchronisation settings') . '</p></div>';
saris_render_tabs('Settings');

echo '<style>
.saris-api-layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; margin-top: 24px; }
.saris-api-form-container { flex: 1 1 450px; }
.saris-api-current-container { flex: 0 0 350px; }
@media (max-width: 850px) {
	.saris-api-current-container { flex: 1 1 100%; }
}
.modern-group { margin-bottom: 18px; }
.modern-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main, #333); margin-bottom: 6px; }
.modern-select { display: block; width: 100%; padding: 8px 12px; font-size: 0.9rem; color: var(--text-main, #333); background: var(--bg-card, #fff); border: 1px solid var(--border-color-medium, #ccc); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; font-family: var(--font-sans, sans-serif); cursor: pointer; }
.modern-select:focus { border-color: var(--primary, #0ea5e9); box-shadow: 0 0 0 3px var(--primary-soft, rgba(14,165,233,0.15)); outline: none; }
.current-settings-card { background: var(--bg-soft, #f8fafc); border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px; padding: 24px; }
.current-setting-item { background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e5e7eb); padding: 12px; border-radius: 6px; margin-bottom: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.current-setting-title { font-size: 0.85rem; font-weight: 600; color: var(--text-main, #333); margin-bottom: 4px; display: block; }
.current-setting-value { font-family: var(--font-mono, monospace); font-size: 0.8rem; color: var(--primary, #0ea5e9); word-break: break-all; font-weight: 600; }
</style>';

echo '<div class="saris-api-layout">';

// FORM CONTAINER
echo '<div class="saris-api-form-container">';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="db-card"><div class="db-card-body" style="padding: 24px;">';

echo '<div class="modern-group"><label class="modern-label">' . __('Sync Mode') . '</label><select class="modern-select" name="SyncMode" id="SyncMode">';
echo '<option value="manual"' . ($settings['sync_mode'] === 'manual' ? ' selected="selected"' : '') . '>' . __('Manual') . '</option>';
echo '<option value="automatic"' . ($settings['sync_mode'] === 'automatic' ? ' selected="selected"' : '') . '>' . __('Automatic') . '</option>';
echo '</select></div>';

echo '<div class="modern-group" id="SyncIntervalWrap"><label class="modern-label">' . __('Sync Interval') . '</label><select class="modern-select" name="SyncInterval">';
$intervals = ['10min' => __('10 min'), '30min' => __('30 min'), '1hr' => __('1 hr'), '1day' => __('1 day')];
foreach ($intervals as $value => $label) {
	echo '<option value="' . $value . '"' . ($settings['sync_interval'] === $value ? ' selected="selected"' : '') . '>' . $label . '</option>';
}
echo '</select></div>';

echo '</div><div class="db-card-footer" style="padding:16px 24px;background:var(--bg-soft);border-top:1px solid var(--border-color);"><button class="db-btn db-btn-primary" type="submit" name="SaveSettings" style="padding:8px 20px;">' . __('Save Settings') . '</button></div></div>';
echo '</form>';
echo '</div>'; // end form container

// CURRENT SETTINGS CONTAINER
echo '<div class="saris-api-current-container">';
echo '<div class="current-settings-card">';
echo '<h3 style="margin-top:0;font-size:1.05rem;color:var(--text-heading);margin-bottom:16px;">' . __('Current Settings') . '</h3>';

echo '<div class="current-setting-item">';
echo '<span class="current-setting-title">' . __('Sync Mode') . '</span>';
echo '<span class="current-setting-value">' . htmlspecialchars(ucfirst($settings['sync_mode']), ENT_QUOTES, 'UTF-8') . '</span>';
echo '</div>';

echo '<div class="current-setting-item">';
echo '<span class="current-setting-title">' . __('Sync Interval') . '</span>';
echo '<span class="current-setting-value">' . htmlspecialchars($settings['sync_interval'] ?: '-', ENT_QUOTES, 'UTF-8') . '</span>';
echo '</div>';

echo '<div class="current-setting-item">';
echo '<span class="current-setting-title">' . __('Updated At') . '</span>';
echo '<span class="current-setting-value">' . htmlspecialchars($settings['updated_at'] ?: '-', ENT_QUOTES, 'UTF-8') . '</span>';
echo '</div>';

echo '</div>'; // end card
echo '</div>'; // end current container

echo '</div>'; // end layout

echo '<script>
function sarisToggleInterval() {
	var mode = document.getElementById("SyncMode").value;
	document.getElementById("SyncIntervalWrap").style.display = mode === "automatic" ? "block" : "none";
}
document.getElementById("SyncMode").addEventListener("change", sarisToggleInterval);
sarisToggleInterval();
</script>';
echo '</div>';

include(__DIR__ . '/includes/footer.php');
?>
