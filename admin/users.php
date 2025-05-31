<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<style>
    /* Стили для адаптивной таблицы пользователей */
    .users-table-container {
        width: 100% !important;
        overflow-x: auto !important;
        margin-bottom: 1.5rem !important;
        -webkit-overflow-scrolling: touch !important;
        position: relative !important;
        border-radius: 12px !important;
        box-shadow: none !important;
    }
    
    /* Индикатор прокрутки */
    .users-table-container.has-overflow::after {
        content: "⟷";
        position: absolute;
        right: 10px;
        bottom: 10px;
        background-color: rgba(0, 0, 0, 0.1);
        color: #666;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 14px;
        pointer-events: none;
    }
    
    .users-table {
        width: 100% !important;
        table-layout: fixed !important;
        margin-bottom: 0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }
    
    .users-table th, .users-table td {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        vertical-align: middle !important;
        font-size: 0.9rem !important;
        padding: 0.5rem !important;
    }
    
    /* Фиксированные ширины для колонок */
    .users-table th:nth-child(1), .users-table td:nth-child(1) { /* ID */
        width: 60px !important;
        max-width: 60px !important;
    }
    
    .users-table th:nth-child(2), .users-table td:nth-child(2) { /* ФИО */
        width: 30% !important;
        max-width: 250px !important;
    }
    
    .users-table th:nth-child(3), .users-table td:nth-child(3) { /* Email */
        width: 35% !important;
        max-width: 280px !important;
    }
    
    .users-table th:nth-child(4), .users-table td:nth-child(4) { /* Телефон */
        width: 20% !important;
        max-width: 180px !important;
    }
    
    .users-table th:nth-child(5), .users-table td:nth-child(5) { /* Роль */
        width: 80px !important;
        max-width: 80px !important;
    }
    
    .users-table th:nth-child(6), .users-table td:nth-child(6) { /* Действия */
        width: 90px !important;
        max-width: 90px !important;
    }
    
    .users-table th {
        font-weight: 600 !important;
        border-bottom-width: 1px !important;
    }
    
    /* Компактная версия таблицы для мобильных устройств */
    @media (max-width: 991px) {
        .users-table th, .users-table td {
            font-size: 0.8rem !important;
            padding: 0.4rem !important;
        }
        
        .action-btn {
            width: 28px !important;
            height: 28px !important;
            padding: 0 !important;
        }
    }
    
    /* Стили для небольших экранов */
    @media (max-width: 767px) {
        .users-table td, .users-table th {
            padding: 0.3rem 0.5rem !important;
        }
        
        .profile-body {
            padding: 1rem !important;
        }
        
        .btn {
            padding: 0.375rem 0.5rem !important;
        }
    }
    
    /* Размеры для разных экранов */
    @media (min-width: 992px) {
        .users-table th, .users-table td {
            white-space: normal !important;
        }
    }
    
    /* Стили для отображения ролей */
    .role-badge {
        padding: 0.25em 0.5em !important;
        font-size: 0.7em !important;
        font-weight: 600 !important;
        display: inline-block !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    /* Стили для кнопок действий */
    .action-buttons {
        display: flex !important;
        justify-content: flex-start !important;
        gap: 3px !important;
    }
    
    .action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 28px !important;
        height: 28px !important;
        padding: 0 !important;
        border-width: 1px !important;
    }
    
    .action-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .badge {
        padding: 0.5em 0.8em !important;
        font-weight: 500 !important;
    }
    
    .empty-state {
        padding: 20px !important;
        text-align: center !important;
    }
    
    .empty-state-icon {
        height: 60px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 0.5 !important;
    }
    
    /* Улучшенный макет для таблицы */
    .profile-body {
        overflow: hidden !important; /* Предотвращает выход содержимого за границы */
    }
    
    .card {
        border-radius: 12px !important;
        overflow: hidden !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }
    
    .card-body {
        padding: 0 !important;
    }
    
    /* Таблица с пользователями */
    .profile-main-card {
        box-shadow: none !important;
    }
    
    .profile-body {
        padding: 20px !important;
    }
</style>

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
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                            <h4 class="profile-section-title mb-2 mb-sm-0">Список пользователей</h4>
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
                        
                        <!-- Таблица пользователей -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="table-responsive users-table-container">
                                    <table class="users-table table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="d-none d-md-table-cell">ID</th>
                                                <th>ФИО</th>
                                                <th>Email</th>
                                                <th class="d-none d-md-table-cell">Телефон</th>
                                                <th>Роль</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($users) > 0): ?>
                                                <?php foreach ($users as $u): ?>
                                                    <tr>
                                                        <td class="d-none d-md-table-cell"><?php echo $u['id']; ?></td>
                                                        <td class="text-truncate" title="<?php echo htmlspecialchars($u['fullname']); ?>"><?php echo htmlspecialchars($u['fullname']); ?></td>
                                                        <td class="text-truncate" title="<?php echo htmlspecialchars($u['email']); ?>"><?php echo htmlspecialchars($u['email']); ?></td>
                                                        <td class="text-truncate d-none d-md-table-cell" title="<?php echo htmlspecialchars($u['phone']); ?>"><?php echo htmlspecialchars($u['phone']); ?></td>
                                                        <td>
                                                            <span class="role-badge badge <?php echo $u['role'] === 'Администратор' ? 'bg-danger' : 'bg-primary'; ?> rounded-pill">
                                                                <?php echo $u['role'] === 'Администратор' ? 'Админ' : 'User'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary action-btn" title="Редактировать">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger action-btn" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');" title="Удалить">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
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
                    </div>
                </div>
                
                <!-- Статистика пользователей -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h5 class="profile-info-title">Статистика пользователей</h5>
                    </div>
                    <div class="profile-info-body">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="text-center">
                                    <h2 class="text-primary mb-2"><?php echo count($users); ?></h2>
                                    <p class="text-muted mb-0">Всего</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
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
                                    <p class="text-muted mb-0">Админов</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mt-3 mt-md-0">
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
                                    <p class="text-muted mb-0">Пользователей</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer/footer.php'; ?>

<script>
    // Функция для настройки таблицы
    function setupTable() {
        const tableContainer = document.querySelector('.users-table-container');
        const table = document.querySelector('.users-table');
        
        if (!tableContainer || !table) return;
        
        // Проверяем, нужна ли горизонтальная прокрутка
        const checkOverflow = () => {
            if (table.offsetWidth > tableContainer.clientWidth) {
                tableContainer.classList.add('has-overflow');
            } else {
                tableContainer.classList.remove('has-overflow');
            }
        };
        
        // Вызываем перерасчет размеров таблицы
        const forceReflow = () => {
            table.style.display = 'none';
            // Форсируем перерасчет стилей
            void table.offsetHeight;
            table.style.display = '';
            // Проверяем прокрутку после перерисовки
            setTimeout(checkOverflow, 10);
        };
        
        // Инициализация таблицы
        setTimeout(forceReflow, 100);
        
        // Обработчик изменения размера окна
        window.addEventListener('resize', checkOverflow);
        
        // Обработчик загрузки всех изображений
        window.addEventListener('load', checkOverflow);
    }
    
    // Инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', setupTable);
</script> 