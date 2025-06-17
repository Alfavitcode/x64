<?php
// Все существующие функции до getSubcategories
require_once 'db_config.php';

/**
 * Получение подкатегорий для заданной категории
 */
function getSubcategories($parent_id) {
    global $conn;
    
    // Если передано имя категории вместо ID, получаем ID
    if (!is_numeric($parent_id)) {
        $parent_id = getCategoryIdByName($parent_id);
    }
    
    if (!$parent_id) {
        return [];
    }
    
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM product p WHERE p.category = c.name) as count 
            FROM categories c 
            WHERE c.parent_id = ? 
            ORDER BY c.name ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategories = [];
    
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    return $subcategories;
}

/**
 * Получение популярных подкатегорий
 */
function getPopularSubcategories($category, $limit = 5) {
    global $conn;
    
    // Получаем ID категории
    $category_id = getCategoryIdByName($category);
    if (!$category_id) {
        return [];
    }
    
    // Получаем все подкатегории для данной категории
    return getSubcategories($category_id);
}

/**
 * Получение ID категории по имени
 */
function getCategoryIdByName($name) {
    global $conn;
    
    $sql = "SELECT id FROM categories WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    
    return null;
}

/**
 * Обновление таблицы товаров для поддержки подкатегорий
 */
function updateProductTableForSubcategories() {
    global $conn;
    
    // Проверяем, существует ли столбец subcategory_id
    $result = $conn->query("SHOW COLUMNS FROM product LIKE 'subcategory_id'");
    if ($result->num_rows === 0) {
        // Добавляем столбец subcategory_id
        $sql = "ALTER TABLE product ADD COLUMN subcategory_id INT NULL DEFAULT NULL";
        $conn->query($sql);
    }
}

// Вызываем функцию при подключении файла
updateProductTableForSubcategories();

/**
 * Удаление заказа
 */
function deleteOrder($order_id) {
    global $conn;
    
    // Начинаем транзакцию
    mysqli_begin_transaction($conn);
    
    try {
        // Получаем текущий статус заказа
        $status_query = "SELECT status FROM orders WHERE id = " . (int)$order_id;
        $status_result = mysqli_query($conn, $status_query);
        $status_row = mysqli_fetch_assoc($status_result);
        
        if ($status_row && ($status_row['status'] === 'completed' || $status_row['status'] === 'closed')) {
            return [
                'success' => false,
                'message' => 'Невозможно удалить заказ в статусе "' . 
                             ($status_row['status'] === 'completed' ? 'Выполнен' : 'Закрыт') . '"'
            ];
        }
        
        // Удаляем элементы заказа
        $delete_items_sql = "DELETE FROM order_items WHERE order_id = " . (int)$order_id;
        if (!mysqli_query($conn, $delete_items_sql)) {
            throw new Exception("Ошибка при удалении элементов заказа: " . mysqli_error($conn));
        }
        
        // Удаляем сам заказ
        $delete_order_sql = "DELETE FROM orders WHERE id = " . (int)$order_id;
        if (!mysqli_query($conn, $delete_order_sql)) {
            throw new Exception("Ошибка при удалении заказа: " . mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        
        return [
            'success' => true,
            'message' => 'Заказ #' . $order_id . ' успешно удален'
        ];
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
} 