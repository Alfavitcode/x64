<?php
// Подключаем необходимые файлы
require_once '../includes/config/telegram_config.php';

// Проверяем, определена ли константа TELEGRAM_BOT_TOKEN
if (!defined('TELEGRAM_BOT_TOKEN')) {
    die("Ошибка: константа TELEGRAM_BOT_TOKEN не определена в файле конфигурации.");
}

// Список команд для добавления
$commands = [
    [
        'command' => 'id',
        'description' => 'Узнать свой Telegram ID'
    ],
    [
        'command' => 'start',
        'description' => 'Начать взаимодействие с ботом'
    ],
    [
        'command' => 'help',
        'description' => 'Получить помощь по использованию бота'
    ],
    [
        'command' => 'accept',
        'description' => 'Подтвердить заказ, например: /accept 123'
    ]
];

// Запрос к Telegram API для установки команд
$ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/setMyCommands');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['commands' => $commands]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

if ($result === false) {
    echo "<div style='color: red; font-weight: bold;'>Ошибка при отправке запроса: " . curl_error($ch) . "</div>";
} else {
    $response = json_decode($result, true);
    
    if ($response && isset($response['ok']) && $response['ok']) {
        echo "<div style='color: green; font-weight: bold;'>Команды успешно добавлены в бота!</div>";
        echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>Ошибка при добавлении команд!</div>";
        echo "<pre>" . htmlspecialchars($result) . "</pre>";
    }
}

curl_close($ch);

// Добавим обработчик команды /id в файл telegram_webhook.php
echo "<h2>Проверка и добавление обработчика команды /id</h2>";

$webhook_file = __DIR__ . '/telegram_webhook.php';
if (!file_exists($webhook_file)) {
    echo "<div style='color: red;'>Файл telegram_webhook.php не найден!</div>";
} else {
    $webhook_content = file_get_contents($webhook_file);
    
    // Проверяем, есть ли уже обработчик команды /id
    if (strpos($webhook_content, 'command === "/id"') !== false || strpos($webhook_content, "command === '/id'") !== false) {
        echo "<div style='color: orange;'>Обработчик команды /id уже существует в файле webhook.</div>";
    } else {
        // Формируем код для добавления
        $id_command_code = <<<EOD
// Обработка команды /id
if (isset(\$message['text']) && strpos(\$message['text'], '/id') === 0) {
    \$chat_id = \$message['chat']['id'];
    \$user_id = \$message['from']['id'];
    \$username = \$message['from']['username'] ?? 'нет имени пользователя';
    \$first_name = \$message['from']['first_name'] ?? '';
    \$last_name = \$message['from']['last_name'] ?? '';
    
    \$response = "✅ <b>Ваш Telegram ID:</b> <code>{\$user_id}</code>\\n\\n";
    \$response .= "<b>Имя:</b> " . htmlspecialchars(\$first_name . ' ' . \$last_name) . "\\n";
    if (\$username) {
        \$response .= "<b>Username:</b> @" . htmlspecialchars(\$username) . "\\n";
    }
    \$response .= "<b>Chat ID:</b> <code>{\$chat_id}</code>\\n\\n";
    \$response .= "Вы можете использовать этот ID для привязки вашего Telegram к аккаунту на сайте.";
    
    sendTelegramMessage(\$chat_id, \$response);
    logTelegramEvent("Отправлен ID пользователю: {\$user_id} ({\$first_name} {\$last_name})");
    exit;
}

EOD;
        
        // Ищем место для вставки (после обработки команды /start или перед первым отправлением сообщения)
        $insertion_point = strpos($webhook_content, '// Обработка команды /start');
        if ($insertion_point !== false) {
            // Находим конец обработчика команды /start
            $end_of_start_command = strpos($webhook_content, 'exit;', $insertion_point);
            if ($end_of_start_command !== false) {
                $insertion_point = strpos($webhook_content, "\n", $end_of_start_command) + 1;
            }
        } else {
            // Если нет обработчика /start, ищем sendTelegramMessage
            $insertion_point = strpos($webhook_content, 'sendTelegramMessage');
            if ($insertion_point !== false) {
                // Находим начало строки, содержащей sendTelegramMessage
                $line_start = strrpos(substr($webhook_content, 0, $insertion_point), "\n") + 1;
                $insertion_point = $line_start;
            } else {
                // Если ничего не нашли, просто вставляем перед концом файла
                $insertion_point = strrpos($webhook_content, '?>');
                if ($insertion_point === false) {
                    $insertion_point = strlen($webhook_content);
                }
            }
        }
        
        // Вставляем код
        $new_webhook_content = substr($webhook_content, 0, $insertion_point) . $id_command_code . substr($webhook_content, $insertion_point);
        
        // Сохраняем изменения
        if (file_put_contents($webhook_file, $new_webhook_content)) {
            echo "<div style='color: green;'>Обработчик команды /id успешно добавлен в файл webhook!</div>";
        } else {
            echo "<div style='color: red;'>Не удалось сохранить изменения в файле webhook!</div>";
        }
    }
}

