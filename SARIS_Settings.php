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

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="db-card" style="max-width:760px;"><div class="db-card-body">';
echo '<div class="db-form-group"><label class="db-form-label">' . __('Sync Mode') . '</label><select class="db-form-select" name="SyncMode" id="SyncMode">';
echo '<option value="manual"' . ($settings['sync_mode'] === 'manual' ? ' selected="selected"' : '') . '>' . __('Manual') . '</option>';
echo '<option value="automatic"' . ($settings['sync_mode'] === 'automatic' ? ' selected="selected"' : '') . '>' . __('Automatic') . '</option>';
echo '</select></div>';
echo '<div class="db-form-group" id="SyncIntervalWrap"><label class="db-form-label">' . __('Sync Interval') . '</label><select class="db-form-select" name="SyncInterval">';
$intervals = ['10min' => __('10 min'), '30min' => __('30 min'), '1hr' => __('1 hr'), '1day' => __('1 day')];
foreach ($intervals as $value => $label) {
	echo '<option value="' . $value . '"' . ($settings['sync_interval'] === $value ? ' selected="selected"' : '') . '>' . $label . '</option>';
}
echo '</select></div>';
echo '</div><div class="db-card-footer"><button class="db-btn db-btn-primary" type="submit" name="SaveSettings">' . __('Save') . '</button></div></div>';
echo '</form>';

echo '<div class="db-card" style="max-width:760px;margin-top:24px;"><div class="db-card-header"><h3 class="db-card-title">' . __('Current Settings') . '</h3></div><div class="db-table-wrapper">';
echo '<table class="db-table"><thead><tr><th>' . __('Sync Mode') . '</th><th>' . __('Sync Interval') . '</th><th>' . __('Updated At') . '</th></tr></thead><tbody><tr>';
echo '<td>' . htmlspecialchars(ucfirst($settings['sync_mode']), ENT_QUOTES, 'UTF-8') . '</td>';
echo '<td>' . htmlspecialchars($settings['sync_interval'] ?: '-', ENT_QUOTES, 'UTF-8') . '</td>';
echo '<td>' . htmlspecialchars($settings['updated_at'] ?: '-', ENT_QUOTES, 'UTF-8') . '</td>';
echo '</tr></tbody></table></div></div>';

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
