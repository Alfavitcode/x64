<?php
// Подключение к базе данных и функции
require_once '../includes/config/db_functions.php';

// Проверяем, что запрос был отправлен
if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode(['status' => 'error', 'message' => 'Запрос не указан']);
    exit;
}

// Получаем поисковой запрос и очищаем его
$query = trim(htmlspecialchars($_GET['query']));

// Минимальная длина для поиска
if (mb_strlen($query) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Слишком короткий запрос']);
    exit;
}

// Ищем товары в базе данных
$results = searchProducts($query, 5); // Ограничиваем до 5 результатов

// Форматируем результаты для вывода
$formattedResults = [];
if (!empty($results)) {
    foreach ($results as $product) {
        // Проверяем наличие изображения
        $imageUrl = isset($product['image']) && !empty($product['image']) ? $product['image'] : '/img/products/placeholder.jpg';
        
        // Форматируем цену
        $price = number_format($product['price'], 0, '.', ' ') . ' ₽';
        
        // Если есть скидка, считаем старую цену
        $oldPrice = '';
        if (isset($product['discount']) && $product['discount'] > 0) {
            $oldPriceValue = round($product['price'] * (100 / (100 - $product['discount'])));
            $oldPrice = number_format($oldPriceValue, 0, '.', ' ') . ' ₽';
        }
        
        // Добавляем товар в результаты
        $formattedResults[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'image' => $imageUrl,
            'price' => $price,
            'oldPrice' => $oldPrice,
            'category' => $product['category'] ?? '',
            'url' => '/product.php?id=' . $product['id']
        ];
    }
}

// Возвращаем результаты в формате JSON
echo json_encode([
    'status' => 'success',
    'results' => $formattedResults,
    'count' => count($formattedResults),
    'query' => $query
]); 