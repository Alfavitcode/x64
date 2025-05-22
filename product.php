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
?>

<!-- Заголовок страницы -->
<section class="page-header py-4">
    <div class="container">
        <h1 class="page-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item"><a href="/catalog.php">Каталог</a></li>
                <li class="breadcrumb-item"><a href="/catalog.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Страница товара -->
<section class="product-page section py-5">
    <div class="container">
        <div class="row">
            <!-- Галерея товара -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="product-gallery">
                    <div class="product-main-image position-relative">
                        <?php
                        // Метки товара
                        if ($product['is_new'] || $product['is_bestseller'] || $product['discount'] > 0) {
                            echo '<div class="product-badges">';
                            if ($product['is_new']) {
                                echo '<span class="badge badge-new">Новинка</span>';
                            }
                            if ($product['is_bestseller']) {
                                echo '<span class="badge badge-bestseller">Хит</span>';
                            }
                            if ($product['discount'] > 0) {
                                echo '<span class="badge badge-sale">-' . $product['discount'] . '%</span>';
                            }
                            echo '</div>';
                        }
                        
                        // Изображение товара
                        $imageUrl = !empty($product['image']) ? $product['image'] : '/img/products/no-image.jpg';
                        ?>
                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="main-product-image" class="img-fluid rounded">
                    </div>
                    
                    <div class="product-thumbnails d-flex mt-3">
                        <div class="thumbnail active me-2" data-image="<?php echo $imageUrl; ?>">
                            <img src="<?php echo $imageUrl; ?>" alt="Превью 1" class="img-fluid rounded">
                        </div>
                        <?php
                        // Здесь можно вывести дополнительные изображения товара, если они есть в базе
                        // Пока что выводим заглушки
                        for ($i = 1; $i <= 3; $i++) {
                            echo '<div class="thumbnail me-2" data-image="/img/products/placeholder-' . $i . '.jpg">
                                <img src="/img/products/placeholder-' . $i . '.jpg" alt="Превью ' . ($i + 1) . '" class="img-fluid rounded">
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Информация о товаре -->
            <div class="col-lg-6">
                <div class="product-info">
                    <h1 class="product-title d-lg-none"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta d-flex flex-wrap align-items-center mb-3">
                        <div class="product-rating me-3">
                            <?php
                            // Формируем рейтинг со звездами
                            $fullStars = floor($product['rating']);
                            $halfStar = ($product['rating'] - $fullStars) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i == $fullStars + 1 && $halfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span class="rating-value"><?php echo number_format($product['rating'], 1); ?></span>
                        </div>
                        
                        <a href="#reviews" class="reviews-link mb-2 mb-md-0"><?php echo $product['reviews_count']; ?> отзывов</a>
                        
                        <div class="product-sku ms-auto">
                            Артикул: <span><?php echo htmlspecialchars($product['sku'] ?? 'SKU-' . $product['id']); ?></span>
                        </div>
                    </div>
                    
                    <div class="product-price-block card p-3 mb-4">
                        <div class="product-price d-flex align-items-center">
                            <span class="current-price fs-3 fw-bold me-2"><?php echo number_format($product['price'], 0, '.', ' '); ?> ₽</span>
                            <?php
                            // Выводим старую цену, если есть скидка
                            if ($product['discount'] > 0) {
                                $oldPrice = round($product['price'] * (100 / (100 - $product['discount'])));
                                echo '<span class="old-price text-muted text-decoration-line-through me-2">' . number_format($oldPrice, 0, '.', ' ') . ' ₽</span>';
                                echo '<span class="discount-badge bg-danger text-white px-2 py-1 rounded-pill">-' . $product['discount'] . '%</span>';
                            }
                            ?>
                        </div>
                        
                        <div class="product-availability mt-2">
                            <?php if ($product['stock'] > 0): ?>
                                <span class="in-stock text-success"><i class="fas fa-check-circle"></i> В наличии</span>
                            <?php else: ?>
                                <span class="out-of-stock text-danger"><i class="fas fa-times-circle"></i> Нет в наличии</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-description mb-4">
                        <h5 class="mb-2">Краткое описание</h5>
                        <div class="card p-3">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="product-actions d-flex mb-4 flex-wrap">
                        <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-selector input-group me-3 mb-2 mb-md-0" style="max-width: 140px;">
                            <button class="btn btn-outline-secondary quantity-btn minus"><i class="fas fa-minus"></i></button>
                            <input type="number" min="1" value="1" class="form-control text-center quantity-input" id="product-quantity">
                            <button class="btn btn-outline-secondary quantity-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                        
                        <button class="btn btn-primary product-add-to-cart" data-product-id="<?php echo $product['id']; ?>" id="add-to-cart-button">
                            <i class="fas fa-shopping-cart me-2"></i> Добавить в корзину
                        </button>
                        <?php else: ?>
                        <div class="alert alert-danger w-100">
                            <i class="fas fa-exclamation-circle me-2"></i> Товар отсутствует в наличии
                        </div>
                        
                        <button class="btn btn-secondary btn-add-to-cart disabled" disabled>
                            <i class="fas fa-shopping-cart"></i> Нет в наличии
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-extra-actions d-flex flex-wrap mb-4">
                        <button class="btn btn-outline-secondary btn-wishlist me-2 mb-2" data-product-id="<?php echo $product['id']; ?>">
                            <i class="far fa-heart"></i> В избранное
                        </button>
                        <button class="btn btn-outline-secondary btn-compare mb-2" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-exchange-alt"></i> Сравнить
                        </button>
                    </div>
                    
                    <div class="product-share">
                        <span class="share-label d-block mb-2">Поделиться:</span>
                        <div class="share-buttons">
                            <a href="#" class="share-btn btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="share-btn btn btn-outline-info btn-sm rounded-circle me-1"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="share-btn btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-vk"></i></a>
                            <a href="#" class="share-btn btn btn-outline-info btn-sm rounded-circle me-1"><i class="fab fa-telegram-plane"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Вкладки с дополнительной информацией -->
        <div class="product-tabs mt-5">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Описание</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Характеристики</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Отзывы (<?php echo $product['reviews_count']; ?>)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab" aria-controls="delivery" aria-selected="false">Доставка и оплата</button>
                </li>
            </ul>
            
            <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <div class="product-full-description">
                        <h3>Подробное описание</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                    <div class="product-specifications">
                        <h3>Технические характеристики</h3>
                        <table class="table table-striped">
                            <tbody>
                                <?php
                                // Здесь в реальном проекте будут выводиться характеристики из БД
                                // Пока что выводим заглушки
                                $specs = [
                                    'Бренд' => 'x64',
                                    'Модель' => $product['name'],
                                    'Гарантия' => '12 месяцев',
                                    'Страна производства' => 'Россия',
                                    'Год выпуска' => '2023'
                                ];
                                
                                foreach ($specs as $name => $value) {
                                    echo '<tr>
                                        <td class="spec-name">' . htmlspecialchars($name) . '</td>
                                        <td class="spec-value">' . htmlspecialchars($value) . '</td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="product-reviews">
                        <h3>Отзывы покупателей</h3>
                        
                        <?php if ($product['reviews_count'] > 0): ?>
                            <div class="reviews-summary">
                                <div class="reviews-average">
                                    <div class="average-rating"><?php echo number_format($product['rating'], 1); ?></div>
                                    <div class="average-stars">
                                        <?php
                                        // Выводим звезды рейтинга
                                        $fullStars = floor($product['rating']);
                                        $halfStar = ($product['rating'] - $fullStars) >= 0.5;
                                        
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $fullStars) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i == $fullStars + 1 && $halfStar) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="reviews-count"><?php echo $product['reviews_count']; ?> отзывов</div>
                                </div>
                                
                                <div class="review-bars">
                                    <?php
                                    // Здесь в реальном проекте будет статистика по звездам
                                    // Пока что выводим заглушки
                                    $reviewStats = [
                                        5 => rand(50, 80),
                                        4 => rand(10, 30),
                                        3 => rand(5, 15),
                                        2 => rand(0, 5),
                                        1 => rand(0, 3)
                                    ];
                                    
                                    for ($i = 5; $i >= 1; $i--) {
                                        $percent = $reviewStats[$i];
                                        echo '<div class="review-bar">
                                            <div class="stars">' . $i . ' <i class="fas fa-star"></i></div>
                                            <div class="bar">
                                                <div class="bar-fill" style="width: ' . $percent . '%;"></div>
                                            </div>
                                            <div class="percent">' . $percent . '%</div>
                                        </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Список отзывов -->
                            <div class="reviews-list">
                                <?php
                                // Здесь в реальном проекте будут выводиться отзывы из БД
                                // Пока что выводим заглушки
                                $reviewsCount = min(3, $product['reviews_count']);
                                for ($i = 0; $i < $reviewsCount; $i++):
                                    $rating = rand(3, 5);
                                ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <img src="/img/avatar-placeholder.jpg" alt="Аватар">
                                            </div>
                                            <div class="reviewer-name">Пользователь <?php echo $i + 1; ?></div>
                                        </div>
                                        <div class="review-date">
                                            <?php echo date('d.m.Y', strtotime('-' . rand(1, 30) . ' days')); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="review-rating">
                                        <?php
                                        for ($j = 1; $j <= 5; $j++) {
                                            if ($j <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="review-content">
                                        <p>Отличный товар! Полностью соответствует описанию. Быстрая доставка и хорошее качество. Рекомендую к покупке.</p>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($product['reviews_count'] > 3): ?>
                                <div class="reviews-more">
                                    <button class="btn btn-outline load-more-reviews">Показать еще отзывы</button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-reviews">
                                <p>У данного товара пока нет отзывов.</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Форма добавления отзыва -->
                        <div class="add-review">
                            <h4>Оставить отзыв</h4>
                            <form class="review-form">
                                <div class="form-group">
                                    <label>Ваша оценка:</label>
                                    <div class="rating-select">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" id="rating-<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                            <label for="rating-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="review-name">Ваше имя:</label>
                                    <input type="text" id="review-name" name="name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="review-email">E-mail:</label>
                                    <input type="email" id="review-email" name="email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="review-text">Отзыв:</label>
                                    <textarea id="review-text" name="text" rows="5" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Отправить отзыв</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">
                    <div class="delivery-info">
                        <h3>Доставка и оплата</h3>
                        
                        <div class="delivery-methods">
                            <h4>Способы доставки:</h4>
                            <ul>
                                <li>
                                    <div class="delivery-icon"><i class="fas fa-truck"></i></div>
                                    <div class="delivery-details">
                                        <h5>Курьерская доставка</h5>
                                        <p>Доставка в течение 1-3 дней. Стоимость: от 300 ₽.</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="delivery-icon"><i class="fas fa-store-alt"></i></div>
                                    <div class="delivery-details">
                                        <h5>Самовывоз из магазина</h5>
                                        <p>Бесплатно. Срок: 1-2 дня.</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="delivery-icon"><i class="fas fa-box"></i></div>
                                    <div class="delivery-details">
                                        <h5>Пункты выдачи</h5>
                                        <p>Доставка в течение 2-5 дней. Стоимость: от 200 ₽.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="payment-methods">
                            <h4>Способы оплаты:</h4>
                            <ul>
                                <li>
                                    <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                                    <div class="payment-details">
                                        <h5>Банковской картой на сайте</h5>
                                        <p>Visa, MasterCard, МИР</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="payment-details">
                                        <h5>Наличными при получении</h5>
                                        <p>Оплата курьеру или в пункте выдачи</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="payment-icon"><i class="fas fa-wallet"></i></div>
                                    <div class="payment-details">
                                        <h5>Электронные кошельки</h5>
                                        <p>ЮMoney, WebMoney, QIWI</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Похожие товары -->
<section class="related-products section">
    <div class="container">
        <h2 class="section-title">Похожие товары</h2>
        
        <div class="products-slider">
            <?php
            // Здесь в реальном проекте будем выводить товары из той же категории
            // Пока что выводим заглушки
            for ($i = 0; $i < 4; $i++):
                $isNew = rand(0, 1);
                $isBestseller = rand(0, 1);
                $hasDiscount = rand(0, 1);
                $discount = $hasDiscount ? rand(10, 30) : 0;
                
                $price = rand(1000, 50000);
                $oldPrice = $hasDiscount ? round($price * (100 / (100 - $discount))) : 0;
                
                $rating = rand(30, 50) / 10;
                $reviews = rand(1, 50);
            ?>
            <div class="product-card">
                <div class="product-badges">
                    <?php if ($isNew): ?>
                        <span class="badge badge-new">Новинка</span>
                    <?php endif; ?>
                    <?php if ($isBestseller): ?>
                        <span class="badge badge-bestseller">Хит</span>
                    <?php endif; ?>
                    <?php if ($hasDiscount): ?>
                        <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-image">
                    <img src="/img/products/placeholder-<?php echo $i + 1; ?>.jpg" alt="Товар <?php echo $i + 1; ?>">
                    <div class="product-actions">
                        <button class="action-btn wishlist-btn"><i class="far fa-heart"></i></button>
                        <button class="action-btn compare-btn"><i class="fas fa-exchange-alt"></i></button>
                        <button class="action-btn quickview-btn"><i class="far fa-eye"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category"><?php echo $product['category']; ?></div>
                    <h3 class="product-title"><a href="/product.php?id=<?php echo $i + 1; ?>">Похожий товар <?php echo $i + 1; ?></a></h3>
                    <div class="product-rating">
                        <?php
                        // Выводим звезды рейтинга
                        $fullStars = floor($rating);
                        $halfStar = ($rating - $fullStars) >= 0.5;
                        
                        for ($j = 1; $j <= 5; $j++) {
                            if ($j <= $fullStars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($j == $fullStars + 1 && $halfStar) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                        <span>(<?php echo $reviews; ?>)</span>
                    </div>
                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($price, 0, '.', ' '); ?> ₽</span>
                        <?php if ($hasDiscount): ?>
                            <span class="old-price"><?php echo number_format($oldPrice, 0, '.', ' '); ?> ₽</span>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="btn btn-add-to-cart"><i class="fas fa-shopping-cart"></i> В корзину</button>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Скрипт для работы с количеством товара -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация скриптов на странице товара');
    
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.getElementById('product-quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        // Обработчик для кнопки уменьшения количества
        minusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
        
        // Обработчик для кнопки увеличения количества
        plusBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            quantityInput.value = currentValue + 1;
        });
        
        // Проверка введенного значения
        quantityInput.addEventListener('change', function() {
            let currentValue = parseInt(quantityInput.value);
            if (isNaN(currentValue) || currentValue < 1) {
                quantityInput.value = 1;
            }
        });
    }
});
</script>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 