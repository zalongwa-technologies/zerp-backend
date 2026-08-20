<?php

require(__DIR__ . '/includes/session.php');

$PricesSecurity = 12; // don't show pricing info unless security token 12 available to user

$Title = __('Stock Status');
$ViewTopic = 'Inventory';
$BookMark = '';

include(__DIR__ . '/includes/header.php');

if (isset($_GET['modal']) || isset($_POST['modal'])) {
    echo '<style>
        .premium-header, header.noPrint, nav.ModuleList, .ScriptTitle, .aw-breadcrumb { display: none !important; }
        .MainBody { padding: 0 !important; gap: 0 !important; background: transparent !important; }
        .db-page, .aw-page { padding: var(--space-6) var(--space-4) !important; background: var(--bg-main) !important; min-height: 100vh !important; }
        .dashboard-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    </style>';
}

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/StockFunctions.php');

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if ($StockID != '') {
	$Res = DB_query("SELECT description, units, decimalplaces, mbflag, serialised, controlled FROM stockmaster WHERE stockid='" . $StockID . "'");
	if (DB_num_rows($Res) > 0) {
		$MyRow = DB_fetch_array($Res);
		$Description = $MyRow['description'];
		$Units = $MyRow['units'];
		$DecimalPlaces = $MyRow['decimalplaces'];
		$KitSet = $MyRow['mbflag'];
		$Serialised = $MyRow['serialised'];
		$Controlled = $MyRow['controlled'];
	}
}

?>
<style>
    :root {
        --primary: hsl(145, 63%, 38%);
        --primary-hover: hsl(145, 63%, 32%);
        --primary-dark: hsl(145, 45%, 22%);
        --primary-soft: hsl(145, 40%, 95%);
        --bg: hsl(210, 20%, 97%);
        --white: #ffffff;
        --border: #e2e8f0;
        --border-soft: #f1f5f9;
        --text-main: #334155;
        --text-muted: #64748b;
        --shadow: 0 1px 3px rgba(0,0,0,0.1);
        --radius: 12px;
        --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
    }
    body { background-color: var(--bg); color: var(--text-main); font-family: var(--font-sans); }
    .aw-page { max-width: 1400px; margin: 0 auto; padding: 2rem; }
    .aw-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; }
    .aw-breadcrumb { font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 0.5rem; }
    .aw-title { font-size: 2rem; font-weight: 900; color: var(--primary-dark); margin: 0; line-height: 1; }
    .aw-layout-grid { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; align-items: start; }
    @media (max-width: 1024px) { .aw-layout-grid { grid-template-columns: 1fr; } }
    .aw-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border-soft); overflow: hidden; margin-bottom: 1.5rem; overflow: hidden; }
    .aw-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-soft); background: var(--white); display: flex; align-items: center; gap: 0.75rem; }
    .aw-card-title { font-size: 1rem; font-weight: 700; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .aw-card-title i { color: var(--primary); font-size: 1.1rem; }
    .aw-card-body { padding: 1.25rem; }
    .aw-field-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.25rem; }
    .aw-label { font-size: 0.8rem; font-weight: 700; color: var(--primary-dark); }
    .aw-input { width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid var(--border); background: var(--white); font-size: 0.9rem; box-sizing: border-box; }
    .aw-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; border: none; text-decoration: none; }
    .aw-btn-primary { background: var(--primary); color: #ffffff !important; }
    .aw-btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
    .aw-btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
    .aw-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .aw-table th { background: var(--primary-soft); color: var(--primary-dark); font-weight: 800; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; text-align: left; border-bottom: 2px solid var(--border-soft); }
    .aw-table td { padding: 1rem; border-bottom: 1px solid var(--border-soft); }
    .aw-table tr:hover { background: var(--bg); }
    .aw-table .number { text-align: right; font-family: 'JetBrains Mono', monospace; }
    .aw-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; background: var(--primary-soft); color: var(--primary); }
</style>

