/**
 * Файл для исправления проблемы с бледными цветами в каталоге
 * Этот скрипт загружается после всех остальных скриптов и применяет наши стили повторно
 */

// Функция для принудительного применения наших стилей
function fixCatalogStyles() {
    console.log('Применяю исправление стилей каталога...');
    
    // Стили для заголовков категорий и фильтров
    const headerStyles = {
        'background': '#2345c2',
        'background-image': 'linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%)',
        'background-color': '#2345c2',
        'color': 'white',
        'border': 'none',
        'padding': '15px 20px',
        'font-weight': '700',
        'border-radius': '0',
        'box-shadow': '0 3px 8px rgba(28, 53, 163, 0.4)'
    };
    
    // Стили для заголовков внутри блоков
    const titleStyles = {
        'color': 'white',
        'font-weight': '700',
        'margin': '0',
        'text-shadow': '0 1px 3px rgba(0, 0, 0, 0.15)',
        'letter-spacing': '0.5px'
    };
    
    // Стили для кнопки Применить фильтры
    const applyButtonStyles = {
        'background': '#2345c2',
        'background-image': 'linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%)',
        'background-color': '#2345c2',
        'border': 'none',
        'color': 'white',
        'box-shadow': '0 4px 12px rgba(28, 53, 163, 0.4)',
        'font-weight': '600'
    };
    
    // Применяем стили к заголовкам
    const headers = document.querySelectorAll('.card-header, .category-header, .filter-header');
    headers.forEach(header => {
        Object.keys(headerStyles).forEach(key => {
            header.style.setProperty(key, headerStyles[key], 'important');
        });
    });
    
    // Применяем стили к заголовкам внутри блоков
    const titles = document.querySelectorAll('.widget-title');
    titles.forEach(title => {
        Object.keys(titleStyles).forEach(key => {
            title.style.setProperty(key, titleStyles[key], 'important');
        });
    });
    
    // Применяем стили к кнопке Применить фильтры
    const applyButton = document.getElementById('apply-filters');
    if (applyButton) {
        Object.keys(applyButtonStyles).forEach(key => {
            applyButton.style.setProperty(key, applyButtonStyles[key], 'important');
        });
    }
    
    // Убеждаемся, что меню подкатегорий для активной категории отображается
    const currentUrl = new URL(window.location.href);
    const categoryParam = currentUrl.searchParams.get('category');
    
    if (categoryParam) {
        document.querySelectorAll('.category-item').forEach(item => {
            const link = item.querySelector('.d-flex > a');
            if (link && link.href.includes(`category=${categoryParam}`)) {
                const menu = item.querySelector('.subcategories-menu');
                if (menu) {
                    menu.classList.add('show');
                    menu.style.display = 'block';
                    menu.style.height = 'auto';
                    menu.style.opacity = '1';
                }
            }
        });
    }
}

// Функция для перехвата GSAP анимаций
function interceptGSAPAnimations() {
    // Проверяем, загружен ли GSAP
    if (typeof gsap !== 'undefined') {
        console.log('GSAP обнаружен, перехватываю анимации...');
        
        // Сохраняем оригинальный метод from
        const originalFrom = gsap.from;
        
        // Перехватываем метод from
        gsap.from = function(targets, vars) {
            // Проверяем, не является ли цель нашими элементами
            if (typeof targets === 'string' && 
                (targets.includes('sidebar-widget') || 
                 targets.includes('card-header') || 
                 targets.includes('widget-title'))) {
                
                console.log('Перехвачена анимация для:', targets);
                
                // Модифицируем параметры анимации
                if (vars.opacity !== undefined && vars.opacity < 1) {
                    vars.opacity = 0.9; // Минимальная прозрачность
                }
                
                // Добавляем обработчик завершения анимации
                const originalOnComplete = vars.onComplete;
                vars.onComplete = function() {
                    // Восстанавливаем наши стили
                    setTimeout(fixCatalogStyles, 10);
                    
                    // Вызываем оригинальный обработчик, если он был
                    if (typeof originalOnComplete === 'function') {
                        originalOnComplete.apply(this, arguments);
                    }
                };
            }
            
            // Вызываем оригинальный метод
            return originalFrom.apply(gsap, arguments);
        };
        
        // Аналогично перехватываем метод to
        const originalTo = gsap.to;
        gsap.to = function(targets, vars) {
            // Проверяем, не является ли цель нашими элементами
            if (typeof targets === 'string' && 
                (targets.includes('sidebar-widget') || 
                 targets.includes('card-header') || 
                 targets.includes('widget-title'))) {
                
                console.log('Перехвачена анимация to для:', targets);
                
                // Модифицируем параметры анимации
                if (vars.opacity !== undefined && vars.opacity < 1) {
                    vars.opacity = 0.9; // Минимальная прозрачность
                }
                
                // Добавляем обработчик завершения анимации
                const originalOnComplete = vars.onComplete;
                vars.onComplete = function() {
                    // Восстанавливаем наши стили
                    setTimeout(fixCatalogStyles, 10);
                    
                    // Вызываем оригинальный обработчик, если он был
                    if (typeof originalOnComplete === 'function') {
                        originalOnComplete.apply(this, arguments);
                    }
                };
            }
            
            // Вызываем оригинальный метод
            return originalTo.apply(gsap, arguments);
        };
    }
}

// Функция для повторного применения стилей после анимаций GSAP
function setupStyleFixer() {
    // Перехватываем GSAP анимации
    interceptGSAPAnimations();
    
    // Применяем исправления сразу после загрузки страницы
    fixCatalogStyles();
    
    // Применяем исправления после небольшой задержки, чтобы перезаписать стили после анимаций
    setTimeout(fixCatalogStyles, 100);
    setTimeout(fixCatalogStyles, 300);
    setTimeout(fixCatalogStyles, 500);
    setTimeout(fixCatalogStyles, 1000);
    
    // Применяем исправления периодически
    setInterval(fixCatalogStyles, 2000);
    
    // Обработчик события прокрутки для повторного применения стилей
    window.addEventListener('scroll', function() {
        fixCatalogStyles();
    });
    
    // Обработчик события изменения размера окна
    window.addEventListener('resize', function() {
        fixCatalogStyles();
    });
    
    // Обработчик события изменения DOM
    const observer = new MutationObserver(function(mutations) {
        fixCatalogStyles();
    });
    
    // Наблюдаем за изменениями в DOM
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Запускаем исправление стилей как можно раньше
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupStyleFixer);
} else {
    setupStyleFixer();
}

// Запускаем исправление стилей после полной загрузки страницы
window.addEventListener('load', fixCatalogStyles); 