<?php
/**
 * UI Components Library
 * 
 * A centralized collection of reusable UI components (Tables, Cards, Forms)
 * designed to enforce consistent UI/UX across the ZERP application.
 */

/**
 * Renders a modern, standardized table using the provided design system.
 *
 * @param array $columns An array of column headers (e.g., ['Product name', 'Color'])
 * @param array $dataRows A 2D array of row data
 * @param array $options Configuration options for features like checkboxes
 */
function render_modern_table($columns, $dataRows, $hasCheckboxes = false, $options = []) {
    $emptyMessage = $options['emptyMessage'] ?? "No records found.";
    $checkboxName = $options['checkboxName'] ?? "selected_ids[]";
    
    // Wrapper
    echo '<div style="position:relative;overflow-x:auto;background-color:var(--surface);box-shadow:0 1px 2px rgba(0,0,0,0.05);border-radius:8px;border:1px solid var(--border-soft);margin-bottom:1rem;max-width:100%;">';
    echo '<table style="width:100%;font-size:0.875rem;text-align:left;color:var(--text-body);border-collapse:collapse;white-space:nowrap;">';
    
    // Headers
    echo '<thead style="background-color:var(--surface-alt);border-bottom:1px solid var(--border);color:var(--text-main);">';
    echo '<tr>';

    // Master Checkbox Header
    if ($hasCheckboxes) {
        echo '<th scope="col" style="padding:12px 24px;font-weight:500;white-space:nowrap;width:16px;text-align:center;">';
        echo '<input id="master-checkbox" type="checkbox" onclick="var checkboxes = document.querySelectorAll(\'.row-checkbox\'); for(var i=0; i<checkboxes.length; i++) checkboxes[i].checked = this.checked;" style="width:16px;height:16px;cursor:pointer;">';
        echo '</th>';
    }

    foreach ($columns as $header) {
        echo '<th scope="col" style="padding:12px 24px;font-weight:500;white-space:nowrap;">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr>';
    echo '</thead>';

    // Body
    echo '<tbody>';
    if (empty($dataRows)) {
        $colSpan = count($columns) + ($hasCheckboxes ? 1 : 0);
        echo '<tr><td colspan="' . $colSpan . '" style="padding:16px 24px;white-space:nowrap;text-align:center;">' . htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    } else {
        foreach ($dataRows as $index => $row) {
            // Hover effect via inline events to mimic CSS hover
            echo '<tr style="background-color:var(--surface);border-bottom:1px solid var(--border-soft);transition:background-color 0.15s ease;" onmouseover="this.style.backgroundColor=\'var(--surface-alt)\';" onmouseout="this.style.backgroundColor=\'var(--surface)\';">';

            // Row Checkbox
            if ($hasCheckboxes) {
                // If the first element in the row is the ID, use it for the checkbox value
                $rowId = isset($row['id']) ? $row['id'] : $index;
                echo '<td style="padding:16px 24px;white-space:nowrap;width:16px;text-align:center;">';
                echo '<input id="checkbox-' . $rowId . '" type="checkbox" name="' . htmlspecialchars($checkboxName, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($rowId, ENT_QUOTES, 'UTF-8') . '" class="row-checkbox" style="width:16px;height:16px;cursor:pointer;">';
                echo '</td>';
            }

            $colIndex = 0;
            foreach ($row as $key => $cellValue) {
                // Skip the ID if it was just passed for the checkbox
                if ($hasCheckboxes && $key === 'id') continue;

                if ($colIndex === 0) {
                    echo '<th scope="row" style="padding:16px 24px;white-space:nowrap;font-weight:500;color:#111827;">' . $cellValue . '</th>';
                } else {
                    echo '<td style="padding:16px 24px;white-space:nowrap;">' . $cellValue . '</td>';
                }
                $colIndex++;
            }
            echo '</tr>';
        }
    }
    echo '</tbody>';

    echo '</table>';
    echo '</div>';
}

/**
 * Renders a modern pagination control based on the provided design system.
 *
 * @param int $totalRows Total number of records across all pages
 * @param int $page Current page number
 * @param int $perPage Number of records per page
 * @param string $baseUrl Base URL for pagination links
 * @param string $extraParams Extra query parameters (e.g. '&Search=xyz')
 */
function render_modern_pagination($totalRows, $page, $perPage, $baseUrl, $extraParams = '') {
    $totalPages = max(1, ceil($totalRows / $perPage));
    
    // Don't render if there's only 1 page
    if ($totalPages <= 1) {
        return;
    }

    echo '<nav aria-label="Page navigation" class="mt-4 flex justify-end">';
    echo '<div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">';
    
    // Previous Link
    if ($page > 1) {
        $prevUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '?Page=' . ($page - 1);
        if ($extraParams !== '') $prevUrl .= '&amp;' . htmlspecialchars($extraParams, ENT_QUOTES, 'UTF-8');
        
        echo '<a href="' . $prevUrl . '" data-tooltip-target="tooltip-previous" class="inline-flex items-center justify-center text-body bg-neutral-secondary-medium rounded-s-base box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-3 focus:ring-neutral-tertiary leading-5 w-9 h-9 focus:outline-none">';
        echo '<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>';
        echo '</a>';
    } else {
        // Disabled state
        echo '<span class="inline-flex items-center justify-center text-body bg-neutral-secondary-medium rounded-s-base box-border border border-default-medium opacity-50 cursor-not-allowed leading-5 w-9 h-9">';
        echo '<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>';
        echo '</span>';
    }
    
    // Tooltip for Previous (Optional, requires JS to function fully, but HTML is included)
    echo '<div id="tooltip-previous" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm leading-4 font-medium text-white transition-opacity duration-300 bg-dark rounded-base shadow-xs opacity-0 tooltip">';
    echo 'Previous';
    echo '<div class="tooltip-arrow" data-popper-arrow></div>';
    echo '</div>';

    // Page Info (e.g. 1 of 99)
    echo '<span class="inline-flex shrink-0 text-sm items-center justify-center text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading leading-5 px-3 h-9 focus:outline-none">';
    echo $page . ' ' . __('of') . ' ' . $totalPages;
    echo '</span>';
    
    // Next Link
    if ($page < $totalPages) {
        $nextUrl = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '?Page=' . ($page + 1);
        if ($extraParams !== '') $nextUrl .= '&amp;' . htmlspecialchars($extraParams, ENT_QUOTES, 'UTF-8');
        
        echo '<a href="' . $nextUrl . '" data-tooltip-target="tooltip-next" class="inline-flex items-center justify-center text-body bg-neutral-secondary-medium rounded-e-base box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-3 focus:ring-neutral-tertiary leading-5 w-9 h-9 focus:outline-none">';
        echo '<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>';
        echo '</a>';
    } else {
        // Disabled state
        echo '<span class="inline-flex items-center justify-center text-body bg-neutral-secondary-medium rounded-e-base box-border border border-default-medium opacity-50 cursor-not-allowed leading-5 w-9 h-9">';
        echo '<svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>';
        echo '</span>';
    }
    
    // Tooltip for Next
    echo '<div id="tooltip-next" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm leading-4 font-medium text-white transition-opacity duration-300 bg-dark rounded-base shadow-xs opacity-0 tooltip">';
    echo 'Next';
    echo '<div class="tooltip-arrow" data-popper-arrow></div>';
    echo '</div>';

    echo '</div>';
    echo '</nav>';
}

/**
 * Renders a modern form-based pagination control.
 * Uses <button type="submit"> instead of <a> tags to preserve POST state.
 */
function render_modern_pagination_form($totalRows, $page, $perPage) {
    $totalPages = max(1, ceil($totalRows / $perPage));

    if ($totalPages <= 1) {
        return;
    }

    echo '<div class="noPrint" style="display:flex;justify-content:flex-end;margin-top:16px;margin-bottom:16px;">';
    echo '<div style="display:inline-flex;border-radius:6px;box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);">';

    $page = (int)$page;

    // Previous Button
    if ($page > 1) {
        echo '<button type="submit" name="Previous" value="Previous" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid var(--border);border-right:none;background:#f9fafb;color:var(--text-body);border-radius:6px 0 0 6px;cursor:pointer;outline:none;">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
        </button>';
    } else {
        echo '<button type="button" disabled style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid var(--border);border-right:none;background:var(--surface-alt);color:#9ca3af;border-radius:6px 0 0 6px;cursor:not-allowed;outline:none;">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
        </button>';
    }

    // Page Info
    echo '<span style="display:inline-flex;align-items:center;justify-content:center;padding:8px 16px;border:1px solid var(--border);background:#f9fafb;color:var(--text-body);font-size:14px;font-weight:500;">' . $page . ' ' . __('of') . ' ' . $totalPages . '</span>';

    // Next Button
    if ($page < $totalPages) {
        echo '<button type="submit" name="Next" value="Next" style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid var(--border);border-left:none;background:#f9fafb;color:var(--text-body);border-radius:0 6px 6px 0;cursor:pointer;outline:none;">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
        </button>';
    } else {
        echo '<button type="button" disabled style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid var(--border);border-left:none;background:var(--surface-alt);color:#9ca3af;border-radius:0 6px 6px 0;cursor:not-allowed;outline:none;">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
        </button>';
    }

    echo '</div></div>';
}
