<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключаем необходимые файлы
require_once 'includes/config/db_config.php';
require_once 'includes/config/db_functions.php';

// Создаем таблицы заказов, если они не существуют или не содержат необходимые поля
createOrdersTablesIfNotExists();

// Определяем переменные для идентификации пользователя
$session_id = session_id();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Получаем содержимое корзины
$cart_items = getCartItems($session_id, $user_id);
$cart_total = 0;
$total_items = 0;

// Проверяем, есть ли товары в корзине
if (empty($cart_items)) {
    // Если корзина пуста, перенаправляем на страницу корзины
    header("Location: /cart.php");
    exit;
}

// Рассчитываем итоги корзины
foreach ($cart_items as $item) {
    $cart_total += $item['subtotal'];
    $total_items += $item['quantity'];
}

// Если пользователь авторизован, получаем его данные
$user = null;
if ($user_id) {
    $user = getUserById($user_id);
}

// Обработка отправки формы
$order_success = false;
$order_error = '';
$order_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Получаем данные из формы
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $region = isset($_POST['region']) ? trim($_POST['region']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $delivery_method = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Проверяем обязательные поля
    if (empty($fullname) || empty($email) || empty($phone) || empty($city) || empty($address)) {
        $order_error = 'Пожалуйста, заполните все обязательные поля';
    } else {
        // Создаем заказ в базе данных
        // Функция createOrder ещё не реализована, нужно добавить её в db_functions.php
        $order_data = [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'region' => $region,
            'city' => $city,
            'address' => $address,
            'postal_code' => $postal_code,
            'payment_method' => $payment_method,
            'delivery_method' => $delivery_method,
            'comment' => $comment,
            'total_amount' => $cart_total,
            'items' => $cart_items
        ];
        
        $result = createOrder($order_data);
        
        if ($result['success']) {
            $order_success = true;
            $order_id = $result['order_id'];
            
            // Очищаем корзину после успешного оформления заказа
            clearCart($session_id, $user_id);
        } else {
            $order_error = $result['message'];
        }
    }
}

// Задаем заголовок страницы
$page_title = 'Оформление заказа';

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/checkout.css">';

// Подключаем шапку сайта
include_once 'includes/header/header.php';
?>

