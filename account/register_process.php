<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Проверяем метод запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $login = trim($_POST["login"]);
    $password = $_POST["password"];
    
    // Проверка на пустые поля
    if (empty($fullname) || empty($email) || empty($phone) || empty($login) || empty($password)) {
        // Перенаправляем с сообщением об ошибке
        header("Location: register.php?error=empty_fields");
        exit;
    }
    
    // Пытаемся зарегистрировать пользователя
    $result = registerUser($fullname, $email, $phone, $login, $password);
    
    if ($result['success']) {
        // Устанавливаем сессию
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['user_fullname'] = $fullname;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_login'] = $login;
        
        // Перенаправляем в личный кабинет
        header("Location: profile.php");
        exit;
    } else {
        // Перенаправляем с сообщением об ошибке
        header("Location: register.php?error=registration_failed&message=" . urlencode($result['message']));
        exit;
    }
} else {
    // Если запрос не POST, перенаправляем на страницу регистрации
    header("Location: register.php");
    exit;
}
?> 