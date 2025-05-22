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

// Обработка формы добавления пользователя
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    
    // Валидация полей
    if (empty($fullname) || empty($email) || empty($phone) || empty($login) || empty($password)) {
        $message = 'Все поля обязательны для заполнения';
        $messageType = 'danger';
    } else {
        // Регистрация нового пользователя
        $result = registerUser($fullname, $email, $phone, $login, $password, $role);
        
        if ($result['success']) {
            $_SESSION['message'] = 'Пользователь успешно добавлен';
            $_SESSION['message_type'] = 'success';
            header("Location: index.php?tab=users");
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

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
                <!-- Форма добавления пользователя -->
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h2 class="profile-name">Добавление пользователя</h2>
                        <p>Создайте нового пользователя в системе</p>
                    </div>
                    
                    <div class="profile-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-4" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" placeholder="Иванов Иван Иванович" required>
                                        <label for="fullname">ФИО</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="mail@example.com" required>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control rounded-4" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+7 (999) 999-99-99" required>
                                        <label for="phone">Телефон</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="login" name="login" value="<?php echo htmlspecialchars($login ?? ''); ?>" placeholder="username" required>
                                        <label for="login">Логин</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating password-field">
                                        <input type="password" class="form-control rounded-4" id="password" name="password" placeholder="Пароль" required>
                                        <label for="password">Пароль</label>
                                        <span class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <select class="form-select rounded-4" id="role" name="role" required>
                                            <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>Пользователь</option>
                                            <option value="Администратор" <?php echo (isset($role) && $role === 'Администратор') ? 'selected' : ''; ?>>Администратор</option>
                                        </select>
                                        <label for="role">Роль пользователя</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="index.php?tab=users" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-plus me-2"></i>Добавить пользователя
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Дополнительные стили для формы */
.form-floating {
    position: relative;
    margin-bottom: 10px;
}

.form-floating > .form-control,
.form-floating > .form-select {
    padding: 1.5rem 1rem 0.5rem;
    height: calc(3.5rem + 2px);
}

.form-floating > label {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 1rem 1rem;
    pointer-events: none;
    border: 1px solid transparent;
    transform-origin: 0 0;
    transition: opacity .1s ease-in-out, transform .1s ease-in-out;
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label,
.form-floating > .form-select ~ label {
    opacity: 0.65;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

.form-control, .form-select {
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(77, 97, 252, 0.15);
}

.rounded-4 {
    border-radius: 12px !important;
}

/* Стили для поля пароля */
.password-field {
    position: relative;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 5;
    color: #6c757d;
    transition: all 0.3s;
}

.password-toggle:hover {
    color: var(--primary-color);
}
</style>

<script>
// Функция для переключения видимости пароля
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Анимации для форм
document.addEventListener('DOMContentLoaded', function() {
    // Анимация для полей формы при фокусе
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-5px)';
            this.parentElement.style.transition = 'all 0.3s ease';
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 