<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем товары из базы данных (4 товара для главной страницы)
$featuredProducts = getProducts(4);

// Устанавливаем флаг для хедера, чтобы использовать стандартный стиль
$useStandardHeader = true;

// Подключение хедера
include_once 'includes/header/header.php';
?>

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
                    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                        <div class="product-card h-100" data-category="' . $dataCategoryAttr . '">
                            <div class="product-badges">
                                ' . $badges . '
                            </div>
                            <div class="product-image">
                                <img src="' . $imageUrl . '" alt="' . htmlspecialchars(isset($product['name']) ? $product['name'] : '') . '">
                                ' . (isset($product['stock']) && $product['stock'] <= 0 ? '<div class="out-of-stock-overlay"><span class="out-of-stock-label">Нет в наличии</span></div>' : '') . '
                            </div>
                            <div class="product-info">
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
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card h-100" data-category="new bestsellers">
                        <div class="product-badges">
                            <span class="badge badge-new">Новинка</span>
                            <span class="badge badge-bestseller">Хит</span>
                        </div>
                        <div class="product-image">
                            <img src="/img/products/8 красный.PNG" alt="Смартфон XYZ Pro">
                        </div>
                        <div class="product-info">
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
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card h-100" data-category="sale">
                        <div class="product-badges">
                            <span class="badge badge-sale">-20%</span>
                        </div>
                        <div class="product-image">
                            <img src="/img/products/8 белый.PNG" alt="Умные часы SmartLife">
                        </div>
                        <div class="product-info">
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
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card h-100" data-category="bestsellers">
                        <div class="product-badges">
                            <span class="badge badge-bestseller">Хит</span>
                        </div>
                        <div class="product-image">
                            <img src="/img/products/8 розовое золото.PNG" alt="Беспроводные наушники SoundPro">
                        </div>
                        <div class="product-info">
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
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card h-100" data-category="new sale">
                        <div class="product-badges">
                            <span class="badge badge-new">Новинка</span>
                            <span class="badge badge-sale">-15%</span>
                        </div>
                        <div class="product-image">
                            <img src="/img/products/14 red.jpg" alt="Фотокамера ProShot">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><a href="/product/126">Фотокамера ProShot</a></h3>
                            <div class="product-price">
                                <span class="current-price">42 490 ₽</span>
                                <span class="old-price">49 990 ₽</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add-to-cart" data-product-id="126">
                                    <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        
        <div class="text-center">
            <a href="/catalog.php" class="btn btn-catalog"><i class="fas fa-store"></i> Все товары</a>
        </div>
    </div>
</section>

<!-- Баннеры с анимациями -->
<section class="banners section">
    <div class="container">
        <div class="banners-grid">
            <div class="banner banner-large animated-banner">
                <div class="banner-image" style="background-image: url('/img/products/8 красный.PNG');"></div>
                <div class="banner-content">
                    <h3 class="banner-title">Эксклюзивные крышки</h3>
                    <p class="banner-text">Скидки до 30% на премиум коллекцию</p>
                    <div class="banner-button">
                        <a href="/catalog.php?category=premium" class="btn btn-white pulse-button">Смотреть коллекцию</a>
                    </div>
                </div>
                <div class="banner-badge">
                    <span>-30%</span>
                </div>
            </div>
            <div class="banner banner-small animated-banner">
                <div class="banner-image" style="background-image: url('/img/products/8 белый.PNG');"></div>
                <div class="banner-content">
                    <h3 class="banner-title">Защитные кейсы</h3>
                    <p class="banner-text">Максимальная защита вашего телефона</p>
                    <div class="banner-button">
                        <a href="/catalog.php?category=cases" class="btn btn-white slide-button">Выбрать кейс</a>
                    </div>
                </div>
                <div class="banner-overlay"></div>
            </div>
            <div class="banner banner-small animated-banner">
                <div class="banner-image" style="background-image: url('/img/products/8 розовое золото.PNG');"></div>
                <div class="banner-content">
                    <h3 class="banner-title">Новые поступления</h3>
                    <p class="banner-text">Трендовые дизайны 2025 года</p>
                    <div class="banner-button">
                        <a href="/catalog.php?category=new" class="btn btn-white glow-button">Смотреть новинки</a>
                    </div>
                </div>
                <div class="banner-badge new-badge">NEW</div>
            </div>
        </div>
    </div>
</section>

