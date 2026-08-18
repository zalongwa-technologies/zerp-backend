<?php
/*
 * SARIS Bank Account Mappings
 * Allows dynamic mapping of SARIS payment sources or fee types to specific ZERP Bank Accounts.
 */

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = _('SARIS Integration - Bank Mappings');
include(__DIR__ . '/includes/SARISIntegration.php');

// Create the mapping table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS saris_bank_mappings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    match_keyword VARCHAR(100) NOT NULL,
    bank_account_code VARCHAR(50) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY match_keyword (match_keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
DB_query($sql, $db);

// Handle form submission for adding/updating
if (isset($_POST['submit'])) {
    $InputError = 0;
    
    $MatchKeyword = trim($_POST['MatchKeyword']);
    $BankAccountCode = $_POST['BankAccountCode'];
    $Description = trim($_POST['Description']);
    
    if (empty($MatchKeyword)) {
        prnMsg(_('The match keyword must not be empty.'), 'error');
        $InputError = 1;
    }
    
    if ($InputError == 0) {
        if (isset($_POST['SelectedMappingID']) && !empty($_POST['SelectedMappingID'])) {
            $sql = "UPDATE saris_bank_mappings SET 
                        match_keyword = '" . DB_escape_string($MatchKeyword) . "',
                        bank_account_code = '" . DB_escape_string($BankAccountCode) . "',
                        description = '" . DB_escape_string($Description) . "'
                    WHERE id = '" . intval($_POST['SelectedMappingID']) . "'";
            $ErrMsg = _('The SARIS bank mapping could not be updated because');
            $DbgMsg = _('The SQL that was used to update the mapping but failed was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            prnMsg(_('The SARIS bank mapping has been updated.'), 'success');
        } else {
            $sql = "INSERT INTO saris_bank_mappings (match_keyword, bank_account_code, description)
                    VALUES (
                        '" . DB_escape_string($MatchKeyword) . "',
                        '" . DB_escape_string($BankAccountCode) . "',
                        '" . DB_escape_string($Description) . "'
                    )";
            $ErrMsg = _('The SARIS bank mapping could not be added because');
            $DbgMsg = _('The SQL that was used to insert the mapping but failed was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
            prnMsg(_('The new SARIS bank mapping has been added.'), 'success');
        }
        unset($_POST['MatchKeyword']);
        unset($_POST['BankAccountCode']);
        unset($_POST['Description']);
        unset($_POST['SelectedMappingID']);
    }
} elseif (isset($_GET['delete'])) {
    $sql = "DELETE FROM saris_bank_mappings WHERE id = '" . intval($_GET['delete']) . "'";
    $ErrMsg = _('The SARIS bank mapping could not be deleted because');
    $result = DB_query($sql, $db, $ErrMsg);
    prnMsg(_('The SARIS bank mapping has been deleted.'), 'success');
}

include(__DIR__ . '/includes/header.php');

echo '<div class="db-page-header">';
echo '<h1 class="db-page-title">' . _('SARIS Bank Account Mappings') . '</h1>';
echo '<p class="db-page-subtitle">' . _('Map SARIS payment sources or fee types to specific ZERP Bank Accounts.') . '</p>';
echo '</div>';

saris_render_tabs('Bank Mappings', 'SARIS Bank Mappings', '');

// Form logic
$SelectedMappingID = '';
$MatchKeyword = '';
$BankAccountCode = '';
$Description = '';

if (isset($_GET['SelectedMappingID'])) {
    $SelectedMappingID = intval($_GET['SelectedMappingID']);
    $sql = "SELECT * FROM saris_bank_mappings WHERE id = '" . $SelectedMappingID . "'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $MatchKeyword = $myrow['match_keyword'];
    $BankAccountCode = $myrow['bank_account_code'];
    $Description = $myrow['description'];
} elseif (isset($_POST['MatchKeyword'])) {
    $MatchKeyword = $_POST['MatchKeyword'];
    $BankAccountCode = $_POST['BankAccountCode'];
    $Description = $_POST['Description'];
}

$bankSql = "SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname";
$bankResult = DB_query($bankSql, $db);

echo '<div style="display:grid;grid-template-columns:1fr 3fr;gap:24px;margin-bottom:24px;">';

// Form Card
echo '<div class="db-card" style="align-self: start;">';
echo '<h2 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#374151;">' . (!empty($SelectedMappingID) ? _('Edit Mapping') : _('Add New Mapping')) . '</h2>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!empty($SelectedMappingID)) {
    echo '<input type="hidden" name="SelectedMappingID" value="' . $SelectedMappingID . '" />';
}

echo '<div class="db-form-group">';
echo '<label class="db-label">' . _('Match Keyword (e.g. GePG, CRDB, Tuition)') . '</label>';
echo '<input type="text" class="db-input" required="required" autofocus="autofocus" name="MatchKeyword" maxlength="100" value="' . htmlspecialchars($MatchKeyword, ENT_QUOTES, 'UTF-8') . '" />';
echo '</div>';

echo '<div class="db-form-group">';
echo '<label class="db-label">' . _('Target Bank Account') . '</label>';
echo '<select required="required" class="db-input" name="BankAccountCode">';
while ($bankRow = DB_fetch_array($bankResult)) {
    $selected = ($BankAccountCode == $bankRow['accountcode']) ? 'selected="selected"' : '';
    echo '<option value="' . htmlspecialchars($bankRow['accountcode'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($bankRow['accountcode'] . ' - ' . $bankRow['bankaccountname'], ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select>';
echo '</div>';

echo '<div class="db-form-group">';
echo '<label class="db-label">' . _('Description/Notes') . '</label>';
echo '<input type="text" class="db-input" name="Description" maxlength="255" value="' . htmlspecialchars($Description, ENT_QUOTES, 'UTF-8') . '" />';
echo '</div>';

echo '<div style="margin-top:24px;">';
echo '<button type="submit" name="submit" class="db-btn db-btn-primary" style="width:100%;">' . _('Save Mapping') . '</button>';
if (!empty($SelectedMappingID)) {
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" class="db-btn db-btn-secondary" style="width:100%;margin-top:8px;text-align:center;">' . _('Cancel') . '</a>';
}
echo '</div>';

echo '</form>';
echo '</div>';

// Table Card
echo '<div class="db-card">';
echo '<div class="saris-table-wrapper">';
echo '<table class="saris-table">';
echo '<thead>
        <tr>
            <th>' . _('Match Keyword') . '</th>
            <th>' . _('Bank Account') . '</th>
            <th>' . _('Description') . '</th>
            <th style="width:100px;">' . _('Actions') . '</th>
        </tr>
      </thead>
      <tbody>';

$sql = "SELECT m.id, m.match_keyword, m.bank_account_code, m.description, b.bankaccountname 
        FROM saris_bank_mappings m
        LEFT JOIN bankaccounts b ON m.bank_account_code = b.accountcode
        ORDER BY m.match_keyword";
$result = DB_query($sql, $db);

if (DB_num_rows($result) == 0) {
    echo '<tr><td colspan="4" style="text-align:center;padding:40px;color:#6b7280;">' . _('No bank mappings found.') . '</td></tr>';
} else {
    while ($myrow = DB_fetch_array($result)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($myrow['match_keyword'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($myrow['bank_account_code'] . ' - ' . $myrow['bankaccountname'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($myrow['description'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>
                <div style="display:flex;gap:8px;">
                    <a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedMappingID=' . $myrow['id'] . '" class="db-btn db-btn-secondary" style="padding:4px 8px;font-size:12px;">' . _('Edit') . '</a>
                    <a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=' . $myrow['id'] . '" class="db-btn db-btn-secondary" style="padding:4px 8px;font-size:12px;color:#dc2626;border-color:#fca5a5;" onclick="return confirm(\'' . _('Are you sure you wish to delete this mapping?') . '\');">' . _('Delete') . '</a>
                </div>
              </td>';
        echo '</tr>';
    }
}

echo '</tbody></table>';
echo '</div>'; // end saris-table-wrapper
echo '</div>'; // end table db-card

echo '</div>'; // end grid

include('includes/footer.php');
?>
