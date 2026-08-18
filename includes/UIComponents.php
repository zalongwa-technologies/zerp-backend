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
function render_modern_table($columns, $dataRows, $options = []) {
    // Extract options with defaults
    $emptyMessage = $options['emptyMessage'] ?? "No records found.";
    $hasCheckboxes = $options['hasCheckboxes'] ?? false;
    $checkboxName = $options['checkboxName'] ?? "selected_ids[]";
    
    // Wrapper
    echo '<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">';
    echo '<table class="w-full text-sm text-left rtl:text-right text-body">';
    
    // Headers
    echo '<thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">';
    echo '<tr>';
    
    // Master Checkbox Header
    if ($hasCheckboxes) {
        echo '<th scope="col" class="p-4">';
        echo '<div class="flex items-center">';
        echo '<input id="master-checkbox" type="checkbox" onclick="var checkboxes = document.querySelectorAll(\'.row-checkbox\'); for(var i=0; i<checkboxes.length; i++) checkboxes[i].checked = this.checked;" class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft">';
        echo '<label for="master-checkbox" class="sr-only">Select all</label>';
        echo '</div>';
        echo '</th>';
    }
    
    foreach ($columns as $header) {
        // Assume the last column might be "Action"
        $headerClass = (strtolower($header) === 'action') ? 'px-6 py-3 font-medium whitespace-nowrap' : 'px-6 py-3 font-medium whitespace-nowrap';
        echo '<th scope="col" class="' . $headerClass . '">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    
    // Body
    echo '<tbody>';
    if (empty($dataRows)) {
        $colSpan = count($columns) + ($hasCheckboxes ? 1 : 0);
        echo '<tr><td colspan="' . $colSpan . '" class="px-6 py-4 text-center text-body">' . htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    } else {
        foreach ($dataRows as $index => $row) {
            echo '<tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">';
            
            // Row Checkbox
            if ($hasCheckboxes) {
                // If the first element in the row is the ID, use it for the checkbox value
                $rowId = isset($row['id']) ? $row['id'] : $index;
                echo '<td class="w-4 p-4">';
                echo '<div class="flex items-center">';
                echo '<input id="checkbox-' . $rowId . '" type="checkbox" name="' . htmlspecialchars($checkboxName, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($rowId, ENT_QUOTES, 'UTF-8') . '" class="row-checkbox w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft">';
                echo '<label for="checkbox-' . $rowId . '" class="sr-only">Select row</label>';
                echo '</div>';
                echo '</td>';
            }
            
            $colIndex = 0;
            foreach ($row as $key => $cellValue) {
                // Skip the ID if it was just passed for the checkbox
                if ($hasCheckboxes && $key === 'id') continue;

                if ($colIndex === 0) {
                    // First data column gets the th scope="row" styling for emphasis
                    echo '<th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">' . $cellValue . '</th>';
                } else {
                    // Check if this is the last column (Action column usually)
                    // If the cell contains HTML links, we might want to wrap it in the flex container
                    if (strpos($cellValue, '<a ') !== false) {
                        echo '<td class="flex items-center px-6 py-4 whitespace-nowrap">' . $cellValue . '</td>';
                    } else {
                        echo '<td class="px-6 py-4 whitespace-nowrap">' . $cellValue . '</td>';
                    }
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
?>
