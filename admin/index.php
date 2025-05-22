<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Если пользователь не авторизован, перенаправляем на страницу входа
if(!isset($_SESSION['user_id'])) {
    header("Location: ../account/login.php");
    exit;
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не найден или не является администратором, перенаправляем на главную
if (!$user || $user['role'] !== 'Администратор') {
    header("Location: /");
    exit;
}

// Получаем всех пользователей для вкладки пользователей
function getAllUsers() {
    global $conn;
    
    $sql = "SELECT id, fullname, email, phone, login, role FROM users ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    $users = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

$users = getAllUsers();

// Получаем номер текущей страницы для пагинации пользователей
$users_page = isset($_GET['users_page']) ? (int)$_GET['users_page'] : 1;
$users_page = max(1, $users_page); // Убеждаемся, что страница не меньше 1

// Получаем пользователей с пагинацией
$users_per_page = 10; // Количество пользователей на странице
$users_data = getPaginatedUsers($users_page, $users_per_page);
$users = $users_data['users'];
$users_pagination = $users_data['pagination'];

// Получаем номер текущей страницы для пагинации заказов
$orders_page = isset($_GET['orders_page']) ? (int)$_GET['orders_page'] : 1;
$orders_page = max(1, $orders_page); // Убеждаемся, что страница не меньше 1

// Получаем заказы с пагинацией
$orders_per_page = 10; // Количество заказов на странице
$orders_data = getPaginatedOrders($orders_page, $orders_per_page);
$orders = $orders_data['orders'];
$orders_pagination = $orders_data['pagination'];

// Обработка добавления нового товара
$product_added = false;
$product_error = '';
$product_updated = false;
$product_deleted = false;

// Переменные для управления категориями
$category_added = false;
$category_updated = false;
$category_deleted = false;
$category_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Получаем данные из формы
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $color = $_POST['color'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $discount = $_POST['discount'] ?? 0;
    
    // Проверяем и загружаем изображение
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/products/';
        
        // Создаем директорию, если она не существует
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Перемещаем загруженный файл
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image = '/img/products/' . $file_name;
        } else {
            $product_error = 'Ошибка при загрузке изображения';
        }
    }
    
    // Если нет ошибок, добавляем товар в базу данных
    if (empty($product_error)) {
        $result = addProduct($name, $description, $price, $category, $color, $image, $sku, $stock, $is_new, $is_bestseller, $discount);
        
        if ($result['success']) {
            $product_added = true;
        } else {
            $product_error = $result['message'];
        }
    }
}

// Обработка редактирования товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Получаем данные из формы
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'category' => $_POST['category'] ?? '',
        'color' => $_POST['color'] ?? '',
        'sku' => $_POST['sku'] ?? '',
        'stock' => $_POST['stock'] ?? 0,
        'is_new' => isset($_POST['is_new']) ? 1 : 0,
        'is_bestseller' => isset($_POST['is_bestseller']) ? 1 : 0,
        'discount' => $_POST['discount'] ?? 0
    ];
    
    // Проверяем и загружаем изображение, если оно было загружено
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/products/';
        
        // Создаем директорию, если она не существует
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Перемещаем загруженный файл
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $data['image'] = '/img/products/' . $file_name;
        } else {
            $product_error = 'Ошибка при загрузке изображения';
        }
    }
    
    // Если нет ошибок, обновляем товар в базе данных
    if (empty($product_error)) {
        $result = updateProduct($product_id, $data);
        
        if ($result['success']) {
            $product_updated = true;
        } else {
            $product_error = $result['message'];
        }
    }
}

// Обработка удаления товара
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && $_GET['tab'] === 'products') {
    $product_id = (int)$_GET['id'];
    $result = deleteProduct($product_id);
    
    if ($result['success']) {
        $product_deleted = true;
        // Перенаправляем на страницу товаров
        header('Location: index.php?tab=products&deleted=true');
        exit;
    } else {
        $product_error = $result['message'];
    }
}

// Обработка добавления новой категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    // Получаем данные из формы
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Проверяем и загружаем изображение
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/categories/';
        
        // Создаем директорию, если она не существует
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Перемещаем загруженный файл
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image = '/img/categories/' . $file_name;
        } else {
            $category_error = 'Ошибка при загрузке изображения';
        }
    }
    
    // Если нет ошибок, добавляем категорию в базу данных
    if (empty($category_error)) {
        $result = addCategory($name, $description, $image);
        
        if ($result['success']) {
            $category_added = true;
        } else {
            $category_error = $result['message'];
        }
    }
}

// Обработка редактирования категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    
    // Получаем данные из формы
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? ''
    ];
    
    // Проверяем и загружаем изображение, если оно было загружено
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/categories/';
        
        // Создаем директорию, если она не существует
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Перемещаем загруженный файл
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $data['image'] = '/img/categories/' . $file_name;
        } else {
            $category_error = 'Ошибка при загрузке изображения';
        }
    }
    
    // Если нет ошибок, обновляем категорию в базе данных
    if (empty($category_error)) {
        $result = updateCategory($category_id, $data);
        
        if ($result['success']) {
            $category_updated = true;
        } else {
            $category_error = $result['message'];
        }
    }
}

// Обработка удаления категории
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && $_GET['tab'] === 'categories') {
    $category_id = (int)$_GET['id'];
    $result = deleteCategory($category_id);
    
    if ($result['success']) {
        $category_deleted = true;
        // Перенаправляем на страницу категорий
        header('Location: index.php?tab=categories&deleted=true');
        exit;
    } else {
        $category_error = $result['message'];
    }
}

// Проверяем, есть ли сообщение в сессии
$message = '';
$messageType = '';

