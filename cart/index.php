<?php
// Подключаем шапку сайта
include_once '../includes/header/header.php';
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();
$cart_items = getCartItems($user_id, $session_id);
$total_sum = 0;
$total_count = 0;
foreach ($cart_items as $item) {
    $total_sum += $item['subtotal'];
    $total_count++;
}
$cart_is_empty = empty($cart_items);
?>

<main class="main-content">
    <div class="container mt-4">
        <h1 class="mb-4">Корзина</h1>
        
        <?php if ($cart_is_empty): ?>
        <!-- Красивый дизайн для пустой корзины -->
        <div class="empty-cart-container">
            <div class="empty-cart-animation">
                <div class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="cart-pulse"></div>
            </div>
            <h3 class="empty-cart-title">Ваша корзина пуста</h3>
            <p class="empty-cart-text">Похоже, вы еще не добавили товары в корзину</p>
            <a href="/catalog.php" class="btn btn-primary btn-lg mt-3 rounded-pill">
                <i class="fas fa-shopping-bag me-2"></i>Перейти к покупкам
            </a>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Товары в корзине</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" id="cart-items-container">
                            <table class="table table-hover mb-0 align-middle" id="cart-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Товар</th>
                                        <th>Цена</th>
                                        <th>Количество</th>
                                        <th>Сумма</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items-body">
                                <?php foreach ($cart_items as $item): ?>
                                    <tr id="cart-item-<?php echo (int)$item['id']; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded me-3" style="width: 60px; height: 60px; object-fit: contain;">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo number_format($item['price'], 0, '.', ' '); ?> ₽</span>
                                        </td>
                                        <td>
                                            <div class="quantity-control d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn quantity-decrease" data-cart-id="<?php echo (int)$item['id']; ?>">-</button>
                                                <span class="mx-2 fw-bold item-quantity"><?php echo (int)$item['quantity']; ?></span>
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn quantity-increase" data-cart-id="<?php echo (int)$item['id']; ?>">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold item-subtotal"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger remove-from-cart" data-cart-id="<?php echo (int)$item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Итого</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Товаров:</span>
                            <span class="fw-bold" id="cart-total-count"><?php echo $total_count; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Итоговая сумма:</span>
                            <span class="fw-bold fs-5" id="cart-total-sum"><?php echo number_format($total_sum, 0, '.', ' '); ?> ₽</span>
                        </div>
                        <button id="checkout-button" class="btn btn-primary w-100 mb-2 rounded-pill" <?php if ($total_count == 0) echo 'disabled'; ?>>Оформить заказ</button>
                        <a href="/catalog.php" class="btn btn-outline-secondary w-100 rounded-pill">Продолжить покупки</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
.card {
    border-radius: 18px;
}
.btn, .form-control {
    border-radius: 12px;
}
.table th, .table td {
    vertical-align: middle;
}
.quantity-control {
    max-width: 120px;
}
.quantity-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}
.quantity-btn.disabled, .btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
.quantity-btn.btn-active {
    background-color: #e9ecef;
    transform: scale(0.95);
}
.row-loader {
    display: inline-block;
    margin-left: 10px;
    vertical-align: middle;
}
/* Анимация для изменения цены */
@keyframes priceUpdate {
    0% { color: #0d6efd; transform: scale(1.1); }
    100% { color: inherit; transform: scale(1); }
}
.price-update {
    animation: priceUpdate 0.5s ease-out;
}
/* Стили для анимации удаления товара */
@keyframes fadeOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(-20px); }
}
.fade-out {
    animation: fadeOut 0.3s forwards;
}

/* Стили для пустой корзины */
.empty-cart-container {
    background-color: #ffffff;
    border-radius: 18px;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    margin: 2rem auto;
    max-width: 600px;
}

.empty-cart-animation {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 1.5rem;
}

.cart-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 3.5rem;
    color: #0d6efd;
    z-index: 2;
}

.cart-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100px;
    height: 100px;
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
    z-index: 1;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0.8;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0.4;
    }
    100% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0.8;
    }
}

.empty-cart-title {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
    color: #333;
}

