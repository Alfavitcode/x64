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

<!-- Подключение библиотек для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="/js/libs/particles.min.js"></script>
<script src="/js/animations/home-animations.js"></script>

<style>
/* Стили для фона с частицами - удалены */
/* #particles-js {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    pointer-events: none;
} */

/* Улучшенные стили для секций */
.section {
    position: relative;
    padding: 60px 0;
    overflow: hidden;
}

.section-header {
    position: relative;
    margin-bottom: 40px;
    z-index: 1;
}

.section-title {
    position: relative;
    display: inline-block;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #4e73df, #6f42c1);
    transition: width 0.5s ease;
}

.section-title:hover::after {
    width: 100%;
}

/* Улучшенные стили для карточек товаров */
.product-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    background-color: #fff;
}

.product-image {
    position: relative;
    overflow: hidden;
    border-radius: 15px 15px 0 0;
}

.product-image img {
    transition: transform 0.5s ease;
}

.product-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

.badge {
    margin-right: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.badge-new {
    background-color: #4e73df;
    color: white;
}

.badge-bestseller {
    background-color: #f6c23e;
    color: white;
}

.badge-sale {
    background-color: #e74a3b;
    color: white;
}

/* Улучшенные стили для вкладок */
.tabs {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 20px;
}

.tab {
    margin: 0 5px 10px;
    padding: 8px 20px;
    border-radius: 30px;
    background-color: #f8f9fc;
    color: #5a5c69;
    border: 1px solid #e3e6f0;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.85rem;
}

.tab:hover {
    background-color: #eaecf4;
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
}

.tab.active {
    background-color: #4e73df;
    color: white;
    border-color: #4e73df;
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
}

/* Улучшенные стили для слайдера */
.hero-slider {
    position: relative;
    height: 500px;
    overflow: hidden;
    border-radius: 20px;
    margin-top: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.slider-container {
    position: relative;
    width: 100%;
    height: 100%;
}

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

.slide-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    transform: scale(1);
    transition: transform 8s ease;
}

.slide.active .slide-bg {
    transform: scale(1.1);
}

/* Адаптивность для мобильных устройств */
@media (max-width: 767.98px) {
    .hero-slider {
        height: 300px;
    }
    
    .section {
        padding: 40px 0;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .tabs {
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    
    .tab {
        flex: 0 0 auto;
    }
}
</style>

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
    // Автоматическое переключение слайдов с использованием GSAP
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    
    // Инициализация первого слайда
    gsap.set(slides[0], { opacity: 1 });
    gsap.set(slides[0].querySelector('.slide-bg'), { scale: 1 });
    
    // Функция для изменения слайда
    function changeSlide() {
        // Скрываем текущий слайд
        gsap.to(slides[currentSlide], {
            opacity: 0,
            duration: 1.5,
            ease: 'power2.inOut'
        });
        
        // Сбрасываем масштаб фона текущего слайда
        gsap.to(slides[currentSlide].querySelector('.slide-bg'), {
            scale: 1,
            duration: 0.5,
            ease: 'power2.inOut'
        });
        
        // Переходим к следующему слайду
        currentSlide = (currentSlide + 1) % slides.length;
        
        // Показываем следующий слайд
        gsap.to(slides[currentSlide], {
            opacity: 1,
            duration: 1.5,
            ease: 'power2.inOut'
        });
        
        // Анимируем увеличение фона для следующего слайда
        gsap.fromTo(slides[currentSlide].querySelector('.slide-bg'),
            { scale: 1 },
            { 
                scale: 1.1,
                duration: 8,
                ease: 'power1.inOut'
            }
        );
    }
    
    // Запускаем автоматическое переключение каждые 5 секунд
    setInterval(changeSlide, 5000);
    
    // Начинаем анимацию увеличения для первого слайда
    gsap.to(slides[0].querySelector('.slide-bg'), {
        scale: 1.1,
        duration: 8,
        ease: 'power1.inOut'
    });
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
                        <div class="product-card" data-category="' . $dataCategoryAttr . '">
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

<script>
// Анимация фильтрации товаров по вкладкам
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const productItems = document.querySelectorAll('.product-item');
    
    // Сначала отображаем все товары
    productItems.forEach(item => {
        item.style.display = 'block';
        gsap.to(item, { opacity: 1, duration: 0.5, ease: 'power2.out' });
    });
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Удаляем активный класс со всех вкладок
            tabs.forEach(t => t.classList.remove('active'));
            
            // Добавляем активный класс на текущую вкладку
            this.classList.add('active');
            
            // Получаем категорию
            const category = this.getAttribute('data-category');
            
            // Анимируем фильтрацию товаров
            filterProducts(category);
        });
    });
    
    function filterProducts(category) {
        productItems.forEach(item => {
            const productCard = item.querySelector('.product-card');
            const categories = productCard ? productCard.getAttribute('data-category') : '';
            
            // Проверяем, соответствует ли товар выбранной категории
            const shouldShow = category === 'all' || (categories && categories.split(' ').includes(category));
            
            if (shouldShow) {
                // Показываем товар с анимацией
                gsap.fromTo(item, 
                    { opacity: 0, y: 20 },
                    { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out', clearProps: 'all' }
                );
                item.style.display = 'block';
            } else {
                // Скрываем товар с анимацией
                gsap.to(item, {
                    opacity: 0,
                    y: -20,
                    duration: 0.3,
                    ease: 'power2.in',
                    onComplete: () => {
                        item.style.display = 'none';
                    }
                });
            }
        });
    }
    
    // Проверка наличия атрибутов data-category и логирование для отладки
    console.log('Проверка товаров и их категорий:');
    productItems.forEach((item, index) => {
        const productCard = item.querySelector('.product-card');
        const categories = productCard ? productCard.getAttribute('data-category') : 'нет категорий';
        console.log(`Товар #${index}:`, categories);
    });
});
</script>

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

