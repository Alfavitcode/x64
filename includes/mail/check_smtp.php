<?php
/**
 * Скрипт для проверки доступности SMTP-серверов
 */

// Подключаем файл конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';

// Список настроек для проверки
$servers = [
    'yandex' => [
        'host' => 'smtp.yandex.ru',
        'port' => 465
    ],
    'gmail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587
    ],
    'mail' => [
        'host' => 'smtp.mail.ru',
        'port' => 465
    ]
];

// Функция для проверки соединения с SMTP-сервером
function checkSmtpConnection($host, $port, $timeout = 5) {
    $errno = 0;
    $errstr = '';
    
    // Для SSL соединений
    if ($port == 465) {
        $host = "ssl://" . $host;
    }
    
    echo "Пытаемся подключиться к $host:$port...<br>";
    
    // Попытка открыть сокет
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    
    if (!$socket) {
        echo "<span style='color: red;'>Ошибка соединения: $errstr ($errno)</span><br>";
        return false;
    } else {
        echo "<span style='color: green;'>Соединение успешно установлено!</span><br>";
        fclose($socket);
        return true;
    }
}

// Функция для проверки наличия необходимых PHP-расширений
function checkRequiredExtensions() {
    $required = ['openssl', 'mbstring', 'curl'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    return $missing;
}

// Выводим информацию о текущих настройках
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Проверка SMTP-серверов</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #333; }
        .section { margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Проверка настроек отправки писем</h1>
    
    <div class='section'>
        <h2>Текущие настройки</h2>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Выбранный сервис</td><td>{$mail_service}</td></tr>
            <tr><td>SMTP-сервер</td><td>" . MAIL_HOST . "</td></tr>
            <tr><td>Порт</td><td>" . MAIL_PORT . "</td></tr>
            <tr><td>Шифрование</td><td>" . MAIL_ENCRYPTION . "</td></tr>
            <tr><td>Имя пользователя</td><td>" . MAIL_USERNAME . "</td></tr>
            <tr><td>Email отправителя</td><td>" . MAIL_FROM_EMAIL . "</td></tr>
            <tr><td>Имя отправителя</td><td>" . MAIL_FROM_NAME . "</td></tr>
        </table>
    </div>";

// Проверяем наличие необходимых расширений PHP
$missing_extensions = checkRequiredExtensions();

echo "<div class='section'>
        <h2>Проверка PHP-расширений</h2>";

if (empty($missing_extensions)) {
    echo "<p class='success'>Все необходимые расширения PHP установлены.</p>";
} else {
    echo "<p class='error'>Отсутствуют следующие расширения PHP: " . implode(', ', $missing_extensions) . "</p>";
    echo "<p>Эти расширения необходимы для корректной работы отправки писем. Обратитесь к администратору хостинга для их установки.</p>";
}

echo "</div>";

// Проверяем соединение с SMTP-серверами
echo "<div class='section'>
        <h2>Проверка соединения с SMTP-серверами</h2>";

foreach ($servers as $server_name => $server_info) {
    echo "<h3>Проверка сервера $server_name</h3>";
    
    $result = checkSmtpConnection($server_info['host'], $server_info['port']);
    
    if ($result) {
        echo "<p>Сервер <strong>{$server_info['host']}</strong> доступен и принимает соединения на порту <strong>{$server_info['port']}</strong>.</p>";
    } else {
        echo "<p class='error'>Не удалось подключиться к серверу <strong>{$server_info['host']}</strong> на порту <strong>{$server_info['port']}</strong>.</p>";
        
        // Предлагаем возможные решения
        echo "<p>Возможные причины:</p>
              <ul>
                <li>Сервер временно недоступен</li>
                <li>Порт заблокирован файрволом хостинга</li>
                <li>Хостинг не разрешает исходящие соединения на этот порт</li>
              </ul>";
    }
    
    echo "<hr>";
}

echo "</div>";

// Информация о PHP-функции mail()
echo "<div class='section'>
        <h2>Проверка функции mail()</h2>";

if (function_exists('mail')) {
    echo "<p class='success'>Функция mail() доступна в PHP.</p>";
    
    // Проверяем настройки PHP для mail()
    $sendmail_path = ini_get('sendmail_path');
    echo "<p>Путь к sendmail: " . ($sendmail_path ? $sendmail_path : 'Не указан') . "</p>";
} else {
    echo "<p class='error'>Функция mail() недоступна в PHP. SimpleMailer не будет работать.</p>";
}

echo "</div>";

echo "<div class='section'>
        <h2>Что делать, если письма не отправляются?</h2>
        <ol>
            <li>Проверьте, что в настройках указаны правильные учетные данные (логин и пароль).</li>
            <li>Для Gmail и Яндекс требуется использовать специальный пароль приложения, а не обычный пароль от аккаунта.</li>
            <li>Убедитесь, что хостинг разрешает исходящие соединения на порты SMTP-серверов.</li>
            <li>Проверьте логи ошибок PHP для получения дополнительной информации.</li>
            <li>Попробуйте использовать другой почтовый сервис (Gmail, Яндекс, Mail.ru).</li>
            <li>Проверьте, что адрес электронной почты указан правильно и существует.</li>
        </ol>
      </div>";

echo "</body></html>"; 