<?php
// Настройки FTP - эти данные нужно заменить на реальные
$ftp_server = "37.140.192.181"; // Укажите адрес FTP сервера
$ftp_username = "u3067829_u306782";    // Укажите логин
$ftp_password = "B6B-fNU-b2a-AxP";   // Укажите пароль
$ftp_port = 21;                 // Стандартный порт FTP
$use_passive = true;            // Использовать пассивный режим

header('Content-Type: text/plain; charset=utf-8');

echo "=== Тест FTP-соединения ===\n\n";

// Устанавливаем увеличенный таймаут
echo "Устанавливаем таймаут 60 секунд...\n";
set_time_limit(60);

// Проверяем наличие FTP-функций
echo "Проверка наличия FTP-расширения в PHP...\n";
if (!function_exists('ftp_connect')) {
    die("ОШИБКА: FTP-функции не доступны в PHP. Расширение FTP не установлено.\n");
}

echo "FTP-расширение доступно.\n\n";

// Пытаемся установить соединение
echo "Подключение к FTP-серверу $ftp_server:$ftp_port...\n";
$start_time = microtime(true);
$conn_id = @ftp_connect($ftp_server, $ftp_port, 30);
$connect_time = round(microtime(true) - $start_time, 2);

if (!$conn_id) {
    die("ОШИБКА: Не удалось подключиться к FTP-серверу за $connect_time сек. Проверьте адрес сервера и порт.\n");
}

echo "Соединение установлено за $connect_time сек.\n\n";

// Пробуем авторизоваться
echo "Авторизация с логином '$ftp_username'...\n";
$start_time = microtime(true);
$login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
$login_time = round(microtime(true) - $start_time, 2);

if (!$login_result) {
    ftp_close($conn_id);
    die("ОШИБКА: Не удалось авторизоваться за $login_time сек. Проверьте логин и пароль.\n");
}

echo "Авторизация успешна за $login_time сек.\n\n";

// Устанавливаем пассивный режим, если требуется
if ($use_passive) {
    echo "Включение пассивного режима...\n";
    ftp_pasv($conn_id, true);
    echo "Пассивный режим включен.\n\n";
}

// Получаем текущую директорию
echo "Получение текущей директории...\n";
$current_dir = ftp_pwd($conn_id);
echo "Текущая директория: $current_dir\n\n";

// Получаем список файлов
echo "Получение списка файлов...\n";
$start_time = microtime(true);
$file_list = @ftp_nlist($conn_id, ".");
$list_time = round(microtime(true) - $start_time, 2);

if (!$file_list) {
    echo "ПРЕДУПРЕЖДЕНИЕ: Не удалось получить список файлов за $list_time сек. Пробуем другой режим...\n";
    
    // Пробуем с другим режимом
    ftp_pasv($conn_id, !$use_passive);
    echo "Режим изменен на " . ($use_passive ? "активный" : "пассивный") . "...\n";
    
    $start_time = microtime(true);
    $file_list = @ftp_nlist($conn_id, ".");
    $list_time = round(microtime(true) - $start_time, 2);
    
    if (!$file_list) {
        echo "ОШИБКА: Не удалось получить список файлов за $list_time сек даже после смены режима.\n\n";
    } else {
        echo "Список файлов получен за $list_time сек в " . ($use_passive ? "активном" : "пассивном") . " режиме.\n";
        echo "Количество файлов/папок в текущей директории: " . count($file_list) . "\n\n";
    }
} else {
    echo "Список файлов получен за $list_time сек.\n";
    echo "Количество файлов/папок в текущей директории: " . count($file_list) . "\n\n";
}

// Пробуем создать тестовый файл
echo "Создание тестового файла...\n";
$temp_file = tempnam(sys_get_temp_dir(), 'ftp_test');
$test_content = "Тестовое содержимое: " . date('Y-m-d H:i:s');
file_put_contents($temp_file, $test_content);
$file_size = filesize($temp_file);

echo "Создан тестовый файл размером $file_size байт.\n";
echo "Загрузка тестового файла на сервер...\n";

$start_time = microtime(true);
$upload_result = @ftp_put($conn_id, "test_upload.txt", $temp_file, FTP_ASCII);
$upload_time = round(microtime(true) - $start_time, 2);

if (!$upload_result) {
    echo "ОШИБКА: Не удалось загрузить файл за $upload_time сек. Проверьте права доступа.\n\n";
} else {
    echo "Файл успешно загружен за $upload_time сек.\n";
    $upload_speed = round($file_size / $upload_time / 1024, 2);
    echo "Скорость загрузки: $upload_speed KB/сек.\n\n";
    
    // Удаляем тестовый файл с сервера
    echo "Удаление тестового файла с сервера...\n";
    if (@ftp_delete($conn_id, "test_upload.txt")) {
        echo "Файл успешно удален.\n\n";
    } else {
        echo "ПРЕДУПРЕЖДЕНИЕ: Не удалось удалить тестовый файл.\n\n";
    }
}

// Удаляем локальный временный файл
unlink($temp_file);

// Проверка наличия SFTP-расширения
echo "Проверка поддержки SFTP...\n";
if (function_exists('ssh2_connect')) {
    echo "SFTP поддерживается (расширение SSH2 доступно).\n";
    echo "Рекомендация: Попробуйте использовать SFTP вместо FTP для GitHub Actions.\n\n";
} else {
    echo "SFTP не поддерживается (расширение SSH2 не установлено).\n";
    echo "Примечание: Это не влияет на текущий тест, но может быть полезно знать для GitHub Actions.\n\n";
}

// Закрываем соединение
echo "Закрытие соединения...\n";
ftp_close($conn_id);
echo "Соединение закрыто.\n\n";

// Проверка GitHub Actions конфигурации
echo "=== Рекомендации для GitHub Actions ===\n\n";
echo "1. Используйте увеличенный таймаут:\n";
echo "   timeout: 120000  # 2 минуты\n\n";

echo "2. Используйте пассивный режим:\n";
echo "   passive: true\n\n";

echo "3. Если FTP не работает, попробуйте SFTP (если поддерживается хостингом):\n";
echo "   protocol: sftp\n";
echo "   port: 22\n\n";

echo "4. Добавьте в исключения:\n";
echo "   exclude: |\n";
echo "     **/.git*\n";
echo "     **/.git*/**\n";
echo "     **/node_modules/**\n\n";

echo "=== Тест завершен ===\n"; 