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

// Получаем информацию о магазине
$shop_name = "X64 Shop";
$shop_address = "г. Москва, ул. Примерная, д. 1";
$shop_phone = "+7 (999) 123-45-67";
$shop_email = "info@x64shop.ru";
$shop_inn = "1234567890";

// Функция для форматирования даты
function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

// Функция для форматирования цены
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' ₽';
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

// Получаем текстовое представление способа оплаты
function getPaymentMethodText($method) {
    switch($method) {
        case 'card':
            return 'Банковская карта';
        case 'cash':
            return 'Наличные при получении';
        default:
            return 'Не указан';
    }
}

// Получаем текстовое представление способа доставки
function getDeliveryMethodText($method) {
    switch($method) {
        case 'courier':
            return 'Курьером';
        case 'post':
            return 'Почтой';
        default:
            return 'Не указан';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чек №<?php echo $order_id; ?> - <?php echo $shop_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size: 12pt;
                line-height: 1.3;
            }
            
            .no-print {
                display: none !important;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            
            .receipt {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                animation: none !important;
                transform: none !important;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            th, td {
                padding: 5px;
            }
            
            .table-bordered th, .table-bordered td {
                border: 1px solid #000;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .receipt-logo {
                max-width: 150px !important;
            }
            
            .receipt-watermark {
                display: none !important;
            }
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Roboto', sans-serif;
            padding: 20px 0;
            position: relative;
            color: #333;
        }
        
        .receipt-container {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        .receipt {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
            transform-origin: top center;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .receipt-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 150px;
            opacity: 0.03;
            font-weight: bold;
            pointer-events: none;
            white-space: nowrap;
            color: #000;
        }
        
        .receipt-header {
            border-bottom: 2px dashed #e0e0e0;
            padding-bottom: 25px;
            margin-bottom: 25px;
            position: relative;
        }
        
        .receipt-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .receipt-subtitle {
            color: #7f8c8d;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .receipt-logo {
            max-width: 80px;
            margin-bottom: 15px;
        }
        
        .receipt-info {
            margin-bottom: 30px;
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .receipt-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
            position: relative;
            padding-left: 15px;
        }
        
        .receipt-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 20px;
            background-color: #4d61fc;
            border-radius: 3px;
        }
        
        .receipt-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .receipt-label {
            font-weight: 500;
            margin-right: 10px;
            color: #7f8c8d;
        }
        
        .receipt-value {
            text-align: right;
            font-weight: 400;
        }
        
        .receipt-table {
            width: 100%;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            animation: fadeIn 1s ease-out;
        }
        
        .receipt-table th {
            background-color: #f8f9fa;
            padding: 12px;
            font-weight: 500;
            color: #2c3e50;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .receipt-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .receipt-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .receipt-table tbody tr:hover {
            background-color: #f0f7ff;
        }
        
        .receipt-table tfoot tr {
            background-color: #f8f9fa;
        }
        
        .receipt-table tfoot td {
            padding: 12px;
            font-weight: 500;
        }
        
        .receipt-footer {
            border-top: 2px dashed #e0e0e0;
            padding-top: 25px;
            margin-top: 25px;
            text-align: center;
            animation: fadeIn 1.2s ease-out;
        }
        
        .receipt-thank-you {
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .receipt-note {
            color: #95a5a6;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .receipt-date {
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 15px;
        }
        
        .btn-action {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 0 5px;
        }
        
        .btn-print {
            background-color: #4d61fc;
            border: none;
            color: white;
        }
        
        .btn-print:hover {
            background-color: #3a4cd1;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn-return {
            background-color: #6c757d;
            border: none;
            color: white;
        }
        
        .btn-return:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .action-buttons {
            margin-top: 30px;
            animation: fadeIn 1.4s ease-out;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            color: white;
        }
        
        .status-pending {
            background-color: #f39c12;
        }
        
        .status-processing {
            background-color: #3498db;
        }
        
        .status-completed {
            background-color: #2ecc71;
        }
        
        .status-cancelled {
            background-color: #e74c3c;
        }
        
        .status-closed {
            background-color: #95a5a6;
        }
        
        .receipt-total-row {
            font-weight: 700;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .receipt-total-value {
            color: #4d61fc;
        }
        
        @media (max-width: 768px) {
            .receipt {
                padding: 20px;
                margin: 10px;
            }
            
            .receipt-title {
                font-size: 22px;
            }
            
            .receipt-info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .receipt-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container receipt-container">
        <div class="receipt">
            <div class="receipt-watermark">X64 SHOP</div>
            
            <div class="receipt-header">
                <div class="row">
                    <div class="col-md-6">
                        <img src="/img/logo.png" alt="<?php echo $shop_name; ?>" class="receipt-logo mb-3" onerror="this.style.display='none'">
                        <div class="receipt-title"><?php echo $shop_name; ?></div>
                        <div class="receipt-subtitle"><i class="fas fa-map-marker-alt me-2"></i><?php echo $shop_address; ?></div>
                        <div class="receipt-subtitle"><i class="fas fa-phone me-2"></i><?php echo $shop_phone; ?></div>
                        <div class="receipt-subtitle"><i class="fas fa-envelope me-2"></i><?php echo $shop_email; ?></div>
                        <div class="receipt-subtitle"><i class="fas fa-id-card me-2"></i>ИНН: <?php echo $shop_inn; ?></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="receipt-title">Чек №<?php echo $order_id; ?></div>
                        <div class="receipt-subtitle"><i class="far fa-calendar-alt me-2"></i>Дата: <?php echo formatDate($order['created_at']); ?></div>
                        <div class="receipt-subtitle mt-2">
                            Статус: 
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo getStatusText($order['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="receipt-info">
                <h5 class="receipt-section-title">Информация о клиенте</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-user me-2"></i>ФИО:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($order['fullname']); ?></span>
                        </div>
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-phone me-2"></i>Телефон:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-envelope me-2"></i>Email:</span>
                            <span class="receipt-value"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-credit-card me-2"></i>Способ оплаты:</span>
                            <span class="receipt-value"><?php echo getPaymentMethodText($order['payment_method']); ?></span>
                        </div>
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-truck me-2"></i>Способ доставки:</span>
                            <span class="receipt-value"><?php echo getDeliveryMethodText($order['delivery_method']); ?></span>
                        </div>
                        <div class="receipt-info-row">
                            <span class="receipt-label"><i class="fas fa-map-marker-alt me-2"></i>Адрес доставки:</span>
                            <span class="receipt-value">
                                <?php
                                    $address_parts = [];
                                    if (!empty($order['region'])) $address_parts[] = htmlspecialchars($order['region']);
                                    if (!empty($order['city'])) $address_parts[] = htmlspecialchars($order['city']);
                                    if (!empty($order['address'])) $address_parts[] = htmlspecialchars($order['address']);
                                    if (!empty($order['postal_code'])) $address_parts[] = 'Индекс: ' . htmlspecialchars($order['postal_code']);
                                    echo implode(', ', $address_parts);
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 class="receipt-section-title">Товары</h5>
            <div class="table-responsive">
                <table class="table table-bordered receipt-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Наименование</th>
                            <th class="text-center">Цена</th>
                            <th class="text-center">Кол-во</th>
                            <th class="text-end">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-center"><?php echo formatPrice($item['price']); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><?php echo formatPrice($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Стоимость доставки:</td>
                            <td class="text-end"><?php echo formatPrice($order['delivery_cost']); ?></td>
                        </tr>
                        <tr class="receipt-total-row">
                            <td colspan="4" class="text-end fw-bold">Итого:</td>
                            <td class="text-end receipt-total-value"><?php echo formatPrice($order['total_amount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <?php if (!empty($order['comment'])): ?>
                <div class="receipt-info">
                    <h5 class="receipt-section-title">Комментарий к заказу</h5>
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-comment text-muted me-2"></i>
                        <?php echo htmlspecialchars($order['comment']); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="receipt-footer">
                <div class="receipt-thank-you">Спасибо за покупку!</div>
                <p class="receipt-note">Этот документ является подтверждением заказа и не является фискальным чеком.</p>
                <p class="receipt-date">Дата печати: <?php echo date('d.m.Y H:i'); ?></p>
            </div>
        </div>
        
        <div class="text-center mb-4 no-print action-buttons">
            <button onclick="window.print();" class="btn btn-action btn-print">
                <i class="fas fa-print me-2"></i>Печать
            </button>
            <a href="view_order.php?id=<?php echo $order_id; ?>" class="btn btn-action btn-return">
                <i class="fas fa-arrow-left me-2"></i>Вернуться к заказу
            </a>
        </div>
    </div>
    
    <script>
        // Автоматически вызываем диалог печати при загрузке страницы
        window.addEventListener('load', function() {
            // Небольшая задержка для полной загрузки стилей
            setTimeout(function() {
                // Если страница была открыта с параметром print=true, автоматически вызываем печать
                if (window.location.search.includes('print=true')) {
                    window.print();
                }
                
                // Добавляем анимации при прокрутке
                const animateElements = document.querySelectorAll('.receipt-info, .receipt-table, .receipt-footer');
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }
                    });
                });
                
                animateElements.forEach(el => {
                    observer.observe(el);
                });
            }, 500);
        });
    </script>
</body>
</html> 