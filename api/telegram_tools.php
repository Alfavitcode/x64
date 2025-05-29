<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/telegram_config.php';

// Проверяем наличие webhook-файла
$webhook_file = __DIR__ . '/telegram_webhook.php';
$webhook_exists = file_exists($webhook_file);

// Проверяем настройки webhook в Telegram
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

// Проверяем количество заказов с ожидающим подтверждением
$pending_orders_count = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending_confirmation'";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $pending_orders_count = (int)$row['count'];
}

// Проверяем пользователей с Telegram ID
$users_with_telegram_count = 0;
$sql = "SELECT COUNT(*) as count FROM users WHERE telegram_id IS NOT NULL";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $users_with_telegram_count = (int)$row['count'];
}

// Проверяем размер лог-файла
$log_file = __DIR__ . '/telegram_log.txt';
$log_size = file_exists($log_file) ? filesize($log_file) : 0;
$log_size_formatted = $log_size > 0 ? number_format($log_size / 1024, 2) . " КБ" : "0 КБ";

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструменты Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7f9;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            font-weight: 600;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .tool-link {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        .tool-link:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        .tool-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background-color: #4e73df;
            color: white;
            border-radius: 50%;
            font-size: 1.2rem;
        }
        .tool-content {
            flex: 1;
        }
        .tool-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .tool-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-card {
            border-left: 5px solid;
            margin-bottom: 20px;
        }
        .status-card.good {
            border-color: #28a745;
        }
        .status-card.warning {
            border-color: #ffc107;
        }
        .status-card.danger {
            border-color: #dc3545;
        }
        .status-icon {
            font-size: 2rem;
            margin-right: 15px;
        }
        .status-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .page-title {
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Инструменты Telegram</h1>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card status-card <?php echo $webhook_status === 'Настроен' ? 'good' : 'danger'; ?>">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas <?php echo $webhook_status === 'Настроен' ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-danger'; ?> status-icon"></i>
                        <div>
                            <h5 class="mb-1">Webhook</h5>
                            <div class="status-value"><?php echo $webhook_status; ?></div>
                            <?php if ($webhook_status === 'Настроен'): ?>
                            <small class="text-muted"><?php echo $webhook_url; ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card status-card <?php echo $users_with_telegram_count > 0 ? 'good' : 'warning'; ?>">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-users status-icon <?php echo $users_with_telegram_count > 0 ? 'text-success' : 'text-warning'; ?>"></i>
                        <div>
                            <h5 class="mb-1">Пользователи с Telegram</h5>
                            <div class="status-value"><?php echo $users_with_telegram_count; ?></div>
                            <small class="text-muted">
                                <?php echo $pending_orders_count; ?> заказов ожидают подтверждения
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-tools me-2"></i> Диагностика и исправление
                    </div>
                    <div class="card-body">
                        <a href="telegram_diagnostics.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #4e73df;">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Панель диагностики</div>
                                <p class="tool-description">Сводка всех проблем и рекомендации по их устранению</p>
                            </div>
                        </a>
                        
                        <a href="check_user_telegram.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #1cc88a;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Проверка привязки Telegram</div>
                                <p class="tool-description">Управление привязкой Telegram к учетным записям пользователей</p>
                            </div>
                        </a>
                        
                        <a href="debug_getUserByTelegramId.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #f6c23e;">
                                <i class="fas fa-bug"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Отладка функции getUserByTelegramId</div>
                                <p class="tool-description">Диагностика проблем с функцией поиска пользователя по Telegram ID</p>
                            </div>
                        </a>
                        
                        <a href="fix_getUserByTelegramId.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #e74a3b;">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Исправление функции getUserByTelegramId</div>
                                <p class="tool-description">Улучшение функции для поддержки разных форматов Telegram ID</p>
                            </div>
                        </a>
                        
                        <a href="fix_telegram_function.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #36b9cc;">
                                <i class="fas fa-code"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Проверка и добавление функций</div>
                                <p class="tool-description">Проверка наличия необходимых функций и их добавление</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-cogs me-2"></i> Тестирование и настройка
                    </div>
                    <div class="card-body">
                        <a href="test_telegram_message.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #1cc88a;">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Отправка тестовых сообщений</div>
                                <p class="tool-description">Тестирование отправки сообщений пользователям через Telegram</p>
                            </div>
                        </a>
                        
                        <a href="check_paths.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #4e73df;">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Проверка настроек и путей</div>
                                <p class="tool-description">Проверка путей и конфигурации для работы с Telegram</p>
                            </div>
                        </a>
                        
                        <a href="telegram_log.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #858796;">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Просмотр логов</div>
                                <p class="tool-description">Просмотр логов обработки webhook-запросов от Telegram</p>
                                <span class="status-badge bg-info"><?php echo $log_size_formatted; ?></span>
                            </div>
                        </a>
                        
                        <a href="test_callback.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #f6c23e;">
                                <i class="fas fa-sync"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Тест Telegram Callback</div>
                                <p class="tool-description">Прямое тестирование обработки callback без использования webhook</p>
                            </div>
                        </a>
                        
                        <a href="simulate_telegram_callback.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #e74a3b;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Симуляция Telegram Callback</div>
                                <p class="tool-description">Симуляция callback-запроса от Telegram через curl</p>
                            </div>
                        </a>
                        
                        <a href="add_id_command.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #36b9cc;">
                                <i class="fas fa-terminal"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Добавление команды /id</div>
                                <p class="tool-description">Добавление команды для получения Telegram ID</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <i class="fas fa-exclamation-circle me-2"></i> Временное решение
                    </div>
                    <div class="card-body">
                        <a href="manual_confirm.php" class="tool-link">
                            <div class="tool-icon" style="background-color: #f6c23e;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="tool-content">
                                <div class="tool-title">Ручное подтверждение заказов</div>
                                <p class="tool-description">Временное решение для подтверждения заказов без использования Telegram</p>
                                <?php if ($pending_orders_count > 0): ?>
                                <span class="status-badge bg-danger"><?php echo $pending_orders_count; ?> заказов</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="../index.php" class="btn btn-secondary">Вернуться на главную</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 