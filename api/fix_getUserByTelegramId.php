<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

echo "<h2>Исправление функции getUserByTelegramId</h2>";

// Проверяем наличие функции
if (!function_exists('getUserByTelegramId')) {
    echo "<div style='color: red; font-weight: bold;'>Функция getUserByTelegramId не найдена в подключенных файлах!</div>";
    exit;
}

// Создаем улучшенную версию функции
$improved_function_code = <<<'EOD'
/**
 * Получает пользователя по Telegram ID
 * Улучшенная версия: поддерживает поиск по строковому и числовому формату ID
 * 
 * @param string|int $telegram_id ID пользователя в Telegram
 * @return array|null Данные пользователя или null, если пользователь не найден
 */
function getUserByTelegramId($telegram_id) {
    global $conn;
    
    // Логирование для отладки (можно закомментировать после исправления проблемы)
    $log_file = __DIR__ . '/telegram_log.txt';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Поиск пользователя по Telegram ID: " . $telegram_id . "\n", FILE_APPEND);
    
    // Попытка поиска по строковому значению
    $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
            FROM users 
            WHERE telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Пользователь найден по строковому ID: " . $user['id'] . ", " . $user['fullname'] . "\n", FILE_APPEND);
        return $user;
    }
    
    // Если не найдено, пробуем искать по числовому значению
    if (is_numeric($telegram_id)) {
        $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
                FROM users 
                WHERE telegram_id = " . (int)$telegram_id;
        
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Пользователь найден по числовому ID: " . $user['id'] . ", " . $user['fullname'] . "\n", FILE_APPEND);
            return $user;
        }
    }
    
    // Если поиск по строковому и числовому ID не дал результатов, попробуем искать по LIKE
    $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
            FROM users 
            WHERE telegram_id LIKE '%" . mysqli_real_escape_string($conn, $telegram_id) . "%'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Пользователь найден по частичному совпадению ID: " . $user['id'] . ", " . $user['fullname'] . "\n", FILE_APPEND);
        return $user;
    }
    
    // Пользователь не найден
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Пользователь с Telegram ID " . $telegram_id . " не найден\n", FILE_APPEND);
    return null;
}
EOD;

// Получаем информацию о текущей функции
$reflection = new ReflectionFunction('getUserByTelegramId');
$filename = $reflection->getFileName();
$start_line = $reflection->getStartLine() - 1;
$end_line = $reflection->getEndLine();
$length = $end_line - $start_line;

// Получаем текущий код функции
$source = file($filename);
$current_code = implode("", array_slice($source, $start_line, $length));

echo "<h3>Текущий код функции:</h3>";
echo "<pre>";
highlight_string("<?php\n" . $current_code . "\n?>");
echo "</pre>";

echo "<h3>Улучшенный код функции:</h3>";
echo "<pre>";
highlight_string("<?php\n" . $improved_function_code . "\n?>");
echo "</pre>";

// Спрашиваем пользователя, хочет ли он заменить функцию
if (isset($_GET['replace']) && $_GET['replace'] == 1) {
    // Заменяем функцию в файле
    $file_content = file_get_contents($filename);
    
    // Ищем текущую функцию по шаблону
    $pattern = '/function\s+getUserByTelegramId\s*\(\s*\$telegram_id\s*\)\s*\{.*?return\s+null;\s*\}/s';
    $new_content = preg_replace($pattern, $improved_function_code, $file_content);
    
    if ($new_content !== $file_content) {
        // Сохраняем изменения
        if (file_put_contents($filename, $new_content)) {
            echo "<div style='color: green; font-weight: bold; margin-top: 20px;'>Функция успешно заменена!</div>";
            echo "<p>Теперь функция поддерживает поиск по строковому и числовому формату Telegram ID.</p>";
        } else {
            echo "<div style='color: red; font-weight: bold; margin-top: 20px;'>Ошибка при сохранении файла!</div>";
        }
    } else {
        echo "<div style='color: orange; font-weight: bold; margin-top: 20px;'>Не удалось найти и заменить функцию в файле!</div>";
        
        // Предлагаем добавить функцию в конец файла
        echo "<p>Попробуем добавить функцию в конец файла...</p>";
        
        // Добавляем функцию в конец файла перед закрывающим тегом PHP
        $closing_tag_pos = strrpos($file_content, '?>');
        if ($closing_tag_pos !== false) {
            $new_content = substr($file_content, 0, $closing_tag_pos) . "\n\n" . $improved_function_code . "\n\n?>";
        } else {
            $new_content = $file_content . "\n\n" . $improved_function_code . "\n";
        }
        
        if (file_put_contents($filename, $new_content)) {
            echo "<div style='color: green; font-weight: bold;'>Функция успешно добавлена в конец файла!</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>Ошибка при добавлении функции в файл!</div>";
        }
    }
    
    // Добавляем ссылку для тестирования
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='debug_getUserByTelegramId.php' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;'>Тестировать исправленную функцию</a>";
    echo "</div>";
} else {
    // Показываем кнопку для замены функции
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='?replace=1' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;' onclick='return confirm(\"Вы уверены, что хотите заменить функцию getUserByTelegramId?\")'>Заменить функцию на улучшенную версию</a>";
    echo "</div>";
}

// Добавляем ссылки для навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='debug_getUserByTelegramId.php' style='margin-right: 15px;'>Диагностика функции</a> | ";
echo "<a href='check_user_telegram.php' style='margin: 0 15px;'>Проверка привязки Telegram</a> | ";
echo "<a href='telegram_diagnostics.php' style='margin-left: 15px;'>Вернуться к диагностике</a>";
echo "</div>"; 