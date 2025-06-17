/**
 * JavaScript для улучшения работы мобильной версии админ-панели
 * Версия 1.3
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Mobile fix script loaded');
    
    // Функция для обеспечения горизонтального скролла таблиц
    function enhanceTableScrolling() {
        // Находим все таблицы
        const tables = document.querySelectorAll('table.table');
        
        tables.forEach(function(table) {
            // Проверяем, не обернута ли уже таблица
            if (!table.parentElement.classList.contains('table-responsive') && 
                !table.parentElement.classList.contains('product-table-wrapper') && 
                !table.parentElement.classList.contains('category-table-wrapper') && 
                !table.parentElement.classList.contains('order-table-wrapper') && 
                !table.parentElement.classList.contains('user-table-wrapper') && 
                !table.parentElement.classList.contains('report-table-wrapper')) {
                
                // Создаем обертку для таблицы
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                wrapper.style.cssText = 'overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100% !important; display: block !important;';
                
                // Оборачиваем таблицу
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
                
                console.log('Table wrapped for scrolling', table);
            }
        });
        
        // Принудительно включаем скролл для таблиц категорий, заказов и пользователей
        const categoryTables = document.querySelectorAll('#categories .category-table-wrapper');
        const orderTables = document.querySelectorAll('#orders .order-table-wrapper');
        const userTables = document.querySelectorAll('#users .user-table-wrapper');
        
        // Функция для применения стилей скролла
        function applyScrollStyles(wrappers) {
            wrappers.forEach(function(wrapper) {
                wrapper.style.overflowX = 'auto';
                wrapper.style.webkitOverflowScrolling = 'touch';
                wrapper.style.maxWidth = '100%';
                wrapper.style.width = '100%';
                wrapper.style.display = 'block';
                
                // Устанавливаем минимальную ширину для таблицы внутри обертки
                const table = wrapper.querySelector('table');
                if (table) {
                    table.style.minWidth = '600px';
                    table.style.width = 'auto';
                }
            });
        }
        
        // Применяем стили скролла к таблицам
        applyScrollStyles(categoryTables);
        applyScrollStyles(orderTables);
        applyScrollStyles(userTables);
    }
    
    // Применяем улучшения для таблиц
    enhanceTableScrolling();
    
    // Также применяем улучшения после загрузки AJAX-контента
    // (если в админке есть динамически загружаемые таблицы)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    // Проверяем, добавлены ли новые таблицы
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const node = mutation.addedNodes[i];
                        if (node.nodeType === 1) { // Только элементы
                            const tables = node.querySelectorAll('table.table');
                            if (tables.length > 0) {
                                enhanceTableScrolling();
                                break;
                            }
                        }
                    }
                }
            });
        });
        
        // Наблюдаем за изменениями в DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Обрабатываем изменение размера окна
    window.addEventListener('resize', function() {
        // Повторно применяем улучшения при изменении размера окна
        enhanceTableScrolling();
    });
}); 