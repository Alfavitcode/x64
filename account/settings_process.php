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

// Получаем ID пользователя из сессии
$user_id = $_SESSION['user_id'];

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    
    // Валидация данных
    $errors = [];
    
    // Проверка ФИО
    if (empty($fullname)) {
        $errors[] = "ФИО не может быть пустым";
    }
    
    // Проверка телефона
    if (empty($phone)) {
        $errors[] = "Телефон не может быть пустым";
    }
    
    // Если нет ошибок, обновляем данные
    if (empty($errors)) {
        // Создаем массив с данными для обновления
        $data = [
            'fullname' => $fullname,
            'phone' => $phone
        ];
        
        // Обновляем данные пользователя
        $result = updateUserData($user_id, $data);
        
        if ($result['success']) {
            // Устанавливаем сообщение об успехе
            $_SESSION['success_message'] = "Данные профиля успешно обновлены";
        } else {
            // Устанавливаем сообщение об ошибке
            $_SESSION['error_message'] = "Ошибка при обновлении данных: " . $result['message'];
        }
    } else {
        // Устанавливаем сообщение об ошибке
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
    
    // Перенаправляем обратно на страницу настроек
    header("Location: settings.php");
    exit;
} else {
    // Если запрос не POST, перенаправляем на страницу настроек
    header("Location: settings.php");
    exit;
}
?> 