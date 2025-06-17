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
                <?php include_once 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Основное содержимое -->
            <div class="col-lg-9 col-md-8">
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h2 class="profile-title text-white">Мои заказы</h2>
                        <p class="profile-description text-white">Отслеживайте статус ваших заказов и историю покупок</p>
                        
                        <?php if ($orderCount > 0): ?>
                        <div class="profile-stats-wrapper">
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $orderCount; ?></div>
                                    <div class="stat-label">ЗАКАЗОВ</div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-body">
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
    </div>
</section>

<?php
// Подключаем файл со стилями для профиля
include_once 'includes/profile_styles.php';

// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 