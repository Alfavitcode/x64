<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

// Добавляем небольшую задержку для предотвращения слишком быстрых повторных запросов
usleep(100000); // 100 мс

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    
    // Проверка валидности данных
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Некорректный ID товара в корзине']);
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
        // Если товар уже удален, считаем операцию успешной
        $total_sum = 0;
        $total_count = 0;
        foreach ($cart_items as $item) {
            $total_sum += $item['subtotal'];
            $total_count++;
        }
        
        echo json_encode([
            'success' => true,
            'cart_total' => $total_sum,
            'cart_count' => $total_count,
            'message' => 'Товар уже был удален из корзины'
        ]);
        exit;
    }
    
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
        
        echo json_encode([
            'success' => true,
            'cart_total' => $total_sum,
            'cart_count' => $total_count,
            'message' => 'Товар успешно удален из корзины'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Ошибка при удалении товара из корзины'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
} 