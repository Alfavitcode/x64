<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    if ($cart_id > 0 && $quantity > 0) {
        $result = updateCartItemQuantity($cart_id, $quantity, $session_id, $user_id);
        
        if ($result['success']) {
            // Получаем обновленные данные о товаре в корзине
            $cart_items = getCartItems($user_id, $session_id);
            $updated_item = null;
            $total_sum = 0;
            $total_count = 0;
            
            foreach ($cart_items as $item) {
                if ($item['id'] == $cart_id) {
                    $updated_item = $item;
                }
                $total_sum += $item['subtotal'];
                $total_count++;
            }
            
            // Добавляем информацию о товаре и общей сумме в ответ
            $result['quantity'] = $updated_item ? $updated_item['quantity'] : $quantity;
            $result['subtotal'] = $updated_item ? $updated_item['subtotal'] : 0;
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