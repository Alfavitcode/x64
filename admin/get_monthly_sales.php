<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Устанавливаем больший лимит времени выполнения для предотвращения тайм-аутов
set_time_limit(60); // 1 минута

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

// Получаем параметры запроса
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;

try {
    // Получаем данные о продажах по месяцам
    $data = getMonthlySalesData($year, $dateFrom, $dateTo);
    
    // Отправляем ответ
    header('Content-Type: application/json');
    echo json_encode($data);
} catch (Exception $e) {
    // Логируем ошибку
    error_log('Error in get_monthly_sales.php: ' . $e->getMessage());
    
    // Отправляем сообщение об ошибке
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Произошла ошибка при получении данных: ' . $e->getMessage()]);
}
?> 