<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Tax Groups');
$ViewTopic = 'Tax';
$BookMark = 'TaxGroups';
include(__DIR__ . '/includes/header.php');

// Inject premium Architect styles
echo '<style>
    :root {
        --primary: hsl(145, 63%, 38%);
        --primary-hover: hsl(145, 63%, 32%);
        --primary-dark: hsl(145, 45%, 22%);
        --primary-light: hsl(145, 40%, 95%);
        --primary-glow: hsla(145, 63%, 38%, 0.15);
        --page-padding: 30px;
        --border-color: #e5e7eb;
        --radius: 12px;
        --text-main: #111827;
        --text-muted: #6b7280;
    }
    .db-page {
        padding: var(--page-padding);
        max-width: 1400px;
        margin: 0 auto;
        font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    /* Header Section */
    .premium-header { 
        margin-bottom: 24px; 
        padding: 24px; 
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        gap: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .premium-header-title h1 {
        font-size: 1.75rem; 
        font-weight: 800; 
        color: var(--text-main); 
        margin: 0; 
        letter-spacing: -0.5px;
        line-height: 1.2;
        background: transparent !important;
        text-shadow: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }
    .premium-header-title .breadcrumbs {
        font-size: 0.75rem; 
        font-weight: 700; 
        color: var(--primary); 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        margin-bottom: 8px; 
        display: flex; 
        align-items: center; 
        gap: 8px;
    }

    /* Layout */
    .db-bottom-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        align-items: flex-start;
        padding-bottom: 50px;
    }
    .db-col-aside {
        flex: 1 1 350px;
        min-width: 320px;
        max-width: 450px;
    }
    .db-col-main {
        flex: 2 1 600px;
        min-width: 0; /* Prevents flex children from overflowing */
    }

    /* Cards */
    .arch-card { 
        background: #ffffff; 
        border-radius: var(--radius); 
        border: 1px solid var(--border-color); 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
    }
    .arch-card-header { 
        background: #f9fafb; 
        border-bottom: 1px solid var(--border-color); 
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    .arch-card-title {
        font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin:0;
        display: flex; align-items: center; gap: 10px;
    }

    /* Buttons */
    .arch-btn {
        display: inline-flex; align-items: center; gap: 8px; justify-content: center;
        padding: 10px 20px; border-radius: 8px;
        background: var(--primary); color: #ffffff; border: none;
        font-weight: 600; font-size: 0.9rem; cursor: pointer;
        transition: all 0.2s ease; text-decoration: none;
    }
    .arch-btn:hover { background: var(--primary-hover); transform: translateY(-1px); }
    .arch-btn-secondary { background: #ffffff; color: var(--text-main); border: 1px solid var(--border-color); }
    .arch-btn-secondary:hover { background: #f3f4f6; }
    .arch-btn-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .arch-btn-danger:hover { background: #fecaca; }

    /* Currency/Tax List */
    .list-container {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }
    .list-item {
        padding: 16px 24px; border-bottom: 1px solid var(--border-color); 
        transition: all 0.2s; cursor: pointer; display: flex; align-items: center; gap: 16px; 
        text-decoration: none; color: inherit; background: #ffffff;
    }
    .list-item:last-child { border-bottom: none; }
    .list-item:hover { background: #f9fafb; }
    .list-item.active { background: var(--primary-light); border-left: 4px solid var(--primary); padding-left: 20px; }
    
    .arch-badge { padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
    .arch-badge-success { background: var(--primary-light); color: var(--primary-dark); }
    .arch-badge-neutral { background: #f3f4f6; color: #4b5563; }

    /* Form Grid */
    .arch-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        padding: 0;
    }
    .arch-form-field { display: flex; flex-direction: column; gap: 8px; }
    .arch-form-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
    .arch-form-input { 
        width: 100%; height: 44px; border-radius: 8px; border: 1px solid var(--border-color); 
        padding: 0 16px; font-size: 0.95rem; transition: all 0.2s; color: var(--text-main); background: #ffffff;
        box-sizing: border-box;
    }
    .arch-form-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px var(--primary-glow); }

    /* Tables */
    .arch-table { width: 100%; border-collapse: collapse; }
    .arch-table th { background: #f9fafb; color: var(--text-muted); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); padding: 16px 20px; text-align: left; }
    .arch-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
    .arch-table tr:last-child td { border-bottom: none; }

    .section-divider {
        margin: 40px 0 24px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-main);
    }
    .section-title {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    @media (max-width: 992px) {
        .db-bottom-layout { flex-direction: column; }
        .db-col-aside, .db-col-main { width: 100%; max-width: none; }
        .db-col-aside { order: 2; }
        .db-col-main { order: 1; }
        .premium-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .list-container { max-height: 400px; }
    }
</style>';

echo '<div class="db-page">
		<header class="premium-header">
			<div class="premium-header-title">
                <div class="breadcrumbs">
                    <i class="fas fa-percent"></i> ' . __('Tax Setup') . ' <i class="fas fa-chevron-right" style="font-size: 0.6rem; opacity: 0.5;"></i> ' . __('Groups') . '
                </div>
                <h1>' . $Title . '</h1>
            </div>
            <div>
                <a href="' . $RootPath . '/SelectOrderItems.php" class="arch-btn arch-btn-secondary">
                    <i class="fas fa-arrow-left"></i> ' . __('Back to Orders') . '
                </a>
            </div>
		</header>';

if (isset($_GET['SelectedGroup'])) {
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif (isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
}

// Logic Handling
if (isset($_POST['submit']) OR isset($_GET['remove']) OR isset($_GET['add']) ) {
	$InputError = 0;
	if (isset($_POST['GroupName']) AND mb_strlen($_POST['GroupName'])<4) {
		$InputError = 1;
		prnMsg(__('The Group description entered must be at least 4 characters long'),'error');
	}
	unset($SQL);
	if (isset($_POST['GroupName']) ) {
		if (isset($SelectedGroup)) {
			$SQL = "UPDATE taxgroups SET taxgroupdescription = '". $_POST['GroupName'] ."' WHERE taxgroupid = '".$SelectedGroup . "'";
			$SuccessMsg = __('Tax group name updated.');
		} else {
			$Result = DB_query("SELECT taxgroupid FROM taxgroups WHERE taxgroupdescription='" . $_POST['GroupName'] . "'");
			if (DB_num_rows($Result)==1) {
				prnMsg( __('A tax group already exists for this name'),'warn');
			} else {
				$SQL = "INSERT INTO taxgroups (taxgroupdescription) VALUES ('". $_POST['GroupName'] . "')";
				$SuccessMsg = __('New tax group created.');
			}
		}
		unset($_POST['GroupName']);
		unset($SelectedGroup);
	} elseif (isset($SelectedGroup) ) {
		$TaxAuthority = $_GET['TaxAuthority'];
		if ( isset($_GET['add']) ) {
			$SQL = "INSERT INTO taxgrouptaxes ( taxgroupid, taxauthid, calculationorder) VALUES ('" . $SelectedGroup . "', '" . $TaxAuthority . "', 0)";
			$SuccessMsg = __('Tax assigned.');
		} elseif ( isset($_GET['remove']) ) {
			$SQL = "DELETE FROM taxgrouptaxes WHERE taxgroupid = '".$SelectedGroup."' AND taxauthid = '".$TaxAuthority . "'";
			$SuccessMsg = __('Tax removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['TaxAuthority']);
	}
	if (isset($SQL) AND $InputError != 1 ) {
		DB_query($SQL);
		prnMsg( $SuccessMsg,'success');
	}
} elseif (isset($_POST['UpdateOrder'])) {
	$SQL = "SELECT taxauthid FROM taxgrouptaxes WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($SQL);
	while ($r=DB_fetch_row($Result)) {
		if (is_numeric($_POST['CalcOrder_' . $r[0]]) AND $_POST['CalcOrder_' . $r[0]] < 10) {
			$SQL = "UPDATE taxgrouptaxes SET calculationorder='" . $_POST['CalcOrder_' . $r[0]] . "', taxontax='" . $_POST['TaxOnTax_' . $r[0]] . "' WHERE taxgroupid='" . $SelectedGroup . "' AND taxauthid='" . $r[0] . "'";
			DB_query($SQL);
		}
	}
    prnMsg(__('Calculation orders updated'),'success');
} elseif (isset($_GET['Delete'])) {
	$SQL= "SELECT COUNT(*) FROM custbranch WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg( __('Cannot delete this tax group because some customer branches are setup using it'),'warn');
	} else {
		DB_query("DELETE FROM taxgrouptaxes WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'");
		DB_query("DELETE FROM taxgroups WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'");
		prnMsg(__('Tax group deleted'),'success');
        unset($SelectedGroup);
	}
}

echo '<div class="db-bottom-layout">
        <aside class="db-col-aside">
            <div class="arch-card" style="position: sticky; top: 100px;">
                <div class="arch-card-header">
                    <h3 class="arch-card-title"><i class="fas fa-list-ul"></i> ' . __('Tax Groups') . '</h3>
                </div>
                <div class="db-card-body" style="padding:0; max-height: calc(100vh - 250px); overflow-y: auto;">';

    $SQL = "SELECT taxgroupid, taxgroupdescription FROM taxgroups ORDER BY taxgroupid";
    $Result = DB_query($SQL);
    while ($MyRow = DB_fetch_array($Result)) {
        $isActive = (isset($SelectedGroup) && $SelectedGroup == $MyRow['taxgroupid']) ? 'active' : '';
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedGroup=' . $MyRow['taxgroupid'] . '" class="list-item ' . $isActive . '">
                <div style="width:32px; height:32px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; border-radius:8px; font-weight:800; font-size:0.75rem;">' . $MyRow['taxgroupid'] . '</div>
                <div style="flex:1;"><div style="font-weight: 800; font-size: 0.85rem; color:#111827;">' . $MyRow['taxgroupdescription'] . '</div></div>
                <i class="fas fa-chevron-right" style="color:#9ca3af; font-size:0.7rem;"></i>
              </a>';
    }

    echo '      </div>
                <div style="padding: 20px; background: #f9fafb; border-top: 1px solid #f3f4f6;">
                    <a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" class="arch-btn arch-btn-secondary" style="width:100%; justify-content:center;">
                        <i class="fas fa-plus"></i> ' . __('Add New Group') . '
                    </a>
                </div>
            </div>
            
            <div class="arch-card">
                <div class="arch-card-header"><h3 class="arch-card-title"><i class="fas fa-tools"></i> ' . __('Setup Hub') . '</h3></div>
                <div class="db-card-body" style="padding: 10px 0;">
                    <a href="' . $RootPath . '/TaxAuthorities.php" class="list-item" style="border:none;"><i class="fas fa-building" style="color:#6366f1;"></i> <span style="font-weight:600; font-size:0.85rem;">' . __('Tax Authorities') . '</span></a>
                    <a href="' . $RootPath . '/TaxProvinces.php" class="list-item" style="border:none;"><i class="fas fa-map-location-dot" style="color:#f59e0b;"></i> <span style="font-weight:600; font-size:0.85rem;">' . __('Tax Provinces') . '</span></a>
                </div>
            </div>
        </aside>

        <main class="db-col-main">';

    if (isset($SelectedGroup)) {
        $r = DB_fetch_array(DB_query("SELECT taxgroupid, taxgroupdescription FROM taxgroups WHERE taxgroupid='" . $SelectedGroup . "'"));
        $_POST['GroupName'] = $r['taxgroupdescription'];
        $formTitle = __('Group Master Dashboard');
        $formSubtitle = __('Complete configuration for') . ' ' . $_POST['GroupName'];
    } else {
        if (!isset($_POST['GroupName'])) $_POST['GroupName'] = '';
        $formTitle = __('Group Registration');
        $formSubtitle = __('Define a new taxing group collection');
    }

    echo '<div class="arch-card">
            <div class="arch-card-header">
                <div>
                    <h3 class="arch-card-title"><i class="fas fa-layer-group" style="color:var(--primary);"></i> ' . $formTitle . '</h3>
                    <div style="font-size: 0.75rem; color: #6b7280; font-weight:600; margin-top:5px;">' . $formSubtitle . '</div>
                </div>';
    
    if (isset($SelectedGroup)) {
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedGroup=' . $SelectedGroup . '&amp;Delete=1" class="arch-btn" style="background:#fee2e2; color:#dc2626;" onclick="return confirm(\'' . __('Delete this group?') . '\');">
                <i class="fas fa-trash-alt"></i>
              </a>';
    }

    echo '  </div>
            <div class="db-card-body" style="padding:40px;">';

    // Partition 1: Identity Form
    echo '      <form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
                <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
                if (isset($SelectedGroup)) echo '<input type="hidden" name="SelectedGroup" value="' . $SelectedGroup . '" />';
    
    echo '      <div class="section-divider" style="margin-top:0;">
                    <i class="fas fa-fingerprint"></i> <span class="section-title">' . __('Group Identity') . '</span>
                </div>
                <div class="arch-form-grid" style="grid-template-columns: 1fr;">
                    <div class="arch-form-field">
                        <label class="arch-form-label">' . __('Tax Group Description') . '</label>
                        <div style="display:flex; gap:16px;">
                            <input type="text" name="GroupName" class="arch-form-input" required maxlength="40" value="' . $_POST['GroupName'] . '" placeholder="' . __('e.g. Standard Regional Sales Tax') . '" style="flex:1;" />
                            <button type="submit" name="submit" class="arch-btn" style="height:48px;">
                                <i class="fas fa-save"></i> ' . (isset($SelectedGroup) ? __('Update') : __('Create')) . '
                            </button>
                        </div>
                    </div>
                </div>
                </form>';

    if (isset($SelectedGroup)) {
        // Partition 2: Calculation Order Table
        $UsedResult = DB_query("SELECT taxauthid, description AS taxname, calculationorder, taxontax FROM taxgrouptaxes INNER JOIN taxauthorities ON taxgrouptaxes.taxauthid=taxauthorities.taxid WHERE taxgroupid='". $SelectedGroup . "' ORDER BY calculationorder");
        $TaxAuthRows = array();
        while($r = DB_fetch_array($UsedResult)) $TaxAuthRows[] = $r;

        if (count($TaxAuthRows) > 0) {
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
                    <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
                    <input type="hidden" name="SelectedGroup" value="' . $SelectedGroup .'" />
                    
                    <div class="section-divider" style="margin-top:50px;">
                        <i class="fas fa-sort-numeric-down"></i> <span class="section-title">' . __('Calculation Hierarchy Rules') . '</span>
                    </div>
                    
                    <table class="arch-table" style="width:100%; margin-bottom:20px;">
                        <thead>
                            <tr>
                                <th>' . __('Tax Jurisdiction') . '</th>
                                <th style="width:120px;">' . __('Order') . '</th>
                                <th>' . __('Tax on Prior Taxes') . '</th>
                            </tr>
                        </thead>
                        <tbody>';
            foreach($TaxAuthRows as $r) {
                echo '<tr>
                        <td><div style="color:var(--primary-dark); font-weight:800;">' . $r['taxname'] . '</div></td>
                        <td><input type="number" class="arch-form-input" style="height:38px; text-align:center;" name="CalcOrder_' . $r['taxauthid'] . '" value="' . $r['calculationorder'] . '" min="1" max="9" /></td>
                        <td>
                            <select name="TaxOnTax_' . $r['taxauthid'] . '" class="arch-form-input" style="height:38px;">
                                <option value="1" ' . ($r['taxontax'] == 1 ? 'selected' : '') . '>' . __('Yes') . '</option>
                                <option value="0" ' . ($r['taxontax'] == 0 ? 'selected' : '') . '>' . __('No') . '</option>
                            </select>
                        </td>
                    </tr>';
            }
            echo '      </tbody>
                    </table>
                    <div style="display:flex; justify-content:center;">
                        <button type="submit" name="UpdateOrder" class="arch-btn arch-btn-secondary" style="background:var(--primary-light); border:1px solid #d1fae5;">
                            <i class="fas fa-check-circle"></i> ' . __('Apply Hierarchy Changes') . '
                        </button>
                    </div>
                  </form>';
        }

        // Partition 3: Authority Allocation Registry
        $AllResult = DB_query("SELECT taxid, description as taxname FROM taxauthorities ORDER BY taxid");
        $UsedIDs = array_column($TaxAuthRows, 'taxauthid');

        echo '  <div class="section-divider" style="margin-top:50px;">
                    <i class="fas fa-link"></i> <span class="section-title">' . __('Authority Allocation Registry') . '</span>
                </div>
                <table class="arch-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:150px;">' . __('Status') . '</th>
                            <th>' . __('Tax Authority') . '</th>
                            <th class="text-center">' . __('Action') . '</th>
                        </tr>
                    </thead>
                    <tbody>';
        while($r = DB_fetch_array($AllResult)) {
            $isUsed = in_array($r['taxid'], $UsedIDs);
            echo '<tr>
                    <td>' . ($isUsed ? '<span class="arch-badge arch-badge-success">' . __('Assigned') . '</span>' : '<span class="arch-badge arch-badge-neutral">' . __('Available') . '</span>') . '</td>
                    <td><div style="font-weight:750;">' . $r['taxname'] . '</div><div style="font-size:0.7rem; color:#9ca3af; font-weight:600;">ID: ' . $r['taxid'] . '</div></td>
                    <td class="text-center">';
            if ($isUsed) {
                echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedGroup=' . $SelectedGroup . '&amp;remove=1&amp;TaxAuthority=' . $r['taxid'] . '" class="arch-btn arch-btn-secondary" style="padding:6px 12px; color:#dc2626; font-size:0.75rem; background:transparent; border:1px solid #fee2e2;">
                        <i class="fas fa-minus-circle"></i> ' . __('Remove') . '
                      </a>';
            } else {
                echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedGroup=' . $SelectedGroup . '&amp;add=1&amp;TaxAuthority=' . $r['taxid'] . '" class="arch-btn" style="padding:6px 12px; font-size:0.75rem;">
                        <i class="fas fa-plus-circle"></i> ' . __('Assign') . '
                      </a>';
            }
            echo '	</td>
                </tr>';
        }
        echo '      </tbody>
                </table>';
    } else {
        echo '<div style="padding: 60px; text-align: center; color: #065f46; border: 2px dashed #d1fae5; border-radius: 12px; background: #f0fdf4; margin-top:20px;">
                <i class="fas fa-hand-pointer" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3 style="font-weight: 850; margin-bottom: 10px;">Select a group to start configuring</h3>
                <p style="font-size: 0.9rem; font-weight: 600; color: #059669;">Choose a tax group from the sidebar to manage identity, hierarchy, and authority assignments.</p>
              </div>';
    }

    echo '  </div>
          </div>';

    echo '</main></div>'; // End Layout
echo '</div>'; // End Page

include(__DIR__ . '/includes/footer.php');
