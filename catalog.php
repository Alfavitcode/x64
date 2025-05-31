<?php
// Подключаем файл управления сессиями
require_once 'includes/config/session.php';

// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Проверяем, является ли запрос AJAX-запросом
$isAjaxRequest = isset($_GET['ajax']) && $_GET['ajax'] === 'true';

// Получаем текущую категорию из GET-параметра
$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// Получаем текущую подкатегорию из GET-параметра
$currentSubcategory = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;

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

// Отфильтруем категории, чтобы оставить только основные (Android и iPhone)
$mainCategories = array_filter($categories, function($category) {
    return $category === 'Android' || $category === 'iPhone';
});

// Принудительно создаем подкатегории для iPhone и Android перед загрузкой страницы
if (in_array('iPhone', $mainCategories)) {
    $iPhoneSubcategories = getSubcategories('iPhone');
    if (empty($iPhoneSubcategories)) {
        // Создаем подкатегории для iPhone
        $iPhoneId = getCategoryIdByName('iPhone');
        if ($iPhoneId) {
            addCategory('Чехлы для iPhone', 'Защитные чехлы для iPhone', '', $iPhoneId);
            addCategory('Зарядные устройства для iPhone', 'Зарядные устройства для iPhone', '', $iPhoneId);
            addCategory('Защитные стекла для iPhone', 'Защитные стекла для iPhone', '', $iPhoneId);
            addCategory('Наушники Apple', 'Наушники для iPhone', '', $iPhoneId);
            addCategory('Аксессуары для iPhone', 'Другие аксессуары для iPhone', '', $iPhoneId);
        }
    }
    
    // Принудительно получаем подкатегории для iPhone снова
    $iPhoneId = getCategoryIdByName('iPhone');
    if ($iPhoneId) {
        $manualIPhoneSubcategories = [
            ['id' => 'iphone_case', 'name' => 'Чехлы для iPhone', 'count' => 0],
            ['id' => 'iphone_charger', 'name' => 'Зарядные устройства для iPhone', 'count' => 0],
            ['id' => 'iphone_glass', 'name' => 'Защитные стекла для iPhone', 'count' => 0],
            ['id' => 'iphone_headphones', 'name' => 'Наушники Apple', 'count' => 0],
            ['id' => 'iphone_accessories', 'name' => 'Аксессуары для iPhone', 'count' => 0]
        ];
        
        // Сохраняем для использования в шаблоне
        $forcedSubcategories['iPhone'] = $manualIPhoneSubcategories;
    }
}

if (in_array('Android', $mainCategories)) {
    $androidSubcategories = getSubcategories('Android');
    if (empty($androidSubcategories)) {
        // Создаем подкатегории для Android
        $androidId = getCategoryIdByName('Android');
        if ($androidId) {
            addCategory('Чехлы для Android', 'Защитные чехлы для Android', '', $androidId);
            addCategory('Зарядные устройства для Android', 'Зарядные устройства для Android', '', $androidId);
            addCategory('Защитные стекла для Android', 'Защитные стекла для Android', '', $androidId);
            addCategory('Наушники Android', 'Наушники для Android', '', $androidId);
            addCategory('Аксессуары для Android', 'Другие аксессуары для Android', '', $androidId);
        }
    }
    
    // Принудительно получаем подкатегории для Android снова
    $androidId = getCategoryIdByName('Android');
    if ($androidId) {
        $manualAndroidSubcategories = [
            ['id' => 'android_case', 'name' => 'Чехлы для Android', 'count' => 0],
            ['id' => 'android_charger', 'name' => 'Зарядные устройства для Android', 'count' => 0],
            ['id' => 'android_glass', 'name' => 'Защитные стекла для Android', 'count' => 0],
            ['id' => 'android_headphones', 'name' => 'Наушники Android', 'count' => 0],
            ['id' => 'android_accessories', 'name' => 'Аксессуары для Android', 'count' => 0]
        ];
        
        // Сохраняем для использования в шаблоне
        $forcedSubcategories['Android'] = $manualAndroidSubcategories;
    }
}

