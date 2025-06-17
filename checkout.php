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

// Получаем содержимое корзины перед обработкой формы
$cart_items = getCartItems($user_id, $session_id);
$cart_total = 0;
$total_items = 0;

// Рассчитываем итоги корзины
if (!empty($cart_items)) {
foreach ($cart_items as $item) {
    $cart_total += $item['subtotal'];
    $total_items += $item['quantity'];
}
}

// Инициализируем переменные для уведомления об успешном заказе
$order_success = false;
$order_error = '';
$order_id = 0;

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Проверяем, есть ли товары в корзине
    if (empty($cart_items)) {
        $order_error = 'Ваша корзина пуста. Добавьте товары перед оформлением заказа.';
    } else {
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
            clearCart($user_id, $session_id);
                
                // Получаем содержимое корзины заново (она должна быть пустой)
                $cart_items = getCartItems($user_id, $session_id);
                $cart_total = 0;
                $total_items = 0;
        } else {
            $order_error = $result['message'];
        }
    }
    }
} else {
    // Проверяем, есть ли товары в корзине
    if (empty($cart_items)) {
        // Если корзина пуста, перенаправляем на страницу корзины
        header("Location: /cart.php");
        exit;
    }
}

// Если пользователь авторизован, получаем его данные
$user = null;
if ($user_id) {
    $user = getUserById($user_id);
}

// Задаем заголовок страницы
$page_title = 'Оформление заказа';

// Подключаем дополнительные стили и скрипты
$additional_styles = '<link rel="stylesheet" href="/css/checkout.css">';
$additional_scripts = '
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
';

// Подключаем шапку сайта
include_once 'includes/header/header.php';
?>

