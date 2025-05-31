<?php
require_once '../includes/header/header.php'; 

require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Начинаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Если пользователь не является администратором, перенаправляем на главную
if (!$user || $user['role'] !== 'Администратор') {
    header("Location: /");
    exit;
}

// Данные пользователя уже получены выше
$user_data = $user;

// Проверяем, передан ли ID заказа
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?tab=orders');
    exit;
}

$order_id = (int) $_GET['id'];
$order = getOrderById($order_id);

// Проверяем, существует ли заказ
if (!$order) {
    header('Location: index.php?tab=orders&error=order_not_found');
    exit;
}

// Получаем элементы заказа
$order_items = getOrderItems($order_id);

// Обработка формы редактирования
$message = '';
$messageType = '';

// Обработка удаления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    // Если заказ закрыт или выполнен, запрещаем удаление
    if ($order['status'] === 'closed' || $order['status'] === 'completed') {
        $message = 'Заказ ' . ($order['status'] === 'completed' ? 'выполнен' : 'закрыт') . ' и не может быть удален';
        $messageType = 'warning';
    } else {
        // Вызываем функцию удаления заказа
        $delete_result = deleteOrder($order_id);
        
        if ($delete_result['success']) {
            // Вместо прямого перенаправления сохраняем информацию для JavaScript
            $redirect_url = 'index.php?tab=orders&message=' . urlencode($delete_result['message']) . '&message_type=success';
            $redirect_script = true;
        } else {
            $message = $delete_result['message'];
            $messageType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    // Если заказ закрыт или выполнен, запрещаем редактирование
    if ($order['status'] === 'closed' || $order['status'] === 'completed') {
        $message = 'Заказ ' . ($order['status'] === 'completed' ? 'выполнен' : 'закрыт') . ' и не может быть изменен';
        $messageType = 'warning';
    } else {
        // Получаем данные из формы
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $region = $_POST['region'] ?? '';
        $city = $_POST['city'] ?? '';
        $address = $_POST['address'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $delivery_method = $_POST['delivery_method'] ?? '';
        $comment = $_POST['comment'] ?? '';
        $status = $_POST['status'] ?? '';
        
        // Проверяем, указана ли причина отмены, если статус "отменен"
        if ($status === 'cancelled' && empty($_POST['cancel_reason'])) {
            $message = 'Необходимо указать причину отмены заказа';
            $messageType = 'danger';
        } else {
            // Подготавливаем дополнительные поля для обновления
            $cancel_reason_sql = "";
            if ($status === 'cancelled') {
                $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason'] ?? '');
                $cancel_reason_sql = ", `cancel_reason` = '" . $cancel_reason . "'";
            }
            
            // Обновляем информацию о заказе в базе данных
            $sql = "UPDATE `orders` SET 
                    `fullname` = '" . mysqli_real_escape_string($conn, $fullname) . "',
                    `email` = '" . mysqli_real_escape_string($conn, $email) . "',
                    `phone` = '" . mysqli_real_escape_string($conn, $phone) . "',
                    `region` = '" . mysqli_real_escape_string($conn, $region) . "',
                    `city` = '" . mysqli_real_escape_string($conn, $city) . "',
                    `address` = '" . mysqli_real_escape_string($conn, $address) . "',
                    `postal_code` = '" . mysqli_real_escape_string($conn, $postal_code) . "',
                    `payment_method` = '" . mysqli_real_escape_string($conn, $payment_method) . "',
                    `delivery_method` = '" . mysqli_real_escape_string($conn, $delivery_method) . "',
                    `comment` = '" . mysqli_real_escape_string($conn, $comment) . "',
                    `status` = '" . mysqli_real_escape_string($conn, $status) . "'" . 
                    $cancel_reason_sql . "
                    WHERE `id` = " . (int)$order_id;
            
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $message = 'Информация о заказе успешно обновлена';
                $messageType = 'success';
                
                // Если установлен флаг закрытия тикета и статус "выполнен" или "отменен"
                if (isset($_POST['close_ticket']) && $_POST['close_ticket'] === 'yes' && 
                    ($status === 'completed' || $status === 'cancelled')) {
                    
                    // Обновляем статус на "closed"
                    $update_status = updateOrderStatus($order_id, 'closed');
                    
                    if ($update_status) {
                        $message = 'Информация о заказе обновлена, заказ закрыт';
                        $messageType = 'success';
                    }
                }
                
                // Перезагружаем данные о заказе
                $order = getOrderById($order_id);
            } else {
                $message = 'Ошибка при обновлении информации о заказе: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Получаем текстовое представление статуса
function getStatusText($status) {
    switch($status) {
        case 'pending':
            return 'Ожидает';
        case 'processing':
            return 'Обрабатывается';
        case 'completed':
            return 'Выполнен';
        case 'cancelled':
            return 'Отменен';
        case 'closed':
            return 'Закрыт';
        default:
            return 'Неизвестно';
    }
}

// Получаем CSS класс для статуса
function getStatusClass($status) {
    switch($status) {
        case 'pending':
            return 'bg-warning';
        case 'processing':
            return 'bg-info';
        case 'completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        case 'closed':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}
?>
<style>
    /* Стили для админки в стиле сайта */
    .admin-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1rem 0;
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
    }

    .admin-header .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-header h1 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
    }

    .admin-header .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .admin-header .btn-logout {
        background-color: rgba(255,255,255,0.2);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--btn-border-radius);
        transition: var(--transition);
    }

    .admin-header .btn-logout:hover {
        background-color: rgba(255,255,255,0.3);
    }

    .order-card {
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: var(--transition);
    }

    .order-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .order-header {
        background-color: var(--light-color);
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-header h4 {
        margin: 0;
        font-weight: 600;
        color: var(--text-color);
    }

    .order-body {
        padding: 2rem;
    }

    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 500;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-color);
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(77, 97, 252, 0.25);
    }

    .btn-back {
        color: var(--primary-color);
        background-color: transparent;
        border: 1px solid var(--primary-color);
        border-radius: var(--btn-border-radius);
        transition: var(--transition);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-back:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        border-radius: var(--btn-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .btn-primary:hover {
        background-color: var(--primary-color-hover);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(77, 97, 252, 0.3);
    }

    .btn-view {
        color: var(--secondary-color);
        background-color: transparent;
        border: 1px solid var(--secondary-color);
        border-radius: var(--btn-border-radius);
        transition: var(--transition);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-view:hover {
        background-color: var(--secondary-color);
        color: white;
    }

    .alert {
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
    }

    .section-title {
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 1.8rem;
        margin-top: 0.5rem;
        position: relative;
        display: inline-block;
        font-size: 1.25rem;
        padding-bottom: 8px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
    }

    .table {
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .table th {
        background-color: var(--light-color);
        font-weight: 600;
        padding: 1rem;
    }

    .table td {
        padding: 1rem;
        vertical-align: middle;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-text {
        font-size: 0.875rem;
        color: var(--secondary-color);
    }
    
    /* Стили для кнопки удаления */
    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: var(--btn-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .btn-danger:hover {
        background-color: #bb2d3b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: var(--btn-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    
    .modal-content {
        border-radius: var(--border-radius);
    }
    
    .modal-header {
        border-top-left-radius: var(--border-radius);
        border-top-right-radius: var(--border-radius);
    }

    /* Увеличиваем отступы блоков */
    .row.mb-4 {
        margin-bottom: 2rem !important;
    }
    
    /* Добавляем отступы между секциями */
    .col-md-6 {
        padding-bottom: 1rem;
    }
</style>

<!-- Основной контент -->
<div class="container py-4">
    <div class="mb-4">
        <a href="index.php?tab=orders" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Вернуться к списку заказов
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Форма редактирования заказа -->
    <div class="order-card">
        <div class="order-header">
            <h4>Редактирование заказа #<?php echo $order_id; ?></h4>
            <span class="badge status-badge <?php echo getStatusClass($order['status']); ?>">
                <?php echo getStatusText($order['status']); ?>
            </span>
        </div>
        <div class="order-body">
            <?php if ($order['status'] === 'closed' || $order['status'] === 'completed'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Этот заказ <?php echo $order['status'] === 'completed' ? 'выполнен' : 'закрыт'; ?> и не может быть изменен. <a href="view_order.php?id=<?php echo $order_id; ?>">Просмотреть информацию</a>.
                </div>
            <?php else: ?>
                <form method="post" action="">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="section-title">Информация о клиенте</h5>
                            <div class="mb-3">
                                <label for="fullname" class="form-label">ФИО</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($order['fullname']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($order['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($order['phone']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="section-title">Адрес доставки</h5>
                            <div class="mb-3">
                                <label for="region" class="form-label">Регион</label>
                                <input type="text" class="form-control" id="region" name="region" value="<?php echo htmlspecialchars($order['region'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">Город</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($order['city']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Адрес</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($order['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Почтовый индекс</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($order['postal_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="section-title">Информация о доставке и оплате</h5>
                            <div class="mb-3">
                                <label for="delivery_method" class="form-label">Способ доставки</label>
                                <select class="form-select" id="delivery_method" name="delivery_method">
                                    <option value="courier" <?php echo $order['delivery_method'] === 'courier' ? 'selected' : ''; ?>>Курьером</option>
                                    <option value="post" <?php echo $order['delivery_method'] === 'post' ? 'selected' : ''; ?>>Почтой</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Способ оплаты</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="card" <?php echo $order['payment_method'] === 'card' ? 'selected' : ''; ?>>Банковская карта</option>
                                    <option value="cash" <?php echo $order['payment_method'] === 'cash' ? 'selected' : ''; ?>>Наличные при получении</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="section-title">Статус и комментарий</h5>
                            <div class="mb-3">
                                <label for="status" class="form-label">Статус заказа</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Обрабатывается</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Выполнен</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                </select>
                            </div>
                            
                            <!-- Поле для ввода причины отмены -->
                            <div class="mb-3" id="cancelReasonDiv" style="display: none;">
                                <label for="cancel_reason" class="form-label">Причина отмены <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"><?php echo htmlspecialchars($order['cancel_reason'] ?? ''); ?></textarea>
                                <div class="form-text text-danger" id="cancelReasonError" style="display: none;">
                                    Укажите причину отмены заказа
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check" id="closeTicketCheckboxDiv">
                                <input type="checkbox" class="form-check-input" id="close_ticket" name="close_ticket" value="yes">
                                <label class="form-check-label" for="close_ticket">Закрыть тикет</label>
                                <div class="form-text">Если отмечено, то при выборе статуса "Выполнен" или "Отменен" заказ будет полностью закрыт и дальнейшее изменение невозможно.</div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Комментарий к заказу</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3"><?php echo htmlspecialchars($order['comment'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="view_order.php?id=<?php echo $order_id; ?>" class="btn-view">
                            <i class="fas fa-eye"></i>
                            Просмотр заказа
                        </a>
                        <div>
                            <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                <i class="fas fa-trash-alt me-2"></i>Удалить заказ
                            </button>
                            <button type="submit" name="update_order" class="btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Товары заказа -->
    <div class="order-card">
        <div class="order-header">
            <h4>Товары заказа</h4>
        </div>
        <div class="order-body">
            <!-- Поисковая строка для товаров -->
            <div class="search-container mb-4">
                <div class="row">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="productSearchInput" class="form-control border-start-0 ps-0" placeholder="Поиск по названию товара...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="categoryFilter" class="form-select">
                            <option value="">Все категории</option>
                            <?php
                            // Получаем уникальные категории из товаров заказа
                            $categories = [];
                            foreach ($order_items as $item) {
                                if (isset($item['category']) && !empty($item['category']) && !in_array($item['category'], $categories)) {
                                    $categories[] = $item['category'];
                                }
                            }
                            
                            // Выводим опции для категорий
                            foreach ($categories as $category) {
                                echo '<option value="' . htmlspecialchars($category) . '">' . htmlspecialchars($category) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="orderItemsTable">
                    <thead>
                        <tr>
                            <th>Наименование</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Кол-во</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>" data-category="<?php echo htmlspecialchars(strtolower($item['category'] ?? '')); ?>">
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category'] ?? 'Не указана'); ?></td>
                                <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> ₽</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Стоимость доставки:</td>
                            <td><?php echo number_format($order['delivery_cost'], 2, ',', ' '); ?> ₽</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Итого:</td>
                            <td class="fw-bold"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="text-muted">
                <p class="small mb-0">Дата заказа: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления заказа -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите удалить заказ #<?php echo $order_id; ?>?</p>
                <p class="text-danger">Внимание! Это действие нельзя отменить. Вся информация о заказе будет безвозвратно удалена из базы данных.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="post" action="">
                    <button type="submit" name="delete_order" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Скрипт для показа/скрытия поля причины отмены
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const cancelReasonDiv = document.getElementById('cancelReasonDiv');
        const cancelReasonInput = document.getElementById('cancel_reason');
        const cancelReasonError = document.getElementById('cancelReasonError');
        const orderForm = document.querySelector('form');

        // Функция для проверки статуса и отображения поля причины отмены
        function checkStatus() {
            if (statusSelect.value === 'cancelled') {
                cancelReasonDiv.style.display = 'block';
                cancelReasonInput.setAttribute('required', 'required');
            } else {
                cancelReasonDiv.style.display = 'none';
                cancelReasonInput.removeAttribute('required');
                cancelReasonError.style.display = 'none';
            }
        }

        // Вызываем функцию при загрузке страницы
        checkStatus();

        // Добавляем слушатель события изменения статуса
        statusSelect.addEventListener('change', checkStatus);

        // Проверка формы перед отправкой
        orderForm.addEventListener('submit', function(event) {
            if (statusSelect.value === 'cancelled' && !cancelReasonInput.value.trim()) {
                event.preventDefault();
                cancelReasonError.style.display = 'block';
                cancelReasonInput.focus();
            }
        });
        
        <?php if (isset($redirect_script) && $redirect_script === true): ?>
        // Выполняем перенаправление через JavaScript
        window.location.href = '<?php echo $redirect_url; ?>';
        <?php endif; ?>
    });

    // Дополнительный скрипт для корректной инициализации всех компонентов
    document.addEventListener('DOMContentLoaded', function() {
        // Фиксируем прокрутку хедера
        const fixHeader = () => {
            const header = document.querySelector('.header');
            if (header) {
                if (window.scrollY > 10) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
        };
        
        // Обработчик прокрутки
        window.addEventListener('scroll', fixHeader);
        
        // Инициализация при загрузке
        fixHeader();
        
        // Функционал поиска товаров
        const productSearchInput = document.getElementById('productSearchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const orderItemsTable = document.getElementById('orderItemsTable');
        const orderItemRows = orderItemsTable ? orderItemsTable.querySelectorAll('tbody tr') : [];
        
        // Функция для фильтрации товаров
        function filterProducts() {
            const searchText = productSearchInput.value.toLowerCase();
            const categoryValue = categoryFilter.value.toLowerCase();
            let hasVisibleRows = false;
            
            orderItemRows.forEach(row => {
                const productName = row.getAttribute('data-name') || '';
                const productCategory = row.getAttribute('data-category') || '';
                
                // Проверяем соответствие фильтрам
                const matchesSearch = productName.includes(searchText);
                const matchesCategory = !categoryValue || productCategory === categoryValue;
                
                // Отображаем или скрываем строку в зависимости от результатов поиска
                if (matchesSearch && matchesCategory) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Проверка, есть ли видимые строки
            const emptyRow = orderItemsTable.querySelector('tbody tr.empty-search-result');
            
            // Если нет видимых строк и нет сообщения о пустом результате, добавляем его
            if (!hasVisibleRows && !emptyRow && orderItemRows.length > 0) {
                const tbody = orderItemsTable.querySelector('tbody');
                const newRow = document.createElement('tr');
                newRow.className = 'empty-search-result';
                newRow.innerHTML = `
                    <td colspan="5" class="text-center py-3">
                        <div class="py-2">
                            <i class="fas fa-search fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Товары не найдены</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(newRow);
            } 
            // Если есть видимые строки и есть сообщение о пустом результате, удаляем его
            else if (hasVisibleRows && emptyRow) {
                emptyRow.remove();
            }
        }
        
        // Добавляем обработчики событий
        if (productSearchInput) {
            productSearchInput.addEventListener('input', filterProducts);
        }
        
        if (categoryFilter) {
            categoryFilter.addEventListener('change', filterProducts);
        }
    });
</script>
<?php require_once '../includes/footer/footer.php'; ?>