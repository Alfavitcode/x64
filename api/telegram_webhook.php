<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Получаем данные от Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Логирование входящих данных для отладки
$log_dir = __DIR__;
$log_file = $log_dir . '/telegram_log.txt';

// Проверяем, доступна ли директория для записи
if (is_writable($log_dir)) {
    // Ограничиваем размер лога
    if (file_exists($log_file) && filesize($log_file) > 1024 * 1024) { // 1MB
        // Сохраняем только последние 100 КБ
        $content = file_get_contents($log_file);
        $content = substr($content, -102400);
        file_put_contents($log_file, $content);
    }
    
    // Логируем запрос
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Получен запрос\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Входящие данные: " . file_get_contents('php://input') . "\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Декодированные данные: " . print_r($update, true) . "\n\n", FILE_APPEND);
}

// Обработка callback-запросов от встроенных кнопок
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $chat_id = $callback_query['from']['id'];
    $callback_data = $callback_query['data'];
    
    // Обработка подтверждения заказа через кнопку
    if (preg_match('/^confirm_order_(\d+)$/', $callback_data, $matches)) {
        $order_id = $matches[1];
        
        // Проверяем, привязан ли Telegram аккаунт к пользователю
        $user = getUserByTelegramId($chat_id);
        
        if (!$user) {
            answerCallbackQuery($callback_query['id'], "Ваш Telegram не привязан к аккаунту на сайте.", true);
            exit;
        }
        
        // Проверяем, существует ли заказ и принадлежит ли он этому пользователю
        $order = getOrderById($order_id, $user['id']);
        
        if (!$order) {
            answerCallbackQuery($callback_query['id'], "Заказ #$order_id не найден или не принадлежит вам.", true);
            exit;
        }
        
        if ($order['status'] !== 'pending_confirmation') {
            $status_text = '';
            switch ($order['status']) {
                case 'pending':
                    $status_text = 'ожидает обработки';
                    break;
                case 'processing':
                    $status_text = 'в обработке';
                    break;
                case 'completed':
                    $status_text = 'выполнен';
                    break;
                case 'cancelled':
                    $status_text = 'отменен';
                    break;
                default:
                    $status_text = $order['status'];
                    break;
            }
            
            answerCallbackQuery($callback_query['id'], "Заказ #$order_id уже подтвержден и $status_text.", true);
            exit;
        }
        
        // Обновляем статус заказа на "pending" (ожидает обработки)
        $sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
        
        if (mysqli_query($conn, $sql)) {
            // Получаем элементы заказа для отображения
            $order_items = getOrderItems($order_id);
            $items_text = "";
            $total = 0;
            
            foreach ($order_items as $item) {
                $items_text .= "• " . $item['name'] . " x" . $item['quantity'] . " - " . number_format($item['subtotal'], 0, '.', ' ') . " ₽\n";
                $total += $item['subtotal'];
            }
            
            // Добавляем стоимость доставки
            $total += $order['delivery_cost'];
            
            $response = "✅ Заказ #$order_id успешно подтвержден!\n\n";
            $response .= "📋 <b>Детали заказа:</b>\n";
            $response .= "Имя: " . $order['fullname'] . "\n";
            $response .= "Адрес: " . $order['city'] . ", " . $order['address'] . "\n";
            $response .= "Доставка: " . getDeliveryMethodText($order['delivery_method']) . " (" . number_format($order['delivery_cost'], 0, '.', ' ') . " ₽)\n\n";
            
            $response .= "🛒 <b>Товары:</b>\n";
            $response .= $items_text . "\n";
            $response .= "💰 <b>Итого:</b> " . number_format($total, 0, '.', ' ') . " ₽\n\n";
            
            $response .= "Ваш заказ передан в обработку. Скоро с вами свяжется наш менеджер для подтверждения деталей.";
            
            // Обновляем оригинальное сообщение
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $callback_query['message']['message_id'],
                'text' => $response,
                'parse_mode' => 'HTML'
            ];
            
            $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/editMessageText');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            
            // Показываем уведомление
            answerCallbackQuery($callback_query['id'], "Заказ #$order_id успешно подтвержден!");
            
            // Отправляем уведомление администраторам о новом подтвержденном заказе
            sendOrderNotificationToAdmin($order_id);
        } else {
            answerCallbackQuery($callback_query['id'], "Произошла ошибка при подтверждении заказа. Пожалуйста, попробуйте позже.", true);
        }
        
        exit;
    }
    
    // Если callback-запрос не распознан
    answerCallbackQuery($callback_query['id'], "Неизвестная команда.");
    exit;
}

