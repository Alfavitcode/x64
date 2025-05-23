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
                <div class="cart-items-container">
                    <?php foreach ($cart_items as $item): ?>
                    <div id="cart-item-<?php echo (int)$item['id']; ?>" class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid">
                        </div>
                        <div class="cart-item-details">
                            <h5 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <div class="cart-item-price-mobile d-block d-md-none">
                                <span class="price-label">Цена:</span>
                                <span class="price-value"><?php echo number_format($item['price'], 0, '.', ' '); ?> ₽</span>
                            </div>
                        </div>
                        <div class="cart-item-price d-none d-md-flex">
                            <?php echo number_format($item['price'], 0, '.', ' '); ?> ₽
                        </div>
                        <div class="cart-item-quantity">
                            <div class="quantity-control">
                                <button class="btn quantity-btn quantity-decrease" data-cart-id="<?php echo (int)$item['id']; ?>">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-value item-quantity"><?php echo (int)$item['quantity']; ?></span>
                                <button class="btn quantity-btn quantity-increase" data-cart-id="<?php echo (int)$item['id']; ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="cart-item-subtotal">
                            <div class="subtotal-label d-block d-md-none">Сумма:</div>
                            <div class="subtotal-value item-subtotal"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</div>
                        </div>
                        <div class="cart-item-actions">
                            <button class="btn btn-remove remove-from-cart" data-cart-id="<?php echo (int)$item['id']; ?>" title="Удалить товар">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cart-summary">
                    <div class="cart-summary-header">
                        <h5>Итого</h5>
                    </div>
                    <div class="cart-summary-body">
                        <div class="cart-summary-row">
                            <span>Товаров:</span>
                            <span class="fw-bold" id="cart-total-count"><?php echo $total_count; ?></span>
                        </div>
                        <div class="cart-summary-row total-row">
                            <span>Итоговая сумма:</span>
                            <span class="fw-bold fs-5" id="cart-total-sum"><?php echo number_format($total_sum, 0, '.', ' '); ?> ₽</span>
                        </div>
                        <button id="checkout-button" class="btn btn-primary w-100 mb-2 rounded-pill" <?php if ($total_count == 0) echo 'disabled'; ?>>
                            <i class="fas fa-check-circle me-2"></i>Оформить заказ
                        </button>
                        <a href="/catalog.php" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="fas fa-shopping-bag me-2"></i>Продолжить покупки
                        </a>
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

/* Стили для списка товаров в корзине */
.cart-items-container {
    margin-bottom: 2rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto auto;
    grid-gap: 1rem;
    align-items: center;
    background-color: #fff;
    border-radius: 18px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: fadeIn 0.4s ease-out;
}

.cart-item:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.cart-item-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.cart-item-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #212529;
}

.cart-item-price {
    font-weight: 600;
    color: #212529;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-item-price-mobile {
    margin-top: 0.25rem;
}

.cart-item-price-mobile .price-label {
    color: #6c757d;
    font-size: 0.85rem;
    margin-right: 0.5rem;
}

.cart-item-price-mobile .price-value {
    font-weight: 600;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-control {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 50px;
    padding: 0.25rem;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #fff;
    border: 1px solid #dee2e6;
    color: #212529;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all 0.2s ease;
}

.quantity-btn:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.quantity-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.quantity-btn.btn-active {
    background-color: #0d6efd;
    color: #fff;
    transform: scale(0.95);
}

.quantity-value {
    width: 32px;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    color: #212529;
}

.cart-item-subtotal {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.subtotal-label {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.subtotal-value {
    font-weight: 600;
    color: #212529;
    font-size: 1.1rem;
}

.cart-item-actions {
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-remove {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #fff;
    border: 1px solid #dee2e6;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-remove:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
    animation: shake 0.5s ease-in-out;
}

/* Стили для итогового блока */
.cart-summary {
    background-color: #fff;
    border-radius: 18px;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    overflow: hidden;
    position: sticky;
    top: 1rem;
}

.cart-summary-header {
    padding: 1.25rem;
    border-bottom: 1px solid #f1f1f1;
}

.cart-summary-header h5 {
    margin: 0;
    font-weight: 600;
    color: #212529;
}

.cart-summary-body {
    padding: 1.25rem;
}

.cart-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.cart-summary-row.total-row {
    margin-bottom: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f1f1;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 767.98px) {
    .cart-item {
        grid-template-columns: 70px 1fr;
        grid-template-rows: auto auto auto;
        grid-gap: 0.75rem;
        padding: 1rem;
    }
    
    .cart-item-image {
        width: 70px;
        height: 70px;
        grid-row: span 2;
    }
    
    .cart-item-details {
        grid-column: 2;
    }
    
    .cart-item-quantity {
        grid-column: 1 / -1;
        justify-content: flex-start;
        margin-top: 0.5rem;
    }
    
    .cart-item-subtotal {
        grid-column: 2;
        align-items: flex-start;
    }
    
    .cart-item-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    
    .btn-remove {
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }
    
    .quantity-btn {
        width: 28px;
        height: 28px;
    }
    
    .quantity-value {
        width: 28px;
    }
}

/* Анимации и интерактивные эффекты */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(-20px); }
}

@keyframes priceUpdate {
    0% { color: #0d6efd; transform: scale(1.1); }
    100% { color: inherit; transform: scale(1); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.quantity-btn:active {
    transform: scale(0.9);
}

.cart-item-image:hover img {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

.btn-remove:hover {
    animation: shake 0.5s ease-in-out;
}

/* Эффект при наведении на кнопку оформления заказа */
#checkout-button:hover {
    animation: pulse 1s infinite;
}

/* Плавный переход для всех элементов */
* {
    transition: all 0.2s ease;
}

/* Стили для состояния загрузки */
.cart-item.loading {
    position: relative;
}

.cart-item.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.4);
    border-radius: 18px;
    z-index: 1;
    pointer-events: none;
}

.row-loader {
    position: relative;
    z-index: 2;
    margin-left: 10px;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.cart-item.loading .cart-item-price,
.cart-item.loading .cart-item-subtotal,
.cart-item.loading .quantity-value {
    position: relative;
    overflow: hidden;
}

.cart-item.loading .cart-item-price::before,
.cart-item.loading .cart-item-subtotal::before,
.cart-item.loading .quantity-value::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    z-index: 2;
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
                
                // Добавляем индикатор загрузки в блок с действиями
                const actionsContainer = row.querySelector('.cart-item-actions');
                if (actionsContainer) {
                    actionsContainer.appendChild(loader);
                }
            }
            loader.style.display = 'inline-block';
            
            // Добавляем класс "loading" к строке товара
            row.classList.add('loading');
        } else if (loader) {
            loader.style.display = 'none';
            
            // Удаляем класс "loading" со строки товара
            row.classList.remove('loading');
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
        const cartContainer = document.querySelector('.cart-items-container');
        if (cartContainer && cartContainer.children.length === 0) {
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