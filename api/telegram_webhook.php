<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Получаем данные от Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Логирование входящих данных для отладки
$log_file = __DIR__ . '/telegram_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Получен запрос\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Входящие данные: " . file_get_contents('php://input') . "\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Декодированные данные: " . print_r($update, true) . "\n\n", FILE_APPEND);

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
        $response .= "/status - Проверить статус привязки аккаунта\n\n";
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
    
    // Если сообщение не распознано
    $response = "Я не понимаю эту команду. Пожалуйста, используйте /help для получения списка доступных команд.";
    sendTelegramMessage($chat_id, $response);
}

/**
 * Отправляет сообщение в Telegram
 * 
 * @param int $chat_id ID чата
 * @param string $text Текст сообщения
 * @return bool Результат отправки
 */
function sendTelegramMessage($chat_id, $text) {
    // Используем токен из конфигурации
    $bot_token = TELEGRAM_BOT_TOKEN;
    
    // Формируем данные для отправки
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    // Отправляем запрос к API Telegram
    $ch = curl_init("https://api.telegram.org/bot$bot_token/sendMessage");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
} 