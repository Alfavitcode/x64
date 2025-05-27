<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем ID товара из GET-параметра
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о товаре
$product = getProductById($productId);

// Если товар не найден, перенаправляем на главную
if (!$product) {
    header('Location: /');
    exit;
}

// Подключение хедера
include_once 'includes/header/header.php';

// Подготовка данных для отображения
$imageUrl = !empty($product['image']) ? $product['image'] : '/img/products/no-image.jpg';

// Проверяем, существует ли файл изображения
if (!empty($imageUrl) && $imageUrl != '/img/products/no-image.jpg') {
    $imageFilePath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
    if (!file_exists($imageFilePath)) {
        $imageUrl = '/img/products/no-image.jpg';
    }
}

$shortDescription = mb_substr(strip_tags($product['description']), 0, 150) . (mb_strlen($product['description']) > 150 ? '...' : '');
$fullDescription = $product['description'];
$price = number_format($product['price'], 0, '.', ' ');
$hasDiscount = isset($product['discount']) && $product['discount'] > 0;
$oldPrice = $hasDiscount ? number_format($product['price'] * (100 / (100 - $product['discount'])), 0, '.', ' ') : null;
?>

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Основной контейнер товара */
    .product-container {
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 30px;
        position: relative;
        z-index: 1;
    }
    
    /* Заголовок товара */
    .product-header {
        padding: 20px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        position: relative;
        z-index: 2;
    }
    
    .product-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #212529;
    }
    
    .product-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #6c757d;
        font-size: 14px;
    }
    
    .product-sku {
        white-space: nowrap;
    }
    
    .product-availability {
        color: #34c759;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .product-availability.out-of-stock {
        color: #ff3b30;
    }
    
    /* Основное содержимое */
    .product-content {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        background-color: #fff;
        position: relative;
        z-index: 2;
    }
    
    /* Изображение товара */
    .product-image-container {
        flex: 0 0 50%;
        min-width: 300px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff;
        position: relative;
        text-align: center;
        min-height: 300px;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
    }
    
    .product-image {
        max-width: 100%;
        max-height: 400px;
        object-fit: contain;
        width: auto;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    
    /* Добавляем медиа-запрос для мобильных устройств */
    @media (max-width: 768px) {
        .product-content {
            flex-direction: column;
            flex-wrap: wrap;
        }
        
        .product-image-container {
            flex: 1 0 100%;
            padding: 0;
            margin: 0;
            width: 100%;
            text-align: center;
            min-height: auto;
            background-color: #fff;
        }
        
        .product-image {
            width: 100%;
            height: auto;
            max-width: 100%;
            max-height: none;
            margin: 0 auto;
            padding: 0;
            object-fit: cover;
        }
        
        .product-info-container {
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            padding: 15px;
        }
        
        .product-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .product-actions {
            flex-direction: column;
            align-items: stretch;
        }
        
        .quantity-control {
            width: 100%;
        }
        
        .add-to-cart-btn {
            width: 100%;
        }
        
        .specs-item {
            flex-direction: column;
        }
        
        .specs-name, .specs-value {
            flex: 0 0 100%;
        }
    }
    
    /* Правая колонка */
    .product-info-container {
        flex: 1;
        min-width: 300px;
        max-width: 50%;
        display: flex;
        flex-direction: column;
        padding: 20px;
    }
    
    /* Блок с ценой и добавлением в корзину */
    .product-purchase-block {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    
    /* Отдельный стиль для контейнера кнопки, чтобы избежать проблем с видимостью */
    .cart-button-container {
        display: block;
        margin-top: 15px;
        width: 100%;
    }
    
    .product-price-block {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .product-price {
        font-size: 28px;
        font-weight: 700;
        color: #212529;
    }
    
    .product-old-price {
        font-size: 16px;
        color: #6c757d;
        text-decoration: line-through;
        margin-left: 10px;
    }
    
    .product-discount {
        background-color: #ff3b30;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        margin-left: 10px;
    }
    
    .product-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        width: 100%;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
        max-width: 130px;
    }
    
    .quantity-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: #f8f9fa;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .quantity-btn:hover {
        background-color: #e9ecef;
    }
    
    .quantity-input {
        width: 50px;
        height: 40px;
        border: none;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        text-align: center;
        font-size: 16px;
    }
    
    .add-to-cart-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 50px;
        background-color: #0d6efd;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0 20px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 200px;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        opacity: 1;
        visibility: visible;
        width: 100%;
    }
    
    .add-to-cart-btn:hover {
        background-color: #0b5ed7;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(13, 110, 253, 0.3);
    }
    
    .add-to-cart-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
    }
    
    .add-to-cart-btn.disabled {
        background-color: #6c757d;
        cursor: not-allowed;
        box-shadow: none;
    }
    
    .cart-icon {
        margin-right: 10px;
        font-size: 18px;
    }
    
    /* Блок с описанием */
    .product-description-block {
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .product-description-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #212529;
        position: relative;
        padding-left: 15px;
    }
    
    .product-description-title:before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 5px;
        height: 18px;
        background-color: #0d6efd;
        border-radius: 3px;
    }
    
    .product-description-content {
        color: #495057;
        line-height: 1.6;
    }
    
    /* Вкладки */
    .product-tabs {
        margin-top: 20px;
        padding: 0 20px 20px;
    }
    
    .nav-tabs {
        border: none;
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 30px;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link.active {
        color: #fff;
        background-color: #0d6efd;
    }
    
    .nav-tabs .nav-link:hover:not(.active) {
        background-color: #e9ecef;
    }
    
    .tab-content {
        padding: 0;
    }
    
    /* Характеристики */
    .specs-card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .specs-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .specs-item {
        display: flex;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .specs-item:last-child {
        border-bottom: none;
    }
    
    .specs-name {
        flex: 0 0 30%;
        padding: 12px 15px;
        background-color: #f8f9fa;
        font-weight: 500;
        color: #495057;
    }
    
    .specs-value {
        flex: 0 0 70%;
        padding: 12px 15px;
        color: #212529;
    }
    
    /* Доставка */
    .delivery-method {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .delivery-method:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .delivery-icon {
        flex: 0 0 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9f5ff;
        border-radius: 50%;
        color: #0d6efd;
        font-size: 20px;
        margin-right: 15px;
    }
    
    .delivery-content {
        flex: 1;
    }
    
    .delivery-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .delivery-description {
        color: #6c757d;
        font-size: 14px;
    }
</style>

<div class="container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Главная</a></li>
            <li class="breadcrumb-item"><a href="/catalog.php" class="text-decoration-none">Каталог</a></li>
            <li class="breadcrumb-item"><a href="/catalog.php?category=<?php echo urlencode($product['category']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <!-- Основной контейнер товара -->
    <div class="product-container">
        <!-- Заголовок с названием товара, артикулом и наличием -->
        <div class="product-header">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-meta">
                <span class="product-sku">Артикул: <?php echo htmlspecialchars($product['sku'] ?? 'SKU-' . $product['id']); ?></span>
                <?php if ($product['stock'] > 0): ?>
                    <span class="product-availability">
                        <i class="fas fa-check-circle"></i> В наличии
                    </span>
                <?php else: ?>
                    <span class="product-availability out-of-stock">
                        <i class="fas fa-times-circle"></i> Нет в наличии
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Основное содержимое: изображение и информация -->
        <div class="product-content">
            <!-- Изображение товара -->
            <div style="width: 100%; height: 400px; display: flex; justify-content: center; align-items: center; background: white; padding: 20px;">
                <!-- Основное изображение товара -->
                <img 
                    src="<?php echo $imageUrl; ?>" 
                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                    style="max-width: 100%; max-height: 100%; object-fit: contain; display: block;"
                    onerror="this.onerror=null; this.src='/img/products/no-image.jpg';"
                >
            </div>
            
            <!-- Правая колонка с информацией -->
            <div class="product-info-container">
                <!-- Блок с ценой и добавлением в корзину -->
                <div class="product-purchase-block">
                    <div class="product-price-block">
                        <span class="product-price"><?php echo $price; ?> ₽</span>
                        <?php if ($hasDiscount): ?>
                            <span class="product-old-price"><?php echo $oldPrice; ?> ₽</span>
                            <span class="product-discount">-<?php echo $product['discount']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-control">
                            <button type="button" class="quantity-btn minus">−</button>
                            <input type="number" min="1" value="1" class="quantity-input" id="product-quantity">
                            <button type="button" class="quantity-btn plus">+</button>
                        </div>
                        
                        <div class="cart-button-container">
                            <button type="button" class="add-to-cart-btn product-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                <span class="cart-icon"><i class="fas fa-shopping-cart"></i></span> Добавить в корзину
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="cart-button-container">
                            <button type="button" class="add-to-cart-btn disabled" disabled>
                                <span class="cart-icon"><i class="fas fa-times-circle"></i></span> Нет в наличии
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Блок с описанием -->
                <div class="product-description-block">
                    <h2 class="product-description-title">Описание</h2>
                    <div class="product-description-content">
                        <?php echo nl2br(htmlspecialchars($fullDescription)); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Вкладки с дополнительной информацией -->
        <div class="product-tabs">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="true">Характеристики</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab" aria-controls="delivery" aria-selected="false">Доставка и оплата</button>
                </li>
            </ul>
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                    <div class="specs-card">
                        <div class="specs-item">
                            <div class="specs-name">Бренд</div>
                            <div class="specs-value">x64</div>
                        </div>
                        <div class="specs-item">
                            <div class="specs-name">Модель</div>
                            <div class="specs-value"><?php echo htmlspecialchars($product['name']); ?></div>
                        </div>
                        <div class="specs-item">
                            <div class="specs-name">Артикул</div>
                            <div class="specs-value"><?php echo htmlspecialchars($product['sku'] ?? 'SKU-' . $product['id']); ?></div>
                        </div>
                        <div class="specs-item">
                            <div class="specs-name">Категория</div>
                            <div class="specs-value"><?php echo htmlspecialchars($product['category']); ?></div>
                        </div>
                        <div class="specs-item">
                            <div class="specs-name">Гарантия</div>
                            <div class="specs-value">12 месяцев</div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">
                    <div class="delivery-method">
                        <div class="delivery-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="delivery-content">
                            <h4 class="delivery-title">Курьерская доставка</h4>
                            <p class="delivery-description">Доставка в течение 1-3 дней. Стоимость: от 300 ₽.</p>
                        </div>
                    </div>
                    
                    <div class="delivery-method">
                        <div class="delivery-icon">
                            <i class="fas fa-store-alt"></i>
                        </div>
                        <div class="delivery-content">
                            <h4 class="delivery-title">Самовывоз из магазина</h4>
                            <p class="delivery-description">Бесплатно. Срок: 1-2 дня.</p>
                        </div>
                    </div>
                    
                    <h4 class="mt-4 mb-3">Способы оплаты:</h4>
                    
                    <div class="delivery-method">
                        <div class="delivery-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="delivery-content">
                            <h4 class="delivery-title">Банковской картой на сайте</h4>
                            <p class="delivery-description">Visa, MasterCard, МИР</p>
                        </div>
                    </div>
                    
                    <div class="delivery-method">
                        <div class="delivery-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="delivery-content">
                            <h4 class="delivery-title">Наличными при получении</h4>
                            <p class="delivery-description">Оплата курьеру или в пункте выдачи</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверка загрузки изображения товара
    const productImage = document.querySelector('.product-image');
    if (productImage) {
        // Если изображение уже загружено или есть в кеше
        if (productImage.complete) {
            console.log('Изображение уже загружено');
            if (productImage.naturalWidth === 0) {
                console.log('Изображение не загрузилось корректно');
                productImage.src = '/img/products/no-image.jpg';
            }
        } else {
            // Если изображение еще загружается
            productImage.addEventListener('load', function() {
                console.log('Изображение успешно загружено');
            });
            
            productImage.addEventListener('error', function() {
                console.log('Ошибка загрузки изображения');
                this.src = '/img/products/no-image.jpg';
            });
        }
    }
    
    // Обработчики для кнопок количества товара
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.getElementById('product-quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            quantityInput.value = currentValue + 1;
        });
        
        quantityInput.addEventListener('change', function() {
            let currentValue = parseInt(quantityInput.value);
            if (isNaN(currentValue) || currentValue < 1) {
                quantityInput.value = 1;
            }
        });
    }
    
    // Обработчик для добавления в корзину
    const addToCartButtons = document.querySelectorAll('.product-add-to-cart');
    
    addToCartButtons.forEach(button => {
        // Убедимся, что кнопка видна
        button.style.opacity = '1';
        button.style.visibility = 'visible';
        
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // Проверяем, что количество корректное
            if (isNaN(quantity) || quantity < 1) {
                alert('Пожалуйста, укажите корректное количество товара');
                return;
            }
            
            // Показываем индикатор загрузки
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавляем...';
            this.disabled = true;
            
            // AJAX запрос для добавления товара в корзину
            fetch('/ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети');
                }
                return response.json();
            })
            .then(data => {
                // Восстанавливаем кнопку
                this.disabled = false;
                
                if (data.success) {
                    // Обновляем счетчик товаров в корзине
                    const cartCounter = document.querySelector('.cart-count');
                    if (cartCounter) {
                        cartCounter.textContent = data.cart_count || 0;
                        
                        // Анимация счетчика корзины
                        cartCounter.classList.add('cart-count-animate');
                        setTimeout(() => {
                            cartCounter.classList.remove('cart-count-animate');
                        }, 500);
                    }
                    
                    // Показываем уведомление об успешном добавлении
                    this.innerHTML = '<i class="fas fa-check"></i> Добавлено';
                    this.style.backgroundColor = '#34c759';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.backgroundColor = '';
                    }, 2000);
                } else {
                    // Показываем сообщение об ошибке
                    this.innerHTML = originalText;
                    alert(data.message || 'Произошла ошибка при добавлении товара в корзину.');
                }
            })
            .catch(error => {
                // Восстанавливаем кнопку в случае ошибки
                this.disabled = false;
                this.innerHTML = originalText;
                
                console.error('Ошибка:', error);
                alert('Произошла ошибка при добавлении товара в корзину.');
            });
        });
    });
    
    // Добавляем стили для анимации счетчика корзины
    const style = document.createElement('style');
    style.textContent = `
        @keyframes cartCounterPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .cart-count-animate {
            animation: cartCounterPulse 0.5s ease-out;
        }
    `;
    document.head.appendChild(style);
    
    // Анимация при наведении на карточки характеристик
    const specsCards = document.querySelectorAll('.specs-card');
    specsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 