.empty-cart-text {
    color: #6c757d;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Флаг для отслеживания выполнения запросов
    let pendingRequests = {};
    
    // Дебаунс функция для предотвращения множественных вызовов
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
    
    // Удаление товара из корзины
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const cartId = this.getAttribute('data-cart-id');
            console.log(`Попытка удаления товара ID: ${cartId}`);
            
            // Проверяем, не выполняется ли уже запрос для этого товара
            if (pendingRequests[cartId]) {
                console.log(`Запрос для товара ID: ${cartId} уже выполняется, игнорируем`);
                return;
            }
            
            // Блокируем кнопку
            this.disabled = true;
            this.classList.add('disabled');
            
            // Устанавливаем флаг запроса
            pendingRequests[cartId] = true;
            
            // Удаляем товар
            removeFromCart(cartId, this);
        });
    });
    
    // Жесткая блокировка всех кнопок товара при любом запросе
    function lockAllItemButtons(cartId) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            const buttons = row.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        }
    }
    
    // Разблокировка всех кнопок товара
    function unlockAllItemButtons(cartId) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            const buttons = row.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('disabled');
                btn.classList.remove('btn-active');
            });
        }
    }
    
    // Изменение количества товара (с использованием абсолютных значений)
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const cartId = this.getAttribute('data-cart-id');
            const action = this.classList.contains('quantity-increase') ? 'increase' : 'decrease';
            
            // Получаем текущее количество из элемента на странице
            const row = document.getElementById('cart-item-' + cartId);
            if (!row) return;
            
            const quantityElement = row.querySelector('.item-quantity');
            if (!quantityElement) return;
            
            let currentQuantity = parseInt(quantityElement.textContent) || 1;
            let newQuantity = action === 'increase' ? currentQuantity + 1 : currentQuantity - 1;
            
            console.log(`Клик по кнопке ${action}. ID товара: ${cartId}, текущее: ${currentQuantity}, новое: ${newQuantity}`);
            
            // Проверяем, не выполняется ли уже запрос для этого товара
            if (pendingRequests[cartId]) {
                console.log(`Запрос для товара ID: ${cartId} уже выполняется, игнорируем`);
                return;
            }
            
            // Визуальная обратная связь
            this.classList.add('btn-active');
            
            // Блокируем все кнопки для этого товара
            lockAllItemButtons(cartId);
            
            // Устанавливаем флаг запроса
            pendingRequests[cartId] = true;
            
            // Если количество 0 или меньше, удаляем товар
            if (newQuantity <= 0) {
                console.log(`Количество <= 0, удаляем товар ID: ${cartId}`);
                removeFromCart(cartId, this);
            } else {
                // Иначе обновляем количество
                console.log(`Обновляем количество товара ID: ${cartId} до ${newQuantity}`);
                updateCartItemQuantity(cartId, newQuantity, this);
            }
        });
    });
    
    // Функция обновления количества товара
    function updateCartItemQuantity(cartId, quantity, button) {
        // Показываем локальный индикатор загрузки для строки товара
        showRowLoading(cartId, true);
        
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        
        console.log(`Отправка запроса на обновление количества. ID товара: ${cartId}, количество: ${quantity}`);
        
        fetch('/ajax/update_cart_quantity.php', {
            method: 'POST',
            body: formData,
            // Важно: отключаем кэширование запросов
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            // Добавляем отладочный код для проверки ответа
            return response.text().then(text => {
                console.log('Ответ сервера:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                    throw new Error('Некорректный ответ сервера');
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Обновляем данные на странице без перезагрузки
                console.log(`Успешное обновление. Новое количество: ${data.quantity}, новая сумма: ${data.subtotal}`);
                updateCartItemUI(cartId, data.quantity, data.subtotal);
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Показываем уведомление
                Cart.showNotification('Количество товара обновлено', 'success');
            } else {
                console.error(`Ошибка обновления: ${data.message}`);
                Cart.showNotification('Ошибка: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            Cart.showNotification('Произошла ошибка при обновлении количества товара', 'error');
        })
        .finally(() => {
            console.log(`Завершение запроса для товара ID: ${cartId}`);
            
            // Скрываем индикатор загрузки
            showRowLoading(cartId, false);
            
            // Разблокируем кнопки
            unlockAllItemButtons(cartId);
            
            // Снимаем флаг запроса с задержкой, чтобы избежать быстрых повторных кликов
            setTimeout(() => {
                delete pendingRequests[cartId];
                console.log(`Флаг запроса снят для товара ID: ${cartId}`);
            }, 1000); // Увеличиваем задержку до 1 секунды
        });
    }
    
    // Функция удаления товара из корзины
    function removeFromCart(cartId, button) {
        // Показываем локальный индикатор загрузки для строки товара
        showRowLoading(cartId, true);
        
        const formData = new FormData();
        formData.append('cart_id', cartId);
        
        console.log(`Отправка запроса на удаление товара ID: ${cartId}`);
        
        fetch('/ajax/remove_from_cart.php', {
            method: 'POST',
            body: formData,
            // Отключаем кэширование запросов
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            // Добавляем отладочный код для проверки ответа
            return response.text().then(text => {
                console.log('Ответ сервера:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                    throw new Error('Некорректный ответ сервера');
                }
            });
        })
        .then(data => {
            if (data.success) {
                console.log(`Успешное удаление товара ID: ${cartId}`);
                
                // Удаляем элемент из DOM с анимацией
                removeCartItemFromUI(cartId);
                
                // Обновляем общую сумму и количество
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Обновляем счетчик товаров в корзине в шапке
                Cart.getCount();
                
                // Проверяем, не стала ли корзина пустой
                checkEmptyCart();
                
                // Показываем уведомление
                Cart.showNotification('Товар удален из корзины', 'success');
            } else {
                console.error(`Ошибка удаления: ${data.message}`);
                
                // Разблокируем кнопки в случае ошибки
                if (button) {
                    button.disabled = false;
                    button.classList.remove('disabled');
                }
                
                Cart.showNotification('Ошибка: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            Cart.showNotification('Произошла ошибка при удалении товара из корзины', 'error');
            
            // Разблокируем кнопки в случае ошибки
            if (button) {
                button.disabled = false;
                button.classList.remove('disabled');
            }
        })
        .finally(() => {
            console.log(`Завершение запроса на удаление товара ID: ${cartId}`);
            
            // Скрываем индикатор загрузки
            showRowLoading(cartId, false);
            
            // Снимаем флаг запроса с задержкой
            setTimeout(() => {
                delete pendingRequests[cartId];
                console.log(`Флаг запроса снят для товара ID: ${cartId}`);
            }, 1000);
        });
    }
    
    // Функция показа/скрытия индикатора загрузки для строки товара
    function showRowLoading(cartId, show) {
        const row = document.getElementById('cart-item-' + cartId);
        if (!row) return;
        
        let loader = row.querySelector('.row-loader');
        
        if (show) {
            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'row-loader';
                loader.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Загрузка...</span></div>';
                
                // Добавляем индикатор загрузки в последнюю ячейку строки
                const lastCell = row.querySelector('td:last-child');
                if (lastCell) {
                    lastCell.appendChild(loader);
                }
            }
            loader.style.display = 'inline-block';
        } else if (loader) {
            loader.style.display = 'none';
        }
    }
    
    // Функция обновления UI для товара в корзине
    function updateCartItemUI(cartId, quantity, subtotal) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            // Обновляем количество
            const quantityElement = row.querySelector('.item-quantity');
            if (quantityElement) {
                quantityElement.textContent = quantity;
            }
            
            // Обновляем сумму с анимацией
            const subtotalElement = row.querySelector('.item-subtotal');
            if (subtotalElement) {
                // Добавляем класс для анимации
                subtotalElement.classList.add('price-update');
                subtotalElement.textContent = formatPrice(subtotal) + ' ₽';
                
                // Удаляем класс анимации через некоторое время
                setTimeout(() => {
                    subtotalElement.classList.remove('price-update');
                }, 500);
            }
        }
    }
    
    // Функция удаления товара из UI
    function removeCartItemFromUI(cartId) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            row.classList.add('fade-out');
            setTimeout(() => {
                row.remove();
                
                // Проверяем, остались ли товары в корзине
                checkEmptyCart();
            }, 300);
        }
    }
    
    // Функция проверки, стала ли корзина пустой
    function checkEmptyCart() {
        const cartBody = document.getElementById('cart-items-body');
        if (cartBody && cartBody.children.length === 0) {
            // Перезагружаем страницу, чтобы показать пустую корзину
            window.location.reload();
        }
    }
    
    // Функция обновления итоговых данных корзины
    function updateCartTotals(total, count) {
        const totalSumElement = document.getElementById('cart-total-sum');
        if (totalSumElement) {
            // Добавляем класс для анимации
            totalSumElement.classList.add('price-update');
            totalSumElement.textContent = formatPrice(total) + ' ₽';
            
            // Удаляем класс анимации через некоторое время
            setTimeout(() => {
                totalSumElement.classList.remove('price-update');
            }, 500);
        }
        
        const totalCountElement = document.getElementById('cart-total-count');
        if (totalCountElement) {
            totalCountElement.textContent = count;
        }
        
        // Обновляем состояние кнопки оформления заказа
        const checkoutButton = document.getElementById('checkout-button');
        if (checkoutButton) {
            if (count > 0) {
                checkoutButton.removeAttribute('disabled');
            } else {
                checkoutButton.setAttribute('disabled', 'disabled');
            }
        }
    }
    
    // Функция форматирования цены
    function formatPrice(price) {
        return new Intl.NumberFormat('ru-RU').format(price);
    }
});
</script>

<?php include_once '../includes/footer/footer.php'; ?> 