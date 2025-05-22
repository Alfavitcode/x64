<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    
    if ($cart_id > 0) {
        $result = removeFromCart($cart_id, $session_id, $user_id);
        
        if ($result['success']) {
            // Получаем обновленные данные корзины
            $cart_items = getCartItems($user_id, $session_id);
            $total_sum = 0;
            $total_count = 0;
            
            foreach ($cart_items as $item) {
                $total_sum += $item['subtotal'];
                $total_count++;
            }
            
            // Добавляем информацию об общей сумме и количестве в ответ
            $result['cart_total'] = $total_sum;
            $result['cart_count'] = $total_count;
        }
        
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Неверный метод']); 