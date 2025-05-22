<?php
/**
 * Конфигурация для Telegram-бота
 */

// Токен вашего бота
define('TELEGRAM_BOT_TOKEN', '7994982639:AAHFGWXmWqtPEO9ue8P7KchZxnB_a1Vdz5k');

// URL для установки вебхука
// Для локальной разработки используйте webhook.site:
// 1. Откройте https://webhook.site/ в браузере
// 2. Скопируйте ваш уникальный URL
// 3. Используйте этот URL в set_webhook.php для установки вебхука
// 4. После тестирования настройте постоянный URL для вашего сервера
define('TELEGRAM_WEBHOOK_URL', 'https://webhook.site/6bc3f81f-bea6-479e-93db-e21a62ab6db1');

// Имя пользователя бота (без @)
define('TELEGRAM_BOT_USERNAME', 'x64shopBot');

// Полный URL бота
define('TELEGRAM_BOT_URL', 'https://t.me/' . TELEGRAM_BOT_USERNAME);