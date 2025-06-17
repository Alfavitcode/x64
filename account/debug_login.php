<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Устанавливаем заголовок для вывода текста
header('Content-Type: text/plain');

// Функция для безопасного вывода информации
function debug_print($label, $value) {
    if (is_array($value) || is_object($value)) {
        echo $label . ":\n";
        print_r($value);
        echo "\n\n";
    } else {
        echo $label . ": " . $value . "\n\n";
    }
}

// 1. Проверяем соединение с базой данных
debug_print("Соединение с базой данных", $conn ? "Успешно установлено" : "Ошибка: " . mysqli_connect_error());

// 2. Проверяем наличие таблицы users
$result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
debug_print("Таблица users существует", mysqli_num_rows($result) > 0 ? "Да" : "Нет");

// 3. Если таблица существует, проверяем её структуру
if (mysqli_num_rows($result) > 0) {
    $result = mysqli_query($conn, "DESCRIBE users");
    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row;
    }
    debug_print("Структура таблицы users", $columns);
}

// 4. Проверяем, есть ли пользователи в базе
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$count = mysqli_fetch_assoc($result)['count'];
debug_print("Количество пользователей в базе", $count);

// 5. Если указаны логин и пароль в GET-параметрах, пробуем авторизоваться
if (isset($_GET['login']) && isset($_GET['password'])) {
    $login = $_GET['login'];
    $password = $_GET['password'];
    
    debug_print("Попытка авторизации", "Логин: $login, Пароль: $password");
    
    // Получаем данные пользователя напрямую из базы
    $sql = "SELECT id, fullname, email, phone, login, password, role FROM users WHERE login = '" . mysqli_real_escape_string($conn, $login) . "'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        debug_print("Пользователь найден", $user);
        
        // Проверяем пароль напрямую
        $password_match = password_verify($password, $user['password']);
        debug_print("Результат проверки пароля", $password_match ? "Пароль верный" : "Пароль неверный");
        
        // Дополнительная информация о хеше пароля
        $hash_info = password_get_info($user['password']);
        debug_print("Информация о хеше пароля", $hash_info);
        
        // Проверяем, нужно ли обновить хеш (если используется устаревший алгоритм)
        debug_print("Нужно ли обновить хеш", password_needs_rehash($user['password'], PASSWORD_DEFAULT) ? "Да" : "Нет");
    } else {
        debug_print("Результат поиска пользователя", "Пользователь с логином '$login' не найден");
    }
    
    // Проверяем через функцию loginUser
    $login_result = loginUser($login, $password);
    debug_print("Результат авторизации через функцию loginUser", $login_result);
}

// 6. Создаем тестового пользователя, если указан флаг create_test
if (isset($_GET['create_test']) && $_GET['create_test'] == 1) {
    $test_fullname = "Тестовый Пользователь";
    $test_email = "test_" . time() . "@example.com";
    $test_phone = "+7900" . rand(1000000, 9999999);
    $test_login = "test_user_" . time();
    $test_password = "test123";
    
    debug_print("Создание тестового пользователя", [
        'fullname' => $test_fullname,
        'email' => $test_email,
        'phone' => $test_phone,
        'login' => $test_login,
        'password' => $test_password
    ]);
    
    $result = registerUser($test_fullname, $test_email, $test_phone, $test_login, $test_password);
    debug_print("Результат создания тестового пользователя", $result);
    
    if ($result['success']) {
        // Проверяем авторизацию нового пользователя
        $login_result = loginUser($test_login, $test_password);
        debug_print("Результат авторизации нового пользователя", $login_result);
    }
}

// 7. Если указан флаг rehash, пытаемся обновить хеш пароля для указанного пользователя
if (isset($_GET['rehash']) && isset($_GET['login']) && isset($_GET['password'])) {
    $login = $_GET['login'];
    $password = $_GET['password'];
    
    // Получаем данные пользователя
    $sql = "SELECT id, password FROM users WHERE login = '" . mysqli_real_escape_string($conn, $login) . "'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['id'];
        
        // Создаем новый хеш
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Обновляем пароль в базе
        $update_sql = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "' WHERE id = " . (int)$user_id;
        $update_result = mysqli_query($conn, $update_sql);
        
        debug_print("Обновление хеша пароля", $update_result ? "Успешно" : "Ошибка: " . mysqli_error($conn));
        
        // Проверяем авторизацию после обновления хеша
        $login_result = loginUser($login, $password);
        debug_print("Результат авторизации после обновления хеша", $login_result);
    } else {
        debug_print("Обновление хеша пароля", "Пользователь с логином '$login' не найден");
    }
}

echo "\n--- Конец отладочной информации ---\n";
?> 