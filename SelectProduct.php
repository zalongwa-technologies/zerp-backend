<?php

/* Selection of items. All item maintenance, transactions and inquiries start with this script. */

$PricesSecurity = 12; //don't show pricing info unless security token 12 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user
$CostSecurity = 18; //don't show cost info unless security token 18 available to user

require(__DIR__ . '/includes/session.php');

$Title = __('Search Inventory Items');
$ViewTopic = 'Inventory';
$BookMark = 'SelectingInventory';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/StockFunctions.php');
include(__DIR__ . '/includes/ImageFunctions.php');

if (isset($_GET['StockID'])) {
	$_GET['StockID'] = trim(mb_strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(mb_strtoupper($_GET['StockID']));
}

if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(mb_strtoupper($_POST['StockCode']));
}

if (!isset($_POST['StockFilter'])) {
    $_POST['StockFilter'] = 'All';
}

// Auto-trigger search if no item is selected and no search performed yet
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) OR (!isset($_POST['Select']) AND !isset($_SESSION['SelectedStockItem']))) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous']) AND !isset($_POST['Search'])) {
        // Default View on Landing: Automatically show the list
		$_POST['PageOffset'] = 1;
        $_POST['StockCat'] = 'All';
        $_POST['Search'] = 'Search'; // Trigger the search results view
	} elseif (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// Just stay on current page if not a nav action
	}
	$SQL = GenerateStockmasterQuery($_POST);
	$SearchResult = DB_query($SQL);
}

