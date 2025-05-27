<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден, выходим из системы
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Обработка формы привязки Telegram
$message = '';
$telegram_linked = false;

// Проверяем, привязан ли уже Telegram
$sql = "SELECT telegram_id, telegram_username FROM users WHERE id = " . (int)$user_id;
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $telegram_data = mysqli_fetch_assoc($result);
    if (!empty($telegram_data['telegram_id'])) {
        $telegram_linked = true;
        $telegram_username = !empty($telegram_data['telegram_username']) ? '@' . $telegram_data['telegram_username'] : 'Неизвестно';
    }
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка кода
    if (isset($_POST['verify_code'])) {
        $entered_code = $_POST['code'];
        
        // Проверяем код в таблице telegram_verification_codes
        $sql = "SELECT * FROM telegram_verification_codes WHERE code = '" . mysqli_real_escape_string($conn, $entered_code) . "' AND expires_at > NOW()";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $verification = mysqli_fetch_assoc($result);
            $telegram_id = $verification['chat_id'];
            $telegram_username = $verification['username'];
            
            // Привязываем Telegram к аккаунту
            $update_sql = "UPDATE users SET 
                          telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "',
                          telegram_username = " . ($telegram_username ? "'" . mysqli_real_escape_string($conn, $telegram_username) . "'" : "NULL") . "
                          WHERE id = " . (int)$user_id;
            
            if (mysqli_query($conn, $update_sql)) {
                // Удаляем использованный код
                mysqli_query($conn, "DELETE FROM telegram_verification_codes WHERE code = '" . mysqli_real_escape_string($conn, $entered_code) . "'");
                
                $telegram_linked = true;
                $message = 'Telegram успешно привязан к вашему аккаунту.';
            } else {
                $message = 'Ошибка при привязке Telegram: ' . mysqli_error($conn);
            }
        } else {
            $message = 'Неверный код подтверждения или срок его действия истек.';
        }
    }
    
    // Отвязка Telegram
    if (isset($_POST['unlink_telegram'])) {
        $result = unlinkTelegram($user_id);
        
        if ($result['success']) {
            $telegram_linked = false;
            $message = $result['message'];
        } else {
            $message = $result['message'];
        }
    }
}

// Подключаем шапку сайта
include_once '../includes/header/header.php';
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
                        <li class="profile-menu-item active">
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
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h2 class="profile-title">Привязка Telegram</h2>
                        <p class="profile-description">Привяжите свой аккаунт Telegram для получения уведомлений и управления аккаунтом через мессенджер.</p>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $telegram_linked ? 'alert-success' : 'alert-info'; ?> mb-4">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="profile-body">
                        <?php if ($telegram_linked): ?>
                        <!-- Информация о привязанном аккаунте -->
                        <div class="telegram-linked-info">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                            <i class="fab fa-telegram-plane text-primary fa-2x"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">Telegram привязан</h4>
                                            <p class="text-muted mb-0">Ваш аккаунт Telegram успешно привязан</p>
                                        </div>
                                    </div>
                                    
                                    <div class="telegram-info mb-4">
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Имя пользователя:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($telegram_username); ?></div>
                                        </div>
                                    </div>
                                    
                                    <form method="post" action="">
                                        <button type="submit" name="unlink_telegram" class="btn btn-outline-danger">
                                            <i class="fas fa-unlink me-2"></i>Отвязать Telegram
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Форма для привязки аккаунта -->
                        <div class="telegram-binding-form">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h4 class="mb-4">Как привязать Telegram:</h4>
                                    
                                    <div class="steps mb-4">
                                        <div class="step d-flex mb-3">
                                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">1</div>
                                            <div class="step-content">
                                                <p class="mb-0">Найдите нашего бота в Telegram: <a href="<?php echo TELEGRAM_BOT_URL; ?>" target="_blank" class="fw-bold">@<?php echo TELEGRAM_BOT_USERNAME; ?></a></p>
                                            </div>
                                        </div>
                                        
                                        <div class="step d-flex mb-3">
                                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">2</div>
                                            <div class="step-content">
                                                <p class="mb-0">Отправьте команду <code>/start</code> для начала работы с ботом</p>
                                            </div>
                                        </div>
                                        
                                        <div class="step d-flex mb-3">
                                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">3</div>
                                            <div class="step-content">
                                                <p class="mb-0">Отправьте команду <code>/code</code> для получения кода привязки</p>
                                            </div>
                                        </div>
                                        
                                        <div class="step d-flex mb-3">
                                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">4</div>
                                            <div class="step-content">
                                                <p class="mb-0">Введите полученный код в поле ниже и нажмите "Подтвердить"</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <form method="post" action="" id="verifyForm">
                                        <div class="mb-3">
                                            <label for="code" class="form-label">Введите код от бота:</label>
                                            <input type="text" class="form-control" id="code" name="code" placeholder="Введите 5-значный код" required>
                                        </div>
                                        
                                        <button type="submit" name="verify_code" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Подтвердить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.step-number {
    width: 30px;
    height: 30px;
    min-width: 30px;
    font-weight: bold;
}

.verification-code {
    letter-spacing: 3px;
    font-family: monospace;
}

code {
    background-color: #f8f9fa;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: monospace;
}
</style>

<?php
// Подключаем подвал сайта
include_once '../includes/footer/footer.php';
?> 