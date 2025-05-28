<?php
// Информация о PHP для диагностики проблем с отправкой писем

// Безопасность: ограничение доступа по IP
$allowed_ips = ['127.0.0.1', '::1']; // localhost
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    // Для безопасности на рабочем сервере можно вывести только ограниченную информацию
    echo "<h1>PHP Информация</h1>";
    echo "<p>PHP версия: " . phpversion() . "</p>";
    
    echo "<h2>Проверка расширений для отправки писем</h2>";
    $extensions = ['openssl', 'mbstring', 'curl', 'sockets'];
    echo "<ul>";
    foreach ($extensions as $ext) {
        echo "<li>" . $ext . ": " . (extension_loaded($ext) ? 'Загружено' : 'НЕ загружено') . "</li>";
    }
    echo "</ul>";
    
    echo "<h2>Информация о путях</h2>";
    echo "<p>include_path: " . ini_get('include_path') . "</p>";
    echo "<p>Текущая директория: " . getcwd() . "</p>";
    echo "<p>Корневая директория сайта: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    
    echo "<h2>Наличие файлов PHPMailer</h2>";
    $phpmailer_files = [
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/PHPMailer.php',
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/SMTP.php',
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/Exception.php'
    ];
    
    echo "<ul>";
    foreach ($phpmailer_files as $file) {
        echo "<li>" . basename($file) . ": " . (file_exists($file) ? 'Существует' : 'НЕ существует') . "</li>";
    }
    echo "</ul>";
    
    exit;
}

// Полная информация для локального хоста
phpinfo(); 