<!-- Дополнительные стили для анимаций и улучшенного дизайна -->
<style>
    /* Улучшенные стили для карточек */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.4s, box-shadow 0.4s;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(77, 97, 252, 0.18);
    }
    
    .card-header {
        background: linear-gradient(135deg, #f1f5ff 0%, #e7eeff 100%);
        border-bottom: none;
        padding: 20px;
    }
    
    .card-header h5 {
        margin: 0;
        color: #2e3a59;
        font-weight: 700;
        font-size: 18px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    /* Улучшенные стили для полей ввода */
    .form-floating {
        margin-bottom: 20px;
    }
    
    .form-control, .form-select {
        height: auto;
        padding: 15px 20px;
        font-size: 16px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(77, 97, 252, 0.15);
    }
    
    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }
    
    /* Fix for floating label white square */
    .form-floating {
        position: relative;
    }
    
    .form-floating > label {
        padding: 1rem 1.25rem;
        opacity: 0.65;
        z-index: 1;
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        opacity: 0.8;
        transform: scale(0.85) translateY(-1.75rem) translateX(0.15rem);
        background-color: transparent;
        padding: 0 5px;
        height: auto;
        color: var(--primary-color);
        font-weight: 600;
        z-index: 1;
    }
    
    .form-floating > label::after {
        content: "";
        position: absolute;
        background-color: transparent;
        height: 100%;
        width: 100%;
        left: 0;
        top: 0;
        z-index: -1;
    }
    
    /* Улучшенные стили для радио-кнопок */
    .form-check {
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 12px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        border: 2px solid transparent;
    }
    
    .form-check:hover {
        background-color: #f1f5ff;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
        margin-top: 0.25em;
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .form-check-input:checked ~ .form-check-label {
        color: var(--primary-color);
    }
    
    .form-check-input:checked ~ .form-check-label strong {
        color: var(--primary-color);
    }
    
    .form-check-input:checked + .form-check-label {
        font-weight: 500;
    }
    
    .form-check-input:checked ~ .form-check {
        border-color: var(--primary-color);
        background-color: rgba(77, 97, 252, 0.05);
    }
    
    /* Стили для иконок в радио-кнопках */
    .form-check .fas, .form-check .fab {
        font-size: 18px;
    }
    
    /* Анимированная кнопка отправки */
    .btn-primary {
        padding: 12px 30px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #3a4cd1 100%);
        border: none;
        color: white;
        box-shadow: 0 6px 15px rgba(77, 97, 252, 0.25);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(77, 97, 252, 0.3);
    }
    
    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 60%);
        transform: skewX(-25deg);
        transition: all 0.6s ease;
    }
    
    .btn-primary:hover::before {
        left: 100%;
    }
    
    /* Улучшенные стили для сводки заказа */
    .order-summary {
        position: sticky;
        top: 80px;
    }
    
    .order-summary-img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        background-color: #f8f9fa;
    }
    
    .order-summary-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .item-price-badge {
        display: inline-block;
        padding: 6px 12px;
        background-color: rgba(77, 97, 252, 0.1);
        color: var(--primary-color);
        border-radius: 30px;
        font-weight: 600;
    }
    
    .delivery-price-badge {
        display: inline-block;
        padding: 5px 10px;
        background-color: rgba(77, 97, 252, 0.1);
        color: var(--primary-color);
        border-radius: 30px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .delivery-price-badge.free {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .total-price-wrapper {
        background: linear-gradient(135deg, #f1f5ff 0%, #e7eeff 100%);
        padding: 8px 15px;
        border-radius: 30px;
    }
    
    .total-price {
        font-weight: 700;
        font-size: 20px;
        color: var(--primary-color);
    }
    
    /* Анимации для элементов страницы */
    .animate-fade-up {
        opacity: 0;
        transform: translateY(30px);
    }
    
    .animate-fade-right {
        opacity: 0;
        transform: translateX(-30px);
    }
    
    .animate-fade-left {
        opacity: 0;
        transform: translateX(30px);
    }
    
    .animate-scale {
        opacity: 0;
        transform: scale(0.8);
    }
    
    /* Анимация для обновления цены */
    @keyframes priceUpdate {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .price-update {
        animation: priceUpdate 0.5s ease;
        color: var(--primary-color);
    }
    
    /* Анимация пульсации */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(77, 97, 252, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(77, 97, 252, 0); }
        100% { box-shadow: 0 0 0 0 rgba(77, 97, 252, 0); }
    }
    
    .pulse-animation {
        animation: pulse 1.5s infinite;
    }
    
    /* Адаптивность */
    @media (max-width: 991.98px) {
        .order-summary {
            position: relative;
            top: 0;
            margin-top: 30px;
        }
    }
    
    @media (max-width: 767.98px) {
        .form-check {
            padding: 10px;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .product-info {
            flex-direction: column;
        }
        
        .order-summary-img {
            margin-bottom: 10px;
        }
    }
</style>

<main class="main-content">
    <div class="container mt-4">
        <h1 class="mb-4">Оформление заказа</h1>
        
        <?php if ($order_success): ?>
        <!-- Уведомление об успешном оформлении заказа -->
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <div>
                    <h4 class="alert-heading">Спасибо за покупку!</h4>
                    <p>Ваш заказ №<?php echo $order_id; ?> успешно оформлен.</p>
                    <p class="mb-0">Данные о заказе отправлены вам на почту.</p>
                </div>
                </div>
            <div class="d-flex justify-content-end mt-3">
                <a href="/catalog.php" class="btn btn-primary me-2">
                        <i class="fas fa-shopping-bag me-2"></i>Продолжить покупки
                    </a>
                    <?php if ($user_id): ?>
                    <a href="/account/orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>Мои заказы
                    </a>
                    <?php else: ?>
                    <a href="/account/login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Войти в личный кабинет
                    </a>
                    <?php endif; ?>
                </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Контактные данные</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Ваше полное имя" required
                                               value="<?php echo $user ? htmlspecialchars($user['fullname']) : ''; ?>">
                                        <label for="fullname">ФИО <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required
                                               value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX" required
                                               value="<?php echo $user ? htmlspecialchars($user['phone']) : ''; ?>">
                                        <label for="phone">Телефон <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Адрес доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="region" name="region" placeholder="Ваш регион">
                                        <label for="region">Регион</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="city" name="city" placeholder="Ваш город" required>
                                        <label for="city">Город <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="address" name="address" placeholder="Улица, дом, квартира" required>
                                        <label for="address">Адрес <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Почтовый индекс">
                                        <label for="postal_code">Почтовый индекс</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Способ доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_courier" value="courier" checked>
                                <label class="form-check-label" for="delivery_courier">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-truck text-primary me-2"></i>
                                                <strong>Курьерская доставка</strong>
                                            </div>
                                            <p class="mb-0 text-muted mt-1">Доставка в течение 1-3 дней</p>
                                        </div>
                                        <span class="delivery-price-badge">300 ₽</span>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_pickup" value="pickup">
                                <label class="form-check-label" for="delivery_pickup">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-store text-primary me-2"></i>
                                                <strong>Самовывоз из магазина</strong>
                                            </div>
                                            <p class="mb-0 text-muted mt-1">Срок: 1-2 дня</p>
                                        </div>
                                        <span class="delivery-price-badge free">Бесплатно</span>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery_post" value="post">
                                <label class="form-check-label" for="delivery_post">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-mail-bulk text-primary me-2"></i>
                                                <strong>Почта России</strong>
                                            </div>
                                            <p class="mb-0 text-muted mt-1">Доставка в течение 5-10 дней</p>
                                        </div>
                                        <span class="delivery-price-badge">250 ₽</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Способ оплаты</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" checked>
                                <label class="form-check-label" for="payment_card">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-credit-card text-primary me-2"></i>
                                        <strong>Банковской картой онлайн</strong>
                                    </div>
                                    <div class="payment-icons">
                                        <i class="fab fa-cc-visa me-1"></i>
                                        <i class="fab fa-cc-mastercard me-1"></i>
                                        <i class="fab fa-cc-jcb"></i>
                                        <p class="mb-0 text-muted mt-1">Visa, MasterCard, МИР</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash">
                                <label class="form-check-label" for="payment_cash">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                        <strong>Наличными при получении</strong>
                                    </div>
                                    <p class="mb-0 text-muted">Оплата курьеру или в пункте выдачи</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_wallet" value="wallet">
                                <label class="form-check-label" for="payment_wallet">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-wallet text-primary me-2"></i>
                                        <strong>Электронные кошельки</strong>
                                    </div>
                                    <div class="payment-icons">
                                        <i class="fas fa-money-check me-1"></i>
                                        <i class="fas fa-money-bill-alt me-1"></i>
                                        <p class="mb-0 text-muted mt-1">ЮMoney, WebMoney, QIWI</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Комментарий к заказу</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-floating">
                                <textarea class="form-control" id="comment" name="comment" placeholder="Дополнительная информация к заказу" style="min-height: 120px;"></textarea>
                                <label for="comment">Ваш комментарий</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Сводка заказа -->
                <div class="col-lg-4">
                    <div class="card order-summary">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shopping-basket me-2"></i>Ваш заказ</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="list-group-item p-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div class="d-flex product-info">
                                            <div class="order-summary-img me-3">
                                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid">
                                            </div>
                                            <div class="product-details">
                                                <h6 class="mb-1 text-primary fw-semibold"><?php echo $item['name']; ?></h6>
                                                <div class="d-flex flex-wrap align-items-center mt-1">
                                                    <span class="badge bg-light text-dark me-2"><?php echo $item['quantity']; ?> шт.</span>
                                                    <span class="text-muted small"><?php echo number_format($item['price'], 0, '.', ' '); ?> ₽/шт.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end ms-2 product-price">
                                            <span class="item-price-badge"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Товары (<?php echo $total_items; ?>):</span>
                                <span class="text-primary fw-semibold"><?php echo number_format($cart_total, 0, '.', ' '); ?> ₽</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 delivery-cost">
                                <span>Доставка:</span>
                                <span class="delivery-value">300 ₽</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between total-amount align-items-center mb-3">
                                <span class="fw-bold fs-5">Итого:</span>
                                <div class="total-price-wrapper">
                                    <span class="total-price"><?php echo number_format($cart_total + 300, 0, '.', ' '); ?> ₽</span>
                                </div>
                            </div>
                            <button type="submit" name="checkout" class="btn btn-primary w-100">
                                <i class="fas fa-check-circle me-2"></i>Оформить заказ
                            </button>
                            <div class="form-text text-center mt-2">
                                Нажимая на кнопку, вы даете согласие на обработку персональных данных и соглашаетесь с <a href="/legal/privacy-policy.php">политикой конфиденциальности</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

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
        // Добавляем подсветку текущего выбранного способа оплаты и доставки
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const deliveryMethods = document.querySelectorAll('input[name="delivery_method"]');
        
        // Функция для анимации выбора опции
        function animateSelection(element) {
            element.closest('.form-check').querySelector('.form-check-label').classList.add('pulse-animation');
            setTimeout(() => {
                element.closest('.form-check').querySelector('.form-check-label').classList.remove('pulse-animation');
            }, 500);
        }
        
        // Добавляем обработчики для красивой анимации при выборе
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                animateSelection(this);
            });
        });
        
        deliveryMethods.forEach(method => {
            method.addEventListener('change', function() {
                animateSelection(this);
                
                // Обновляем стоимость доставки
                updateDeliveryCost(this.value);
            });
        });
        
        // Обновление стоимости доставки и итоговой суммы
        function updateDeliveryCost(deliveryType) {
            let deliveryPrice = 0;
            
            // Определяем стоимость доставки
            if (deliveryType === 'courier') {
                deliveryPrice = 300;
            } else if (deliveryType === 'post') {
                deliveryPrice = 250;
            }
            
            // Добавляем анимацию к изменению стоимости
            const deliveryCostElement = document.querySelector('.delivery-cost span:last-child');
            const totalPriceElement = document.querySelector('.total-price');
            
            // Добавляем класс для анимации
            deliveryCostElement.classList.add('price-update');
            totalPriceElement.classList.add('price-update');
            
            // Обновляем отображение стоимости доставки
            deliveryCostElement.textContent = deliveryPrice === 0 ? 'Бесплатно' : deliveryPrice + ' ₽';
            
            // Обновляем итоговую сумму
            const cartTotal = <?php echo $cart_total; ?>;
            totalPriceElement.textContent = new Intl.NumberFormat('ru-RU').format(cartTotal + deliveryPrice) + ' ₽';
            
            // Удаляем класс анимации через время
            setTimeout(() => {
                deliveryCostElement.classList.remove('price-update');
                totalPriceElement.classList.remove('price-update');
            }, 500);
        }
        
        // Валидация формы для мгновенной обратной связи
        const requiredFields = checkoutForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            // Проверка при потере фокуса
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            // Очистка ошибки при вводе
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const errorElement = this.nextElementSibling;
                    if (errorElement && errorElement.classList.contains('invalid-feedback')) {
                        errorElement.remove();
                    }
                }
            });
        });
        
        // Функция для валидации поля
        function validateField(field) {
            // Удаляем существующие сообщения об ошибках
            const existingError = field.nextElementSibling;
            if (existingError && existingError.classList.contains('invalid-feedback')) {
                existingError.remove();
            }
            
            // Проверяем заполнение поля
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                
                // Создаем сообщение об ошибке
                const errorMessage = document.createElement('div');
                errorMessage.className = 'invalid-feedback';
                errorMessage.textContent = 'Это поле обязательно для заполнения';
                
                // Вставляем сообщение после поля
                field.insertAdjacentElement('afterend', errorMessage);
                return false;
            } else {
                field.classList.remove('is-invalid');
                
                // Дополнительная валидация для email
                if (field.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        field.classList.add('is-invalid');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'invalid-feedback';
                        errorMessage.textContent = 'Пожалуйста, введите корректный email';
                        field.insertAdjacentElement('afterend', errorMessage);
                        return false;
                    }
                }
                
                // Дополнительная валидация для телефона
                if (field.id === 'phone') {
                    const phoneRegex = /^[\d\+][\d\(\)\ -]{7,14}\d$/;
                    if (!phoneRegex.test(field.value)) {
                        field.classList.add('is-invalid');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'invalid-feedback';
                        errorMessage.textContent = 'Пожалуйста, введите корректный номер телефона';
                        field.insertAdjacentElement('afterend', errorMessage);
                        return false;
                    }
                }
                
                return true;
            }
        }
        
        // Отправка формы
        checkoutForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Проверяем все обязательные поля
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                
                // Плавно прокручиваем к первому полю с ошибкой
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Добавляем анимацию загрузки при отправке формы
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Оформление...';
                submitBtn.disabled = true;
            }
        });
    }
    
    <?php if ($order_success): ?>
    // Автоматическая прокрутка к сообщению об успешном заказе
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Добавляем эффект пульсации для привлечения внимания
        setTimeout(() => {
            successAlert.classList.add('pulse-animation');
        }, 300);
    }
    <?php endif; ?>
});
</script>

