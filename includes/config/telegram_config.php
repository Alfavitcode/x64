<?php
/**
 * Конфигурация для Telegram-бота
 */

// Токен вашего бота
define('TELEGRAM_BOT_TOKEN', '7994982639:AAHFGWXmWqtPEO9ue8P7KchZxnB_a1Vdz5k');

// URL для вебхука - путь к скрипту на вашем сервере
// Это должен быть полный URL к файлу telegram_webhook.php
define('TELEGRAM_WEBHOOK_URL', 'https://x64shop.ru/api/telegram_webhook.php');

// Имя пользователя бота (без @)
define('TELEGRAM_BOT_USERNAME', 'x64shopBot');

// Полный URL бота
define('TELEGRAM_BOT_URL', 'https://t.me/' . TELEGRAM_BOT_USERNAME);