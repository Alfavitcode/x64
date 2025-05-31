/**
 * Функция для инициализации категорий и подкатегорий в каталоге
 */
function initCategoryToggle() {
    console.log('Инициализация категорий и подкатегорий...');
    
    const toggles = document.querySelectorAll('.subcategory-toggle');
    
    toggles.forEach(toggle => {
        // Удаляем старые обработчики событий, чтобы избежать дублирования
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);
        
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Получаем родительский элемент (li.category-item)
            const categoryItem = this.closest('.category-item');
            
            // Находим меню подкатегорий внутри родительского элемента
            const subcategoriesMenu = categoryItem.querySelector('.subcategories-menu');
            
            // Переключаем отображение меню
            if (!subcategoriesMenu.classList.contains('show')) {
                // Скрываем все другие открытые меню подкатегорий
                document.querySelectorAll('.subcategories-menu').forEach(menu => {
                    if (menu !== subcategoriesMenu) {
                        menu.classList.remove('show');
                        const otherToggle = menu.closest('.category-item').querySelector('.subcategory-toggle i');
                        if (otherToggle) {
                            otherToggle.className = 'fas fa-chevron-down';
                        }
                    }
                });
                
                // Показываем текущее меню
                subcategoriesMenu.classList.add('show');
                this.querySelector('i').className = 'fas fa-chevron-up';
            } else {
                // Скрываем текущее меню
                subcategoriesMenu.classList.remove('show');
                this.querySelector('i').className = 'fas fa-chevron-down';
            }
        });
    });
    
    // Убираем автоматическое открытие подкатегорий активной категории при загрузке
    document.querySelectorAll('.subcategories-menu').forEach(menu => {
        menu.classList.remove('show');
        const toggle = menu.closest('.category-item').querySelector('.subcategory-toggle i');
        if (toggle) {
            toggle.className = 'fas fa-chevron-down';
        }
    });
    
    // Подсвечиваем активную подкатегорию, но не открываем меню
    const activeSubcategoryLink = document.querySelector('.subcategory-link.active-subcategory');
    if (activeSubcategoryLink) {
        const parentMenu = activeSubcategoryLink.closest('.subcategories-menu');
        if (parentMenu) {
            parentMenu.classList.add('show');
            const toggle = parentMenu.closest('.category-item').querySelector('.subcategory-toggle i');
            if (toggle) {
                toggle.className = 'fas fa-chevron-up';
            }
        }
    }
}

/**
 * Обработчик категорий и подкатегорий при AJAX-загрузке
 */
function handleCategoriesAfterAjax() {
    console.log('Обработка категорий после AJAX-загрузки...');
    
    // Предотвращаем автоматическое открытие подкатегорий при загрузке
    setTimeout(function() {
        document.querySelectorAll('.subcategories-menu').forEach(menu => {
            // Проверяем, нужно ли оставить это меню открытым
            if (menu.dataset.keepOpen === 'true') {
                // Оставляем меню открытым
                menu.classList.add('show');
                // Сбрасываем флаг, чтобы при следующем вызове функции он не учитывался
                menu.dataset.keepOpen = 'false';
                
                // Устанавливаем правильную иконку стрелки
                const toggle = menu.closest('.category-item').querySelector('.subcategory-toggle i');
                if (toggle) {
                    toggle.className = 'fas fa-chevron-up';
                }
            } else {
                // Закрываем меню
                menu.classList.remove('show');
                
                // Устанавливаем правильную иконку стрелки
                const toggle = menu.closest('.category-item').querySelector('.subcategory-toggle i');
                if (toggle) {
                    toggle.className = 'fas fa-chevron-down';
                }
            }
        });
    }, 100);
}

/**
 * Инициализация фильтрации товаров
 */
function initFilterForm() {
    const filterForm = document.getElementById('ajax-filter-form');
    const applyButton = document.getElementById('apply-filters');
    const resetButton = document.getElementById('reset-filters');
    
    if (filterForm && applyButton && resetButton) {
        console.log('Инициализация формы фильтрации...');
        
        // Обработчик события для применения фильтров
        applyButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Получаем данные формы
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(window.location.search);
            
            // Добавляем все поля формы в параметры
            for (const [key, value] of formData.entries()) {
                if (value) { // Только если значение не пустое
                    params.set(key, value);
                } else {
                    params.delete(key);
                }
            }
            
            // Обновляем URL и перезагружаем страницу
            window.location.href = window.location.pathname + '?' + params.toString();
        });
        
        // Обработчик события для сброса фильтров
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Сохраняем текущую категорию и подкатегорию перед сбросом
            const categoryParam = new URLSearchParams(window.location.search).get('category');
            const subcategoryParam = new URLSearchParams(window.location.search).get('subcategory');
            
            // Создаем новые параметры только с категорией и подкатегорией
            const params = new URLSearchParams();
            
            if (categoryParam) {
                params.set('category', categoryParam);
                
                if (subcategoryParam) {
                    params.set('subcategory', subcategoryParam);
                }
            }
            
            // Обновляем URL и перезагружаем страницу
            window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        });
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initCategoryToggle();
    initFilterForm();
    
    // Обработка активных категорий и подкатегорий
    const currentUrl = new URL(window.location.href);
    const categoryParam = currentUrl.searchParams.get('category');
    const subcategoryParam = currentUrl.searchParams.get('subcategory');
    
    if (categoryParam) {
        // Ищем соответствующую категорию и делаем её активной
        document.querySelectorAll('.category-item').forEach(item => {
            const link = item.querySelector('.d-flex > a');
            if (link && link.href.includes(`category=${categoryParam}`)) {
                item.classList.add('active');
                
                // Если указана подкатегория, ищем и активируем её
                if (subcategoryParam) {
                    const subcatLink = item.querySelector(`.subcategory-link[href*="subcategory=${subcategoryParam}"]`);
                    if (subcatLink) {
                        subcatLink.classList.add('active-subcategory');
                        
                        // Открываем меню подкатегорий
                        const menu = subcatLink.closest('.subcategories-menu');
                        if (menu) {
                            menu.classList.add('show');
                            const toggle = item.querySelector('.subcategory-toggle i');
                            if (toggle) {
                                toggle.className = 'fas fa-chevron-up';
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Если нет категории, активируем "Все категории"
        const allCategoriesItem = document.querySelector('.list-group-item:first-child');
        if (allCategoriesItem) {
            allCategoriesItem.classList.add('active');
        }
    }
}); 