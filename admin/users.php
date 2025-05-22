<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию
session_start();

// Если пользователь не авторизован, перенаправляем на страницу входа
if(!isset($_SESSION['user_id'])) {
    header("Location: ../account/login.php");
    exit;
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден или не является администратором, перенаправляем на главную
if (!$user || $user['role'] !== 'Администратор') {
    header("Location: /");
    exit;
}

// Проверяем, есть ли сообщение в сессии
$message = '';
$messageType = '';

if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    
    // Очищаем сообщение из сессии
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Получаем всех пользователей
function getAllUsers() {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, role FROM users ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    $users = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

$users = getAllUsers();

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<section class="admin-section">
    <div class="container">
        <div class="row">
            <!-- Боковое меню админ-панели -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-card profile-sidebar">
                    <div class="profile-menu-header">
                        <h5 class="mb-0">Админ-панель</h5>
                    </div>
                    <ul class="profile-menu">
                        <li class="profile-menu-item">
                            <a href="index.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Панель управления
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="products.php">
                                <i class="fas fa-box"></i>
                                Управление товарами
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="categories.php">
                                <i class="fas fa-tags"></i>
                                Категории
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="orders.php">
                                <i class="fas fa-shopping-bag"></i>
                                Заказы
                            </a>
                        </li>
                        <li class="profile-menu-item active">
                            <a href="users.php">
                                <i class="fas fa-users"></i>
                                Пользователи
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="settings.php">
                                <i class="fas fa-cog"></i>
                                Настройки
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="/account/profile.php">
                                <i class="fas fa-user"></i>
                                Личный кабинет
                            </a>
                        </li>
                        <li class="profile-menu-item logout">
                            <a href="../account/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Выйти
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Основное содержимое -->
            <div class="col-lg-9 col-md-8">
                <!-- Управление пользователями -->
                <div class="profile-main-card mb-4">
                    <div class="profile-header">
                        <h2 class="profile-name">Управление пользователями</h2>
                        <p>Просмотр и управление пользователями сайта</p>
                    </div>
                    
                    <div class="profile-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="profile-section-title mb-0">Список пользователей</h4>
                            <a href="add_user.php" class="btn btn-primary rounded-pill">
                                <i class="fas fa-plus me-2"></i>Добавить пользователя
                            </a>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-4 mb-4" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ФИО</th>
                                        <th>Email</th>
                                        <th>Телефон</th>
                                        <th>Логин</th>
                                        <th>Роль</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?php echo $u['id']; ?></td>
                                                <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td><?php echo htmlspecialchars($u['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($u['login']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $u['role'] === 'Администратор' ? 'bg-danger' : 'bg-primary'; ?> rounded-pill">
                                                        <?php echo htmlspecialchars($u['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon mb-3">
                                                        <i class="fas fa-users fa-3x text-muted"></i>
                                                    </div>
                                                    <h5>Нет пользователей</h5>
                                                    <p class="text-muted">Пользователи не найдены в базе данных</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Статистика пользователей -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h5 class="profile-info-title">Статистика пользователей</h5>
                    </div>
                    <div class="profile-info-body">
                        <div class="row">
                            <div class="col-md-4 mb-4 mb-md-0">
                                <div class="text-center">
                                    <h2 class="text-primary mb-2"><?php echo count($users); ?></h2>
                                    <p class="text-muted mb-0">Всего пользователей</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4 mb-md-0">
                                <div class="text-center">
                                    <h2 class="text-danger mb-2">
                                        <?php 
                                        $adminCount = 0;
                                        foreach ($users as $u) {
                                            if ($u['role'] === 'Администратор') $adminCount++;
                                        }
                                        echo $adminCount;
                                        ?>
                                    </h2>
                                    <p class="text-muted mb-0">Администраторов</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h2 class="text-success mb-2">
                                        <?php 
                                        $userCount = 0;
                                        foreach ($users as $u) {
                                            if ($u['role'] === 'user') $userCount++;
                                        }
                                        echo $userCount;
                                        ?>
                                    </h2>
                                    <p class="text-muted mb-0">Обычных пользователей</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Дополнительные стили для страницы пользователей */
.table {
    border-radius: 12px;
    overflow: hidden;
}

.table th {
    font-weight: 600;
    border-bottom-width: 1px;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.empty-state {
    padding: 20px;
    text-align: center;
}

.empty-state-icon {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.5;
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 