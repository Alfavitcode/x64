<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';

// Устанавливаем заголовок для вывода текста
header('Content-Type: text/plain');

// Проверяем, есть ли секретный ключ для запуска скрипта
if (!isset($_GET['key']) || $_GET['key'] !== 'x64secure') {
    echo "Доступ запрещен. Необходим секретный ключ.";
    exit;
}

// Проверяем, указаны ли необходимые параметры
if (!isset($_GET['login']) || !isset($_GET['password'])) {
    echo "Ошибка: Необходимо указать логин и новый пароль.";
    echo "\nИспользование: update_password.php?key=x64secure&login=логин&password=новый_пароль";
    exit;
}

$login = $_GET['login'];
$new_password = $_GET['password'];

echo "Обновление пароля для пользователя: $login\n\n";

// 1. Проверяем соединение с базой данных
echo "Проверка соединения с базой данных: ";
if ($conn) {
    echo "Успешно\n";
} else {
    echo "Ошибка: " . mysqli_connect_error() . "\n";
    exit;
}

// 2. Проверяем наличие пользователя
$result = mysqli_query($conn, "SELECT id, login FROM users WHERE login = '" . mysqli_real_escape_string($conn, $login) . "'");

if (mysqli_num_rows($result) === 0) {
    echo "Ошибка: Пользователь с логином '$login' не найден.";
    exit;
}

$user = mysqli_fetch_assoc($result);
$user_id = $user['id'];

echo "Пользователь найден (ID: $user_id)\n";

// 3. Хешируем новый пароль
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
echo "Новый хеш пароля создан\n";

// 4. Обновляем пароль в базе
$update_sql = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "' WHERE id = " . (int)$user_id;

if (mysqli_query($conn, $update_sql)) {
    echo "Пароль успешно обновлен!\n";
    
    // Проверяем, что хеш работает
    $verify_result = password_verify($new_password, $new_hash);
    echo "Проверка хеша: " . ($verify_result ? "Успешно" : "Ошибка") . "\n";
    
    echo "\nТеперь вы можете войти в систему, используя:\n";
    echo "Логин: $login\n";
    echo "Пароль: $new_password\n";
} else {
    echo "Ошибка при обновлении пароля: " . mysqli_error($conn) . "\n";
}

echo "\n--- Конец процесса ---\n";
?> 