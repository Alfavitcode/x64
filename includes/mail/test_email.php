<?php
/**
 * Скрипт для тестирования отправки писем через разные почтовые сервисы
 * 
 * Этот скрипт позволяет проверить работу отправки писем с разными настройками
 * Запустите его в браузере или через командную строку
 */

// Подключаем класс для работы с почтой
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/Mailer.php';
    $mailer_class_available = true;
} catch (Exception $e) {
    $mailer_class_available = false;
}

// Подключаем простой класс для отправки писем
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/SimpleMailer.php';
$simple_mailer_available = true;

// Определяем переменные для тестирования
$test_email = isset($_GET['email']) ? $_GET['email'] : 'test@example.com';
$mail_service = isset($_GET['service']) ? $_GET['service'] : null;
$mailer_type = isset($_GET['mailer']) ? $_GET['mailer'] : 'auto';

// Если указан сервис в запросе, переключаемся на него
if ($mail_service && in_array($mail_service, ['yandex', 'gmail', 'mail'])) {
    // Перезаписываем выбранный сервис в конфигурации
    $config_file = $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';
    $config_content = file_get_contents($config_file);
    
    // Заменяем строку с выбором сервиса
    $config_content = preg_replace(
        '/\$mail_service\s*=\s*\'[^\']*\';/', 
        "\$mail_service = '$mail_service';", 
        $config_content
    );
    
    // Сохраняем изменения
    file_put_contents($config_file, $config_content);
    
    // Перезагружаем конфигурацию
    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';
}

// Функция для вывода результата в HTML или CLI
function outputResult($title, $result) {
    $is_cli = php_sapi_name() === 'cli';
    
    if ($is_cli) {
        echo "\n===== $title =====\n";
        echo "Результат: " . ($result['success'] ? "УСПЕШНО" : "ОШИБКА") . "\n";
        echo "Сообщение: " . $result['message'] . "\n";
        echo "=============================\n";
    } else {
        echo "<div style='margin: 10px 0; padding: 15px; border-radius: 5px; " . 
             ($result['success'] ? "background-color: #d4edda; color: #155724;" : "background-color: #f8d7da; color: #721c24;") . 
             "'>";
        echo "<h3>$title</h3>";
        echo "<p><strong>Результат:</strong> " . ($result['success'] ? "УСПЕШНО" : "ОШИБКА") . "</p>";
        echo "<p><strong>Сообщение:</strong> " . htmlspecialchars($result['message']) . "</p>";
        echo "</div>";
    }
}