// Проверяем, что это сообщение
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = isset($message['text']) ? $message['text'] : '';
    $username = isset($message['from']['username']) ? $message['from']['username'] : '';
    $first_name = isset($message['from']['first_name']) ? $message['from']['first_name'] : '';
    $last_name = isset($message['from']['last_name']) ? $message['from']['last_name'] : '';
    
    // Обработка команды /start
    if ($text === '/start') {
        // Сохраняем информацию о пользователе в сессионный файл
        $user_data = [
            'chat_id' => $chat_id,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'timestamp' => time()
        ];
        
        // Создаем директорию для хранения сессий, если она не существует
        $sessions_dir = __DIR__ . '/telegram_sessions';
        if (!file_exists($sessions_dir)) {
            mkdir($sessions_dir, 0755, true);
        }
        
        // Сохраняем данные пользователя в файл
        file_put_contents($sessions_dir . '/' . $chat_id . '.json', json_encode($user_data));
        
        $response = "Добро пожаловать, " . htmlspecialchars($first_name) . "! 👋\n\n";
        $response .= "Этот бот позволяет привязать ваш аккаунт Telegram к аккаунту на сайте x64.\n\n";
        $response .= "Для получения кода привязки, отправьте команду /code\n\n";
        $response .= "Если у вас возникли вопросы, напишите /help";
        
        sendTelegramMessage($chat_id, $response);
        exit;
    }
    
    // Обработка команды /code
    if ($text === '/code') {
        // Проверяем, есть ли сохраненная информация о пользователе
        $sessions_dir = __DIR__ . '/telegram_sessions';
        $session_file = $sessions_dir . '/' . $chat_id . '.json';
        
        if (file_exists($session_file)) {
            $user_data = json_decode(file_get_contents($session_file), true);
            
            // Проверяем, привязан ли уже этот Telegram ID к какому-либо аккаунту
            $user = getUserByTelegramId($chat_id);
            
            if ($user) {
                $response = "Ваш Telegram уже привязан к аккаунту " . $user['fullname'] . ".\n\n";
                $response .= "Если вы хотите отвязать аккаунт, используйте соответствующую функцию в личном кабинете на сайте.";
                sendTelegramMessage($chat_id, $response);
                exit;
            }
            
            // Генерируем временный код для привязки
            $code = sprintf("%05d", mt_rand(0, 99999));
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            
            // Сохраняем код в базе данных для последующей проверки
            $sql = "INSERT INTO telegram_verification_codes (chat_id, username, first_name, last_name, code, expires_at) 
                    VALUES ('$chat_id', " . 
                    ($username ? "'$username'" : "NULL") . ", " .
                    ($first_name ? "'" . mysqli_real_escape_string($conn, $first_name) . "'" : "NULL") . ", " .
                    ($last_name ? "'" . mysqli_real_escape_string($conn, $last_name) . "'" : "NULL") . ", " .
                    "'$code', '$expires')
                    ON DUPLICATE KEY UPDATE 
                    code = '$code', 
                    expires_at = '$expires',
                    username = " . ($username ? "'$username'" : "username") . ",
                    first_name = " . ($first_name ? "'" . mysqli_real_escape_string($conn, $first_name) . "'" : "first_name") . ",
                    last_name = " . ($last_name ? "'" . mysqli_real_escape_string($conn, $last_name) . "'" : "last_name");
            
            // Проверяем, существует ли таблица telegram_verification_codes
            $check_table_sql = "SHOW TABLES LIKE 'telegram_verification_codes'";
            $check_result = mysqli_query($conn, $check_table_sql);
            
            if (mysqli_num_rows($check_result) == 0) {
                // Создаем таблицу, если она не существует
                $create_table_sql = "CREATE TABLE telegram_verification_codes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    chat_id VARCHAR(50) NOT NULL,
                    username VARCHAR(100) NULL,
                    first_name VARCHAR(100) NULL,
                    last_name VARCHAR(100) NULL,
                    code VARCHAR(10) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY (chat_id)
                )";
                mysqli_query($conn, $create_table_sql);
            }
            
            if (mysqli_query($conn, $sql)) {
                $response = "Ваш код для привязки аккаунта: <b>$code</b>\n\n";
                $response .= "Введите этот код на странице привязки Telegram в личном кабинете на сайте.\n\n";
                $response .= "Код действителен в течение 30 минут.";
                sendTelegramMessage($chat_id, $response);
            } else {
                $response = "Произошла ошибка при генерации кода. Пожалуйста, попробуйте позже.";
                sendTelegramMessage($chat_id, $response);
            }
        } else {
            $response = "Для начала работы с ботом, отправьте команду /start";
            sendTelegramMessage($chat_id, $response);
        }
        exit;
    }
    
    // Обработка команды /help
    if ($text === '/help') {
        $response = "Справка по командам бота:\n\n";
        $response .= "/start - Начать работу с ботом\n";
        $response .= "/code - Получить код для привязки аккаунта\n";
        $response .= "/help - Показать эту справку\n";
        $response .= "/status - Проверить статус привязки аккаунта\n";
        $response .= "/accept ID - Подтвердить заказ с указанным ID\n\n";
        $response .= "Для привязки аккаунта получите код с помощью команды /code и введите его на странице привязки Telegram в личном кабинете на сайте.";
        
        sendTelegramMessage($chat_id, $response);
        exit;
    }
    
    // Обработка команды /status
    if ($text === '/status') {
        // Проверяем, привязан ли аккаунт
        $user = getUserByTelegramId($chat_id);
        
        if ($user) {
            $response = "Ваш Telegram привязан к аккаунту:\n\n";
            $response .= "Имя: " . $user['fullname'] . "\n";
            $response .= "Email: " . $user['email'] . "\n";
            $response .= "Телефон: " . $user['phone'] . "\n\n";
            $response .= "Для отвязки аккаунта используйте соответствующую функцию в личном кабинете на сайте.";
        } else {
            $response = "Ваш Telegram не привязан к аккаунту на сайте.\n\n";
            $response .= "Для привязки аккаунта выполните следующие шаги:\n";
            $response .= "1. Получите код с помощью команды /code\n";
            $response .= "2. Войдите в свой аккаунт на сайте\n";
            $response .= "3. Перейдите в раздел \"Привязка Telegram\"\n";
            $response .= "4. Введите полученный код в соответствующее поле";
        }
        
        sendTelegramMessage($chat_id, $response);
        exit;
    }
    
    // Обработка команды /accept для подтверждения заказа
    if (preg_match('/^\/accept\s+(\d+)$/', $text, $matches)) {
        $order_id = $matches[1];
        
        // Проверяем, привязан ли Telegram аккаунт к пользователю
        $user = getUserByTelegramId($chat_id);
        
        if (!$user) {
            $response = "Ваш Telegram не привязан к аккаунту на сайте.\n\n";
            $response .= "Для привязки аккаунта выполните следующие шаги:\n";
            $response .= "1. Получите код с помощью команды /code\n";
            $response .= "2. Войдите в свой аккаунт на сайте\n";
            $response .= "3. Перейдите в раздел \"Привязка Telegram\"\n";
            $response .= "4. Введите полученный код в соответствующее поле";
            
            sendTelegramMessage($chat_id, $response);
            exit;
        }
        
        // Проверяем, существует ли заказ и принадлежит ли он этому пользователю
        $order = getOrderById($order_id, $user['id']);
        
        if (!$order) {
            sendTelegramMessage($chat_id, "Заказ #$order_id не найден или не принадлежит вам.");
            exit;
        }
        
        if ($order['status'] !== 'pending_confirmation') {
            $status_text = '';
            switch ($order['status']) {
                case 'pending':
                    $status_text = 'ожидает обработки';
                    break;
                case 'processing':
                    $status_text = 'в обработке';
                    break;
                case 'completed':
                    $status_text = 'выполнен';
                    break;
                case 'cancelled':
                    $status_text = 'отменен';
                    break;
                default:
                    $status_text = $order['status'];
                    break;
            }
            
            sendTelegramMessage($chat_id, "Заказ #$order_id уже подтвержден и $status_text.");
            exit;
        }
        
        // Обновляем статус заказа на "pending" (ожидает обработки)
        $sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
        
        if (mysqli_query($conn, $sql)) {
            // Получаем элементы заказа для отображения
            $order_items = getOrderItems($order_id);
            $items_text = "";
            $total = 0;
            
            foreach ($order_items as $item) {
                $items_text .= "• " . $item['name'] . " x" . $item['quantity'] . " - " . number_format($item['subtotal'], 0, '.', ' ') . " ₽\n";
                $total += $item['subtotal'];
            }
            
            // Добавляем стоимость доставки
            $total += $order['delivery_cost'];
            
            $response = "✅ Заказ #$order_id успешно подтвержден!\n\n";
            $response .= "📋 <b>Детали заказа:</b>\n";
            $response .= "Имя: " . $order['fullname'] . "\n";
            $response .= "Адрес: " . $order['city'] . ", " . $order['address'] . "\n";
            $response .= "Доставка: " . getDeliveryMethodText($order['delivery_method']) . " (" . number_format($order['delivery_cost'], 0, '.', ' ') . " ₽)\n\n";
            
            $response .= "🛒 <b>Товары:</b>\n";
            $response .= $items_text . "\n";
            $response .= "💰 <b>Итого:</b> " . number_format($total, 0, '.', ' ') . " ₽\n\n";
            
            $response .= "Ваш заказ передан в обработку. Скоро с вами свяжется наш менеджер для подтверждения деталей.";
            
            sendTelegramMessage($chat_id, $response);
            
            // Отправляем уведомление администраторам о новом подтвержденном заказе
            sendOrderNotificationToAdmin($order_id);
        } else {
            sendTelegramMessage($chat_id, "Произошла ошибка при подтверждении заказа. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону.");
        }
        
        exit;
    }
    
    // Обработка кода подтверждения (5 цифр)
    if (preg_match('/^\d{5}$/', $text)) {
        $code = $text;
        
        // Ищем пользователя с таким кодом
        $sql = "SELECT id, fullname, telegram_verification_code, telegram_code_expires FROM users 
                WHERE telegram_verification_code = '$code'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Проверяем, не истек ли срок действия кода
            $now = new DateTime();
            $expires = new DateTime($user['telegram_code_expires']);
            
            if ($now > $expires) {
                sendTelegramMessage($chat_id, "Срок действия кода истек. Пожалуйста, сгенерируйте новый код на сайте.");
                exit;
            }
            
            // Привязываем Telegram к аккаунту
            $update_sql = "UPDATE users SET 
                          telegram_id = '$chat_id',
                          telegram_username = " . ($username ? "'$username'" : "NULL") . ",
                          telegram_verification_code = NULL,
                          telegram_code_expires = NULL
                          WHERE id = " . $user['id'];
            
            if (mysqli_query($conn, $update_sql)) {
                $response = "Поздравляем! 🎉\n\n";
                $response .= "Ваш Telegram успешно привязан к аккаунту " . $user['fullname'] . ".\n\n";
                $response .= "Теперь вы будете получать уведомления о входе в аккаунт и других важных событиях.";
                sendTelegramMessage($chat_id, $response);
            } else {
                sendTelegramMessage($chat_id, "Произошла ошибка при привязке аккаунта. Пожалуйста, попробуйте позже.");
            }
        } else {
            sendTelegramMessage($chat_id, "Неверный код подтверждения. Пожалуйста, проверьте код и попробуйте снова.");
        }
        
        exit;
    }
    
    // Обработка обычного текста, который не является командой
    if (substr($text, 0, 1) !== '/' && preg_match('/^\d{5}$/', $text) === 0) {
        // Проверяем, есть ли сохраненная информация о пользователе
        $sessions_dir = __DIR__ . '/telegram_sessions';
        $session_file = $sessions_dir . '/' . $chat_id . '.json';
        
        if (file_exists($session_file)) {
            // Предполагаем, что пользователь хочет получить код
            $response = "Привет! Похоже, вы хотите получить код для привязки аккаунта.\n\n";
            $response .= "Для получения кода отправьте команду /code\n\n";
            $response .= "Для просмотра всех команд отправьте /help";
            sendTelegramMessage($chat_id, $response);
        } else {
            $response = "Для начала работы с ботом, отправьте команду /start";
            sendTelegramMessage($chat_id, $response);
        }
        exit;
    }
    
    // Если сообщение не распознано
    $response = "Я не понимаю эту команду. Пожалуйста, используйте /help для получения списка доступных команд.";
    sendTelegramMessage($chat_id, $response);
}

