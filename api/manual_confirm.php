<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Проверяем, был ли отправлен ID заказа
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$confirm = isset($_GET['confirm']) && $_GET['confirm'] == 1;

// Если запрос на подтверждение заказа
if ($confirm && $order_id) {
    // Получаем данные о заказе
    $order = getOrderById($order_id);
    
    if (!$order) {
        die("Заказ с ID $order_id не найден");
    }
    
    if ($order['status'] !== 'pending_confirmation') {
        die("Заказ уже имеет статус: " . $order['status']);
    }
    
    // Обновляем статус заказа на "pending"
    $sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        // Перенаправляем обратно на страницу со списком заказов
        header("Location: manual_confirm.php?success=1&id=" . $order_id);
        exit;
    } else {
        die("Ошибка при обновлении статуса заказа: " . mysqli_error($conn));
    }
}

// Успешное подтверждение
$success = isset($_GET['success']) && $_GET['success'] == 1;
$success_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Показываем список заказов со статусом pending_confirmation
$sql = "SELECT o.*, u.fullname as user_fullname, u.email as user_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.status = 'pending_confirmation' 
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Ошибка при выполнении запроса: " . mysqli_error($conn));
}

// Вывод HTML
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ручное подтверждение заказов</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .alert { margin-bottom: 20px; }
        .table { margin-top: 20px; }
        .btn-confirm { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ручное подтверждение заказов</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            Заказ #<?php echo $success_id; ?> успешно подтвержден!
        </div>
        <?php endif; ?>
        
        <h2>Заказы, ожидающие подтверждения</h2>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Клиент</th>
                    <th>Контакты</th>
                    <th>Сумма</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td>
                        <?php 
                        if (!empty($order['user_fullname'])) {
                            echo $order['user_fullname'];
                        } else {
                            echo $order['fullname'];
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($order['user_email'])) {
                            echo $order['user_email'] . '<br>';
                        } else if (!empty($order['email'])) {
                            echo $order['email'] . '<br>';
                        }
                        if (!empty($order['phone'])) {
                            echo $order['phone'];
                        }
                        ?>
                    </td>
                    <td><?php echo number_format($order['total_amount'], 0, '.', ' '); ?> ₽</td>
                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="?id=<?php echo $order['id']; ?>&confirm=1" class="btn btn-primary btn-sm btn-confirm" onclick="return confirm('Вы уверены, что хотите подтвердить заказ #<?php echo $order['id']; ?>?');">
                            Подтвердить
                        </a>
                        <a href="test_order_confirmation.php?order_id=<?php echo $order['id']; ?>&user_id=<?php echo $order['user_id']; ?>" class="btn btn-info btn-sm" target="_blank">
                            Тест
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert alert-info">
            Нет заказов, ожидающих подтверждения.
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="../admin/index.php" class="btn btn-secondary mr-2">Вернуться в админку</a>
            <a href="check_orders_table.php" class="btn btn-info mr-2">Проверить таблицу заказов</a>
            <a href="check_paths.php" class="btn btn-warning">Проверить настройки Telegram</a>
        </div>
    </div>
</body>
</html> 