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

<!-- Подключаем GSAP для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<!-- Стили для предотвращения мерцания контента -->
<style id="preload-styles">
    .order-detail-container,
    .order-progress-wrapper,
    .info-card,
    .order-item,
    .order-summary {
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Сбрасываем стили после полной загрузки страницы */
    .js-content-loaded #preload-styles {
        display: none;
    }
</style>

<section class="profile-section">
    <div class="profile-container">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-lg-3 col-md-4 mb-4">
                <?php include_once 'includes/sidebar.php'; ?>
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
                    
                    <!-- Индикатор прогресса заказа (новый элемент) -->
                    <div class="order-progress-wrapper">
                        <div class="order-progress">
                            <div class="progress-step <?php echo in_array($order['status'], ['pending', 'processing', 'shipped', 'delivered', 'completed']) ? 'active' : ''; ?>">
                                <div class="step-icon"><i class="fas fa-check"></i></div>
                                <div class="step-label">Заказ создан</div>
                            </div>
                            <div class="progress-step <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered', 'completed']) ? 'active' : ''; ?>">
                                <div class="step-icon"><i class="fas fa-box"></i></div>
                                <div class="step-label">В обработке</div>
                            </div>
                            <div class="progress-step <?php echo in_array($order['status'], ['shipped', 'delivered', 'completed']) ? 'active' : ''; ?>">
                                <div class="step-icon"><i class="fas fa-truck"></i></div>
                                <div class="step-label">Отправлен</div>
                            </div>
                            <div class="progress-step <?php echo in_array($order['status'], ['delivered', 'completed']) ? 'active' : ''; ?>">
                                <div class="step-icon"><i class="fas fa-home"></i></div>
                                <div class="step-label">Доставлен</div>
                            </div>
                            <div class="progress-step <?php echo $order['status'] === 'completed' ? 'active' : ''; ?>">
                                <div class="step-icon"><i class="fas fa-star"></i></div>
                                <div class="step-label">Выполнен</div>
                            </div>
                            
                            <div class="progress-line">
                                <div class="progress-line-inner" style="
                                    width: <?php 
                                        switch($order['status']) {
                                            case 'pending': echo '10%'; break;
                                            case 'processing': echo '30%'; break;
                                            case 'shipped': echo '50%'; break;
                                            case 'delivered': echo '80%'; break;
                                            case 'completed': echo '100%'; break;
                                            case 'cancelled': case 'closed': echo '0%'; break;
                                            default: echo '0%';
                                        }
                                    ?>"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-info-section">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
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
                                    <div class="info-card-icon">
                                        <i class="fas fa-truck"></i>
                                    </div>
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
                        <div class="section-header">
                            <h3 class="section-title">Товары в заказе</h3>
                            <div class="section-icon"><i class="fas fa-shopping-basket"></i></div>
                        </div>
                        
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
    box-shadow: 0 10px 30px rgba(81, 101, 246, 0.15);
    overflow: hidden;
    margin-bottom: 30px;
    padding: 35px;
    position: relative;
    transition: all 0.4s ease;
}

.order-detail-container::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
    background: linear-gradient(135deg, rgba(81, 101, 246, 0.05) 0%, rgba(81, 101, 246, 0) 70%);
    border-radius: 0 0 0 100%;
    z-index: 0;
}

.order-detail-header {
    margin-bottom: 25px;
    border-bottom: 1px solid #f0f0f7;
    padding-bottom: 25px;
    position: relative;
    z-index: 1;
}

.order-title h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #333;
    position: relative;
    display: inline-block;
}

.order-title h2::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(to right, #5165F6, #4e73df);
    border-radius: 3px;
}

.order-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

.order-date {
    color: #666;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.order-date::before {
    content: '\f073';
    font-family: 'Font Awesome 5 Free';
    margin-right: 5px;
    color: #5165F6;
    font-weight: 900;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    color: #5165F6;
    font-weight: 500;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 30px;
    background-color: rgba(81, 101, 246, 0.1);
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(81, 101, 246, 0.1);
}

.back-btn:hover {
    background-color: #5165F6;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.2);
}

.back-btn i {
    margin-right: 8px;
    transition: all 0.3s ease;
}

/* Новый компонент - индикатор прогресса заказа */
.order-progress-wrapper {
    margin-bottom: 35px;
    position: relative;
    z-index: 1;
}

.order-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    padding: 0 10px;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
    max-width: 20%;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f0f0f7;
    color: #adb5bd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    transition: all 0.4s ease;
    font-size: 16px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.progress-step.active .step-icon {
    background: linear-gradient(135deg, #5165F6, #4e73df);
    color: white;
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.3);
}

.step-label {
    font-size: 13px;
    color: #666;
    text-align: center;
    font-weight: 500;
    transition: all 0.4s ease;
}

.progress-step.active .step-label {
    color: #5165F6;
    font-weight: 600;
}

.progress-line {
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #f0f0f7;
    z-index: 1;
}

.progress-line-inner {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(to right, #5165F6, #4e73df);
    transition: width 1s ease;
    border-radius: 3px;
}

.order-info-section {
    margin-bottom: 35px;
    position: relative;
    z-index: 1;
}

.info-card {
    background-color: #f8f9fa;
    border-radius: 16px;
    padding: 25px;
    height: 100%;
    transition: all 0.3s ease;
    border: 1px solid rgba(240, 240, 247, 0.7);
    position: relative;
    overflow: hidden;
}

.info-card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 24px;
    color: #5165F6;
    opacity: 0.2;
    transition: all 0.3s ease;
}

