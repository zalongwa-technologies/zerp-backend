<?php
$PricesSecurity = 12; //don't show pricing info unless security token 12 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user
$CostSecurity = 18; //don't show cost info unless security token 18 available to user

require(__DIR__ . '/includes/session.php');

$Title = __('Inventory Item Movements');
$ViewTopic = 'Inventory';
$BookMark = 'InventoryMovement';
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/UIComponents.php');

if (isset($_GET['StockID'])) {
    $StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
    $StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
    $StockID = '';
}

if (!isset($_POST['BeforeDate'])) {
    $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate'])) {
    $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 3, Date('d'), Date('y')));
}

$ModalParam = (isset($_GET['modal']) || isset($_POST['modal'])) ? '&modal=1' : '';

?>
<style>
    /* Same Premium Container CSS from StockStatus and SelectProduct */
    .premium-status-container {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        max-width: 1200px;
        margin: 2rem auto;
        overflow: hidden;
        border: 1px solid var(--border-soft); overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .status-header {
        background: var(--surface-alt);
        color: var(--text-main);
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1.5rem;
        border-bottom: 1px solid var(--border-soft);
    }

    .status-title h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--primary-dark);
    }

    .status-badges {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .aw-badge {
        background: var(--primary-soft);
        border: 1px solid var(--primary-soft);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--primary);
    }

    .filter-bar {
        background: var(--surface);
        padding: 1rem 2rem;
        border-bottom: 1px solid var(--border-soft);
    }

    .filter-form {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--primary-dark);
        text-transform: uppercase;
    }

    .filter-group input, .filter-group select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.875rem;
        background: var(--surface);
        color: var(--text-main);
        min-width: 150px;
    }

    .status-footer {
        background: var(--surface-alt);
        border-top: 1px solid var(--border-soft);
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .aw-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    
    .aw-btn-primary { background: var(--primary); color: #ffffff !important; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); }
    .aw-btn-primary:hover { background: var(--primary-hover); }
    
    .aw-btn-outline { background: var(--surface); color: var(--text-main); border: 1px solid var(--border); }
    .aw-btn-outline:hover { background: var(--surface-alt); color: var(--primary-dark); border-color: var(--primary); }
</style>

<div class="premium-status-container">
    <!-- Header -->
    <div class="status-header">
        <div class="status-title">
            <h1><i class="fas fa-exchange-alt" style="color: var(--primary);"></i> <?php echo __('Stock Movements'); ?></h1>
            <div class="status-badges">
                <?php if ($StockID) { ?>
                    <span class="aw-badge"><?php echo $StockID; ?></span>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Horizontal) -->
    <div class="filter-bar">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" class="filter-form">
            <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
            <?php if (isset($_GET['modal']) || isset($_POST['modal'])) { ?>
                <input type="hidden" name="modal" value="1" />
            <?php } ?>
            
            <div class="filter-group">
                <label><?php echo __('Stock Code'); ?></label>
                <input type="text" name="StockID" value="<?php echo $StockID; ?>" required placeholder="e.g. ITEM-001" />
            </div>
            
            <div class="filter-group">
                <label><?php echo __('Location'); ?></label>
                <select name="StockLocation">
                    <option value="All"><?php echo __('All Locations'); ?></option>
                    <?php
                    $SQL_Loc = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1 ORDER BY locationname";
                    $ResStkLocs = DB_query($SQL_Loc);
                    while ($RowLoc = DB_fetch_array($ResStkLocs)) {
                        echo '<option ' . ((($_POST['StockLocation'] ?? '') == $RowLoc['loccode']) ? 'selected' : '') . ' value="' . $RowLoc['loccode'] . '">' . $RowLoc['locationname'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><?php echo __('From Date'); ?></label>
                <input name="AfterDate" type="date" value="<?php echo FormatDateForSQL($_POST['AfterDate']); ?>" />
            </div>
            
            <div class="filter-group">
                <label><?php echo __('To Date'); ?></label>
                <input name="BeforeDate" type="date" value="<?php echo FormatDateForSQL($_POST['BeforeDate']); ?>" />
            </div>
            
            <button type="submit" name="ShowMoves" class="aw-btn aw-btn-primary" style="padding: 0.6rem 1.5rem;"><i class="fas fa-search"></i> <?php echo __('Search'); ?></button>
        </form>
    </div>

    <!-- Table Body -->
    <div style="padding: 0; background: var(--surface);">
        <?php
        if ($StockID == '' || !isset($_POST['ShowMoves'])) {
            echo '<div style="padding: 6rem 2rem; text-align: center; color: var(--text-muted);">
                <i class="fas fa-exchange-alt fa-4x" style="color: var(--border-soft); margin-bottom: 1.5rem;"></i>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--text-main);">' . __('Ready to Search') . '</h3>
                <p style="margin-top: 0.5rem;">' . __('Enter an item code and adjust filters to view movement history.') . '</p>
            </div>';
        } else {
            $SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
            $SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
            
            $LocFilter = "";
            if (isset($_POST['StockLocation']) && $_POST['StockLocation'] != 'All') {
                $LocFilter = " AND stockmoves.loccode='" . $_POST['StockLocation'] . "'";
            }

            $SQL = "SELECT stockmoves.*, systypes.typename, stockmaster.decimalplaces, stockmaster.controlled, stockmaster.serialised FROM stockmoves INNER JOIN systypes ON stockmoves.type=systypes.typeid INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid WHERE stockmoves.trandate >= '" . $SQLAfterDate . "' AND stockmoves.stockid = '" . $StockID . "' AND stockmoves.trandate <= '" . $SQLBeforeDate . "'" . $LocFilter . " AND hidemovt=0 ORDER BY stkmoveno DESC";
            $Res = DB_query($SQL);

            if (DB_num_rows($Res) > 0) {
                $columns = [
                    __('Type / #'),
                    __('Date'),
                    __('User / Ref'),
                    __('Qty'),
                    __('New QOH'),
                    __('Narrative')
                ];

                $dataRows = [];
                while ($row = DB_fetch_array($Res)) {
                    $qtyColor = $row['qty'] < 0 ? '#ef4444' : '#166534';
                    
                    $dataRows[] = [
                        '<div style="font-weight: 700; color: var(--primary);">' . $row['typename'] . '</div><div style="font-size: 0.75rem; color: var(--text-muted);">#' . $row['transno'] . '</div>',
                        '<span style="white-space: nowrap;">' . ConvertSQLDate($row['trandate']) . '</span>',
                        '<div style="font-weight: 600; font-size: 0.85rem;">' . $row['userid'] . '</div><div style="font-size: 0.75rem; color: var(--text-muted);">' . $row['reference'] . '</div>',
                        '<span style="font-family: \'JetBrains Mono\', monospace; font-weight: 700; color: ' . $qtyColor . ';">' . locale_number_format($row['qty'], $row['decimalplaces']) . '</span>',
                        '<span style="font-family: \'JetBrains Mono\', monospace; font-weight: 700;">' . locale_number_format($row['newqoh'], $row['decimalplaces']) . '</span>',
                        '<span style="font-size: 0.85rem; color: var(--text-muted);">' . $row['narrative'] . '</span>'
                    ];
                }

                echo '<div style="margin: 0; border: none; box-shadow: none;">';
                render_modern_table($columns, $dataRows, false, ['emptyMessage' => __('No movements found for the selected period.')]);
                echo '</div>';
            } else {
                echo '<div style="padding: 4rem; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-folder-open fa-3x" style="color: var(--border-soft); margin-bottom: 1rem;"></i>
                    <p>' . __('No movements found for the selected period.') . '</p>
                </div>';
            }
        }
        ?>
    </div>

    <!-- Footer Quick Insights -->
    <?php include(__DIR__ . '/includes/ItemQuickActions.php'); ?>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
