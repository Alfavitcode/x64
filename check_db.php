<?php
// Подключаем файлы конфигурации
require_once 'includes/config/db_config.php';

echo 'Проверка подключения к базе данных: ' . (isset($conn) && $conn ? 'Успешно' : 'Ошибка') . PHP_EOL;

// Проверяем наличие таблиц
$result = mysqli_query($conn, 'SHOW TABLES');

if ($result) {
    echo 'Таблицы в базе данных:' . PHP_EOL;
    $tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
        echo '- ' . $row[0] . PHP_EOL;
    }

    // Проверяем наличие таблиц orders и order_items
    $hasOrders = in_array('orders', $tables);
    $hasOrderItems = in_array('order_items', $tables);

    echo PHP_EOL . 'Наличие необходимых таблиц:' . PHP_EOL;
    echo '- orders: ' . ($hasOrders ? 'Да' : 'Нет') . PHP_EOL;
    echo '- order_items: ' . ($hasOrderItems ? 'Да' : 'Нет') . PHP_EOL;
} else {
    echo 'Ошибка при получении списка таблиц: ' . mysqli_error($conn);
}
?> 