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
    
    // Подсвечиваем активную подкатегорию и открываем соответствующее меню
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
    
    // Предотвращаем автоматическое закрытие подкатегорий для активной категории
    setTimeout(function() {
        // Получаем параметры URL для определения текущей категории
        const currentUrl = new URL(window.location.href);
        const categoryParam = currentUrl.searchParams.get('category');
        
        document.querySelectorAll('.subcategories-menu').forEach(menu => {
            const categoryItem = menu.closest('.category-item');
            const categoryLink = categoryItem ? categoryItem.querySelector('.d-flex > a') : null;
            const isActiveCategory = categoryLink && categoryLink.href.includes(`category=${categoryParam}`);
            
            // Если это активная категория или меню помечено для сохранения открытым
            if (isActiveCategory || menu.dataset.keepOpen === 'true') {
                // Оставляем меню открытым
                menu.classList.add('show');
                // Устанавливаем display: block напрямую для гарантии
                menu.style.display = 'block';
                menu.style.height = 'auto';
                menu.style.opacity = '1';
                
                // Сбрасываем флаг, чтобы при следующем вызове функции он не учитывался
                if (menu.dataset.keepOpen === 'true') {
                    menu.dataset.keepOpen = 'false';
                }
                
                // Устанавливаем правильную иконку стрелки
                const toggle = categoryItem.querySelector('.subcategory-toggle i');
                if (toggle) {
                    toggle.className = 'fas fa-chevron-up';
                }
            } else {
                // Для неактивных категорий закрываем меню только если оно не имеет класса show
                if (!menu.classList.contains('show')) {
                    menu.style.display = 'none';
                    
                    // Устанавливаем правильную иконку стрелки
                    const toggle = categoryItem.querySelector('.subcategory-toggle i');
                    if (toggle) {
                        toggle.className = 'fas fa-chevron-down';
                    }
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
    const sortSelect = document.getElementById('sort-select');
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    const hiddenSortField = document.getElementById('hidden-sort');
    
    if (filterForm && applyButton && resetButton) {
        console.log('Инициализация формы фильтрации...');
        
        // Очищаем существующие обработчики событий
        const newApplyButton = applyButton.cloneNode(true);
        applyButton.parentNode.replaceChild(newApplyButton, applyButton);
        
        const newResetButton = resetButton.cloneNode(true);
        resetButton.parentNode.replaceChild(newResetButton, resetButton);
        
        // Обработчик события для применения фильтров
        newApplyButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Применение фильтров...');
            
            // Получаем данные формы
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(window.location.search);
            
            // Сначала удаляем старые параметры типа товара
            params.delete('type[]');
            
            // Добавляем все поля формы в параметры
            for (const [key, value] of formData.entries()) {
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
            if (sortSelect) {
                params.set('sort', sortSelect.value);
                
                // Обновляем скрытое поле для сортировки, если оно существует
                if (hiddenSortField) {
                    hiddenSortField.value = sortSelect.value;
                }
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
                            initCategoryToggle();
                            initFilterForm();
                            handleCategoriesAfterAjax();
                            
                            // Восстанавливаем состояние фильтров
                            restoreFilterParams();
                            
                            // Добавляем классы активности для чекбоксов
                            const checkboxes = document.querySelectorAll('.filter-checkbox');
                            checkboxes.forEach(checkbox => {
                                const formCheck = checkbox.closest('.form-check');
                                if (formCheck && checkbox.checked) {
                                    formCheck.style.backgroundColor = 'rgba(78, 115, 223, 0.1)';
                                }
                            });
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
        
        // Обработчик события для сброса фильтров
        newResetButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Сброс фильтров...');
            
            // Сохраняем текущую категорию и подкатегорию перед сбросом
            const params = new URLSearchParams();
            const currentParams = new URLSearchParams(window.location.search);
            
            // Сохраняем основные параметры страницы
            const keysToPreserve = ['category', 'subcategory', 'sort'];
            for (const key of keysToPreserve) {
                const value = currentParams.get(key);
                if (value) {
                    params.set(key, value);
                }
            }
            
            // Сбрасываем значения в форме
            if (minPriceInput) minPriceInput.value = '';
            if (maxPriceInput) maxPriceInput.value = '';
            
            // Снимаем все чекбоксы
            const checkboxes = document.querySelectorAll('.filter-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                const formCheck = checkbox.closest('.form-check');
                if (formCheck) {
                    formCheck.style.backgroundColor = 'transparent';
                }
            });
            
            // Важно! Удаляем параметры фильтрации из URL
            params.delete('min_price');
            params.delete('max_price');
            params.delete('type[]');
            
            // Добавляем параметр ajax=true для AJAX-запроса
            params.set('ajax', 'true');
            
            // Показываем индикатор загрузки
            const catalogContent = document.querySelector('.catalog-content');
            if (catalogContent) {
                catalogContent.style.opacity = '0.5';
            }
            
            // Выводим отладочную информацию
            console.log('Параметры после сброса:', params.toString());
            
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
                            initCategoryToggle();
                            initFilterForm();
                            handleCategoriesAfterAjax();
                            restoreFilterParams();
                        }, 100);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при сбросе фильтров:', error);
                    if (catalogContent) {
                        catalogContent.style.opacity = '1';
                    }
                });
        });
    }
    
    // Инициализация обработчика сортировки
    if (sortSelect) {
        initSortSelect(sortSelect);
    }
    
    // Проверяем наличие AJAX-селекта сортировки
    const ajaxSortSelect = document.getElementById('ajax-sort-select');
    if (ajaxSortSelect) {
        initSortSelect(ajaxSortSelect);
    }

    // Инициализация полей ввода цены
    if (minPriceInput && maxPriceInput) {
        // Очищаем существующие обработчики событий
        const newMinPriceInput = minPriceInput.cloneNode(true);
        minPriceInput.parentNode.replaceChild(newMinPriceInput, minPriceInput);
        
        const newMaxPriceInput = maxPriceInput.cloneNode(true);
        maxPriceInput.parentNode.replaceChild(newMaxPriceInput, maxPriceInput);
        
        // Обработчик для валидации мин/макс цены
        newMinPriceInput.addEventListener('change', function() {
            console.log('Изменение минимальной цены:', this.value);
            if (newMaxPriceInput.value && parseInt(this.value) > parseInt(newMaxPriceInput.value)) {
                this.value = newMaxPriceInput.value;
            }
        });
        
        newMaxPriceInput.addEventListener('change', function() {
            console.log('Изменение максимальной цены:', this.value);
            if (newMinPriceInput.value && parseInt(this.value) < parseInt(newMinPriceInput.value)) {
                this.value = newMinPriceInput.value;
            }
        });
        
        // Активируем кнопку применить при изменении цены
        newMinPriceInput.addEventListener('input', function() {
            const applyBtn = document.getElementById('apply-filters');
            if (applyBtn) {
                applyBtn.classList.add('btn-pulse');
                setTimeout(() => {
                    applyBtn.classList.remove('btn-pulse');
                }, 1000);
            }
        });
        
        newMaxPriceInput.addEventListener('input', function() {
            const applyBtn = document.getElementById('apply-filters');
            if (applyBtn) {
                applyBtn.classList.add('btn-pulse');
                setTimeout(() => {
                    applyBtn.classList.remove('btn-pulse');
                }, 1000);
            }
        });
    }

    // Инициализация чекбоксов фильтров
    const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
    if (filterCheckboxes.length > 0) {
        filterCheckboxes.forEach(checkbox => {
            // Очищаем существующие обработчики событий
            const newCheckbox = checkbox.cloneNode(true);
            checkbox.parentNode.replaceChild(newCheckbox, checkbox);
            
            newCheckbox.addEventListener('change', function() {
                console.log('Изменение чекбокса:', this.id, 'состояние:', this.checked);
                const applyBtn = document.getElementById('apply-filters');
                if (applyBtn) {
                    applyBtn.classList.add('btn-pulse');
                    setTimeout(() => {
                        applyBtn.classList.remove('btn-pulse');
                    }, 1000);
                }
                
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
}

/**
 * Инициализация обработчика для селекта сортировки
 */
function initSortSelect(selectElement) {
    if (!selectElement) return;
    
    // Удаляем предыдущие обработчики
    const newSelect = selectElement.cloneNode(true);
    selectElement.parentNode.replaceChild(newSelect, selectElement);
    
    // Сохраняем текущее значение
    const initialValue = newSelect.value;
    console.log('Начальное значение селекта сортировки:', initialValue);
    
    newSelect.addEventListener('change', function() {
        // Запоминаем выбранное значение
        const selectedValue = this.value;
        console.log('Выбрано значение сортировки:', selectedValue);
        
        // Сохраняем значение в атрибут для последующего восстановления
        this.setAttribute('data-current', selectedValue);
        
        // Обновляем скрытое поле сортировки, если оно есть
        const hiddenSort = document.getElementById('hidden-sort');
        if (hiddenSort) {
            hiddenSort.value = selectedValue;
        }
        
        // Получаем текущие параметры URL
        const params = new URLSearchParams(window.location.search);
        
        // Обновляем параметр сортировки
        params.set('sort', selectedValue);
        
        // Получаем данные формы фильтров
        const filterForm = document.getElementById('ajax-filter-form');
        if (filterForm) {
            // Сначала удаляем старые параметры типа товара
            params.delete('type[]');
            
            const formData = new FormData(filterForm);
            for (const [key, value] of formData.entries()) {
                if (value) {
                    if (key === 'type[]') {
                        // Для чекбоксов добавляем все выбранные значения
                        params.append(key, value);
                    } else {
                        params.set(key, value);
                    }
                }
            }
        }

        // Добавляем параметр ajax=true для AJAX-запроса
        params.set('ajax', 'true');
        
        // Показываем индикатор загрузки
        const catalogContent = document.querySelector('.catalog-content');
        if (catalogContent) {
            catalogContent.style.opacity = '0.5';
        }
        
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
                        initCategoryToggle();
                        initFilterForm();
                        handleCategoriesAfterAjax();
                        
                        // Устанавливаем ранее выбранное значение в новый селект
                        const newSortSelect = document.getElementById('sort-select');
                        if (newSortSelect) {
                            newSortSelect.value = selectedValue;
                            console.log('Установлено значение для нового селекта:', selectedValue);
                            
                            // Сохраняем выбранное значение в атрибут
                            newSortSelect.setAttribute('data-current', selectedValue);
                        }
                        
                        // Восстанавливаем все параметры фильтрации
                        restoreFilterParams();
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

/**
 * Восстанавливает выбранное значение сортировки
 */
function restoreSortSelection() {
    // Получаем селект сортировки
    const sortSelect = document.getElementById('sort-select');
    if (!sortSelect) return;
    
    // Получаем значение из URL
    const urlParams = new URLSearchParams(window.location.search);
    const sortParam = urlParams.get('sort');
    
    // Получаем текущее значение из атрибута data-current
    const currentSort = sortSelect.getAttribute('data-current');
    
    // Устанавливаем значение сортировки
    if (sortParam) {
        console.log('Восстановление значения сортировки из URL:', sortParam);
        sortSelect.value = sortParam;
    } else if (currentSort) {
        console.log('Восстановление значения сортировки из атрибута:', currentSort);
        sortSelect.value = currentSort;
    } else {
        console.log('Используем значение по умолчанию: popular');
        sortSelect.value = 'popular';
    }
    
    // Принудительно обновляем скрытое поле сортировки, если оно существует
    const hiddenSort = document.getElementById('hidden-sort');
    if (hiddenSort) {
        hiddenSort.value = sortSelect.value;
    }
    
    // Добавляем дополнительный обработчик для исправления работы селекта на мобильных устройствах
    sortSelect.addEventListener('focus', function() {
        // Для мобильных устройств нужно заново инициализировать селект при фокусе
        if (window.innerWidth < 768) {
            this.blur();
            this.focus();
        }
    });
    
    // Ещё один способ гарантировать работу селекта - добавить обработчик для каждого варианта
    Array.from(sortSelect.options).forEach(option => {
        option.addEventListener('click', function() {
            console.log('Выбран вариант сортировки через клик на опцию:', this.value);
            sortSelect.value = this.value;
            // Имитируем событие изменения
            const event = new Event('change');
            sortSelect.dispatchEvent(event);
        });
    });
    
    // Защита от случаев, когда сортировка не сохраняется
    const originalSelectedValue = sortSelect.value;
    
    // Добавляем обработчик на change для сохранения значения в data-атрибут
    sortSelect.addEventListener('change', function() {
        console.log('Сохраняем выбранное значение сортировки:', this.value);
        this.setAttribute('data-current', this.value);
        
        // Обновляем скрытое поле
        const hiddenSort = document.getElementById('hidden-sort');
        if (hiddenSort) {
            hiddenSort.value = this.value;
        }
    });
    
    // Если значение изменилось, но обработчик не сработал, принудительно устанавливаем его
    if (sortSelect.value !== originalSelectedValue) {
        console.log('Принудительно обновляем data-current после установки значения');
        sortSelect.setAttribute('data-current', sortSelect.value);
    }
}

/**
 * Восстанавливает все параметры фильтрации из скрытых полей после AJAX-обновления
 */
function restoreFilterParams() {
    console.log('Восстановление параметров фильтрации из хранилища...');
    
    // Восстанавливаем значения полей цены
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    const storedMinPrice = document.getElementById('current-min-price');
    const storedMaxPrice = document.getElementById('current-max-price');
    
    if (minPriceInput && storedMinPrice) {
        minPriceInput.value = storedMinPrice.value;
        console.log('Восстановлена минимальная цена:', storedMinPrice.value);
    }
    
    if (maxPriceInput && storedMaxPrice) {
        maxPriceInput.value = storedMaxPrice.value;
        console.log('Восстановлена максимальная цена:', storedMaxPrice.value);
    }
    
    // Восстанавливаем значения чекбоксов типов товаров
    const typeFilters = document.querySelectorAll('.current-type-filter');
    const filterTypes = Array.from(typeFilters).map(filter => filter.value);
    
    if (filterTypes.length > 0) {
        console.log('Найдены сохраненные типы товаров:', filterTypes.join(', '));
        
        // Отмечаем соответствующие чекбоксы
        const checkboxes = document.querySelectorAll('.filter-checkbox');
        checkboxes.forEach(checkbox => {
            if (filterTypes.includes(checkbox.value)) {
                checkbox.checked = true;
                console.log('Отмечен чекбокс:', checkbox.id, checkbox.value);
                
                // Подсвечиваем блок чекбокса
                const formCheck = checkbox.closest('.form-check');
                if (formCheck) {
                    formCheck.style.backgroundColor = 'rgba(78, 115, 223, 0.1)';
                }
            }
        });
    }
    
    // Восстанавливаем значение сортировки
    restoreSortSelection();
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initCategoryToggle();
    initFilterForm();
    
    // Установка правильного значения сортировки при загрузке страницы
    restoreSortSelection();
    
    // Восстановление параметров фильтрации
    restoreFilterParams();
    
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
                
                // Открываем меню подкатегорий для активной категории
                const menu = item.querySelector('.subcategories-menu');
                if (menu) {
                    menu.classList.add('show');
                    // Устанавливаем display: block напрямую для гарантии
                    menu.style.display = 'block';
                    menu.style.height = 'auto';
                    menu.style.opacity = '1';
                    
                    const toggle = item.querySelector('.subcategory-toggle i');
                    if (toggle) {
                        toggle.className = 'fas fa-chevron-up';
                    }
                }
                
                // Если указана подкатегория, ищем и активируем её
                if (subcategoryParam) {
                    const subcatLink = item.querySelector(`.subcategory-link[href*="subcategory=${subcategoryParam}"]`);
                    if (subcatLink) {
                        subcatLink.classList.add('active-subcategory');
                        subcatLink.style.color = '#2345c2';
                        subcatLink.style.fontWeight = '600';
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