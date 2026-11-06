<?php

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('SARIS API Configuration');
include(__DIR__ . '/includes/SARISIntegration.php');

// ── AJAX: Test Connection ─────────────────────────────────────────────────────
// Must run before header.php so we can return JSON without HTML pollution.
if (isset($_GET['ajax']) && $_GET['ajax'] === 'test') {
	header('Content-Type: application/json');

	// Use values POSTed from the form (not yet saved) so the admin can test
	// before committing.  Fall back to the DB-saved value when field is empty.
	$baseUrl      = !empty($_POST['saris_base_url'])
		? rtrim(trim($_POST['saris_base_url']), '/')
		: rtrim(getSarisSetting('saris_base_url', ''), '/');
	$tokenPath    = !empty($_POST['saris_token_endpoint'])
		? trim($_POST['saris_token_endpoint'])
		: getSarisSetting('saris_token_endpoint', '/api/v1/login');
	$clientId     = !empty($_POST['saris_client_id'])
		? trim($_POST['saris_client_id'])
		: getSarisSetting('saris_client_id', '');
	$clientSecret = !empty($_POST['saris_client_secret'])
		? trim($_POST['saris_client_secret'])
		: getSarisSetting('saris_client_secret', '');

	if ($baseUrl === '' || $clientId === '' || $clientSecret === '') {
		echo json_encode(['success' => false, 'message' => __('Base URL, Client ID, and Client Secret are required.')]);
		exit();
	}

	$url     = $baseUrl . $tokenPath;
	$attempts = [
		[
			'label' => 'oauth-form-with-scope',
			'body' => http_build_query([
				'client_id'     => $clientId,
				'client_secret' => $clientSecret,
				'grant_type'    => 'client_credentials',
				'scope'         => 'SARIS',
			]),
			'headers' => [
				'Content-Type: application/x-www-form-urlencoded',
				'Accept: application/json',
			],
		],
		[
			'label' => 'oauth-json-with-scope',
			'body' => json_encode([
				'client_id'     => $clientId,
				'client_secret' => $clientSecret,
				'grant_type'    => 'client_credentials',
				'scope'         => 'SARIS',
			]),
			'headers' => [
				'Content-Type: application/json',
				'Accept: application/json',
			],
		],
		[
			'label' => 'oauth-form-no-scope',
			'body' => http_build_query([
				'client_id'     => $clientId,
				'client_secret' => $clientSecret,
				'grant_type'    => 'client_credentials',
			]),
			'headers' => [
				'Content-Type: application/x-www-form-urlencoded',
				'Accept: application/json',
			],
		],
	];
	$result = false;
	$curlErr = '';
	$httpCode = 0;
	$lastVariant = '';
	foreach ($attempts as $attempt) {
		$lastVariant = $attempt['label'];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $attempt['body']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $attempt['headers']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$result   = curl_exec($ch);
		$curlErr  = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($result !== false && $httpCode >= 200 && $httpCode < 300) {
			break;
		}
	}

	if ($result === false) {
		echo json_encode(['success' => false, 'message' => __('Connection failed') . ': ' . $curlErr]);
		exit();
	}
	$response = json_decode($result, true);
	$bodySnippet = trim(preg_replace('/\s+/', ' ', substr((string)$result, 0, 240)));
	if (!is_array($response)) {
		$msg = __('Server returned an invalid response') . ' (HTTP ' . $httpCode . ', ' . $lastVariant . ')';
		if ($bodySnippet !== '') {
			$msg .= ': ' . $bodySnippet;
		}
		echo json_encode(['success' => false, 'message' => $msg]);
		exit();
	}
	if (!empty($response['success']) && !empty($response['results']['access_token'])) {
		$expiresIn = isset($response['results']['expires_in']) ? (int)$response['results']['expires_in'] : 3600;
		echo json_encode(['success' => true, 'message' => __('Connection successful.') . ' ' . __('Token valid for') . ' ' . $expiresIn . ' ' . __('seconds') . '.']);
	} else {
		$msg = isset($response['message']) ? $response['message'] : __('Authentication failed') . ' (HTTP ' . $httpCode . ', ' . $lastVariant . ')';
		if ($bodySnippet !== '') {
			$msg .= ': ' . $bodySnippet;
		}
		echo json_encode(['success' => false, 'message' => $msg]);
	}
	exit();
}

// ── Page setup ────────────────────────────────────────────────────────────────
include(__DIR__ . '/includes/header.php');

