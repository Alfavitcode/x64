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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $valid_statuses = ['pending', 'processing', 'completed', 'cancelled', 'closed'];
    
    if (in_array($new_status, $valid_statuses)) {
        // Проверяем, был ли заказ уже закрыт или выполнен
        if ($order['status'] === 'closed' || $order['status'] === 'completed') {
            $message = 'Заказ ' . ($order['status'] === 'completed' ? 'выполнен' : 'закрыт') . ' и не может быть изменен';
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
    .btn-order-action {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        transition: background-color 0.15s ease-in-out;
    }
    .btn-danger:hover {
        background-color: #c82333;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        transition: background-color 0.15s ease-in-out;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    .modal-content {
        border-radius: 0.5rem;
    }
    .modal-header {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
</style>

<!-- Основной контент -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Заказ #<?php echo $order_id; ?></h2>
        <div>
            <a href="index.php?tab=orders" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Вернуться к списку
            </a>
            <?php if ($order['status'] !== 'closed' && $order['status'] !== 'completed'): ?>
                <a href="edit_order.php?id=<?php echo $order_id; ?>" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-edit me-2"></i>Редактировать заказ
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Информация о заказе -->
            <div class="profile-info-card">
                <div class="profile-info-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Детали заказа</h5>
                    <span class="badge status-badge <?php echo getStatusClass($order['status']); ?>">
                        <?php echo getStatusText($order['status']); ?>
                    </span>
                </div>
                <div class="profile-info-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="order-detail-item">
                                <div class="order-detail-label">Номер заказа:</div>
                                <div>#<?php echo $order_id; ?></div>
                            </div>
                            <div class="order-detail-item">
                                <div class="order-detail-label">Дата заказа:</div>
                                <div><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
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
                                            echo 'Не указан';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="order-detail-item">
                                <div class="order-detail-label">Способ доставки:</div>
                                <div>
                                    <?php
                                    switch($order['delivery_method']) {
                                        case 'courier':
                                            echo 'Курьером';
                                            break;
                                        case 'post':
                                            echo 'Почтой';
                                            break;
                                        default:
                                            echo 'Не указан';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="order-detail-item">
                                <div class="order-detail-label">Сумма заказа:</div>
                                <div class="fs-5 fw-bold"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</div>
                            </div>
                            <div class="order-detail-item">
                                <div class="order-detail-label">Стоимость доставки:</div>
                                <div><?php echo number_format($order['delivery_cost'], 2, ',', ' '); ?> ₽</div>
                            </div>
                            <?php if ($order['status'] === 'cancelled' && !empty($order['cancel_reason'])): ?>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Причина отмены:</div>
                                    <div class="text-danger"><?php echo htmlspecialchars($order['cancel_reason']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($order['comment'])): ?>
                                <div class="order-detail-item">
                                    <div class="order-detail-label">Комментарий к заказу:</div>
                                    <div><?php echo htmlspecialchars($order['comment']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Товары заказа -->
            <div class="profile-info-card">
                <div class="profile-info-header">
                    <h5 class="mb-0">Товары заказа</h5>
                </div>
                <div class="profile-info-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 order-items-table">
                            <thead>
                                <tr>
                                    <th>Наименование</th>
                                    <th class="text-center">Цена</th>
                                    <th class="text-center">Кол-во</th>
                                    <th class="text-end">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="text-center"><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> ₽</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Стоимость доставки:</td>
                                    <td class="text-end"><?php echo number_format($order['delivery_cost'], 2, ',', ' '); ?> ₽</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Итого:</td>
                                    <td class="text-end fw-bold"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Информация о клиенте -->
            <div class="profile-info-card">
                <div class="profile-info-header">
                    <h5 class="mb-0">Информация о клиенте</h5>
                </div>
                <div class="profile-info-body">
                    <div class="order-detail-item">
                        <div class="order-detail-label">ФИО:</div>
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
                    <div class="order-detail-item">
                        <div class="order-detail-label">Адрес доставки:</div>
                        <div>
                            <?php
                                $address_parts = [];
                                if (!empty($order['region'])) $address_parts[] = htmlspecialchars($order['region']);
                                if (!empty($order['city'])) $address_parts[] = htmlspecialchars($order['city']);
                                if (!empty($order['address'])) $address_parts[] = htmlspecialchars($order['address']);
                                if (!empty($order['postal_code'])) $address_parts[] = 'Индекс: ' . htmlspecialchars($order['postal_code']);
                                echo implode(', ', $address_parts);
                            ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="print_receipt.php?id=<?php echo $order_id; ?>" class="btn btn-outline-primary w-100" target="_blank">
                            <i class="fas fa-print me-2"></i>Распечатать чек
                        </a>
                        <?php if ($order['status'] !== 'closed' && $order['status'] !== 'completed'): ?>
                            <button type="button" class="btn btn-outline-danger w-100 mt-2" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                <i class="fas fa-trash-alt me-2"></i>Удалить заказ
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Форма управления заказом -->
            <?php if ($order['status'] !== 'closed' && $order['status'] !== 'completed'): ?>
                <div class="profile-info-card">
                    <div class="profile-info-header">
                        <h5 class="mb-0">Управление заказом</h5>
                    </div>
                    <div class="profile-info-body">
                        <form id="orderStatusForm" method="post" action="">
                            <div class="mb-3">
                                <label for="status" class="form-label">Изменить статус</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Обрабатывается</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Выполнен</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                </select>
                            </div>
                            
                            <div class="mb-3 form-check" id="closeTicketCheckboxDiv">
                                <input type="checkbox" class="form-check-input" id="close_ticket" name="close_ticket" value="yes">
                                <label class="form-check-label" for="close_ticket">Закрыть тикет</label>
                                <div class="form-text">Если отмечено, заказ будет полностью закрыт и дальнейшее изменение будет невозможно.</div>
                            </div>
                            
                            <div class="mb-3" id="cancelReasonDiv" style="display: none;">
                                <label for="cancel_reason" class="form-label">Причина отмены <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"><?php echo htmlspecialchars($order['cancel_reason'] ?? ''); ?></textarea>
                                <div class="form-text text-danger" id="cancelReasonError" style="display: none;">
                                    Укажите причину отмены заказа
                                </div>
                            </div>
                            
                            <button type="submit" name="update_status" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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
    // Показывать/скрывать чекбокс "Закрыть тикет" и поле "Причина отмены" в зависимости от выбранного статуса
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const closeTicketDiv = document.getElementById('closeTicketCheckboxDiv');
        const cancelReasonDiv = document.getElementById('cancelReasonDiv');
        const cancelReasonField = document.getElementById('cancel_reason');
        const cancelReasonError = document.getElementById('cancelReasonError');
        const orderForm = document.getElementById('orderStatusForm');
        
        function updateFormVisibility() {
            if (statusSelect.value === 'cancelled') {
                cancelReasonDiv.style.display = 'block';
                cancelReasonField.setAttribute('required', 'required');
            } else {
                cancelReasonDiv.style.display = 'none';
                cancelReasonField.removeAttribute('required');
                cancelReasonError.style.display = 'none';
            }
            
            // Показывать чекбокс только для статусов "Выполнен" или "Отменен"
            if (statusSelect.value === 'completed' || statusSelect.value === 'cancelled') {
                closeTicketDiv.style.display = 'block';
            } else {
                closeTicketDiv.style.display = 'none';
                document.getElementById('close_ticket').checked = false;
            }
        }
        
        // Проверка при загрузке страницы
        if (statusSelect) {
            updateFormVisibility();
            
            // Слушатель изменения статуса
            statusSelect.addEventListener('change', updateFormVisibility);
            
            // Проверка формы перед отправкой
            if (orderForm) {
                orderForm.addEventListener('submit', function(event) {
                    if (statusSelect.value === 'cancelled' && !cancelReasonField.value.trim()) {
                        event.preventDefault();
                        cancelReasonError.style.display = 'block';
                        cancelReasonField.focus();
                    }
                });
            }
        }
        
        <?php if (isset($redirect_script) && $redirect_script === true): ?>
        // Выполняем перенаправление через JavaScript
        window.location.href = '<?php echo $redirect_url; ?>';
        <?php endif; ?>
    });
    
    // Дополнительный скрипт для корректной инициализации хедера
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
    });
</script>
<?php require_once '../includes/footer/footer.php'; ?>