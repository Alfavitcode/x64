<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Получаем текущую категорию из GET-параметра
$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// Получаем поисковый запрос, если он передан
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Получаем текущую страницу из GET-параметра
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// Количество товаров на странице
$itemsPerPage = 6;

// Получаем параметры фильтрации
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$typeFilters = isset($_GET['type']) && is_array($_GET['type']) ? $_GET['type'] : [];

// Получаем параметр сортировки
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'popular';

// Получаем все категории товаров
$categories = getCatalogCategories();

// Получаем товары в зависимости от выбранной категории и поискового запроса
if (!empty($searchQuery)) {
    // Если есть поисковый запрос, используем его вместе с категорией
    $allProducts = searchProducts($searchQuery);
    if ($currentCategory !== 'all') {
        // Фильтруем результаты по категории
        $allProducts = array_filter($allProducts, function($product) use ($currentCategory) {
            return $product['category'] === $currentCategory;
        });
    }
} else {
    // Стандартный запрос товаров по категории
    $allProducts = getProducts(null, $currentCategory !== 'all' ? $currentCategory : null);
}

// Применяем фильтры
if (!empty($allProducts)) {
    // Фильтр по цене
    if ($minPrice !== null || $maxPrice !== null) {
        $allProducts = array_filter($allProducts, function($product) use ($minPrice, $maxPrice) {
            $price = (int)$product['price'];
            if ($minPrice !== null && $maxPrice !== null) {
                return $price >= $minPrice && $price <= $maxPrice;
            } elseif ($minPrice !== null) {
                return $price >= $minPrice;
            } elseif ($maxPrice !== null) {
                return $price <= $maxPrice;
            }
            return true;
        });
    }
    
    // Фильтр по типу товара
    if (!empty($typeFilters)) {
        $allProducts = array_filter($allProducts, function($product) use ($typeFilters) {
            foreach ($typeFilters as $type) {
                if ($type === 'new' && $product['is_new'] == 1) {
                    return true;
                }
                if ($type === 'sale' && $product['discount'] > 0) {
                    return true;
                }
                if ($type === 'bestseller' && $product['is_bestseller'] == 1) {
                    return true;
                }
            }
            return empty($typeFilters); // Если фильтры не выбраны, показываем все товары
        });
    }
    
    // Применяем сортировку
    if (!empty($allProducts)) {
        switch($sortBy) {
            case 'price_asc':
                usort($allProducts, function($a, $b) {
                    return $a['price'] - $b['price'];
                });
                break;
            case 'price_desc':
                usort($allProducts, function($a, $b) {
                    return $b['price'] - $a['price'];
                });
                break;
            case 'new':
                usort($allProducts, function($a, $b) {
                    return $b['is_new'] - $a['is_new'];
                });
                break;
            case 'popular':
            default:
                usort($allProducts, function($a, $b) {
                    return $b['is_bestseller'] - $a['is_bestseller'];
                });
                break;
        }
    }
}

// Общее количество товаров
$totalItems = count($allProducts);

// Общее количество страниц
$totalPages = ceil($totalItems / $itemsPerPage);

// Если текущая страница больше общего количества страниц, устанавливаем последнюю страницу
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

// Вычисляем начальный индекс для среза массива товаров
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Получаем товары для текущей страницы
$products = array_slice($allProducts, $startIndex, $itemsPerPage);

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Заголовок страницы -->
<section class="page-header py-4">
    <div class="container">
        <h1 class="page-title">
            <?php if (!empty($searchQuery)): ?>
                Результаты поиска: "<?php echo htmlspecialchars($searchQuery); ?>"
            <?php else: ?>
                Каталог товаров
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item<?php echo $currentCategory === 'all' && empty($searchQuery) ? ' active' : ''; ?>"<?php echo $currentCategory === 'all' && empty($searchQuery) ? ' aria-current="page"' : ''; ?>>
                    <?php if ($currentCategory === 'all' && empty($searchQuery)): ?>
                        Каталог
                    <?php else: ?>
                        <a href="/catalog.php">Каталог</a>
                    <?php endif; ?>
                </li>
                <?php if (!empty($searchQuery)): ?>
                    <li class="breadcrumb-item active" aria-current="page">Поиск: <?php echo htmlspecialchars($searchQuery); ?></li>
                <?php elseif ($currentCategory !== 'all'): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($currentCategory); ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- Каталог товаров -->
