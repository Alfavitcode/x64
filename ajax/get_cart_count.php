<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

// Отладочная информация
$debug = [
    'session_id' => session_id(),
    'user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
    'session_data' => $_SESSION,
    'timestamp' => date('Y-m-d H:i:s')
];

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

// Создаем таблицу корзины, если её не существует
createCartTableIfNotExists();

// Получаем количество товаров в корзине
$cart_count = getCartItemCount($session_id, $user_id);

// Добавляем отладочную информацию в ответ
echo json_encode([
    'success' => true,
    'cart_count' => $cart_count,
    'debug' => $debug
]); 