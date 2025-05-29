<?php
// Подключаем необходимые файлы
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/config/telegram_config.php';

echo "<h2>Проверка и исправление функции getUserByTelegramId</h2>";

// Проверяем наличие функции
if (!function_exists('getUserByTelegramId')) {
    echo "<div style='color: red; font-weight: bold;'>Функция getUserByTelegramId не найдена в подключенных файлах!</div>";
    
    // Добавляем функцию
    echo "<div>Добавляем функцию getUserByTelegramId...</div>";
    
    $function_code = <<<'EOD'
/**
 * Получает пользователя по Telegram ID
 * 
 * @param string $telegram_id ID пользователя в Telegram
 * @return array|null Данные пользователя или null, если пользователь не найден
 */
function getUserByTelegramId($telegram_id) {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
            FROM users 
            WHERE telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}
EOD;
    
    // Добавляем функцию в файл db_functions.php
    $db_functions_file = '../includes/config/db_functions.php';
    $db_functions_content = file_get_contents($db_functions_file);
    
    // Добавляем функцию в конец файла перед закрывающим тегом PHP
    $closing_tag_pos = strrpos($db_functions_content, '?>');
    if ($closing_tag_pos !== false) {
        $new_content = substr($db_functions_content, 0, $closing_tag_pos) . "\n\n" . $function_code . "\n\n?>";
    } else {
        $new_content = $db_functions_content . "\n\n" . $function_code . "\n";
    }
    
    if (file_put_contents($db_functions_file, $new_content)) {
        echo "<div style='color: green;'>Функция getUserByTelegramId успешно добавлена в файл db_functions.php.</div>";
        echo "<div>Перезагружаем страницу для применения изменений...</div>";
        echo "<meta http-equiv='refresh' content='2;url=fix_telegram_function.php'>";
        exit;
    } else {
        echo "<div style='color: red;'>Не удалось добавить функцию в файл db_functions.php!</div>";
        exit;
    }
}

// Если функция существует, проверяем её работоспособность
echo "<div style='color: green;'>Функция getUserByTelegramId найдена.</div>";

// Проверяем наличие столбца telegram_id в таблице users
$table_check_sql = "SHOW COLUMNS FROM users LIKE 'telegram_id'";
$table_check_result = mysqli_query($conn, $table_check_sql);

if (!$table_check_result || mysqli_num_rows($table_check_result) == 0) {
    echo "<div style='color: red; font-weight: bold;'>Столбец telegram_id отсутствует в таблице users!</div>";
    
    // Добавляем столбец в таблицу
    if (isset($_GET['add_column']) && $_GET['add_column'] == 1) {
        $add_column_sql = "ALTER TABLE users 
                          ADD COLUMN telegram_id VARCHAR(50) NULL,
                          ADD COLUMN telegram_username VARCHAR(100) NULL,
                          ADD COLUMN telegram_verification_code VARCHAR(10) NULL,
                          ADD COLUMN telegram_code_expires DATETIME NULL";
        
        if (mysqli_query($conn, $add_column_sql)) {
            echo "<div style='color: green;'>Столбцы для Telegram успешно добавлены в таблицу users!</div>";
            echo "<meta http-equiv='refresh' content='2;url=fix_telegram_function.php'>";
        } else {
            echo "<div style='color: red;'>Ошибка при добавлении столбцов: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<p>Необходимо добавить столбцы для работы с Telegram в таблицу users:</p>";
        echo "<pre>ALTER TABLE users 
      ADD COLUMN telegram_id VARCHAR(50) NULL,
      ADD COLUMN telegram_username VARCHAR(100) NULL,
      ADD COLUMN telegram_verification_code VARCHAR(10) NULL,
      ADD COLUMN telegram_code_expires DATETIME NULL</pre>";
        
        echo "<p><a href='?add_column=1' style='padding: 5px 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;'>Добавить столбцы</a></p>";
    }
    
    exit;
}

// Проверяем таблицу telegram_verification_codes
$check_table_sql = "SHOW TABLES LIKE 'telegram_verification_codes'";
$check_table_result = mysqli_query($conn, $check_table_sql);

if (!$check_table_result || mysqli_num_rows($check_table_result) == 0) {
    echo "<div style='color: red; font-weight: bold;'>Таблица telegram_verification_codes не существует!</div>";
    
    // Создаем таблицу
    if (isset($_GET['create_table']) && $_GET['create_table'] == 1) {
        $create_table_sql = "CREATE TABLE telegram_verification_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chat_id VARCHAR(50) NOT NULL,
            username VARCHAR(100) NULL,
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            code VARCHAR(10) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (chat_id)
        )";
        
        if (mysqli_query($conn, $create_table_sql)) {
            echo "<div style='color: green;'>Таблица telegram_verification_codes успешно создана!</div>";
            echo "<meta http-equiv='refresh' content='2;url=fix_telegram_function.php'>";
        } else {
            echo "<div style='color: red;'>Ошибка при создании таблицы: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<p>Необходимо создать таблицу telegram_verification_codes:</p>";
        echo "<pre>CREATE TABLE telegram_verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id VARCHAR(50) NOT NULL,
    username VARCHAR(100) NULL,
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    code VARCHAR(10) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (chat_id)
)</pre>";
        
        echo "<p><a href='?create_table=1' style='padding: 5px 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;'>Создать таблицу</a></p>";
    }
}

