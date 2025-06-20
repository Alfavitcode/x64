<?php
// Подключаем файл конфигурации
require_once 'db_config.php';

/**
 * Получение всех товаров из базы данных
 * 
 * @param int $limit Лимит товаров (по умолчанию все)
 * @param string $category Категория товаров (по умолчанию все)
 * @param int $subcategory_id ID подкатегории (по умолчанию все)
 * @return array Массив товаров
 */
function getProducts($limit = null, $category = null, $subcategory_id = null) {
    global $conn;
    
    $sql = "SELECT * FROM product";
    $conditions = [];
    
    if ($category && $category != 'all') {
        $conditions[] = "category = '" . mysqli_real_escape_string($conn, $category) . "'";
    }
    
    if ($subcategory_id) {
        $conditions[] = "subcategory_id = " . (int)$subcategory_id;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY id DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Получение категорий товаров из таблицы categories
 * 
 * @return array Массив категорий
 */
function getCategories() {
    global $conn;
    
    // Создаем таблицу категорий, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        parent_id INT(11) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($conn, $sql);
    
    // Получаем категории из базы данных
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    
    // Если категорий нет, добавляем две стандартные
    if (empty($categories)) {
        addCategory('iPhone', 'Смартфоны iPhone', '');
        addCategory('Android', 'Смартфоны на Android', '');
        
        // Получаем категории снова
        $sql = "SELECT * FROM categories ORDER BY name";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }
        }
    }
    
    return $categories;
}

/**
 * Получение категорий товаров из таблицы product (для обратной совместимости)
 * 
 * @return array Массив категорий
 */
function getProductCategories() {
    global $conn;
    
    // Получаем уникальные категории из таблицы product
    $sql = "SELECT DISTINCT category FROM product WHERE category IS NOT NULL AND category != '' ORDER BY category";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category'];
        }
    }
    
    // Если категорий нет, добавляем стандартные
    if (empty($categories)) {
        $categories = ['iPhone', 'Android'];
    }
    
    return $categories;
}

/**
 * Получение информации о товаре по ID
 * 
 * @param int $id ID товара
 * @return array|null Информация о товаре или null, если товар не найден
 */
function getProductById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM product WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    return $result && mysqli_num_rows($result) ? mysqli_fetch_assoc($result) : null;
}

/**
 * Поиск товаров по запросу
 * 
 * @param string $query Поисковый запрос
 * @param int $limit Ограничение количества результатов (по умолчанию без ограничения)
 * @return array Массив найденных товаров
 */
function searchProducts($query, $limit = null) {
    global $conn;
    
    $search = mysqli_real_escape_string($conn, $query);
    
    $sql = "SELECT * FROM product 
            WHERE name LIKE '%$search%' 
            OR description LIKE '%$search%' 
            OR category LIKE '%$search%'
            ORDER BY name";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Создание таблицы пользователей, если она не существует
 */
function createUsersTableIfNotExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        login VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($conn, $sql);
}

/**
 * Регистрация нового пользователя
 * 
 * @param string $fullname ФИО пользователя
 * @param string $email Email пользователя
 * @param string $phone Телефон пользователя
 * @param string $login Логин пользователя
 * @param string $password Пароль пользователя
 * @param string $role Роль пользователя (по умолчанию 'user')
 * @return array Массив с результатом регистрации
 */
function registerUser($fullname, $email, $phone, $login, $password, $role = 'user') {
    global $conn;
    
    // Создаем таблицу, если её не существует
    createUsersTableIfNotExists();
    
    // Проверяем, не существует ли уже пользователь с таким email
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
    
    if (mysqli_num_rows($check_email) > 0) {
        return [
            'success' => false,
            'message' => 'Пользователь с таким email уже зарегистрирован'
        ];
    }
    
    // Проверяем, не существует ли уже пользователь с таким логином
    $check_login = mysqli_query($conn, "SELECT id FROM users WHERE login = '" . mysqli_real_escape_string($conn, $login) . "'");
    
    if (mysqli_num_rows($check_login) > 0) {
        return [
            'success' => false,
            'message' => 'Пользователь с таким логином уже зарегистрирован'
        ];
    }
    
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Добавляем пользователя в базу
    $sql = "INSERT INTO users (fullname, email, phone, login, password, role) VALUES (
        '" . mysqli_real_escape_string($conn, $fullname) . "',
        '" . mysqli_real_escape_string($conn, $email) . "',
        '" . mysqli_real_escape_string($conn, $phone) . "',
        '" . mysqli_real_escape_string($conn, $login) . "',
        '" . mysqli_real_escape_string($conn, $hashed_password) . "',
        '" . mysqli_real_escape_string($conn, $role) . "'
    )";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'user_id' => mysqli_insert_id($conn)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при регистрации: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Получение данных пользователя по ID
 * 
 * @param int $user_id ID пользователя
 * @return array|null Данные пользователя или null, если пользователь не найден
 */