<div class="aw-page">
    <style>
        .premium-status-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin-bottom: 2rem;
        }
        .status-header {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .status-title-area h1 {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary-dark);
            margin: 0 0 0.75rem 0;
            line-height: 1.2;
        }
        .status-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .status-footer {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .aw-table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .aw-table td { border-bottom: 1px solid #f1f5f9; }
    </style>

    <div class="premium-status-container">
        <?php if ($StockID != ''): ?>
            <!-- Header for specific item -->
            <div class="status-header">
                <div class="status-title-area">
                    <div class="aw-breadcrumb hide-in-modal" style="margin-bottom: 0.5rem; font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">
                        <?php echo __('Inventory'); ?> / <?php echo __('Stock Status'); ?>
                    </div>
                    <h1><span style="color: var(--text-muted); font-size: 1.2rem;"><?php echo $StockID; ?></span> - <?php echo $Description ?? ''; ?></h1>
                    
                    <div class="status-badges">
                        <span class="aw-badge" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-balance-scale"></i> <?php echo $Units ?? ''; ?></span>
                        <span class="aw-badge" style="background: #f1f5f9; color: #475569;"><i class="fas fa-hashtag"></i> Precision: <?php echo $DecimalPlaces ?? ''; ?></span>
                    </div>
                </div>
                
                <div class="hide-in-modal">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>" style="display: flex; gap: 0.5rem;">
                        <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
                        <input type="text" name="StockID" class="aw-input" placeholder="<?php echo __('Stock Code'); ?>" required style="max-width: 180px;" />
                        <button type="submit" class="aw-btn aw-btn-primary" style="padding: 0.6rem 1rem;"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <!-- Table Body -->
            <div style="padding: 0;">
                <?php
                $SQL = "SELECT locstock.loccode, locations.locationname, locstock.quantity, locstock.reorderlevel, locstock.bin, locations.managed, canupd FROM locstock INNER JOIN locations ON locstock.loccode=locations.loccode INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1 WHERE locstock.stockid = '" . $StockID . "' ORDER BY locations.locationname";
                $Res = DB_query($SQL);

                if (DB_num_rows($Res) > 0) {
                    require_once(__DIR__ . '/includes/UIComponents.php');
                    
                    $columns = [
                        __('Location'),
                        __('Bin'),
                        __('On Hand'),
                        __('Demand'),
                        __('Available'),
                        __('On Order')
                    ];
                    
                    $dataRows = [];
                    while ($row = DB_fetch_array($Res)) {
                        $DemandQty = GetDemand($StockID, $row['loccode']);
                        $QOO = GetQuantityOnOrder($StockID, $row['loccode']);
                        $Avail = $row['quantity'] - $DemandQty;
                        
                        $dataRows[] = [
                            '<span style="font-weight: 700; color: var(--primary);">' . htmlspecialchars($row['locationname']) . '</span>',
                            '<span style="font-size: 0.8rem; color: var(--text-muted);">' . htmlspecialchars($row['bin'] ?: '-') . '</span>',
                            '<span style="font-family: \'JetBrains Mono\', monospace; font-weight: 700;">' . locale_number_format($row['quantity'], $DecimalPlaces) . '</span>',
                            '<span style="font-family: \'JetBrains Mono\', monospace; color: #ef4444;">' . locale_number_format($DemandQty, $DecimalPlaces) . '</span>',
                            '<span style="font-family: \'JetBrains Mono\', monospace; font-weight: 700; color: var(--primary);">' . locale_number_format($Avail, $DecimalPlaces) . '</span>',
                            '<span style="font-family: \'JetBrains Mono\', monospace;">' . locale_number_format($QOO, $DecimalPlaces) . '</span>'
                        ];
                    }
                    
                    // Render the UI table component directly inside the padding-0 div
                    echo '<div style="margin: 0; border: none; box-shadow: none;">';
                    render_modern_table($columns, $dataRows, false, ['emptyMessage' => __('No stock records found for this item.')]);
                    echo '</div>';
                } else {
                    echo '<div style="padding: 4rem; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-warehouse fa-3x" style="color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <p>' . __('No stock records found for this item.') . '</p>
                    </div>';
                }
                ?>
            </div>

            <!-- Footer Quick Insights -->
            <?php include(__DIR__ . '/includes/ItemQuickActions.php'); ?>

        <?php else: ?>
            <!-- No item selected state -->
            <div class="status-header">
                <div class="status-title-area">
                    <h1><?php echo __('Item Lookup'); ?></h1>
                </div>
            </div>
            <div style="padding: 4rem 2rem; text-align: center;">
                <i class="fas fa-search fa-3x" style="color: #cbd5e1; margin-bottom: 1.5rem;"></i>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>" style="max-width: 400px; margin: 0 auto;">
                    <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
                    <div class="aw-field-group text-left" style="text-align: left;">
                        <label class="aw-label"><?php echo __('Stock Code'); ?></label>
                        <input type="text" name="StockID" class="aw-input" required autofocus />
                    </div>
                    <button type="submit" class="aw-btn aw-btn-primary w-100" style="width: 100%;"><i class="fas fa-search"></i> <?php echo __('Show Status'); ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
