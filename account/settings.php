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
                        <li class="profile-menu-item">
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
                        
                        <li class="profile-menu-item active">
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
                <!-- Настройки профиля -->
                <div class="profile-main-card mb-4">
                    <div class="profile-header">
                        <h2 class="profile-name">Настройки профиля</h2>
                        <p>Обновите ваши персональные данные</p>
                    </div>
                    
                    <div class="profile-body">
                        <form action="settings_process.php" method="post" id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" placeholder="Иванов Иван Иванович" required>
                                        <label for="fullname">ФИО</label>
                                        <small class="text-muted">Отчество не обязательно</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" placeholder="username" required>
                                        <label for="login">Логин</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="mail@example.com" required>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control rounded-4" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="+7 (999) 999-99-99" required>
                                        <label for="phone">Телефон</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="role" value="<?php echo htmlspecialchars($user['role']); ?>" placeholder="Роль" readonly>
                                        <label for="role">Роль</label>
                                        <small class="text-muted">Роль пользователя изменить нельзя</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info rounded-4 mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Функционал в разработке</h5>
                                        <p class="mb-0">В настоящее время функционал изменения настроек находится в разработке. Скоро вы сможете обновлять свои данные.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2" disabled>
                                    <i class="fas fa-save me-2"></i>Сохранить изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Смена пароля -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h5 class="profile-info-title">Смена пароля</h5>
                    </div>
                    <div class="profile-info-body">
                        <form action="change_password.php" method="post" id="passwordForm">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="password" class="form-control rounded-4" id="current_password" name="current_password" placeholder="Текущий пароль" required>
                                        <label for="current_password">Текущий пароль</label>
                                    </div>
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating password-field">
                                        <input type="password" class="form-control rounded-4" id="new_password" name="new_password" placeholder="Новый пароль" required>
                                        <label for="new_password">Новый пароль</label>
                                        <span class="password-toggle" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating password-field">
                                        <input type="password" class="form-control rounded-4" id="confirm_password" name="confirm_password" placeholder="Подтверждение нового пароля" required>
                                        <label for="confirm_password">Подтверждение нового пароля</label>
                                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="password-strength mb-4 d-none">
                                <div class="progress rounded-pill" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Слабый</small>
                                    <small class="text-muted">Средний</small>
                                    <small class="text-muted">Сильный</small>
                                </div>
                            </div>
                            
                            <div class="alert alert-info rounded-4 mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Функционал в разработке</h5>
                                        <p class="mb-0">В настоящее время функционал смены пароля находится в разработке. Скоро вы сможете сменить свой пароль.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-danger rounded-pill px-4 py-2" disabled>
                                    <i class="fas fa-lock me-2"></i>Изменить пароль
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
/* Дополнительные стили для страницы настроек */
.form-floating {
    position: relative;
}

.form-floating > .form-control {
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
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    opacity: 0.65;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

.form-control {
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(77, 97, 252, 0.15);
}

.rounded-4 {
    border-radius: 12px !important;
}

.alert-info {
    background-color: rgba(23, 162, 184, 0.05);
    border-color: rgba(23, 162, 184, 0.1);
    color: #0c5460;
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

/* Стили для индикатора силы пароля */
.password-strength {
    margin-top: -15px;
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
    const formControls = document.querySelectorAll('.form-control');
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