// Получаем товары в зависимости от выбранной категории и поискового запроса
if (!empty($searchQuery)) {
    // Если есть поисковый запрос, используем его вместе с категорией
    $allProducts = searchProducts($searchQuery);
    if ($currentCategory !== 'all') {
        // Фильтруем результаты по категории
        $allProducts = array_filter($allProducts, function($product) use ($currentCategory) {
            return $product['category'] === $currentCategory;
        });
        
        // Если указана подкатегория, дополнительно фильтруем по ней
        if ($currentSubcategory > 0) {
            $allProducts = array_filter($allProducts, function($product) use ($currentSubcategory) {
                return isset($product['subcategory_id']) && $product['subcategory_id'] == $currentSubcategory;
            });
        }
    }
} else {
    // Стандартный запрос товаров по категории и подкатегории
    $allProducts = getProducts(null, $currentCategory !== 'all' ? $currentCategory : null, $currentSubcategory > 0 ? $currentSubcategory : null);
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

// Если это AJAX-запрос, выводим только необходимый контент
if ($isAjaxRequest) {
    // Начинаем буферизацию вывода
    ob_start();
    
    // Выводим обновленный каталог товаров
    ?>
    <!-- AJAX-ответ: начало -->
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
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-badges">
                            <?php echo $badges; ?>
                        </div>
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
    <!-- AJAX-ответ: конец -->
    <?php
    
    // Получаем буферизированный контент и очищаем буфер
    $ajaxContent = ob_get_clean();
    
    // Выводим AJAX-ответ и завершаем выполнение скрипта
    echo $ajaxContent;
    exit;
}

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Подключение общих стилей для карточек товаров -->
<link rel="stylesheet" href="/css/components/product-card.css">

<!-- Подключение стилей для категорий и подкатегорий -->
<link rel="stylesheet" href="/css/components/catalog-categories.css">

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
                <li class="breadcrumb-item<?php echo $currentCategory === 'all' && empty($searchQuery) && $currentSubcategory === 0 ? ' active' : ''; ?>"<?php echo $currentCategory === 'all' && empty($searchQuery) && $currentSubcategory === 0 ? ' aria-current="page"' : ''; ?>>
                    <?php if ($currentCategory === 'all' && empty($searchQuery) && $currentSubcategory === 0): ?>
                        Каталог
                    <?php else: ?>
                        <a href="/catalog.php">Каталог</a>
                    <?php endif; ?>
                </li>
                <?php if (!empty($searchQuery)): ?>
                    <li class="breadcrumb-item active" aria-current="page">Поиск: <?php echo htmlspecialchars($searchQuery); ?></li>
                <?php elseif ($currentCategory !== 'all'): ?>
                    <li class="breadcrumb-item<?php echo $currentSubcategory === 0 ? ' active' : ''; ?>"<?php echo $currentSubcategory === 0 ? ' aria-current="page"' : ''; ?>>
                        <?php if ($currentSubcategory === 0): ?>
                            <?php echo htmlspecialchars($currentCategory); ?>
                        <?php else: ?>
                            <a href="/catalog.php?category=<?php echo urlencode($currentCategory); ?>"><?php echo htmlspecialchars($currentCategory); ?></a>
                        <?php endif; ?>
                    </li>
                    <?php if ($currentSubcategory > 0): 
                        $subcategoryName = '';
                        $subcategories = getSubcategories($currentCategory);
                        foreach ($subcategories as $subcategory) {
                            if ($subcategory['id'] == $currentSubcategory) {
                                $subcategoryName = $subcategory['name'];
                                break;
                            }
                        }
                    ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($subcategoryName); ?></li>
                    <?php endif; ?>
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
                                    <a class="text-decoration-none" href="/catalog.php">
                                        <i class="fas fa-th-large me-2"></i> Все категории
                                    </a>
                                </li>
                                <?php foreach ($mainCategories as $category): 
                                    $categoryIcon = $category === 'Android' ? '<i class="fab fa-android me-2"></i>' : '<i class="fab fa-apple me-2"></i>';
                                    // Определяем, должно ли быть открыто меню подкатегорий
                                    $isMenuOpen = false; // Всегда закрыто при загрузке страницы
                                    $menuDisplay = $isMenuOpen ? 'block' : 'none';
                                    $toggleIcon = $isMenuOpen ? 'fa-chevron-up' : 'fa-chevron-down';
                                ?>
                                <li class="list-group-item<?php echo $currentCategory === $category ? ' active' : ''; ?> category-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a class="text-decoration-none" href="/catalog.php?category=<?php echo urlencode($category); ?>">
                                            <?php echo $categoryIcon . htmlspecialchars($category); ?>
                                        </a>
                                        <span class="subcategory-toggle"><i class="fas <?php echo $toggleIcon; ?>"></i></span>
                                    </div>
                                    <div class="subcategories-menu">
                                        <ul class="list-unstyled ps-3 mt-2 mb-0">
                                            <?php
                                            // Используем принудительно заданные подкатегории, если они есть
                                            if (isset($forcedSubcategories[$category])) {
                                                $subcats = $forcedSubcategories[$category];
                                            } else {
                                                $subcats = getPopularSubcategories($category, 10);
                                            }
                                            
                                            foreach ($subcats as $subcategory) {
                                                $icon = '';
                                                $subcategoryName = $subcategory['name'];
                                                
                                                if (strpos($subcategoryName, 'Чехлы') !== false) {
                                                    $icon = '<i class="fas fa-mobile-alt"></i>';
                                                } elseif (strpos($subcategoryName, 'Зарядные') !== false) {
                                                    $icon = '<i class="fas fa-plug"></i>';
                                                } elseif (strpos($subcategoryName, 'Защитные стекла') !== false) {
                                                    $icon = '<i class="fas fa-shield-alt"></i>';
                                                } elseif (strpos($subcategoryName, 'Наушники') !== false) {
                                                    $icon = '<i class="fas fa-headphones"></i>';
                                                } elseif (strpos($subcategoryName, 'Аксессуары') !== false) {
                                                    $icon = '<i class="fas fa-cogs"></i>';
                                                }
                                                
                                                $subcategoryId = $subcategory['id'];
                                                $count = isset($subcategory['count']) ? $subcategory['count'] : 0;
                                                
                                                $countBadge = $count > 0 ? '<span class="badge bg-light text-dark rounded-pill">' . $count . '</span>' : '';
                                                
                                                echo '<li class="mb-1"><a href="/catalog.php?category=' . urlencode($category) . '&subcategory=' . urlencode($subcategoryId) . '" class="text-decoration-none subcategory-link">' . $icon . '<span class="link-text">' . htmlspecialchars($subcategoryName) . '</span>' . $countBadge . '</a></li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
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
                            <form class="filter-form" id="ajax-filter-form" method="GET" action="/catalog.php" onsubmit="return false;">
                                <!-- Скрытые поля для сохранения текущих параметров -->
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($currentCategory); ?>">
                                <?php if ($currentSubcategory > 0): ?>
                                <input type="hidden" name="subcategory" value="<?php echo $currentSubcategory; ?>">
                                <?php endif; ?>
                                <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="sort" id="hidden-sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                                
                                <div class="filter-group mb-3">
                                    <h4 class="filter-title fs-6 mb-2">Цена</h4>
                                    <div class="price-range row g-2">
                                        <div class="col">
                                            <input type="number" min="0" placeholder="от" name="min_price" id="min-price" value="<?php echo $minPrice !== null ? $minPrice : ''; ?>" class="form-control price-input">
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <span class="range-sep">-</span>
                                        </div>
                                        <div class="col">
                                            <input type="number" min="0" placeholder="до" name="max_price" id="max-price" value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>" class="form-control price-input">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="filter-group mb-3">
                                    <h4 class="filter-title fs-6 mb-2">Тип товара</h4>
                                    <div class="checkbox-group">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input filter-checkbox" type="checkbox" id="newItems" name="type[]" value="new" <?php echo in_array('new', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="newItems">
                                                Новинки
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input filter-checkbox" type="checkbox" id="saleItems" name="type[]" value="sale" <?php echo in_array('sale', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="saleItems">
                                                Со скидкой
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input filter-checkbox" type="checkbox" id="bestsellerItems" name="type[]" value="bestseller" <?php echo in_array('bestseller', $typeFilters) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="bestsellerItems">
                                                Хиты продаж
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" id="apply-filters" class="btn btn-primary">Применить</button>
                                    <button type="button" id="reset-filters" class="btn btn-outline-secondary">Сбросить</button>
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
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <div class="product-badges">
                                            <?php echo $badges; ?>
                                        </div>
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

<!-- Подключение JavaScript для инициализации карточек товаров -->
<script src="/js/product-cards.js"></script>

<!-- Подключение JavaScript для инициализации категорий и подкатегорий -->
<script src="/js/catalog-categories.js"></script>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 