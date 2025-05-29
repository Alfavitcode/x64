<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Проверяем, переданы ли необходимые параметры
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$order_id || !$user_id) {
    die("Необходимо указать ID заказа и ID пользователя");
}

// Получаем данные о пользователе
$user = getUserById($user_id);
if (!$user) {
    die("Пользователь с ID $user_id не найден");
}

// Проверяем, есть ли у пользователя привязанный Telegram
if (empty($user['telegram_id'])) {
    die("У пользователя с ID $user_id нет привязанного Telegram ID");
}

// Получаем данные о заказе
$order = getOrderById($order_id, $user_id);
if (!$order) {
    die("Заказ с ID $order_id не найден или не принадлежит пользователю с ID $user_id");
}

if ($order['status'] !== 'pending_confirmation') {
    die("Заказ уже имеет статус: " . $order['status']);
}

// Создаем callback_query в формате Telegram API
$callback_data = [
    'update_id' => rand(100000, 999999),
    'callback_query' => [
        'id' => 'callback_' . time() . rand(1000, 9999),
        'from' => [
            'id' => $user['telegram_id'],
            'is_bot' => false,
            'first_name' => $user['fullname'],
            'username' => $user['telegram_username'] ?? 'test_user'
        ],
        'message' => [
            'message_id' => rand(1000, 9999),
            'chat' => [
                'id' => $user['telegram_id'],
                'first_name' => $user['fullname'],
                'type' => 'private'
            ],
            'date' => time(),
            'text' => "Заказ #$order_id"
        ],
        'chat_instance' => 'chat_' . rand(1000000, 9999999),
        'data' => "confirm_order_$order_id"
    ]
];

// Преобразуем данные в JSON
$callback_json = json_encode($callback_data);

echo "<h2>Сформированный callback_query</h2>";
echo "<pre>" . htmlspecialchars($callback_json) . "</pre>";

// Опция для отправки запроса
$direct_test = isset($_GET['direct_test']) && $_GET['direct_test'] == 1;

if ($direct_test) {
    // Непосредственно вызываем обработчик (для локального тестирования)
    echo "<h2>Симуляция прямого вызова обработчика</h2>";
    
    // Сохраняем текущий input
    $original_input = file_get_contents('php://input');
    
    // Устанавливаем новый input с нашими данными
    $GLOBALS['HTTP_RAW_POST_DATA'] = $callback_json;
    
    // Временно перенаправляем вывод
    ob_start();
    include 'telegram_webhook.php';
    $webhook_output = ob_get_clean();
    
    // Выводим результат
    echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
    echo htmlspecialchars($webhook_output);
    echo "</div>";
} else {
    // Отправляем POST-запрос на webhook URL
    echo "<h2>Отправка запроса на webhook URL</h2>";
    
    $webhook_url = TELEGRAM_WEBHOOK_URL;
    echo "URL вебхука: " . htmlspecialchars($webhook_url) . "<br>";
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $callback_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        echo "<div style='color: red;'>Ошибка при отправке запроса: " . curl_error($ch) . "</div>";
    } else {
        echo "<div style='color: green;'>Запрос отправлен успешно</div>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP-код ответа: $http_code<br>";
    
    curl_close($ch);
    
    echo "<div style='margin-top: 20px;'>";
    echo "Проверьте лог-файл для подтверждения обработки запроса: <br>";
    echo "<a href='telegram_log.txt' target='_blank'>Открыть лог-файл</a>";
    echo "</div>";
}

// Добавляем ссылку для обновления статуса заказа вручную
echo "<div style='margin-top: 20px;'>";
echo "<a href='test_order_confirmation.php?order_id=$order_id&user_id=$user_id' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Обновить статус заказа вручную</a>";
echo "</div>"; 