if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    
    // Очищаем сообщение из сессии
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Получаем активную вкладку из GET-параметра или устанавливаем по умолчанию
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<section class="admin-section">
    <div class="container">
        <div class="row">
            <!-- Боковое меню админ-панели -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-card profile-sidebar">
                    <div class="profile-menu-header">
                        <h5 class="mb-0">Админ-панель</h5>
                        <a href="../account/logout.php" class="text-danger small" title="Выйти">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                    <ul class="profile-menu">
                        <li class="profile-menu-item <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">
                            <a href="index.php?tab=dashboard" class="tab-link" data-tab="dashboard">
                                <i class="fas fa-tachometer-alt"></i>
                                Панель управления
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'products' ? 'active' : ''; ?>">
                            <a href="index.php?tab=products" class="tab-link" data-tab="products">
                                <i class="fas fa-box"></i>
                                Управление товарами
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'categories' ? 'active' : ''; ?>">
                            <a href="index.php?tab=categories" class="tab-link" data-tab="categories">
                                <i class="fas fa-tags"></i>
                                Категории
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                            <a href="index.php?tab=orders" class="tab-link" data-tab="orders">
                                <i class="fas fa-shopping-bag"></i>
                                Заказы
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'users' ? 'active' : ''; ?>">
                            <a href="index.php?tab=users" class="tab-link" data-tab="users">
                                <i class="fas fa-users"></i>
                                Пользователи
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'reports' ? 'active' : ''; ?>">
                            <a href="index.php?tab=reports" class="tab-link" data-tab="reports">
                                <i class="fas fa-chart-bar"></i>
                                Отчеты
                            </a>
                        </li>
                        <li class="profile-menu-item <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                            <a href="index.php?tab=settings" class="tab-link" data-tab="settings">
                                <i class="fas fa-cog"></i>
                                Настройки
                            </a>
                        </li>
                        <li class="profile-menu-item">
                            <a href="/account/profile.php">
                                <i class="fas fa-user"></i>
                                Личный кабинет
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Основное содержимое -->
            <div class="col-lg-9 col-md-8">
                <!-- Сообщения -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-4 mb-4" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Вкладки -->
                <div class="tab-content">
                    <!-- Вкладка: Панель управления -->
                    <div class="tab-pane <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                        <!-- Приветствие -->
                        <div class="profile-main-card">
                            <div class="profile-header">
                                <p class="profile-welcome">Добро пожаловать в панель администратора,</p>
                                <h2 class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                                <p>Здесь вы можете управлять товарами, категориями, заказами и пользователями.</p>
                                
                                <!-- Статистика -->
                                <div class="profile-stats">
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo count(getProducts()); ?></div>
                                        <div class="stat-label">Товаров</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo count(getCategories()); ?></div>
                                        <div class="stat-label">Категорий</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo count($orders); ?></div>
                                        <div class="stat-label">Заказов</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo count($users); ?></div>
                                        <div class="stat-label">Пользователей</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="profile-body">
                                <div class="profile-content">
                                    <h4 class="profile-section-title">Быстрые действия</h4>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 rounded-4 shadow-sm">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                                            <i class="fas fa-plus text-primary"></i>
                                                        </div>
                                                        <h5 class="card-title mb-0">Добавить товар</h5>
                                                    </div>
                                                    <p class="card-text text-muted">Создайте новый товар в каталоге</p>
                                                    <a href="index.php?tab=products&action=add" class="btn btn-sm btn-outline-primary rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 rounded-4 shadow-sm">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                                            <i class="fas fa-list text-success"></i>
                                                        </div>
                                                        <h5 class="card-title mb-0">Управление заказами</h5>
                                                    </div>
                                                    <p class="card-text text-muted">Просмотр и управление заказами</p>
                                                    <a href="index.php?tab=orders" class="btn btn-sm btn-outline-success rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 rounded-4 shadow-sm">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                                            <i class="fas fa-users text-info"></i>
                                                        </div>
                                                        <h5 class="card-title mb-0">Пользователи</h5>
                                                    </div>
                                                    <p class="card-text text-muted">Управление пользователями сайта</p>
                                                    <a href="index.php?tab=users" class="btn btn-sm btn-outline-info rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 rounded-4 shadow-sm">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                                            <i class="fas fa-cog text-warning"></i>
                                                        </div>
                                                        <h5 class="card-title mb-0">Настройки сайта</h5>
                                                    </div>
                                                    <p class="card-text text-muted">Общие настройки сайта</p>
                                                    <a href="index.php?tab=settings" class="btn btn-sm btn-outline-warning rounded-pill">Перейти <i class="fas fa-arrow-right ms-1"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Управление товарами -->
                    <div class="tab-pane <?php echo $activeTab === 'products' ? 'active' : ''; ?>" id="products">
                        <div class="profile-main-card">
                            <div class="profile-header">
                                <h2 class="profile-name">Управление товарами</h2>
                                <p>Добавление, редактирование и удаление товаров</p>
                            </div>
                            
                            <div class="profile-body">
                                <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                                    <!-- Форма добавления товара -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h4 class="profile-section-title mb-0">Добавление нового товара</h4>
                                            <a href="index.php?tab=products" class="btn btn-outline-secondary rounded-pill">
                                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                            </a>
                                        </div>
                                        
                                        <?php if ($product_added): ?>
                                            <div class="alert alert-success rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Товар успешно добавлен</h5>
                                                        <p class="mb-0">Новый товар был успешно добавлен в базу данных.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($product_error)): ?>
                                            <div class="alert alert-danger rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Ошибка</h5>
                                                        <p class="mb-0"><?php echo htmlspecialchars($product_error); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form action="index.php?tab=products&action=add" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Основная информация</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Название товара *</label>
                                                                <input type="text" class="form-control" id="name" name="name" required>
                                                                <div class="invalid-feedback">Пожалуйста, введите название товара</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Описание</label>
                                                                <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="price" class="form-label">Цена *</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                                                        <span class="input-group-text">₽</span>
                                                                        <div class="invalid-feedback">Пожалуйста, укажите цену</div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="category" class="form-label">Категория *</label>
                                                                    <select class="form-select" id="category" name="category" required>
                                                                        <option value="">Выберите категорию</option>
                                                                        <?php 
                                                                        $categories = getCategories();
                                                                        foreach ($categories as $cat): 
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <div class="invalid-feedback">Пожалуйста, выберите категорию</div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="color" class="form-label">Цвет *</label>
                                                                    <select class="form-select" id="color" name="color" required>
                                                                        <option value="">Выберите цвет</option>
                                                                        <?php 
                                                                        $colors = getColors();
                                                                        foreach ($colors as $color_key => $color_name): 
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($color_key); ?>"><?php echo htmlspecialchars($color_name); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <div class="invalid-feedback">Пожалуйста, выберите цвет</div>
                                                                </div>
                                                                
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="sku" class="form-label">Артикул (SKU)</label>
                                                                    <input type="text" class="form-control" id="sku" name="sku">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Дополнительная информация</h5>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="stock" class="form-label">Количество на складе</label>
                                                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-4 mb-3">
                                                                    <label for="discount" class="form-label">Скидка (%)</label>
                                                                    <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" value="0">
                                                                </div>
                                                                
                                                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" id="is_new" name="is_new">
                                                                        <label class="form-check-label" for="is_new">
                                                                            Новинка
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" id="is_bestseller" name="is_bestseller">
                                                                        <label class="form-check-label" for="is_bestseller">
                                                                            Бестселлер
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Изображение товара</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="image" class="form-label">Загрузить изображение</label>
                                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                                <div class="form-text">Рекомендуемый размер: 800x800 пикселей</div>
                                                            </div>
                                                            
                                                            <div class="image-preview mt-3 text-center">
                                                                <img id="preview" src="../img/products/placeholder.png" alt="Предпросмотр" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="add_product" class="btn btn-primary btn-lg rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Добавить товар
                                                        </button>
                                                        <a href="index.php?tab=products" class="btn btn-outline-secondary rounded-pill">Отмена</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
                                    <!-- Форма редактирования товара -->
                                    <?php
                                    $product_id = (int)$_GET['id'];
                                    $product = getProductById($product_id);
                                    
                                    if (!$product) {
                                        echo '<div class="alert alert-danger rounded-4 mb-4">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                </div>
                                                <div>
                                                    <h5 class="alert-heading">Ошибка</h5>
                                                    <p class="mb-0">Товар не найден</p>
                                                </div>
                                            </div>
                                        </div>';
                                    } else {
                                    ?>
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h4 class="profile-section-title mb-0">Редактирование товара</h4>
                                            <a href="index.php?tab=products" class="btn btn-outline-secondary rounded-pill">
                                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                            </a>
                                        </div>
                                        
                                        <?php if ($product_updated): ?>
                                            <div class="alert alert-success rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Товар обновлен</h5>
                                                        <p class="mb-0">Информация о товаре была успешно обновлена.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($product_error)): ?>
                                            <div class="alert alert-danger rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Ошибка</h5>
                                                        <p class="mb-0"><?php echo htmlspecialchars($product_error); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form action="index.php?tab=products&action=edit&id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Основная информация</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Название товара *</label>
                                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                                <div class="invalid-feedback">Пожалуйста, введите название товара</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Описание</label>
                                                                <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="price" class="form-label">Цена *</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                                                        <span class="input-group-text">₽</span>
                                                                        <div class="invalid-feedback">Пожалуйста, укажите цену</div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="category" class="form-label">Категория *</label>
                                                                    <select class="form-select" id="category" name="category" required>
                                                                        <option value="">Выберите категорию</option>
                                                                        <?php 
                                                                        $categories = getCategories();
                                                                        foreach ($categories as $cat): 
                                                                            $selected = ($cat['name'] === $product['category']) ? 'selected' : '';
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <div class="invalid-feedback">Пожалуйста, выберите категорию</div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="color" class="form-label">Цвет *</label>
                                                                    <select class="form-select" id="color" name="color" required>
                                                                        <option value="">Выберите цвет</option>
                                                                        <?php 
                                                                        $colors = getColors();
                                                                        foreach ($colors as $color_key => $color_name): 
                                                                        ?>
                                                                        <option value="<?php echo htmlspecialchars($color_key); ?>" <?php echo ($color_key === $product['color']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($color_name); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <div class="invalid-feedback">Пожалуйста, выберите цвет</div>
                                                                </div>
                                                                
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="sku" class="form-label">Артикул (SKU)</label>
                                                                    <input type="text" class="form-control" id="sku" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Дополнительная информация</h5>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="stock" class="form-label">Количество на складе</label>
                                                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-4 mb-3">
                                                                    <label for="discount" class="form-label">Скидка (%)</label>
                                                                    <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" value="<?php echo $product['discount']; ?>">
                                                                </div>
                                                                
                                                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" id="is_new" name="is_new" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" for="is_new">
                                                                            Новинка
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" id="is_bestseller" name="is_bestseller" <?php echo $product['is_bestseller'] ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" for="is_bestseller">
                                                                            Бестселлер
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Изображение товара</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="image" class="form-label">Загрузить новое изображение</label>
                                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                                <div class="form-text">Оставьте пустым, чтобы сохранить текущее изображение</div>
                                                            </div>
                                                            
                                                            <div class="image-preview mt-3 text-center">
                                                                <?php if (!empty($product['image'])): ?>
                                                                    <img id="preview" src="<?php echo htmlspecialchars($product['image']); ?>" alt="Изображение товара" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                                <?php else: ?>
                                                                    <img id="preview" src="../img/products/placeholder.png" alt="Нет изображения" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="edit_product" class="btn btn-primary btn-lg rounded-pill">
                                                            <i class="fas fa-save me-2"></i>Сохранить изменения
                                                        </button>
                                                        <a href="index.php?tab=products" class="btn btn-outline-secondary rounded-pill">Отмена</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php } ?>
                                <?php else: ?>
                                    <!-- Список товаров -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="profile-section-title mb-0">Список товаров</h4>
                                        <a href="index.php?tab=products&action=add" class="btn btn-primary rounded-pill">
                                            <i class="fas fa-plus me-2"></i>Добавить товар
                                        </a>
                                    </div>
                                    
                                    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'true'): ?>
                                        <div class="alert alert-success rounded-4 mb-4">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                </div>
                                                <div>
                                                    <h5 class="alert-heading">Товар удален</h5>
                                                    <p class="mb-0">Товар был успешно удален из базы данных.</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Получаем список всех товаров
                                    $products = getProducts();
                                    if (count($products) > 0): 
                                    ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Изображение</th>
                                                    <th>Название</th>
                                                    <th>Категория</th>
                                                    <th>Цвет</th>
                                                    <th>Цена</th>
                                                    <th>Наличие</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td><?php echo $product['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($product['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail" style="max-width: 50px;">
                                                        <?php else: ?>
                                                            <div class="bg-light text-center p-2 rounded" style="width: 50px; height: 50px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                        <?php if ($product['is_new']): ?>
                                                            <span class="badge bg-info ms-1">Новинка</span>
                                                        <?php endif; ?>
                                                        <?php if ($product['is_bestseller']): ?>
                                                            <span class="badge bg-warning ms-1">Хит</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $colors = getColors();
                                                        $colorCodes = getColorCodes();
                                                        $color = isset($product['color']) && $product['color'] ? $product['color'] : 'black'; // Значение по умолчанию
                                                        $colorName = isset($colors[$color]) ? $colors[$color] : $color;
                                                        $colorCode = isset($colorCodes[$color]) ? $colorCodes[$color] : '#cccccc';
                                                        ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="color-dot me-2" style="background-color: <?php echo $colorCode; ?>"></div>
                                                            <?php echo htmlspecialchars($colorName); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($product['discount'] > 0): ?>
                                                            <span class="text-decoration-line-through text-muted me-1">
                                                                <?php echo number_format($product['price'], 0, ',', ' '); ?> ₽
                                                            </span>
                                                            <span class="text-danger">
                                                                <?php echo number_format($product['price'] * (1 - $product['discount'] / 100), 0, ',', ' '); ?> ₽
                                                            </span>
                                                        <?php else: ?>
                                                            <?php echo number_format($product['price'], 0, ',', ' '); ?> ₽
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($product['stock'] > 0): ?>
                                                            <span class="badge bg-success"><?php echo $product['stock']; ?> шт.</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Нет в наличии</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="index.php?tab=products&action=edit&id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="index.php?tab=products&action=delete&id=<?php echo $product['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить этот товар?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info rounded-4 mb-4">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading">Нет товаров</h5>
                                                <p class="mb-0">В базе данных пока нет товаров. Нажмите кнопку "Добавить товар", чтобы создать первый товар.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Категории -->
                    <div class="tab-pane <?php echo $activeTab === 'categories' ? 'active' : ''; ?>" id="categories">
                        <div class="profile-main-card">
                            <div class="profile-header">
                                <h2 class="profile-name">Категории товаров</h2>
                                <p>Управление категориями товаров</p>
                            </div>
                            
                            <div class="profile-body">
                                <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                                    <!-- Форма добавления категории -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h4 class="profile-section-title mb-0">Добавление новой категории</h4>
                                            <a href="index.php?tab=categories" class="btn btn-outline-secondary rounded-pill">
                                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                            </a>
                                        </div>
                                        
                                        <?php if ($category_added): ?>
                                            <div class="alert alert-success rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Категория успешно добавлена</h5>
                                                        <p class="mb-0">Новая категория была успешно добавлена в базу данных.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($category_error)): ?>
                                            <div class="alert alert-danger rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Ошибка</h5>
                                                        <p class="mb-0"><?php echo htmlspecialchars($category_error); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form action="index.php?tab=categories&action=add" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Информация о категории</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Название категории *</label>
                                                                <input type="text" class="form-control" id="name" name="name" required>
                                                                <div class="invalid-feedback">Пожалуйста, введите название категории</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Описание</label>
                                                                <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Изображение категории</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="image" class="form-label">Загрузить изображение</label>
                                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                                <div class="form-text">Рекомендуемый размер: 800x600 пикселей</div>
                                                            </div>
                                                            
                                                            <div class="image-preview mt-3 text-center">
                                                                <img id="preview" src="../img/categories/placeholder.png" alt="Предпросмотр" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="add_category" class="btn btn-primary btn-lg rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Добавить категорию
                                                        </button>
                                                        <a href="index.php?tab=categories" class="btn btn-outline-secondary rounded-pill">Отмена</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
                                    <!-- Форма редактирования категории -->
                                    <?php
                                    $category_id = (int)$_GET['id'];
                                    $category = getCategoryById($category_id);
                                    
                                    if (!$category) {
                                        echo '<div class="alert alert-danger rounded-4 mb-4">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                </div>
                                                <div>
                                                    <h5 class="alert-heading">Ошибка</h5>
                                                    <p class="mb-0">Категория не найдена</p>
                                                </div>
                                            </div>
                                        </div>';
                                    } else {
                                    ?>
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h4 class="profile-section-title mb-0">Редактирование категории</h4>
                                            <a href="index.php?tab=categories" class="btn btn-outline-secondary rounded-pill">
                                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                                            </a>
                                        </div>
                                        
                                        <?php if ($category_updated): ?>
                                            <div class="alert alert-success rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Категория обновлена</h5>
                                                        <p class="mb-0">Информация о категории была успешно обновлена.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($category_error)): ?>
                                            <div class="alert alert-danger rounded-4 mb-4">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="alert-heading">Ошибка</h5>
                                                        <p class="mb-0"><?php echo htmlspecialchars($category_error); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form action="index.php?tab=categories&action=edit&id=<?php echo $category_id; ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Информация о категории</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="name" class="form-label">Название категории *</label>
                                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                                <div class="invalid-feedback">Пожалуйста, введите название категории</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="description" class="form-label">Описание</label>
                                                                <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div class="card-body p-4">
                                                            <h5 class="card-title mb-4">Изображение категории</h5>
                                                            
                                                            <div class="mb-3">
                                                                <label for="image" class="form-label">Загрузить новое изображение</label>
                                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                                <div class="form-text">Оставьте пустым, чтобы сохранить текущее изображение</div>
                                                            </div>
                                                            
                                                            <div class="image-preview mt-3 text-center">
                                                                <?php if (!empty($category['image'])): ?>
                                                                    <img id="preview" src="<?php echo htmlspecialchars($category['image']); ?>" alt="Изображение категории" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                                <?php else: ?>
                                                                    <img id="preview" src="../img/categories/placeholder.png" alt="Нет изображения" class="img-fluid rounded-3 border" style="max-height: 200px;">
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" name="edit_category" class="btn btn-primary btn-lg rounded-pill">
                                                            <i class="fas fa-save me-2"></i>Сохранить изменения
                                                        </button>
                                                        <a href="index.php?tab=categories" class="btn btn-outline-secondary rounded-pill">Отмена</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php } ?>
                                <?php else: ?>
                                    <!-- Список категорий -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="profile-section-title mb-0">Список категорий</h4>
                                        <a href="index.php?tab=categories&action=add" class="btn btn-primary rounded-pill">
                                            <i class="fas fa-plus me-2"></i>Добавить категорию
                                        </a>
                                    </div>
                                    
                                    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'true'): ?>
                                        <div class="alert alert-success rounded-4 mb-4">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                </div>
                                                <div>
                                                    <h5 class="alert-heading">Категория удалена</h5>
                                                    <p class="mb-0">Категория была успешно удалена из базы данных.</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Получаем список всех категорий
                                    $categories = getCategories();
                                    if (count($categories) > 0): 
                                    ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Изображение</th>
                                                    <th>Название</th>
                                                    <th>Описание</th>
                                                    <th>Товаров</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($category['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-thumbnail" style="max-width: 50px;">
                                                        <?php else: ?>
                                                            <div class="bg-light text-center p-2 rounded" style="width: 50px; height: 50px;">
                                                                <i class="fas fa-folder text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td><?php echo htmlspecialchars(mb_substr($category['description'] ?? '', 0, 50)) . (mb_strlen($category['description'] ?? '') > 50 ? '...' : ''); ?></td>
                                                    <td>
                                                        <?php
                                                        // Подсчет товаров в категории
                                                        $products_count_sql = "SELECT COUNT(*) as count FROM product WHERE category = '" . mysqli_real_escape_string($conn, $category['name']) . "'";
                                                        $products_count_result = mysqli_query($conn, $products_count_sql);
                                                        $products_count = mysqli_fetch_assoc($products_count_result)['count'];
                                                        ?>
                                                        <span class="badge bg-primary rounded-pill"><?php echo $products_count; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="index.php?tab=categories&action=edit&id=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="index.php?tab=categories&action=delete&id=<?php echo $category['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить эту категорию?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info rounded-4 mb-4">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading">Нет категорий</h5>
                                                <p class="mb-0">В базе данных пока нет категорий. Нажмите кнопку "Добавить категорию", чтобы создать первую категорию.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Заказы -->
                    <div class="tab-pane <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" id="orders">
                        <div class="profile-main-card">
                            <div class="profile-header">
                                <h2 class="profile-name">Управление заказами</h2>
                                <p>Просмотр и управление заказами пользователей</p>
                            </div>
                            
                            <div class="profile-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="profile-section-title mb-0">Список заказов</h4>
                                </div>
                                
                                <?php if (count($orders) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Пользователь</th>
                                                    <th>Сумма</th>
                                                    <th>Статус</th>
                                                    <th>Дата</th>
                                                    <th>Адрес</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td><?php echo $order['id']; ?></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($order['fullname'] ?? 'Нет данных'); ?><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($order['email'] ?? 'Нет email'); ?></small>
                                                        </td>
                                                        <td><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</td>
                                                        <td>
                                                            <?php
                                                            $statusClass = '';
                                                            $statusText = '';
                                                            
                                                            switch($order['status'] ?? 'unknown') {
                                                                case 'pending':
                                                                    $statusClass = 'bg-warning';
                                                                    $statusText = 'Ожидает';
                                                                    break;
                                                                case 'processing':
                                                                    $statusClass = 'bg-info';
                                                                    $statusText = 'Обрабатывается';
                                                                    break;
                                                                case 'completed':
                                                                    $statusClass = 'bg-success';
                                                                    $statusText = 'Выполнен';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusClass = 'bg-danger';
                                                                    $statusText = 'Отменен';
                                                                    break;
                                                                case 'closed':
                                                                    $statusClass = 'bg-secondary';
                                                                    $statusText = 'Закрыт';
                                                                    break;
                                                                default:
                                                                    $statusClass = 'bg-secondary';
                                                                    $statusText = 'Неизвестно';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                                                <?php echo $statusText; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $order['created_at'] ? date('d.m.Y H:i', strtotime($order['created_at'])) : 'Нет даты'; ?></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($order['city'] ?? 'Нет города'); ?>,<br>
                                                            <?php echo htmlspecialchars($order['address'] ?? 'Нет адреса'); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Пагинация для заказов -->
                                    <?php if ($orders_pagination['total_pages'] > 1): ?>
                                    <div class="d-flex justify-content-center mt-4">
                                        <nav aria-label="Навигация по страницам заказов">
                                            <ul class="pagination">
                                                <!-- Кнопка "Предыдущая" -->
                                                <li class="page-item <?php echo $orders_page <= 1 ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="index.php?tab=orders&orders_page=<?php echo $orders_page - 1; ?>" aria-label="Предыдущая">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                                
                                                <!-- Номера страниц -->
                                                <?php for ($i = 1; $i <= $orders_pagination['total_pages']; $i++): ?>
                                                    <li class="page-item <?php echo $i == $orders_page ? 'active' : ''; ?>">
                                                        <a class="page-link" href="index.php?tab=orders&orders_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <!-- Кнопка "Следующая" -->
                                                <li class="page-item <?php echo $orders_page >= $orders_pagination['total_pages'] ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="index.php?tab=orders&orders_page=<?php echo $orders_page + 1; ?>" aria-label="Следующая">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="alert alert-info rounded-4 mb-4">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading">Нет заказов</h5>
                                                <p class="mb-0">В системе пока нет заказов.</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Статистика заказов -->
                        <div class="profile-info-card mt-4">
                            <div class="profile-info-header">
                                <h4 class="profile-section-title">Статистика заказов</h4>
                            </div>
                            <div class="profile-info-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h2 class="text-primary mb-2"><?php echo $orders_pagination['total_orders']; ?></h2>
                                            <p class="text-muted mb-0">Всего заказов</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <?php 
                                            $pendingCount = 0;
                                            foreach ($orders as $order) {
                                                if (isset($order['status']) && $order['status'] === 'pending') $pendingCount++;
                                            }
                                            ?>
                                            <h2 class="text-warning mb-2"><?php echo $pendingCount; ?></h2>
                                            <p class="text-muted mb-0">Ожидают</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <?php 
                                            $processingCount = 0;
                                            foreach ($orders as $order) {
                                                if (isset($order['status']) && $order['status'] === 'processing') $processingCount++;
                                            }
                                            ?>
                                            <h2 class="text-info mb-2"><?php echo $processingCount; ?></h2>
                                            <p class="text-muted mb-0">В обработке</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <?php 
                                            $completedCount = 0;
                                            foreach ($orders as $order) {
                                                if (isset($order['status']) && $order['status'] === 'completed') $completedCount++;
                                            }
                                            ?>
                                            <h2 class="text-success mb-2"><?php echo $completedCount; ?></h2>
                                            <p class="text-muted mb-0">Выполнено</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Пользователи -->
                    <div class="tab-pane <?php echo $activeTab === 'users' ? 'active' : ''; ?>" id="users">
                        <div class="profile-main-card mb-4">
                            <div class="profile-header">
                                <h2 class="profile-name">Управление пользователями</h2>
                                <p>Просмотр и управление пользователями сайта</p>
                            </div>
                            
                            <div class="profile-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="profile-section-title mb-0">Список пользователей</h4>
                                    <a href="add_user.php" class="btn btn-primary rounded-pill">
                                        <i class="fas fa-plus me-2"></i>Добавить пользователя
                                    </a>
                                </div>
                                
                                <?php if (!empty($message)): ?>
                                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-4 mb-4" role="alert">
                                        <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>ФИО</th>
                                                <th>Email</th>
                                                <th>Телефон</th>
                                                <th>Логин</th>
                                                <th>Роль</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($users) > 0): ?>
                                                <?php foreach ($users as $u): ?>
                                                    <tr>
                                                        <td><?php echo $u['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($u['phone']); ?></td>
                                                        <td><?php echo htmlspecialchars($u['login']); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $u['role'] === 'Администратор' ? 'bg-danger' : 'bg-primary'; ?> rounded-pill">
                                                                <?php echo htmlspecialchars($u['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <div class="empty-state">
                                                            <div class="empty-state-icon mb-3">
                                                                <i class="fas fa-users fa-3x text-muted"></i>
                                                            </div>
                                                            <h5>Нет пользователей</h5>
                                                            <p class="text-muted">Пользователи не найдены в базе данных</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Пагинация -->
                                <?php if ($users_pagination['total_pages'] > 1): ?>
                                <div class="d-flex justify-content-center mt-4">
                                    <nav aria-label="Навигация по страницам пользователей">
                                        <ul class="pagination">
                                            <!-- Кнопка "Предыдущая" -->
                                            <li class="page-item <?php echo $users_page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="index.php?tab=users&users_page=<?php echo $users_page - 1; ?>" aria-label="Предыдущая">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            
                                            <!-- Номера страниц -->
                                            <?php for ($i = 1; $i <= $users_pagination['total_pages']; $i++): ?>
                                                <li class="page-item <?php echo $i == $users_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="index.php?tab=users&users_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Кнопка "Следующая" -->
                                            <li class="page-item <?php echo $users_page >= $users_pagination['total_pages'] ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="index.php?tab=users&users_page=<?php echo $users_page + 1; ?>" aria-label="Следующая">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Статистика пользователей -->
                        <div class="profile-info-card">
                            <div class="profile-info-header">
                                <h4 class="profile-section-title">Статистика пользователей</h4>
                            </div>
                            <div class="profile-info-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h2 class="text-primary mb-2"><?php echo $users_pagination['total_users']; ?></h2>
                                            <p class="text-muted mb-0">Всего пользователей</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h2 class="text-danger mb-2">
                                                <?php 
                                                $adminCount = 0;
                                                foreach ($users as $u) {
                                                    if ($u['role'] === 'Администратор') $adminCount++;
                                                }
                                                echo $adminCount;
                                                ?>
                                            </h2>
                                            <p class="text-muted mb-0">Администраторов</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h2 class="text-success mb-2">
                                                <?php 
                                                $userCount = 0;
                                                foreach ($users as $u) {
                                                    if ($u['role'] === 'user') $userCount++;
                                                }
                                                echo $userCount;
                                                ?>
                                            </h2>
                                            <p class="text-muted mb-0">Обычных пользователей</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Отчеты -->
                    <div class="tab-pane <?php echo $activeTab === 'reports' ? 'active' : ''; ?>" id="reports">
                        <div class="profile-main-card mb-4">
                            <div class="profile-header">
                                <h2 class="profile-name">Отчеты по продажам</h2>
                                <p>Анализ продаж товаров, статистика и экспорт данных</p>
                            </div>
                            
                            <div class="profile-body">
                                <!-- Фильтры для отчетов -->
                                <div class="filter-container mb-4">
                                    <form id="reports-filter" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="dateFrom" class="form-label">Дата с</label>
                                            <input type="date" class="form-control" id="dateFrom" name="dateFrom">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dateTo" class="form-label">Дата по</label>
                                            <input type="date" class="form-control" id="dateTo" name="dateTo">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="category" class="form-label">Категория</label>
                                            <select class="form-select" id="category" name="category">
                                                <option value="">Все категории</option>
                                                <?php
                                                // Получаем уникальные категории из таблицы product
                                                $sql = "SELECT DISTINCT category FROM product ORDER BY category";
                                                $result = mysqli_query($conn, $sql);
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo '<option value="' . htmlspecialchars($row['category']) . '">' . htmlspecialchars($row['category']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-filter me-2"></i>Применить фильтры
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Карточки со статистикой -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="card-title">Общая сумма продаж</h5>
                                                <p class="card-text display-6" id="totalSales">
                                                    <?php 
                                                    $total = getTotalSalesAmount();
                                                    echo number_format($total, 0, '.', ' ') . ' ₽'; 
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h5 class="card-title">Кол-во проданных товаров</h5>
                                                <p class="card-text display-6" id="totalItems">
                                                    <?php 
                                                    $count = getTotalSoldItemsCount();
                                                    echo number_format($count, 0, '.', ' '); 
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h5 class="card-title">Количество заказов</h5>
                                                <p class="card-text display-6" id="totalOrders">
                                                    <?php 
                                                    $orders = getCompletedOrdersCount();
                                                    echo number_format($orders, 0, '.', ' '); 
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-dark">
                                            <div class="card-body">
                                                <h5 class="card-title">Средний чек</h5>
                                                <p class="card-text display-6" id="averageOrder">
                                                    <?php 
                                                    $avg = $orders > 0 ? $total / $orders : 0;
                                                    echo number_format($avg, 0, '.', ' ') . ' ₽'; 
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Таблица отчета по товарам -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Отчет по продажам товаров</h5>
                                        <button id="exportExcel" class="btn btn-sm btn-success">
                                            <i class="fas fa-file-excel me-2"></i>Выгрузить в Excel
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="products-report">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Наименование товара</th>
                                                        <th scope="col">Категория</th>
                                                        <th scope="col">Кол-во продаж</th>
                                                        <th scope="col">Сумма продаж</th>
                                                        <th scope="col">Средняя цена</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="report-data">
                                                    <?php 
                                                    $reportData = getProductsSalesReport();
                                                    if (!empty($reportData)) {
                                                        $counter = 1;
                                                        foreach ($reportData as $item) {
                                                            echo '<tr>';
                                                            echo '<td>' . $counter++ . '</td>';
                                                            echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                                                            echo '<td>' . htmlspecialchars($item['category']) . '</td>';
                                                            echo '<td>' . number_format($item['quantity'], 0, '.', ' ') . '</td>';
                                                            echo '<td>' . number_format($item['total_amount'], 0, '.', ' ') . ' ₽</td>';
                                                            echo '<td>' . number_format($item['avg_price'], 0, '.', ' ') . ' ₽</td>';
                                                            echo '</tr>';
                                                        }
                                                    } else {
                                                        echo '<tr><td colspan="6" class="text-center">Нет данных для отображения</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- График продаж -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Динамика продаж</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Вкладка: Настройки -->
                    <div class="tab-pane <?php echo $activeTab === 'settings' ? 'active' : ''; ?>" id="settings">
                        <div class="profile-main-card">
                            <div class="profile-header">
                                <h2 class="profile-name">Настройки сайта</h2>
                                <p>Управление основными настройками сайта</p>
                            </div>
                            
                            <div class="profile-body">
                                <div class="alert alert-info rounded-4 mb-4">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-info-circle fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h5 class="alert-heading">Функционал в разработке</h5>
                                            <p class="mb-0">Функционал управления настройками сайта находится в разработке и скоро будет доступен.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Дополнительные стили для админ-панели */
.admin-section {
    padding: 40px 0;
    background: linear-gradient(135deg, #f8f9fc 0%, #eef1f9 100%);
    min-height: calc(100vh - 200px);
}

/* Анимации для карточек */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(77, 97, 252, 0.2) !important;
}

/* Стили для вкладок */
.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Стили для таблицы пользователей */
.table {
    border-radius: 12px;
    overflow: hidden;
}

.table th {
    font-weight: 600;
    border-bottom-width: 1px;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.empty-state {
    padding: 20px;
    text-align: center;
}

.empty-state-icon {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.5;
}

.profile-menu-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3a4cd1 100%);
    color: white;
    padding: 25px 20px;
    position: relative;
    overflow: hidden;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-menu-header a {
    color: white;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.profile-menu-header a:hover {
    opacity: 1;
    transform: scale(1.2);
}

/* Стили для отображения цветов */
.color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1px solid #ddd;
}

/* Стили для конкретных цветов */
.color-black { background-color: #000; }
.color-white { background-color: #fff; }
.color-red { background-color: #ff3b30; }
.color-green { background-color: #34c759; }
.color-blue { background-color: #007aff; }
.color-yellow { background-color: #ffcc00; }
.color-purple { background-color: #af52de; }
.color-pink { background-color: #ff2d55; }
.color-gold { background-color: #d4af37; }
.color-silver { background-color: #c0c0c0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Анимация для карточек быстрого доступа
    const actionCards = document.querySelectorAll('.card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 10px 25px rgba(77, 97, 252, 0.2)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(77, 97, 252, 0.1)';
        });
    });
});
</script>

    </div>
    
    <!-- Bootstrap JS и другие скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Скрипт для предварительного просмотра изображения -->
    <script>
        // Функция для предварительного просмотра изображения
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const previewImg = document.getElementById('preview');
            
            if (imageInput && previewImg) {
                imageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            
            // Валидация формы Bootstrap
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
    
    <!-- Подключаем Chart.js для графиков -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Подключаем SheetJS для экспорта в Excel -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    
    <!-- Скрипт для работы с отчетами -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация графика продаж, если мы находимся на вкладке отчетов
            if (document.querySelector('#salesChart')) {
                initSalesChart();
            }
            
            // Обработчик фильтрации отчетов
            const reportsFilter = document.getElementById('reports-filter');
            if (reportsFilter) {
                reportsFilter.addEventListener('submit', function(e) {
                    e.preventDefault();
                    filterReports();
                });
            }
            
            // Обработчик экспорта в Excel
            const exportBtn = document.getElementById('exportExcel');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    exportToExcel();
                });
            }
        });
        
        // Функция инициализации графика продаж
        function initSalesChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            // Загружаем актуальные данные для графика через AJAX
            fetch('get_monthly_sales.php')
                .then(response => response.json())
                .then(data => {
                    const monthNames = ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
                    
                    const salesData = {
                        labels: monthNames,
                        datasets: [{
                            label: 'Продажи (₽)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            data: data.monthlySales
                        }]
                    };
                    
                    window.salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: salesData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Продажи по месяцам'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Ошибка при загрузке данных графика:', error);
                    // В случае ошибки показываем пустой график
                    const salesData = {
                        labels: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                        datasets: [{
                            label: 'Продажи (₽)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                        }]
                    };
                    
                    window.salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: salesData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Продажи по месяцам'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
        }
        
        // Функция фильтрации отчетов
        function filterReports() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const category = document.getElementById('category').value;
            
            // Показываем индикатор загрузки
            const reportData = document.getElementById('report-data');
            reportData.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Загрузка данных...</td></tr>';
            
            // Отправляем AJAX-запрос к серверу для получения отфильтрованных данных
            fetch('get_filtered_reports.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `dateFrom=${dateFrom}&dateTo=${dateTo}&category=${category}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // Если есть ошибка, отображаем ее
                    reportData.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.error}</td></tr>`;
                    return;
                }
                
                // Обновляем таблицу данными
                updateReportTable(data.reportData);
                
                // Обновляем статистику
                updateStatistics(data.statistics);
                
                // Обновляем график
                updateSalesChart(data.chart);
            })
            .catch(error => {
                console.error('Ошибка:', error);
                reportData.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Ошибка при загрузке данных</td></tr>';
            });
        }
        
        // Функция экспорта таблицы в Excel
        function exportToExcel() {
            const table = document.getElementById('products-report');
            
            // Создаем рабочую книгу и лист
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Задаем ширину столбцов
            const colWidths = [
                { wch: 5 },  // #
                { wch: 40 }, // Наименование товара
                { wch: 20 }, // Категория
                { wch: 15 }, // Кол-во продаж
                { wch: 15 }, // Сумма продаж
                { wch: 15 }  // Средняя цена
            ];
            ws['!cols'] = colWidths;
            
            // Добавляем лист в книгу
            XLSX.utils.book_append_sheet(wb, ws, "Отчет по продажам");
            
            // Генерируем имя файла с текущей датой
            const now = new Date();
            const dateStr = now.toISOString().split('T')[0];
            const fileName = `Отчет_по_продажам_${dateStr}.xlsx`;
            
            // Сохраняем файл
            XLSX.writeFile(wb, fileName);
        }
        
        // Функция обновления таблицы отчета
        function updateReportTable(data) {
            const reportData = document.getElementById('report-data');
            
            if (!data || data.length === 0) {
                reportData.innerHTML = '<tr><td colspan="6" class="text-center">Нет данных для выбранных фильтров</td></tr>';
                return;
            }
            
            let html = '';
            let counter = 1;
            
            data.forEach(item => {
                html += `<tr>
                    <td>${counter++}</td>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>${numberFormat(item.quantity)}</td>
                    <td>${numberFormat(item.total_amount)} ₽</td>
                    <td>${numberFormat(item.avg_price)} ₽</td>
                </tr>`;
            });
            
            reportData.innerHTML = html;
        }
        
        // Функция обновления статистики
        function updateStatistics(statistics) {
            document.getElementById('totalSales').textContent = statistics.totalSales;
            document.getElementById('totalItems').textContent = statistics.totalItems;
            document.getElementById('totalOrders').textContent = statistics.totalOrders;
            document.getElementById('averageOrder').textContent = statistics.averageOrder;
        }
        
        // Функция обновления графика
        function updateSalesChart(chartData) {
            if (!window.salesChart) {
                return;
            }
            
            // Обновляем данные графика
            window.salesChart.data.datasets[0].data = chartData;
            window.salesChart.update();
        }
        
        // Функция форматирования чисел
        function numberFormat(value) {
            return new Intl.NumberFormat('ru-RU').format(value);
        }
    </script>
</body>
</html> 