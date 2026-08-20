<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Usage');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_POST['ShowGraphUsage'])) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation']  . '&amp;StockID=' . $StockID . '">';
	prnMsg(__('You should automatically be forwarded to the usage graph') .
			'. ' . __('If this does not happen') .' (' . __('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $RootPath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation'] .'&amp;StockID=' . $StockID . '">' . __('click here') . '</a> ' . __('to continue'),'info');
	include(__DIR__ . '/includes/footer.php');
	exit();
}

$ViewTopic = 'Inventory';
$BookMark = '';
include(__DIR__ . '/includes/header.php');

require_once(__DIR__ . '/includes/UIComponents.php');

echo '<style>
    .premium-status-container {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem 2rem 0 2rem;
        border: 1px solid var(--border-soft);
        overflow: hidden;
    }
    .status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .status-title-area h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .status-title-area p {
        margin: 0.25rem 0 0 0;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    .premium-filter-bar {
        background: var(--surface-alt);
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        gap: 1.5rem;
        align-items: flex-end;
        flex-wrap: wrap;
        border: 1px solid var(--border-soft);
    }
    .aw-field-group { margin-bottom: 0; flex: 1; min-width: 200px; }
    .aw-label { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .aw-input, .aw-select { width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); font-size: 0.9rem; }
    .aw-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; border: none; text-decoration: none; }
    .aw-btn-primary { background: var(--primary); color: #ffffff !important; }
    .aw-btn-outline { background: transparent; border: 1px solid var(--border-soft); color: var(--text-main); }
    .metric-card {
        background: var(--surface-alt);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-soft);
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .metric-card-icon {
        background: var(--primary-soft);
        color: var(--primary);
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .metric-card-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        font-weight: 800;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }
    .metric-card-value {
        font-size: 1.2rem;
        font-weight: 900;
        color: var(--primary-dark);
    }
</style>';

echo '<div class="aw-page premium-status-container">';

echo '<div class="status-header">
        <div class="status-title-area">
            <h1><i class="fas fa-chart-line"></i> ' . __('Stock Usage Analysis') . '</h1>
            <p>' . __('View consumption history over time') . '</p>
        </div>
      </div>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="premium-filter-bar">
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
        <div class="aw-field-group">
            <label class="aw-label">' . __('Stock Code') . '</label>
            <input type="text" name="StockID" class="aw-input" value="' . htmlspecialchars($StockID) . '" required="required" placeholder="' . __('e.g. ITEM-001') . '" autofocus />
        </div>
        <div class="aw-field-group">
            <label class="aw-label">' . __('Location') . '</label>
            <select name="StockLocation" class="aw-select">';
                $SQL_Loc = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
                $ResStkLocs = DB_query($SQL_Loc);
                while ($RowLoc = DB_fetch_array($ResStkLocs)) {
                    $selected = (isset($_POST['StockLocation']) AND $_POST['StockLocation'] == $RowLoc['loccode']) ? 'selected="selected"' : '';
                    echo '<option ' . $selected . ' value="' . $RowLoc['loccode'] . '">' . $RowLoc['locationname'] . '</option>';
                }
                $all_selected = (isset($_POST['StockLocation']) AND $_POST['StockLocation'] == 'All') ? 'selected="selected"' : '';
                echo '<option ' . $all_selected . ' value="All">' . __('All Locations') . '</option>
            </select>
        </div>
        <div class="aw-field-group" style="flex: 0 0 auto; display: flex; gap: 0.5rem;">
            <button type="submit" name="ShowUsage" class="aw-btn aw-btn-primary" style="padding: 0.6rem 1.5rem;">
                <i class="fas fa-search"></i> ' . __('Search') . '
            </button>
            <button type="submit" name="ShowGraphUsage" class="aw-btn aw-btn-outline" style="padding: 0.6rem 1.5rem;">
                <i class="fas fa-chart-bar"></i> ' . __('Graph') . '
            </button>
        </div>
      </form>';

$CurrentPeriod = GetPeriod(date($_SESSION['DefaultDateFormat']));

if (isset($_POST['ShowUsage'])){
    if ($_POST['StockLocation']=='All'){
        $SQL = "SELECT periods.periodno,
                periods.lastdate_in_period,
                canview,
                SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
                            AND stockmoves.hidemovt=0
                            AND stockmoves.stockid = '" . $StockID . "'
                        THEN -stockmoves.qty ELSE 0 END) AS qtyused
                FROM periods LEFT JOIN stockmoves
                    ON periods.periodno=stockmoves.prd
                INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
                WHERE periods.periodno <='" . $CurrentPeriod . "'
                GROUP BY periods.periodno,
                    periods.lastdate_in_period
                ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
    } else {
        $SQL = "SELECT periods.periodno,
                periods.lastdate_in_period,
                SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
                                AND stockmoves.hidemovt=0
                                AND stockmoves.stockid = '" . $StockID . "'
                                AND stockmoves.loccode='" . $_POST['StockLocation'] . "'
                            THEN -stockmoves.qty ELSE 0 END) AS qtyused
                FROM periods LEFT JOIN stockmoves
                    ON periods.periodno=stockmoves.prd
                WHERE periods.periodno <='" . $CurrentPeriod . "'
                GROUP BY periods.periodno,
                    periods.lastdate_in_period
                ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
    }
    $ErrMsg = __('The stock usage for the selected criteria could not be retrieved');
    $MovtsResult = DB_query($SQL, $ErrMsg);

    $TotalUsage = 0;
    $PeriodsCounter = 0;
    $data = [];

    while ($MyRow = DB_fetch_array($MovtsResult)) {
        $DisplayDate = MonthAndYearFromSQLDate($MyRow['lastdate_in_period']);
        $TotalUsage += $MyRow['qtyused'];
        $PeriodsCounter++;
        
        $data[] = [
            'period' => '<div style="font-weight: 700; color: var(--primary);">' . $DisplayDate . '</div>',
            'qty' => '<div style="text-align: right; font-weight: 700;">' . locale_number_format($MyRow['qtyused'], $DecimalPlaces) . '</div>'
        ];
    }
    
    // Calculate Average
    $AvgUsage = ($PeriodsCounter > 0) ? locale_number_format($TotalUsage/$PeriodsCounter, $DecimalPlaces) : 0;
    
    // Display Metric Card
    if ($TotalUsage > 0 AND $PeriodsCounter > 0) {
        $ResMaster = DB_query("SELECT units FROM stockmaster WHERE stockid='".$StockID."'");
        $Units = (DB_num_rows($ResMaster) > 0) ? DB_fetch_array($ResMaster)['units'] : '';
        
        echo '<div class="metric-card">
                <div class="metric-card-icon"><i class="fas fa-calculator"></i></div>
                <div>
                    <div class="metric-card-title">' . __('Average Monthly Usage') . '</div>
                    <div class="metric-card-value">' . $AvgUsage . ' ' . $Units . '</div>
                </div>
              </div>';
    }

    $columns = [
        'period' => ['label' => __('Period / Month')],
        'qty' => ['label' => __('Physical Usage'), 'style' => 'text-align: right;']
    ];

    echo render_modern_table($columns, $data);

} else if ($StockID == '') {
    echo '<div style="background: var(--surface-alt); padding: 2rem; border-radius: 12px; text-align: center; color: var(--text-muted); margin-bottom: 2rem;">
            <i class="fas fa-arrow-up" style="font-size: 2rem; margin-bottom: 1rem; color: var(--border);"></i>
            <p>' . __('Please enter a stock code and select a location to view usage trends.') . '</p>
          </div>';
}

echo '    <!-- Footer Quick Insights -->
    '; include(__DIR__ . '/includes/ItemQuickActions.php'); echo '
</div>'; // end db-bottom-layout

include(__DIR__ . '/includes/footer.php');
