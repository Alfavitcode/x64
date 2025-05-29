<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

// Получаем ID пользователя
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Проверяем, передан ли ID пользователя
if (!$user_id) {
    die("Необходимо указать ID пользователя");
}

// Получаем данные о пользователе
$user = getUserById($user_id);
if (!$user) {
    die("Пользователь с ID $user_id не найден");
}

// Проверяем, есть ли привязанный Telegram ID
if (empty($user['telegram_id'])) {
    die("У пользователя нет привязанного Telegram ID. <a href='check_user_telegram.php?manual_link_form=$user_id'>Привязать вручную</a>");
}

// Выводим информацию о пользователе
echo "<h2>Отправка тестового сообщения пользователю</h2>";
echo "<p><strong>Имя:</strong> " . htmlspecialchars($user['fullname']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
echo "<p><strong>Telegram ID:</strong> " . htmlspecialchars($user['telegram_id']) . "</p>";

// Форма для отправки сообщения
if (isset($_POST['send_message']) && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    
    // Отправляем сообщение
    $data = [
        'chat_id' => $user['telegram_id'],
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    // Если указана кнопка, добавляем её
    if (!empty($_POST['button_text']) && !empty($_POST['button_data'])) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $_POST['button_text'],
                        'callback_data' => $_POST['button_data']
                    ]
                ]
            ]
        ];
        
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    
    if ($result === false) {
        echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>Ошибка при отправке сообщения: " . curl_error($ch) . "</div>";
    } else {
        $response = json_decode($result, true);
        
        if ($response && isset($response['ok']) && $response['ok']) {
            echo "<div style='color: green; font-weight: bold; margin: 20px 0;'>Сообщение успешно отправлено!</div>";
            echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        } else {
            echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>Ошибка при отправке сообщения!</div>";
            echo "<pre>" . htmlspecialchars($result) . "</pre>";
        }
    }
    
    curl_close($ch);
}

// Выводим форму для отправки сообщения
echo "<h3>Отправить сообщение</h3>";
echo "<form method='post'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='message' style='display: block; margin-bottom: 5px;'>Текст сообщения:</label>";
echo "<textarea id='message' name='message' rows='5' style='width: 100%; padding: 10px;'>Тестовое сообщение от x64shop</textarea>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; margin-bottom: 5px;'>Добавить кнопку:</label>";
echo "<div style='display: flex; gap: 10px;'>";
echo "<input type='text' name='button_text' placeholder='Текст кнопки' style='padding: 5px; flex: 1;'>";
echo "<input type='text' name='button_data' placeholder='Callback данные' style='padding: 5px; flex: 1;'>";
echo "</div>";
echo "<p style='font-size: 0.8em; color: #666; margin-top: 5px;'>Пример для подтверждения заказа: confirm_order_12 (где 12 - ID заказа)</p>";
echo "</div>";

echo "<button type='submit' name='send_message' style='padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 3px;'>Отправить</button>";
echo "</form>";

// Добавляем предустановленные шаблоны
echo "<h3 style='margin-top: 20px;'>Шаблоны сообщений</h3>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";

// Шаблон 1: Простое текстовое сообщение
echo "<button onclick=\"document.getElementById('message').value='Привет! Это тестовое сообщение от x64shop.\\n\\nМы проверяем работу Telegram уведомлений.'\" style='padding: 5px 10px; background-color: #f1f1f1; border: 1px solid #ddd; border-radius: 3px;'>Простое сообщение</button>";

// Шаблон 2: Уведомление о заказе
echo "<button onclick=\"document.getElementById('message').value='🛍️ <b>Новый заказ #123</b>\\n\\n📋 <b>Детали заказа:</b>\\nИмя: " . htmlspecialchars($user['fullname']) . "\\nАдрес: Москва, ул. Примерная, д. 123\\nДоставка: Курьерская доставка (300 ₽)\\n\\n🛒 <b>Товары:</b>\\n• Чехол iPhone 14 x1 - 1 200 ₽\\n\\n💰 <b>Итого:</b> 1 500 ₽\\n\\nДля подтверждения заказа отправьте команду:\\n<code>/accept 123</code>'\" style='padding: 5px 10px; background-color: #f1f1f1; border: 1px solid #ddd; border-radius: 3px;'>Уведомление о заказе</button>";

// Шаблон 3: Подтверждение аккаунта
echo "<button onclick=\"document.getElementById('message').value='Поздравляем! 🎉\\n\\nВаш Telegram успешно привязан к аккаунту " . htmlspecialchars($user['fullname']) . ".\\n\\nТеперь вы будете получать уведомления о входе в аккаунт и других важных событиях.'\" style='padding: 5px 10px; background-color: #f1f1f1; border: 1px solid #ddd; border-radius: 3px;'>Подтверждение аккаунта</button>";

echo "</div>";

// Кнопка получения ID
echo "<h3 style='margin-top: 20px;'>Добавить команду /id в бота</h3>";
echo "<p>Для получения Telegram ID пользователей вы можете добавить команду /id в вашего бота.</p>";
echo "<a href='add_id_command.php' class='button' style='display: inline-block; padding: 5px 10px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 3px;'>Добавить команду /id в бота</a>";

// Ссылки навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='check_user_telegram.php'>Вернуться к списку пользователей</a>";
echo "</div>"; 