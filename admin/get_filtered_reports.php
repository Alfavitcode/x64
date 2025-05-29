<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Устанавливаем больший лимит времени выполнения для предотвращения тайм-аутов
set_time_limit(120); // 2 минуты

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
$dateFrom = isset($_POST['dateFrom']) && !empty($_POST['dateFrom']) ? $_POST['dateFrom'] : null;
$dateTo = isset($_POST['dateTo']) && !empty($_POST['dateTo']) ? $_POST['dateTo'] : null;
$category = isset($_POST['category']) && !empty($_POST['category']) ? $_POST['category'] : null;
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
$per_page = isset($_POST['per_page']) && is_numeric($_POST['per_page']) ? (int)$_POST['per_page'] : 10;

// Валидация параметров
if ($page < 1) $page = 1;
if ($per_page < 1) $per_page = 10;
if ($per_page > 100) $per_page = 100; // Ограничиваем максимальное количество записей

try {
    // Получаем отфильтрованные данные отчета
    $data = getFilteredReportData($dateFrom, $dateTo, $category, $page, $per_page);
    
    // Форматируем статистику для вывода
    $statistics = [
        'totalSales' => number_format($data['totalSales'], 0, '.', ' ') . ' ₽',
        'totalItems' => number_format($data['totalItems'], 0, '.', ' '),
        'totalOrders' => number_format($data['totalOrders'], 0, '.', ' '),
        'averageOrder' => $data['totalOrders'] > 0 ? 
            number_format($data['totalSales'] / $data['totalOrders'], 0, '.', ' ') . ' ₽' : 
            '0 ₽'
    ];
    
    // Получаем данные для графика
    $chartData = getMonthlySalesData();
    
    // Формируем ответ
    $response = [
        'reportData' => $data['reportData'],
        'pagination' => $data['pagination'],
        'statistics' => $statistics,
        'chart' => $chartData['monthlySales']
    ];
    
    // Отправляем ответ
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    // Логируем ошибку
    error_log('Error in get_filtered_reports.php: ' . $e->getMessage());
    
    // Отправляем сообщение об ошибке
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Произошла ошибка при получении данных: ' . $e->getMessage()]);
}
?> 