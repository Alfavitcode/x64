<?php
/**
 * Скрипт для создания тестовых фоновых изображений
 * 
 * Этот скрипт создает градиентные изображения для использования
 * в качестве фона для слайдера
 */

// Создаем директорию для фоновых изображений, если она не существует
$directory = __DIR__ . '/img/backgrounds';
if (!file_exists($directory)) {
    mkdir($directory, 0755, true);
    echo "Директория создана: $directory\n";
}

// Массив с цветами для градиентов
$gradients = [
    // Синий градиент
    [
        'start' => [41, 128, 185],
        'end' => [109, 213, 250],
        'name' => 'blue-gradient'
    ],
    // Фиолетовый градиент
    [
        'start' => [142, 68, 173],
        'end' => [155, 89, 182],
        'name' => 'purple-gradient'
    ],
    // Зелено-синий градиент
    [
        'start' => [26, 188, 156],
        'end' => [41, 128, 185],
        'name' => 'teal-gradient'
    ],
    // Оранжево-красный градиент
    [
        'start' => [231, 76, 60],
        'end' => [241, 196, 15],
        'name' => 'orange-gradient'
    ],
    // Темно-синий градиент
    [
        'start' => [44, 62, 80],
        'end' => [52, 152, 219],
        'name' => 'dark-blue-gradient'
    ]
];

// Размеры изображения
$width = 1920;
$height = 1080;

// Создаем изображения для каждого градиента
foreach ($gradients as $index => $gradient) {
    // Создаем изображение
    $image = imagecreatetruecolor($width, $height);
    
    // Получаем цвета градиента
    $startColor = $gradient['start'];
    $endColor = $gradient['end'];
    
    // Создаем градиент
    for ($y = 0; $y < $height; $y++) {
        // Вычисляем процент позиции
        $percent = $y / $height;
        
        // Интерполируем цвета
        $r = $startColor[0] + ($endColor[0] - $startColor[0]) * $percent;
        $g = $startColor[1] + ($endColor[1] - $startColor[1]) * $percent;
        $b = $startColor[2] + ($endColor[2] - $startColor[2]) * $percent;
        
        // Создаем цвет
        $color = imagecolorallocate($image, $r, $g, $b);
        
        // Рисуем линию
        imageline($image, 0, $y, $width, $y, $color);
    }
    
    // Добавляем немного шума для текстуры
    for ($i = 0; $i < 10000; $i++) {
        $x = mt_rand(0, $width - 1);
        $y = mt_rand(0, $height - 1);
        
        // Получаем текущий цвет пикселя
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        
        // Немного изменяем цвет
        $variation = mt_rand(-10, 10);
        $r = max(0, min(255, $r + $variation));
        $g = max(0, min(255, $g + $variation));
        $b = max(0, min(255, $b + $variation));
        
        // Устанавливаем новый цвет
        $color = imagecolorallocate($image, $r, $g, $b);
        imagesetpixel($image, $x, $y, $color);
    }
    
    // Добавляем узор
    for ($i = 0; $i < 50; $i++) {
        $x1 = mt_rand(0, $width);
        $y1 = mt_rand(0, $height);
        $x2 = $x1 + mt_rand(-200, 200);
        $y2 = $y1 + mt_rand(-200, 200);
        
        $color = imagecolorallocatealpha($image, 255, 255, 255, 120); // Полупрозрачный белый
        imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    
    // Формируем путь для сохранения
    $path = $directory . "/bg-" . ($index + 1) . ".jpg";
    
    // Сохраняем изображение
    imagejpeg($image, $path, 90);
    
    // Освобождаем память
    imagedestroy($image);
    
    echo "Изображение создано: $path\n";
}

echo "Создание изображений завершено. Изображения сохранены в директории: $directory\n";
?> 