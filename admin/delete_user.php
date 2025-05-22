<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию
session_start();

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

// Проверяем, передан ли ID пользователя для удаления
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$delete_user_id = (int)$_GET['id'];

// Нельзя удалить самого себя
if ($delete_user_id === $user_id) {
    $_SESSION['message'] = 'Вы не можете удалить свою учетную запись';
    $_SESSION['message_type'] = 'danger';
    header("Location: users.php");
    exit;
}

// Функция для удаления пользователя
function deleteUser($id) {
    global $conn;
    
    $sql = "DELETE FROM users WHERE id = " . (int)$id;
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при удалении пользователя: ' . mysqli_error($conn)
        ];
    }
}

// Удаляем пользователя
$result = deleteUser($delete_user_id);

if ($result['success']) {
    $_SESSION['message'] = 'Пользователь успешно удален';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = 'danger';
}

// Перенаправляем обратно на страницу пользователей
header("Location: index.php?tab=users");
exit;
?> 