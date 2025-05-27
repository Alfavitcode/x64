<?php
/**
 * Скрипт для создания панорамного фонового изображения
 * 
 * Этот скрипт создает широкое панорамное изображение для
 * бесконечной плавной прокрутки фона
 */

// Создаем директорию для фоновых изображений, если она не существует
$directory = __DIR__ . '/img/backgrounds';
if (!file_exists($directory)) {
    mkdir($directory, 0755, true);
    echo "Директория создана: $directory\n";
}

// Размеры панорамного изображения (широкое для горизонтального скроллинга)
$width = 3840; // Ширина панорамы (2x стандартного экрана)
$height = 1080; // Высота панорамы

// Массив с цветами для градиентов
$gradients = [
    // Синий градиент
    [
        'colors' => [
            [41, 128, 185],   // Синий
            [52, 152, 219],   // Светло-синий
            [41, 128, 185],   // Синий (повтор для плавного перехода)
        ],
        'name' => 'blue-panorama'
    ],
    // Фиолетовый градиент
    [
        'colors' => [
            [142, 68, 173],   // Фиолетовый
            [155, 89, 182],   // Светло-фиолетовый
            [142, 68, 173],   // Фиолетовый (повтор для плавного перехода)
        ],
        'name' => 'purple-panorama'
    ],
    // Зелено-синий градиент
    [
        'colors' => [
            [26, 188, 156],   // Зеленый
            [41, 128, 185],   // Синий
            [26, 188, 156],   // Зеленый (повтор для плавного перехода)
        ],
        'name' => 'teal-panorama'
    ]
];

// Создаем панорамные изображения
foreach ($gradients as $index => $gradient) {
    // Создаем изображение
    $image = imagecreatetruecolor($width, $height);
    
    // Получаем цвета градиента
    $colors = $gradient['colors'];
    $colorCount = count($colors);
    
    // Создаем горизонтальный градиент
    $segmentWidth = $width / ($colorCount - 1);
    
    for ($x = 0; $x < $width; $x++) {
        // Определяем, в каком сегменте находится текущая точка
        $segment = floor($x / $segmentWidth);
        $segment = min($segment, $colorCount - 2); // Не выходим за пределы массива
        
        // Вычисляем процент позиции внутри сегмента
        $percent = ($x - $segment * $segmentWidth) / $segmentWidth;
        
        // Получаем цвета для интерполяции
        $startColor = $colors[$segment];
        $endColor = $colors[$segment + 1];
        
        // Интерполируем цвета
        $r = $startColor[0] + ($endColor[0] - $startColor[0]) * $percent;
        $g = $startColor[1] + ($endColor[1] - $startColor[1]) * $percent;
        $b = $startColor[2] + ($endColor[2] - $startColor[2]) * $percent;
        
        // Создаем цвет
        $color = imagecolorallocate($image, $r, $g, $b);
        
        // Рисуем вертикальную линию
        imageline($image, $x, 0, $x, $height, $color);
    }
    
    // Добавляем текстуру и эффекты
    addTextureAndEffects($image, $width, $height);
    
    // Формируем путь для сохранения
    $path = $directory . "/panorama-" . ($index + 1) . ".jpg";
    
    // Сохраняем изображение
    imagejpeg($image, $path, 90);
    
    // Освобождаем память
    imagedestroy($image);
    
    echo "Панорамное изображение создано: $path\n";
}

// Функция для добавления текстуры и эффектов
function addTextureAndEffects($image, $width, $height) {
    // Добавляем немного шума для текстуры
    for ($i = 0; $i < 50000; $i++) {
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
    
    // Добавляем волнистые линии для создания эффекта движения
    for ($i = 0; $i < 30; $i++) {
        $startY = mt_rand(0, $height);
        $amplitude = mt_rand(10, 50);
        $period = mt_rand(200, 800);
        $thickness = mt_rand(1, 3);
        $alpha = mt_rand(10, 40); // Прозрачность (0-127)
        
        $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        
        // Рисуем волнистую линию
        for ($x = 0; $x < $width; $x += 2) {
            $y = $startY + $amplitude * sin($x / $period * 2 * M_PI);
            
            // Рисуем точку с заданной толщиной
            for ($t = 0; $t < $thickness; $t++) {
                imagesetpixel($image, $x, $y + $t, $color);
            }
        }
    }
    
    // Добавляем градиентные круги для создания эффекта глубины
    for ($i = 0; $i < 10; $i++) {
        $centerX = mt_rand(0, $width);
        $centerY = mt_rand(0, $height);
        $maxRadius = mt_rand(100, 300);
        
        // Рисуем градиентный круг
        for ($radius = $maxRadius; $radius > 0; $radius -= 2) {
            $alpha = 127 - (127 * $radius / $maxRadius); // Прозрачность увеличивается к центру
            $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            
            // Рисуем окружность
            imagearc($image, $centerX, $centerY, $radius * 2, $radius * 2, 0, 360, $color);
        }
    }
}

echo "Создание панорамных изображений завершено. Изображения сохранены в директории: $directory\n";
?> 