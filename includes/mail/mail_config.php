<?php
/**
 * Файл конфигурации для отправки электронных писем
 * 
 * Этот файл содержит настройки, необходимые для отправки писем через SMTP
 */

// Выберите почтовый сервис, указав его название: 'yandex', 'gmail' или 'mail'
$mail_service = 'yandex';

// Настройки для разных почтовых сервисов
$mail_configs = [
    // Настройки для Яндекс.Почты
    'yandex' => [
        'host' => 'smtp.yandex.ru',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'cyberpank99@yandex.ru',
        'password' => 'zrvuzfaccjrhrqmo', // Пароль для приложений
        'from_email' => 'cyberpank99@yandex.ru',
        'from_name' => 'X64Shop'
    ],
    
    // Настройки для Gmail
    'gmail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password', // Пароль для приложений
        'from_email' => 'your-email@gmail.com',
        'from_name' => 'X64Shop'
    ],
    
    // Настройки для Mail.ru
    'mail' => [
        'host' => 'smtp.mail.ru',
        'port' => 465,
        'encryption' => 'ssl',
        'username' => 'den.filia@mail.ru',
        'password' => 'itSACvbqYC1SGyB772xp', // Пароль для внешних приложений
        'from_email' => 'den.filia@mail.ru',
        'from_name' => 'X64Shop'
    ]
];

// Применяем настройки выбранного сервиса
$current_config = $mail_configs[$mail_service];

// Настройки SMTP-сервера
define('MAIL_HOST', $current_config['host']);
define('MAIL_PORT', $current_config['port']);
define('MAIL_USERNAME', $current_config['username']);
define('MAIL_PASSWORD', $current_config['password']);
define('MAIL_ENCRYPTION', $current_config['encryption']);

// Настройки отправителя
define('MAIL_FROM_EMAIL', $current_config['from_email']);
define('MAIL_FROM_NAME', $current_config['from_name']);

// Email администратора для получения уведомлений
define('ADMIN_EMAIL', 'cyberpank99@x64shop.ru');

// Контактная информация магазина для отображения в письмах
define('SHOP_PHONE', '+7 (962) 276-39-27');
define('SHOP_SUPPORT_EMAIL', 'support@x64shop.ru');
define('SHOP_WEBSITE', 'https://x64shop.ru');

/**
 * Инструкция по настройке:
 * 
 * 1. Для Яндекс.Почты:
 *    - Получите пароль для приложений в настройках безопасности Яндекс аккаунта
 *    - Измените настройки в секции 'yandex' выше
 * 
 * 2. Для Gmail:
 *    - Включите двухфакторную аутентификацию в настройках Google
 *    - Создайте пароль для приложения в разделе "Безопасность"
 *    - Измените настройки в секции 'gmail' выше
 * 
 * 3. Для Mail.ru:
 *    - Получите пароль для внешних приложений в настройках
 *    - Измените настройки в секции 'mail' выше
 * 
 * 4. Укажите правильный почтовый сервис в переменной $mail_service вверху файла
 */ 