<!-- Добавляем стили для анимаций баннеров -->
<style>
.animated-banner {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 16px; /* Закругляем углы баннеров */
    height: 100%;
    display: flex;
}

.animated-banner:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.banner-image {
    transition: transform 0.5s ease, filter 0.5s ease;
    background-size: cover;
    background-position: center;
    border-radius: 16px; /* Закругляем углы изображений */
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    z-index: 1;
}

.animated-banner:hover .banner-image {
    transform: scale(1.05);
    filter: brightness(1.1);
}

.banner-content {
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease;
    padding: 25px; /* Увеличиваем отступы для более современного вида */
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 300px;
}

.banner-small .banner-content {
    min-height: 250px;
}

.animated-banner:hover .banner-content {
    transform: translateY(-5px);
}

.banner-title {
    margin-top: 10px;
    margin-bottom: 10px;
    color: white;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.banner-text {
    color: rgba(255,255,255,0.9);
    margin-bottom: 20px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.banner-button {
    margin-top: auto;
}

.banner-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #ff3366;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3);
    transform: rotate(10deg);
    transition: transform 0.3s ease;
    z-index: 3;
}

.new-badge {
    background-color: #33cc66;
    width: auto;
    height: auto;
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 16px;
    box-shadow: 0 5px 15px rgba(51, 204, 102, 0.3);
}

.animated-banner:hover .banner-badge {
    transform: rotate(0deg) scale(1.1);
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.2) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 16px; /* Закругляем углы оверлея */
    z-index: 2;
}

.animated-banner:hover .banner-overlay {
    opacity: 1;
}

.banner-tag {
    position: absolute;
    top: 20px;
    left: 20px;
    background-color: #33cc66;
    color: white;
    padding: 8px 18px; /* Увеличиваем отступы */
    font-weight: bold;
    border-radius: 50px; /* Делаем тег полностью закругленным */
    transform: translateY(-5px);
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
    z-index: 3;
}

.animated-banner:hover .banner-tag {
    transform: translateY(0);
    opacity: 1;
}

/* Добавим затемнение фона для лучшей читаемости текста */
.banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.4) 100%);
    z-index: 2;
    border-radius: 16px;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.animated-banner:hover::before {
    opacity: 0.5;
}

/* Стиль для баннеров в сетке */
.banners-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 20px;
}

.banner-large {
    grid-column: 1;
    grid-row: 1 / span 2;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .banners-grid {
        grid-template-columns: 1fr;
    }
    
    .banner-large {
        grid-column: 1;
        grid-row: auto;
    }
    
    .banner-content {
        min-height: 220px;
    }
}

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

/* Глобальные стили для закругления всех контейнеров */
.section {
    padding: 60px 0; /* Увеличиваем вертикальные отступы */
}

.container {
    padding: 0 30px; /* Увеличиваем горизонтальные отступы */
}

.product-card {
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.product-card:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.product-image {
    border-radius: 16px 16px 0 0;
    overflow: hidden;
}

/* Стили для кнопок в карточках товаров */
.btn-add-to-cart {
    border-radius: 50px;
    width: 90%;
    margin: 15px auto;
    display: block;
    padding: 12px;
}

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

.newsletter-container {
    border-radius: 20px;
    padding: 40px;
}

.newsletter-input {
    border-radius: 50px;
    padding: 15px 25px;
    border: none;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.newsletter-form-group .btn {
    border-radius: 50px;
}

.badge {
    border-radius: 50px;
    padding: 5px 12px;
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

<!-- Подписка на рассылку -->
<section class="newsletter section">
    <div class="container">
        <div class="newsletter-container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="newsletter-content">
                        <h2 class="newsletter-title">Подпишитесь на рассылку</h2>
                        <p class="newsletter-description">
                            Получайте уведомления о новых товарах, эксклюзивных предложениях и скидках
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form">
                        <div class="newsletter-form-group mb-3">
                            <div class="input-group">
                                <input type="email" class="form-control newsletter-input" placeholder="Ваш email">
                                <button type="submit" class="btn btn-primary">Подписаться</button>
                            </div>
                        </div>
                        <div class="newsletter-agreement form-check">
                            <input type="checkbox" class="form-check-input" id="agreement">
                            <label class="form-check-label" for="agreement">Я согласен с <a href="/privacy">Политикой конфиденциальности</a></label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?>

<!-- Подключение скриптов анимации -->
<script src="js/banner-animations.js"></script>
