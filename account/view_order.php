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

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден, выходим из системы
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Проверяем, передан ли ID заказа
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = (int)$_GET['id'];

// Получаем информацию о заказе
$order = getOrderById($order_id, $user_id);

// Если заказ не найден или принадлежит другому пользователю, перенаправляем на страницу со всеми заказами
if (!$order) {
    header("Location: orders.php");
    exit;
}

// Получаем товары в заказе
$order_items = getOrderItems($order_id);

// Получаем информацию об изображениях товаров из базы данных
global $conn;
foreach ($order_items as &$item) {
    $product_id = (int)$item['product_id'];
    $sql = "SELECT image FROM product WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $item['image'] = $row['image'];
    } else {
        $item['image'] = null;
    }
}
unset($item); // Разрываем ссылку

// Подключаем шапку сайта
include_once '../includes/header/header.php';

// Функция для определения статуса заказа
function getOrderStatusInfo($status) {
    $statuses = [
        'pending' => [
            'class' => 'status-pending',
            'text' => 'Ожидает'
        ],
        'processing' => [
            'class' => 'status-processing',
            'text' => 'В обработке'
        ],
        'shipped' => [
            'class' => 'status-shipped',
            'text' => 'Отправлен'
        ],
        'delivered' => [
            'class' => 'status-delivered',
            'text' => 'Доставлен'
        ],
        'completed' => [
            'class' => 'status-completed',
            'text' => 'Выполнен'
        ],
        'cancelled' => [
            'class' => 'status-cancelled',
            'text' => 'Отменен'
        ],
        'closed' => [
            'class' => 'status-closed',
            'text' => 'Закрыт'
        ]
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : [
        'class' => 'status-default',
        'text' => 'Неизвестно'
    ];
}

$statusInfo = getOrderStatusInfo($order['status']);
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
                            <a href="telegram.php">
                                <i class="fab fa-telegram"></i>
                                Привязка Telegram
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
                <div class="order-detail-container">
                    <div class="order-detail-header">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="order-title">
                                <h2>Заказ #<?php echo $order['id']; ?></h2>
                                <div class="order-meta">
                                    <span class="order-date">от <?php echo date('d.m.Y', strtotime($order['created_at'])); ?></span>
                                    <span class="status-badge <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['text']; ?></span>
                                </div>
                            </div>
                            <a href="orders.php" class="back-btn">
                                <i class="fas fa-arrow-left"></i> Назад к заказам
                            </a>
                        </div>
                    </div>
                    
                    <div class="order-info-section">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="info-card">
                                    <h3 class="info-card-title">Информация о заказе</h3>
                                    <div class="info-item">
                                        <span class="info-label">Статус заказа:</span>
                                        <span class="status-badge <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['text']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Дата заказа:</span>
                                        <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Способ оплаты:</span>
                                        <span class="info-value">
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
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Способ доставки:</span>
                                        <span class="info-value">
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
                                        </span>
                                    </div>
                                    <?php if (!empty($order['comment'])): ?>
                                    <div class="info-item">
                                        <span class="info-label">Комментарий:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($order['comment']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="info-card">
                                    <h3 class="info-card-title">Информация о доставке</h3>
                                    <div class="info-item">
                                        <span class="info-label">Получатель:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($order['fullname']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Телефон:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Адрес доставки:</span>
                                        <span class="info-value">
                                            <?php
                                            $address = [];
                                            if (!empty($order['region'])) $address[] = htmlspecialchars($order['region']);
                                            if (!empty($order['city'])) $address[] = htmlspecialchars($order['city']);
                                            if (!empty($order['address'])) $address[] = htmlspecialchars($order['address']);
                                            if (!empty($order['postal_code'])) $address[] = htmlspecialchars($order['postal_code']);
                                            echo implode(', ', $address);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-items-section">
                        <h3 class="section-title">Товары в заказе</h3>
                        
                        <div class="order-items-container">
                            <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php if (!empty($item['image']) && file_exists('../' . ltrim($item['image'], '/'))): ?>
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-text">
                                            <?php 
                                            // Получаем первые буквы слов из названия товара
                                            $words = explode(' ', $item['name']);
                                            $initials = '';
                                            foreach ($words as $word) {
                                                $initials .= mb_substr($word, 0, 1, 'UTF-8');
                                                if (strlen($initials) >= 2) break;
                                            }
                                            echo htmlspecialchars($initials);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <div class="item-meta">
                                        <span class="item-price"><?php echo number_format($item['price'], 0, '.', ' '); ?> ₽</span>
                                        <span class="item-quantity">Количество: <?php echo $item['quantity']; ?></span>
                                    </div>
                                </div>
                                <div class="item-total">
                                    <span class="total-label">Итого:</span>
                                    <span class="total-value"><?php echo number_format($item['subtotal'], 0, '.', ' '); ?> ₽</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-summary">
                            <div class="summary-item">
                                <span class="summary-label">Стоимость товаров:</span>
                                <span class="summary-value"><?php echo number_format($order['total_amount'] - $order['delivery_cost'], 0, '.', ' '); ?> ₽</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Стоимость доставки:</span>
                                <span class="summary-value">
                                    <?php echo $order['delivery_cost'] > 0 
                                        ? number_format($order['delivery_cost'], 0, '.', ' ') . ' ₽' 
                                        : 'Бесплатно'; ?>
                                </span>
                            </div>
                            <div class="summary-item total">
                                <span class="summary-label">Итого:</span>
                                <span class="summary-value"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Стили для страницы деталей заказа */
.order-detail-container {
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 25px;
    padding: 30px;
}

.order-detail-header {
    margin-bottom: 20px;
    border-bottom: 1px solid #f0f0f7;
    padding-bottom: 20px;
}

.order-title h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.order-meta {
    display: flex;
    align-items: center;
    gap: 15px;
}

.order-date {
    color: #666;
    font-size: 14px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    color: #5165F6;
    font-weight: 500;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 30px;
    background-color: rgba(81, 101, 246, 0.1);
    transition: all 0.3s ease;
}

.back-btn:hover {
    background-color: #5165F6;
    color: white;
}

.back-btn i {
    margin-right: 6px;
}

.order-info-section {
    margin-bottom: 30px;
}

.info-card {
    background-color: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
    height: 100%;
}

.info-card-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.info-item {
    margin-bottom: 12px;
    display: flex;
    flex-direction: column;
}

.info-label {
    color: #666;
    margin-bottom: 4px;
    font-size: 13px;
}

.info-value {
    font-weight: 500;
    color: #333;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.order-items-container {
    margin-bottom: 30px;
}

.order-item {
    display: flex;
    padding: 15px;
    border: 1px solid #f0f0f7;
    border-radius: 10px;
    margin-bottom: 15px;
    background-color: #fff;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    margin-right: 15px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.item-image .placeholder-text {
    font-size: 24px;
    font-weight: 700;
    color: #5165F6;
    background-color: rgba(81, 101, 246, 0.1);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.item-meta {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #666;
}

.item-price {
    font-weight: 500;
}

.item-total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    min-width: 120px;
}

.total-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 4px;
}

.total-value {
    font-size: 16px;
    font-weight: 700;
    color: #5165F6;
}

.order-summary {
    background-color: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.summary-item.total {
    font-weight: 700;
    font-size: 18px;
    color: #333;
}

.summary-item.total .summary-value {
    color: #5165F6;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 500;
    text-align: center;
}

.status-pending {
    background-color: #FFD166;
    color: #333;
}

.status-processing {
    background-color: #06AED5;
    color: white;
}

.status-shipped {
    background-color: #3498db;
    color: white;
}

.status-delivered {
    background-color: #9b59b6;
    color: white;
}

.status-completed {
    background-color: #42BA96;
    color: white;
}

.status-cancelled {
    background-color: #DF4759;
    color: white;
}

.status-closed {
    background-color: #6c757d;
    color: white;
}

.status-default {
    background-color: #6c757d;
    color: white;
}

/* Адаптивность */
@media (max-width: 767px) {
    .order-detail-container {
        padding: 20px 15px;
    }
    
    .order-detail-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .back-btn {
        margin-top: 15px;
    }
    
    .order-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .order-item {
        flex-direction: column;
    }
    
    .item-image {
        margin-bottom: 15px;
    }
    
    .item-meta {
        flex-direction: column;
        gap: 5px;
    }
    
    .item-total {
        align-items: flex-start;
        margin-top: 15px;
    }
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 