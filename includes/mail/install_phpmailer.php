<?php
/**
 * Скрипт для установки PHPMailer
 */

// Проверка прав доступа
if (!isset($_GET['install']) || $_GET['install'] !== 'confirm') {
    echo "<h1>Установка PHPMailer</h1>";
    echo "<p>Этот скрипт загрузит и установит PHPMailer в директорию vendor.</p>";
    echo "<p>Нажмите кнопку ниже, чтобы продолжить:</p>";
    echo "<a href=\"?install=confirm\" style=\"display: inline-block; padding: 10px 20px; background: #5165F6; color: white; text-decoration: none; border-radius: 5px;\">Установить PHPMailer</a>";
    exit;
}

// Начинаем установку
echo "<h1>Установка PHPMailer</h1>";

// Создаем директории
$vendor_dir = $_SERVER['DOCUMENT_ROOT'] . '/vendor';
$phpmailer_dir = $vendor_dir . '/phpmailer';

echo "<h2>Шаг 1: Создание директорий</h2>";
if (!is_dir($vendor_dir)) {
    if (mkdir($vendor_dir, 0755, true)) {
        echo "<p style='color: green;'>Директория vendor создана успешно.</p>";
    } else {
        echo "<p style='color: red;'>Ошибка при создании директории vendor.</p>";
        echo "<p>Проверьте права доступа.</p>";
        exit;
    }
} else {
    echo "<p>Директория vendor уже существует.</p>";
}

if (!is_dir($phpmailer_dir)) {
    if (mkdir($phpmailer_dir, 0755, true)) {
        echo "<p style='color: green;'>Директория phpmailer создана успешно.</p>";
    } else {
        echo "<p style='color: red;'>Ошибка при создании директории phpmailer.</p>";
        echo "<p>Проверьте права доступа.</p>";
        exit;
    }
} else {
    echo "<p>Директория phpmailer уже существует.</p>";
}

// Список файлов для загрузки
$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

echo "<h2>Шаг 2: Загрузка файлов</h2>";
foreach ($files as $filename => $url) {
    $file_path = $phpmailer_dir . '/' . $filename;
    
    echo "<p>Загрузка файла {$filename}... ";
    
    // Проверяем, существует ли файл
    if (file_exists($file_path)) {
        echo "файл уже существует, пропускаем.</p>";
        continue;
    }
    
    // Пытаемся загрузить файл
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "ошибка при загрузке файла.</p>";
        echo "<p style='color: red;'>Не удалось загрузить файл с URL: {$url}</p>";
        continue;
    }
    
    // Сохраняем файл
    $result = @file_put_contents($file_path, $content);
    if ($result === false) {
        echo "ошибка при сохранении файла.</p>";
        echo "<p style='color: red;'>Не удалось сохранить файл: {$file_path}</p>";
        echo "<p>Проверьте права доступа.</p>";
    } else {
        echo "успешно загружен и сохранен.</p>";
    }
}

echo "<h2>Шаг 3: Проверка установки</h2>";
$all_exists = true;
foreach ($files as $filename => $url) {
    $file_path = $phpmailer_dir . '/' . $filename;
    if (file_exists($file_path)) {
        echo "<p style='color: green;'>Файл {$filename} существует.</p>";
    } else {
        echo "<p style='color: red;'>Файл {$filename} не существует.</p>";
        $all_exists = false;
    }
}

if ($all_exists) {
    echo "<h2>PHPMailer успешно установлен!</h2>";
    echo "<p>Теперь вы можете использовать PHPMailer для отправки писем.</p>";
    echo "<p><a href='/includes/mail/check_smtp.php' style='display: inline-block; padding: 10px 20px; background: #5165F6; color: white; text-decoration: none; border-radius: 5px;'>Проверить настройки SMTP</a></p>";
} else {
    echo "<h2>Установка PHPMailer не завершена</h2>";
    echo "<p>Не все файлы были успешно загружены. Проверьте ошибки выше и права доступа.</p>";
}

// Проверка работоспособности
echo "<h2>Тест PHPMailer</h2>";
try {
    require_once $phpmailer_dir . '/PHPMailer.php';
    require_once $phpmailer_dir . '/SMTP.php';
    require_once $phpmailer_dir . '/Exception.php';
    
    if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "<p style='color: green;'>Класс PHPMailer успешно загружен.</p>";
    } else {
        echo "<p style='color: red;'>Класс PHPMailer не найден.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка при загрузке PHPMailer: " . $e->getMessage() . "</p>";
} 