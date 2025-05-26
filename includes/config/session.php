<?php
/**
 * Файл для управления сессиями
 * Включается перед любым выводом в браузер
 */

// Проверяем, не были ли уже отправлены заголовки и не запущена ли уже сессия
if (!headers_sent() && session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации по токену "Запомнить меня"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    // Подключаем файлы конфигурации, если они еще не подключены
    if (!function_exists('validateRememberToken')) {
        require_once __DIR__ . '/db_config.php';
        require_once __DIR__ . '/db_functions.php';
    }
    
    // Проверяем токен и получаем данные пользователя
    $user = validateRememberToken($_COOKIE['remember_token']);
    
    if ($user) {
        // Устанавливаем сессию
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_fullname'] = $user['user_fullname'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_phone'] = $user['user_phone'];
        $_SESSION['user_login'] = $user['user_login'];
        $_SESSION['user_role'] = $user['user_role'];
        
        // Обновляем токен для продления срока действия
        $token = generateRememberToken($user['user_id']);
        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
    } else {
        // Если токен недействителен, удаляем куку
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
} 