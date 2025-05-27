<?php
/**
 * Файл автозагрузки для PHPMailer
 * 
 * Этот файл регистрирует пространства имен для корректной работы PHPMailer
 */

// Проверяем, существуют ли уже классы PHPMailer
if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
    $phpmailer_dir = $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/';
    
    // Подключаем файлы напрямую
    if (file_exists($phpmailer_dir . 'Exception.php')) {
        require_once $phpmailer_dir . 'Exception.php';
    }
    
    if (file_exists($phpmailer_dir . 'PHPMailer.php')) {
        require_once $phpmailer_dir . 'PHPMailer.php';
    }
    
    if (file_exists($phpmailer_dir . 'SMTP.php')) {
        require_once $phpmailer_dir . 'SMTP.php';
    }
}

// Регистрируем автозагрузчик для PHPMailer на случай, если прямые включения не сработали
spl_autoload_register(function ($class) {
    // Проверяем, является ли класс частью PHPMailer
    if (strpos($class, 'PHPMailer\\PHPMailer\\') === 0) {
        // Получаем имя файла из имени класса
        $class_name = substr($class, strrpos($class, '\\') + 1);
        
        // Путь к файлу PHPMailer
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/' . $class_name . '.php';
        
        // Проверяем, существует ли файл, и подключаем его
        if (file_exists($file_path)) {
            require_once $file_path;
            return true;
        }
    }
    
    return false;
}); 