// Тестовый запрос к функции
echo "<h3>Тестирование функции getUserByTelegramId</h3>";

// Проверяем наличие пользователей с привязанным Telegram
$sql = "SELECT COUNT(*) as count FROM users WHERE telegram_id IS NOT NULL";
$result = mysqli_query($conn, $sql);
$count = 0;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $count = (int)$row['count'];
}

if ($count > 0) {
    echo "<div>Найдено пользователей с привязанным Telegram: $count</div>";
    
    // Получаем одного пользователя для теста
    $sql = "SELECT id, fullname, telegram_id FROM users WHERE telegram_id IS NOT NULL LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        echo "<div>Тестируем функцию для пользователя: " . htmlspecialchars($user['fullname']) . " (ID: " . $user['id'] . ", Telegram ID: " . $user['telegram_id'] . ")</div>";
        
        // Вызываем функцию
        $test_user = getUserByTelegramId($user['telegram_id']);
        
        if ($test_user) {
            echo "<div style='color: green; margin-top: 10px;'>Тест пройден успешно! Функция вернула данные пользователя:</div>";
            echo "<pre>" . print_r($test_user, true) . "</pre>";
        } else {
            echo "<div style='color: red; margin-top: 10px;'>Тест не пройден! Функция не вернула данные пользователя.</div>";
            
            // Выполняем запрос вручную для проверки
            $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
                    FROM users 
                    WHERE telegram_id = '" . mysqli_real_escape_string($conn, $user['telegram_id']) . "'";
            
            $manual_result = mysqli_query($conn, $sql);
            
            if ($manual_result && mysqli_num_rows($manual_result) > 0) {
                $manual_user = mysqli_fetch_assoc($manual_result);
                echo "<div>Ручной запрос вернул данные пользователя:</div>";
                echo "<pre>" . print_r($manual_user, true) . "</pre>";
                
                echo "<div style='color: red;'>Проблема в функции getUserByTelegramId!</div>";
                
                // Исправляем функцию
                $fixed_function_code = <<<'EOD'
/**
 * Получает пользователя по Telegram ID
 * 
 * @param string $telegram_id ID пользователя в Telegram
 * @return array|null Данные пользователя или null, если пользователь не найден
 */
function getUserByTelegramId($telegram_id) {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
            FROM users 
            WHERE telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}
EOD;
                
                // Заменяем функцию в файле
                $db_functions_file = '../includes/config/db_functions.php';
                $db_functions_content = file_get_contents($db_functions_file);
                
                // Находим старую функцию и заменяем её
                $pattern = '/function\s+getUserByTelegramId\s*\(\s*\$telegram_id\s*\)\s*\{.*?return\s+null;\s*\}/s';
                $new_content = preg_replace($pattern, $fixed_function_code, $db_functions_content);
                
                if ($new_content !== $db_functions_content) {
                    if (file_put_contents($db_functions_file, $new_content)) {
                        echo "<div style='color: green;'>Функция getUserByTelegramId успешно исправлена!</div>";
                        echo "<div>Перезагружаем страницу для применения изменений...</div>";
                        echo "<meta http-equiv='refresh' content='2;url=fix_telegram_function.php'>";
                    } else {
                        echo "<div style='color: red;'>Не удалось исправить функцию в файле db_functions.php!</div>";
                    }
                } else {
                    echo "<div style='color: orange;'>Не удалось найти функцию getUserByTelegramId для замены!</div>";
                }
            } else {
                echo "<div style='color: red;'>Ручной запрос не вернул данные пользователя! Ошибка: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        echo "<div style='color: red;'>Не удалось получить пользователя для теста!</div>";
    }
} else {
    echo "<div style='color: orange;'>Нет пользователей с привязанным Telegram для тестирования.</div>";
    echo "<div>Вы можете привязать Telegram вручную через скрипт <a href='check_user_telegram.php'>check_user_telegram.php</a></div>";
}

// Добавляем форму для тестирования функции с произвольным Telegram ID
echo "<h3 style='margin-top: 30px;'>Тестирование функции с произвольным Telegram ID</h3>";

if (isset($_POST['test_telegram_id'])) {
    $test_telegram_id = $_POST['telegram_id'];
    
    if (!empty($test_telegram_id)) {
        echo "<div>Тестируем функцию для Telegram ID: " . htmlspecialchars($test_telegram_id) . "</div>";
        
        // Вызываем функцию
        $test_user = getUserByTelegramId($test_telegram_id);
        
        if ($test_user) {
            echo "<div style='color: green; margin-top: 10px;'>Функция вернула данные пользователя:</div>";
            echo "<pre>" . print_r($test_user, true) . "</pre>";
        } else {
            echo "<div style='color: orange; margin-top: 10px;'>Функция не вернула данные пользователя (null). Пользователь с таким Telegram ID не найден.</div>";
        }
    }
}

echo "<form method='post'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='telegram_id' style='display: block; margin-bottom: 5px;'>Telegram ID:</label>";
echo "<input type='text' id='telegram_id' name='telegram_id' required style='padding: 5px; width: 300px;'>";
echo "</div>";
echo "<button type='submit' name='test_telegram_id' style='padding: 5px 10px; background-color: #2196F3; color: white; border: none; border-radius: 3px;'>Проверить</button>";
echo "</form>";

// Проверяем функцию проверки заказа в telegram_webhook.php
echo "<h3 style='margin-top: 30px;'>Проверка обработчика заказов в webhook</h3>";

$webhook_file = __DIR__ . '/telegram_webhook.php';
if (file_exists($webhook_file)) {
    echo "<div style='color: green;'>Файл telegram_webhook.php найден.</div>";
    
    // Проверяем, что файл содержит обработку заказов
    $webhook_content = file_get_contents($webhook_file);
    
    if (strpos($webhook_content, 'confirm_order_') !== false && strpos($webhook_content, '/accept') !== false) {
        echo "<div style='color: green;'>Обработчик подтверждения заказов найден в файле.</div>";
    } else {
        echo "<div style='color: red;'>Обработчик подтверждения заказов не найден в файле!</div>";
    }
} else {
    echo "<div style='color: red;'>Файл telegram_webhook.php не найден!</div>";
}

// Добавляем ссылки для навигации
echo "<div style='margin-top: 30px;'>";
echo "<a href='check_user_telegram.php' style='margin-right: 15px;'>Проверка привязки Telegram</a> | ";
echo "<a href='test_telegram_message.php' style='margin: 0 15px;'>Отправка тестовых сообщений</a> | ";
echo "<a href='check_paths.php' style='margin-left: 15px;'>Проверка настроек Telegram</a>";
echo "</div>"; 