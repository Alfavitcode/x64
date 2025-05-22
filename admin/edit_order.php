<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Проверяем авторизацию
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    // Если заказ закрыт, запрещаем редактирование
    if ($order['status'] === 'closed') {
        $message = 'Заказ закрыт и не может быть изменен';
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
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование заказа #<?php echo $order_id; ?> | Админ-панель</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
        }
        .profile-info-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .profile-info-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 10px 10px 0 0;
        }
        .profile-info-body {
            padding: 1.5rem;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .profile-section-title {
            color: #4a5568;
            font-weight: 600;
        }
        .order-detail-item {
            margin-bottom: 1rem;
        }
        .order-detail-label {
            font-weight: 600;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <!-- Шапка админ-панели -->
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">Админ-панель X64</h1>
                <div class="d-flex align-items-center">
                    <span class="me-3"><?php echo htmlspecialchars($user_data['fullname']); ?></span>
                    <a href="../logout.php" class="btn btn-sm btn-outline-light">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="container py-4">
        <div class="mb-4">
            <a href="index.php?tab=orders" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Вернуться к списку заказов
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-4 mb-4" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Форма редактирования заказа -->
        <div class="profile-info-card">
            <div class="profile-info-header d-flex justify-content-between align-items-center">
                <h4 class="profile-section-title mb-0">Редактирование заказа #<?php echo $order_id; ?></h4>
                <span class="badge status-badge <?php echo getStatusClass($order['status']); ?>">
                    <?php echo getStatusText($order['status']); ?>
                </span>
            </div>
            <div class="profile-info-body">
                <?php if ($order['status'] === 'closed'): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Этот заказ закрыт и не может быть изменен. <a href="view_order.php?id=<?php echo $order_id; ?>">Просмотреть информацию</a>.
                    </div>
                <?php else: ?>
                    <form method="post" action="">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="mb-3">Информация о клиенте</h5>
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
                                <h5 class="mb-3">Адрес доставки</h5>
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
                                <h5 class="mb-3">Информация о доставке и оплате</h5>
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
                                <h5 class="mb-3">Статус и комментарий</h5>
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
                            <a href="view_order.php?id=<?php echo $order_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-eye me-2"></i>Просмотр заказа
                            </a>
                            <button type="submit" name="update_order" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Товары заказа (только для информации) -->
        <div class="profile-info-card">
            <div class="profile-info-header">
                <h4 class="profile-section-title mb-0">Товары заказа</h4>
            </div>
            <div class="profile-info-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Наименование</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> ₽</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Стоимость доставки:</td>
                                <td><?php echo number_format($order['delivery_cost'], 2, ',', ' '); ?> ₽</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Итого:</td>
                                <td class="fw-bold"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="text-muted">