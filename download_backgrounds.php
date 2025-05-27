<?php
/**
 * Скрипт для загрузки фоновых изображений с Unsplash
 * 
 * Этот скрипт загружает красивые изображения для фона слайдера
 * с сервиса Unsplash и сохраняет их в директорию img/backgrounds
 */

// Создаем директорию для фоновых изображений, если она не существует
$directory = __DIR__ . '/img/backgrounds';
if (!file_exists($directory)) {
    mkdir($directory, 0755, true);
    echo "Директория создана: $directory\n";
}

// Массив с темами для фоновых изображений
$themes = [
    'abstract blue',
    'gradient blue',
    'minimal blue pattern',
    'soft blue background',
    'blue technology'
];

// Функция для загрузки изображения
function downloadImage($url, $path) {
    $ch = curl_init($url);
    $fp = fopen($path, 'wb');
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "Ошибка загрузки: " . curl_error($ch) . "\n";
        return false;
    }
    
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    
    if ($statusCode == 200) {
        echo "Изображение успешно загружено: $path\n";
        return true;
    } else {
        echo "Ошибка загрузки, статус код: $statusCode\n";
        return false;
    }
}

// Загружаем изображения для каждой темы
foreach ($themes as $index => $theme) {
    // Формируем URL для Unsplash Source API
    $theme = urlencode($theme);
    $url = "https://source.unsplash.com/1920x1080/?" . $theme;
    
    // Формируем путь для сохранения
    $path = $directory . "/bg-" . ($index + 1) . ".jpg";
    
    echo "Загрузка изображения для темы '$theme'...\n";
    
    // Загружаем изображение
    if (downloadImage($url, $path)) {
        // Добавляем небольшую задержку, чтобы не перегружать API
        sleep(1);
    }
}

echo "Загрузка завершена. Изображения сохранены в директории: $directory\n";
?> 