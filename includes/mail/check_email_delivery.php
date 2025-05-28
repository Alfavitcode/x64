<?php
/**
 * Скрипт для проверки доставки электронных писем
 * Этот скрипт отправляет тестовое письмо с уникальным идентификатором
 * и помогает проверить, почему письма могут не доходить
 */

// Подключаем файл конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';

// Генерируем уникальный идентификатор для письма
$test_id = uniqid();
$result = null;
$message = '';
$success = false;
$used_method = '';

// Обрабатываем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $to = $_POST['email'];
    $method = isset($_POST['method']) ? $_POST['method'] : 'auto';
    
    try {
        // Создаем уникальный заголовок для отслеживания письма
        $subject = "Тестовое письмо #{$test_id}";
        $special_id = "X64SHOP-TEST-" . $test_id;
        
        // Журналируем отправку
        error_log("Sending test email #{$test_id} to {$to} using method {$method}");
        
        // Формируем тело письма с уникальным идентификатором
        $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$subject}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #5165F6; margin-bottom: 10px;">Тестовое письмо</h1>
        <p style="font-size: 18px; color: #666;">Это тестовое письмо для проверки доставки.</p>
    </div>
    
    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
        <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Информация о письме</h2>
        <p style="margin: 5px 0;"><strong>Уникальный ID:</strong> {$special_id}</p>
        <p style="margin: 5px 0;"><strong>Дата и время отправки:</strong> {$timestamp}</p>
        <p style="margin: 5px 0;"><strong>Отправитель:</strong> {$current_config['from_email']}</p>
        <p style="margin: 5px 0;"><strong>Получатель:</strong> {$to}</p>
        <p style="margin: 5px 0;"><strong>Метод отправки:</strong> {$method}</p>
    </div>
    
    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px;">
        <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Что делать дальше?</h2>
        <p style="margin: 5px 0;">1. Проверьте папку "Входящие" вашей почты</p>
        <p style="margin: 5px 0;">2. Если письмо не пришло, проверьте папку "СПАМ"</p>
        <p style="margin: 5px 0;">3. Если письмо все равно не пришло, проверьте фильтры и настройки почтового клиента</p>
        <p style="margin: 5px 0;">4. Убедитесь, что домен отправителя не заблокирован в вашей почте</p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;">
        <p>&copy; {$year} X64Shop. Все права защищены.</p>
        <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
    </div>
</body>
</html>
HTML;

        // Отправляем письмо выбранным методом
        if ($method === 'phpmailer' || $method === 'auto') {
            // Пытаемся использовать PHPMailer
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/Mailer.php';
            $mailer = new Mailer();
            $result = $mailer->send($to, $subject, $body);
            $used_method = 'PHPMailer (SMTP)';
        } elseif ($method === 'simplemail') {
            // Используем SimpleMailer
            require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/SimpleMailer.php';
            $mailer = new SimpleMailer();
            $result = $mailer->send($to, $subject, $body);
            $used_method = 'SimpleMailer (PHP mail)';
        }
        
        $success = $result['success'];
        $message = $result['message'];
        
        // Логируем результат
        error_log("Test email #{$test_id} result: " . ($success ? 'success' : 'failure') . 
                  ", Method: {$used_method}, Message: {$message}");
        
    } catch (Exception $e) {
        $success = false;
        $message = 'Ошибка: ' . $e->getMessage();
        error_log("Test email #{$test_id} exception: " . $e->getMessage());
    }
}

// Форматируем текущую дату и время
$timestamp = date('Y-m-d H:i:s');
$year = date('Y');

// Получаем текущие настройки
$current_service = $mail_service;
$current_config = $mail_configs[$current_service];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка доставки электронных писем</title>
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
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid green;
            margin: 15px 0;
        }
        .error {
            color: #a94442;
            background-color: #f2dede;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #a94442;
            margin: 15px 0;
        }
        .info {
            color: #31708f;
            background-color: #d9edf7;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #31708f;
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
        .checklist {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .checklist h3 {
            margin-top: 0;
        }
        .checklist ul {
            padding-left: 20px;
        }
        .checklist li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/includes/mail/check_smtp.php">← Проверка SMTP</a>
        <a href="/includes/mail/phpinfo.php">Информация о PHP</a>
        <a href="/includes/mail/test_email.php">Тестирование Email</a>
    </div>
    
    <h1>Проверка доставки электронных писем</h1>
    
    <div class="info">
        <h3>Как это работает?</h3>
        <p>Этот скрипт отправляет тестовое письмо с уникальным идентификатором. По этому идентификатору вы сможете найти письмо в своем почтовом ящике, даже если оно попало в СПАМ.</p>
    </div>
    
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
            <h3><?php echo $success ? 'Письмо отправлено!' : 'Ошибка при отправке письма'; ?></h3>
            <p><strong>Уникальный ID письма:</strong> X64SHOP-TEST-<?php echo $test_id; ?></p>
            <p><strong>Использованный метод:</strong> <?php echo $used_method; ?></p>
            <p><?php echo $message; ?></p>
            
            <?php if ($success): ?>
                <div class="info" style="margin-top: 15px;">
                    <h4>Инструкции для проверки:</h4>
                    <ol>
                        <li>Проверьте папку "Входящие" в почтовом ящике <?php echo htmlspecialchars($to); ?></li>
                        <li>Если письмо не пришло, проверьте папку "СПАМ" или "Нежелательная почта"</li>
                        <li>Используйте поиск по уникальному ID: <strong>X64SHOP-TEST-<?php echo $test_id; ?></strong></li>
                        <li>Проверьте доставку через 5-10 минут, если письмо не пришло сразу</li>
                    </ol>
                </div>
            <?php endif; ?>
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
    
    <div class="checklist">
        <h3>Почему письма могут не доходить?</h3>
        <ul>
            <li><strong>Фильтры СПАМ</strong> - Многие почтовые сервисы имеют строгие фильтры СПАМ</li>
            <li><strong>Некорректные настройки SMTP</strong> - Проверьте логин/пароль и настройки шифрования</li>
            <li><strong>Блокировка портов</strong> - Хостинг может блокировать порты для SMTP (465, 587)</li>
            <li><strong>Проблемы с DNS</strong> - Обратные DNS-записи могут влиять на доставку писем</li>
            <li><strong>Отсутствие SPF/DKIM</strong> - Отсутствие этих записей снижает доверие к письмам</li>
            <li><strong>Блокировка IP</strong> - IP-адрес сервера может быть в черных списках</li>
            <li><strong>Ограничения почтовых сервисов</strong> - Gmail, Яндекс и другие могут иметь лимиты на получение писем</li>
        </ul>
    </div>
    
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