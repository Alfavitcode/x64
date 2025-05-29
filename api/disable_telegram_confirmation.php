<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Включаем вывод всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Обновляем статус всех заказов, ожидающих подтверждения
$updated_orders = false;
$count = 0;

// Получаем список заказов со статусом pending_confirmation
$sql = "SELECT id FROM orders WHERE status = 'pending_confirmation'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    // Обновляем статус каждого заказа на 'pending'
    $update_sql = "UPDATE orders SET status = 'pending' WHERE status = 'pending_confirmation'";
    if (mysqli_query($conn, $update_sql)) {
        $count = mysqli_affected_rows($conn);
        $updated_orders = true;
    }
}

// Проверяем наличие trigger'а или event'а в базе данных, который может менять статус
$triggers = [];
$events = [];

$trigger_sql = "SHOW TRIGGERS";
$trigger_result = mysqli_query($conn, $trigger_sql);
if ($trigger_result) {
    while ($row = mysqli_fetch_assoc($trigger_result)) {
        $triggers[] = $row;
    }
}

$event_sql = "SHOW EVENTS";
$event_result = mysqli_query($conn, $event_sql);
if ($event_result) {
    while ($row = mysqli_fetch_assoc($event_result)) {
        $events[] = $row;
    }
}

// Проверяем наличие функции для отправки уведомлений в Telegram
$has_telegram_function = false;
$updated_checkout = false;

// Ищем файлы, содержащие код подтверждения через Telegram
$files_to_check = [
    '../includes/shop/checkout.php',
    '../includes/shop/order.php',
    '../includes/shop/cart.php',
    '../includes/config/telegram_config.php'
];

$files_with_confirmation = [];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'pending_confirmation') !== false) {
            $files_with_confirmation[] = $file;
        }
    }
}

// Обновление checkout.php для изменения статуса заказа
if (file_exists('../includes/shop/checkout.php')) {
    $checkout_content = file_get_contents('../includes/shop/checkout.php');
    
    // Заменяем все упоминания статуса 'pending_confirmation' на 'pending'
    $new_checkout_content = str_replace("'pending_confirmation'", "'pending'", $checkout_content);
    $new_checkout_content = str_replace('"pending_confirmation"', '"pending"', $new_checkout_content);
    
    if ($checkout_content !== $new_checkout_content) {
        file_put_contents('../includes/shop/checkout.php', $new_checkout_content);
        $updated_checkout = true;
    }
}

// HTML вывод результатов
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отключение подтверждения заказов через Telegram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f5f7f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
        .success-text {
            color: #28a745;
        }
        .warning-text {
            color: #ffc107;
        }
        .danger-text {
            color: #dc3545;
        }
        .info-text {
            color: #17a2b8;
        }
        .action-button {
            margin-top: 20px;
        }
        code {
            padding: 2px 5px;
            background-color: #f1f1f1;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Отключение подтверждения заказов через Telegram</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Статус операции
            </div>
            <div class="card-body">
                <h5>Изменение статусов заказов</h5>
                <?php if ($updated_orders): ?>
                <p class="success-text">✅ Успешно обновлено <?php echo $count; ?> заказов со статуса "pending_confirmation" на "pending".</p>
                <?php else: ?>
                <p class="info-text">ℹ️ Нет заказов, ожидающих подтверждения через Telegram.</p>
                <?php endif; ?>
                
                <h5 class="mt-4">Обновление файлов</h5>
                <?php if ($updated_checkout): ?>
                <p class="success-text">✅ Файл checkout.php успешно обновлен. Теперь заказы будут создаваться со статусом "pending".</p>
                <?php else: ?>
                <p class="warning-text">⚠️ Файл checkout.php не был обновлен или уже содержит правильный статус.</p>
                <?php endif; ?>
                
                <?php if (!empty($files_with_confirmation)): ?>
                <h5 class="mt-4">Файлы, требующие внимания</h5>
                <p class="warning-text">Обнаружены файлы, содержащие код для подтверждения через Telegram:</p>
                <ul>
                    <?php foreach ($files_with_confirmation as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>В этих файлах может потребоваться ручное обновление кода, связанного с подтверждением заказов.</p>
                <?php endif; ?>
                
                <?php if (!empty($triggers) || !empty($events)): ?>
                <h5 class="mt-4">Триггеры и события базы данных</h5>
                <?php if (!empty($triggers)): ?>
                <p class="warning-text">Обнаружены триггеры базы данных, которые могут влиять на статусы заказов:</p>
                <ul>
                    <?php foreach ($triggers as $trigger): ?>
                    <li><?php echo htmlspecialchars($trigger['Trigger']); ?> (<?php echo htmlspecialchars($trigger['Table']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <?php if (!empty($events)): ?>
                <p class="warning-text">Обнаружены события базы данных, которые могут влиять на статусы заказов:</p>
                <ul>
                    <?php foreach ($events as $event): ?>
                    <li><?php echo htmlspecialchars($event['Name']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <p>Может потребоваться проверка и обновление этих элементов базы данных.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                Дальнейшие действия
            </div>
            <div class="card-body">
                <p>Система изменена так, чтобы заказы больше не требовали подтверждения через Telegram. Теперь пользователям будет отправляться только уведомление о создании заказа.</p>
                
                <p>Рекомендуемые действия:</p>
                <ol>
                    <li>Обновите шаблоны уведомлений Telegram, чтобы убрать кнопки подтверждения</li>
                    <li>Убедитесь, что администраторы знают о новом процессе обработки заказов</li>
                    <li>При необходимости обновите другие файлы, связанные с подтверждением заказов</li>
                </ol>
                
                <div class="alert alert-info">
                    <strong>Примечание:</strong> Если вы хотите полностью отключить уведомления Telegram, удалите или закомментируйте код отправки сообщений в файлах, указанных выше.
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo file_exists('telegram_tools.php') ? 'telegram_tools.php' : '../index.php'; ?>" class="btn btn-primary">Вернуться к инструментам</a>
                    <a href="../index.php" class="btn btn-secondary ms-2">На главную</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 