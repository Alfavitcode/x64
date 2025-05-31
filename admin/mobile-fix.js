/**
 * Скрипт для исправления отображения таблиц на мобильных устройствах в админ-панели
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a mobile device
    const isMobile = window.innerWidth <= 767;
    
    if (isMobile) {
        // Apply mobile styles to all tables
        applyMobileStyles();
        
        // Special handling for reports table
        const reportsWrapper = document.querySelector('#reports .report-table-wrapper');
        const reportsTable = document.querySelector('#reports .report-table');
        
        if (reportsWrapper && reportsTable) {
            reportsWrapper.style.overflowX = 'auto';
            reportsWrapper.style.webkitOverflowScrolling = 'touch';
            reportsWrapper.style.maxWidth = '100%';
            
            reportsTable.style.minWidth = '660px';
            reportsTable.style.width = 'auto';
        }
        
        // Special handling for products table
        const productsWrapper = document.querySelector('#products .product-table-wrapper');
        const productsTable = document.querySelector('#products .product-table');
        
        if (productsWrapper && productsTable) {
            productsWrapper.style.overflowX = 'auto';
            productsWrapper.style.webkitOverflowScrolling = 'touch';
            productsWrapper.style.maxWidth = '100%';
            
            productsTable.style.minWidth = '630px';
            productsTable.style.width = 'auto';
            productsTable.style.tableLayout = 'fixed';
            
            // Apply specific column widths
            applyProductTableColumnWidths(productsTable);
            
            // Set content alignment
            alignProductTableContent(productsTable);
        }
    } else {
        // Apply desktop styles
        applyDesktopStyles();
    }
    
    // Apply desktop styles to tables
    function applyDesktopStyles() {
        // Hide horizontal scrolling on desktop
        const wrappers = document.querySelectorAll('.product-table-wrapper, .category-table-wrapper, .order-table-wrapper, .report-table-wrapper, .user-table-wrapper');
        wrappers.forEach(wrapper => {
            wrapper.style.overflowX = 'hidden';
            wrapper.style.maxWidth = '100%';
        });
        
        // Make tables full width
        const tables = document.querySelectorAll('.product-table, .category-table, .order-table, .report-table, .user-table');
        tables.forEach(table => {
            table.style.width = '100%';
            table.style.minWidth = 'auto';
            table.style.maxWidth = '100%';
            table.style.tableLayout = 'fixed';
        });
        
        // Special handling for product table on desktop
        const productsTable = document.querySelector('#products .product-table');
        if (productsTable) {
            // Set percentage-based column widths for desktop
            const columnWidths = [
                { index: 0, width: '5%' },   // ID
                { index: 1, width: '8%' },   // Изображение
                { index: 2, width: '30%' },  // Название
                { index: 3, width: '15%' },  // Категория
                { index: 4, width: '15%' },  // Цена
                { index: 5, width: '12%' },  // Наличие
                { index: 6, width: '15%' }   // Действия
            ];
            
            // Apply column widths
            const headers = productsTable.querySelectorAll('th');
            columnWidths.forEach(column => {
                if (headers[column.index]) {
                    headers[column.index].style.width = column.width;
                }
            });
            
            // Ensure cells don't overflow
            const cells = productsTable.querySelectorAll('td');
            cells.forEach(cell => {
                cell.style.overflow = 'hidden';
                cell.style.textOverflow = 'ellipsis';
                cell.style.whiteSpace = 'nowrap';
            });
        }
    }
    
    // Apply styles to all table wrappers for mobile
    function applyMobileStyles() {
        // Find all table wrappers
        const wrappers = document.querySelectorAll('.product-table-wrapper, .category-table-wrapper, .order-table-wrapper, .report-table-wrapper, .user-table-wrapper');
        
        // Apply horizontal scroll styles
        for (let i = 0; i < wrappers.length; i++) {
            wrappers[i].style.overflowX = 'auto';
            wrappers[i].style.webkitOverflowScrolling = 'touch';
            wrappers[i].style.maxWidth = '100%';
        }
        
        // Find all tables
        const tables = document.querySelectorAll('.product-table, .order-table');
        
        // Set minimum width
        for (let j = 0; j < tables.length; j++) {
            tables[j].style.minWidth = '800px';
        }
    }
    
    // Apply specific column widths to product table for mobile
    function applyProductTableColumnWidths(table) {
        if (!table) return;
        
        // Define column widths
        const columnWidths = [
            { index: 0, width: '40px' },   // ID
            { index: 1, width: '80px' },   // Изображение
            { index: 2, width: '180px' },  // Название
            { index: 3, width: '100px' },  // Категория
            { index: 4, width: '80px' },   // Цена
            { index: 5, width: '80px' },   // Наличие
            { index: 6, width: '70px' }    // Действия
        ];
        
        // Get all headers
        const headers = table.querySelectorAll('th');
        
        // Apply widths to headers
        columnWidths.forEach(column => {
            if (headers[column.index]) {
                headers[column.index].style.width = column.width;
                headers[column.index].style.minWidth = column.width;
                headers[column.index].style.maxWidth = column.width;
            }
        });
    }
    
    // Align content with headers in product table
    function alignProductTableContent(table) {
        if (!table) return;
        
        // Get all cells
        const cells = table.querySelectorAll('td');
        
        // Apply text alignment
        cells.forEach((cell, index) => {
            const columnIndex = index % 7; // 7 columns total
            
            if (columnIndex === 0) { // ID
                cell.style.textAlign = 'center';
                cell.style.paddingRight = '0';
            } else if (columnIndex === 1) { // Image
                cell.style.textAlign = 'center';
                cell.style.paddingLeft = '0';
                cell.style.paddingRight = '0';
            } else if (columnIndex === 5 || columnIndex === 6) { // Наличие, Действия
                cell.style.textAlign = 'center';
            }
            
            // Make sure all cells handle overflow properly
            cell.style.overflow = 'hidden';
            cell.style.textOverflow = 'ellipsis';
            cell.style.whiteSpace = 'nowrap';
            cell.style.padding = '0.4rem 0.3rem';
        });
    }
    
    // Handle resize events
    window.addEventListener('resize', function() {
        const isMobileNow = window.innerWidth <= 767;
        
        if (isMobileNow) {
            applyMobileStyles();
            
            // Special handling for reports table on resize
            const reportsWrapper = document.querySelector('#reports .report-table-wrapper');
            const reportsTable = document.querySelector('#reports .report-table');
            
            if (reportsWrapper && reportsTable) {
                reportsWrapper.style.overflowX = 'auto';
                reportsWrapper.style.webkitOverflowScrolling = 'touch';
                reportsWrapper.style.maxWidth = '100%';
                
                reportsTable.style.minWidth = '660px';
                reportsTable.style.width = 'auto';
            }
            
            // Special handling for products table on resize
            const productsWrapper = document.querySelector('#products .product-table-wrapper');
            const productsTable = document.querySelector('#products .product-table');
            
            if (productsWrapper && productsTable) {
                productsWrapper.style.overflowX = 'auto';
                productsWrapper.style.webkitOverflowScrolling = 'touch';
                productsWrapper.style.maxWidth = '100%';
                
                productsTable.style.minWidth = '630px';
                productsTable.style.width = 'auto';
                productsTable.style.tableLayout = 'fixed';
                
                applyProductTableColumnWidths(productsTable);
                alignProductTableContent(productsTable);
            }
        } else {
            // Apply desktop styles on resize
            applyDesktopStyles();
        }
    });
    
    // Apply styles when switching tabs
    const tabLinks = document.querySelectorAll('.tab-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Apply styles after tab content loads
            setTimeout(function() {
                if (isMobile) {
                    applyMobileStyles();
                    
                    // Check if we're on the products tab
                    const productsTab = document.querySelector('#products.active');
                    if (productsTab) {
                        const productsTable = productsTab.querySelector('.product-table');
                        applyProductTableColumnWidths(productsTable);
                        alignProductTableContent(productsTable);
                    }
                } else {
                    // Apply desktop styles when switching tabs
                    applyDesktopStyles();
                }
            }, 300);
        });
    });
}); 