function getUserById($user_id) {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, password, role FROM users WHERE id = " . (int)$user_id;
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Авторизация пользователя
 * 
 * @param string $login Логин пользователя
 * @param string $password Пароль пользователя
 * @return array Массив с результатом авторизации
 */
function loginUser($login, $password) {
    global $conn;
    
    // Создаем таблицу, если её не существует
    createUsersTableIfNotExists();
    
    // Получаем данные пользователя
    $sql = "SELECT id, fullname, email, phone, login, password, role FROM users WHERE login = '" . mysqli_real_escape_string($conn, $login) . "'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Проверяем пароль
        if (password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user_id' => $user['id'],
                'user_fullname' => $user['fullname'],
                'user_email' => $user['email'],
                'user_phone' => $user['phone'],
                'user_login' => $user['login'],
                'user_role' => $user['role']
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Неверный логин или пароль'
    ];
}

/**
 * Получение всех пользователей с пагинацией
 * 
 * @param int $page Номер страницы (начиная с 1)
 * @param int $per_page Количество записей на странице
 * @return array Массив с пользователями и информацией о пагинации
 */
function getPaginatedUsers($page = 1, $per_page = 10) {
    global $conn;
    
    // Вычисляем смещение
    $offset = ($page - 1) * $per_page;
    
    // Получаем общее количество пользователей
    $count_sql = "SELECT COUNT(*) as total FROM users";
    $count_result = mysqli_query($conn, $count_sql);
    $total_users = mysqli_fetch_assoc($count_result)['total'];
    
    // Вычисляем общее количество страниц
    $total_pages = ceil($total_users / $per_page);
    
    // Получаем пользователей для текущей страницы
    $sql = "SELECT id, fullname, email, phone, login, role FROM users ORDER BY id DESC LIMIT $offset, $per_page";
    $result = mysqli_query($conn, $sql);
    $users = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return [
        'users' => $users,
        'pagination' => [
            'total_users' => $total_users,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ]
    ];
}

/**
 * Получение всех заказов с пагинацией
 * 
 * @param int $page Номер страницы (начиная с 1)
 * @param int $per_page Количество записей на странице
 * @return array Массив с заказами и информацией о пагинации
 */
function getPaginatedOrders($page = 1, $per_page = 10) {
    global $conn;
    
    // Вычисляем смещение
    $offset = ($page - 1) * $per_page;
    
    // Получаем общее количество заказов
    $count_sql = "SELECT COUNT(*) as total FROM orders";
    $count_result = mysqli_query($conn, $count_sql);
    $total_orders = mysqli_fetch_assoc($count_result)['total'];
    
    // Вычисляем общее количество страниц
    $total_pages = ceil($total_orders / $per_page);
    
    // Получаем заказы для текущей страницы с информацией о пользователе
    $sql = "SELECT o.*, u.fullname, u.email, u.phone 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT $offset, $per_page";
    $result = mysqli_query($conn, $sql);
    $orders = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Обеспечиваем, что все поля имеют значения (не NULL)
            if (!isset($row['fullname'])) $row['fullname'] = null;
            if (!isset($row['email'])) $row['email'] = null;
            if (!isset($row['phone'])) $row['phone'] = null;
            if (!isset($row['city'])) $row['city'] = null;
            if (!isset($row['address'])) $row['address'] = null;
            if (!isset($row['status'])) $row['status'] = 'unknown';
            if (!isset($row['created_at'])) $row['created_at'] = null;
            if (!isset($row['region'])) $row['region'] = null;
            if (!isset($row['postal_code'])) $row['postal_code'] = null;
            
            $orders[] = $row;
        }
    }
    
    return [
        'orders' => $orders,
        'pagination' => [
            'total_orders' => $total_orders,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ]
    ];
}

/**
 * Добавление нового товара
 * 
 * @param string $name Название товара
 * @param string $description Описание товара
 * @param float $price Цена товара
 * @param string $category Категория товара
 * @param string $color Цвет товара
 * @param string $image Путь к изображению товара
 * @param string $sku Артикул товара
 * @param int $stock Количество товара на складе
 * @param bool $is_new Флаг "Новинка"
 * @param bool $is_bestseller Флаг "Бестселлер"
 * @param int $discount Процент скидки
 * @return array Массив с результатом добавления товара
 */
function addProduct($name, $description, $price, $category, $color, $image, $sku, $stock, $is_new = 0, $is_bestseller = 0, $discount = 0) {
    global $conn;
    
    $sql = "INSERT INTO product (name, description, price, category, image, sku, stock, is_new, is_bestseller, discount) VALUES (
        '" . mysqli_real_escape_string($conn, $name) . "',
        '" . mysqli_real_escape_string($conn, $description) . "',
        " . (float)$price . ",
        '" . mysqli_real_escape_string($conn, $category) . "',
        '" . mysqli_real_escape_string($conn, $image) . "',
        '" . mysqli_real_escape_string($conn, $sku) . "',
        " . (int)$stock . ",
        " . (int)$is_new . ",
        " . (int)$is_bestseller . ",
        " . (int)$discount . "
    )";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'product_id' => mysqli_insert_id($conn),
            'message' => 'Товар успешно добавлен'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при добавлении товара: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Обновление информации о товаре
 * 
 * @param int $id ID товара
 * @param array $data Данные товара для обновления
 * @return array Массив с результатом обновления
 */
function updateProduct($id, $data) {
    global $conn;
    
    $id = (int)$id;
    $updates = [];
    
    // Формируем строку обновления для каждого поля
    foreach ($data as $field => $value) {
        if ($field === 'price' || $field === 'stock' || $field === 'discount' || 
            $field === 'is_new' || $field === 'is_bestseller') {
            // Числовые поля
            $updates[] = "`$field` = " . (float)$value;
        } else {
            // Строковые поля
            $updates[] = "`$field` = '" . mysqli_real_escape_string($conn, $value) . "'";
        }
    }
    
    // Если нет полей для обновления
    if (empty($updates)) {
        return [
            'success' => false,
            'message' => 'Нет данных для обновления'
        ];
    }
    
    $sql = "UPDATE product SET " . implode(', ', $updates) . " WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Товар успешно обновлен'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении товара: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Удаление товара
 * 
 * @param int $id ID товара
 * @return array Массив с результатом удаления
 */
function deleteProduct($id) {
    global $conn;
    
    $id = (int)$id;
    
    // Проверяем, существует ли товар
    $check_sql = "SELECT id FROM product WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) === 0) {
        return [
            'success' => false,
            'message' => 'Товар не найден'
        ];
    }
    
    // Удаляем товар
    $sql = "DELETE FROM product WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Товар успешно удален'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при удалении товара: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Получение всех доступных цветов товаров
 * 
 * @return array Массив цветов
 */
function getColors() {
    return [
        'black' => 'Черный',
        'white' => 'Белый',
        'red' => 'Красный',
        'green' => 'Зеленый',
        'blue' => 'Синий',
        'yellow' => 'Желтый',
        'purple' => 'Фиолетовый',
        'pink' => 'Розовый',
        'gold' => 'Золотой',
        'silver' => 'Серебристый'
    ];
}

/**
 * Получение CSS-цветов для отображения
 * 
 * @return array Массив CSS-цветов
 */
function getColorCodes() {
    return [
        'black' => '#000000',
        'white' => '#ffffff',
        'red' => '#ff3b30',
        'green' => '#34c759',
        'blue' => '#007aff',
        'yellow' => '#ffcc00',
        'purple' => '#af52de',
        'pink' => '#ff2d55',
        'gold' => '#d4af37',
        'silver' => '#c0c0c0'
    ];
}

/**
 * Добавление новой категории
 * 
 * @param string $name Название категории
 * @param string $description Описание категории
 * @param string $image Путь к изображению категории
 * @param int $parent_id ID родительской категории (если есть)
 * @return array Массив с результатом добавления категории
 */
function addCategory($name, $description, $image, $parent_id = null) {
    global $conn;
    
    try {
        // Проверяем, существует ли категория с таким именем
        $check_sql = "SELECT id FROM categories WHERE name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Категория с таким названием уже существует'
            ];
        }
        
        // Если указан parent_id, проверяем его существование
        if ($parent_id) {
            $check_parent_sql = "SELECT id FROM categories WHERE id = ?";
            $check_parent_stmt = $conn->prepare($check_parent_sql);
            $check_parent_stmt->bind_param("i", $parent_id);
            $check_parent_stmt->execute();
            if ($check_parent_stmt->get_result()->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Родительская категория не существует'
                ];
            }
        }
        
        // Добавляем новую категорию
        $sql = "INSERT INTO categories (name, description, image, parent_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $description, $image, $parent_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Категория успешно добавлена',
                'id' => $stmt->insert_id
            ];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при добавлении категории: ' . $e->getMessage()
        ];
    }
}

