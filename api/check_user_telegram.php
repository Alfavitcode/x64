<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Проверяем наличие telegram_id в таблице пользователей
echo "<h2>Проверка привязки Telegram у пользователей</h2>";

// Проверяем наличие столбца telegram_id в таблице users
$table_check_sql = "SHOW COLUMNS FROM users LIKE 'telegram_id'";
$table_check_result = mysqli_query($conn, $table_check_sql);

if (!$table_check_result) {
    die("Ошибка при проверке структуры таблицы users: " . mysqli_error($conn));
}

if (mysqli_num_rows($table_check_result) == 0) {
    echo "<div style='color: red; font-weight: bold;'>Столбец telegram_id отсутствует в таблице users!</div>";
    echo "<p>Необходимо добавить столбец в таблицу:</p>";
    echo "<pre>ALTER TABLE users ADD COLUMN telegram_id VARCHAR(50) NULL;</pre>";
    
    // Предлагаем автоматически добавить столбец
    if (isset($_GET['add_column']) && $_GET['add_column'] == 1) {
        $add_column_sql = "ALTER TABLE users ADD COLUMN telegram_id VARCHAR(50) NULL";
        $add_column_result = mysqli_query($conn, $add_column_sql);
        
        if ($add_column_result) {
            echo "<div style='color: green;'>Столбец telegram_id успешно добавлен в таблицу users!</div>";
            echo "<meta http-equiv='refresh' content='2;url=check_user_telegram.php'>";
        } else {
            echo "<div style='color: red;'>Ошибка при добавлении столбца: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<p><a href='?add_column=1' style='padding: 5px 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;'>Добавить столбец</a></p>";
    }
    
    exit;
}

// Получаем пользователей с привязанным Telegram ID
$users_with_telegram_sql = "SELECT id, fullname, email, phone, telegram_id FROM users WHERE telegram_id IS NOT NULL";
$users_with_telegram_result = mysqli_query($conn, $users_with_telegram_sql);

if (!$users_with_telegram_result) {
    die("Ошибка при получении пользователей с привязанным Telegram: " . mysqli_error($conn));
}

// Получаем пользователей без привязанного Telegram ID
$users_without_telegram_sql = "SELECT id, fullname, email, phone FROM users WHERE telegram_id IS NULL";
$users_without_telegram_result = mysqli_query($conn, $users_without_telegram_sql);

if (!$users_without_telegram_result) {
    die("Ошибка при получении пользователей без привязанного Telegram: " . mysqli_error($conn));
}

// Выводим пользователей с привязанным Telegram ID
echo "<h3>Пользователи с привязанным Telegram (" . mysqli_num_rows($users_with_telegram_result) . ")</h3>";

if (mysqli_num_rows($users_with_telegram_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Telegram ID</th><th>Действия</th></tr>";
    
    while ($user = mysqli_fetch_assoc($users_with_telegram_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($user['telegram_id']) . "</td>";
        echo "<td>";
        echo "<a href='?unlink_telegram=" . $user['id'] . "' onclick='return confirm(\"Вы уверены, что хотите отвязать Telegram от пользователя " . htmlspecialchars($user['fullname']) . "?\")' style='color: red;'>Отвязать</a>";
        echo " | <a href='test_telegram_message.php?user_id=" . $user['id'] . "' target='_blank'>Тест</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Нет пользователей с привязанным Telegram ID!</p>";
}

// Выводим пользователей без привязанного Telegram ID
echo "<h3>Пользователи без привязанного Telegram (" . mysqli_num_rows($users_without_telegram_result) . ")</h3>";

if (mysqli_num_rows($users_without_telegram_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Действия</th></tr>";
    
    while ($user = mysqli_fetch_assoc($users_without_telegram_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
        echo "<td>";
        echo "<a href='?manual_link_form=" . $user['id'] . "'>Привязать вручную</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Нет пользователей без привязанного Telegram ID.</p>";
}

// Обработка отвязки Telegram
if (isset($_GET['unlink_telegram']) && is_numeric($_GET['unlink_telegram'])) {
    $user_id = (int)$_GET['unlink_telegram'];
    $unlink_sql = "UPDATE users SET telegram_id = NULL WHERE id = $user_id";
    $unlink_result = mysqli_query($conn, $unlink_sql);
    
    if ($unlink_result) {
        echo "<div style='color: green; margin-top: 20px;'>Telegram успешно отвязан от пользователя!</div>";
        echo "<meta http-equiv='refresh' content='2;url=check_user_telegram.php'>";
    } else {
        echo "<div style='color: red; margin-top: 20px;'>Ошибка при отвязке Telegram: " . mysqli_error($conn) . "</div>";
    }
}

// Форма для ручной привязки Telegram ID
if (isset($_GET['manual_link_form']) && is_numeric($_GET['manual_link_form'])) {
    $user_id = (int)$_GET['manual_link_form'];
    $user_sql = "SELECT id, fullname, email FROM users WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_sql);
    
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        
        echo "<h3 style='margin-top: 20px;'>Привязка Telegram ID к пользователю: " . htmlspecialchars($user['fullname']) . "</h3>";
        echo "<form method='post' action='?manual_link=" . $user_id . "'>";
        echo "<div style='margin-bottom: 10px;'>";
        echo "<label for='telegram_id' style='display: block; margin-bottom: 5px;'>Telegram ID:</label>";
        echo "<input type='text' id='telegram_id' name='telegram_id' required style='padding: 5px; width: 300px;'>";
        echo "<p style='font-size: 0.8em; color: #666;'>Telegram ID можно узнать, отправив боту команду /start и затем /id.</p>";
        echo "</div>";
        echo "<button type='submit' style='padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 3px;'>Привязать</button>";
        echo " <a href='check_user_telegram.php' style='text-decoration: none; color: #666;'>Отмена</a>";
        echo "</form>";
    } else {
        echo "<div style='color: red; margin-top: 20px;'>Пользователь не найден!</div>";
    }
}

// Обработка ручной привязки Telegram ID
if (isset($_GET['manual_link']) && is_numeric($_GET['manual_link']) && isset($_POST['telegram_id'])) {
    $user_id = (int)$_GET['manual_link'];
    $telegram_id = trim($_POST['telegram_id']);
    
    if (!empty($telegram_id)) {
        $link_sql = "UPDATE users SET telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "' WHERE id = $user_id";
        $link_result = mysqli_query($conn, $link_sql);
        
        if ($link_result) {
            echo "<div style='color: green; margin-top: 20px;'>Telegram ID успешно привязан к пользователю!</div>";
            echo "<meta http-equiv='refresh' content='2;url=check_user_telegram.php'>";
        } else {
            echo "<div style='color: red; margin-top: 20px;'>Ошибка при привязке Telegram ID: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div style='color: red; margin-top: 20px;'>Telegram ID не может быть пустым!</div>";
    }
}

// Проверка заказов с ожидающим подтверждением
echo "<h2 style='margin-top: 30px;'>Заказы, ожидающие подтверждения</h2>";

$pending_orders_sql = "SELECT o.id, o.user_id, o.fullname, o.status, o.created_at, u.telegram_id 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.status = 'pending_confirmation'";
$pending_orders_result = mysqli_query($conn, $pending_orders_sql);

if (!$pending_orders_result) {
    die("Ошибка при получении заказов: " . mysqli_error($conn));
}

if (mysqli_num_rows($pending_orders_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID заказа</th><th>ID пользователя</th><th>Имя</th><th>Статус</th><th>Дата создания</th><th>Telegram ID</th><th>Действия</th></tr>";
    
    while ($order = mysqli_fetch_assoc($pending_orders_result)) {
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td>" . $order['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($order['fullname']) . "</td>";
        echo "<td>" . $order['status'] . "</td>";
        echo "<td>" . $order['created_at'] . "</td>";
        echo "<td>" . (empty($order['telegram_id']) ? "<span style='color: red;'>Не привязан</span>" : htmlspecialchars($order['telegram_id'])) . "</td>";
        echo "<td>";
        
        if (!empty($order['telegram_id'])) {
            echo "<a href='simulate_telegram_callback.php?order_id=" . $order['id'] . "&user_id=" . $order['user_id'] . "' target='_blank'>Симулировать callback</a>";
        } else {
            echo "<a href='manual_confirm.php?id=" . $order['id'] . "&confirm=1' style='color: orange;'>Подтвердить вручную</a>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Нет заказов, ожидающих подтверждения.</p>";
}

// Добавляем скрипт для отправки тестового сообщения
echo "<h2 style='margin-top: 30px;'>Отправка тестового сообщения в Telegram</h2>";
echo "<form action='test_telegram_message.php' method='get' target='_blank'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='user_id_test' style='display: block; margin-bottom: 5px;'>ID пользователя:</label>";
echo "<input type='number' id='user_id_test' name='user_id' required style='padding: 5px; width: 200px;'>";
echo "</div>";
echo "<button type='submit' style='padding: 5px 10px; background-color: #2196F3; color: white; border: none; border-radius: 3px;'>Отправить тестовое сообщение</button>";
echo "</form>";

// Ссылки навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='manual_confirm.php' style='margin-right: 15px;'>Ручное подтверждение заказов</a> | ";
echo "<a href='check_paths.php' style='margin: 0 15px;'>Проверка настроек Telegram</a> | ";
echo "<a href='telegram_log.php' style='margin-left: 15px;'>Просмотр логов</a>";
echo "</div>"; 