/**
 * Отправляет сообщение в Telegram
 * 
 * @param int $chat_id ID чата
 * @param string $text Текст сообщения
 * @param array $keyboard Клавиатура (опционально)
 * @return bool Результат отправки
 */
function sendTelegramMessage($chat_id, $text, $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($keyboard !== null) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}

/**
 * Получает текстовое описание метода доставки
 * 
 * @param string $delivery_method Код метода доставки
 * @return string Описание метода доставки
 */
function getDeliveryMethodText($delivery_method) {
    switch ($delivery_method) {
        case 'courier':
            return 'Курьерская доставка';
        case 'pickup':
            return 'Самовывоз из магазина';
        case 'post':
            return 'Почта России';
        default:
            return $delivery_method;
    }
}

/**
 * Отправляет уведомление администраторам о новом подтвержденном заказе
 * 
 * @param int $order_id ID заказа
 * @return void
 */
function sendOrderNotificationToAdmin($order_id) {
    global $conn;
    
    // Получаем данные заказа
    $order = getOrderById($order_id);
    if (!$order) return;
    
    // Получаем список администраторов
    $sql = "SELECT id, telegram_id FROM users WHERE role = 'Администратор' AND telegram_id IS NOT NULL";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        // Формируем текст уведомления
        $message = "🔔 <b>Новый подтвержденный заказ #$order_id</b>\n\n";
        $message .= "Клиент: " . $order['fullname'] . "\n";
        $message .= "Телефон: " . $order['phone'] . "\n";
        $message .= "Email: " . $order['email'] . "\n";
        $message .= "Адрес: " . $order['city'] . ", " . $order['address'] . "\n";
        $message .= "Сумма: " . number_format($order['total_amount'], 0, '.', ' ') . " ₽\n\n";
        $message .= "<a href='https://x64shop.ru/admin/view_order.php?id=$order_id'>Посмотреть заказ в панели администратора</a>";
        
        // Отправляем уведомление каждому администратору
        while ($admin = mysqli_fetch_assoc($result)) {
            sendTelegramMessage($admin['telegram_id'], $message);
        }
    }
}

/**
 * Отправляет ответ на callback-запрос
 * 
 * @param string $callback_query_id ID callback-запроса
 * @param string $text Текст уведомления
 * @param bool $show_alert Показывать как alert (true) или как всплывающее уведомление (false)
 * @return bool Результат отправки
 */
function answerCallbackQuery($callback_query_id, $text = '', $show_alert = false) {
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $show_alert
    ];
    
    $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/answerCallbackQuery');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
} 