/**
 * Обновление категории
 */
function updateCategory($id, $data) {
    global $conn;
    
    try {
        // Проверяем существование категории с таким же именем, исключая текущую
        $check_sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $data['name'], $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Категория с таким названием уже существует'
            ];
        }
        
        // Если указан parent_id, проверяем его существование и валидность
        if (!empty($data['parent_id'])) {
            // Проверяем, не пытаемся ли мы установить категорию как родителя самой себе
            if ($data['parent_id'] == $id) {
                return [
                    'success' => false,
                    'message' => 'Категория не может быть родителем самой себе'
                ];
            }
            
            // Проверяем существование родительской категории
            $check_parent_sql = "SELECT id FROM categories WHERE id = ?";
            $check_parent_stmt = $conn->prepare($check_parent_sql);
            $check_parent_stmt->bind_param("i", $data['parent_id']);
            $check_parent_stmt->execute();
            if ($check_parent_stmt->get_result()->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Родительская категория не существует'
                ];
            }
        }
        
        // Обновляем категорию
        $sql = "UPDATE categories SET name = ?, description = ?, parent_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $parent_id = empty($data['parent_id']) ? null : $data['parent_id'];
        $stmt->bind_param("ssii", $data['name'], $data['description'], $parent_id, $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Категория успешно обновлена'
            ];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении категории: ' . $e->getMessage()
        ];
    }
}

/**
 * Получение категории по ID
 */
