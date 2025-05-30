<?php
// Настройки SFTP - эти данные нужно заменить на реальные
$sftp_server = "ваш_сервер";       // Укажите адрес SFTP сервера
$sftp_username = "ваш_логин";      // Укажите логин
$sftp_password = "ваш_пароль";     // Укажите пароль
$sftp_port = 22;                   // Стандартный порт SFTP (SSH)

header('Content-Type: text/plain; charset=utf-8');

echo "=== Тест SFTP-соединения ===\n\n";

// Устанавливаем увеличенный таймаут
echo "Устанавливаем таймаут 60 секунд...\n";
set_time_limit(60);

// Проверяем наличие SSH2-расширения
echo "Проверка наличия SSH2-расширения в PHP...\n";
if (!function_exists('ssh2_connect')) {
    die("ОШИБКА: SFTP-функции не доступны в PHP. Расширение SSH2 не установлено.\n" .
        "Для GitHub Actions это не проблема, так как там есть встроенная поддержка SFTP.\n" .
        "Для локальной проверки установите расширение ssh2 для PHP или используйте другой SFTP-клиент.\n");
}

echo "SSH2-расширение доступно.\n\n";

// Пытаемся установить SSH-соединение
echo "Подключение к SFTP-серверу $sftp_server:$sftp_port...\n";
$start_time = microtime(true);
$connection = @ssh2_connect($sftp_server, $sftp_port);
$connect_time = round(microtime(true) - $start_time, 2);

if (!$connection) {
    die("ОШИБКА: Не удалось подключиться к SFTP-серверу за $connect_time сек. Проверьте адрес сервера и порт.\n");
}

echo "SSH-соединение установлено за $connect_time сек.\n\n";

// Пробуем авторизоваться
echo "Авторизация с логином '$sftp_username'...\n";
$start_time = microtime(true);
$auth_result = @ssh2_auth_password($connection, $sftp_username, $sftp_password);
$login_time = round(microtime(true) - $start_time, 2);

if (!$auth_result) {
    die("ОШИБКА: Не удалось авторизоваться за $login_time сек. Проверьте логин и пароль.\n");
}

echo "Авторизация успешна за $login_time сек.\n\n";

// Инициализируем SFTP-сессию
echo "Инициализация SFTP-сессии...\n";
$sftp = @ssh2_sftp($connection);

if (!$sftp) {
    die("ОШИБКА: Не удалось инициализировать SFTP-сессию. Возможно, SFTP не поддерживается на сервере.\n");
}

echo "SFTP-сессия инициализирована.\n\n";

// Получаем текущую директорию
echo "Получение списка файлов в корневой директории...\n";
$dir_path = "ssh2.sftp://$sftp/";
$start_time = microtime(true);
$handle = @opendir($dir_path);
$list_time = round(microtime(true) - $start_time, 2);

if (!$handle) {
    echo "ОШИБКА: Не удалось открыть директорию за $list_time сек. Проверьте права доступа.\n\n";
} else {
    echo "Директория открыта за $list_time сек.\n";
    $files = [];
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $files[] = $entry;
        }
    }
    closedir($handle);
    
    echo "Количество файлов/папок в директории: " . count($files) . "\n";
    echo "Первые 5 файлов/папок (если есть):\n";
    $count = 0;
    foreach ($files as $file) {
        if ($count >= 5) break;
        echo "- $file\n";
        $count++;
    }
    echo "\n";
}

// Пробуем создать тестовый файл
echo "Создание тестового файла...\n";
$temp_file = tempnam(sys_get_temp_dir(), 'sftp_test');
$test_content = "Тестовое содержимое SFTP: " . date('Y-m-d H:i:s');
file_put_contents($temp_file, $test_content);
$file_size = filesize($temp_file);

echo "Создан тестовый файл размером $file_size байт.\n";
echo "Загрузка тестового файла на сервер...\n";

$remote_file = "ssh2.sftp://$sftp/test_sftp_upload.txt";
$start_time = microtime(true);
$upload_result = @file_put_contents($remote_file, $test_content);
$upload_time = round(microtime(true) - $start_time, 2);

if ($upload_result === false) {
    echo "ОШИБКА: Не удалось загрузить файл за $upload_time сек. Проверьте права доступа.\n\n";
} else {
    echo "Файл успешно загружен за $upload_time сек.\n";
    $upload_speed = round($file_size / $upload_time / 1024, 2);
    echo "Скорость загрузки: $upload_speed KB/сек.\n\n";
    
    // Удаляем тестовый файл с сервера
    echo "Удаление тестового файла с сервера...\n";
    if (@ssh2_sftp_unlink($sftp, "/test_sftp_upload.txt")) {
        echo "Файл успешно удален.\n\n";
    } else {
        echo "ПРЕДУПРЕЖДЕНИЕ: Не удалось удалить тестовый файл.\n\n";
    }
}

// Удаляем локальный временный файл
unlink($temp_file);

// Закрываем соединение - в PHP нет прямого метода для закрытия SSH2-соединения
// Соединение будет закрыто автоматически в конце скрипта
echo "Закрытие соединения...\n";
echo "Примечание: PHP не имеет прямого метода для закрытия SSH2-соединения.\n";
echo "Соединение будет закрыто автоматически при завершении скрипта.\n\n";

// Рекомендации для GitHub Actions
echo "=== Рекомендации для GitHub Actions с SFTP ===\n\n";
echo "1. Используйте SFTP вместо FTP:\n";
echo "   name: Deploy via SFTP\n";
echo "   on:\n";
echo "     push:\n";
echo "       branches: [ main ]\n\n";
echo "   jobs:\n";
echo "     deploy:\n";
echo "       runs-on: ubuntu-latest\n";
echo "       steps:\n";
echo "         - name: Checkout code\n";
echo "           uses: actions/checkout@v3\n\n";
echo "         - name: Deploy using SFTP\n";
echo "           uses: wlixcc/SFTP-Deploy-Action@v1.2.4\n";
echo "           with:\n";
echo "             username: \${{ secrets.FTP_USERNAME }}\n";
echo "             server: \${{ secrets.FTP_SERVER }}\n";
echo "             port: 22\n";
echo "             password: \${{ secrets.FTP_PASSWORD }}\n";
echo "             local_path: ./\n";
echo "             remote_path: /path/to/your/website/\n";
echo "             delete_remote_files: false\n";
echo "             exclude: \".git*,.github*,node_modules,vendor\"\n\n";

echo "2. Альтернативно, используйте SSH-ключи для более безопасного доступа:\n";
echo "   with:\n";
echo "     username: \${{ secrets.FTP_USERNAME }}\n";
echo "     server: \${{ secrets.FTP_SERVER }}\n";
echo "     port: 22\n";
echo "     ssh_private_key: \${{ secrets.SSH_PRIVATE_KEY }}\n";
echo "     local_path: ./\n";
echo "     remote_path: /path/to/your/website/\n\n";

echo "=== Тест завершен ===\n"; 