/**
 * Скрипт для исправления проблем с фильтрацией по категориям и подкатегориям
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация исправлений для фильтрации категорий и подкатегорий...');
    
    // Функция для инициализации обработчиков ссылок подкатегорий
    function initSubcategoryLinks() {
        // Выбираем все ссылки подкатегорий
        const subcategoryLinks = document.querySelectorAll('.subcategory-link');
        console.log('Найдено ссылок подкатегорий:', subcategoryLinks.length);
        
        subcategoryLinks.forEach(link => {
            // Удаляем старые обработчики событий для предотвращения дублирования
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            // Добавляем новый обработчик клика
            newLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Получаем URL подкатегории
                const subcategoryUrl = this.getAttribute('href');
                console.log('Клик по подкатегории:', subcategoryUrl);
                
                // Показываем индикатор загрузки
                const catalogContent = document.querySelector('.catalog-content');
                if (catalogContent) {
                    catalogContent.style.opacity = '0.5';
                }
                
                // Добавляем параметр ajax=true для AJAX-запроса
                const ajaxUrl = subcategoryUrl + (subcategoryUrl.includes('?') ? '&' : '?') + 'ajax=true';
                
                // Отправляем AJAX-запрос
                fetch(ajaxUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Ошибка сети при обновлении каталога');
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (catalogContent) {
                            // Обновляем содержимое каталога
                            catalogContent.innerHTML = html;
                            catalogContent.style.opacity = '1';
                            
                            // Обновляем URL без перезагрузки страницы
                            window.history.pushState({}, '', subcategoryUrl);
                            
                            // Переинициализируем все необходимые обработчики
                            setTimeout(() => {
                                // Вызываем функции инициализации из других файлов
                                if (typeof initCategoryToggle === 'function') {
                                    initCategoryToggle();
                                }
                                
                                if (typeof initFilterForm === 'function') {
                                    initFilterForm();
                                }
                                
                                if (typeof handleCategoriesAfterAjax === 'function') {
                                    handleCategoriesAfterAjax();
                                }
                                
                                if (typeof restoreFilterParams === 'function') {
                                    restoreFilterParams();
                                }
                                
                                // Повторно инициализируем ссылки подкатегорий
                                initSubcategoryLinks();
                                
                                // Активируем текущую подкатегорию
                                highlightActiveSubcategory();
                            }, 100);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при обновлении каталога:', error);
                        if (catalogContent) {
                            catalogContent.style.opacity = '1';
                        }
                    });
            });
        });
    }
    
    // Функция для подсветки активной подкатегории
    function highlightActiveSubcategory() {
        // Получаем текущие параметры URL
        const currentUrl = new URL(window.location.href);
        const categoryParam = currentUrl.searchParams.get('category');
        const subcategoryParam = currentUrl.searchParams.get('subcategory');
        
        if (categoryParam && subcategoryParam) {
            // Находим все ссылки подкатегорий
            document.querySelectorAll('.subcategory-link').forEach(link => {
                // Сбрасываем стили для всех ссылок
                link.classList.remove('active-subcategory');
                link.style.color = '';
                link.style.fontWeight = '';
                
                // Проверяем, соответствует ли ссылка текущим параметрам
                if (link.href.includes(`category=${categoryParam}`) && 
                    link.href.includes(`subcategory=${subcategoryParam}`)) {
                    // Подсвечиваем активную подкатегорию
                    link.classList.add('active-subcategory');
                    link.style.color = '#2345c2';
                    link.style.fontWeight = '600';
                    
                    // Убеждаемся, что меню подкатегорий открыто
                    const parentMenu = link.closest('.subcategories-menu');
                    if (parentMenu) {
                        parentMenu.classList.add('show');
                        parentMenu.style.display = 'block';
                        parentMenu.style.height = 'auto';
                        parentMenu.style.opacity = '1';
                        
                        // Изменяем иконку переключателя
                        const categoryItem = parentMenu.closest('.category-item');
                        if (categoryItem) {
                            const toggle = categoryItem.querySelector('.subcategory-toggle i');
                            if (toggle) {
                                toggle.className = 'fas fa-chevron-up';
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Инициализируем обработчики ссылок подкатегорий
    initSubcategoryLinks();
    
    // Подсвечиваем активную подкатегорию при загрузке страницы
    highlightActiveSubcategory();
}); 