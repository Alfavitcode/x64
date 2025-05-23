<?php
// Включаем вывод всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
header('Content-Type: application/json');

// Добавляем небольшую задержку для предотвращения слишком быстрых повторных запросов
usleep(100000); // 100 мс

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

// Отладочный вывод
$debug = [
    'post' => $_POST,
    'session_id' => $session_id,
    'user_id' => $user_id
];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        
        // Проверка валидности данных
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некорректный ID товара в корзине']);
            exit;
        }
        
        if ($quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Количество товара должно быть больше 0']);
            exit;
        }
        
        // Получаем текущий элемент корзины для проверки
        $currentItem = getCartItemById($cart_id, $session_id, $user_id);
        if (!$currentItem) {
            echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине', 'debug' => $debug]);
            exit;
        }
        
        // Получаем информацию о товаре для проверки наличия на складе
        $product = getProductById($currentItem['product_id']);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Товар не найден', 'debug' => $debug]);
            exit;
        }
        
        // Проверка максимального количества (не больше, чем есть на складе)
        if ($quantity > $product['stock']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Недостаточно товара на складе. Доступно: ' . $product['stock'],
                'available_quantity' => $product['stock']
            ]);
            exit;
        }
        
        // Проверяем, не изменилось ли количество с момента отправки запроса
        if ($currentItem['quantity'] == $quantity) {
            // Если количество не изменилось, просто возвращаем текущие данные
            $cart_items = getCartItems($user_id, $session_id);
            $total_sum = 0;
            $total_count = 0;
            
            foreach ($cart_items as $item) {
                $total_sum += $item['subtotal'];
                $total_count++;
            }
            
            echo json_encode([
                'success' => true,
                'quantity' => $currentItem['quantity'],
                'subtotal' => $currentItem['subtotal'],
                'cart_total' => $total_sum,
                'cart_count' => $total_count,
                'message' => 'Количество товара не изменилось'
            ]);
            exit;
        }
        
        $result = updateCartItemQuantity($cart_id, $quantity, $session_id, $user_id);
        
        if ($result['success']) {
            // Получаем обновленные данные о товаре в корзине
            $cart_items = getCartItems($user_id, $session_id);
            $total_sum = 0;
            $total_count = 0;
            $updated_item = null;
            
            foreach ($cart_items as $item) {
                $total_sum += $item['subtotal'];
                $total_count++;
                
                if ($item['id'] == $cart_id) {
                    $updated_item = $item;
                }
            }
            
            echo json_encode([
                'success' => true,
                'quantity' => $updated_item ? $updated_item['quantity'] : $quantity,
                'subtotal' => $updated_item ? $updated_item['subtotal'] : 0,
                'cart_total' => $total_sum,
                'cart_count' => $total_count,
                'message' => 'Количество товара успешно обновлено'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'Ошибка при обновлении количества товара',
                'debug' => $debug
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка: ' . $e->getMessage(),
        'debug' => $debug
    ]);
} 