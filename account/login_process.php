<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Проверяем метод запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $login = trim($_POST["login"]);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]) ? true : false;
    
    // Проверка на пустые поля
    if (empty($login) || empty($password)) {
        // Перенаправляем с сообщением об ошибке
        header("Location: login.php?error=empty_fields");
        exit;
    }
    
    // Пытаемся авторизовать пользователя
    $result = loginUser($login, $password);
    
    if ($result['success']) {
        // Устанавливаем сессию
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['user_fullname'] = $result['user_fullname'];
        $_SESSION['user_email'] = $result['user_email'];
        $_SESSION['user_phone'] = $result['user_phone'];
        $_SESSION['user_login'] = $result['user_login'];
        $_SESSION['user_role'] = $result['user_role'];
        
        // Если пользователь выбрал "Запомнить меня", создаем токен и устанавливаем куку
        if ($remember) {
            $token = generateRememberToken($result['user_id']);
            if (!empty($token)) {
                // Устанавливаем куку на 30 дней
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
            }
        }
        
        // Отправляем уведомление через Telegram
        $user_id = $result['user_id'];
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $login_time = date('d.m.Y H:i:s');
        
        $message = "<b>🔐 Вход в аккаунт</b>\n\n";
        $message .= "Выполнен вход в ваш аккаунт на сайте x64.\n\n";
        $message .= "<b>Время:</b> $login_time\n";
        $message .= "<b>IP-адрес:</b> $user_ip\n";
        $message .= "<b>Устройство:</b> " . htmlspecialchars($user_agent) . "\n\n";
        $message .= "Если это были не вы, немедленно измените пароль в настройках аккаунта.";
        
        // Отправляем уведомление (асинхронно, чтобы не задерживать авторизацию)
        sendTelegramNotification($user_id, $message);
        
        // Перенаправляем в личный кабинет
        header("Location: profile.php");
        exit;
    } else {
        // Перенаправляем с сообщением об ошибке
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
} else {
    // Если запрос не POST, перенаправляем на страницу логина
    header("Location: login.php");
    exit;
} 