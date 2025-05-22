<?php
/**
 * AJAX обработчик формы обратной связи
 */

// Заголовок ответа
header('Content-Type: application/json');

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Недопустимый метод запроса'
    ]);
    exit;
}

// Получение данных из формы
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$requestType = isset($_POST['requestType']) ? trim($_POST['requestType']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Валидация полей
$errors = [];

if (empty($name)) {
    $errors[] = 'Поле "Имя" обязательно для заполнения';
}

if (empty($email)) {
    $errors[] = 'Поле "Email" обязательно для заполнения';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный формат Email';
}

if (empty($requestType)) {
    $errors[] = 'Необходимо выбрать тип обращения';
}

if (empty($message)) {
    $errors[] = 'Поле "Сообщение" обязательно для заполнения';
}

// Если есть ошибки, возвращаем их
if (!empty($errors)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Проверьте правильность заполнения формы:<br>' . implode('<br>', $errors)
    ]);
    exit;
}

// В реальном проекте здесь должна быть логика для:
// 1. Сохранения данных в базу данных
// 2. Отправки уведомления администраторам
// 3. Защиты от спама (например, с помощью reCAPTCHA)

// Пример сохранения в БД (закомментировано, так как требует настройки)
/*
require_once '../includes/config/db_functions.php';
$pdo = getPDO();

try {
    $stmt = $pdo->prepare("
        INSERT INTO contact_requests (name, email, phone, request_type, message, created_at)
        VALUES (:name, :email, :phone, :requestType, :message, NOW())
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':requestType' => $requestType,
        ':message' => $message
    ]);
    
    $requestId = $pdo->lastInsertId();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Произошла ошибка при сохранении данных. Пожалуйста, попробуйте позже.'
    ]);
    exit;
}
*/

// Пример отправки письма администратору (закомментировано, так как требует настройки сервера)
/*
$adminEmail = 'admin@x64.ru';
$subject = 'Новое обращение с сайта X64';

$messageBody = "Получено новое обращение с сайта X64\n\n";
$messageBody .= "Имя: {$name}\n";
$messageBody .= "Email: {$email}\n";
$messageBody .= "Телефон: {$phone}\n";
$messageBody .= "Тип обращения: {$requestType}\n";
$messageBody .= "Сообщение: {$message}\n";

$headers = "From: {$email}\r\n";
$headers .= "Reply-To: {$email}\r\n";

mail($adminEmail, $subject, $messageBody, $headers);
*/

// Возвращаем успешный ответ
echo json_encode([
    'status' => 'success',
    'message' => 'Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.'
]);
exit; 