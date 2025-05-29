<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Включаем вывод всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Диагностика функции getUserByTelegramId</h2>";

// Проверяем наличие функции
if (!function_exists('getUserByTelegramId')) {
    echo "<div style='color: red; font-weight: bold;'>Ошибка: Функция getUserByTelegramId не существует!</div>";
    exit;
}

// Получаем Telegram ID из GET-параметра или используем ID со скриншота
$telegram_id = isset($_GET['telegram_id']) ? $_GET['telegram_id'] : '1169792623';

// Записываем информацию о тестируемом ID
echo "<p>Тестируем с Telegram ID: <strong>" . htmlspecialchars($telegram_id) . "</strong></p>";

// Проверяем наличие пользователя с таким Telegram ID напрямую через SQL-запрос
$sql = "SELECT id, fullname, email, telegram_id FROM users WHERE telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "<div style='color: red;'>Ошибка SQL: " . mysqli_error($conn) . "</div>";
    exit;
}

echo "<h3>Результат прямого SQL-запроса:</h3>";
if (mysqli_num_rows($result) > 0) {
    $user_from_sql = mysqli_fetch_assoc($result);
    echo "<div style='color: green;'>Пользователь найден через SQL:</div>";
    echo "<pre>";
    print_r($user_from_sql);
    echo "</pre>";
} else {
    echo "<div style='color: red;'>Пользователь с Telegram ID " . htmlspecialchars($telegram_id) . " не найден в базе данных!</div>";
    
    // Проверим, может ID представлен в разных форматах
    echo "<p>Проверяем возможные альтернативные форматы ID...</p>";
    
    // Пробуем искать по целочисленному значению
    $sql_int = "SELECT id, fullname, email, telegram_id FROM users WHERE telegram_id = " . (int)$telegram_id;
    $result_int = mysqli_query($conn, $sql_int);
    
    if ($result_int && mysqli_num_rows($result_int) > 0) {
        $user_from_sql_int = mysqli_fetch_assoc($result_int);
        echo "<div style='color: green;'>Пользователь найден через SQL (целочисленный формат):</div>";
        echo "<pre>";
        print_r($user_from_sql_int);
        echo "</pre>";
    } else {
        echo "<div style='color: red;'>Пользователь также не найден с использованием целочисленного формата.</div>";
    }
}

// Проверяем работу функции getUserByTelegramId
echo "<h3>Результат вызова функции getUserByTelegramId:</h3>";
$user_from_function = getUserByTelegramId($telegram_id);

if ($user_from_function) {
    echo "<div style='color: green;'>Пользователь найден через функцию:</div>";
    echo "<pre>";
    print_r($user_from_function);
    echo "</pre>";
} else {
    echo "<div style='color: red;'>Функция getUserByTelegramId не нашла пользователя с Telegram ID " . htmlspecialchars($telegram_id) . "!</div>";
}

// Сравниваем результаты
if (isset($user_from_sql) && isset($user_from_function)) {
    if ($user_from_sql['id'] === $user_from_function['id']) {
        echo "<div style='color: green; font-weight: bold;'>Результаты SQL-запроса и функции совпадают!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>Результаты SQL-запроса и функции различаются!</div>";
    }
} elseif (isset($user_from_sql) && !$user_from_function) {
    echo "<div style='color: red; font-weight: bold;'>Пользователь существует в базе данных, но функция getUserByTelegramId его не находит!</div>";
    echo "<p>Рекомендуется проверить код функции.</p>";
}

// Отображаем исходный код функции
echo "<h3>Код функции getUserByTelegramId:</h3>";
$function_code = new ReflectionFunction('getUserByTelegramId');
$filename = $function_code->getFileName();
$start_line = $function_code->getStartLine() - 1;
$end_line = $function_code->getEndLine();
$length = $end_line - $start_line;

$source = file($filename);
$body = implode("", array_slice($source, $start_line, $length));

echo "<pre>";
highlight_string("<?php\n" . $body . "\n?>");
echo "</pre>";

// Проверим структуру таблицы users
echo "<h3>Структура таблицы users:</h3>";
$table_structure_sql = "DESCRIBE users";
$table_structure_result = mysqli_query($conn, $table_structure_sql);

if ($table_structure_result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th><th>Дополнительно</th></tr>";
    
    while ($row = mysqli_fetch_assoc($table_structure_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<div style='color: red;'>Ошибка при получении структуры таблицы: " . mysqli_error($conn) . "</div>";
}

// Показать первые 10 пользователей с telegram_id
echo "<h3>Список пользователей с telegram_id (первые 10):</h3>";
$users_sql = "SELECT id, fullname, email, telegram_id FROM users WHERE telegram_id IS NOT NULL LIMIT 10";
$users_result = mysqli_query($conn, $users_sql);

if ($users_result && mysqli_num_rows($users_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Имя</th><th>Email</th><th>Telegram ID</th></tr>";
    
    while ($user = mysqli_fetch_assoc($users_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['telegram_id']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<div style='color: orange;'>Нет пользователей с привязанным Telegram ID или произошла ошибка: " . mysqli_error($conn) . "</div>";
}

// Форма для тестирования функции с произвольным Telegram ID
echo "<h3>Тестирование с другим Telegram ID:</h3>";
echo "<form method='get'>";
echo "<input type='text' name='telegram_id' placeholder='Введите Telegram ID' required style='padding: 5px; width: 300px;'>";
echo "<button type='submit' style='padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; margin-left: 10px;'>Проверить</button>";
echo "</form>";

// Добавляем ссылки для навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='check_user_telegram.php' style='margin-right: 15px;'>Проверка привязки Telegram</a> | ";
echo "<a href='fix_telegram_function.php' style='margin: 0 15px;'>Исправить функцию</a> | ";
echo "<a href='telegram_diagnostics.php' style='margin-left: 15px;'>Вернуться к диагностике</a>";
echo "</div>"; 