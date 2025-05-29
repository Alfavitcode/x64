<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/telegram_config.php';

// Получаем статистику заказов с ожидающим подтверждением
$pending_confirmation_count = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending_confirmation'";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $pending_confirmation_count = (int)$row['count'];
}

// Проверяем настройки webhook в Telegram
$webhook_status = "Неизвестно";
$webhook_url = "";
$pending_updates = 0;

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
        
        if (isset($webhook_info['result']['pending_update_count'])) {
            $pending_updates = $webhook_info['result']['pending_update_count'];
        }
    } else {
        $webhook_status = "Ошибка";
    }
}

// Проверяем наличие пользователей с привязанным Telegram
$users_with_telegram_count = 0;
$sql = "SELECT COUNT(*) as count FROM users WHERE telegram_id IS NOT NULL";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $users_with_telegram_count = (int)$row['count'];
}

// Получаем размер лог-файла
$log_file = __DIR__ . '/telegram_log.txt';
$log_size = file_exists($log_file) ? filesize($log_file) : 0;
$log_size_formatted = $log_size > 0 ? number_format($log_size / 1024, 2) . " КБ" : "0 КБ";
$log_empty = $log_size === 0;

// Проверяем права доступа к файлам
$webhook_file = __DIR__ . '/telegram_webhook.php';
$webhook_writable = file_exists($webhook_file) && is_writable($webhook_file);
$log_writable = is_writable(__DIR__) || (file_exists($log_file) && is_writable($log_file));

// Формируем список возможных проблем
$issues = [];

if ($webhook_status !== "Настроен") {
    $issues[] = "Webhook не настроен или настроен некорректно.";
}

if ($log_empty) {
    $issues[] = "Лог-файл пуст, что может указывать на отсутствие запросов от Telegram.";
}

if (!$webhook_writable) {
    $issues[] = "Файл webhook.php недоступен для записи, что может затруднить его обновление.";
}

if (!$log_writable) {
    $issues[] = "Нет прав на запись лог-файла, что может препятствовать логированию.";
}

if ($users_with_telegram_count === 0) {
    $issues[] = "Нет пользователей с привязанным Telegram ID.";
}

// Формируем список рекомендаций
$recommendations = [];

if ($webhook_status !== "Настроен") {
    $recommendations[] = "Проверьте и настройте webhook через скрипт <a href='check_paths.php'>check_paths.php</a> или <a href='reset_webhook.php'>reset_webhook.php</a>.";
}

if ($log_empty) {
    $recommendations[] = "Проверьте доступность вашего сервера извне и корректность настройки SSL-сертификата.";
}

if ($users_with_telegram_count === 0) {
    $recommendations[] = "Привяжите Telegram ID к учетным записям пользователей через <a href='check_user_telegram.php'>check_user_telegram.php</a>.";
}

$recommendations[] = "Проверьте функцию getUserByTelegramId и структуру таблиц через <a href='fix_telegram_function.php'>fix_telegram_function.php</a>.";
$recommendations[] = "Проверьте возможность отправки сообщений через <a href='test_telegram_message.php?user_id=1'>test_telegram_message.php</a>.";
$recommendations[] = "Симулируйте callback от Telegram через <a href='simulate_telegram_callback.php'>simulate_telegram_callback.php</a>.";
$recommendations[] = "Проверьте логи через <a href='telegram_log.php'>telegram_log.php</a>.";
$recommendations[] = "Временное решение: используйте ручное подтверждение заказов через <a href='manual_confirm.php'>manual_confirm.php</a>.";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram - Диагностика и устранение проблем</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-weight: 600;
        }
        .stats-card {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
            color: #4e73df;
        }
        .tool-link {
            display: block;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        .tool-link:hover {
            background-color: #e2e6ea;
            transform: translateX(5px);
        }
        .tool-link i {
            margin-right: 10px;
        }
        .issues-list li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Telegram - Диагностика и устранение проблем</h1>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Заказы ожидающие подтверждения</h5>
                    <div class="stat-value"><?php echo $pending_confirmation_count; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Webhook статус</h5>
                    <div class="stat-value" style="color: <?php echo $webhook_status === 'Настроен' ? '#28a745' : '#dc3545'; ?>">
                        <?php echo $webhook_status; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Пользователи с Telegram</h5>
                    <div class="stat-value"><?php echo $users_with_telegram_count; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Размер лога</h5>
                    <div class="stat-value" style="color: <?php echo $log_empty ? '#dc3545' : '#28a745'; ?>">
                        <?php echo $log_size_formatted; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($issues)): ?>
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                Обнаруженные проблемы
            </div>
            <div class="card-body">
                <ul class="issues-list">
                    <?php foreach ($issues as $issue): ?>
                    <li><?php echo $issue; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Диагностические инструменты
                    </div>
                    <div class="card-body">
                        <a href="check_user_telegram.php" class="tool-link">
                            <i class="fas fa-users"></i> Проверка привязки Telegram у пользователей
                        </a>
                        <a href="fix_telegram_function.php" class="tool-link">
                            <i class="fas fa-code"></i> Проверка и исправление функций Telegram
                        </a>
                        <a href="test_telegram_message.php" class="tool-link">
                            <i class="fas fa-paper-plane"></i> Отправка тестовых сообщений
                        </a>
                        <a href="simulate_telegram_callback.php" class="tool-link">
                            <i class="fas fa-exchange-alt"></i> Симуляция callback от Telegram
                        </a>
                        <a href="check_paths.php" class="tool-link">
                            <i class="fas fa-cogs"></i> Проверка настроек и путей Telegram
                        </a>
                        <a href="telegram_log.php" class="tool-link">
                            <i class="fas fa-list-alt"></i> Просмотр лог-файла Telegram
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Рекомендации по устранению проблем
                    </div>
                    <div class="card-body">
                        <ol class="recommendations-list">
                            <?php foreach ($recommendations as $recommendation): ?>
                            <li class="mb-3"><?php echo $recommendation; ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        Временное решение
                    </div>
                    <div class="card-body">
                        <p>Пока вы устраняете проблемы с Telegram, вы можете использовать ручное подтверждение заказов:</p>
                        <a href="manual_confirm.php" class="btn btn-warning w-100">Ручное подтверждение заказов</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="../index.php" class="btn btn-secondary">Вернуться на главную</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html> 