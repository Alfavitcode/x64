<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем товары из базы данных (4 товара для главной страницы)
$featuredProducts = getProducts(4);

// Устанавливаем флаг для хедера, чтобы использовать стандартный стиль
$useStandardHeader = true;

// Подключение хедераа
include_once 'includes/header/header.php';
?>

<!-- Подключение общих стилей для карточек товаров -->
<link rel="stylesheet" href="/css/components/product-card.css">

<!-- Главный баннер с слайдером -->
<section class="hero-slider">
        <div class="slider-container">
            <div class="slide active">
            <div class="slide-bg" style="background-image: url('/img/slider/apple.png'); background-size: cover; background-position: center; background-color: #f5f5f5;"></div>
                <div class="slide-content container">
                    <!-- Контент слайдера удален по запросу -->
                </div>
            </div>
            <div class="slide">
            <div class="slide-bg" style="background-image: url('/img/slider/samsung.JPEG'); background-size: cover; background-position: center; background-color: #f5f5f5;"></div>
                <div class="slide-content container">
                    <!-- Контент слайдера удален по запросу -->
                </div>
            </div>
            <div class="slide">
            <div class="slide-bg" style="background-image: url('/img/slider/xiaomi.JPEG'); background-size: cover; background-position: center; background-color: #f5f5f5;"></div>
                <div class="slide-content container">
                    <!-- Контент слайдера удален по запросу -->
                </div>
            </div>
        </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическое переключение слайдов
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    
    // Функция для изменения слайда
    function changeSlide() {
        // Скрываем все слайды
        slides.forEach(slide => slide.classList.remove('active'));
        
        // Переходим к следующему слайду
        currentSlide = (currentSlide + 1) % slides.length;
        
        // Показываем текущий слайд
        slides[currentSlide].classList.add('active');
    }
    
    // Запускаем автоматическое переключение каждые 5 секунд
    setInterval(changeSlide, 5000);
    
    // Добавляем плавную анимацию перехода слайдов
    const style = document.createElement('style');
    style.textContent = `
        .slide {
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .slide.active {
            opacity: 1;
            z-index: 1;
        }
    `;
    document.head.appendChild(style);
});
</script>

