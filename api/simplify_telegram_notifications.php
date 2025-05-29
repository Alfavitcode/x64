<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/telegram_config.php';

// Включаем вывод всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Обрабатываем отправку формы
$action_performed = false;
$action_result = '';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'disable_confirmation':
            // Перенаправляем на скрипт отключения подтверждения
            header('Location: disable_telegram_confirmation.php');
            exit;
            
        case 'update_webhook':
            // Перенаправляем на скрипт обновления webhook
            header('Location: update_telegram_webhook.php');
            exit;
            
        case 'disable_all':
            // Отключаем все уведомления Telegram
            $action_performed = true;
            
            // 1. Ищем файл конфигурации Telegram
            $config_file = '../includes/config/telegram_config.php';
            if (file_exists($config_file)) {
                $config_content = file_get_contents($config_file);
                $new_config_content = "<?php\n// Telegram API отключен\n// " . $config_content;
                file_put_contents($config_file, $new_config_content);
                $action_result .= "✅ Telegram API отключен в файле конфигурации.<br>";
            } else {
                $action_result .= "❌ Файл конфигурации Telegram не найден.<br>";
            }
            
            // 2. Обновляем статусы заказов
            $update_sql = "UPDATE orders SET status = 'pending' WHERE status = 'pending_confirmation'";
            if (mysqli_query($conn, $update_sql)) {
                $count = mysqli_affected_rows($conn);
                $action_result .= "✅ Обновлено $count заказов со статусом 'pending_confirmation'.<br>";
            } else {
                $action_result .= "❌ Ошибка при обновлении статусов заказов: " . mysqli_error($conn) . "<br>";
            }
            
            // 3. Очищаем логи Telegram
            $log_file = __DIR__ . '/telegram_log.txt';
            if (file_exists($log_file)) {
                file_put_contents($log_file, "// Telegram API отключен " . date('Y-m-d H:i:s') . "\n");
                $action_result .= "✅ Лог-файл Telegram очищен.<br>";
            }
            
            break;
    }
}

// Проверяем наличие заказов, ожидающих подтверждения
$pending_confirmation_count = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending_confirmation'";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $pending_confirmation_count = (int)$row['count'];
}

// Проверяем наличие webhook в Telegram
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

// Проверяем файлы, связанные с Telegram
$files_to_check = [
    'webhook' => __DIR__ . '/telegram_webhook.php',
    'config' => '../includes/config/telegram_config.php',
    'checkout' => '../includes/shop/checkout.php'
];

$files_status = [];
foreach ($files_to_check as $key => $file) {
    $files_status[$key] = file_exists($file);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Упрощение системы уведомлений Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7f9;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            font-weight: 600;
            padding: 15px 20px;
        }
        .option-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .option-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .option-icon {
            width: 50px;
            height: 50px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        .option-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .file-status {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .file-status i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Упрощение системы уведомлений Telegram</h1>
        
        <?php if ($action_performed): ?>
        <div class="alert alert-success mb-4">
            <h4 class="alert-heading">Действие выполнено!</h4>
            <p><?php echo $action_result; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Текущее состояние системы
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Статус заказов</h5>
                        <p>
                            <?php if ($pending_confirmation_count > 0): ?>
                            <span class="badge bg-warning"><?php echo $pending_confirmation_count; ?> заказов</span> ожидают подтверждения через Telegram.
                            <?php else: ?>
                            <span class="badge bg-success">0 заказов</span> ожидают подтверждения через Telegram.
                            <?php endif; ?>
                        </p>
                        
                        <h5 class="mt-4">Webhook в Telegram</h5>
                        <p>
                            Статус: 
                            <?php if ($webhook_status === 'Настроен'): ?>
                            <span class="badge bg-success">Настроен</span>
                            <?php elseif ($webhook_status === 'Не настроен'): ?>
                            <span class="badge bg-warning">Не настроен</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Ошибка</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($webhook_status === 'Настроен'): ?>
                        <p class="small text-muted"><?php echo htmlspecialchars($webhook_url); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Файлы системы</h5>
                        <div class="file-status">
                            <i class="fas fa-<?php echo $files_status['webhook'] ? 'check text-success' : 'times text-danger'; ?>"></i>
                            <span>telegram_webhook.php</span>
                        </div>
                        <div class="file-status">
                            <i class="fas fa-<?php echo $files_status['config'] ? 'check text-success' : 'times text-danger'; ?>"></i>
                            <span>telegram_config.php</span>
                        </div>
                        <div class="file-status">
                            <i class="fas fa-<?php echo $files_status['checkout'] ? 'check text-success' : 'times text-danger'; ?>"></i>
                            <span>checkout.php</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <h3 class="mb-3">Выберите действие:</h3>
        
        <form method="post">
            <div class="option-card">
                <div class="option-header">
                    <div class="option-icon" style="color: #17a2b8;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="option-title">Отключить подтверждение заказов</h3>
                </div>
                <p>Эта опция отключит необходимость подтверждения заказов через Telegram, но сохранит отправку уведомлений пользователям о создании заказов.</p>
                <p><strong>Что будет изменено:</strong></p>
                <ul>
                    <li>Статус новых заказов будет сразу "pending" вместо "pending_confirmation"</li>
                    <li>Все существующие заказы в статусе "pending_confirmation" будут переведены в "pending"</li>
                    <li>Уведомления Telegram не будут содержать кнопок подтверждения</li>
                </ul>
                <button type="submit" name="action" value="disable_confirmation" class="btn btn-primary">Отключить подтверждение</button>
            </div>
            
            <div class="option-card">
                <div class="option-header">
                    <div class="option-icon" style="color: #28a745;">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="option-title">Обновить webhook-обработчик</h3>
                </div>
                <p>Эта опция обновит webhook-обработчик Telegram, чтобы он отправлял только уведомления о создании заказов без запроса на подтверждение.</p>
                <p><strong>Что будет изменено:</strong></p>
                <ul>
                    <li>Код обработки callback-запросов подтверждения будет заменен</li>
                    <li>Команда /accept больше не будет подтверждать заказы</li>
                    <li>Будет добавлена функция для отправки простых уведомлений</li>
                </ul>
                <button type="submit" name="action" value="update_webhook" class="btn btn-success">Обновить webhook</button>
            </div>
            
            <div class="option-card">
                <div class="option-header">
                    <div class="option-icon" style="color: #dc3545;">
                        <i class="fas fa-power-off"></i>
                    </div>
                    <h3 class="option-title">Полностью отключить Telegram</h3>
                </div>
                <p>Эта опция полностью отключит интеграцию с Telegram. Уведомления перестанут отправляться, а заказы будут создаваться как обычно.</p>
                <p><strong>Что будет изменено:</strong></p>
                <ul>
                    <li>Будет отключен Telegram API в файле конфигурации</li>
                    <li>Все заказы со статусом "pending_confirmation" будут переведены в "pending"</li>
                    <li>Лог-файл Telegram будет очищен</li>
                </ul>
                <button type="submit" name="action" value="disable_all" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите полностью отключить интеграцию с Telegram?')">Отключить Telegram</button>
            </div>
        </form>
        
        <div class="mt-4 text-center">
            <a href="<?php echo file_exists('telegram_tools.php') ? 'telegram_tools.php' : '../index.php'; ?>" class="btn btn-secondary">Вернуться назад</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 