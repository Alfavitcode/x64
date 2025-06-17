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
?>

<!-- Подключаем GSAP для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<section class="profile-section">
    <div class="profile-container">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-lg-3 col-md-4 mb-4">
                <?php include_once 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Основное содержимое -->
            <div class="col-lg-9 col-md-8">
                <!-- Приветствие -->
                <div class="profile-main-card">
                    <div class="profile-header">
                        <p class="profile-welcome">Добро пожаловать,</p>
                        <h2 class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                        <p>Здесь вы можете управлять своим профилем, заказами и настройками учетной записи.</p>
                        
                        <!-- Статистика -->
                        <div class="profile-stats-wrapper">
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $orderCount; ?></div>
                                    <div class="stat-label">ЗАКАЗОВ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-body">
                        <div class="profile-content">
                            <h4 class="profile-section-title">Что можно сделать</h4>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 rounded-4 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                                    <i class="fas fa-shopping-bag text-primary"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Мои заказы</h5>
                                            </div>
                                            <p class="card-text text-muted">Просматривайте историю и статус ваших заказов</p>
                                            <a href="orders.php" class="btn btn-sm btn-outline-primary rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 rounded-4 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                                    <i class="fas fa-cog text-success"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Настройки</h5>
                                            </div>
                                            <p class="card-text text-muted">Обновите информацию вашего профиля</p>
                                            <a href="settings.php" class="btn btn-sm btn-outline-success rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 rounded-4 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                                    <i class="fab fa-telegram-plane text-primary"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Привязка Telegram</h5>
                                            </div>
                                            <p class="card-text text-muted">Получайте уведомления и управляйте аккаунтом через Telegram</p>
                                            <a href="telegram.php" class="btn btn-sm btn-outline-primary rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Информация профиля -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h5 class="profile-info-title">Информация профиля</h5>
                        <a href="settings.php" class="profile-edit-btn">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                    </div>
                    <div class="profile-info-body">
                        <div class="profile-info-row">
                            <div class="profile-info-label">ФИО:</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($user['fullname']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Логин:</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($user['login']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Email:</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Телефон:</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Роль:</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($user['role']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Последние заказы (если есть) -->
                <?php if ($orderCount > 0): ?>
                <div class="profile-info-card mt-4">
                    <div class="profile-info-header">
                        <h5 class="profile-info-title">Последние заказы</h5>
                        <a href="orders.php" class="profile-view-all-btn">
                            <i class="fas fa-eye"></i> Все заказы
                        </a>
                    </div>
                    <div class="profile-info-body">
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
                                    <?php 
                                    // Показываем только последние 3 заказа
                                    $recentOrders = array_slice($orders, 0, 3);
                                    foreach ($recentOrders as $order): 
                                        // Определяем класс для статуса
                                        $statusClass = '';
                                        $statusText = '';
                                        
                                        switch($order['status']) {
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                $statusText = 'Ожидает';
                                                break;
                                            case 'processing':
                                                $statusClass = 'status-processing';
                                                $statusText = 'В обработке';
                                                break;
                                            case 'completed':
                                                $statusClass = 'status-completed';
                                                $statusText = 'Выполнен';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-cancelled';
                                                $statusText = 'Отменен';
                                                break;
                                            case 'closed':
                                                $statusClass = 'status-closed';
                                                $statusText = 'Закрыт';
                                                break;
                                            default:
                                                $statusClass = 'status-default';
                                                $statusText = 'Неизвестно';
                                        }
                                    ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $order['id']; ?></td>
                                        <td class="order-date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                        <td class="order-amount"><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</td>
                                        <td class="order-status">
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
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
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Подключаем файл со стилями для профиля
include_once 'includes/profile_styles.php';
?>

<!-- Дополнительный стиль для устранения белой полоски -->
<style>
.footer {
    margin-top: -1px !important;
}
main {
    overflow: hidden;
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?>

<!-- Подключаем скрипт анимаций для профиля -->
<script src="../js/animations/profile-animations.js"></script> 