<?php
// Проверка доступа (опционально)
$allow_access = true;

// Путь к файлу логов
$log_file = __DIR__ . '/telegram_log.txt';

// Проверяем действие
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

switch ($action) {
    case 'clear':
        // Очищаем лог-файл
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
            echo "Лог-файл очищен успешно!";
            echo "<p><a href='?action=view'>Вернуться к просмотру</a></p>";
        } else {
            echo "Лог-файл не существует!";
        }
        break;
        
    case 'view':
    default:
        // Выводим содержимое лог-файла
        echo "<h1>Содержимое лог-файла Telegram</h1>";
        
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            
            if (empty($log_content)) {
                echo "<div style='color: orange; font-weight: bold;'>Лог-файл пуст!</div>";
            } else {
                echo "<div style='text-align: right; margin-bottom: 10px;'>";
                echo "<a href='?action=clear' onclick='return confirm(\"Вы уверены, что хотите очистить лог-файл?\")' style='padding: 5px 10px; background-color: #f44336; color: white; text-decoration: none; border-radius: 3px;'>Очистить лог</a>";
                echo "</div>";
                
                // Выводим содержимое в обратном порядке (новые записи сверху)
                $lines = explode("\n", $log_content);
                $chunks = [];
                $current_chunk = [];
                
                // Группируем связанные записи
                foreach ($lines as $line) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} - Получен запрос/', $line)) {
                        if (!empty($current_chunk)) {
                            $chunks[] = $current_chunk;
                            $current_chunk = [];
                        }
                    }
                    $current_chunk[] = $line;
                }
                
                if (!empty($current_chunk)) {
                    $chunks[] = $current_chunk;
                }
                
                // Выводим чанки в обратном порядке
                foreach (array_reverse($chunks) as $index => $chunk) {
                    echo "<div style='margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f9f9f9;'>";
                    echo "<h3>Запрос #" . ($index + 1) . "</h3>";
                    echo "<pre style='white-space: pre-wrap; max-height: 400px; overflow-y: auto;'>";
                    foreach ($chunk as $line) {
                        // Подсветка ошибок
                        if (strpos($line, 'Ошибка') !== false) {
                            echo "<span style='color: red;'>" . htmlspecialchars($line) . "</span>\n";
                        }
                        // Подсветка успешных действий
                        else if (strpos($line, 'успешно') !== false || strpos($line, 'Успешно') !== false) {
                            echo "<span style='color: green;'>" . htmlspecialchars($line) . "</span>\n";
                        }
                        else {
                            echo htmlspecialchars($line) . "\n";
                        }
                    }
                    echo "</pre>";
                    echo "</div>";
                }
            }
        } else {
            echo "<div style='color: red; font-weight: bold;'>Лог-файл не существует!</div>";
        }
        
        // Добавляем ссылки для навигации
        echo "<div style='margin-top: 20px;'>";
        echo "<a href='check_webhook.php' style='margin-right: 10px;'>Проверить webhook</a> | ";
        echo "<a href='reset_webhook.php' style='margin: 0 10px;'>Сбросить webhook</a> | ";
        echo "<a href='check_orders_table.php' style='margin-left: 10px;'>Проверить таблицу заказов</a>";
        echo "</div>";
        
        // Автоматическое обновление страницы каждые 5 секунд
        echo "<script>
        setTimeout(function() {
            location.reload();
        }, 5000);
        </script>";
        
        break;
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
                    <?php 
                    $logs = "";
                    if (file_exists($log_file)) {
                        $log_content = file_get_contents($log_file);
                        if (!empty($log_content)) {
                            // Обработка и форматирование логов
                            $lines = explode("\n", $log_content);
                            
                            foreach ($lines as $line) {
                                if (empty($line)) continue;
                                
                                // Подсветка ошибок
                                if (strpos($line, 'Ошибка') !== false || strpos($line, 'ошибка') !== false) {
                                    $logs .= "<span style='color: red;'>" . htmlspecialchars($line) . "</span><br>";
                                }
                                // Подсветка успешных действий
                                else if (strpos($line, 'успешно') !== false || strpos($line, 'Успешно') !== false) {
                                    $logs .= "<span style='color: green;'>" . htmlspecialchars($line) . "</span><br>";
                                }
                                else {
                                    $logs .= htmlspecialchars($line) . "<br>";
                                }
                            }
                        } else {
                            $logs = "<div style='color: orange; padding: 20px;'>Лог-файл пуст!</div>";
                        }
                    } else {
                        $logs = "<div style='color: red; padding: 20px;'>Лог-файл не существует!</div>";
                    }
                    
                    echo $logs;
                    ?>
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