// Проверяем наличие функций для отправки сообщений
echo "<h2>Проверка функций для отправки сообщений</h2>";

if (strpos($webhook_content, 'function sendTelegramMessage') === false) {
    echo "<div style='color: red;'>Функция sendTelegramMessage не найдена в файле webhook!</div>";
    
    // Добавляем базовые функции, если их нет
    $basic_functions = <<<EOD

/**
 * Отправляет сообщение в Telegram
 */
function sendTelegramMessage(\$chat_id, \$text, \$reply_markup = null) {
    \$data = [
        'chat_id' => \$chat_id,
        'text' => \$text,
        'parse_mode' => 'HTML'
    ];
    
    if (\$reply_markup) {
        \$data['reply_markup'] = \$reply_markup;
    }
    
    \$ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
    curl_setopt(\$ch, CURLOPT_POST, 1);
    curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$data);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    \$result = curl_exec(\$ch);
    curl_close(\$ch);
    
    return \$result;
}

/**
 * Записывает событие в лог
 */
function logTelegramEvent(\$message) {
    \$log_file = __DIR__ . '/telegram_log.txt';
    \$date = date("Y-m-d H:i:s");
    \$log_message = "{\$date} - {\$message}\\n";
    file_put_contents(\$log_file, \$log_message, FILE_APPEND);
}

EOD;
    
    // Находим место для вставки функций (в начале файла, после подключения конфигурации)
    $require_pos = strpos($webhook_content, 'require_once');
    if ($require_pos !== false) {
        $end_of_requires = strpos($webhook_content, "\n\n", $require_pos);
        if ($end_of_requires !== false) {
            $insertion_point = $end_of_requires + 2;
        } else {
            $insertion_point = strpos($webhook_content, "\n", $require_pos) + 1;
        }
    } else {
        $insertion_point = 0;
    }
    
    $new_webhook_content = substr($webhook_content, 0, $insertion_point) . $basic_functions . substr($webhook_content, $insertion_point);
    
    if (file_put_contents($webhook_file, $new_webhook_content)) {
        echo "<div style='color: green;'>Базовые функции для отправки сообщений добавлены в файл webhook!</div>";
    } else {
        echo "<div style='color: red;'>Не удалось добавить базовые функции в файл webhook!</div>";
    }
} else {
    echo "<div style='color: green;'>Функция sendTelegramMessage найдена в файле webhook.</div>";
}

// Добавляем ссылки для навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='test_telegram_message.php' style='margin-right: 15px;'>Тестирование отправки сообщений</a> | ";
echo "<a href='check_user_telegram.php' style='margin: 0 15px;'>Проверка привязки Telegram</a> | ";
echo "<a href='check_paths.php' style='margin-left: 15px;'>Проверка настроек Telegram</a>";
echo "</div>"; 