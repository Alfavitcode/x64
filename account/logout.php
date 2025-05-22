<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Уничтожаем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на главную страницу
header("Location: /");
exit;
?> 