<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';
require_once '../includes/header/header.php';

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

// Обработка изменения статуса заказа
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $valid_statuses = ['pending', 'processing', 'completed', 'cancelled', 'closed'];
    
    if (in_array($new_status, $valid_statuses)) {
        // Проверяем, был ли заказ уже закрыт
        if ($order['status'] === 'closed') {
            $message = 'Заказ уже закрыт и не может быть изменен';
            $messageType = 'warning';
        } else {
            // Проверяем наличие причины при отмене заказа
            if ($new_status === 'cancelled' && empty($_POST['cancel_reason'])) {
                $message = 'Укажите причину отмены заказа';
                $messageType = 'danger';
            } else {
                // Обновляем причину отмены, если она указана
                if ($new_status === 'cancelled') {
                    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);
                    $update_reason_sql = "UPDATE `orders` SET `cancel_reason` = '{$cancel_reason}' WHERE `id` = {$order_id}";
                    mysqli_query($conn, $update_reason_sql);
                }
                
                // Если новый статус "выполнен" или "отменен", и установлен флаг close_ticket, 
                // то устанавливаем статус "closed"
                if (($new_status === 'completed' || $new_status === 'cancelled') && isset($_POST['close_ticket']) && $_POST['close_ticket'] === 'yes') {
                    $new_status = 'closed';
                }
                
                // Обновляем статус
                $result = updateOrderStatus($order_id, $new_status);
                
                if ($result) {
                    $message = 'Статус заказа успешно обновлен';
                    $messageType = 'success';
                    
                    // Обновляем информацию о заказе
                    $order = getOrderById($order_id);
                } else {
                    $message = 'Не удалось обновить статус заказа';
                    $messageType = 'danger';
                }
            }
        }
    } else {
        $message = 'Указан некорректный статус заказа';
        $messageType = 'danger';
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
    <title>Просмотр заказа #<?php echo $order_id; ?> | Админ-панель</title>
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
        .order-items-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
   

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

        <!-- Информация о заказе -->
        <div class="row">
            <div class="col-lg-8">
                <div class="profile-info-card">
                    <div class="profile-info-header d-flex justify-content-between align-items-center">
                        <h4 class="profile-section-title mb-0">Заказ #<?php echo $order_id; ?></h4>
                        <span class="badge status-badge <?php echo getStatusClass($order['status']); ?>">
                            <?php echo getStatusText($order['status']); ?>
                        </span>
                    </div>
                    <div class="profile-info-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Дата создания:</div>
                                    <div><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">ФИО клиента:</div>
                                    <div><?php echo htmlspecialchars($order['fullname']); ?></div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Email:</div>
                                    <div><?php echo htmlspecialchars($order['email']); ?></div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Телефон:</div>
                                    <div><?php echo htmlspecialchars($order['phone']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Адрес доставки:</div>
                                    <div>
                                        <?php if (!empty($order['region'])): ?>
                                            <?php echo htmlspecialchars($order['region']); ?>, 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($order['city']); ?>,<br>
                                        <?php echo htmlspecialchars($order['address']); ?>
                                        <?php if (!empty($order['postal_code'])): ?>
                                            , <?php echo htmlspecialchars($order['postal_code']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Способ доставки:</div>
                                    <div>
                                        <?php echo $order['delivery_method'] === 'courier' ? 'Курьером' : 'Почтой'; ?>
                                        (<?php echo number_format($order['delivery_cost'], 2, ',', ' '); ?> ₽)
                                    </div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Способ оплаты:</div>
                                    <div>
                                        <?php 
                                        switch($order['payment_method']) {
                                            case 'card':
                                                echo 'Банковская карта';
                                                break;
                                            case 'cash':
                                                echo 'Наличные при получении';
                                                break;
                                            default:
                                                echo htmlspecialchars($order['payment_method']);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Комментарий:</div>
                                    <div><?php echo !empty($order['comment']) ? htmlspecialchars($order['comment']) : 'Нет комментария'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Товары заказа -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h4 class="profile-section-title mb-0">Товары заказа</h4>
                    </div>
                    <div class="profile-info-body">
                        <div class="table-responsive">
                            <table class="table table-hover order-items-table">
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
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Управление статусом заказа -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h4 class="profile-section-title mb-0">Управление заказом</h4>
                    </div>
                    <div class="profile-info-body">
                        <?php if ($order['status'] !== 'closed'): ?>
                            <form method="post" action="" id="orderStatusForm">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Изменить статус</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Обрабатывается</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Выполнен</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                    </select>
                                </div>
                                
                                <!-- Поле для ввода причины отмены -->
                                <div class="mb-3" id="cancelReasonDiv" style="display: none;">
                                    <label for="cancel_reason" class="form-label">Причина отмены <span class="text-danger">*</span></label>
                                    <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="3"><?php echo htmlspecialchars($order['cancel_reason'] ?? ''); ?></textarea>
                                    <div class="form-text text-danger" id="cancelReasonError" style="display: none;">
                                        Укажите причину отмены заказа
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check" id="closeTicketCheckboxDiv">
                                    <input type="checkbox" class="form-check-input" id="close_ticket" name="close_ticket" value="yes">
                                    <label class="form-check-label" for="close_ticket">Закрыть тикет</label>
                                    <div class="form-text">Если отмечено, то при выборе статуса "Выполнен" или "Отменен" заказ будет полностью закрыт и дальнейшее изменение невозможно.</div>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary w-100">Сохранить изменения</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-secondary mb-0">
                                Заказ закрыт и не может быть изменен.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Дополнительное действие -->
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h4 class="profile-section-title mb-0">Действия</h4>
                    </div>
                    <div class="profile-info-body">
                        <a href="javascript:window.print();" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-print me-2"></i>Распечатать заказ
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-envelope me-2"></i>Написать клиенту
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Показывать/скрывать чекбокс "Закрыть тикет" и поле "Причина отмены" в зависимости от выбранного статуса
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const closeTicketDiv = document.getElementById('closeTicketCheckboxDiv');
            const cancelReasonDiv = document.getElementById('cancelReasonDiv');
            const cancelReasonField = document.getElementById('cancel_reason');
            const cancelReasonError = document.getElementById('cancelReasonError');
            const orderForm = document.getElementById('orderStatusForm');
            
            function updateFormVisibility() {
                if (statusSelect) {
                    const selectedStatus = statusSelect.value;
                    
                    // Управление показом чекбокса "Закрыть тикет"
                    if (closeTicketDiv) {
                        if (selectedStatus === 'completed' || selectedStatus === 'cancelled') {
                            closeTicketDiv.style.display = 'block';
                        } else {
                            closeTicketDiv.style.display = 'none';
                        }
                    }
                    
                    // Управление показом поля "Причина отмены"
                    if (cancelReasonDiv) {
                        if (selectedStatus === 'cancelled') {
                            cancelReasonDiv.style.display = 'block';
                        } else {
                            cancelReasonDiv.style.display = 'none';
                        }
                    }
                }
            }
            
            // Проверяем валидацию формы при отправке
            if (orderForm) {
                orderForm.addEventListener('submit', function(e) {
                    if (statusSelect.value === 'cancelled') {
                        if (!cancelReasonField.value.trim()) {
                            e.preventDefault();
                            cancelReasonError.style.display = 'block';
                            cancelReasonField.focus();
                        } else {
                            cancelReasonError.style.display = 'none';
                        }
                    }
                });
            }
            
            // Вызываем функцию при загрузке страницы для установки начального состояния
            updateFormVisibility();
            
            // Добавляем слушатель события изменения статуса
            if (statusSelect) {
                statusSelect.addEventListener('change', updateFormVisibility);
            }
        });
    </script>
</body>
</html>