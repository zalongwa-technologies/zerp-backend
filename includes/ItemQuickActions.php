<?php
/**
 * Unified Item Navigation Footer
 * Included in SelectProduct.php, StockStatus.php, StockMovements.php, etc.
 * Provides a consistent "Tab" bar for the active product, indicating the active page.
 */
if (!isset($StockID) || empty($StockID)) {
    return; // Don't show if no item is selected
}

$ModalParam = (isset($_GET['modal']) || isset($_POST['modal'])) ? '&modal=1' : '';
$currentPage = basename($_SERVER['PHP_SELF']);

$navLinks = [
    'SelectProduct.php' => ['icon' => 'fa-th', 'label' => __('Dashboard')],
    'Stocks.php' => ['icon' => 'fa-edit', 'label' => __('Edit')],
    'StockStatus.php' => ['icon' => 'fa-warehouse', 'label' => __('Status')],
    'StockMovements.php' => ['icon' => 'fa-exchange-alt', 'label' => __('Movements')],
    'StockAdjustments.php' => ['icon' => 'fa-sliders-h', 'label' => __('Adjust')],
    'StockLocTransfer.php' => ['icon' => 'fa-dolly', 'label' => __('Transfer')],
    'Prices.php' => ['icon' => 'fa-tags', 'label' => __('Prices')]
];
?>
<style>
.item-quick-actions .aw-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
.item-quick-actions .aw-btn-primary { background: hsl(145, 63%, 38%); color: #ffffff !important; }
.item-quick-actions .aw-btn-primary:hover { background: hsl(145, 63%, 32%); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.item-quick-actions .aw-btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #334155; }
.item-quick-actions .aw-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
</style>
<div class="item-quick-actions" style="background: var(--surface-alt, #f8fafc); border: 1px solid var(--border-soft, #e2e8f0); margin: 1.5rem 0 0 0; padding: 1.25rem 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <span style="font-weight: 800; color: var(--text-muted, #64748b); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">
        <i class="fas fa-bolt"></i> <?php echo __('Item Actions'); ?>
    </span>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <?php foreach ($navLinks as $page => $nav) : ?>
            <?php 
            $isActive = ($currentPage == $page);
            $btnClass = $isActive ? 'aw-btn-primary' : 'aw-btn-outline';
            // Special parameter names for certain scripts
            $paramName = 'StockID';
            if ($page == 'SelectSalesOrder.php') $paramName = 'SelectedStockItem';
            if ($page == 'Prices.php') $paramName = 'Item';
            ?>
            <a href="<?php echo htmlspecialchars($page . '?' . $paramName . '=' . urlencode($StockID) . $ModalParam); ?>" 
               class="aw-btn <?php echo $btnClass; ?>" 
               <?php if ($isActive) echo 'style="pointer-events: none; opacity: 0.8; cursor: default;"'; ?>>
                <i class="fas <?php echo $nav['icon']; ?>"></i> <?php echo $nav['label']; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