// Always show the search facilities
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result1 = DB_query($SQL);
if (DB_num_rows($Result1) == 0) {
	prnMsg(__('There are no stock categories currently defined'), 'warn');
	
include(__DIR__ . '/includes/footer.php');
	exit();
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
    body { background-color: var(--bg); color: var(--text-main); font-family: var(--font-sans); overflow-x: hidden; }
    .aw-page { max-width: 100%; margin: 0; padding: 1.5rem 2.5%; }
    .aw-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; }
    .aw-breadcrumb { font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 0.25rem; }
    .aw-title { font-size: 1.75rem; font-weight: 900; color: var(--primary-dark); margin: 0; line-height: 1; }
    
    .aw-layout-grid { display: grid; gap: 1.5rem; align-items: start; }
    .aw-layout-search { grid-template-columns: 320px 1fr; }
    .aw-layout-dashboard { grid-template-columns: 1fr 350px; }
    
    @media (max-width: 1024px) { .aw-layout-grid { grid-template-columns: 1fr; } }
    
    .aw-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border-soft); overflow: hidden; margin-bottom: 1rem; overflow: hidden; }
    .aw-card-header { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-soft); background: var(--white); display: flex; align-items: center; gap: 0.75rem; }
    .aw-card-title { font-size: 0.9rem; font-weight: 700; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .aw-card-body { padding: 1rem; }
    
    .aw-field-group { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.75rem; }
    .aw-label { font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); }
    .aw-input, .aw-select { width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: var(--white); font-size: 0.85rem; box-sizing: border-box; }
    
    .aw-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
    .aw-btn-primary { background: var(--primary); color: #ffffff !important; }
    .aw-btn-primary:hover { background: var(--primary-hover); }
    .aw-btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); transition: all 0.2s; }
    .aw-btn-outline:hover { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .aw-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.75rem; border-radius: 6px; }
    
    .aw-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .aw-table th { background: var(--primary-soft); color: var(--primary-dark); font-weight: 800; text-transform: uppercase; font-size: 0.65rem; padding: 0.75rem; text-align: left; position: sticky; top: 0; z-index: 10; }
    .aw-table td { padding: 0.75rem; border-bottom: 1px solid var(--border-soft); }
    .aw-table tr:hover { background: var(--bg); }
    .aw-table .number { text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 600; }
    
    .aw-pagination { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; background: var(--white); border-top: 1px solid var(--border-soft); }
    .aw-page-link { min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: var(--white); border: 1px solid var(--border); color: var(--text-main); font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: all 0.2s; }
    .aw-page-link:hover { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .aw-page-link.active { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .aw-page-link.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
    
    .aw-badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: var(--primary-soft); color: var(--primary); }

    /* Premium Revamp Styles */
    .premium-filter-bar {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-radius: var(--radius);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .premium-filter-bar .aw-field-group { margin-bottom: 0; flex: 1; min-width: 150px; }
    .premium-filter-bar .aw-btn { height: 38px; align-self: flex-end; }
    
    .premium-table-container {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        border: 1px solid var(--border-soft); overflow: hidden;
        overflow: hidden;
    }
    .aw-table th { background: #f8fafc; color: #475569; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .aw-table tr { transition: all 0.2s ease; }
    .aw-table tr:hover { background: #f1f5f9; transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.02); z-index: 1; position: relative; }
    
    /* Remove old grid layout */
    .aw-layout-search { display: block; }
</style>

<div class="aw-page">

<?php 
if (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {
	// --- DASHBOARD MODE (MASTER-SIDEBAR) ---
	if (isset($_POST['Select'])) {
		$_SESSION['SelectedStockItem'] = $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}

	$Result = DB_query("SELECT stockmaster.*, stockcategory.stocktype, stockcategory.categorydescription
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockid='" . $StockID . "'");
	$MyRow = DB_fetch_array($Result);
	
	$Its_A_Kitset_Assembly_Or_Dummy = in_array($MyRow['mbflag'], ['A', 'G', 'K', 'D']);
?>
    <style>
        .premium-profile-container {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .profile-banner {
            height: 120px;
            background: linear-gradient(135deg, hsl(145, 63%, 38%) 0%, hsl(145, 63%, 28%) 100%);
            position: relative;
        }

        .profile-content {
            padding: 0 2.5rem 2.5rem 2.5rem;
            position: relative;
        }

        .profile-image-wrapper {
            position: relative;
            margin-top: -60px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .profile-image {
            width: 140px;
            height: 140px;
            background: #ffffff;
            border-radius: 16px;
            border: 4px solid #ffffff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #f8fafc;
        }

        .profile-header-text {
            flex: 1;
            padding-left: 2rem;
            padding-top: 1rem;
        }

        .stock-id-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            border: 1px solid #e2e8f0;
        }

        .profile-title {
            font-size: 2rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 1rem 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .profile-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .profile-badges .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            color: #475569;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid #e2e8f0;
        }

        .profile-badges .badge-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .profile-badges .badge-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .metric-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .metric-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .metric-content {
            flex: 1;
        }

        .metric-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
        }

        .specs-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .specs-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #334155;
            margin: 0 0 1.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .spec-item {
            background: #f8fafc;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .spec-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }

        .spec-value {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
        }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .profile-header-text { padding-left: 0; padding-top: 1.5rem; }
            .profile-image-wrapper { flex-direction: column; align-items: flex-start; }
            .profile-content { padding: 0 1.5rem 1.5rem 1.5rem; }
        }
    </style>

    <?php 
    $QOH = ($Its_A_Kitset_Assembly_Or_Dummy ? 0 : GetQuantityOnHand($StockID, 'ALL'));
    $Demand = GetDemand($StockID, 'ALL');
    ?>

    <div class="premium-profile-container">
        <div class="profile-banner"></div>
        <div class="profile-content">
            
            <div class="profile-image-wrapper">
                <div class="profile-image">
                    <?php 
                    $PicDir = isset($_SESSION['part_pics_dir']) ? $_SESSION['part_pics_dir'] : 'companies/' . $_SESSION['DatabaseName'] . '/part_pics';
                    $PossibleImageFiles = glob($PicDir . '/' . $StockID . '.{png,jpg,jpeg}', GLOB_BRACE);
                    $ImageFile = (count($PossibleImageFiles) > 0 ? $PossibleImageFiles[0] : '');
                    if ($ImageFile) {
                        echo '<img src="' . $ImageFile . '" alt="' . $StockID . '">';
                    } else {
                        echo '<i class="fas fa-box-open" style="font-size: 3.5rem; color: #cbd5e1;"></i>';
                    }
                    ?>
                </div>

                <div class="hide-in-modal" style="margin-bottom: 1rem;">
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?NewSearch=Yes" class="aw-btn aw-btn-outline"><i class="fas fa-search"></i> <?php echo __('New Search'); ?></a>
                </div>
            </div>

            <div class="profile-header-text">
                <div class="stock-id-badge"><i class="fas fa-fingerprint"></i> <?php echo $StockID; ?></div>
                <h1 class="profile-title"><?php echo $MyRow['description']; ?></h1>
                
                <div class="profile-badges">
                    <span class="badge"><i class="fas fa-folder-open"></i> <?php echo $MyRow['categorydescription']; ?></span>
                    <span class="badge"><i class="fas fa-ruler"></i> <?php echo $MyRow['units']; ?></span>
                    <span class="badge <?php echo $QOH > 0 ? 'badge-success' : 'badge-danger'; ?>">
                        <i class="fas <?php echo $QOH > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i> 
                        <?php echo $QOH > 0 ? __('In Stock') : __('Out of Stock'); ?>
                    </span>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="metric-card">
                    <div class="metric-icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-cubes"></i></div>
                    <div class="metric-content">
                        <div class="metric-label"><?php echo __('Quantity On Hand'); ?></div>
                        <div class="metric-value <?php echo $QOH > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo locale_number_format($QOH, $MyRow['decimalplaces']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-chart-line"></i></div>
                    <div class="metric-content">
                        <div class="metric-label"><?php echo __('Demand'); ?></div>
                        <div class="metric-value" style="color: #d97706;">
                            <?php echo locale_number_format($Demand, $MyRow['decimalplaces']); ?>
                        </div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon" style="background: #f1f5f9; color: #475569;"><i class="fas fa-weight-hanging"></i></div>
                    <div class="metric-content">
                        <div class="metric-label"><?php echo __('Gross Weight'); ?></div>
                        <div class="metric-value" style="color: #334155;">
                            <?php echo locale_number_format($MyRow['grossweight'], 3); ?> <span style="font-size: 1rem; color: #94a3b8;">kg</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="specs-container">
                <h3 class="specs-title"><i class="fas fa-list-alt"></i> <?php echo __('Item Specifications'); ?></h3>
                <div class="specs-grid">
                    <div class="spec-item">
                        <span class="spec-label"><?php echo __('Item Type'); ?></span>
                        <span class="spec-value"><?php echo $MyRow['mbflag']; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label"><?php echo __('Volume'); ?></span>
                        <span class="spec-value"><?php echo locale_number_format($MyRow['volume'], 3); ?> m³</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label"><?php echo __('Net Weight'); ?></span>
                        <span class="spec-value"><?php echo locale_number_format($MyRow['netweight'], 3); ?> kg</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label"><?php echo __('Barcode'); ?></span>
                        <span class="spec-value"><?php echo !empty($MyRow['barcode']) ? $MyRow['barcode'] : '-'; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label"><?php echo __('Pack Size'); ?></span>
                        <span class="spec-value"><?php echo $MyRow['pansize']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Footer -->
            <?php include(__DIR__ . '/includes/ItemQuickActions.php'); ?>
        </div>
    </div>

<?php 
} else {
	// --- SEARCH MODE (FILTERS LEFT, RESULTS RIGHT) ---
?>
    <div class="aw-header">
        <div>

            <h1 class="aw-title"><?php echo __('Inventory Hub'); ?></h1>
        </div>
        <div>
            <button type="button" onclick="openModal('Stocks.php', 'Add New Item')" class="aw-btn aw-btn-primary"><i class="fas fa-plus"></i> <?php echo __('Add New Item'); ?></button>
        </div>
    </div>

    <div class="aw-layout-search">
        <!-- TOP: PREMIUM HORIZONTAL FILTERS -->
        <form id="FilterBar" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post" class="premium-filter-bar">
            <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID'] ?? ''; ?>" />
            
            <div class="aw-field-group">
                <label class="aw-label"><?php echo __('Category'); ?></label>
                <select name="StockCat" class="aw-select">
                    <option value="All"><?php echo __('All Categories'); ?></option>
                    <?php
                    DB_data_seek($Result1, 0);
                    while ($cRow = DB_fetch_array($Result1)) {
                        echo '<option ' . (($_POST['StockCat'] ?? '') == $cRow['categoryid'] ? 'selected' : '') . ' value="' . $cRow['categoryid'] . '">' . $cRow['categorydescription'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="aw-field-group">
                <label class="aw-label"><?php echo __('Stock Status'); ?></label>
                <select name="StockFilter" class="aw-select">
                    <option value="All" <?php if ($_POST['StockFilter'] == 'All') echo 'selected'; ?>><?php echo __('All Registered Items'); ?></option>
                    <option value="InStock" <?php if ($_POST['StockFilter'] == 'InStock') echo 'selected'; ?>><?php echo __('In-Stock Only'); ?></option>
                    <option value="OutOfStock" <?php if ($_POST['StockFilter'] == 'OutOfStock') echo 'selected'; ?>><?php echo __('Out-of-Stock Only'); ?></option>
                </select>
            </div>
            
            <div class="aw-field-group">
                <label class="aw-label"><?php echo __('Keywords'); ?></label>
                <input type="text" name="Keywords" class="aw-input" placeholder="<?php echo __('Search names...'); ?>" value="<?php echo $_POST['Keywords'] ?? ''; ?>" />
            </div>
            
            <div class="aw-field-group">
                <label class="aw-label"><?php echo __('Stock Code'); ?></label>
                <input type="text" name="StockCode" class="aw-input" placeholder="<?php echo __('Exact code...'); ?>" value="<?php echo $_POST['StockCode'] ?? ''; ?>" />
            </div>
            
            <button type="submit" name="Search" class="aw-btn aw-btn-primary"><i class="fas fa-search"></i> <?php echo __('Filter'); ?></button>
        </form>

        <!-- RESULTS WITH RESPONSIVE PAGINATION -->
        <main id="SearchResultsMain">
            <?php 
            if (isset($SearchResult)) {
                $ListCount = DB_num_rows($SearchResult);
                $DisplayMax = 8; // Set a modern compact limit to avoid scrolling
                $MaxPages = ceil($ListCount / $DisplayMax);
                $PageOffset = $_POST['PageOffset'] ?? 1;
                ?>
                    <div class="aw-card">
                        <div class="aw-card-header" style="justify-content: space-between;">
                            <h3 class="aw-card-title"><i class="fas fa-list"></i> <?php echo __('Inventory Records'); ?></h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <span class="aw-badge" style="background: var(--primary-soft); color: var(--primary);"><?php echo $ListCount; ?> <?php echo __('Found'); ?></span>
                                <?php if ($_POST['StockFilter'] == 'All'): ?>
                                    <span class="aw-badge" style="background: #dcfce7; color: #166534;"><?php echo __('All SKUs'); ?></span>
                                <?php elseif ($_POST['StockFilter'] == 'InStock'): ?>
                                    <span class="aw-badge" style="background: #dcfce7; color: #166534;"><?php echo __('Physical Stock'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="aw-card-body p-0">
                            <?php
                            include_once(__DIR__ . '/includes/UIComponents.php');
                            
                            $columns = [__('Code'), __('Item Name'), __('Category'), __('Type'), __('On Hand'), __('Units'), __('Actions')];
                            $rows = [];
                            
                            DB_data_seek($SearchResult, ($PageOffset - 1) * $DisplayMax);
                            $i = 0;
                            while ($row = DB_fetch_array($SearchResult) AND ($i < $DisplayMax)) {
                                $qohColor = $row['qoh'] > 0 ? '#166534' : '#ef4444';
                                $actions = '<div style="display: flex; gap: 4px; justify-content: flex-end;">
                                                <button type="button" onclick="openModal(\'Stocks.php?StockID='.urlencode($row['stockid']).'\', \'Edit Item\')" class="aw-btn aw-btn-outline aw-btn-sm" title="'.__('Edit Item').'"><i class="fas fa-edit"></i> '.__('Edit').'</button>
                                                <button type="button" onclick="openModal(\'StockStatus.php?StockID='.urlencode($row['stockid']).'\', \'View Status\')" class="aw-btn aw-btn-outline aw-btn-sm" title="'.__('View Status').'"><i class="fas fa-eye"></i> '.__('View').'</button>
                                                <button type="button" onclick="openModal(\'SelectProduct.php?StockID='.urlencode($row['stockid']).'\', \'Item Details\')" class="aw-btn aw-btn-outline aw-btn-sm" title="'.__('More Actions').'"><i class="fas fa-ellipsis-v"></i> '.__('Select').'</button>
                                            </div>';
                                
                                $rows[] = [
                                    '<span style="font-weight: 700; color: var(--primary);">' . $row['stockid'] . '</span>',
                                    '<div style="font-weight: 600; white-space: normal; min-width: 150px; max-width: 350px;">' . $row['description'] . '</div>',
                                    $row['categorydescription'],
                                    $row['stocktype'],
                                    '<span class="number" style="color: '.$qohColor.';">' . locale_number_format($row['qoh'], $row['decimalplaces']) . '</span>',
                                    $row['units'],
                                    $actions
                                ];
                                $i++;
                            }
                            
                            render_modern_table($columns, $rows, false, ['emptyMessage' => __('No items found matching your criteria.')]);
                            
                            if ($MaxPages > 1) {
                                echo '<div style="padding: 0 1.5rem 1rem 1.5rem;">'; // Add padding to prevent overlap with card border
                                echo '<form method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'">';
                                echo '<input type="hidden" name="FormID" value="'.($_SESSION['FormID'] ?? '').'" />';
                                echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />';
                                echo '<input type="hidden" name="StockFilter" value="'.$_POST['StockFilter'].'" />';
                                echo '<input type="hidden" name="Keywords" value="'.($_POST['Keywords'] ?? '').'" />';
                                echo '<input type="hidden" name="StockCode" value="'.($_POST['StockCode'] ?? '').'" />';
                                echo '<input type="hidden" name="Search" value="Search" />';
                                
                                // render_modern_pagination_form expects totalRows, page, perPage
                                render_modern_pagination_form($ListCount, $PageOffset, $DisplayMax);
                                
                                echo '</form>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                
            <?php
            }
            ?>
        </main>
    </div>
<?php } ?>

</div>


<!-- GLASS MODAL -->
<style>
.glass-modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}
.glass-modal-content {
    background: #ffffff;
    width: 90%;
    max-width: 1400px;
    height: 90vh;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
}
.glass-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.glass-modal-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.glass-modal-close {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0.5rem;
    line-height: 1;
}
.glass-modal-close:hover {
    color: #ef4444;
}
.glass-modal-body {
    flex: 1;
    overflow: hidden;
    background: #f8fafc;
}
.glass-modal-iframe {
    width: 100%;
    height: 100%;
    border: none;
    flex: 1;
    display: block;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* Premium Revamp Styles */
    .premium-filter-bar {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-radius: var(--radius);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .premium-filter-bar .aw-field-group { margin-bottom: 0; flex: 1; min-width: 150px; }
    .premium-filter-bar .aw-btn { height: 38px; align-self: flex-end; }
    
    .premium-table-container {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        border: 1px solid var(--border-soft); overflow: hidden;
        overflow: hidden;
    }
    .aw-table th { background: #f8fafc; color: #475569; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .aw-table tr { transition: all 0.2s ease; }
    .aw-table tr:hover { background: #f1f5f9; transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.02); z-index: 1; position: relative; }
    
    /* Remove old grid layout */
    .aw-layout-search { display: block; }
</style>

<div class="glass-modal-overlay" id="inventoryModal">
    <div class="glass-modal-content">
        <div class="glass-modal-header">
            <h3 class="glass-modal-title" id="inventoryModalTitle">Item Hub</h3>
            <button class="glass-modal-close" onclick="closeModal()" style="font-size: 24px; font-weight: bold; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f1f5f9;">&times;</button>
        </div>
        <div class="glass-modal-body" style="display: flex; flex-direction: column;">
            <iframe class="glass-modal-iframe" id="inventoryModalFrame"></iframe>
        </div>
    </div>
</div>

<script>
function openModal(url, title) {
    document.getElementById('inventoryModalTitle').innerText = title;
    if (url.includes('?')) {
        url += '&modal=1&t=' + new Date().getTime();
    } else {
        url += '?modal=1&t=' + new Date().getTime();
    }
    document.getElementById('inventoryModalFrame').src = url;
    document.getElementById('inventoryModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('inventoryModal').style.display = 'none';
    document.getElementById('inventoryModalFrame').src = '';
    window.location.reload();
}

window.addEventListener('message', function(event) {
    if (event.data === 'closeModalAndRefresh') {
        closeModal();
    }
});
</script>

<?php 

?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('FilterBar');
    if (!filterForm) return;

    let debounceTimer;

    function fetchResults() {
        const mainContainer = document.getElementById('SearchResultsMain');
        if (mainContainer) mainContainer.style.opacity = '0.5';

        const formData = new FormData(filterForm);
        formData.append('Search', 'Filter'); 

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.getElementById('SearchResultsMain');
            if (newMain && mainContainer) {
                mainContainer.innerHTML = newMain.innerHTML;
                mainContainer.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            if (mainContainer) mainContainer.style.opacity = '1';
        });
    }

    filterForm.querySelectorAll('select').forEach(el => {
        el.addEventListener('change', fetchResults);
    });

    filterForm.querySelectorAll('input[type="text"]').forEach(el => {
        el.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchResults, 400); 
        });
    });
});
</script>
<?php
include(__DIR__ . '/includes/footer.php');


function PrepareSearchString(string $InputString): string {
    return '%' . str_replace(' ', '%', mb_strtoupper($InputString)) . '%';
}

function GenerateStockmasterQuery(array $post): string {
    $SQL = "SELECT stockmaster.stockid, stockmaster.description, SUM(locstock.quantity) AS qoh, stockmaster.units, stockmaster.decimalplaces, stockcategory.categorydescription, stockcategory.stocktype
            FROM stockmaster 
            LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
            LEFT JOIN locstock ON stockmaster.stockid = locstock.stockid ";
    $WhereSQL = " WHERE 1=1 "; 
    if (isset($post['Keywords']) && mb_strlen($post['Keywords']) > 0) {
        $SearchString = PrepareSearchString($post['Keywords']);
        $WhereSQL .= "AND (stockmaster.description LIKE '$SearchString' OR stockmaster.stockid LIKE '$SearchString') ";
    } elseif (isset($post['StockCode']) && mb_strlen($post['StockCode']) > 0) {
        $SearchString = PrepareSearchString($post['StockCode']);
        $WhereSQL .= "AND stockmaster.stockid LIKE '$SearchString' ";
    }
    if ($post['StockCat'] != 'All') {
        $WhereSQL .= "AND stockmaster.categoryid = '" . $post['StockCat'] . "' ";
    }
    
    $SQL .= $WhereSQL . " GROUP BY stockmaster.stockid, stockmaster.description, stockmaster.units, stockmaster.decimalplaces, stockcategory.categorydescription, stockcategory.stocktype ";
    
    // Applying the Stock Filter
    if ($post['StockFilter'] == 'InStock') {
        $SQL .= " HAVING SUM(locstock.quantity) > 0 ";
    } elseif ($post['StockFilter'] == 'OutOfStock') {
        $SQL .= " HAVING SUM(locstock.quantity) <= 0 OR SUM(locstock.quantity) IS NULL ";
    }

    $SQL .= " ORDER BY stockmaster.stockid";
    return $SQL;
}
?>
