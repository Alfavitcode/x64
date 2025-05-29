<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/telegram_config.php';

// Включаем вывод всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Путь к файлу webhook
$webhook_file = __DIR__ . '/telegram_webhook.php';
$backup_file = __DIR__ . '/telegram_webhook_backup.php';
$updated = false;
$webhook_exists = file_exists($webhook_file);

// Создаем резервную копию файла
if ($webhook_exists) {
    copy($webhook_file, $backup_file);
    $webhook_content = file_get_contents($webhook_file);
    
    // Заменяем код, связанный с подтверждением заказов
    $search_patterns = [
        // Паттерн для поиска обработки callback-запросов подтверждения заказа
        '/if \(preg_match\(\'\/\^confirm_order_\(\\\d\+\)\$\/\', \$callback_data, \$matches\)\) \{.*?\}/s',
        // Паттерн для поиска обработки команды /accept
        '/if \(isset\(\$message\[\'text\'\]\) && strpos\(\$message\[\'text\'\], \'\/accept\'\) === 0\) \{.*?\}/s'
    ];
    
    $original_content = $webhook_content;
    
    // Обновляем обработку callback запросов
    $webhook_content = preg_replace_callback($search_patterns[0], 
        function($matches) {
            return "if (preg_match('/^confirm_order_(\\d+)$/', \$callback_data, \$matches)) {
        // Отправляем только подтверждение получения callback запроса
        answerCallbackQuery(\$callback_query['id'], 'Спасибо! Ваш заказ уже в обработке.');
        exit;
    }";
        }, 
        $webhook_content
    );
    
    // Обновляем обработку команды /accept
    $webhook_content = preg_replace_callback($search_patterns[1], 
        function($matches) {
            return "if (isset(\$message['text']) && strpos(\$message['text'], '/accept') === 0) {
        // Отправляем сообщение, что команда больше не требуется
        sendTelegramMessage(\$chat_id, 'Ваш заказ автоматически принят в обработку при оформлении. Спасибо!');
        exit;
    }";
        }, 
        $webhook_content
    );
    
    // Проверяем, были ли внесены изменения
    if ($webhook_content !== $original_content) {
        file_put_contents($webhook_file, $webhook_content);
        $updated = true;
    }
}

// Создаем простой шаблон уведомления о заказе
$notification_template = <<<'EOD'
/**
 * Отправляет уведомление о заказе пользователю через Telegram
 * 
 * @param int $order_id ID заказа
 * @param int $user_id ID пользователя
 * @return bool Успешность отправки
 */
function sendOrderNotificationToUser($order_id, $user_id) {
    global $conn;
    
    // Получаем данные о пользователе
    $user = getUserById($user_id);
    if (!$user || empty($user['telegram_id'])) {
        return false;
    }
    
    // Получаем данные о заказе
    $order = getOrderById($order_id);
    if (!$order) {
        return false;
    }
    
    // Получаем элементы заказа
    $items = getOrderItems($order_id);
    $items_text = "";
    $total = 0;
    
    foreach ($items as $item) {
        $items_text .= "• " . $item['name'] . " x" . $item['quantity'] . " - " . number_format($item['subtotal'], 0, '.', ' ') . " ₽\n";
        $total += $item['subtotal'];
    }
    
    // Добавляем стоимость доставки
    $total += $order['delivery_cost'];
    
    // Формируем сообщение
    $message = "🛍️ <b>Ваш заказ #$order_id успешно оформлен!</b>\n\n";
    $message .= "📋 <b>Детали заказа:</b>\n";
    $message .= "Имя: " . $order['fullname'] . "\n";
    if (!empty($order['city']) || !empty($order['address'])) {
        $message .= "Адрес: " . $order['city'] . ", " . $order['address'] . "\n";
    }
    if (!empty($order['delivery_method'])) {
        $message .= "Доставка: " . getDeliveryMethodText($order['delivery_method']) . " (" . number_format($order['delivery_cost'], 0, '.', ' ') . " ₽)\n";
    }
    $message .= "\n";
    
    $message .= "🛒 <b>Товары:</b>\n";
    $message .= $items_text . "\n";
    $message .= "💰 <b>Итого:</b> " . number_format($total, 0, '.', ' ') . " ₽\n\n";
    
    $message .= "Ваш заказ передан в обработку. Скоро с вами свяжется наш менеджер для подтверждения деталей.";
    
    // Отправляем сообщение
    return sendTelegramMessage($user['telegram_id'], $message);
}
EOD;

// Проверяем, есть ли такая функция в файле
$has_notification_function = false;

if ($webhook_exists) {
    $has_notification_function = strpos($webhook_content, 'function sendOrderNotificationToUser') !== false;
    
    // Если функции нет, добавляем её в конец файла перед закрывающим тегом PHP
    if (!$has_notification_function) {
        $closing_tag_pos = strrpos($webhook_content, '?>');
        if ($closing_tag_pos !== false) {
            $webhook_content = substr($webhook_content, 0, $closing_tag_pos) . "\n\n" . $notification_template . "\n\n?>";
        } else {
            $webhook_content = $webhook_content . "\n\n" . $notification_template . "\n";
        }
        
        file_put_contents($webhook_file, $webhook_content);
        $updated = true;
    }
}

// Проверяем, настроен ли webhook в Telegram
$webhook_status = "Неизвестно";
$webhook_url = "";

