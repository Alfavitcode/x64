<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

// Добавляем задержку для предотвращения слишком быстрых повторных запросов
usleep(200000); // 200 мс

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    // Проверка валидности данных
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Некорректный ID товара в корзине']);
        exit;
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Количество товара должно быть больше нуля']);
        exit;
    }
    
    // Проверка существования товара в корзине
    $cart_items = getCartItems($user_id, $session_id);
    $item_exists = false;
    foreach ($cart_items as $item) {
        if ($item['id'] == $cart_id) {
            $item_exists = true;
            break;
        }
    }
    
    if (!$item_exists) {
        echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине']);
        exit;
    }
    
    // Обновляем количество товара
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
    exit;
}

echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']); 