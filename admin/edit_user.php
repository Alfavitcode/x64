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

// Проверяем, передан ли ID пользователя для редактирования
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$edit_user_id = (int)$_GET['id'];
$edit_user = getUserById($edit_user_id);

// Если пользователь для редактирования не найден, перенаправляем на список пользователей
if (!$edit_user) {
    header("Location: users.php");
    exit;
}

// Функция для обновления данных пользователя
function updateUser($id, $fullname, $email, $phone, $login, $role, $password = null) {
    global $conn;
    
    // Подготавливаем SQL запрос без пароля
    $sql = "UPDATE users SET 
            fullname = '" . mysqli_real_escape_string($conn, $fullname) . "', 
            email = '" . mysqli_real_escape_string($conn, $email) . "', 
            phone = '" . mysqli_real_escape_string($conn, $phone) . "', 
            login = '" . mysqli_real_escape_string($conn, $login) . "', 
            role = '" . mysqli_real_escape_string($conn, $role) . "'";
    
    // Если пароль указан, добавляем его в запрос
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = '" . mysqli_real_escape_string($conn, $hashed_password) . "'";
    }
    
    $sql .= " WHERE id = " . (int)$id;
    
    // Выполняем запрос
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных: ' . mysqli_error($conn)
        ];
    }
}

// Обработка формы редактирования пользователя
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
    if (empty($fullname) || empty($email) || empty($phone) || empty($login)) {
        $message = 'Все поля, кроме пароля, обязательны для заполнения';
        $messageType = 'danger';
    } else {
        // Обновление данных пользователя
        $result = updateUser($edit_user_id, $fullname, $email, $phone, $login, $role, $password ?: null);
        
        if ($result['success']) {
            $_SESSION['message'] = 'Данные пользователя успешно обновлены';
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
        <div class="row justify-content-center">
            <!-- Основное содержимое -->
            <div class="col-lg-8 col-md-10">
                <!-- Форма редактирования пользователя -->
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h2 class="profile-name">Редактирование пользователя</h2>
                        <p>Изменение данных пользователя ID: <?php echo $edit_user_id; ?></p>
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
                                        <input type="text" class="form-control rounded-4" id="fullname" name="fullname" value="<?php echo htmlspecialchars($edit_user['fullname']); ?>" placeholder="Иванов Иван Иванович" required>
                                        <label for="fullname">ФИО</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" placeholder="mail@example.com" required>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control rounded-4" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" placeholder="+7 (999) 999-99-99" required>
                                        <label for="phone">Телефон</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control rounded-4" id="login" name="login" value="<?php echo htmlspecialchars($edit_user['login']); ?>" placeholder="username" required>
                                        <label for="login">Логин</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating password-field">
                                        <input type="password" class="form-control rounded-4" id="password" name="password" placeholder="Пароль">
                                        <label for="password">Новый пароль (оставьте пустым, чтобы не менять)</label>
                                        <span class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <select class="form-select rounded-4" id="role" name="role" required>
                                            <option value="user" <?php echo $edit_user['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                            <option value="Администратор" <?php echo $edit_user['role'] === 'Администратор' ? 'selected' : ''; ?>>Администратор</option>
                                        </select>
                                        <label for="role">Роль пользователя</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="index.php?tab=users" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                </a>
                                <button type="submit" class="btn btn-success rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Сохранить изменения
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

/* Добавляем стили для центрирования */
.admin-section {
    padding: 2rem 0;
}

.profile-main-card {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    padding: 2rem;
    margin-bottom: 2rem;
}

.profile-header {
    border-bottom: 1px solid #f2f2f2;
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.profile-name {
    margin-bottom: 0.5rem;
    font-weight: 600;
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
</script>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 