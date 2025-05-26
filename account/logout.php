<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Сохраняем ID пользователя перед удалением сессии
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Удаляем токен "Запомнить меня" из базы данных
if ($user_id) {
    removeRememberToken($user_id);
}

// Удаляем куку "Запомнить меня"
setcookie('remember_token', '', time() - 3600, '/', '', false, true);

// Уничтожаем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на главную страницу
header("Location: /");
exit;
?> 