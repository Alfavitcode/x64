<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем информацию о пользователе из базы данных
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден, выходим из системы
if (!$user) {
    header("Location: logout.php");
    exit;
}

// Обновляем данные сессии в соответствии с новой структурой
$_SESSION['user_fullname'] = $user['fullname'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_phone'] = $user['phone'];
$_SESSION['user_login'] = $user['login'];
$_SESSION['user_role'] = $user['role'];

// Удаляем устаревшие данные, если они есть
if (isset($_SESSION['user_name'])) unset($_SESSION['user_name']);
if (isset($_SESSION['user_surname'])) unset($_SESSION['user_surname']);
if (isset($_SESSION['user_patronymic'])) unset($_SESSION['user_patronymic']);

// Сообщение об успешном обновлении
echo '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновление сессии</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>Успешно</h4>
            </div>
            <div class="card-body">
                <p class="mb-3">Данные сессии успешно обновлены.</p>
                <a href="profile.php" class="btn btn-primary">Вернуться в профиль</a>
            </div>
        </div>
    </div>
</body>
</html>';
?> 