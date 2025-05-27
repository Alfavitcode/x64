<?php
// Подключаем файл конфигурации
require_once '../includes/config/telegram_config.php';

// Функция для установки вебхука
function setTelegramWebhook() {
    $bot_token = TELEGRAM_BOT_TOKEN;
    $webhook_url = TELEGRAM_WEBHOOK_URL;
    
    // Сначала удаляем текущий вебхук
    $delete_url = "https://api.telegram.org/bot$bot_token/deleteWebhook";
    $ch = curl_init($delete_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $delete_result = curl_exec($ch);
    curl_close($ch);
    
    // Формируем URL для установки нового вебхука
    $set_url = "https://api.telegram.org/bot$bot_token/setWebhook?url=$webhook_url&drop_pending_updates=true";
    
    // Отправляем запрос
    $ch = curl_init($set_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    // Декодируем результат
    $response = json_decode($result, true);
    
    return $response;
}

// Функция для получения информации о текущем вебхуке
function getTelegramWebhookInfo() {
    $bot_token = TELEGRAM_BOT_TOKEN;
    
    // Формируем URL для запроса
    $url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";
    
    // Отправляем запрос
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    // Декодируем результат
    $response = json_decode($result, true);
    
    return $response;
}

// Функция для удаления вебхука
function deleteTelegramWebhook() {
    $bot_token = TELEGRAM_BOT_TOKEN;
    
    // Формируем URL для запроса
    $url = "https://api.telegram.org/bot$bot_token/deleteWebhook";
    
    // Отправляем запрос
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    // Декодируем результат
    $response = json_decode($result, true);
    
    return $response;
}

// Проверяем действие
$action = isset($_GET['action']) ? $_GET['action'] : 'set';

// Выполняем действие
switch ($action) {
    case 'set':
        $result = setTelegramWebhook();
        $title = 'Установка вебхука';
        break;
    case 'info':
        $result = getTelegramWebhookInfo();
        $title = 'Информация о вебхуке';
        break;
    case 'delete':
        $result = deleteTelegramWebhook();
        $title = 'Удаление вебхука';
        break;
    default:
        $result = ['error' => 'Неизвестное действие'];
        $title = 'Ошибка';
}

// Выводим результат
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление вебхуком Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1><?php echo $title; ?></h1>
        
        <div class="card my-4">
            <div class="card-header">Результат</div>
            <div class="card-body">
                <pre><?php echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="?action=set" class="btn btn-primary">Установить вебхук</a>
            <a href="?action=info" class="btn btn-info">Информация о вебхуке</a>
            <a href="?action=delete" class="btn btn-danger">Удалить вебхук</a>
        </div>
        
        <div class="mt-4">
            <a href="../index.php" class="btn btn-secondary">Вернуться на главную</a>
        </div>
    </div>
</body>
</html> 