$apiFields = [
	'saris_base_url' => [
		'label'       => __('Base URL'),
		'type'        => 'url',
		'placeholder' => 'https://saris.iae.ac.tz',
		'help'        => __('Root URL of the SARIS-IAE API server. Must begin with https://'),
	],
	'saris_token_endpoint' => [
		'label'       => __('Token Endpoint'),
		'type'        => 'text',
		'placeholder' => '/api/v1/login',
		'help'        => __('Path for OAuth2 token acquisition (POST client_credentials)'),
	],
	'saris_student_endpoint' => [
		'label'       => __('Student Endpoint'),
		'type'        => 'text',
		'placeholder' => '/api/v1/students',
		'help'        => __('Path for the students resource'),
	],
	'saris_invoice_endpoint' => [
		'label'       => __('Invoice Endpoint'),
		'type'        => 'text',
		'placeholder' => '/api/v1/invoices',
		'help'        => __('Path for the invoices resource'),
	],
	'saris_payment_endpoint' => [
		'label'       => __('Payment Endpoint'),
		'type'        => 'text',
		'placeholder' => '/api/v1/payments',
		'help'        => __('Path for the payments resource'),
	],
	'saris_client_id' => [
		'label'       => __('Client ID'),
		'type'        => 'text',
		'placeholder' => 'CLIENT',
		'help'        => __('OAuth2 client identifier provided by SARIS-IAE'),
	],
	'saris_client_secret' => [
		'label'       => __('Client Secret'),
		'type'        => 'password',
		'placeholder' => __('Enter client secret'),
		'help'        => __('OAuth2 client secret. Leave blank to keep the current saved value.'),
	],
];

// ── Handle Save ───────────────────────────────────────────────────────────────
$saved  = false;
$errors = [];

if (isset($_POST['SaveAPISettings'])) {
	// Validate URL fields
	$baseUrlVal = isset($_POST['saris_base_url']) ? trim($_POST['saris_base_url']) : '';
	if ($baseUrlVal !== '' && strpos($baseUrlVal, 'https://') !== 0) {
		$errors[] = __('Base URL must begin with https://');
	}

	if (empty($errors)) {
		try {
			foreach ($apiFields as $key => $meta) {
				$value = isset($_POST[$key]) ? trim($_POST[$key]) : '';
				// Never overwrite the secret with a blank submission
				if ($key === 'saris_client_secret' && $value === '') {
					continue;
				}
				saris_execute(
					'INSERT INTO saris_api_settings (setting_key, setting_value)
					VALUES (?, ?)
					ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
					[$key, $value]
				);
			}
			$saved = true;
		} catch (Exception $e) {
			$errors[] = __('Database error') . ': ' . $e->getMessage()
				. ' — ' . __('Run the migration (Z_UpgradeDatabase.php) to create the settings table first.');
		}
	}
}

// ── Load current values from DB ───────────────────────────────────────────────
$current = [];
foreach (array_keys($apiFields) as $key) {
	$current[$key] = getSarisSetting($key, '');
}

// ── Render ────────────────────────────────────────────────────────────────────
echo '<div class="db-page">';
echo '<div class="db-page-header">'
	. '<h1 class="db-page-title">' . __('SARIS Integration') . '</h1>'
	. '<p class="db-page-subtitle">' . __('API Configuration — SARIS-IAE REST API v1.0') . '</p>'
	. '</div>';
saris_render_tabs('API Configuration');

if (!empty($errors)) {
	echo '<div style="background:#fef2f2;border-left:4px solid #dc2626;color:#7f1d1d;padding:12px 16px;border-radius:4px;margin-bottom:16px;">';
	echo '<strong>' . __('Please fix the following errors:') . '</strong><ul style="margin:8px 0 0 16px;">';
	foreach ($errors as $err) {
		echo '<li>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '</li>';
	}
	echo '</ul></div>';
}
if ($saved) {
	prnMsg(__('SARIS API settings saved successfully.'), 'success');
}

