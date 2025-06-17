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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация данных
    $errors = [];
    
    // Проверка текущего пароля
    if (empty($current_password)) {
        $errors[] = "Введите текущий пароль";
    }
    
    // Проверка нового пароля
    if (empty($new_password)) {
        $errors[] = "Введите новый пароль";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Новый пароль должен содержать не менее 6 символов";
    }
    
    // Проверка подтверждения пароля
    if ($new_password !== $confirm_password) {
        $errors[] = "Пароли не совпадают";
    }
    
    // Если нет ошибок, проверяем текущий пароль и обновляем
    if (empty($errors)) {
        // Получаем информацию о пользователе
        $user = getUserById($user_id);
        
        if ($user) {
            // Проверяем текущий пароль
            if (password_verify($current_password, $user['password'])) {
                // Хешируем новый пароль
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Создаем массив с данными для обновления
                $data = [
                    'password' => $hashed_password
                ];
                
                // Обновляем пароль пользователя
                $result = updateUserData($user_id, $data);
                
                if ($result['success']) {
                    // Устанавливаем сообщение об успехе
                    $_SESSION['success_message'] = "Пароль успешно изменен";
                } else {
                    // Устанавливаем сообщение об ошибке
                    $_SESSION['error_message'] = "Ошибка при изменении пароля: " . $result['message'];
                }
            } else {
                $_SESSION['error_message'] = "Неверный текущий пароль";
            }
        } else {
            $_SESSION['error_message'] = "Пользователь не найден";
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