<?php
/**
 * Скрипт для тестирования отправки электронных писем
 */

// Подключаем файл конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';

// Обрабатываем отправку формы
$result = null;
$message = '';
$success = false;
$used_method = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $to = $_POST['email'];
    $method = isset($_POST['method']) ? $_POST['method'] : 'auto';
    
    try {
        if ($method === 'phpmailer' || $method === 'auto') {
            // Пытаемся использовать PHPMailer
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/Mailer.php';
            $mailer = new Mailer();
            $result = $mailer->sendTestEmail($to);
            $used_method = 'PHPMailer (SMTP)';
        } elseif ($method === 'simplemail') {
            // Используем SimpleMailer
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/SimpleMailer.php';
            $mailer = new SimpleMailer();
            $result = $mailer->sendTestEmail($to);
            $used_method = 'SimpleMailer (PHP mail)';
        }
        
        $success = $result['success'];
        $message = $result['message'];
        
        // Логируем результат
        error_log('Test email result: ' . ($success ? 'success' : 'failure') . ', Method: ' . $used_method . ', Message: ' . $message);
        
    } catch (Exception $e) {
        $success = false;
        $message = 'Ошибка: ' . $e->getMessage();
        error_log('Test email exception: ' . $e->getMessage());
    }
}

// Получаем текущие настройки
$current_service = $mail_service;
$current_config = $mail_configs[$current_service];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Тестирование отправки электронных писем</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        h1, h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #5165F6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .success {
            color: green;
            background-color: #f0fff0;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid green;
            margin: 15px 0;
        }
        .error {
            color: #a94442;
            background-color: #f2dede;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #a94442;
            margin: 15px 0;
        }
        .settings {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .method-select {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .method-option {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
        }
        .method-option.selected {
            border-color: #5165F6;
            background-color: #f0f4ff;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            display: inline-block;
            margin-right: 10px;
            color: #5165F6;
            text-decoration: none;
        }
        .nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/includes/mail/check_smtp.php">← Проверка SMTP</a>
        <a href="/includes/mail/phpinfo.php">Информация о PHP</a>
        <a href="/includes/mail/install_phpmailer.php">Установить PHPMailer</a>
    </div>
    
    <h1>Тестирование отправки электронных писем</h1>
    
    <div class="settings">
        <h2>Текущие настройки почты</h2>
        <p><strong>Почтовый сервис:</strong> <?php echo ucfirst($current_service); ?></p>
        <p><strong>SMTP-сервер:</strong> <?php echo $current_config['host']; ?></p>
        <p><strong>Порт:</strong> <?php echo $current_config['port']; ?></p>
        <p><strong>Шифрование:</strong> <?php echo $current_config['encryption']; ?></p>
        <p><strong>Email отправителя:</strong> <?php echo $current_config['from_email']; ?></p>
    </div>
    
    <?php if ($result !== null): ?>
        <div class="<?php echo $success ? 'success' : 'error'; ?>">
            <h3><?php echo $success ? 'Письмо успешно отправлено!' : 'Ошибка при отправке письма'; ?></h3>
            <p><strong>Использованный метод:</strong> <?php echo $used_method; ?></p>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email для тестирования:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Выберите способ отправки:</label>
            <div class="method-select">
                <div class="method-option <?php echo (!isset($_POST['method']) || $_POST['method'] === 'auto') ? 'selected' : ''; ?>" onclick="selectMethod('auto')">
                    <input type="radio" id="auto" name="method" value="auto" <?php echo (!isset($_POST['method']) || $_POST['method'] === 'auto') ? 'checked' : ''; ?>>
                    <label for="auto">Автоматический выбор</label>
                </div>
                <div class="method-option <?php echo (isset($_POST['method']) && $_POST['method'] === 'phpmailer') ? 'selected' : ''; ?>" onclick="selectMethod('phpmailer')">
                    <input type="radio" id="phpmailer" name="method" value="phpmailer" <?php echo (isset($_POST['method']) && $_POST['method'] === 'phpmailer') ? 'checked' : ''; ?>>
                    <label for="phpmailer">PHPMailer (SMTP)</label>
                </div>
                <div class="method-option <?php echo (isset($_POST['method']) && $_POST['method'] === 'simplemail') ? 'selected' : ''; ?>" onclick="selectMethod('simplemail')">
                    <input type="radio" id="simplemail" name="method" value="simplemail" <?php echo (isset($_POST['method']) && $_POST['method'] === 'simplemail') ? 'checked' : ''; ?>>
                    <label for="simplemail">SimpleMailer (PHP mail)</label>
                </div>
            </div>
        </div>
        
        <button type="submit" name="test_email">Отправить тестовое письмо</button>
    </form>
    
    <script>
        function selectMethod(method) {
            document.querySelectorAll('.method-option').forEach(function(el) {
                el.classList.remove('selected');
            });
            document.getElementById(method).parentElement.classList.add('selected');
            document.getElementById(method).checked = true;
        }
    </script>
</body>
</html> 