<main class="main-content">
    <div class="container mt-4">
        <h1 class="mb-4">Оформление заказа</h1>
        
        <?php if ($order_success): ?>
        <!-- Успешное оформление заказа -->
        <div class="card mb-4">
            <div class="card-body text-center py-5">
                <div class="order-success-icon mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h2 class="mb-3">Заказ успешно оформлен!</h2>
                <p class="mb-3">Ваш заказ №<?php echo $order_id; ?> успешно оформлен. Мы отправили подтверждение на указанный email.</p>
                <?php if ($user_id): ?>
                <p class="mb-4">Вы можете отслеживать статус заказа в <a href="/account/orders.php">личном кабинете</a>.</p>
                <?php endif; ?>
                <div class="d-flex justify-content-center">
                    <a href="/catalog.php" class="btn btn-primary me-2">Продолжить покупки</a>
                    <?php if ($user_id): ?>
                    <a href="/account/orders.php" class="btn btn-outline-secondary">Мои заказы</a>
                    <?php else: ?>
                    <a href="/account/login.php" class="btn btn-outline-secondary">Войти в личный кабинет</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <?php if (!empty($order_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $order_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="post" action="" id="checkout-form">
            <div class="row">
                <!-- Форма заказа -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Контактные данные</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="fullname" class="form-label">ФИО <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" required
                                           value="<?php echo $user ? htmlspecialchars($user['fullname']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required
                                           value="<?php echo $user ? htmlspecialchars($user['phone']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Адрес доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="region" class="form-label">Регион</label>
                                    <input type="text" class="form-control" id="region" name="region">
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Город <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Адрес <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" required 
                                           placeholder="Улица, дом, квартира">
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Почтовый индекс</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Способ доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_courier" value="courier" checked>
                                <label class="form-check-label" for="delivery_courier">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <strong>Курьерская доставка</strong>
                                            <p class="mb-0 text-muted">Доставка в течение 1-3 дней</p>
                                        </div>
                                        <span class="fw-bold">300 ₽</span>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_pickup" value="pickup">
                                <label class="form-check-label" for="delivery_pickup">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <strong>Самовывоз из магазина</strong>
                                            <p class="mb-0 text-muted">Срок: 1-2 дня</p>
                                        </div>
                                        <span class="fw-bold">Бесплатно</span>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_post" value="post">
                                <label class="form-check-label" for="delivery_post">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <strong>Почта России</strong>
                                            <p class="mb-0 text-muted">Доставка в течение 5-10 дней</p>
                                        </div>
                                        <span class="fw-bold">250 ₽</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Способ оплаты</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" checked>
                                <label class="form-check-label" for="payment_card">
                                    <div>
                                        <strong>Банковской картой онлайн</strong>
                                        <p class="mb-0 text-muted">Visa, MasterCard, МИР</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash">
                                <label class="form-check-label" for="payment_cash">
                                    <div>
                                        <strong>Наличными при получении</strong>
                                        <p class="mb-0 text-muted">Оплата курьеру или в пункте выдачи</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_wallet" value="wallet">
                                <label class="form-check-label" for="payment_wallet">
                                    <div>
                                        <strong>Электронные кошельки</strong>
                                        <p class="mb-0 text-muted">ЮMoney, WebMoney, QIWI</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Комментарий к заказу</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Дополнительная информация к заказу"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Сводка заказа -->
                <div class="col-lg-4">
                    <div class="card order-summary">
                        <div class="card-header">
                            <h5 class="mb-0">Ваш заказ</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="order-summary-img me-3">
                                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid">
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                <small class="text-muted"><?php echo $item['quantity']; ?> шт.</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Товары (<?php echo $total_items; ?>):</span>
                                <span><?php echo number_format($cart_total, 0, '.', ' '); ?> ₽</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 delivery-cost">
                                <span>Доставка:</span>
                                <span>300 ₽</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between total-amount">
                                <strong>Итого:</strong>
                                <strong class="total-price"><?php echo number_format($cart_total + 300, 0, '.', ' '); ?> ₽</strong>
                            </div>
                            <button type="submit" name="checkout" class="btn btn-primary w-100 mt-3">Оформить заказ</button>
                            <div class="form-text text-center mt-2">
                                Нажимая на кнопку, вы даете согласие на обработку персональных данных и соглашаетесь с <a href="/privacy.php">политикой конфиденциальности</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<style>
/* Стили для страницы оформления заказа */
.order-summary-img {
    width: 50px;
    height: 50px;
    overflow: hidden;
    border-radius: 4px;
}

.order-summary-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.total-amount {
    font-size: 1.2rem;
}

.form-check-label {
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.form-check-input:checked + .form-check-label {
    background-color: rgba(var(--primary-color-rgb), 0.05);
}

.form-check {
    padding: 0;
    margin-bottom: 5px;
}

.form-check-input {
    position: relative;
    margin-left: 15px;
    margin-top: 15px;
}

.order-success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(40, 167, 69, 0.1);
}

@media (max-width: 767px) {
    .form-check-label {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .form-check-label span.fw-bold {
        margin-top: 5px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обновление стоимости доставки при изменении способа доставки
    const deliveryMethods = document.querySelectorAll('input[name="delivery_method"]');
    const deliveryCost = document.querySelector('.delivery-cost span:last-child');
    const totalPrice = document.querySelector('.total-price');
    const cartTotal = <?php echo $cart_total; ?>;
    
    deliveryMethods.forEach(method => {
        method.addEventListener('change', function() {
            let deliveryPrice = 0;
            
            // Определяем стоимость доставки
            if (this.value === 'courier') {
                deliveryPrice = 300;
            } else if (this.value === 'post') {
                deliveryPrice = 250;
            }
            
            // Обновляем отображение стоимости доставки
            deliveryCost.textContent = deliveryPrice === 0 ? 'Бесплатно' : deliveryPrice + ' ₽';
            
            // Обновляем итоговую сумму
            totalPrice.textContent = new Intl.NumberFormat('ru-RU').format(cartTotal + deliveryPrice) + ' ₽';
        });
    });
    
    // Валидация формы перед отправкой
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            const requiredFields = checkoutForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля!');
            }
        });
    }
});
</script>

<?php include_once 'includes/footer/footer.php'; ?> 