<!-- Дополнительный скрипт для инициализации фильтров на главной странице -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Убедимся, что фильтры на главной странице работают корректно
    console.log('Дополнительная инициализация фильтров на главной странице...');
    
    // Находим все кнопки фильтров
    const filterButtons = document.querySelectorAll('.tab');
    if (filterButtons.length > 0) {
        console.log('Найдены кнопки фильтров:', filterButtons.length);
        
        // Проверяем, есть ли у них атрибут data-category
        filterButtons.forEach(button => {
            if (!button.hasAttribute('data-category')) {
                console.warn('У кнопки фильтра нет атрибута data-category:', button.textContent);
                
                // Определяем категорию по тексту кнопки
                const buttonText = button.textContent.trim().toLowerCase();
                let category = 'all';
                
                if (buttonText.includes('нов')) {
                    category = 'new';
                } else if (buttonText.includes('скид')) {
                    category = 'sale';
                } else if (buttonText.includes('бестселлер') || buttonText.includes('хит')) {
                    category = 'bestsellers';
                } else if (buttonText.includes('все')) {
                    category = 'all';
                }
                
                // Добавляем атрибут data-category
                button.setAttribute('data-category', category);
                console.log('Добавлен атрибут data-category:', category);
            }
        });
        
        // Проверяем наличие обработчиков событий для кнопок фильтров
        filterButtons.forEach(button => {
            // Проверяем, есть ли у кнопки уже обработчики
            const hasClickListeners = button._click_listeners && button._click_listeners.length > 0;
            
            if (!hasClickListeners) {
                console.log('Добавляем обработчик клика для кнопки:', button.textContent);
                
                button.addEventListener('click', function() {
                    // Удаляем активный класс со всех кнопок
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Добавляем активный класс на текущую кнопку
                    this.classList.add('active');
                    
                    // Получаем категорию
                    const category = this.getAttribute('data-category');
                    console.log('Выбрана категория:', category);
                    
                    // Фильтруем товары
                    const productItems = document.querySelectorAll('.product-item');
                    productItems.forEach(item => {
                        const productCard = item.querySelector('.product-card');
                        const categories = productCard ? productCard.getAttribute('data-category') : '';
                        
                        // Преобразуем категории в массив
                        const categoriesArray = categories ? categories.split(' ') : [];
                        
                        // Проверяем, соответствует ли товар выбранной категории
                        const shouldShow = category === 'all' || categoriesArray.includes(category);
                        
                        if (shouldShow) {
                            // Показываем товар с анимацией
                            item.style.display = 'block';
                            gsap.fromTo(item, 
                                { opacity: 0, y: 20 },
                                { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out', clearProps: 'all' }
                            );
                        } else {
                            // Скрываем товар с анимацией
                            gsap.to(item, {
                                opacity: 0,
                                y: -20,
                                duration: 0.3,
                                ease: 'power2.in',
                                onComplete: () => {
                                    item.style.display = 'none';
                                }
                            });
                        }
                    });
                });
                
                // Отмечаем, что кнопка имеет обработчик
                button._click_listeners = [true];
            }
        });
        
        // Активируем фильтр "Все" по умолчанию, если нет активного фильтра
        const activeButton = document.querySelector('.tab.active');
        if (!activeButton) {
            console.log('Активируем фильтр "Все" по умолчанию');
            const allButton = document.querySelector('.tab[data-category="all"]');
            if (allButton) {
                allButton.classList.add('active');
                allButton.click();
            }
        }
    }
});
</script>