<!-- JavaScript для анимаций -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация GSAP ScrollTrigger
    gsap.registerPlugin(ScrollTrigger);
    
    // Анимация заголовка страницы
    gsap.from('h1.mb-4', {
        opacity: 0,
        y: -30,
        duration: 0.8,
        ease: 'power2.out'
    });
    
    // Анимация карточек
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        gsap.from(card, {
            opacity: 0,
            y: 50,
            duration: 0.8,
            delay: 0.1 * (index % 4),
            ease: 'power2.out',
            scrollTrigger: {
                trigger: card,
                start: 'top 90%',
                toggleActions: 'play none none none'
            }
        });
    });
    
    // Анимация полей формы
    const formFields = document.querySelectorAll('.form-floating');
    formFields.forEach((field, index) => {
        gsap.from(field, {
            opacity: 0,
            x: index % 2 === 0 ? -30 : 30,
            duration: 0.6,
            delay: 0.1 * index,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: field,
                start: 'top 90%',
                toggleActions: 'play none none none'
            }
        });
    });
    
    // Анимация радио-кнопок
    const formChecks = document.querySelectorAll('.form-check');
    formChecks.forEach((check, index) => {
        gsap.from(check, {
            opacity: 0,
            x: -30,
            duration: 0.6,
            delay: 0.1 * index,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: check,
                start: 'top 90%',
                toggleActions: 'play none none none'
            }
        });
    });
    
    // Анимация элементов в сводке заказа
    const orderItems = document.querySelectorAll('.list-group-item');
    orderItems.forEach((item, index) => {
        gsap.from(item, {
            opacity: 0,
            scale: 0.9,
            duration: 0.6,
            delay: 0.1 * index,
            ease: 'back.out(1.7)',
            scrollTrigger: {
                trigger: item,
                start: 'top 90%',
                toggleActions: 'play none none none'
            }
        });
    });
    
    // Анимация кнопки отправки
    gsap.from('.btn-primary', {
        opacity: 0,
        scale: 0.8,
        duration: 0.8,
        delay: 0.5,
        ease: 'elastic.out(1, 0.5)',
        scrollTrigger: {
            trigger: '.btn-primary',
            start: 'top 90%',
            toggleActions: 'play none none none'
        }
    });
    
    // Эффект при наведении на элементы формы
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            gsap.to(this, {
                scale: 1.02,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        input.addEventListener('blur', function() {
            gsap.to(this, {
                scale: 1,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    });
    
    // Анимация при выборе способа оплаты или доставки
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const label = this.closest('.form-check');
                gsap.to(label, {
                    backgroundColor: 'rgba(77, 97, 252, 0.05)',
                    borderColor: 'var(--primary-color)',
                    duration: 0.3,
                    ease: 'power2.out'
                });
                
                // Сбрасываем стили для других элементов той же группы
                const name = this.getAttribute('name');
                const otherRadios = document.querySelectorAll(`input[name="${name}"]:not(:checked)`);
                otherRadios.forEach(other => {
                    const otherLabel = other.closest('.form-check');
                    gsap.to(otherLabel, {
                        backgroundColor: '#f8f9fa',
                        borderColor: 'transparent',
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
            }
        });
    });
    
    // Применяем начальные стили для выбранных радио-кнопок
    const checkedRadios = document.querySelectorAll('input[type="radio"]:checked');
    checkedRadios.forEach(radio => {
        const label = radio.closest('.form-check');
        gsap.set(label, {
            backgroundColor: 'rgba(77, 97, 252, 0.05)',
            borderColor: 'var(--primary-color)'
        });
    });
});
</script>

<?php include_once 'includes/footer/footer.php'; ?> 