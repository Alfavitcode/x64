<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Проверяем, переданы ли необходимые параметры
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$order_id || !$user_id) {
    die("Необходимо указать ID заказа и ID пользователя");
}

// Получаем данные о пользователе
$user = getUserById($user_id);
if (!$user) {
    die("Пользователь с ID $user_id не найден");
}

// Получаем данные о заказе
$order = getOrderById($order_id, $user_id);
if (!$order) {
    die("Заказ с ID $order_id не найден или не принадлежит пользователю с ID $user_id");
}

echo "<h2>Детали заказа</h2>";
echo "<pre>";
print_r($order);
echo "</pre>";

// Проверяем статус заказа
if ($order['status'] !== 'pending_confirmation') {
    die("Заказ уже имеет статус: " . $order['status']);
}

// Пытаемся обновить статус заказа на "pending"
$sql = "UPDATE orders SET status = 'pending' WHERE id = " . (int)$order_id;
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<div style='color: green; font-weight: bold;'>Заказ успешно подтвержден и переведен в статус 'pending'</div>";
    
    // Получаем элементы заказа для отображения
    $order_items = getOrderItems($order_id);
    echo "<h3>Товары в заказе:</h3>";
    echo "<ul>";
    foreach ($order_items as $item) {
        echo "<li>{$item['name']} x{$item['quantity']} - {$item['subtotal']} ₽</li>";
    }
    echo "</ul>";
    
    // Пытаемся отправить уведомление администраторам
    if (function_exists('sendOrderNotificationToAdmin')) {
        $admin_notification = sendOrderNotificationToAdmin($order_id);
        if ($admin_notification) {
            echo "<div style='color: green;'>Уведомление администраторам отправлено успешно</div>";
        } else {
            echo "<div style='color: orange;'>Не удалось отправить уведомление администраторам</div>";
        }
    } else {
        echo "<div style='color: red;'>Функция sendOrderNotificationToAdmin не найдена</div>";
    }
    
    // Проверяем, есть ли у пользователя привязанный Telegram
    if (!empty($user['telegram_id'])) {
        echo "<div>У пользователя есть привязанный Telegram ID: {$user['telegram_id']}</div>";
        
        // Формируем и отправляем сообщение
        $message = "✅ Тестовое подтверждение заказа #$order_id\n\n";
        $message .= "Заказ переведен в статус 'pending' через тестовый скрипт.";
        
        $data = [
            'chat_id' => $user['telegram_id'],
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_result = curl_exec($ch);
        
        if ($curl_result === false) {
            echo "<div style='color: red;'>Ошибка при отправке сообщения в Telegram: " . curl_error($ch) . "</div>";
        } else {
            echo "<div style='color: green;'>Сообщение в Telegram отправлено успешно</div>";
            echo "<pre>" . htmlspecialchars($curl_result) . "</pre>";
        }
        
        curl_close($ch);
    } else {
        echo "<div style='color: orange;'>У пользователя нет привязанного Telegram ID</div>";
    }
} else {
    echo "<div style='color: red; font-weight: bold;'>Ошибка при обновлении статуса заказа: " . mysqli_error($conn) . "</div>";
} 