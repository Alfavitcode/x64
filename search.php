<?php
// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем поисковый запрос из GET-параметра
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Получаем результаты поиска, если запрос не пустой
$searchResults = !empty($query) ? searchProducts($query) : [];

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Заголовок страницы -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Результаты поиска</h1>
        <div class="breadcrumbs">
            <a href="/">Главная</a> <span class="separator">/</span> <span class="current">Поиск</span>
        </div>
    </div>
</section>

<!-- Результаты поиска -->
<section class="search-results section">
    <div class="container">
        <?php if (!empty($query)): ?>
            <div class="search-query">
                <p>Результаты поиска по запросу: <strong>"<?php echo htmlspecialchars($query); ?>"</strong></p>
                <p>Найдено товаров: <strong><?php echo count($searchResults); ?></strong></p>
            </div>
            
            <?php if (!empty($searchResults)): ?>
                <div class="products-grid search-grid">
                    <?php foreach ($searchResults as $product): 
                        // Определяем метки для товара
                        $badges = '';
                        if ($product['is_new']) {
                            $badges .= '<span class="badge badge-new">Новинка</span>';
                        }
                        if ($product['is_bestseller']) {
                            $badges .= '<span class="badge badge-bestseller">Хит</span>';
                        }
                        if ($product['discount'] > 0) {
                            $badges .= '<span class="badge badge-sale">-' . $product['discount'] . '%</span>';
                        }

                        // Вычисляем старую цену, если есть скидка
                        $oldPriceHtml = '';
                        if ($product['discount'] > 0 && $product['price'] > 0) {
                            $oldPrice = round($product['price'] * (100 / (100 - $product['discount'])));
                            $oldPriceHtml = '<span class="old-price">' . number_format($oldPrice, 0, '.', ' ') . ' ₽</span>';
                        }

                        // Формируем рейтинг со звездами
                        $ratingHtml = '';
                        $fullStars = floor($product['rating']);
                        $halfStar = ($product['rating'] - $fullStars) >= 0.5;
                        
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $fullStars) {
                                $ratingHtml .= '<i class="fas fa-star"></i>';
                            } elseif ($i == $fullStars + 1 && $halfStar) {
                                $ratingHtml .= '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                $ratingHtml .= '<i class="far fa-star"></i>';
                            }
                        }

                        // Формируем изображение товара
                        $imageUrl = !empty($product['image']) ? $product['image'] : '/img/products/no-image.jpg';
                    ?>
                    <div class="product-card">
                        <div class="product-badges">
                            <?php echo $badges; ?>
                        </div>
                        <div class="product-image">
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['stock'] <= 0): ?>
                                <div class="out-of-stock-overlay">
                                    <span class="out-of-stock-label">Нет в наличии</span>
                                </div>
                            <?php endif; ?>
                            <div class="product-actions">
                                <button class="action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>"><i class="far fa-heart"></i></button>
                                <button class="action-btn compare-btn" data-product-id="<?php echo $product['id']; ?>"><i class="fas fa-exchange-alt"></i></button>
                                <button class="action-btn quickview-btn" data-product-id="<?php echo $product['id']; ?>"><i class="far fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                            <h3 class="product-title"><a href="/product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <div class="product-rating">
                                <?php echo $ratingHtml; ?>
                                <span>(<?php echo $product['reviews_count']; ?>)</span>
                            </div>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($product['price'], 0, '.', ' '); ?> ₽</span>
                                <?php echo $oldPriceHtml; ?>
                            </div>
                        </div>
                        <button class="btn btn-add-to-cart<?php echo ($product['stock'] <= 0) ? ' disabled' : ''; ?>" data-product-id="<?php echo $product['id']; ?>"><i class="fas fa-shopping-cart"></i> В корзину</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-search">
                    <div class="empty-search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Товары не найдены</h3>
                    <p>К сожалению, по вашему запросу ничего не найдено. Попробуйте изменить запрос или посмотреть наш каталог.</p>
                    <div class="empty-search-actions">
                        <a href="/catalog.php" class="btn btn-primary">Перейти в каталог</a>
                        <a href="/" class="btn btn-outline">На главную</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="search-form-block">
                <h2>Поиск товаров</h2>
                <p>Введите название товара или ключевые слова для поиска</p>
                <form action="/search.php" method="GET" class="search-page-form">
                    <div class="search-input-group">
                        <input type="text" name="query" placeholder="Поиск товаров..." class="search-page-input">
                        <button type="submit" class="btn btn-primary search-page-button">
                            <i class="fas fa-search"></i> Искать
                        </button>
                    </div>
                </form>
                <div class="search-suggestions">
                    <h3>Популярные запросы:</h3>
                    <div class="tag-cloud">
                        <a href="/search.php?query=смартфон" class="tag">Смартфоны</a>
                        <a href="/search.php?query=планшет" class="tag">Планшеты</a>
                        <a href="/search.php?query=ноутбук" class="tag">Ноутбуки</a>
                        <a href="/search.php?query=наушники" class="tag">Наушники</a>
                        <a href="/search.php?query=аксессуар" class="tag">Аксессуары</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 