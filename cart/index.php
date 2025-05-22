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
?>

<main class="main-content">
    <div class="container mt-4">
        <h1 class="mb-4">Корзина</h1>
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
                                <?php if (empty($cart_items)): ?>
                                    <tr id="empty-cart-row"><td colspan="5" class="text-center text-muted">Ваша корзина пуста</td></tr>
                                <?php else: foreach ($cart_items as $item): ?>
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
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn quantity-decrease" data-cart-id="<?php echo (int)$item['id']; ?>" data-quantity="<?php echo (int)$item['quantity'] - 1; ?>">-</button>
                                                <span class="mx-2 fw-bold item-quantity"><?php echo (int)$item['quantity']; ?></span>
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn quantity-increase" data-cart-id="<?php echo (int)$item['id']; ?>" data-quantity="<?php echo (int)$item['quantity'] + 1; ?>">+</button>
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
                                <?php endforeach; endif; ?>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Флаг для отслеживания выполнения запроса
    let isRequestInProgress = false;
    
    // Удаление товара из корзины
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Предотвращаем действие по умолчанию
            
            if (isRequestInProgress) return; // Не выполняем запрос, если предыдущий еще не завершен
            
            const cartId = this.getAttribute('data-cart-id');
            removeFromCart(cartId);
        });
    });
    
    // Изменение количества товара
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Предотвращаем действие по умолчанию
            
            if (isRequestInProgress) return; // Не выполняем запрос, если предыдущий еще не завершен
            
            const cartId = this.getAttribute('data-cart-id');
            const quantity = parseInt(this.getAttribute('data-quantity'));
            
            if (quantity <= 0) {
                // Если количество 0 или меньше, удаляем товар
                removeFromCart(cartId);
            } else {
                // Иначе обновляем количество
                updateCartItemQuantity(cartId, quantity);
            }
        });
    });
    
    // Функция обновления количества товара
    function updateCartItemQuantity(cartId, quantity) {
        // Устанавливаем флаг, что запрос выполняется
        isRequestInProgress = true;
        
        // Блокируем кнопки для этого товара
        disableButtons(cartId);
        
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        
        // Показываем индикатор загрузки
        showLoading(true);
        
        fetch('/ajax/update_cart_quantity.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Скрываем индикатор загрузки
            showLoading(false);
            
            if (data.success) {
                // Обновляем данные на странице без перезагрузки
                updateCartItemUI(cartId, data.quantity, data.subtotal);
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Показываем уведомление
                Cart.showNotification('Количество товара обновлено', 'success');
            } else {
                Cart.showNotification('Ошибка: ' + data.message, 'error');
            }
        })
        .catch(error => {
            // Скрываем индикатор загрузки
            showLoading(false);
            
            console.error('Ошибка:', error);
            Cart.showNotification('Произошла ошибка при обновлении количества товара', 'error');
        })
        .finally(() => {
            // Сбрасываем флаг и разблокируем кнопки
            isRequestInProgress = false;
            enableButtons(cartId);
        });
    }
    
    // Функция удаления товара из корзины
    function removeFromCart(cartId) {
        // Устанавливаем флаг, что запрос выполняется
        isRequestInProgress = true;
        
        // Блокируем кнопки для этого товара
        disableButtons(cartId);
        
        const formData = new FormData();
        formData.append('cart_id', cartId);
        
        // Показываем индикатор загрузки
        showLoading(true);
        
        fetch('/ajax/remove_from_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Скрываем индикатор загрузки
            showLoading(false);
            
            if (data.success) {
                // Удаляем элемент из DOM
                removeCartItemFromUI(cartId);
                
                // Обновляем общую сумму и количество
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Обновляем счетчик товаров в корзине в шапке
                Cart.getCount();
                
                // Показываем уведомление
                Cart.showNotification('Товар удален из корзины', 'success');
            } else {
                Cart.showNotification('Ошибка: ' + data.message, 'error');
            }
        })
        .catch(error => {
            // Скрываем индикатор загрузки
            showLoading(false);
            
            console.error('Ошибка:', error);
            Cart.showNotification('Произошла ошибка при удалении товара из корзины', 'error');
        })
        .finally(() => {
            // Сбрасываем флаг запроса
            isRequestInProgress = false;
        });
    }
    
    // Функция блокировки кнопок товара
    function disableButtons(cartId) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            const buttons = row.querySelectorAll('button');
            buttons.forEach(button => {
                button.setAttribute('disabled', 'disabled');
                button.classList.add('disabled');
            });
        }
    }
    
    // Функция разблокировки кнопок товара
    function enableButtons(cartId) {
        const row = document.getElementById('cart-item-' + cartId);
        if (row) {
            const buttons = row.querySelectorAll('button');
            buttons.forEach(button => {
                button.removeAttribute('disabled');
                button.classList.remove('disabled');
            });
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
            
            // Обновляем сумму
            const subtotalElement = row.querySelector('.item-subtotal');
            if (subtotalElement) {
                subtotalElement.textContent = formatPrice(subtotal) + ' ₽';
            }
            
            // Обновляем кнопки увеличения/уменьшения количества
            const decreaseBtn = row.querySelector('.quantity-decrease');
            if (decreaseBtn) {
                decreaseBtn.setAttribute('data-quantity', quantity - 1);
            }
            
            const increaseBtn = row.querySelector('.quantity-increase');
            if (increaseBtn) {
                increaseBtn.setAttribute('data-quantity', quantity + 1);
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
                const cartBody = document.getElementById('cart-items-body');
                if (cartBody && cartBody.children.length === 0) {
                    cartBody.innerHTML = '<tr id="empty-cart-row"><td colspan="5" class="text-center text-muted">Ваша корзина пуста</td></tr>';
                }
            }, 300);
        }
    }
    
    // Функция обновления итоговых данных корзины
    function updateCartTotals(total, count) {
        const totalSumElement = document.getElementById('cart-total-sum');
        if (totalSumElement) {
            totalSumElement.textContent = formatPrice(total) + ' ₽';
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
    
    // Функция отображения/скрытия индикатора загрузки
    function showLoading(show) {
        let loader = document.getElementById('cart-loader');
        
        if (show) {
            if (!loader) {
                loader = document.createElement('div');
                loader.id = 'cart-loader';
                loader.className = 'cart-loader';
                loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Загрузка...</span></div>';
                document.body.appendChild(loader);
            }
            loader.style.display = 'flex';
        } else if (loader) {
            loader.style.display = 'none';
        }
    }
});
</script>

<style>
/* Стили для анимации удаления товара */
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

.fade-out {
    animation: fadeOut 0.3s forwards;
}

/* Стили для индикатора загрузки */
.cart-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>

<?php include_once '../includes/footer/footer.php'; ?> 