<!-- Популярные товары -->
<section class="featured-products section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Популярные товары</h2>
            <div class="tabs">
                <button class="tab active" data-category="all">Все</button>
                <button class="tab" data-category="new">Новинки</button>
                <button class="tab" data-category="sale">Скидки</button>
                <button class="tab" data-category="bestsellers">Бестселлеры</button>
            </div>
        </div>
        
        <div class="products-grid row">
            <?php
            // Если товары найдены в базе данных
            if (!empty($featuredProducts)) {
                foreach ($featuredProducts as $product) {
                    // Определяем метки для товара
                    $badges = '';
                    if (isset($product['is_new']) && $product['is_new']) {
                        $badges .= '<span class="badge badge-new">Новинка</span>';
                    }
                    if (isset($product['is_bestseller']) && $product['is_bestseller']) {
                        $badges .= '<span class="badge badge-bestseller">Хит</span>';
                    }
                    if (isset($product['discount']) && $product['discount'] > 0) {
                        $badges .= '<span class="badge badge-sale">-' . $product['discount'] . '%</span>';
                    }

                    // Формируем категории для атрибута data-category
                    $dataCategories = array();
                    if (isset($product['is_new']) && $product['is_new']) $dataCategories[] = 'new';
                    if (isset($product['is_bestseller']) && $product['is_bestseller']) $dataCategories[] = 'bestsellers';
                    if (isset($product['discount']) && $product['discount'] > 0) $dataCategories[] = 'sale';
                    $dataCategoryAttr = implode(' ', $dataCategories);

                    // Вычисляем старую цену, если есть скидка
                    $oldPriceHtml = '';
                    if (isset($product['discount']) && $product['discount'] > 0 && isset($product['price']) && $product['price'] > 0) {
                        $oldPrice = round($product['price'] * (100 / (100 - $product['discount'])));
                        $oldPriceHtml = '<span class="old-price">' . number_format($oldPrice, 0, '.', ' ') . ' ₽</span>';
                    }

                    // Формируем изображение товара
                    $imageUrl = isset($product['image']) && !empty($product['image']) ? $product['image'] : '/img/products/placeholder.jpg';
                    
                    // Проверяем, существует ли файл изображения
                    $imageFilePath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
                    if (!file_exists($imageFilePath)) {
                        $imageUrl = '/img/products/placeholder.jpg';
                    }

                    // Выводим карточку товара
                    echo '
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-5 product-item">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="' . $imageUrl . '" alt="' . htmlspecialchars(isset($product['name']) ? $product['name'] : '') . '">
                                <div class="product-badges">
                                    ' . $badges . '
                                </div>
                                ' . (isset($product['stock']) && $product['stock'] <= 0 ? '<div class="out-of-stock-overlay"><span class="out-of-stock-label">Нет в наличии</span></div>' : '') . '
                            </div>
                            <div class="product-info">
                                <div class="product-category">' . htmlspecialchars(isset($product['category']) ? $product['category'] : '') . '</div>
                                <h3 class="product-title"><a href="/product.php?id=' . (isset($product['id']) ? $product['id'] : 0) . '">' . htmlspecialchars(isset($product['name']) ? $product['name'] : '') . '</a></h3>
                                <div class="product-price">
                                    <span class="current-price">' . number_format(isset($product['price']) ? $product['price'] : 0, 0, '.', ' ') . ' ₽</span>
                                    ' . $oldPriceHtml . '
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary btn-add-to-cart" data-product-id="' . (isset($product['id']) ? $product['id'] : 0) . '">
                                        <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                // Если товары не найдены, выводим стандартные карточки
            ?>
                <!-- Товар 1 -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-5 product-item">
                    <div class="product-card" data-category="new bestsellers">
                        <div class="product-image">
                            <img src="/img/products/8 красный.PNG" alt="Смартфон XYZ Pro">
                            <div class="product-badges">
                                <span class="badge badge-new">Новинка</span>
                                <span class="badge badge-bestseller">Хит</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Смартфоны</div>
                            <h3 class="product-title"><a href="/product/123">Смартфон XYZ Pro</a></h3>
                            <div class="product-price">
                                <span class="current-price">29 990 ₽</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart" data-product-id="123">
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Товар 2 -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-5 product-item">
                    <div class="product-card" data-category="sale">
                        <div class="product-image">
                            <img src="/img/products/8 белый.PNG" alt="Умные часы SmartLife">
                            <div class="product-badges">
                                <span class="badge badge-sale">-20%</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Аксессуары</div>
                            <h3 class="product-title"><a href="/product/124">Умные часы SmartLife</a></h3>
                            <div class="product-price">
                                <span class="current-price">7 990 ₽</span>
                                <span class="old-price">9 990 ₽</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart" data-product-id="124">
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Товар 3 -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-5 product-item">
                    <div class="product-card" data-category="bestsellers">
                        <div class="product-image">
                            <img src="/img/products/8 розовое золото.PNG" alt="Беспроводные наушники SoundPro">
                            <div class="product-badges">
                                <span class="badge badge-bestseller">Хит</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Наушники</div>
                            <h3 class="product-title"><a href="/product/125">Беспроводные наушники SoundPro</a></h3>
                            <div class="product-price">
                                <span class="current-price">5 490 ₽</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart" data-product-id="125">
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Товар 4 -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-5 product-item">
                    <div class="product-card" data-category="new sale">
                        <div class="product-image">
                            <img src="/img/products/14 red.jpg" alt="Фотокамера ProShot">
                            <div class="product-badges">
                                <span class="badge badge-new">Новинка</span>
                                <span class="badge badge-sale">-15%</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Фототехника</div>
                            <h3 class="product-title"><a href="/product/126">Фотокамера ProShot</a></h3>
                            <div class="product-price">
                                <span class="current-price">25 490 ₽</span>
                                <span class="old-price">29 990 ₽</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart" data-product-id="126">
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/catalog.php" class="btn btn-outline-primary btn-lg btn-catalog">Перейти в каталог</a>
        </div>
    </div>
</section>

<style>
    /* Кнопки с анимациями */
    .pulse-button, .slide-button, .glow-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border-radius: 50px; /* Полностью закругляем кнопки */
        padding: 12px 25px; /* Увеличиваем отступы */
        border: none;
        font-weight: 500;
    }

    .pulse-button:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(255,255,255,0.5);
    }

    .pulse-button:before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        opacity: 0;
    }

    .pulse-button:hover:before {
        animation: pulse 0.8s ease-out;
    }

    @keyframes pulse {
        0% {
            width: 0;
            height: 0;
            opacity: 0.5;
        }
        100% {
            width: 200px;
            height: 200px;
            opacity: 0;
        }
    }

    .slide-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .slide-button:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
        transition: all 0.3s ease;
        border-radius: 50px; /* Закругляем также и эффект свечения */
    }

    .slide-button:hover:before {
        left: 100%;
    }

    .glow-button {
        transition: all 0.3s ease;
    }

    .glow-button:hover {
        box-shadow: 0 0 20px rgba(255,255,255,0.7);
    }

    /* Стили для кнопок фильтров */
    .tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .tab {
        padding: 8px 20px;
        background-color: #f5f5f5;
        border: none;
        border-radius: 30px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #555;
        position: relative;
        overflow: hidden;
    }

    .tab:hover {
        background-color: #e0e0e0;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .tab.active {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }
    
    .tab.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: rgba(255, 255, 255, 0.5);
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 0.5; }
        50% { opacity: 1; }
        100% { opacity: 0.5; }
    }

    /* Глобальные стили для закругления всех контейнеров */
    .section {
        padding: 60px 0; /* Увеличиваем вертикальные отступы */
    }

    .container {
        padding: 0 30px; /* Увеличиваем горизонтальные отступы */
    }

    /* Стили для карточек товаров */
    /* Удалены дублирующиеся стили, теперь они перенесены в общий файл css/components/product-card.css */

    .feature-card {
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .feature-icon {
        background-color: rgba(var(--primary-color-rgb), 0.1);
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 24px;
        color: var(--primary-color);
    }

    /* Стили для секции О нас */
    .about-us {
        position: relative;
        overflow: hidden;
    }

    .about-us .section-title {
        margin-bottom: 30px;
    }

    .about-list {
        list-style: none;
        padding-left: 0;
    }

    .about-list li {
        margin-bottom: 15px;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .about-list li i {
        width: 25px;
        font-size: 18px;
    }

    .about-image-container {
        position: relative;
        padding: 15px;
        border-radius: 16px;
        overflow: hidden;
    }

    .about-experience {
        position: absolute;
        bottom: 30px;
        left: 30px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 16px;
        padding: 15px 25px;
        display: flex;
        align-items: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .experience-years {
        font-size: 32px;
        font-weight: 700;
        margin-right: 10px;
        line-height: 1;
    }

    .experience-text {
        font-size: 16px;
        line-height: 1.2;
    }

    @media (max-width: 991px) {
        .about-image-container {
            margin-top: 30px;
        }
    }

    /* Стили для модальных окон и диалогов */
    .product-quickview-content {
        border-radius: 20px;
    }

    .angle-view {
        border-radius: 50px !important;
    }

    .phone-model-select {
        border-radius: 50px !important;
    }

    .color-option {
        border-radius: 50% !important;
    }

    /* Стили для улучшения ощущения глубины и объема */
    .section-title {
        position: relative;
        display: inline-block;
        margin-bottom: 40px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 50px;
    }

    /* Мягкие переходы цветов и теней */
    :root {
        --transition-standard: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * {
        transition: var(--transition-standard);
    }

    /* Стили для слайдера */
    .hero-slider {
        padding: 30px 0;
        background-color: transparent;
    }

    .slider-container {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: none;
        height: 500px;
        max-height: 60vh;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.8s ease;
        display: flex;
        align-items: center;
    }

    .slide.active {
        opacity: 1;
        z-index: 1;
    }

    .slide-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-color: transparent;
        filter: none;
        transition: none;
    }

    .slide:hover .slide-bg {
        transform: none;
        filter: none;
    }

    .slide-content {
        position: relative;
        z-index: 2;
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
        color: #212529;
    }

    /* Убираем градиент, который может мешать видеть изображение полностью */
    .slide::after {
        display: none;
    }

    /* Адаптив для мобильных устройств */
    @media (max-width: 768px) {
        .slider-container {
            height: 400px;
        }
        
        .slide-content {
            padding: 20px;
        }
    }

    @media (max-width: 576px) {
        .slider-container {
            height: 300px;
        }
    }

    .product-item {
        opacity: 0;
        transform: translateY(10px); /* Уменьшаем высоту смещения */
        animation: fadeInUp 0.4s forwards;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Задержка анимации для каждого элемента */
    .product-item:nth-child(1) { animation-delay: 0.05s; }
    .product-item:nth-child(2) { animation-delay: 0.1s; }
    .product-item:nth-child(3) { animation-delay: 0.15s; }
    .product-item:nth-child(4) { animation-delay: 0.2s; }
    .product-item:nth-child(5) { animation-delay: 0.25s; }
    .product-item:nth-child(6) { animation-delay: 0.3s; }
    .product-item:nth-child(7) { animation-delay: 0.35s; }
    .product-item:nth-child(8) { animation-delay: 0.4s; }

    /* Стили для бейджей (меток) */
    /* Удалены дублирующиеся стили бейджей, теперь они в общем файле css/components/product-card.css */

    /* Адаптивность для карточек товаров */
    /* Удалены дублирующиеся адаптивные стили, теперь они в общем файле css/components/product-card.css */

    /* Удален дублирующийся стиль product-actions, теперь он в общем файле css/components/product-card.css */

    /* Общие CSS-переменные вынесены в общий файл css/components/product-card.css */
    
    /* Дополнительные стили для точного соответствия изображениям */
    .product-card {
        box-shadow: 0 2px 10px rgba(0,0,0,0.04) !important;
        border: none !important;
    }
</style>

<!-- Преимущества -->
<section class="features section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="feature-title">Быстрая доставка</h3>
                    <p class="feature-description">Доставка по всей России от 1 до 7 дней</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3 class="feature-title">Возврат товара</h3>
                    <p class="feature-description">14 дней на возврат без объяснения причин</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="feature-title">Безопасная оплата</h3>
                    <p class="feature-description">Защищенные платежи и безопасные покупки</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">Поддержка 24/7</h3>
                    <p class="feature-description">Круглосуточная поддержка клиентов</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- О нас -->
<section class="about-us section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="section-title">О нас</h2>
                <p class="mb-4">
                    <strong>x64</strong> – это современный сервисный центр и магазин техники, специализирующийся на ремонте, обслуживании и продаже мобильных устройств и аксессуаров.
                </p>
                <p class="mb-4">
                    Мы предлагаем широкий ассортимент продукции:
                </p>
                <ul class="about-list mb-4">
                    <li><i class="fas fa-mobile-alt me-2 text-primary"></i> Новые и восстановленные смартфоны</li>
                    <li><i class="fas fa-microchip me-2 text-primary"></i> Оригинальные запчасти для всех моделей телефонов</li>
                    <li><i class="fas fa-headphones me-2 text-primary"></i> Аудиотехника и аксессуары</li>
                    <li><i class="fas fa-tools me-2 text-primary"></i> Профессиональный ремонт любой сложности</li>
                    <li><i class="fas fa-shield-alt me-2 text-primary"></i> Защитные чехлы, стекла и пленки</li>
                </ul>
                <p>
                    Наши специалисты имеют многолетний опыт работы с мобильными устройствами и электроникой. Мы гарантируем качество наших товаров и услуг, а также обеспечиваем техническую поддержку на каждом этапе сотрудничества.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="about-image-container">
                    <img src="/img/slider/xiaomi.JPEG" alt="Сервисный центр x64" class="img-fluid rounded shadow-lg">
                    <div class="about-experience">
                        <span class="experience-years">5+</span>
                        <span class="experience-text">лет<br>опыта</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?>

<!-- Подключение jQuery, если он еще не подключен -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Подключение скриптов анимации -->
<script src="js/banner-animations.js"></script>

<!-- Скрипт для фильтрации товаров -->
<script>
// Функция для проверки загрузки jQuery с повторными попытками функционал перенесен в общий файл js/product-cards.js
</script>

<!-- Функция для инициализации карточек товаров -->
<script src="/js/product-cards.js"></script>