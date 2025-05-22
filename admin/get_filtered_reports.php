<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Проверяем авторизацию
session_start();
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

// Получаем отфильтрованные данные
$data = getFilteredReportData($dateFrom, $dateTo, $category);

// Получаем данные для графика с учетом выбранного периода
if ($dateFrom && $dateTo) {
    // Если выбран диапазон дат, получаем данные только за этот период
    $year = date('Y', strtotime($dateFrom));
    $chart_data = getMonthlySalesData($year);
    
    // Если период захватывает другой год, получаем данные для него тоже
    $year_to = date('Y', strtotime($dateTo));
    if ($year_to > $year) {
        $chart_data_next_year = getMonthlySalesData($year_to);
        
        // Объединяем данные
        $month_from = (int)date('n', strtotime($dateFrom));
        $month_to = (int)date('n', strtotime($dateTo));
        
        // Сбрасываем месяцы вне выбранного периода
        for ($i = 1; $i < $month_from; $i++) {
            $chart_data[$i - 1] = 0;
        }
        
        for ($i = $month_to + 1; $i <= 12; $i++) {
            $chart_data_next_year[$i - 1] = 0;
        }
        
        // Если выбран период, включающий два года, комбинируем данные
        if ($year_to - $year == 1) {
            for ($i = 1; $i <= $month_to; $i++) {
                $chart_data[11 + $i] = $chart_data_next_year[$i - 1];
            }
        }
    } else {
        // Если период в пределах одного года, обнуляем месяцы вне периода
        $month_from = (int)date('n', strtotime($dateFrom));
        $month_to = (int)date('n', strtotime($dateTo));
        
        for ($i = 1; $i < $month_from; $i++) {
            $chart_data[$i - 1] = 0;
        }
        
        for ($i = $month_to + 1; $i <= 12; $i++) {
            $chart_data[$i - 1] = 0;
        }
    }
} else {
    // Если период не выбран, используем текущий год
    $chart_data = getMonthlySalesData(date('Y'));
}

// Добавляем данные графика в результат
$data['chart'] = $chart_data;

// Форматируем данные для отправки
$formattedData = [
    'reportData' => $data['reportData'],
    'statistics' => [
        'totalSales' => number_format($data['statistics']['totalSales'], 0, '.', ' ') . ' ₽',
        'totalItems' => number_format($data['statistics']['totalItems'], 0, '.', ' '),
        'totalOrders' => number_format($data['statistics']['totalOrders'], 0, '.', ' '),
        'averageOrder' => number_format($data['statistics']['averageOrder'], 0, '.', ' ') . ' ₽'
    ],
    'chart' => $data['chart']
];

// Отправляем данные в формате JSON
header('Content-Type: application/json');
echo json_encode($formattedData);
?> 