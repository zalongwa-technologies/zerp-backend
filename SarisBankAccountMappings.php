<?php
/*
 * SARIS Bank Account Mappings
 * Allows dynamic mapping of SARIS payment sources or fee types to specific ZERP Bank Accounts.
 */

include('includes/session.php');
$Title = _('SARIS Bank Account Mappings');
$ViewTopic = 'SARIS';
$BookMark = 'SarisBankMappings';

include('includes/header.php');

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

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/bank.png" title="' . _('SARIS Bank Mappings') . '" alt="" />' . ' ' . $Title . '</p>';

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

// Display existing mappings
$sql = "SELECT m.id, m.match_keyword, m.bank_account_code, m.description, b.bankaccountname 
        FROM saris_bank_mappings m
        LEFT JOIN bankaccounts b ON m.bank_account_code = b.accountcode
        ORDER BY m.match_keyword";
$result = DB_query($sql, $db);

echo '<table class="selection">';
echo '<tr>
        <th>' . _('Match Keyword') . '</th>
        <th>' . _('Bank Account') . '</th>
        <th>' . _('Description') . '</th>
        <th colspan="2"></th>
    </tr>';

$k = 0; //row colour counter
while ($myrow = DB_fetch_array($result)) {
    if ($k == 1) {
        echo '<tr class="EvenTableRows">';
        $k = 0;
    } else {
        echo '<tr class="OddTableRows">';
        $k = 1;
    }
    
    printf('<td>%s</td>
            <td>%s - %s</td>
            <td>%s</td>
            <td><a href="%s&amp;SelectedMappingID=%s">' . _('Edit') . '</a></td>
            <td><a href="%s&amp;delete=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this mapping?') . '\');">' . _('Delete') . '</a></td>
            </tr>',
            $myrow['match_keyword'],
            $myrow['bank_account_code'],
            $myrow['bankaccountname'],
            $myrow['description'],
            htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
            $myrow['id'],
            htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
            $myrow['id']);
}
echo '</table><br />';

// Fetch bank accounts for the dropdown
$bankSql = "SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname";
$bankResult = DB_query($bankSql, $db);

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

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!empty($SelectedMappingID)) {
    echo '<input type="hidden" name="SelectedMappingID" value="' . $SelectedMappingID . '" />';
}

echo '<table class="selection">';
echo '<tr><th colspan="2">' . _('Mapping Details') . '</th></tr>';

echo '<tr><td>' . _('Match Keyword (e.g. GePG, CRDB, Tuition)') . ':</td>
        <td><input type="text" required="required" autofocus="autofocus" name="MatchKeyword" size="30" maxlength="100" value="' . $MatchKeyword . '" /></td></tr>';

echo '<tr><td>' . _('Target Bank Account') . ':</td>
        <td><select required="required" name="BankAccountCode">';
        
while ($bankRow = DB_fetch_array($bankResult)) {
    if ($BankAccountCode == $bankRow['accountcode']) {
        echo '<option selected="selected" value="' . $bankRow['accountcode'] . '">' . $bankRow['accountcode'] . ' - ' . $bankRow['bankaccountname'] . '</option>';
    } else {
        echo '<option value="' . $bankRow['accountcode'] . '">' . $bankRow['accountcode'] . ' - ' . $bankRow['bankaccountname'] . '</option>';
    }
}
echo '</select></td></tr>';

echo '<tr><td>' . _('Description/Notes') . ':</td>
        <td><input type="text" name="Description" size="50" maxlength="255" value="' . $Description . '" /></td></tr>';

echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '" /></div>';
echo '</div>';
echo '</form>';

include('includes/footer.php');
?>
