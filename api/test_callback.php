<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Установим параметры по умолчанию
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Если не указаны параметры, показываем форму
if (!$order_id || !$user_id) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Тест Telegram Callback</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
            h1 { color: #333; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input, select { padding: 8px; width: 100%; box-sizing: border-box; }
            button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
            .results { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
            .error { color: red; }
            .success { color: green; }
        </style>
    </head>
    <body>
        <h1>Тест Telegram Callback</h1>
        <p>Этот инструмент напрямую вызывает функции обработки callback-запросов от Telegram, минуя webhook.</p>
        
        <form method="get">
            <div class="form-group">
                <label for="order_id">ID заказа:</label>
                <input type="number" id="order_id" name="order_id" required>
            </div>
            
            <div class="form-group">
                <label for="user_id">ID пользователя:</label>
                <input type="number" id="user_id" name="user_id" required>
            </div>
            
            <div class="form-group">
                <label for="action_type">Тип действия:</label>
                <select id="action_type" name="action_type">
                    <option value="callback">Симуляция нажатия на кнопку</option>
                    <option value="command">Симуляция команды /accept</option>
                </select>
            </div>
            
            <button type="submit">Запустить тест</button>
        </form>
        
        <p>Также вы можете проверить существующие заказы:</p>
        <ul>
            <li><a href="check_user_telegram.php">Проверить привязку Telegram у пользователей</a></li>
            <li><a href="fix_getUserByTelegramId.php">Исправить функцию getUserByTelegramId</a></li>
            <li><a href="telegram_diagnostics.php">Панель диагностики Telegram</a></li>
        </ul>
    </body>
    </html>
    <?php
    exit;
}

// Получаем данные пользователя
$user = getUserById($user_id);
if (!$user) {
    die("Ошибка: Пользователь с ID $user_id не найден.");
}

// Проверяем наличие Telegram ID у пользователя
if (empty($user['telegram_id'])) {
    die("Ошибка: У пользователя нет привязанного Telegram ID. <a href='check_user_telegram.php?manual_link_form=$user_id'>Привязать вручную</a>");
}

// Получаем данные заказа
$order = getOrderById($order_id);
if (!$order) {
    die("Ошибка: Заказ с ID $order_id не найден.");
}

// Проверяем принадлежность заказа пользователю
if ($order['user_id'] != $user_id) {
    echo "<div class='error'>Предупреждение: Заказ не принадлежит указанному пользователю! Это может вызвать ошибку при обработке.</div>";
}

// Проверяем статус заказа
if ($order['status'] !== 'pending_confirmation') {
    echo "<div class='error'>Предупреждение: Заказ имеет статус '{$order['status']}', а не 'pending_confirmation'.</div>";
}

// Определяем тип действия
$action_type = isset($_GET['action_type']) ? $_GET['action_type'] : 'callback';

// Начинаем тестирование
echo "<h2>Запуск теста Telegram callback</h2>";
echo "<p>Заказ ID: $order_id</p>";
echo "<p>Пользователь: {$user['fullname']} (ID: $user_id)</p>";
echo "<p>Telegram ID: {$user['telegram_id']}</p>";
echo "<p>Тип действия: " . ($action_type == 'callback' ? 'Нажатие на кнопку' : 'Команда /accept') . "</p>";

// Очищаем лог перед тестом
$log_file = __DIR__ . '/test_callback_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Начало теста для заказа #$order_id пользователя #{$user['id']} ({$user['fullname']})\n");

// Режим отладки: сохраняем все выводы
ob_start();

// Симулируем callback-запрос или команду
if ($action_type == 'callback') {
    // Создаем данные callback-запроса
    $callback_data = [
        'update_id' => rand(100000, 999999),
        'callback_query' => [
            'id' => 'callback_' . time() . rand(1000, 9999),
            'from' => [
                'id' => $user['telegram_id'],
                'is_bot' => false,
                'first_name' => $user['fullname'],
                'username' => $user['telegram_username'] ?? 'user'
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
    
    // Сохраняем сформированные данные в лог
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Сформирован callback-запрос:\n" . json_encode($callback_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    
    echo "<h3>Сформированный callback-запрос:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($callback_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    // Сохраняем callback-данные в глобальную переменную
    $GLOBALS['update'] = $callback_data;
    
    try {
        // Обработка заказа
        $sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
        
        if (mysqli_query($conn, $sql)) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Статус заказа успешно обновлен на 'pending'\n", FILE_APPEND);
            echo "<div class='success'>Статус заказа успешно обновлен на 'pending'</div>";
            
            // Проверяем актуальный статус заказа
            $check_sql = "SELECT status FROM orders WHERE id = " . (int)$order_id;
            $check_result = mysqli_query($conn, $check_sql);
            
            if ($check_result && $row = mysqli_fetch_assoc($check_result)) {
                echo "<div>Текущий статус заказа: <strong>{$row['status']}</strong></div>";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Текущий статус заказа: {$row['status']}\n", FILE_APPEND);
            }
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Ошибка при обновлении статуса заказа: " . mysqli_error($conn) . "\n", FILE_APPEND);
            echo "<div class='error'>Ошибка при обновлении статуса заказа: " . mysqli_error($conn) . "</div>";
        }
    } catch (Exception $e) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Исключение: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "<div class='error'>Исключение: " . $e->getMessage() . "</div>";
    }
} else {
    // Создаем данные для команды /accept
    $message_data = [
        'update_id' => rand(100000, 999999),
        'message' => [
            'message_id' => rand(1000, 9999),
            'from' => [
                'id' => $user['telegram_id'],
                'is_bot' => false,
                'first_name' => $user['fullname'],
                'username' => $user['telegram_username'] ?? 'user'
            ],
            'chat' => [
                'id' => $user['telegram_id'],
                'first_name' => $user['fullname'],
                'type' => 'private'
            ],
            'date' => time(),
            'text' => "/accept $order_id"
        ]
    ];
    
    // Сохраняем сформированные данные в лог
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Сформирована команда /accept:\n" . json_encode($message_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    
    echo "<h3>Сформированная команда:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($message_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    // Сохраняем данные в глобальную переменную
    $GLOBALS['update'] = $message_data;
    
    try {
        // Обработка заказа
        $sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
        
        if (mysqli_query($conn, $sql)) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Статус заказа успешно обновлен на 'pending'\n", FILE_APPEND);
            echo "<div class='success'>Статус заказа успешно обновлен на 'pending'</div>";
            
            // Проверяем актуальный статус заказа
            $check_sql = "SELECT status FROM orders WHERE id = " . (int)$order_id;
            $check_result = mysqli_query($conn, $check_sql);
            
            if ($check_result && $row = mysqli_fetch_assoc($check_result)) {
                echo "<div>Текущий статус заказа: <strong>{$row['status']}</strong></div>";
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Текущий статус заказа: {$row['status']}\n", FILE_APPEND);
            }
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Ошибка при обновлении статуса заказа: " . mysqli_error($conn) . "\n", FILE_APPEND);
            echo "<div class='error'>Ошибка при обновлении статуса заказа: " . mysqli_error($conn) . "</div>";
        }
    } catch (Exception $e) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Исключение: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "<div class='error'>Исключение: " . $e->getMessage() . "</div>";
    }
}

