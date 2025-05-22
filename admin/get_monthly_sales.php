<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден или не является администратором, возвращаем ошибку
if (!$user || $user['role'] !== 'Администратор') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

// Получаем текущий год
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Получаем данные о продажах по месяцам
$monthlySales = getMonthlySalesData($year);

// Отправляем данные в формате JSON
header('Content-Type: application/json');
echo json_encode(['monthlySales' => $monthlySales]);
?> 