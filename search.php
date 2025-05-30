<?php
// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем поисковый запрос из GET-параметра
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Определяем параметры для пагинации
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// Количество товаров на странице
$itemsPerPage = 12;

// Получаем результаты поиска, если запрос не пустой
$allSearchResults = !empty($query) ? searchProducts($query) : [];

// Общее количество товаров
$totalItems = count($allSearchResults);

// Общее количество страниц
$totalPages = ceil($totalItems / $itemsPerPage);

// Если текущая страница больше общего количества страниц, устанавливаем последнюю страницу
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

// Вычисляем начальный индекс для среза массива товаров
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Получаем товары для текущей страницы
$searchResults = array_slice($allSearchResults, $startIndex, $itemsPerPage);

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Заголовок страницы -->
<section class="page-header py-4">
    <div class="container">
        <h1 class="page-title">Результаты поиска</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item active" aria-current="page">Поиск</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Результаты поиска -->
<section class="search-results section py-5">
    <div class="container">
        <?php if (!empty($query)): ?>
            <div class="search-summary mb-4">
                <div class="card rounded-4 p-4 border-0 shadow-sm">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="me-auto">
                            <h2 class="h5 mb-0">Результаты поиска по запросу: <span class="text-primary">"<?php echo htmlspecialchars($query); ?>"</span></h2>
                            <p class="text-muted mb-0 mt-2">Найдено товаров: <strong><?php echo count($allSearchResults); ?></strong></p>
                        </div>
                        <div class="ms-auto mt-3 mt-md-0">
                            <a href="catalog.php?search=<?php echo urlencode($query); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-filter me-2"></i>Перейти в каталог с фильтрами
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($searchResults)): ?>
                <div class="products-grid row">
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

                        // Формируем изображение товара
                        $imageUrl = !empty($product['image']) ? $product['image'] : '/img/products/no-image.jpg';
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-5">
                        <div class="product-card h-100">
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
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                <h3 class="product-title"><a href="/product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <div class="product-price">
                                    <span class="current-price"><?php echo number_format($product['price'], 0, '.', ' '); ?> ₽</span>
                                    <?php echo $oldPriceHtml; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary btn-add-to-cart<?php echo ($product['stock'] <= 0) ? ' disabled' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <!-- Пагинация -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Кнопка "Предыдущая страница" -->
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?query=' . urlencode($query) . '&page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
                                <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                            </a>
                        </li>
                        
                        <?php
                        // Определяем диапазон страниц для отображения
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        
                        // Если диапазон не охватывает 5 страниц, корректируем его
                        if ($endPage - $startPage < 4) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        // Выводим номера страниц
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?query=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Многоточие и последняя страница -->
                        <?php if ($endPage < $totalPages): ?>
                            <li class="page-item">
                                <span class="page-link">...</span>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?query=<?php echo urlencode($query); ?>&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Кнопка "Следующая страница" -->
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($currentPage < $totalPages) ? '?query=' . urlencode($query) . '&page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                                <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-search">
                    <div class="text-center py-5">
                        <div class="empty-search-icon mb-4">
                            <i class="fas fa-search fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">Товары не найдены</h3>
                        <p class="mb-4">К сожалению, по вашему запросу ничего не найдено. Попробуйте изменить запрос или посмотреть наш каталог.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="/catalog.php" class="btn btn-primary">Перейти в каталог</a>
                            <a href="/" class="btn btn-outline-secondary">На главную</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="search-form-block">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                            <h2 class="text-center mb-4">Поиск товаров</h2>
                            <p class="text-center mb-4">Введите название товара или ключевые слова для поиска</p>
                            <form action="/search.php" method="GET" class="search-page-form">
                                <div class="input-group mb-4">
                                    <input type="text" name="query" placeholder="Поиск товаров..." class="form-control form-control-lg rounded-start">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Искать
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="search-suggestions mt-4">
                            <h3 class="h5 text-center mb-3">Популярные запросы:</h3>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="/search.php?query=смартфон" class="btn btn-sm btn-outline-primary rounded-pill">Смартфоны</a>
                                <a href="/search.php?query=планшет" class="btn btn-sm btn-outline-primary rounded-pill">Планшеты</a>
                                <a href="/search.php?query=ноутбук" class="btn btn-sm btn-outline-primary rounded-pill">Ноутбуки</a>
                                <a href="/search.php?query=наушники" class="btn btn-sm btn-outline-primary rounded-pill">Наушники</a>
                                <a href="/search.php?query=аксессуар" class="btn btn-sm btn-outline-primary rounded-pill">Аксессуары</a>
                            </div>
                        </div>
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