if (defined('TELEGRAM_BOT_TOKEN')) {
    $bot_token = TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $webhook_info = json_decode($result, true);
    
    if ($webhook_info && isset($webhook_info['ok']) && $webhook_info['ok']) {
        if (isset($webhook_info['result']['url']) && !empty($webhook_info['result']['url'])) {
            $webhook_status = "Настроен";
            $webhook_url = $webhook_info['result']['url'];
        } else {
            $webhook_status = "Не настроен";
        }
    } else {
        $webhook_status = "Ошибка";
    }
}

// Проверяем checkout.php
$checkout_file = '../includes/shop/checkout.php';
$checkout_updated = false;

if (file_exists($checkout_file)) {
    $checkout_content = file_get_contents($checkout_file);
    
    // Ищем вызов функции отправки уведомления через Telegram
    if (strpos($checkout_content, 'sendOrderNotificationToUser') === false && 
        strpos($checkout_content, 'telegram') !== false) {
        
        // Добавляем код для отправки уведомления
        $checkout_pattern = '/\$order_id\s*=\s*mysqli_insert_id\(\$conn\);/';
        $checkout_replacement = '$order_id = mysqli_insert_id($conn);
        
        // Отправляем уведомление в Telegram, если функция существует
        if (function_exists("sendOrderNotificationToUser")) {
            sendOrderNotificationToUser($order_id, $user_id);
        }';
        
        $new_checkout_content = preg_replace($checkout_pattern, $checkout_replacement, $checkout_content);
        
        if ($checkout_content !== $new_checkout_content) {
            file_put_contents($checkout_file, $new_checkout_content);
            $checkout_updated = true;
        }
    }
}

// HTML вывод результатов
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновление webhook-обработчика Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f5f7f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
        .success-text {
            color: #28a745;
        }
        .warning-text {
            color: #ffc107;
        }
        .danger-text {
            color: #dc3545;
        }
        .info-text {
            color: #17a2b8;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 14px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Обновление webhook-обработчика Telegram</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Статус операции
            </div>
            <div class="card-body">
                <h5>Обновление webhook-обработчика</h5>
                <?php if (!$webhook_exists): ?>
                <p class="danger-text">❌ Файл webhook-обработчика не найден!</p>
                <?php elseif ($updated): ?>
                <p class="success-text">✅ Webhook-обработчик успешно обновлен. Создана резервная копия исходного файла.</p>
                <?php else: ?>
                <p class="info-text">ℹ️ Webhook-обработчик не требует обновления или не удалось найти код для замены.</p>
                <?php endif; ?>
                
                <h5 class="mt-4">Функция отправки уведомлений</h5>
                <?php if ($has_notification_function || $updated): ?>
                <p class="success-text">✅ Функция для отправки уведомлений о заказе <?php echo $has_notification_function ? 'существует' : 'добавлена'; ?>.</p>
                <?php else: ?>
                <p class="warning-text">⚠️ Не удалось добавить функцию для отправки уведомлений о заказе.</p>
                <?php endif; ?>
                
                <h5 class="mt-4">Обновление checkout.php</h5>
                <?php if ($checkout_updated): ?>
                <p class="success-text">✅ Файл checkout.php успешно обновлен для отправки уведомлений.</p>
                <?php elseif (file_exists($checkout_file)): ?>
                <p class="info-text">ℹ️ Файл checkout.php не требует обновления или не удалось найти место для добавления кода.</p>
                <?php else: ?>
                <p class="warning-text">⚠️ Файл checkout.php не найден.</p>
                <?php endif; ?>
                
                <h5 class="mt-4">Webhook в Telegram</h5>
                <?php if ($webhook_status === 'Настроен'): ?>
                <p class="success-text">✅ Webhook настроен в Telegram: <?php echo htmlspecialchars($webhook_url); ?></p>
                <?php elseif ($webhook_status === 'Не настроен'): ?>
                <p class="warning-text">⚠️ Webhook не настроен в Telegram. Уведомления отправляться не будут.</p>
                <p>Вы можете настроить webhook, используя <a href="check_paths.php">инструмент проверки настроек</a>.</p>
                <?php else: ?>
                <p class="danger-text">❌ Не удалось проверить статус webhook в Telegram.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                Новая функциональность
            </div>
            <div class="card-body">
                <p>Система обновлена так, чтобы заказы больше не требовали подтверждения через Telegram. Теперь пользователям будет отправляться только уведомление о создании заказа.</p>
                
                <h5 class="mt-3">Пример уведомления:</h5>
                <pre>🛍️ <b>Ваш заказ #123 успешно оформлен!</b>

📋 <b>Детали заказа:</b>
Имя: Иванов Иван
Адрес: Москва, ул. Примерная, д. 123
Доставка: Курьерская доставка (300 ₽)

🛒 <b>Товары:</b>
• Чехол iPhone 14 x1 - 1 200 ₽

💰 <b>Итого:</b> 1 500 ₽

Ваш заказ передан в обработку. Скоро с вами свяжется наш менеджер для подтверждения деталей.</pre>
                
                <p class="mt-3">Этот шаблон уведомления отправляется автоматически после создания заказа.</p>
                
                <div class="alert alert-info mt-4">
                    <strong>Примечание:</strong> Если вы хотите полностью отключить уведомления Telegram, вы можете закомментировать вызов функции <code>sendOrderNotificationToUser</code> в файле checkout.php.
                </div>
                
                <div class="mt-4">
                    <a href="disable_telegram_confirmation.php" class="btn btn-primary">Отключить подтверждение заказов</a>
                    <a href="../index.php" class="btn btn-secondary ms-2">На главную</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 