<section class="catalog-section section py-5">
    <div class="container">
        <div class="row">
            <!-- Фильтры и категории -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <aside class="catalog-sidebar">
                    <div class="sidebar-widget categories-widget card mb-4">
                        <div class="card-header">
                            <h3 class="widget-title m-0 fs-5">Категории</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="categories-list list-group list-group-flush">
                                <li class="list-group-item<?php echo $currentCategory === 'all' ? ' active' : ''; ?>">
                                    <a class="text-decoration-none<?php echo $currentCategory === 'all' ? ' text-white' : ''; ?>" href="/catalog.php">Все категории</a>
                                </li>
                                <?php foreach ($categories as $category): ?>
                                <li class="list-group-item<?php echo $currentCategory === $category ? ' active' : ''; ?>">
                                    <a class="text-decoration-none<?php echo $currentCategory === $category ? ' text-white' : ''; ?>" href="/catalog.php?category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="sidebar-widget filters-widget card">
                        <div class="card-header">
                            <h3 class="widget-title m-0 fs-5">Фильтры</h3>
                        </div>
                        <div class="card-body">
                            <form class="filter-form" method="GET" action="/catalog.php">
                                <!-- Скрытые поля для сохранения текущих параметров -->
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($currentCategory); ?>">
                                <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="sort" id="hidden-sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                                
                                <div class="filter-group mb-3">
                                    <h4 class="filter-title fs-6 mb-2">Цена</h4>
                                    <div class="price-range row g-2">
                                        <div class="col">
                                            <input type="number" min="0" placeholder="от" name="min_price" value="<?php echo $minPrice !== null ? $minPrice : ''; ?>" class="form-control price-input">
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <span class="range-sep">-</span>
                                        </div>
                                        <div class="col">
                                            <input type="number" min="0" placeholder="до" name="max_price" value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>" class="form-control price-input">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="filter-group mb-3">
                                    <h4 class="filter-title fs-6 mb-2">Тип товара</h4>
                                    <div class="checkbox-group">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="newItems" name="type[]" value="new" <?php echo in_array('new', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="newItems">
                                                Новинки
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="saleItems" name="type[]" value="sale" <?php echo in_array('sale', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="saleItems">
                                                Со скидкой
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bestsellerItems" name="type[]" value="bestseller" <?php echo in_array('bestseller', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="bestsellerItems">
                                                Хиты продаж
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Применить</button>
                                    <a href="/catalog.php<?php echo $currentCategory !== 'all' ? '?category=' . urlencode($currentCategory) : ''; ?>" class="btn btn-outline-secondary">Сбросить</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </aside>
            </div>
            
            <!-- Список товаров -->
            <div class="col-lg-9">
                <div class="catalog-content">
                    <?php if (!empty($searchQuery)): ?>
                    <!-- Информация о поисковом запросе -->
                    <div class="search-info-card mb-4 p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <p class="mb-0"><i class="fas fa-search me-2"></i> Результаты поиска по запросу: <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>
                                <p class="text-muted mb-0 small">Найдено товаров: <strong><?php echo $totalItems; ?></strong></p>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <a href="/catalog.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Сбросить поиск
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="catalog-header d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <div class="catalog-count mb-2 mb-sm-0"><span class="badge bg-primary rounded-pill fs-6"><?php echo $totalItems; ?> товаров</span></div>
                        <div class="catalog-sort d-flex align-items-center">
                            <label for="sort-select" class="me-2">Сортировать по:</label>
                            <select id="sort-select" class="form-select sort-select">
                                <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Популярности</option>
                                <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Цене (по возрастанию)</option>
                                <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Цене (по убыванию)</option>
                                <option value="new" <?php echo $sortBy === 'new' ? 'selected' : ''; ?>>Новизне</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="products-grid row">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): 
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
                                            <button class="btn btn-primary btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-shopping-cart me-2"></i>Добавить в корзину
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="empty-catalog text-center py-5">
                                    <div class="empty-catalog-icon mb-3">
                                        <i class="fas fa-search fa-4x text-muted"></i>
                                    </div>
                                    <h3 class="mb-3">Товары не найдены</h3>
                                    <p class="mb-4">К сожалению, в данной категории товаров пока нет.</p>
                                    <a href="/catalog.php" class="btn btn-catalog"><i class="fas fa-store"></i> Посмотреть все товары</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <!-- Пагинация -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Создаем базовый URL для пагинации с сохранением всех параметров -->
                            <?php
                            $paginationUrl = '?';
                            $urlParams = [];
                            
                            if ($currentCategory !== 'all') {
                                $urlParams[] = 'category=' . urlencode($currentCategory);
                            }
                            
                            if (!empty($searchQuery)) {
                                $urlParams[] = 'search=' . urlencode($searchQuery);
                            }
                            
                            if ($minPrice !== null) {
                                $urlParams[] = 'min_price=' . $minPrice;
                            }
                            
                            if ($maxPrice !== null) {
                                $urlParams[] = 'max_price=' . $maxPrice;
                            }
                            
                            if (!empty($typeFilters)) {
                                foreach ($typeFilters as $type) {
                                    $urlParams[] = 'type[]=' . urlencode($type);
                                }
                            }
                            
                            if ($sortBy && $sortBy !== 'popular') {
                                $urlParams[] = 'sort=' . urlencode($sortBy);
                            }
                            
                            $paginationUrl .= implode('&', $urlParams);
                            if (!empty($urlParams)) {
                                $paginationUrl .= '&';
                            }
                            ?>
                            
                            <!-- Кнопка "Предыдущая страница" -->
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($currentPage > 1) ? $paginationUrl . 'page=' . ($currentPage - 1) : '#'; ?>" aria-label="Previous">
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
                                    <a class="page-link" href="<?php echo $paginationUrl . 'page=' . $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Многоточие и последняя страница -->
                            <?php if ($endPage < $totalPages): ?>
                                <li class="page-item">
                                    <span class="page-link">...</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $paginationUrl . 'page=' . $totalPages; ?>"><?php echo $totalPages; ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Кнопка "Следующая страница" -->
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo ($currentPage < $totalPages) ? $paginationUrl . 'page=' . ($currentPage + 1) : '#'; ?>" aria-label="Next">
                                    <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?>

<script>
// Функция для получения параметров из URL
function getUrlParams() {
    const params = {};
    window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
        params[key] = decodeURIComponent(value);
    });
    return params;
}

// Функция для установки параметра в URL
function updateUrlParam(key, value) {
    const params = getUrlParams();
    params[key] = value;
    
    let newUrl = window.location.pathname;
    let queryParts = [];
    
    for (const key in params) {
        if (key === 'type[]') {
            if (Array.isArray(params[key])) {
                params[key].forEach(val => queryParts.push(`type[]=${encodeURIComponent(val)}`));
            } else {
                queryParts.push(`type[]=${encodeURIComponent(params[key])}`);
            }
        } else {
            queryParts.push(`${key}=${encodeURIComponent(params[key])}`);
        }
    }
    
    if (queryParts.length > 0) {
        newUrl += '?' + queryParts.join('&');
    }
    
    return newUrl;
}

// Обработка изменения сортировки
document.getElementById('sort-select').addEventListener('change', function() {
    const sortValue = this.value;
    document.getElementById('hidden-sort').value = sortValue;
    
    // Перенаправляем на новый URL с обновленным параметром сортировки
    window.location.href = updateUrlParam('sort', sortValue);
});
</script> 