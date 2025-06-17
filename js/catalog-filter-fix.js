/**
 * Скрипт для исправления проблем с фильтрацией по типам товаров (чекбоксы)
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация исправлений для фильтрации по типам товаров...');
    
    // Функция для инициализации чекбоксов фильтров
    function initFilterCheckboxes() {
        const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
        console.log('Найдено чекбоксов фильтров:', filterCheckboxes.length);
        
        // Если чекбоксы найдены, добавляем обработчики
        if (filterCheckboxes.length > 0) {
            filterCheckboxes.forEach(checkbox => {
                // Удаляем старые обработчики событий для предотвращения дублирования
                const newCheckbox = checkbox.cloneNode(true);
                checkbox.parentNode.replaceChild(newCheckbox, checkbox);
                
                // Добавляем новый обработчик изменения
                newCheckbox.addEventListener('change', function() {
                    console.log('Изменение чекбокса:', this.id, 'состояние:', this.checked);
                    
                    // Подсветка формы фильтра при изменении чекбокса
                    const formCheck = this.closest('.form-check');
                    if (formCheck) {
                        if (this.checked) {
                            formCheck.style.backgroundColor = 'rgba(78, 115, 223, 0.1)';
                        } else {
                            formCheck.style.backgroundColor = 'transparent';
                        }
                    }
                });
            });
        }
        
        // Инициализация кнопки применения фильтров
        const applyButton = document.getElementById('apply-filters');
        if (applyButton) {
            // Удаляем старые обработчики событий
            const newApplyButton = applyButton.cloneNode(true);
            applyButton.parentNode.replaceChild(newApplyButton, applyButton);
            
            // Добавляем новый обработчик клика
            newApplyButton.addEventListener('click', function() {
                console.log('Клик по кнопке применения фильтров');
                
                // Получаем форму фильтров
                const filterForm = document.getElementById('ajax-filter-form');
                if (!filterForm) {
                    console.error('Форма фильтров не найдена!');
                    return;
                }
                
                // Собираем данные формы
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(window.location.search);
                
                // Сначала удаляем старые параметры типа товара
                params.delete('type[]');
                
                // Добавляем все поля формы в параметры
                for (const [key, value] of formData.entries()) {
                    console.log(`Параметр формы: ${key} = ${value}`);
                    if (value) { // Только если значение не пустое
                        if (key === 'type[]') {
                            // Для чекбоксов добавляем все выбранные значения
                            params.append(key, value);
                        } else {
                            params.set(key, value);
                        }
                    } else if (key !== 'type[]') { // Не удаляем type[], они обрабатываются отдельно
                        params.delete(key);
                    }
                }
                
                // Добавляем текущее значение сортировки
                const sortSelect = document.getElementById('sort-select');
                if (sortSelect) {
                    params.set('sort', sortSelect.value);
                }
                
                // Добавляем параметр ajax=true для AJAX-запроса
                params.set('ajax', 'true');
                
                // Показываем индикатор загрузки
                const catalogContent = document.querySelector('.catalog-content');
                if (catalogContent) {
                    catalogContent.style.opacity = '0.5';
                }
                
                // Выводим отладочную информацию о параметрах
                console.log('Отправляемые параметры:', params.toString());
                
                // Отправляем AJAX-запрос
                fetch(`${window.location.pathname}?${params.toString()}`)
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
                            const newUrl = `${window.location.pathname}?${params.toString().replace('ajax=true', '')}`;
                            window.history.pushState({}, '', newUrl);
                            
                            // Переинициализируем обработчики
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
                                
                                // Повторно инициализируем чекбоксы фильтров
                                initFilterCheckboxes();
                                
                                // Восстанавливаем состояние фильтров
                                restoreFilterState();
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
        }
    }
    
    // Функция для восстановления состояния фильтров
    function restoreFilterState() {
        console.log('Восстановление состояния фильтров...');
        
        // Получаем текущие параметры URL
        const urlParams = new URLSearchParams(window.location.search);
        
        // Восстанавливаем значения полей цены
        const minPriceInput = document.getElementById('min-price');
        const maxPriceInput = document.getElementById('max-price');
        
        if (minPriceInput) {
            const minPrice = urlParams.get('min_price');
            if (minPrice) {
                minPriceInput.value = minPrice;
                console.log('Восстановлена минимальная цена:', minPrice);
            }
        }
        
        if (maxPriceInput) {
            const maxPrice = urlParams.get('max_price');
            if (maxPrice) {
                maxPriceInput.value = maxPrice;
                console.log('Восстановлена максимальная цена:', maxPrice);
            }
        }
        
        // Восстанавливаем значения чекбоксов типов товаров
        const typeValues = urlParams.getAll('type[]');
        
        if (typeValues.length > 0) {
            console.log('Восстановление выбранных типов товаров:', typeValues.join(', '));
            
            // Отмечаем соответствующие чекбоксы
            const checkboxes = document.querySelectorAll('.filter-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = typeValues.includes(checkbox.value);
                
                // Подсвечиваем блок чекбокса
                const formCheck = checkbox.closest('.form-check');
                if (formCheck) {
                    if (checkbox.checked) {
                        formCheck.style.backgroundColor = 'rgba(78, 115, 223, 0.1)';
                    } else {
                        formCheck.style.backgroundColor = 'transparent';
                    }
                }
            });
        } else {
            // Если нет выбранных типов, снимаем все чекбоксы
            const checkboxes = document.querySelectorAll('.filter-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                
                // Сбрасываем подсветку
                const formCheck = checkbox.closest('.form-check');
                if (formCheck) {
                    formCheck.style.backgroundColor = 'transparent';
                }
            });
        }
    }
    
    // Инициализируем чекбоксы фильтров
    initFilterCheckboxes();
    
    // Восстанавливаем состояние фильтров при загрузке страницы
    restoreFilterState();
}); 