echo '<style>
.saris-api-layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; margin-top: 24px; }
.saris-api-form-container { flex: 1 1 450px; }
.saris-api-guide-container { flex: 0 0 350px; }
@media (max-width: 850px) {
	.saris-api-guide-container { flex: 1 1 100%; }
}
.modern-group { margin-bottom: 18px; }
.modern-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
.modern-input { display: block; width: 100%; padding: 8px 12px; font-size: 0.85rem; color: var(--text-main); background: var(--bg-body); border: 1px solid var(--border-color-medium, var(--border-color)); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; font-family: var(--font-sans); }
.modern-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft, rgba(0,0,0,0.05)); outline: none; }
.modern-help { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; margin-bottom: 0; line-height: 1.4; }
.guide-card { background: var(--bg-soft, #f8fafc); border: 1px solid var(--border-color); border-radius: 8px; padding: 24px; }
.guide-endpoint { background: var(--bg-card, #ffffff); border: 1px solid var(--border-color); padding: 12px; border-radius: 6px; margin-bottom: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.guide-endpoint-title { font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 4px; display: block; }
.guide-endpoint-code { font-family: var(--font-mono, monospace); font-size: 0.75rem; color: var(--primary); word-break: break-all; }
</style>';

echo '<div class="saris-api-layout">';

// FORM CONTAINER
echo '<div class="saris-api-form-container">';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="saris-api-form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<div class="db-card">';
echo '<div class="db-card-body" style="padding: 24px;">';

foreach ($apiFields as $key => $meta) {
	$val = htmlspecialchars($current[$key], ENT_QUOTES, 'UTF-8');
	echo '<div class="modern-group">';
	echo '<label class="modern-label" for="' . $key . '">'
		. htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8')
		. '</label>';

	if ($meta['type'] === 'password') {
		echo '<div style="position:relative;">'
			. '<input type="password" id="' . $key . '" name="' . $key . '"'
			. ' class="modern-input" autocomplete="off"'
			. ' placeholder="' . htmlspecialchars($meta['placeholder'], ENT_QUOTES, 'UTF-8') . '"'
			. ' style="padding-right:40px;" />'
			. '<button type="button"'
			. ' onclick="sarisApiToggleSecret(this)"'
			. ' title="' . __('Toggle visibility') . '"'
			. ' style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;opacity:0.55;font-size:1rem;padding:4px;">👁️</button>'
			. '</div>';
	} else {
		echo '<input type="text" id="' . $key . '" name="' . $key . '"'
			. ' class="modern-input" value="' . $val . '"'
			. ' placeholder="' . htmlspecialchars($meta['placeholder'], ENT_QUOTES, 'UTF-8') . '" />';
	}

	echo '<p class="modern-help">'
		. htmlspecialchars($meta['help'], ENT_QUOTES, 'UTF-8')
		. '</p>';
	echo '</div>';
}

echo '</div>';// db-card-body
echo '<div class="db-card-footer" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:16px 24px;background:var(--bg-soft);border-top:1px solid var(--border-color);">';
echo '<button class="db-btn db-btn-primary" type="submit" name="SaveAPISettings" style="padding:8px 20px;">' . __('Save Settings') . '</button>';
echo '<button class="db-btn db-btn-secondary" type="button" id="saris-api-test-btn" style="padding:8px 20px;">' . __('Test Connection') . '</button>';
echo '<span id="saris-api-test-result" style="font-size:0.85rem;font-weight:500;"></span>';
echo '</div>';
echo '</div>';// db-card
echo '</form>';
echo '</div>';// end form container

// GUIDE CONTAINER
echo '<div class="saris-api-guide-container">';
echo '<div class="guide-card">'
	. '<h3 style="margin-top:0;font-size:1.05rem;color:var(--text-heading);margin-bottom:16px;">' . __('How URLs are constructed') . '</h3>'
	. '<p style="font-size:0.85rem;color:var(--text-muted);line-height:1.6;margin-top:0;margin-bottom:16px;">' . __('Each resource URL is built dynamically at runtime by combining your configured Base URL and its specific Endpoint path:') . '</p>'
	
	. '<div class="guide-endpoint" style="background:var(--primary-soft); border-color:var(--primary-soft);">'
	. '<span class="guide-endpoint-title" style="color:var(--primary);">' . __('Format') . '</span>'
	. '<span class="guide-endpoint-code" style="color:var(--primary); font-weight:600;">{Base URL}{Endpoint}</span>'
	. '</div>'

	. '<div class="guide-endpoint">'
	. '<span class="guide-endpoint-title">' . __('Students') . '</span>'
	. '<span class="guide-endpoint-code">{Base URL}{Student Endpoint}</span>'
	. '</div>'
	
	. '<div class="guide-endpoint">'
	. '<span class="guide-endpoint-title">' . __('Invoices') . '</span>'
	. '<span class="guide-endpoint-code">{Base URL}{Invoice Endpoint}</span>'
	. '</div>'

	. '<div class="guide-endpoint">'
	. '<span class="guide-endpoint-title">' . __('Payments') . '</span>'
	. '<span class="guide-endpoint-code">{Base URL}{Payment Endpoint}</span>'
	. '</div>'

	. '<div class="guide-endpoint">'
	. '<span class="guide-endpoint-title">' . __('Token') . '</span>'
	. '<span class="guide-endpoint-code">{Base URL}{Token Endpoint}</span>'
	. '</div>'

	. '</div>';
echo '</div>'; // end guide container

echo '</div>';// layout

echo '</div>';// db-page

echo '<script>
function sarisApiToggleSecret(btn) {
	var inp = btn.previousElementSibling;
	if (inp.type === "password") {
		inp.type = "text";
		btn.textContent = "🙈";
	} else {
		inp.type = "password";
		btn.textContent = "👁️";
	}
}

document.getElementById("saris-api-test-btn").addEventListener("click", function() {
	var btn    = this;
	var result = document.getElementById("saris-api-test-result");
	var form   = document.getElementById("saris-api-form");

	btn.disabled    = true;
	btn.textContent = "' . __('Testing...') . '";
	result.textContent = "";
	result.style.color = "";

	var data = new FormData(form);
	fetch("' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ajax=test", {
		method: "POST",
		headers: {"X-Requested-With": "XMLHttpRequest"},
		body: data
	})
	.then(function(r) { return r.json(); })
	.then(function(json) {
		result.textContent = json.message;
		result.style.color = json.success ? "#16a34a" : "#dc2626";
	})
	.catch(function(e) {
		result.textContent = "' . __('Test failed') . ': " + e.message;
		result.style.color = "#dc2626";
	})
	.finally(function() {
		btn.disabled    = false;
		btn.textContent = "' . __('Test Connection') . '";
	});
});
</script>';

include(__DIR__ . '/includes/footer.php');
