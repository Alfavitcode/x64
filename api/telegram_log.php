<?php
// Проверка доступа (опционально)
$allow_access = true;

// Путь к файлу логов
$log_file = __DIR__ . '/telegram_log.txt';

// Очистка лога, если нужно
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    file_put_contents($log_file, '');
    header('Location: telegram_log.php');
    exit;
}

// Чтение логов
$logs = 'Файл логов не найден или пуст.';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    
    // Если логов нет
    if (empty($logs)) {
        $logs = 'Лог пуст. Нет записей.';
    } else {
        // Преобразуем все символы < и > в HTML-сущности для безопасности
        $logs = htmlspecialchars($logs);
        // Делаем ссылки кликабельными
        $logs = preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank">$1</a>', $logs);
        // Форматируем JSON для более удобного чтения
        $logs = preg_replace_callback('/({(?:[^{}]|(?R))*})/', function($match) {
            $json = json_decode($match[0], true);
            if ($json) {
                return '<pre>' . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
            }
            return $match[0];
        }, $logs);
        // Добавляем переносы строк
        $logs = nl2br($logs);
        // Выделяем даты жирным
        $logs = preg_replace('/([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2})/', '<strong>$1</strong>', $logs);
    }
}

// Чтение данных сессий
$sessions_dir = __DIR__ . '/telegram_sessions';
$sessions = [];

if (file_exists($sessions_dir) && is_dir($sessions_dir)) {
    foreach (glob($sessions_dir . '/*.json') as $file) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if ($data) {
            $sessions[] = $data;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Bot Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .log-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            max-height: 600px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .log-container pre {
            margin: 10px 0;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
            overflow-x: auto;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            font-weight: 600;
        }
        .btn-action {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Логи Telegram бота</h1>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Панель управления
                    </div>
                    <div class="card-body">
                        <div class="btn-group mb-3">
                            <a href="set_telegram_webhook.php?action=set" class="btn btn-primary btn-action">Установить вебхук</a>
                            <a href="set_telegram_webhook.php?action=info" class="btn btn-info btn-action">Информация о вебхуке</a>
                            <a href="set_telegram_webhook.php?action=delete" class="btn btn-warning btn-action">Удалить вебхук</a>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="?clear=1" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите очистить лог?')">Очистить лог</a>
                            <a href="?refresh=1" class="btn btn-secondary">Обновить страницу</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Активные сессии (<?php echo count($sessions); ?>)
                    </div>
                    <div class="card-body">
                        <?php if (empty($sessions)): ?>
                            <p class="text-muted">Нет активных сессий</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($sessions as $session): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div><strong><?php echo htmlspecialchars($session['first_name'] ?? 'Неизвестно'); ?> <?php echo htmlspecialchars($session['last_name'] ?? ''); ?></strong></div>
                                            <div class="text-muted">@<?php echo htmlspecialchars($session['username'] ?? 'нет имени'); ?></div>
                                            <div class="text-muted small">ID: <?php echo htmlspecialchars($session['chat_id'] ?? 'н/д'); ?></div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo date('d.m.Y', $session['timestamp'] ?? time()); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Содержимое лога</span>
                <span class="badge bg-info"><?php echo date('d.m.Y H:i:s'); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="log-container">
                    <?php echo $logs; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="../index.php" class="btn btn-secondary">Вернуться на главную</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Автоматически прокручиваем лог в конец
        document.addEventListener('DOMContentLoaded', function() {
            const logContainer = document.querySelector('.log-container');
            logContainer.scrollTop = logContainer.scrollHeight;
        });
    </script>
</body>
</html> 