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
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Товар</th>
                                        <th>Цена</th>
                                        <th>Количество</th>
                                        <th>Сумма</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($cart_items)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Ваша корзина пуста</td></tr>
                                <?php else: foreach ($cart_items as $item): ?>
                                    <tr>
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
                                                <span class="mx-2 fw-bold"><?php echo (int)$item['quantity']; ?></span>
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn quantity-increase" data-cart-id="<?php echo (int)$item['id']; ?>" data-quantity="<?php echo (int)$item['quantity'] + 1; ?>">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
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
                            <span class="fw-bold"><?php echo $total_count; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Итоговая сумма:</span>
                            <span class="fw-bold fs-5"><?php echo number_format($total_sum, 0, '.', ' '); ?> ₽</span>
                        </div>
                        <button class="btn btn-primary w-100 mb-2 rounded-pill" <?php if ($total_count == 0) echo 'disabled'; ?>>Оформить заказ</button>
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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Удаление товара из корзины
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            Cart.removeItem(cartId);
        });
    });
    
    // Изменение количества товара
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const quantity = parseInt(this.getAttribute('data-quantity'));
            
            if (quantity <= 0) {
                // Если количество 0 или меньше, удаляем товар
                Cart.removeItem(cartId);
            } else {
                // Иначе обновляем количество
                updateCartItemQuantity(cartId, quantity);
            }
        });
    });
    
    // Функция обновления количества товара
    function updateCartItemQuantity(cartId, quantity) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        
        fetch('/ajax/update_cart_quantity.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем страницу
                window.location.reload();
            } else {
                Cart.showNotification('Ошибка: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            Cart.showNotification('Произошла ошибка при обновлении количества товара', 'error');
        });
    }
});
</script>

<?php include_once '../includes/footer/footer.php'; ?> 