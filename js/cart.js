/**
 * Файл с функциями для работы с корзиной
 */

// Самовызывающаяся функция для изоляции переменных
(function() {
    // Флаг для отслеживания инициализации
    let isInitialized = false;
    
    // Массив для хранения ID кнопок, чтобы избежать дублирования
    let initializedButtonIds = [];

    // Объект с функциями корзины
    const Cart = {
        // Обновление количества товаров в корзине
        updateCount: function(count) {
            console.log('Обновление счетчика корзины, новое значение:', count);
            const cartCountElements = document.querySelectorAll('.cart-count');
            console.log('Найдено элементов счетчика корзины:', cartCountElements.length);
            
            cartCountElements.forEach((element, index) => {
                console.log(`Элемент счетчика #${index}:`, element);
                element.textContent = count;
            });
            
            if (cartCountElements.length === 0) {
                console.error('Не найдены элементы с классом .cart-count!');
            }
        },

        // Получение количества товаров в корзине
        getCount: function() {
            console.log('Запрос количества товаров в корзине...');
            fetch('/ajax/get_cart_count.php')
                .then(response => {
                    console.log('Получен ответ от сервера:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Данные о количестве товаров:', data);
                    if (data.success) {
                        this.updateCount(data.cart_count);
                    } else {
                        console.error('Ошибка в ответе сервера:', data);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при получении количества товаров в корзине:', error);
                });
        },

        // Добавление товара в корзину
        addItem: function(productId, quantity = 1) {
            console.log('Добавление товара в корзину:', productId, quantity);
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('/ajax/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Ответ сервера:', data);
                
                if (data.success) {
                    // Обновляем счетчик товаров в корзине
                    this.getCount();
                    
                    // Показываем уведомление
                    if (data.action === 'insert') {
                        console.log('Показываем уведомление: Товар добавлен в корзину');
                        this.showNotification('Товар добавлен в корзину', 'success');
                    } else {
                        console.log('Показываем уведомление: Количество товара обновлено');
                        this.showNotification('Количество товара в корзине обновлено', 'success');
                    }
                } else {
                    console.log('Показываем уведомление об ошибке:', data.message);
                    this.showNotification('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                this.showNotification('Произошла ошибка при добавлении товара в корзину', 'error');
            });
        },

        // Удаление товара из корзины
        removeItem: function(cartId) {
            const formData = new FormData();
            formData.append('cart_id', cartId);

            fetch('/ajax/remove_from_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем счетчик товаров в корзине
                    this.getCount();
                    
                    // Показываем уведомление
                    this.showNotification('Товар удален из корзины', 'success');
                    
                    // Обновляем страницу корзины, если мы на ней находимся
                    if (window.location.pathname.includes('/cart/')) {
                        window.location.reload();
                    }
                } else {
                    this.showNotification('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                this.showNotification('Произошла ошибка при удалении товара из корзины', 'error');
            });
        },

        // Отображение уведомлений
        showNotification: function(message, type = 'info') {
            console.log('Вызов showNotification:', message, type);
            
            // Создаем контейнер для уведомлений, если его нет
            let container = document.querySelector('.notification-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'notification-container';
                document.body.appendChild(container);
                console.log('Создан контейнер для уведомлений');
            }
            
            // Создаем элемент уведомления
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            
            // Добавляем иконку в зависимости от типа уведомления
            let icon = '';
            switch (type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle"></i>';
                    break;
                case 'info':
                    icon = '<i class="fas fa-info-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
            }
            
            // Формируем содержимое уведомления
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon">${icon}</div>
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close"><i class="fas fa-times"></i></button>
            `;
            
            // Добавляем уведомление в контейнер
            container.appendChild(notification);
            console.log('Уведомление добавлено в DOM');
            
            // Делаем уведомление видимым (для CSS-анимации)
            setTimeout(() => {
                notification.classList.add('notification-show');
            }, 10);
            
            // Добавляем обработчик для закрытия уведомления
            const closeButton = notification.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                notification.classList.remove('notification-show');
                notification.classList.add('notification-hide');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
            
            // Автоматически скрываем уведомление через 5 секунд
            setTimeout(() => {
                notification.classList.remove('notification-show');
                notification.classList.add('notification-hide');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        },

        // Инициализация кнопок добавления в корзину
        initButtons: function() {
            // Добавляем обработчики для кнопок добавления в корзину
            const buttons = document.querySelectorAll('.btn-add-to-cart');
            console.log('Найдено кнопок для инициализации:', buttons.length);
            
            // Создаем уникальные ID для кнопок, если их нет
            buttons.forEach((button, index) => {
                // Генерируем уникальный ID для кнопки, если его нет
                if (!button.id) {
                    button.id = 'cart-button-' + Date.now() + '-' + index;
                }
                
                // Проверяем, была ли уже инициализирована эта кнопка
                if (initializedButtonIds.includes(button.id) || button.hasAttribute('data-cart-initialized')) {
                    console.log('Кнопка уже инициализирована:', button.id);
                    return; // Пропускаем уже инициализированные кнопки
                }
                
                // Удаляем все существующие обработчики событий
                const newButton = button.cloneNode(true);
                if (button.parentNode) {
                    button.parentNode.replaceChild(newButton, button);
                }
                
                // Добавляем новый обработчик
                newButton.setAttribute('data-cart-initialized', 'true');
                newButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('Клик по кнопке добавления в корзину:', newButton.id);
                    
                    const productId = newButton.getAttribute('data-product-id');
                    if (productId) {
                        console.log('ID товара:', productId);
                        this.addItem(productId);
                    }
                });
                
                // Добавляем ID кнопки в список инициализированных
                initializedButtonIds.push(newButton.id);
                console.log('Кнопка инициализирована:', newButton.id);
            });
            
            // Добавляем обработчик для кнопки на странице товара
            const productButton = document.getElementById('add-to-cart-button');
            if (productButton && !productButton.hasAttribute('data-cart-initialized') && 
                !initializedButtonIds.includes(productButton.id)) {
                
                // Генерируем ID для кнопки, если его нет
                if (!productButton.id) {
                    productButton.id = 'product-cart-button-' + Date.now();
                }
                
                // Клонируем кнопку для удаления существующих обработчиков
                const newProductButton = productButton.cloneNode(true);
                if (productButton.parentNode) {
                    productButton.parentNode.replaceChild(newProductButton, productButton);
                }
                
                newProductButton.setAttribute('data-cart-initialized', 'true');
                newProductButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('Клик по кнопке добавления в корзину на странице товара:', newProductButton.id);
                    
                    const productId = newProductButton.getAttribute('data-product-id');
                    const quantityInput = document.getElementById('product-quantity');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    
                    console.log('ID товара:', productId, 'Количество:', quantity);
                    
                    if (isNaN(quantity) || quantity < 1) {
                        this.showNotification('Пожалуйста, укажите корректное количество товара', 'error');
                        return;
                    }
                    
                    this.addItem(productId, quantity);
                });
                
                // Добавляем ID кнопки в список инициализированных
                initializedButtonIds.push(newProductButton.id);
                console.log('Кнопка товара инициализирована:', newProductButton.id);
            }
            
            // Выводим количество найденных кнопок
            console.log('Найдено кнопок для инициализации:', buttons.length);
            console.log('Инициализировано кнопок:', initializedButtonIds.length);
        },

        // Основная инициализация
        init: function() {
            if (isInitialized) {
                console.log('Корзина уже инициализирована, пропускаем');
                return;
            }
            
            console.log('Инициализация корзины');
            
            // Создаем контейнер для уведомлений заранее
            if (!document.querySelector('.notification-container')) {
                const container = document.createElement('div');
                container.className = 'notification-container';
                document.body.appendChild(container);
                console.log('Создан контейнер для уведомлений при инициализации');
            }
            
            // Получаем количество товаров в корзине
            console.log('Запрашиваем количество товаров при инициализации...');
            this.getCount();
            
            // Инициализируем кнопки
            this.initButtons();
            
            // Отслеживаем изменения DOM для инициализации динамически добавленных кнопок
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        let initializeButtons = false;
                        
                        mutation.addedNodes.forEach((node) => {
                            // Проверяем, является ли добавленный узел или его дочерние элементы кнопками
                            if (node.nodeType === 1) { // Элемент
                                if (node.classList && node.classList.contains('btn-add-to-cart') && 
                                    !node.hasAttribute('data-cart-initialized')) {
                                    initializeButtons = true;
                                } else if (node.querySelectorAll) {
                                    const buttons = node.querySelectorAll('.btn-add-to-cart:not([data-cart-initialized])');
                                    if (buttons.length > 0) {
                                        initializeButtons = true;
                                    }
                                }
                            }
                        });
                        
                        if (initializeButtons) {
                            this.initButtons();
                        }
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            isInitialized = true;
            console.log('Корзина инициализирована');
        }
    };

    // Инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded сработал, инициализируем корзину...');
        Cart.init();
    });

    // Добавляем Cart в глобальную область видимости для доступа из других скриптов
    window.Cart = Cart;
    console.log('Объект Cart добавлен в глобальную область видимости');
})(); 