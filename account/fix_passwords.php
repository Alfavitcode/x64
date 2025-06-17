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

echo "Начало процесса исправления паролей...\n\n";

// 1. Проверяем соединение с базой данных
echo "Проверка соединения с базой данных: ";
if ($conn) {
    echo "Успешно\n";
} else {
    echo "Ошибка: " . mysqli_connect_error() . "\n";
    exit;
}

// 2. Проверяем наличие таблицы users
$result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
echo "Проверка таблицы users: ";
if (mysqli_num_rows($result) > 0) {
    echo "Таблица существует\n";
} else {
    echo "Таблица не найдена\n";
    exit;
}

// 3. Получаем список всех пользователей
$result = mysqli_query($conn, "SELECT id, login, password FROM users");
$total_users = mysqli_num_rows($result);
echo "Найдено пользователей: $total_users\n\n";

// 4. Проверяем возможные проблемы с хешированием
$fixed_users = 0;
$problems_found = 0;
$already_ok = 0;

while ($user = mysqli_fetch_assoc($result)) {
    $user_id = $user['id'];
    $login = $user['login'];
    $password_hash = $user['password'];
    
    echo "Проверка пользователя ID: $user_id, Логин: $login\n";
    
    // Проверяем, является ли хеш действительным хешем bcrypt
    $hash_info = password_get_info($password_hash);
    
    if ($hash_info['algo'] === 0) {
        echo "  - Обнаружена проблема: хеш не распознан как bcrypt\n";
        $problems_found++;
        
        // Проверяем, не является ли пароль уже хешем MD5 или другим форматом
        if (strlen($password_hash) === 32 && ctype_xdigit($password_hash)) {
            echo "  - Похоже, что пароль хранится как MD5 хеш\n";
            
            // Если указан пароль для сброса
            if (isset($_GET['reset']) && $_GET['reset'] === 'yes') {
                // Создаем новый пароль
                $new_password = substr(md5(uniqid(rand(), true)), 0, 8);
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Обновляем пароль в базе
                $update_sql = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "' WHERE id = " . (int)$user_id;
                if (mysqli_query($conn, $update_sql)) {
                    echo "  - Пароль сброшен на: $new_password\n";
                    $fixed_users++;
                } else {
                    echo "  - Ошибка при сбросе пароля: " . mysqli_error($conn) . "\n";
                }
            } else {
                echo "  - Для сброса пароля добавьте параметр &reset=yes\n";
            }
        } else {
            echo "  - Формат хеша не определен\n";
            
            // Если указан пароль для сброса
            if (isset($_GET['reset']) && $_GET['reset'] === 'yes') {
                // Создаем новый пароль
                $new_password = substr(md5(uniqid(rand(), true)), 0, 8);
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Обновляем пароль в базе
                $update_sql = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "' WHERE id = " . (int)$user_id;
                if (mysqli_query($conn, $update_sql)) {
                    echo "  - Пароль сброшен на: $new_password\n";
                    $fixed_users++;
                } else {
                    echo "  - Ошибка при сбросе пароля: " . mysqli_error($conn) . "\n";
                }
            } else {
                echo "  - Для сброса пароля добавьте параметр &reset=yes\n";
            }
        }
    } else if (password_needs_rehash($password_hash, PASSWORD_DEFAULT)) {
        echo "  - Хеш требует обновления (устаревший алгоритм)\n";
        $problems_found++;
        
        // Если указан rehash
        if (isset($_GET['rehash']) && $_GET['rehash'] === 'yes') {
            echo "  - Обновление хеша невозможно без исходного пароля\n";
            
            // Если указан пароль для сброса
            if (isset($_GET['reset']) && $_GET['reset'] === 'yes') {
                // Создаем новый пароль
                $new_password = substr(md5(uniqid(rand(), true)), 0, 8);
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Обновляем пароль в базе
                $update_sql = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "' WHERE id = " . (int)$user_id;
                if (mysqli_query($conn, $update_sql)) {
                    echo "  - Пароль сброшен на: $new_password\n";
                    $fixed_users++;
                } else {
                    echo "  - Ошибка при сбросе пароля: " . mysqli_error($conn) . "\n";
                }
            } else {
                echo "  - Для сброса пароля добавьте параметр &reset=yes\n";
            }
        } else {
            echo "  - Для попытки обновления хеша добавьте параметр &rehash=yes\n";
        }
    } else {
        echo "  - Хеш в порядке, проблем не обнаружено\n";
        $already_ok++;
    }
    
    echo "\n";
}

echo "Итоги проверки:\n";
echo "- Всего пользователей: $total_users\n";
echo "- Пользователей без проблем: $already_ok\n";
echo "- Обнаружено проблем: $problems_found\n";
echo "- Исправлено пользователей: $fixed_users\n";

if ($problems_found > 0 && $fixed_users === 0) {
    echo "\nДля исправления проблем добавьте параметр &reset=yes\n";
}

echo "\n--- Конец процесса ---\n";
?> 