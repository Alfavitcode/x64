<?php
// Проверка путей и настроек для отладки проблем с Telegram webhook

// Базовая информация о сервере
echo "<h2>Информация о сервере</h2>";
echo "<ul>";
echo "<li>PHP версия: " . phpversion() . "</li>";
echo "<li>Серверное ПО: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Имя хоста: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li>Документ рут: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Запрошенный URI: " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li>Скрипт: " . $_SERVER['SCRIPT_FILENAME'] . "</li>";
echo "</ul>";

// Проверяем директорию api
echo "<h2>Содержимое директории api</h2>";
$api_dir = __DIR__;
$files = scandir($api_dir);

echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $path = $api_dir . DIRECTORY_SEPARATOR . $file;
        $size = is_file($path) ? filesize($path) : 'Директория';
        $modified = date("Y-m-d H:i:s", filemtime($path));
        $permissions = substr(sprintf('%o', fileperms($path)), -4);
        
        echo "<li>";
        echo "$file - $size байт - изменен: $modified - права: $permissions";
        echo is_readable($path) ? " [Чтение: ✓]" : " [Чтение: ✗]";
        echo is_writable($path) ? " [Запись: ✓]" : " [Запись: ✗]";
        echo is_executable($path) ? " [Исполнение: ✓]" : " [Исполнение: ✗]";
        echo "</li>";
    }
}
echo "</ul>";

// Проверяем настройки Telegram
echo "<h2>Настройки Telegram</h2>";
if (file_exists('../includes/config/telegram_config.php')) {
    require_once '../includes/config/telegram_config.php';
    
    echo "<ul>";
    echo "<li>TELEGRAM_BOT_TOKEN: " . (defined('TELEGRAM_BOT_TOKEN') ? substr(TELEGRAM_BOT_TOKEN, 0, 8) . '...' : 'Не определен') . "</li>";
    echo "<li>TELEGRAM_WEBHOOK_URL: " . (defined('TELEGRAM_WEBHOOK_URL') ? TELEGRAM_WEBHOOK_URL : 'Не определен') . "</li>";
    echo "<li>TELEGRAM_BOT_USERNAME: " . (defined('TELEGRAM_BOT_USERNAME') ? TELEGRAM_BOT_USERNAME : 'Не определен') . "</li>";
    echo "</ul>";
    
    // Проверяем файл telegram_webhook.php
    $webhook_file = __DIR__ . '/telegram_webhook.php';
    echo "<h3>Файл telegram_webhook.php</h3>";
    
    if (file_exists($webhook_file)) {
        $file_url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/telegram_webhook.php';
        echo "<p>Файл существует. URL: <a href='$file_url'>$file_url</a></p>";
        
        // Проверяем доступность webhook URL через curl
        if (defined('TELEGRAM_WEBHOOK_URL')) {
            echo "<h3>Проверка доступности webhook URL</h3>";
            
            $ch = curl_init(TELEGRAM_WEBHOOK_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            echo "<p>HTTP-код: $http_code</p>";
            
            if ($http_code >= 200 && $http_code < 300) {
                echo "<p style='color: green;'>URL доступен!</p>";
            } else {
                echo "<p style='color: red;'>URL недоступен! Проверьте настройки сервера и webhook.</p>";
            }
            
            curl_close($ch);
            
            // Проверяем настройки webhook в Telegram
            echo "<h3>Проверка настроек webhook в Telegram</h3>";
            $bot_token = TELEGRAM_BOT_TOKEN;
            $url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
            
            $webhook_info = json_decode($result, true);
            
            if ($webhook_info && isset($webhook_info['ok']) && $webhook_info['ok']) {
                echo "<pre>" . json_encode($webhook_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                
                if (isset($webhook_info['result']['url']) && $webhook_info['result']['url'] === TELEGRAM_WEBHOOK_URL) {
                    echo "<p style='color: green;'>Webhook URL настроен правильно!</p>";
                } else {
                    echo "<p style='color: red;'>Webhook URL не соответствует конфигурации!</p>";
                    echo "<p>Настроен: " . htmlspecialchars($webhook_info['result']['url']) . "</p>";
                    echo "<p>Ожидается: " . htmlspecialchars(TELEGRAM_WEBHOOK_URL) . "</p>";
                }
                
                if (isset($webhook_info['result']['pending_update_count']) && $webhook_info['result']['pending_update_count'] > 0) {
                    echo "<p style='color: orange;'>Внимание! Есть " . $webhook_info['result']['pending_update_count'] . " необработанных обновлений!</p>";
                    echo "<p><a href='reset_webhook.php' style='color: red;'>Сбросить webhook и удалить необработанные обновления</a></p>";
                }
            } else {
                echo "<p style='color: red;'>Ошибка при получении информации о webhook!</p>";
                echo "<pre>" . htmlspecialchars($result) . "</pre>";
            }
        }
    } else {
        echo "<p style='color: red;'>Файл не существует!</p>";
    }
} else {
    echo "<p style='color: red;'>Файл конфигурации Telegram не найден!</p>";
}

// Добавляем ссылки для навигации
echo "<div style='margin-top: 20px;'>";
echo "<a href='telegram_log.php' style='margin-right: 10px;'>Просмотр логов</a> | ";
echo "<a href='reset_webhook.php' style='margin: 0 10px;'>Сбросить webhook</a> | ";
echo "<a href='check_orders_table.php' style='margin-left: 10px;'>Проверить таблицу заказов</a>";
echo "</div>"; 