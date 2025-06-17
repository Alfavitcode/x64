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

// Получаем заказы пользователя для отображения счетчика
$orders = getUserOrders($user_id);
$orderCount = count($orders);
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
                <!-- Сообщения об успехе и ошибках -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
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
                                        <input type="text" class="form-control rounded-4" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" placeholder="username" readonly>
                                        <label for="login">Логин</label>
                                        <small class="text-muted">Логин изменить нельзя</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="mail@example.com" readonly>
                                        <label for="email">Email</label>
                                        <small class="text-muted">Email изменить нельзя</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control rounded-4" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="+7 (999) 999-99-99" required>
                                        <label for="phone">Телефон</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
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
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-danger rounded-pill px-4 py-2">
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

<?php
// Подключаем файл со стилями для профиля
include_once 'includes/profile_styles.php';

// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?>

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

.password-field {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    z-index: 10;
}

.password-toggle:hover {
    color: var(--primary-color);
}
</style>

<script>
// Функция для переключения видимости пароля
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.password-toggle i');
    
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

// Проверка совпадения паролей
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordForm = document.getElementById('passwordForm');
    
    passwordForm.addEventListener('submit', function(event) {
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            event.preventDefault();
            alert('Пароли не совпадают!');
        }
    });
});
</script> 