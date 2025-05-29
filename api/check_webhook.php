<?php
// Подключаем файл конфигурации
require_once '../includes/config/telegram_config.php';

// Получаем информацию о вебхуке
$bot_token = TELEGRAM_BOT_TOKEN;
$url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";

// Отправляем запрос
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);

// Выводим результат в формате JSON
header('Content-Type: application/json');
echo $result; 