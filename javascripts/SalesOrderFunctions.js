/**
 * Sales Order Search Modern Logic
 */

const SalesOrderSearch = {
    debounceTimer: null,
    formId: '',

    init() {
        console.log('SalesOrderSearch Module Initialized');
        this.bindEvents();
        this.formId = document.querySelector('input[name="FormID"]')?.value || '';

        // Load initial data if none exists
        const tableBody = document.getElementById('OrdersTableBody');
        if (tableBody && tableBody.children.length === 0) {
            this.fetchResults();
        }
    },

    bindEvents() {
        // Smart Search input
        const searchInput = document.getElementById('SmartSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.debounce(() => this.fetchResults(), 500);
            });
        }

        // Advanced filter changes
        document.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('change', () => this.fetchResults());
        });

        // Toggle Advanced Filters
        const toggleBtn = document.getElementById('ToggleFilters');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const panel = document.getElementById('AdvancedFiltersPanel');
                const isHidden = panel.style.display === 'none' || panel.style.display === '';
                panel.style.display = isHidden ? 'grid' : 'none';
                toggleBtn.classList.toggle('active', isHidden);
            });
        }

        // Bulk selection
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('so-check-all')) {
                document.querySelectorAll('.so-row-check').forEach(cb => cb.checked = e.target.checked);
                this.updateBulkBar();
            }
            if (e.target.classList.contains('so-row-check')) {
                this.updateBulkBar();
            }
        });

        // Intercept form submissions
        const form = document.getElementById('SalesOrderForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                const submitter = e.submitter;
                if (submitter && submitter.name === 'PlacePO') {
                    // Let bulk purchase orders form submit normally
                    return;
                }
                // Otherwise prevent reload and search via AJAX
                e.preventDefault();
                clearTimeout(this.debounceTimer);
                this.fetchResults();
            });
        }
    },

    debounce(func, delay) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, delay);
    },

    async fetchResults() {
        const tableBody = document.getElementById('OrdersTableBody');
        const container = document.querySelector('.db-table-wrapper');

        if (container) {
            container.classList.add('table-loading-overlay');
        }

        const formData = new FormData(document.getElementById('SalesOrderForm'));
        formData.append('AjaxSearch', '1');

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Search failed');

            const data = await response.json();

            // Update table body
            if (tableBody) {
                tableBody.innerHTML = data.html;
            }

            // Update card title if element exists
            const cardTitle = document.getElementById('CardHeaderTitle');
            if (cardTitle && data.title) {
                cardTitle.innerHTML = data.title;
            }

            // Update count tag if element exists
            const countTag = document.getElementById('ResultsCountTag');
            if (countTag && data.count) {
                countTag.textContent = data.count;
            }

            // Update footer total if element exists
            const footerTotal = document.getElementById('FooterTotalValue');
            if (footerTotal && data.total) {
                footerTotal.textContent = data.total;
            }

        } catch (error) {
            console.error('Search error:', error);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="100%" class="text-center p-8 text-red-500">Error loading results. Please try again.</td></tr>';
            }
        } finally {
            if (container) {
                container.classList.remove('table-loading-overlay');
            }
            this.updateBulkBar();
        }
    },

    updateBulkBar() {
        const selected = document.querySelectorAll('.so-row-check:checked');
        const bar = document.getElementById('BulkActionsBar');
        const countSpan = document.getElementById('SelectedCount');

        if (selected.length > 0) {
            bar.style.display = 'flex';
            countSpan.textContent = `${selected.length} items selected`;
        } else {
            bar.style.display = 'none';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => SalesOrderSearch.init());
