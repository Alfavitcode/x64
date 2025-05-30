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

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/checkout.css">';

// Подключаем шапку сайта
include_once 'includes/header/header.php';
?>

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

<style>
/* Анимация пульсации для уведомления */
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
    100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}

.pulse-animation {
    animation: pulse 1.5s infinite;
}

/* Стили для alert-success */
.alert-success {
    border-left: 5px solid #198754;
}

.alert-success .fa-check-circle {
    color: #198754;
}

/* Предотвращаем скрытие уведомления при прокрутке */
#successAlert {
    position: relative;
    z-index: 100;
}
</style>

<?php include_once 'includes/footer/footer.php'; ?> 