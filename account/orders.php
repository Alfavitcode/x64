<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Подключаем шапку сайта
include_once '../includes/header/header.php';

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден, выходим из системы
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Получаем заказы пользователя
$orders = getUserOrders($user_id);

// Функция для получения названия статуса заказа на русском
function getOrderStatusName($status) {
    $statuses = [
        'pending' => 'Ожидает обработки',
        'processing' => 'В обработке',
        'shipped' => 'Отправлен',
        'delivered' => 'Доставлен',
        'completed' => 'Выполнен',
        'cancelled' => 'Отменен',
        'closed' => 'Закрыт'
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : 'Неизвестный статус';
}

// Функция для получения класса индикатора статуса
function getStatusClass($status) {
    $classes = [
        'pending' => 'bg-warning',
        'processing' => 'bg-info',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'closed' => 'bg-secondary'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'bg-secondary';
}

// Получаем детали заказа, если указан ID
$order_details = null;
$order_items = [];

if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $order_details = getOrderById($order_id, $user_id);
    
    if ($order_details) {
        $order_items = getOrderItems($order_id);
    }
}
?>

<section class="profile-section">
    <div class="profile-container">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-card profile-sidebar">
                    <div class="profile-menu-header">
                        <h5 class="mb-0">Личный кабинет</h5>
                    </div>
                    <ul class="profile-menu">
                        <li class="profile-menu-item">
                            <a href="profile.php">
                                <i class="fas fa-user"></i>
                                Мой профиль
                            </a>
                        </li>
                        <li class="profile-menu-item active">
                            <a href="orders.php">
                                <i class="fas fa-shopping-bag"></i>
                                Мои заказы
                            </a>
                        </li>
                        
                        <li class="profile-menu-item">
                            <a href="settings.php">
                                <i class="fas fa-cog"></i>
                                Настройки
                            </a>
                        </li>
                        <li class="profile-menu-item logout">
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Выйти
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Основное содержимое -->
            <div class="col-lg-9 col-md-8">
                <!-- Если отображаются детали заказа -->
                <?php if ($order_details): ?>
                
                <div class="profile-main-card mb-4">
                    <div class="profile-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="profile-name text-white">Детали заказа #<?php echo $order_details['id']; ?></h2>
                            <a href="orders.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Назад к заказам
                            </a>
                        </div>
                        <div class="order-meta">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Дата заказа</small>
                                    <span class="text-white"><?php echo date('d.m.Y H:i', strtotime($order_details['created_at'])); ?></span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Статус</small>
                                    <span class="badge <?php echo getStatusClass($order_details['status']); ?> rounded-pill px-3 py-2">
                                        <?php echo getOrderStatusName($order_details['status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted d-block">Сумма заказа</small>
                                    <span class="fw-bold text-white"><?php echo number_format($order_details['total_amount'], 0, '.', ' '); ?> ₽</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="order-details-card">
                                    <h5 class="mb-3">Информация о заказе</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Способ оплаты</small>
                                        <span class="fw-medium">
                                            <?php 
                                            $payment_methods = [
                                                'card' => 'Банковской картой онлайн',
                                                'cash' => 'Наличными при получении',
                                                'wallet' => 'Электронные кошельки'
                                            ];
                                            echo isset($payment_methods[$order_details['payment_method']]) 
                                                ? $payment_methods[$order_details['payment_method']] 
                                                : $order_details['payment_method'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Способ доставки</small>
                                        <span class="fw-medium">
                                            <?php 
                                            $delivery_methods = [
                                                'courier' => 'Курьерская доставка',
                                                'pickup' => 'Самовывоз из магазина',
                                                'post' => 'Почта России'
                                            ];
                                            echo isset($delivery_methods[$order_details['delivery_method']]) 
                                                ? $delivery_methods[$order_details['delivery_method']] 
                                                : $order_details['delivery_method'];
                                            ?>
                                        </span>
                                    </div>
                                    <?php if ($order_details['comment']): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Комментарий к заказу</small>
                                        <span class="text-break"><?php echo htmlspecialchars($order_details['comment']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($order_details['status'] === 'cancelled' && !empty($order_details['cancel_reason'])): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Причина отмены</small>
                                        <span class="text-break text-danger"><?php echo htmlspecialchars($order_details['cancel_reason']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="order-details-card">
                                    <h5 class="mb-3">Адрес доставки</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Получатель</small>
                                        <span class="fw-medium"><?php echo htmlspecialchars($order_details['fullname']); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Телефон</small>
                                        <span class="fw-medium"><?php echo htmlspecialchars($order_details['phone']); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Email</small>
                                        <span class="fw-medium"><?php echo htmlspecialchars($order_details['email']); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Адрес</small>
                                        <span class="text-break fw-medium">
                                            <?php 
                                            echo htmlspecialchars($order_details['region'] ? $order_details['region'] . ', ' : '');
                                            echo htmlspecialchars($order_details['city'] . ', ');
                                            echo htmlspecialchars($order_details['address']);
                                            echo htmlspecialchars($order_details['postal_code'] ? ', ' . $order_details['postal_code'] : '');
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Товары в заказе</h5>
                        <div class="order-items-list">
                            <?php foreach ($order_items as $item): ?>
                            <div class="order-item-card mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <a href="/product.php?id=<?php echo $item['product_id']; ?>" class="order-item-image me-3">
                                                <img src="/img/products/placeholder.jpg" alt="<?php echo $item['name']; ?>">
                                            </a>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="/product.php?id=<?php echo $item['product_id']; ?>" class="text-dark text-decoration-none">
                                                        <?php echo htmlspecialchars($item['name']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">Цена: <?php echo number_format($item['price'], 0, '.', ' '); ?> ₽</small>
                                                <small class="text-muted d-block">Количество: <?php echo $item['quantity']; ?> шт.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <span class="fw-bold text-primary"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-total-summary mt-4">
                            <div class="row">
                                <div class="col-md-8 offset-md-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Товары:</span>
                                        <span class="fw-medium"><?php echo number_format($order_details['total_amount'] - $order_details['delivery_cost'], 0, '.', ' '); ?> ₽</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Доставка:</span>
                                        <span class="fw-medium"><?php echo $order_details['delivery_cost'] > 0 ? number_format($order_details['delivery_cost'], 0, '.', ' ') . ' ₽' : 'Бесплатно'; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 total-row">
                                        <strong>Итого:</strong>
                                        <strong class="text-primary"><?php echo number_format($order_details['total_amount'], 0, '.', ' '); ?> ₽</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Карточка заказов -->
                <div class="profile-main-card">
                    <div class="profile-header">
                        <div class="orders-header">
                            <h2 class="profile-name text-white">Мои заказы</h2>
                            <p class="text-white mb-0">Здесь вы можете просмотреть все ваши заказы и их статус.</p>
                        </div>
                    </div>
                    
                    <div class="profile-body">
                        <?php if (empty($orders)): ?>
                        <!-- Пустая история заказов -->
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon mb-4">
                                    <i class="fas fa-shopping-bag fa-4x text-primary opacity-25"></i>
                                </div>
                                <h4 class="mb-3">У вас пока нет заказов</h4>
                                <p class="text-muted mb-4">Когда вы сделаете заказ, он появится здесь, и вы сможете отслеживать его статус.</p>
                                <a href="/catalog.php" class="btn btn-primary rounded-pill px-5 py-2">
                                    <i class="fas fa-store me-2"></i>Перейти в каталог
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Список заказов -->
                        <div class="orders-container">
                            <?php foreach ($orders as $order): ?>
                            <div class="profile-info-card mb-4">
                                <div class="order-item">
                                    <div class="order-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="order-number">Заказ #<?php echo $order['id']; ?></span>
                                                <span class="order-date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></span>
                                            </div>
                                            <div class="order-status">
                                                <span class="badge <?php echo getStatusClass($order['status']); ?> rounded-pill px-3 py-2">
                                                    <?php echo getOrderStatusName($order['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0">
                                                    <strong>Способ доставки:</strong> 
                                                    <?php 
                                                    $delivery_methods = [
                                                        'courier' => 'Курьерская доставка',
                                                        'pickup' => 'Самовывоз из магазина',
                                                        'post' => 'Почта России'
                                                    ];
                                                    echo isset($delivery_methods[$order['delivery_method']]) 
                                                        ? $delivery_methods[$order['delivery_method']] 
                                                        : $order['delivery_method'];
                                                    ?>
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Способ оплаты:</strong> 
                                                    <?php 
                                                    $payment_methods = [
                                                        'card' => 'Банковской картой онлайн',
                                                        'cash' => 'Наличными при получении',
                                                        'wallet' => 'Электронные кошельки'
                                                    ];
                                                    echo isset($payment_methods[$order['payment_method']]) 
                                                        ? $payment_methods[$order['payment_method']] 
                                                        : $order['payment_method'];
                                                    ?>
                                                </p>
                                                </div>
                                            <div class="order-total text-end">
                                                <p class="mb-1">Итого: <span class="order-total-price"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</span></p>
                                                <a href="?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Дополнительные стили для страницы заказов */
.empty-state {
    padding: 30px;
    max-width: 500px;
    margin: 0 auto;
}

.empty-state-icon {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-item {
    padding: 20px;
}

.order-header {
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f7;
    margin-bottom: 15px;
}

.order-number {
    font-weight: 600;
    font-size: 16px;
    margin-right: 15px;
}

.order-date {
    color: #6c757d;
    font-size: 14px;
}

.order-total-price {
    font-weight: 700;
    font-size: 18px;
    color: var(--primary-color);
}

/* Глобальные стили для профиля */
.profile-main-card {
    background-color: #ffffff;
    color: #333;
}

.profile-header {
    color: #333;
}

.profile-name {
    color: #212529;
}

/* Специальные стили для деталей заказа */
.order-meta, .orders-header {
    background: var(--primary-color);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px !important;
}

.order-meta small.text-muted, .orders-header small.text-muted {
    color: #b3d7ff !important;
}

.order-meta span.text-white, .orders-header span.text-white, .orders-header h2.text-white, .orders-header p.text-white {
    color: #ffffff !important;
    font-weight: 500;
}

.order-details-card {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    height: 100%;
    color: #333;
}

.order-details-card small.text-muted {
    color: #6c757d !important;
}

.order-item-card {
    padding: 15px;
    border: 1px solid #f0f0f7;
    border-radius: 10px;
    background-color: #ffffff;
    color: #333;
}

.order-item-image {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.order-item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.total-row {
    padding-top: 10px;
    margin-top: 10px;
    border-top: 1px solid #f0f0f7;
    font-size: 18px;
}

/* Стили для отображения статусов заказа */
.badge.bg-warning {
    color: #212529;
}

.badge.bg-info {
    color: #212529;
}

.order-total-summary {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    color: #333;
}

/* Стиль для карточек списка заказов */
.profile-info-card {
    background-color: #ffffff;
    color: #333;
}

.profile-menu-item a {
    color: #495057;
}

.profile-menu-item.active a {
    color: var(--primary-color);
}

/* Стиль для карточек заказов в списке */
.order-item .order-header {
    background: var(--primary-color);
    border-radius: 10px 10px 0 0;
    padding: 15px;
    margin-bottom: 0;
    border-bottom: none;
}

.order-item .order-header .order-number,
.order-item .order-header .order-date {
    color: #ffffff;
}

.order-item .order-body {
    padding: 15px;
}

/* Медиа-запросы для мобильных устройств */
@media (max-width: 767px) {
    .order-total {
        flex-direction: column;
        align-items: flex-start;
        margin-top: 10px;
    }
    
    .order-total p {
        margin-bottom: 10px;
    }
    
    .order-meta .row {
        margin-bottom: -15px;
    }
    
    /* Улучшение отображения заказов на мобильных устройствах */
    .order-item .order-body {
        padding: 15px 10px;
    }
    
    .order-item .order-body .d-flex {
        flex-direction: column;
    }
    
    .order-item .order-body .d-flex > div {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .order-total.text-end {
        text-align: left !important;
    }
    
    .order-total-price {
        display: inline-block;
        margin-bottom: 10px;
    }
    
    /* Улучшение отображения информации о доставке и оплате */
    .order-item .order-body p.mb-0 {
        margin-bottom: 8px !important;
        line-height: 1.4;
        word-break: break-word;
    }
    
    /* Улучшение отображения статуса заказа */
    .order-item .order-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-item .order-header .order-status {
        margin-top: 10px;
        width: 100%;
    }
    
    .badge.rounded-pill {
        display: inline-block;
        margin-top: 5px;
        font-size: 12px;
        padding: 6px 12px !important;
        white-space: normal;
        text-align: center;
        line-height: 1.4;
    }
    
    /* Улучшение отображения номера заказа и даты */
    .order-number {
        display: block;
        margin-bottom: 5px;
    }
    
    /* Улучшение отображения карточки заказа */
    .profile-info-card {
        margin-bottom: 15px;
    }
    
    /* Увеличение размера шрифта для лучшей читаемости */
    .order-item .order-body strong {
        font-size: 15px;
    }
    
    /* Добавляем кнопку "Подробнее" */
    .btn.btn-sm.btn-outline-primary.rounded-pill {
        display: inline-block;
        margin-top: 5px;
        padding: 8px 16px;
        font-size: 14px;
        width: 100%;
        text-align: center;
    }
    
    /* Улучшение отображения для деталей заказа */
    .order-item {
        padding: 0;
    }
    
    /* Улучшение отображения информации о заказе */
    .profile-header h2.profile-name {
        font-size: 18px;
    }
    
    /* Улучшение отображения для карточек с деталями заказа */
    .order-details-card {
        padding: 12px;
        margin-bottom: 15px;
    }
    
    /* Улучшение отображения деталей заказа на странице детального просмотра */
    .order-meta .row .col-md-4 {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .order-items-list .order-item-card .row {
        margin-left: -5px;
        margin-right: -5px;
    }
    
    .order-items-list .order-item-card .col-md-8,
    .order-items-list .order-item-card .col-md-4 {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .order-item-card .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-item-card .order-item-image {
        margin-bottom: 10px;
        width: 100px;
        height: 100px;
    }
    
    .order-total-summary {
        padding: 10px;
    }
    
    .order-total-summary .col-md-8.offset-md-4 {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    /* Улучшение отображения кнопки "Назад к заказам" */
    .profile-header .d-flex.justify-content-between {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .profile-header .d-flex.justify-content-between .btn {
        margin-top: 10px;
    }
}

/* Стили для очень маленьких экранов */
@media (max-width: 375px) {
    .order-item .order-header {
        padding: 12px 10px;
    }
    
    .order-number {
        font-size: 14px;
    }
    
    .order-date {
        font-size: 13px;
    }
    
    .order-item .order-body {
        padding: 12px 10px;
    }
    
    .order-total-price {
        font-size: 16px;
    }
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 