.info-card:hover .info-card-icon {
    transform: scale(1.2);
    opacity: 0.4;
}

.info-card-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    position: relative;
    display: inline-block;
}

.info-card-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(to right, #5165F6, #4e73df);
    border-radius: 2px;
}

.info-item {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    position: relative;
    padding-left: 0;
}

.info-label {
    color: #666;
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 500;
}

.info-value {
    font-weight: 500;
    color: #333;
    font-size: 15px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-icon {
    font-size: 22px;
    color: #5165F6;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(81, 101, 246, 0.1) 0%, rgba(81, 101, 246, 0.05) 100%);
    box-shadow: 0 2px 10px rgba(81, 101, 246, 0.1);
}

.section-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 0;
    color: #333;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(to right, #5165F6, #4e73df);
    border-radius: 3px;
}

.order-items-container {
    margin-bottom: 30px;
}

.order-item {
    display: flex;
    padding: 20px;
    border: 1px solid #f0f0f7;
    border-radius: 16px;
    margin-bottom: 15px;
    background-color: #fff;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    --before-opacity: 0; /* CSS переменная для анимации левой полосы */
}

.order-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, #5165F6, #4e73df);
    opacity: var(--before-opacity, 0); /* Используем CSS переменную */
    transition: opacity 0.3s ease;
}

.order-item:hover::before {
    opacity: 1;
}

.item-image {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    overflow: hidden;
    margin-right: 20px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
}

.item-image .placeholder-text {
    font-size: 28px;
    font-weight: 700;
    color: #5165F6;
    background: linear-gradient(135deg, rgba(81, 101, 246, 0.2) 0%, rgba(81, 101, 246, 0.1) 100%);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
}

.item-details {
    flex: 1;
    padding-right: 15px;
}

.item-name {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
    transition: all 0.3s ease;
}

.item-meta {
    display: flex;
    gap: 20px;
    font-size: 15px;
    color: #666;
}

.item-price {
    font-weight: 600;
    color: #5165F6;
    display: flex;
    align-items: center;
}

.item-price::before {
    content: '\f3d1';
    font-family: 'Font Awesome 5 Free';
    margin-right: 5px;
    font-weight: 900;
    font-size: 14px;
}

.item-quantity {
    display: flex;
    align-items: center;
}

.item-quantity::before {
    content: '\f291';
    font-family: 'Font Awesome 5 Free';
    margin-right: 5px;
    color: #5165F6;
    font-weight: 900;
    font-size: 14px;
}

.item-total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    min-width: 140px;
    padding-left: 20px;
    border-left: 1px solid #f0f0f7;
}

.total-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.total-value {
    font-size: 18px;
    font-weight: 700;
    color: #5165F6;
    transition: all 0.3s ease;
}

.order-summary {
    background: linear-gradient(to right, #f8f9fa, #f5f7ff);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
    border: 1px solid rgba(240, 240, 247, 0.7);
    position: relative;
    overflow: hidden;
}

.order-summary::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, rgba(81, 101, 246, 0.1) 0%, rgba(81, 101, 246, 0) 70%);
    border-radius: 50%;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.summary-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.summary-item.total {
    font-weight: 700;
    font-size: 20px;
    color: #333;
    padding-top: 5px;
}

.summary-item.total .summary-value {
    color: #5165F6;
    font-size: 22px;
}

.status-badge {
    display: inline-block;
    padding: 7px 15px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    min-width: 120px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.status-pending {
    background: linear-gradient(135deg, #FFD166 0%, #FFC233 100%);
    color: #333;
}

.status-processing {
    background: linear-gradient(135deg, #06AED5 0%, #0095B6 100%);
    color: white;
}

.status-shipped {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.status-delivered {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    color: white;
}

.status-completed {
    background: linear-gradient(135deg, #42BA96 0%, #2D9D7A 100%);
    color: white;
}

.status-cancelled {
    background: linear-gradient(135deg, #DF4759 0%, #C9364A 100%);
    color: white;
}

.status-closed {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.status-default {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

/* Декоративные элементы */
.decor-circle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(81, 101, 246, 0.15) 0%, rgba(81, 101, 246, 0) 70%);
    opacity: 0.6;
    z-index: 0;
    pointer-events: none;
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
    
    .order-progress {
        overflow-x: auto;
        padding-bottom: 15px;
        -webkit-overflow-scrolling: touch;
    }
    
    .progress-step {
        min-width: 80px;
    }
    
    .step-label {
        font-size: 12px;
    }
    
    .order-item {
        flex-direction: column;
    }
    
    .item-image {
        margin-bottom: 15px;
        margin-right: 0;
    }
    
    .item-meta {
        flex-direction: column;
        gap: 5px;
    }
    
    .item-total {
        align-items: flex-start;
        margin-top: 15px;
        border-left: none;
        border-top: 1px solid #f0f0f7;
        padding-left: 0;
        padding-top: 15px;
    }
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?>

<!-- Подключаем скрипт анимаций для страницы просмотра заказа -->
<script src="../js/animations/order-view-animations.js"></script>

<!-- Скрипт для отметки полной загрузки контента -->
<script>
    window.addEventListener('load', function() {
        document.documentElement.classList.add('js-content-loaded');
    });
</script> 