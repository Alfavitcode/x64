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
                        <li class="profile-menu-item active">
                            <a href="profile.php">
                                <i class="fas fa-user"></i>
                                Мой профиль
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="orders.php">
                                <i class="fas fa-shopping-bag"></i>
                                Мои заказы
                                <?php if ($orderCount > 0): ?>
                                <span class="badge rounded-pill bg-primary ms-2"><?php echo $orderCount; ?></span>
                                <?php endif; ?>
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
                                    <div class="stat-icon mb-2">
                                        <i class="fas fa-shopping-bag text-primary"></i>
                                    </div>
                                    <span class="stat-value"><?php echo $orderCount; ?></span>
                                    <span class="stat-label">Заказов</span>
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
                        <a href="orders.php" class="profile-edit-btn">
                            <i class="fas fa-eye"></i> Все заказы
                        </a>
                    </div>
                    <div class="profile-info-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                $statusClass = 'bg-warning';
                                                $statusText = 'Ожидает';
                                                break;
                                            case 'processing':
                                                $statusClass = 'bg-info';
                                                $statusText = 'Обрабатывается';
                                                break;
                                            case 'completed':
                                                $statusClass = 'bg-success';
                                                $statusText = 'Выполнен';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-danger';
                                                $statusText = 'Отменен';
                                                break;
                                            case 'closed':
                                                $statusClass = 'bg-secondary';
                                                $statusText = 'Закрыт';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                                $statusText = 'Неизвестно';
                                        }
                                    ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> ₽</td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
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

<script>
// JavaScript для анимаций при наведении
document.addEventListener('DOMContentLoaded', function() {
    // Анимация для карточек быстрого доступа
    const actionCards = document.querySelectorAll('.card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 10px 25px rgba(77, 97, 252, 0.2)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(77, 97, 252, 0.1)';
        });
    });
});
</script>

<style>
/* Стили для мобильной адаптивности */
@media (max-width: 767px) {
    .profile-content .card {
        margin-bottom: 15px;
    }
    
    .profile-stats {
        flex-direction: row;
        justify-content: space-around;
    }
    
    .stat-item {
        text-align: center;
        padding: 0 10px;
    }
    
    .profile-info-row {
        flex-direction: column;
        padding: 8px 0;
    }
    
    .profile-info-label {
        margin-bottom: 5px;
    }
    
    .profile-info-value {
        padding-left: 0;
    }
    
    .profile-main-card {
        padding: 15px;
    }
    
    .profile-header {
        padding: 15px;
    }
    
    .profile-body {
        padding: 15px;
    }
}

/* Улучшения для всех устройств */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Улучшенные стили для статистики */
.profile-stats-wrapper {
    margin: 25px 0;
}

.profile-stats {
    display: flex;
    justify-content: flex-start;
}

.stat-item {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    min-width: 150px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #eaeaea;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: var(--primary-color);
}

.stat-icon {
    font-size: 22px;
    color: var(--primary-color);
    background-color: rgba(77, 97, 252, 0.1);
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 auto 10px;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 5px;
    display: block;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Адаптация для разных устройств */
@media (max-width: 991px) {
    .profile-stats {
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .stat-item {
        padding: 15px;
        min-width: 120px;
    }
    
    .stat-value {
        font-size: 24px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 