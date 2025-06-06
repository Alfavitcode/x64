<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        $result = addToCart($product_id, $quantity, $user_id, $session_id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Неверный метод']); 