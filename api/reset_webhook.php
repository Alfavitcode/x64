<?php
// Подключаем файл конфигурации
require_once '../includes/config/telegram_config.php';

// Определяем URL вебхука
$bot_token = TELEGRAM_BOT_TOKEN;
$webhook_url = TELEGRAM_WEBHOOK_URL;

// Сначала удаляем текущий вебхук
$delete_url = "https://api.telegram.org/bot$bot_token/deleteWebhook";
$ch = curl_init($delete_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$delete_result = curl_exec($ch);
curl_close($ch);

echo "Удаление вебхука: " . $delete_result . "<br>";

// Устанавливаем новый вебхук
$set_url = "https://api.telegram.org/bot$bot_token/setWebhook?url=$webhook_url&drop_pending_updates=true";
$ch = curl_init($set_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$set_result = curl_exec($ch);
curl_close($ch);

echo "Установка вебхука: " . $set_result . "<br>";

// Получаем информацию о вебхуке
$info_url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";
$ch = curl_init($info_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$info_result = curl_exec($ch);
curl_close($ch);

echo "Информация о вебхуке: <pre>" . json_encode(json_decode($info_result), JSON_PRETTY_PRINT) . "</pre>"; 