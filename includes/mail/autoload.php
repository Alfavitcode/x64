<?php
/**
 * Файл автозагрузки для PHPMailer
 * 
 * Этот файл регистрирует пространства имен для корректной работы PHPMailer
 */

// Регистрируем автозагрузчик для PHPMailer
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