<?php
// Подключаем файл конфигурации базы данных
require_once '../includes/config/db_config.php';

// Выполняем запрос к информационной схеме для получения структуры таблицы orders
$sql = "DESCRIBE orders";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Ошибка при выполнении запроса: " . mysqli_error($conn));
}

echo "<h2>Структура таблицы orders</h2>";
echo "<table border='1'>";
echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Проверяем, существуют ли заказы со статусом pending_confirmation
$sql = "SELECT id, user_id, status, created_at FROM orders WHERE status = 'pending_confirmation' ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Ошибка при выполнении запроса: " . mysqli_error($conn));
}

echo "<h2>Заказы со статусом pending_confirmation</h2>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ID пользователя</th><th>Статус</th><th>Дата создания</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Заказов со статусом pending_confirmation не найдено.</p>";
}

// Проверяем, существуют ли заказы со статусом pending
$sql = "SELECT id, user_id, status, created_at FROM orders WHERE status = 'pending' ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Ошибка при выполнении запроса: " . mysqli_error($conn));
}

echo "<h2>Заказы со статусом pending</h2>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ID пользователя</th><th>Статус</th><th>Дата создания</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Заказов со статусом pending не найдено.</p>";
} 