function getCategoryById($id) {
    global $conn;
    
    $sql = "SELECT c.*, pc.name as parent_name 
            FROM categories c 
            LEFT JOIN categories pc ON c.parent_id = pc.id 
            WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Получение всех категорий
 */
function getAllCategories() {
    global $conn;
    
    $sql = "SELECT c.*, pc.name as parent_name 
            FROM categories c 
            LEFT JOIN categories pc ON c.parent_id = pc.id 
            ORDER BY c.parent_id IS NULL DESC, c.name ASC";
    
    $result = $conn->query($sql);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Получение основных категорий (без родителя)
 */
function getMainCategories() {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
    $result = $conn->query($sql);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}



/**
 * Удаление категории
 * 
 * @param int $id ID категории
 * @return array Массив с результатом удаления
 */
function deleteCategory($id) {
    global $conn;
    
    $id = (int)$id;
    
    // Проверяем, существует ли категория
    $check_sql = "SELECT id FROM categories WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) === 0) {
        return [
            'success' => false,
            'message' => 'Категория не найдена'
        ];
    }
    
    // Удаляем категорию
    $sql = "DELETE FROM categories WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Категория успешно удалена'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при удалении категории: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Получение категорий для страницы каталога
 * 
 * @return array Массив названий категорий
 */
function getCatalogCategories() {
    $categories = getCategories();
    $result = [];
    
    foreach ($categories as $category) {
        $result[] = $category['name'];
    }
    
    return $result;
}

/**
 * Генерирует случайный код для привязки Telegram
 * 
 * @param int $user_id ID пользователя
 * @return string Сгенерированный код
 */
function generateTelegramVerificationCode($user_id) {
    global $conn;
    
    // Генерируем случайный 5-значный код
    $code = sprintf("%05d", mt_rand(0, 99999));
    
    // Устанавливаем время истечения кода (30 минут)
    $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Обновляем данные пользователя
    $sql = "UPDATE users SET 
            telegram_verification_code = '$code',
            telegram_code_expires = '$expires'
            WHERE id = " . (int)$user_id;
    
    mysqli_query($conn, $sql);
    
    return $code;
}

/**
 * Проверяет код подтверждения для привязки Telegram
 * 
 * @param int $user_id ID пользователя
 * @param string $code Код подтверждения
 * @param string $telegram_id ID пользователя в Telegram
 * @param string $telegram_username Username пользователя в Telegram
 * @return array Результат проверки
 */
function verifyTelegramCode($user_id, $code, $telegram_id, $telegram_username = null) {
    global $conn;
    
    // Получаем данные пользователя
    $sql = "SELECT telegram_verification_code, telegram_code_expires FROM users WHERE id = " . (int)$user_id;
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Проверяем, не истек ли срок действия кода
        $now = new DateTime();
        $expires = new DateTime($user['telegram_code_expires']);
        
        if ($now > $expires) {
            return [
                'success' => false,
                'message' => 'Срок действия кода истек. Пожалуйста, сгенерируйте новый код.'
            ];
        }
        
        // Проверяем код
        if ($user['telegram_verification_code'] === $code) {
            // Код верный, привязываем Telegram
            $update_sql = "UPDATE users SET 
                          telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "',
                          telegram_username = " . ($telegram_username ? "'" . mysqli_real_escape_string($conn, $telegram_username) . "'" : "NULL") . ",
                          telegram_verification_code = NULL,
                          telegram_code_expires = NULL
                          WHERE id = " . (int)$user_id;
            
            if (mysqli_query($conn, $update_sql)) {
                return [
                    'success' => true,
                    'message' => 'Telegram успешно привязан к вашему аккаунту.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка при привязке Telegram: ' . mysqli_error($conn)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Неверный код подтверждения.'
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Пользователь не найден.'
    ];
}

/**
 * Отвязывает Telegram от аккаунта пользователя
 * 
 * @param int $user_id ID пользователя
 * @return array Результат операции
 */
function unlinkTelegram($user_id) {
    global $conn;
    
    $sql = "UPDATE users SET 
            telegram_id = NULL,
            telegram_username = NULL,
            telegram_verification_code = NULL,
            telegram_code_expires = NULL
            WHERE id = " . (int)$user_id;
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Telegram успешно отвязан от вашего аккаунта.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при отвязке Telegram: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Получает пользователя по Telegram ID
 * 
 * @param string $telegram_id ID пользователя в Telegram
 * @return array|null Данные пользователя или null, если пользователь не найден
 */
function getUserByTelegramId($telegram_id) {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, role, telegram_id, telegram_username 
            FROM users 
            WHERE telegram_id = '" . mysqli_real_escape_string($conn, $telegram_id) . "'";
    
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Обновляет информацию о пользователе в базе данных
 * 
 * @param int $user_id ID пользователя
 * @param array $data Данные для обновления
 * @return array Результат операции
 */
function updateUserData($user_id, $data) {
    global $conn;
    
    $updates = [];
    
    // Формируем строку обновления только для переданных полей
    if (isset($data['fullname'])) {
        $updates[] = "fullname = '" . mysqli_real_escape_string($conn, $data['fullname']) . "'";
    }
    
    if (isset($data['email'])) {
        $updates[] = "email = '" . mysqli_real_escape_string($conn, $data['email']) . "'";
    }
    
    if (isset($data['phone'])) {
        $updates[] = "phone = '" . mysqli_real_escape_string($conn, $data['phone']) . "'";
    }
    
    if (isset($data['login'])) {
        $updates[] = "login = '" . mysqli_real_escape_string($conn, $data['login']) . "'";
    }
    
    if (isset($data['password'])) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $updates[] = "password = '" . mysqli_real_escape_string($conn, $hashed_password) . "'";
    }
    
    if (empty($updates)) {
        return [
            'success' => false,
            'message' => 'Нет данных для обновления.'
        ];
    }
    
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = " . (int)$user_id;
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Данные успешно обновлены.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Отправляет уведомление через Telegram
 * 
 * @param int $user_id ID пользователя
 * @param string $message Текст сообщения
 * @return bool Результат отправки
 */
function sendTelegramNotification($user_id, $message) {
    global $conn;
    
    // Получаем Telegram ID пользователя
    $sql = "SELECT telegram_id FROM users WHERE id = " . (int)$user_id . " AND telegram_id IS NOT NULL";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $telegram_id = $user['telegram_id'];
        
        // Проверяем, что есть конфигурация для Telegram
        if (!defined('TELEGRAM_BOT_TOKEN')) {
            // Подключаем файл конфигурации, если он еще не подключен
            require_once __DIR__ . '/telegram_config.php';
        }
        
        // Токен бота
        $bot_token = TELEGRAM_BOT_TOKEN;
        
        // Формируем данные для отправки
        $data = [
            'chat_id' => $telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        // Отправляем запрос к API Telegram
        $ch = curl_init("https://api.telegram.org/bot$bot_token/sendMessage");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result !== false;
    }
    
    return false;
}

/**
 * Создание таблицы корзины, если она не существует
 */
function createCartTableIfNotExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) DEFAULT NULL,
        session_id VARCHAR(255) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (session_id),
        INDEX (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($conn, $sql);
}

/**
 * Добавление товара в корзину
 * 
 * @param int $product_id ID товара
 * @param int $quantity Количество товара
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @param int|null $user_id ID пользователя (если авторизован)
 * @return array Результат операции
 */
function addToCart($product_id, $quantity = 1, $user_id = null, $session_id = null) {
    global $conn;
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    if (!$session_id) $session_id = session_id();
    
    // Проверяем, есть ли такой товар в корзине
    if ($user_id) {
        $sql = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    } else {
        $sql = "SELECT * FROM cart WHERE session_id = '" . mysqli_real_escape_string($conn, $session_id) . "' AND product_id = $product_id";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result)) {
        // Товар уже есть в корзине - обновляем количество
        $row = mysqli_fetch_assoc($result);
        $new_quantity = $row['quantity'] + $quantity;
        
        if ($user_id) {
            $update = "UPDATE cart SET quantity = $new_quantity WHERE id = {$row['id']}";
        } else {
            $update = "UPDATE cart SET quantity = $new_quantity WHERE id = {$row['id']}";
        }
        
        if (mysqli_query($conn, $update)) {
            return [
                'success' => true,
                'message' => 'Количество товара в корзине обновлено',
                'quantity' => $new_quantity,
                'action' => 'update'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении товара в корзине: ' . mysqli_error($conn)
            ];
        }
    } else {
        // Товара нет в корзине - добавляем новый
        if ($user_id) {
            $insert = "INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES ($user_id, '', $product_id, $quantity)";
        } else {
            $insert = "INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (NULL, '" . mysqli_real_escape_string($conn, $session_id) . "', $product_id, $quantity)";
        }
        
        if (mysqli_query($conn, $insert)) {
            return [
                'success' => true,
                'message' => 'Товар добавлен в корзину',
                'quantity' => $quantity,
                'action' => 'insert'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении товара в корзину: ' . mysqli_error($conn)
            ];
        }
    }
}

/**
 * Обновление количества товара в корзине
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @param int $cart_id ID записи в корзине
 * @param int $quantity Новое количество товара
 * @return array Результат операции
 */
function updateCartItemQuantity($user_id = null, $session_id = null, $cart_id = 0, $quantity = 1) {
    global $conn;
    
    if (!$session_id) $session_id = session_id();
    
    if ($quantity <= 0) {
        return removeFromCart($user_id, $session_id, $cart_id);
    }
    
    if ($user_id) {
        $update_sql = "UPDATE cart SET quantity = " . (int)$quantity . " WHERE id = " . (int)$cart_id . " AND user_id = " . (int)$user_id;
    } else {
        $update_sql = "UPDATE cart SET quantity = " . (int)$quantity . " WHERE id = " . (int)$cart_id . " AND session_id = '" . mysqli_real_escape_string($conn, $session_id) . "'";
    }
    
    if (mysqli_query($conn, $update_sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            return [
                'success' => true,
                'message' => 'Количество товара обновлено',
                'quantity' => $quantity
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Товар не найден в корзине'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении товара в корзине: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Удаление товара из корзины
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @param int $cart_id ID записи в корзине
 * @return array Результат операции
 */
function removeFromCart($user_id = null, $session_id = null, $cart_id = 0) {
    global $conn;
    
    if (!$session_id) $session_id = session_id();
    
    if ($user_id) {
        $delete_sql = "DELETE FROM cart WHERE id = " . (int)$cart_id . " AND user_id = " . (int)$user_id;
    } else {
        $delete_sql = "DELETE FROM cart WHERE id = " . (int)$cart_id . " AND session_id = '" . mysqli_real_escape_string($conn, $session_id) . "'";
    }
    
    if (mysqli_query($conn, $delete_sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            return [
                'success' => true,
                'message' => 'Товар удален из корзины'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Товар не найден в корзине'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при удалении товара из корзины: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Получение содержимого корзины
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @return array Массив товаров в корзине с деталями
 */
function getCartItems($user_id = null, $session_id = null) {
    global $conn;
    
    // Создаем таблицу корзины, если её не существует
    createCartTableIfNotExists();
    
    if (!$session_id) $session_id = session_id();
    
    if ($user_id) {
        $sql = "SELECT c.*, p.name, p.price, p.image FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = " . (int)$user_id;
    } else {
        $sql = "SELECT c.*, p.name, p.price, p.image FROM cart c JOIN product p ON c.product_id = p.id WHERE c.session_id = '" . mysqli_real_escape_string($conn, $session_id) . "' AND c.user_id IS NULL";
    }
    
    $result = mysqli_query($conn, $sql);
    $items = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $items[] = $row;
        }
    }
    
    return $items;
}

/**
 * Получение количества товаров в корзине
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @return int Количество товаров
 */
function getCartItemCount($session_id = null, $user_id = null) {
    global $conn;
    
    // Создаем таблицу корзины, если её не существует
    createCartTableIfNotExists();
    
    if (!$session_id) $session_id = session_id();
    
    // Отладочная информация
    $debug = [
        'function' => 'getCartItemCount',
        'session_id' => $session_id,
        'user_id' => $user_id
    ];
    
    if ($user_id) {
        $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = " . (int)$user_id;
    } else {
        $sql = "SELECT SUM(quantity) as total FROM cart WHERE session_id = '" . mysqli_real_escape_string($conn, $session_id) . "' AND user_id IS NULL";
    }
    
    // Добавляем SQL запрос в отладочную информацию
    $debug['sql'] = $sql;
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total = (int)$row['total'];
        
        // Добавляем результат в отладочную информацию
        $debug['result'] = $total;
        
        // Записываем отладочную информацию в лог-файл
        error_log(print_r($debug, true));
        
        return $total;
    }
    
    // Записываем отладочную информацию в лог-файл (нулевой результат)
    $debug['result'] = 0;
    error_log(print_r($debug, true));
    
    return 0;
}

/**
 * Очистка корзины пользователя
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии для неавторизованных пользователей
 * @return array Результат операции
 */
function clearCart($user_id = null, $session_id = null) {
    global $conn;
    
    if (!$session_id) $session_id = session_id();
    
    if ($user_id) {
        $sql = "DELETE FROM cart WHERE user_id = " . (int)$user_id;
    } else {
        $sql = "DELETE FROM cart WHERE session_id = '" . mysqli_real_escape_string($conn, $session_id) . "' AND user_id IS NULL";
    }
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Корзина очищена'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Ошибка при очистке корзины: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Создание таблицы заказов, если она не существует
 */
function createOrdersTablesIfNotExists() {
    global $conn;
    
    // Таблица заказов
    $orders_sql = "CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) NULL DEFAULT NULL,
        `session_id` VARCHAR(255) NULL,
        `fullname` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NOT NULL,
        `region` VARCHAR(255) NULL DEFAULT NULL,
        `city` VARCHAR(255) NOT NULL,
        `address` TEXT NOT NULL,
        `postal_code` VARCHAR(20) NULL DEFAULT NULL,
        `payment_method` VARCHAR(50) NOT NULL,
        `delivery_method` VARCHAR(50) NOT NULL,
        `delivery_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `total_amount` DECIMAL(10,2) NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `comment` TEXT NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_session_id` (`session_id`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    // Таблица элементов заказа
    $order_items_sql = "CREATE TABLE IF NOT EXISTS `order_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `order_id` INT(11) NOT NULL,
        `product_id` INT(11) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `quantity` INT(11) NOT NULL,
        `subtotal` DECIMAL(10,2) NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_order_id` (`order_id`),
        INDEX `idx_product_id` (`product_id`),
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($conn, $orders_sql);
    mysqli_query($conn, $order_items_sql);
}

/**
 * Создание нового заказа
 * 
 * @param array $order_data Данные заказа
 * @return array Результат создания заказа
 */
function createOrder($order_data) {
    global $conn;
    
    // Начинаем транзакцию
    mysqli_begin_transaction($conn);
    
    try {
        // Рассчитываем общую стоимость товаров в корзине
        $total_amount = 0;
        foreach ($order_data['items'] as $item) {
            $total_amount += $item['subtotal'];
        }
        
        // Добавляем общую стоимость в данные заказа
        $order_data['total_amount'] = $total_amount;
        
        // Определяем стоимость доставки
        $delivery_cost = 0;
        switch ($order_data['delivery_method']) {
            case 'courier':
                $delivery_cost = 300;
                break;
            case 'post':
                $delivery_cost = 250;
                break;
            case 'pickup':
                $delivery_cost = 0;
                break;
            default:
                $delivery_cost = 0;
        }
        
        // Создаем заказ в базе данных
        $order_sql = "INSERT INTO `orders` (
            `user_id`, 
            `session_id`, 
            `fullname`, 
            `email`, 
            `phone`, 
            `region`, 
            `city`, 
            `address`, 
            `postal_code`, 
            `payment_method`, 
            `delivery_method`, 
            `delivery_cost`,
            `total_amount`, 
            `status`, 
            `comment`
        ) VALUES (
            " . ($order_data['user_id'] ? (int)$order_data['user_id'] : "NULL") . ",
            '" . mysqli_real_escape_string($conn, $order_data['session_id']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['fullname']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['email']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['phone']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['region']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['city']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['address']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['postal_code']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['payment_method']) . "',
            '" . mysqli_real_escape_string($conn, $order_data['delivery_method']) . "',
            " . (float)$delivery_cost . ",
            " . (float)($order_data['total_amount'] + $delivery_cost) . ",
            'pending',
            '" . mysqli_real_escape_string($conn, $order_data['comment']) . "'
        )";
        
        if (!mysqli_query($conn, $order_sql)) {
            throw new Exception("Ошибка при создании заказа: " . mysqli_error($conn));
        }
        
        $order_id = mysqli_insert_id($conn);
        
        // Добавляем товары из заказа
        foreach ($order_data['items'] as $item) {
            $item_sql = "INSERT INTO `order_items` (
                `order_id`, 
                `product_id`, 
                `name`, 
                `price`, 
                `quantity`, 
                `subtotal`
            ) VALUES (
                " . (int)$order_id . ",
                " . (int)$item['product_id'] . ",
                '" . mysqli_real_escape_string($conn, $item['name']) . "',
                " . (float)$item['price'] . ",
                " . (int)$item['quantity'] . ",
                " . (float)$item['subtotal'] . "
            )";
            
            if (!mysqli_query($conn, $item_sql)) {
                throw new Exception("Ошибка при добавлении товара к заказу: " . mysqli_error($conn));
            }
            
            // Уменьшаем количество товара на складе
            $update_stock_sql = "UPDATE product SET stock = GREATEST(0, stock - " . (int)$item['quantity'] . ") 
                               WHERE id = " . (int)$item['product_id'];
            
            if (!mysqli_query($conn, $update_stock_sql)) {
                throw new Exception("Ошибка при обновлении остатков товара: " . mysqli_error($conn));
            }
        }
        
        // Фиксируем транзакцию
        mysqli_commit($conn);
        
        return [
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Заказ успешно создан'
        ];
        
    } catch (Exception $e) {
        // Откатываем транзакцию в случае ошибки
        mysqli_rollback($conn);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Получение заказов пользователя
 * 
 * @param int $user_id ID пользователя
 * @param int $limit Лимит заказов
 * @param int $offset Смещение для пагинации
 * @return array Массив заказов
 */
function getUserOrders($user_id, $limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT * FROM `orders` WHERE `user_id` = " . (int)$user_id . " ORDER BY `created_at` DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
        
        if ($offset !== null) {
            $sql .= " OFFSET " . (int)$offset;
        }
    }
    
    $result = mysqli_query($conn, $sql);
    $orders = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

/**
 * Получение деталей заказа по ID
 * 
 * @param int $order_id ID заказа
 * @param int $user_id ID пользователя (для проверки доступа)
 * @return array|null Данные заказа или null, если заказ не найден
 */
function getOrderById($order_id, $user_id = null) {
    global $conn;
    
    $sql = "SELECT * FROM `orders` WHERE `id` = " . (int)$order_id;
    
    // Если указан ID пользователя, проверяем доступ
    if ($user_id !== null) {
        $sql .= " AND `user_id` = " . (int)$user_id;
    }
    
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Получение товаров заказа
 * 
 * @param int $order_id ID заказа
 * @return array Массив товаров заказа
 */
function getOrderItems($order_id) {
    global $conn;
    
    $sql = "SELECT * FROM `order_items` WHERE `order_id` = " . (int)$order_id;
    $result = mysqli_query($conn, $sql);
    $items = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    
    return $items;
}

/**
 * Обновление статуса заказа
 * 
 * @param int $order_id ID заказа
 * @param string $status Новый статус
 * @return bool Результат обновления
 */
function updateOrderStatus($order_id, $status) {
    global $conn;
    
    $valid_statuses = ['pending', 'processing', 'completed', 'cancelled', 'closed'];
    
    if (!in_array($status, $valid_statuses)) {
        return false;
    }
    
    // Получаем текущий статус заказа перед обновлением
    $current_status_query = "SELECT status FROM orders WHERE id = " . (int)$order_id;
    $current_status_result = mysqli_query($conn, $current_status_query);
    $current_status_row = mysqli_fetch_assoc($current_status_result);
    $current_status = $current_status_row ? $current_status_row['status'] : '';
    
    // Проверяем, можно ли редактировать заказ
    // Если заказ уже закрыт или выполнен, запрещаем изменение статуса
    if ($current_status === 'closed' || $current_status === 'completed') {
        return false;
    }
    
    // Обновляем статус заказа
    $sql = "UPDATE `orders` SET 
            `status` = '" . mysqli_real_escape_string($conn, $status) . "'
            WHERE `id` = " . (int)$order_id;
    
    $result = mysqli_query($conn, $sql);
    
    // Если статус изменен на "completed" или "closed" и предыдущий статус не был "completed" или "closed",
    // обновляем статистику продаж
    if ($result && ($status === 'completed' || $status === 'closed') && 
        $current_status !== 'completed' && $current_status !== 'closed') {
        // Здесь статистика продаж обновляется автоматически, так как функции отчетов
        // учитывают заказы со статусами 'completed' и 'closed'
    }
    
    return $result;
}

/**
 * Получает общую сумму продаж
 * 
 * @param string $dateFrom Дата начала периода (опционально)
 * @param string $dateTo Дата окончания периода (опционально)
 * @param string $category Название категории (опционально)
 * @return float Общая сумма продаж
 */
function getTotalSalesAmount($dateFrom = null, $dateTo = null, $category = null) {
    global $conn;
    
    $sql = "SELECT SUM(oi.price * oi.quantity) as total 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id
            JOIN product p ON oi.product_id = p.id
            WHERE (o.status = 'completed' OR o.status = 'closed')";
    
    // Добавляем фильтры по датам, если указаны
    if ($dateFrom) {
        $dateFrom = mysqli_real_escape_string($conn, $dateFrom);
        $sql .= " AND o.created_at >= '$dateFrom 00:00:00'";
    }
    
    if ($dateTo) {
        $dateTo = mysqli_real_escape_string($conn, $dateTo);
        $sql .= " AND o.created_at <= '$dateTo 23:59:59'";
    }
    
    // Добавляем фильтр по категории, если указан
    if ($category) {
        $category = mysqli_real_escape_string($conn, $category);
        $sql .= " AND p.category = '$category'";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'] ?: 0;
    }
    
    return 0;
}

/**
 * Получает общее количество проданных товаров
 * 
 * @param string $dateFrom Дата начала периода (опционально)
 * @param string $dateTo Дата окончания периода (опционально)
 * @param string $category Название категории (опционально)
 * @return int Общее количество проданных товаров
 */
function getTotalSoldItemsCount($dateFrom = null, $dateTo = null, $category = null) {
    global $conn;
    
    $sql = "SELECT SUM(oi.quantity) as total 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id
            JOIN product p ON oi.product_id = p.id
            WHERE (o.status = 'completed' OR o.status = 'closed')";
    
    // Добавляем фильтры по датам, если указаны
    if ($dateFrom) {
        $dateFrom = mysqli_real_escape_string($conn, $dateFrom);
        $sql .= " AND o.created_at >= '$dateFrom 00:00:00'";
    }
    
    if ($dateTo) {
        $dateTo = mysqli_real_escape_string($conn, $dateTo);
        $sql .= " AND o.created_at <= '$dateTo 23:59:59'";
    }
    
    // Добавляем фильтр по категории, если указан
    if ($category) {
        $category = mysqli_real_escape_string($conn, $category);
        $sql .= " AND p.category = '$category'";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'] ?: 0;
    }
    
    return 0;
}

/**
 * Получает количество выполненных заказов
 * 
 * @param string $dateFrom Дата начала периода (опционально)
 * @param string $dateTo Дата окончания периода (опционально)
 * @return int Количество выполненных заказов
 */
function getCompletedOrdersCount($dateFrom = null, $dateTo = null) {
    global $conn;
    
    $sql = "SELECT COUNT(DISTINCT id) as total 
            FROM orders 
            WHERE (status = 'completed' OR status = 'closed')";
    
    // Добавляем фильтры по датам, если указаны
    if ($dateFrom) {
        $dateFrom = mysqli_real_escape_string($conn, $dateFrom);
        $sql .= " AND created_at >= '$dateFrom 00:00:00'";
    }
    
    if ($dateTo) {
        $dateTo = mysqli_real_escape_string($conn, $dateTo);
        $sql .= " AND created_at <= '$dateTo 23:59:59'";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'] ?: 0;
    }
    
    return 0;
}

/**
 * Получает отчет по продажам товаров
 * 
 * @param string $dateFrom Дата начала периода (опционально)
 * @param string $dateTo Дата окончания периода (опционально)
 * @param string $category Название категории (опционально)
 * @param int $page Номер страницы (опционально)
 * @param int $per_page Количество записей на странице (опционально)
 * @return array Массив с данными отчета и информацией о пагинации
 */
function getProductsSalesReport($dateFrom = null, $dateTo = null, $category = null, $page = 1, $per_page = 10) {
    global $conn;
    
    // Запрос для подсчета общего количества записей
    $count_sql = "SELECT 
                COUNT(DISTINCT p.id) as total_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN product p ON oi.product_id = p.id
            WHERE 1=1";
    
    // Запрос для получения данных отчета
    $sql = "SELECT 
                p.id,
                p.name,
                p.category,
                SUM(oi.quantity) as quantity,
                SUM(oi.price * oi.quantity) as total_amount,
                AVG(oi.price) as avg_price
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN product p ON oi.product_id = p.id
            WHERE 1=1";
    
    // Добавляем условия фильтрации
    $params = [];
    
    // Фильтрация по статусу заказа (только выполненные заказы)
    $sql .= " AND o.status = 'completed'";
    $count_sql .= " AND o.status = 'completed'";
    
    // Фильтрация по дате
    if ($dateFrom && $dateTo) {
        $sql .= " AND o.created_at BETWEEN ? AND ?";
        $count_sql .= " AND o.created_at BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    } else if ($dateFrom) {
        $sql .= " AND o.created_at >= ?";
        $count_sql .= " AND o.created_at >= ?";
        $params[] = $dateFrom;
    } else if ($dateTo) {
        $sql .= " AND o.created_at <= ?";
        $count_sql .= " AND o.created_at <= ?";
        $params[] = $dateTo;
    }
    
    // Фильтрация по категории
    if ($category) {
        $sql .= " AND p.category = ?";
        $count_sql .= " AND p.category = ?";
        $params[] = $category;
    }
    
    // Группировка и сортировка
    $sql .= " GROUP BY p.id, p.name, p.category ORDER BY total_amount DESC";
    
    // Подготавливаем и выполняем запрос для подсчета общего количества записей
    $stmt = mysqli_prepare($conn, $count_sql);
    
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_stmt_get_result($stmt);
        $total_count = mysqli_fetch_assoc($count_result)['total_count'];
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing count statement: " . mysqli_error($conn));
        $total_count = 0;
    }
    
    // Добавляем пагинацию
    $total_pages = ceil($total_count / $per_page);
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $per_page;
    
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $per_page;
    
    // Подготавливаем и выполняем запрос для получения данных
    $stmt = mysqli_prepare($conn, $sql);
    $data = [];
    
    if ($stmt) {
        if (!empty($params)) {
            $types = str_repeat('s', count($params) - 2) . 'ii'; // Типы для offset и limit - целые числа
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'category' => $row['category'],
                'quantity' => (int)$row['quantity'],
                'total_amount' => (float)$row['total_amount'],
                'avg_price' => (float)$row['avg_price']
            ];
        }
        
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing data statement: " . mysqli_error($conn));
    }
    
    // Возвращаем результат
    return [
        'data' => $data,
        'pagination' => [
            'total_records' => $total_count,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ]
    ];
}

/**
 * Получает данные о продажах по месяцам для построения графика
 * 
 * @param int $year Год для получения данных (по умолчанию текущий год)
 * @param string $dateFrom Начальная дата для фильтрации (опционально)
 * @param string $dateTo Конечная дата для фильтрации (опционально)
 * @return array Массив с данными о продажах по месяцам
 */
function getMonthlySalesData($year = null, $dateFrom = null, $dateTo = null) {
    global $conn;
    
    // Если год не указан, используем текущий год
    if (!$year) {
        $year = date('Y');
    }
    
    // Инициализируем массив с нулевыми значениями для всех месяцев
    $monthlySales = array_fill(0, 12, 0);
    
    // Подготавливаем SQL запрос с использованием подготовленных выражений
    $sql = "SELECT 
                MONTH(o.created_at) as month, 
                SUM(oi.price * oi.quantity) as total 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.status = 'completed' AND YEAR(o.created_at) = ?";
    
    $params = [$year];
    $types = 'i'; // i для целого числа (год)
    
    // Добавляем фильтры по датам, если они указаны
    if ($dateFrom) {
        $sql .= " AND o.created_at >= ?";
        $params[] = $dateFrom;
        $types .= 's'; // s для строки (дата)
    }
    
    if ($dateTo) {
        $sql .= " AND o.created_at <= ?";
        $params[] = $dateTo;
        $types .= 's'; // s для строки (дата)
    }
    
    $sql .= " GROUP BY MONTH(o.created_at)";
    
    // Подготавливаем и выполняем запрос
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Заполняем массив данными из результата запроса
        while ($row = mysqli_fetch_assoc($result)) {
            $month = (int)$row['month'];
            if ($month >= 1 && $month <= 12) {
                $monthlySales[$month - 1] = (float)$row['total'];
            }
        }
        
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement in getMonthlySalesData: " . mysqli_error($conn));
    }
    
    return [
        'monthlySales' => $monthlySales
    ];
}

/**
 * Создает PHP-обработчик для получения отфильтрованных данных отчета через AJAX
 * 
 * @param string $dateFrom Дата начала периода
 * @param string $dateTo Дата окончания периода
 * @param string $category Название категории
 * @param int $page Номер страницы
 * @param int $per_page Количество записей на странице
 * @return array Данные для отчета
 */
function getFilteredReportData($dateFrom, $dateTo, $category, $page = 1, $per_page = 10) {
    // Устанавливаем максимальное время выполнения запроса
    set_time_limit(60);
    
    // Проверяем входные параметры
    $page = max(1, (int)$page);
    $per_page = max(1, min(100, (int)$per_page));
    
    try {
        // Получаем основные данные для отчета с пагинацией
        $reportResult = getProductsSalesReport($dateFrom, $dateTo, $category, $page, $per_page);
        
        // Получаем статистику с использованием оптимизированных запросов
        $totalSales = getTotalSalesAmount($dateFrom, $dateTo, $category);
        $totalItems = getTotalSoldItemsCount($dateFrom, $dateTo, $category);
        $totalOrders = getCompletedOrdersCount($dateFrom, $dateTo);
        
        // Формируем результат
        return [
            'reportData' => $reportResult['data'],
            'pagination' => $reportResult['pagination'],
            'totalSales' => $totalSales,
            'totalItems' => $totalItems,
            'totalOrders' => $totalOrders
        ];
    } catch (Exception $e) {
        // Логируем ошибку
        error_log('Error in getFilteredReportData: ' . $e->getMessage());
        throw $e; // Перебрасываем исключение для обработки на уровне выше
    }
}

/**
 * Получить элемент корзины по его ID
 * 
 * @param int|null $user_id ID пользователя (если авторизован)
 * @param string $session_id ID сессии
 * @param int $cart_id ID элемента корзины
 * @return array|false Данные элемента корзины или false, если не найден
 */
function getCartItemById($user_id = null, $session_id = null, $cart_id = 0) {
    global $conn;
    
    if (!$session_id) $session_id = session_id();
    
    // Формируем условие для поиска по user_id или session_id
    if ($user_id) {
        $sql = "SELECT c.*, p.name, p.image, p.stock as available_quantity, c.quantity * p.price as subtotal
                FROM cart c 
                JOIN product p ON c.product_id = p.id 
                WHERE c.id = " . (int)$cart_id . " AND c.user_id = " . (int)$user_id;
    } else {
        $sql = "SELECT c.*, p.name, p.image, p.stock as available_quantity, c.quantity * p.price as subtotal
                FROM cart c 
                JOIN product p ON c.product_id = p.id 
                WHERE c.id = " . (int)$cart_id . " AND c.session_id = '" . mysqli_real_escape_string($conn, $session_id) . "'";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

/**
 * Создание таблицы для хранения токенов "Запомнить меня", если она не существует
 */
function createRememberTokensTableIfNotExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($conn, $sql);
}

/**
 * Генерация токена для функции "Запомнить меня"
 * 
 * @param int $user_id ID пользователя
 * @param int $days Количество дней, на которое действует токен
 * @return string Сгенерированный токен
 */
function generateRememberToken($user_id, $days = 30) {
    global $conn;
    
    // Создаем таблицу, если её не существует
    createRememberTokensTableIfNotExists();
    
    // Генерируем уникальный токен
    $token = bin2hex(random_bytes(32));
    
    // Вычисляем дату истечения токена
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$days} days"));
    
    // Удаляем старые токены этого пользователя
    $sql = "DELETE FROM remember_tokens WHERE user_id = " . (int)$user_id;
    mysqli_query($conn, $sql);
    
    // Сохраняем новый токен в базе данных
    $sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (
        " . (int)$user_id . ",
        '" . mysqli_real_escape_string($conn, $token) . "',
        '" . $expires_at . "'
    )";
    
    if (mysqli_query($conn, $sql)) {
        return $token;
    }
    
    return '';
}

/**
 * Проверка токена "Запомнить меня" и авторизация пользователя
 * 
 * @param string $token Токен из куки
 * @return array|null Данные пользователя или null, если токен недействителен
 */
function validateRememberToken($token) {
    global $conn;
    
    if (empty($token)) {
        return null;
    }
    
    // Создаем таблицу, если её не существует
    createRememberTokensTableIfNotExists();
    
    // Ищем токен в базе данных
    $sql = "SELECT rt.user_id, u.fullname, u.email, u.phone, u.login, u.role 
            FROM remember_tokens rt 
            JOIN users u ON rt.user_id = u.id 
            WHERE rt.token = '" . mysqli_real_escape_string($conn, $token) . "' 
            AND rt.expires_at > NOW()";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        return [
            'user_id' => $user['user_id'],
            'user_fullname' => $user['fullname'],
            'user_email' => $user['email'],
            'user_phone' => $user['phone'],
            'user_login' => $user['login'],
            'user_role' => $user['role']
        ];
    }
    
    // Если токен не найден или истек, удаляем его из куки
    return null;
}

/**
 * Удаление токена "Запомнить меня" при выходе из системы
 * 
 * @param int $user_id ID пользователя
 */
function removeRememberToken($user_id) {
    global $conn;
    
    $sql = "DELETE FROM remember_tokens WHERE user_id = " . (int)$user_id;
    mysqli_query($conn, $sql);
}

/**
 * Получение подкатегорий для указанной родительской категории
 * 
 * @param int|string $parent Идентификатор или название родительской категории
 * @return array Массив подкатегорий
 */
function getSubcategories($parent) {
    global $conn;
    
    // Если передано название категории, находим её ID
    if (!is_numeric($parent)) {
        $sql = "SELECT id FROM categories WHERE name = '" . mysqli_real_escape_string($conn, $parent) . "'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $category = mysqli_fetch_assoc($result);
            $parent_id = $category['id'];
        } else {
            return [];
        }
    } else {
        $parent_id = (int)$parent;
    }
    
    // Получаем подкатегории
    $sql = "SELECT * FROM categories WHERE parent_id = $parent_id ORDER BY name";
    $result = mysqli_query($conn, $sql);
    $subcategories = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = $row;
        }
    }
    
    return $subcategories;
}

/**
 * Получение популярных подкатегорий для указанной родительской категории
 * 
 * @param string $category Название категории
 * @param int $limit Количество подкатегорий (по умолчанию 5)
 * @return array Массив подкатегорий с количеством товаров
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
 * Удаление заказа и связанных с ним данных
 * 
 * @param int $order_id ID заказа
 * @return array Результат операции
 */
function deleteOrder($order_id) {
    global $conn;
    
    // Начинаем транзакцию
    mysqli_begin_transaction($conn);
    
    try {
        // Получаем текущий статус заказа перед удалением
        $status_query = "SELECT status FROM orders WHERE id = " . (int)$order_id;
        $status_result = mysqli_query($conn, $status_query);
        $status_row = mysqli_fetch_assoc($status_result);
        
        // Проверяем, можно ли удалить заказ
        // Если заказ уже выполнен или закрыт, запрещаем удаление
        if ($status_row && ($status_row['status'] === 'completed' || $status_row['status'] === 'closed')) {
            return [
                'success' => false,
                'message' => 'Невозможно удалить заказ в статусе "' . 
                             ($status_row['status'] === 'completed' ? 'Выполнен' : 'Закрыт') . '"'
            ];
        }
        
        // Удаляем элементы заказа (у нас уже есть внешний ключ ON DELETE CASCADE,
        // но для надежности удалим явно)
        $delete_items_sql = "DELETE FROM order_items WHERE order_id = " . (int)$order_id;
        if (!mysqli_query($conn, $delete_items_sql)) {
            throw new Exception("Ошибка при удалении элементов заказа: " . mysqli_error($conn));
        }
        
        // Удаляем сам заказ
        $delete_order_sql = "DELETE FROM orders WHERE id = " . (int)$order_id;
        if (!mysqli_query($conn, $delete_order_sql)) {
            throw new Exception("Ошибка при удалении заказа: " . mysqli_error($conn));
        }
        
        // Фиксируем транзакцию
        mysqli_commit($conn);
        
        return [
            'success' => true,
            'message' => 'Заказ #' . $order_id . ' успешно удален'
        ];
        
    } catch (Exception $e) {
        // Откатываем транзакцию в случае ошибки
        mysqli_rollback($conn);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
 