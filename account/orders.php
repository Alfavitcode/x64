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
$orderCount = count($orders);

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
                <div class="orders-container">
                    <div class="orders-header">
                        <h2>Мои заказы</h2>
                        <p class="orders-description">Отслеживайте статус ваших заказов и историю покупок</p>
                        
                        <?php if ($orderCount > 0): ?>
                        <div class="orders-count">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $orderCount; ?></div>
                                <div class="stat-label">ЗАКАЗОВ</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($orderCount === 0): ?>
                    <div class="empty-orders">
                        <div class="empty-orders-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>У вас пока нет заказов</h3>
                        <p>Начните делать покупки, чтобы ваши заказы отображались здесь</p>
                        <a href="/" class="btn-primary">Перейти в каталог</a>
                    </div>
                    <?php else: ?>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>№ Заказа</th>
                                    <th>Дата</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): 
                                    $statusInfo = getOrderStatusInfo($order['status']);
                                ?>
                                <tr>
                                    <td class="order-id">#<?php echo $order['id']; ?></td>
                                    <td class="order-date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                    <td class="order-amount"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</td>
                                    <td class="order-status">
                                        <span class="status-badge <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['text']; ?></span>
                                    </td>
                                    <td class="order-actions">
                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="view-order-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Стили для страницы заказов */
.orders-container {
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 25px;
    padding: 30px;
}

.orders-header {
    margin-bottom: 30px;
}

.orders-header h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.orders-description {
    color: #666;
    margin-bottom: 20px;
}

.orders-count {
    margin-bottom: 25px;
}

.stat-item {
    background-color: #5165F6;
    border-radius: 15px;
    padding: 20px 35px;
    text-align: center;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(81, 101, 246, 0.3);
    transition: all 0.3s ease;
    border: none;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: white;
    margin-bottom: 5px;
    display: block;
    line-height: 1.2;
}

.stat-label {
    color: rgba(255, 255, 255, 0.85);
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 1px;
}

.empty-orders {
    text-align: center;
    padding: 50px 20px;
}

.empty-orders-icon {
    font-size: 60px;
    color: #5165F6;
    opacity: 0.2;
    margin-bottom: 20px;
}

.empty-orders h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.empty-orders p {
    color: #666;
    margin-bottom: 25px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.btn-primary {
    display: inline-block;
    background-color: #5165F6;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 12px 30px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #3a4ccc;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.3);
    color: white;
}

/* Стили для таблицы заказов */
.orders-table-container {
    width: 100%;
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.orders-table th {
    color: #666;
    font-weight: 500;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f7;
    font-size: 14px;
}

.orders-table td {
    padding: 15px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f7;
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.order-id {
    font-weight: 600;
    color: #333;
}

.order-date {
    color: #666;
}

.order-amount {
    font-weight: 600;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 500;
    text-align: center;
    min-width: 100px;
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

.order-actions {
    text-align: center;
}

.view-order-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(81, 101, 246, 0.1);
    color: #5165F6;
    transition: all 0.3s ease;
}

.view-order-btn:hover {
    background-color: #5165F6;
    color: white;
    transform: scale(1.1);
}

/* Адаптивность */
@media (max-width: 767px) {
    .orders-container {
        padding: 20px 15px;
    }
    
    .orders-header h2 {
        font-size: 22px;
    }
    
    .stat-item {
        padding: 15px 25px;
    }
    
    .stat-value {
        font-size: 30px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .orders-table th:nth-child(2), 
    .orders-table td:nth-child(2) {
        display: none;
    }
    
    .orders-table th, 
    .orders-table td {
        padding: 12px 10px;
    }
    
    .status-badge {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 80px;
    }
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 