// Получаем и сохраняем все выводы
$output = ob_get_clean();
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Результат выполнения:\n" . strip_tags($output) . "\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Тест завершен\n\n", FILE_APPEND);

// Выводим HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>Результаты теста Telegram Callback</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #333; }
        .results { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
        pre { background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
        .error { color: red; }
        .success { color: green; }
        .nav { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; }
        .button { display: inline-block; padding: 8px 15px; background-color: #4CAF50; color: white; text-decoration: none; margin-right: 10px; border-radius: 3px; }
        .button.blue { background-color: #2196F3; }
        .button.orange { background-color: #FF9800; }
    </style>
</head>
<body>
    <h1>Результаты теста Telegram Callback</h1>
    
    <div class="results">
        <?php echo $output; ?>
        
        <h3>Журнал выполнения:</h3>
        <pre><?php echo htmlspecialchars(file_get_contents($log_file)); ?></pre>
    </div>
    
    <div class="nav">
        <a href="test_callback.php" class="button">Выполнить новый тест</a>
        <a href="check_user_telegram.php" class="button blue">Проверить привязку Telegram</a>
        <a href="telegram_diagnostics.php" class="button orange">Панель диагностики</a>
    </div>
    
    <p><strong>Примечание:</strong> Этот инструмент напрямую изменяет статус заказа в базе данных, минуя стандартные проверки webhook.</p>
</body>
</html> 