// Выводим заголовок и форму только для веб-версии
if (php_sapi_name() !== 'cli') {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Тестирование отправки писем</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; max-width: 800px; margin: 0 auto; }
            h1 { color: #333; }
            form { margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input, select { padding: 8px; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
            button { background-color: #5165F6; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
            button:hover { background-color: #3951e7; }
            .info { background-color: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .mailer-options { display: flex; gap: 10px; margin-bottom: 15px; }
            .mailer-option { padding: 10px; border-radius: 5px; border: 1px solid #ddd; cursor: pointer; }
            .mailer-option.active { background-color: #e2e6ea; border-color: #5165F6; }
            .mailer-option.disabled { opacity: 0.5; cursor: not-allowed; }
        </style>
    </head>
    <body>
        <h1>Тестирование отправки писем</h1>
        
        <div class="info">
            <p><strong>Текущий почтовый сервис:</strong> ' . $mail_service . '</p>
            <p><strong>SMTP сервер:</strong> ' . MAIL_HOST . '</p>
            <p><strong>Порт:</strong> ' . MAIL_PORT . '</p>
            <p><strong>Шифрование:</strong> ' . MAIL_ENCRYPTION . '</p>
            <p><strong>Email отправителя:</strong> ' . MAIL_FROM_EMAIL . '</p>
            <p><strong>Доступные способы отправки:</strong> ' . 
                ($mailer_class_available ? 'PHPMailer (SMTP), ' : '') . 
                ($simple_mailer_available ? 'SimpleMailer (PHP mail)' : '') . 
            '</p>
        </div>
        
        <form method="get" action="">
            <div>
                <label for="email">Email для отправки тестового письма:</label>
                <input type="email" id="email" name="email" value="' . htmlspecialchars($test_email) . '" required>
            </div>
            
            <div>
                <label for="service">Выберите почтовый сервис:</label>
                <select id="service" name="service">
                    <option value="yandex"' . ($mail_service == 'yandex' ? ' selected' : '') . '>Яндекс.Почта</option>
                    <option value="gmail"' . ($mail_service == 'gmail' ? ' selected' : '') . '>Gmail</option>
                    <option value="mail"' . ($mail_service == 'mail' ? ' selected' : '') . '>Mail.ru</option>
                </select>
            </div>
            
            <div>
                <label>Выберите способ отправки:</label>
                <div class="mailer-options">
                    <label class="mailer-option ' . ($mailer_type == 'auto' ? 'active' : '') . '">
                        <input type="radio" name="mailer" value="auto" ' . ($mailer_type == 'auto' ? 'checked' : '') . '>
                        Автоматический выбор
                    </label>
                    <label class="mailer-option ' . ($mailer_type == 'phpmailer' ? 'active' : '') . ($mailer_class_available ? '' : ' disabled') . '">
                        <input type="radio" name="mailer" value="phpmailer" ' . ($mailer_type == 'phpmailer' ? 'checked' : '') . ($mailer_class_available ? '' : ' disabled') . '>
                        PHPMailer (SMTP)
                    </label>
                    <label class="mailer-option ' . ($mailer_type == 'simple' ? 'active' : '') . '">
                        <input type="radio" name="mailer" value="simple" ' . ($mailer_type == 'simple' ? 'checked' : '') . '>
                        SimpleMailer (PHP mail)
                    </label>
                </div>
            </div>
            
            <button type="submit">Отправить тестовое письмо</button>
        </form>
    ';
}

// Выполняем тесты только если указан email
if ($test_email && $test_email !== 'test@example.com') {
    
    // Определяем, какой класс использовать для отправки
    if ($mailer_type == 'phpmailer' && $mailer_class_available) {
        // Явно выбран PHPMailer
        $mailer = new Mailer();
        $mailer_used = 'PHPMailer (SMTP)';
    } else if ($mailer_type == 'simple' || !$mailer_class_available) {
        // Явно выбран SimpleMailer или PHPMailer недоступен
        $mailer = new SimpleMailer();
        $mailer_used = 'SimpleMailer (PHP mail)';
    } else {
        // Автоматический выбор - сначала пробуем PHPMailer
        if ($mailer_class_available) {
            $mailer = new Mailer();
            $mailer_used = 'PHPMailer (SMTP)';
        } else {
            $mailer = new SimpleMailer();
            $mailer_used = 'SimpleMailer (PHP mail)';
        }
    }
    
    // Выводим информацию о том, какой класс используется
    if (php_sapi_name() !== 'cli') {
        echo '<div class="info">
            <p><strong>Используемый метод отправки:</strong> ' . $mailer_used . '</p>
        </div>';
    }
    
    // Тестируем отправку простого письма
    $result = $mailer->sendTestEmail($test_email);
    outputResult("Тестовое письмо на адрес $test_email", $result);
    
    // Если находимся в веб-версии, добавляем кнопку возврата
    if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_REFERER'])) {
        echo '<p><a href="' . htmlspecialchars($_SERVER['HTTP_REFERER']) . '" style="display: inline-block; margin-top: 20px; text-decoration: none; color: #5165F6;">← Вернуться назад</a></p>';
    }
} else if (php_sapi_name() === 'cli') {
    // В режиме командной строки выводим сообщение о необходимости указать email
    echo "Пожалуйста, укажите email для тестирования в параметре --email=your@email.com\n";
}

// Закрываем HTML-теги только для веб-версии
if (php_sapi_name() !== 'cli') {
    echo '</body></html>';
} 