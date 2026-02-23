/* SuiteCRM Website - Core JavaScript Functionality */

document.addEventListener('DOMContentLoaded', function() {
    // Load header and footer components first
    loadComponents();
    
    // Initialize all functionality
    initializeTables();
    initializePagination();
    initializeWidgetControls();
    initializeTabs();
    initializeNavigation();
});

// Load header and footer components
function loadComponents() {
    // Load header
    const headerContainer = document.getElementById('header-container');
    if (headerContainer) {
        fetch('header.html')
            .then(response => response.text())
            .then(html => {
                headerContainer.innerHTML = html;
                setActiveNavigation();
            })
            .catch(error => console.log('Header not loaded:', error));
    }
    
    // Load footer
    const footerContainer = document.getElementById('footer-container');
    if (footerContainer) {
        fetch('footer.html')
            .then(response => response.text())
            .then(html => {
                footerContainer.innerHTML = html;
            })
            .catch(error => console.log('Footer not loaded:', error));
    }
}

// Set active navigation based on current page
function setActiveNavigation() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navItems = document.querySelectorAll('.nav-item');
    
    // Remove all active states first
    navItems.forEach(item => item.classList.remove('active'));
    
    // Set active state based on current page
    if (currentPage.includes('accounts')) {
        document.querySelector('.nav-item.accounts')?.classList.add('active');
    } else if (currentPage.includes('contacts')) {
        document.querySelector('.nav-item.contacts')?.classList.add('active');
    } else if (currentPage.includes('opportunities')) {
        document.querySelector('.nav-item.opportunities')?.classList.add('active');
    } else if (currentPage.includes('quotes')) {
        document.querySelector('.nav-item.quotes')?.classList.add('active');
    }
}

// Navigation functionality
function initializeNavigation() {
    // Handle search functionality
    const searchBox = document.querySelector('.search-box');
    if (searchBox) {
        searchBox.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch(this.value);
            }
        });
    }
    
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchBox = document.querySelector('.search-box');
            performSearch(searchBox.value);
        });
    }
}

function performSearch(query) {
    if (query.trim()) {
        console.log('Searching for:', query);
        // In a real application, this would perform the search
        alert('Search functionality would be implemented here for: ' + query);
    }
}

// Table functionality
function initializeTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        // Add hover effects (handled by CSS)
        // Add sorting functionality if needed
        addTableSorting(table);
    });
}

function addTableSorting(table) {
    const headers = table.querySelectorAll('th.sortable');
    
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.column;
            const currentSort = this.dataset.sort || 'asc';
            const newSort = currentSort === 'asc' ? 'desc' : 'asc';
            
            // Update sort indicator
            this.dataset.sort = newSort;
            
            // Remove sort indicators from other headers
            headers.forEach(h => {
                if (h !== this) {
                    delete h.dataset.sort;
                }
            });
            
            // Sort table rows
            sortTableByColumn(table, column, newSort);
        });
    });
}

function sortTableByColumn(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aText = a.querySelector(`[data-column="${column}"]`)?.textContent || 
                     a.cells[getColumnIndex(table, column)]?.textContent || '';
        const bText = b.querySelector(`[data-column="${column}"]`)?.textContent || 
                     b.cells[getColumnIndex(table, column)]?.textContent || '';
        
        if (direction === 'asc') {
            return aText.localeCompare(bText);
        } else {
            return bText.localeCompare(aText);
        }
    });
    
    // Clear tbody and append sorted rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

function getColumnIndex(table, column) {
    const headers = table.querySelectorAll('th');
    for (let i = 0; i < headers.length; i++) {
        if (headers[i].dataset.column === column) {
            return i;
        }
    }
    return 0;
}

// Pagination functionality
function initializePagination() {
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            
            // Remove active state from all buttons in this pagination
            const container = this.closest('.pagination-container');
            container.querySelectorAll('.pagination-btn.active').forEach(activeBtn => {
                activeBtn.classList.remove('active');
            });
            
            // Add active state to clicked button (unless it's an arrow)
            if (!this.textContent.includes('←') && !this.textContent.includes('→')) {
                this.classList.add('active');
            }
            
            // Handle pagination logic here
            console.log('Pagination clicked:', this.textContent);
        });
    });
}

// Widget controls functionality
function initializeWidgetControls() {
    const controlBtns = document.querySelectorAll('.widget-control-btn');
    
    controlBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const widget = this.closest('.widget');
            
            switch(action) {
                case 'settings':
                    showWidgetSettings(widget);
                    break;
                case 'close':
                    closeWidget(widget);
                    break;
                default:
                    console.log('Widget control clicked');
            }
        });
    });
}

function showWidgetSettings(widget) {
    // Placeholder for widget settings functionality
    alert('Widget settings - functionality would be implemented here');
}

function closeWidget(widget) {
    if (confirm('Are you sure you want to close this widget?')) {
        widget.style.display = 'none';
    }
}

// Tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.dataset.bsTarget;
            const target = document.querySelector(targetId);
            
            if (target) {
                // Remove active state from all tabs in this group
                const tabGroup = this.closest('.nav-tabs');
                tabGroup.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active state to clicked tab
                this.classList.add('active');
                
                // Hide all tab panes in this group
                const tabContent = target.closest('.tab-content');
                tabContent.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active', 'show');
                });
                
                // Show target tab pane
                target.classList.add('active', 'show');
            }
        });
    });
}

// Action button functionality
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-action')) {
        const action = e.target.closest('.btn-action').dataset.action;
        handleActionButton(action, e.target);
    }
});

function handleActionButton(action, button) {
    switch(action) {
        case 'edit':
            console.log('Edit action clicked');
            break;
        case 'view':
            console.log('View action clicked');
            break;
        case 'delete':
            if (confirm('Are you sure you want to delete this item?')) {
                console.log('Delete action confirmed');
            }
            break;
        default:
            console.log('Action button clicked:', action);
    }
}

// Bulk actions functionality
function initializeBulkActions() {
    const selectAllCheckbox = document.querySelector('#selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkActionBtns = document.querySelectorAll('.bulk-actions .btn');
    
    bulkActionBtns.forEach(btn => {
        btn.disabled = checkedBoxes.length === 0;
    });
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Utility functions
function showModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal">
            <div class="modal-header">
                <h5>${title}</h5>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize bulk actions when DOM is ready
document.addEventListener('DOMContentLoaded', initializeBulkActions);