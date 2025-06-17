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

// Улучшенная обработка массива типов товаров
$typeFilters = [];
if (isset($_GET['type']) && is_array($_GET['type'])) {
    $typeFilters = $_GET['type'];
} elseif (isset($_GET['type'])) {
    // Если передан один параметр, преобразуем его в массив
    $typeFilters = [$_GET['type']];
}

// Очистка и валидация параметров типов
$validTypes = ['new', 'sale', 'bestseller'];
$typeFilters = array_filter($typeFilters, function($type) use ($validTypes) {
    return in_array($type, $validTypes);
});

// Отладочная информация
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    echo '<pre>';
    echo "Параметры фильтрации:\n";
    echo "Категория: " . $currentCategory . "\n";
    echo "Подкатегория: " . $currentSubcategory . "\n";
    echo "Мин. цена: " . ($minPrice ?? 'не указана') . "\n";
    echo "Макс. цена: " . ($maxPrice ?? 'не указана') . "\n";
    echo "Типы товаров: " . implode(', ', $typeFilters) . "\n";
    echo "Сортировка: " . ($sortBy ?? 'не указана') . "\n";
    echo '</pre>';
}

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
    <!-- Скрытые поля для хранения параметров -->
    <div class="filter-params-storage" style="display:none;">
        <input type="hidden" id="current-sort" value="<?php echo htmlspecialchars($sortBy); ?>">
        <input type="hidden" id="current-category" value="<?php echo htmlspecialchars($currentCategory); ?>">
        <input type="hidden" id="current-subcategory" value="<?php echo $currentSubcategory; ?>">
        <?php if ($minPrice !== null): ?>
        <input type="hidden" id="current-min-price" value="<?php echo $minPrice; ?>">
        <?php endif; ?>
        <?php if ($maxPrice !== null): ?>
        <input type="hidden" id="current-max-price" value="<?php echo $maxPrice; ?>">
        <?php endif; ?>
        <?php foreach ($typeFilters as $type): ?>
        <input type="hidden" class="current-type-filter" value="<?php echo htmlspecialchars($type); ?>">
        <?php endforeach; ?>
    </div>
    
    <div class="catalog-header d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div class="catalog-sort d-flex align-items-center" style="background-color: #f8f9fa !important; padding: 15px 20px !important; border-radius: 10px !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important; position: relative !important;">
            <label for="sort-select" class="me-2" style="font-weight: 600 !important; color: #2d3142 !important;">Сортировать по:</label>
            <select id="sort-select" class="form-select sort-select" data-current="<?php echo htmlspecialchars($sortBy); ?>" style="appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
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

<!-- Подключение улучшенных стилей для каталога -->
<link rel="stylesheet" href="/css/components/catalog-enhanced.css">

<!-- Подключение исправленных стилей для меню категорий и фильтров -->
<link rel="stylesheet" href="/css/components/catalog-fix.css">

<!-- Приоритетные стили для исправления бледного меню -->
<link rel="stylesheet" href="/css/components/catalog-override.css">

<!-- Исправленные стили для фильтра сортировки -->
<link rel="stylesheet" href="/css/components/sort-filter-fix.css">

<!-- Встроенные стили с наивысшим приоритетом -->
<style>
/* Критически важные стили, которые нельзя перезаписать */
.card-header,
.category-header,
.filter-header,
.sidebar-widget .card-header,
.categories-widget .card-header,
.filters-widget .card-header {
    background: #2345c2 !important;
    background-image: linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%) !important;
    background-color: #2345c2 !important;
    color: white !important;
    border: none !important;
    padding: 15px 20px !important;
    font-weight: 700 !important;
    border-radius: 0 !important;
    box-shadow: 0 3px 8px rgba(28, 53, 163, 0.4) !important;
}

.widget-title,
.sidebar-widget .widget-title,
.categories-widget .widget-title,
.filters-widget .widget-title {
    color: white !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important;
    letter-spacing: 0.5px !important;
}

#apply-filters {
    background: #2345c2 !important;
    background-image: linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%) !important;
    background-color: #2345c2 !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 12px rgba(28, 53, 163, 0.4) !important;
    font-weight: 600 !important;
}

.subcategories-menu.show {
    display: block !important;
    height: auto !important;
    opacity: 1 !important;
}
</style>

<!-- Подключение библиотек для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="/js/animations/catalog-animations.js"></script>

<!-- Подключение JavaScript для инициализации карточек товаров -->
<script src="/js/product-cards.js"></script>

<!-- Подключение JavaScript для инициализации категорий и подкатегорий -->
<script src="/js/catalog-categories.js"></script>

<!-- Подключение скрипта для исправления проблемы с бледными цветами -->
<script src="/js/catalog-style-fix.js"></script>

<!-- Подключение скрипта для отладки фильтров -->
<script src="/js/debug-filters.js"></script>

<!-- Подключение скрипта для исправления проблем с фильтрацией категорий и подкатегорий -->
<script src="/js/catalog-subcategory-fix.js"></script>

<!-- Стили для заголовка страницы -->
<style>
    /* Заголовок страницы */
    .page-header-bg {
        background: linear-gradient(135deg, #f1f5ff 0%, #e7eeff 100%);
        border-radius: 15px;
        padding: 30px;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .page-header-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(77, 97, 252, 0.05) 0%, transparent 70%);
        transform: rotate(-15deg);
    }
    
    .page-title {
        position: relative;
        z-index: 2;
        font-weight: 700;
        color: #2e3a59;
        margin-bottom: 10px;
    }
    
    .breadcrumb {
        position: relative;
        z-index: 2;
    }
</style>

<!-- Заголовок страницы -->
<section class="py-4">
    <div class="container mt-4 animate-fade-up">
        <div class="page-header-bg">
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
    </div>
</section>

<!-- Каталог товаров -->
<section class="catalog-section section py-5" style="background-color: #ffffff !important;">
    <div class="container">
        <!-- Скрытые поля для хранения параметров -->
        <div class="filter-params-storage" style="display:none;">
            <input type="hidden" id="current-sort" value="<?php echo htmlspecialchars($sortBy); ?>">
            <input type="hidden" id="current-category" value="<?php echo htmlspecialchars($currentCategory); ?>">
            <input type="hidden" id="current-subcategory" value="<?php echo $currentSubcategory; ?>">
            <?php if ($minPrice !== null): ?>
            <input type="hidden" id="current-min-price" value="<?php echo $minPrice; ?>">
            <?php endif; ?>
            <?php if ($maxPrice !== null): ?>
            <input type="hidden" id="current-max-price" value="<?php echo $maxPrice; ?>">
            <?php endif; ?>
            <?php foreach ($typeFilters as $type): ?>
            <input type="hidden" class="current-type-filter" value="<?php echo htmlspecialchars($type); ?>">
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            <!-- Фильтры и категории -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <aside class="catalog-sidebar">
                    <div class="sidebar-widget categories-widget card mb-4" style="background-color: white !important; border: none !important; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1) !important;">
                        <div class="card-header category-header" style="background: #2345c2 !important; background: linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%) !important; color: white !important; border: none !important; box-shadow: 0 3px 8px rgba(28, 53, 163, 0.4) !important;">
                            <h3 class="widget-title m-0 fs-5" style="color: white !important; font-weight: 700 !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important; letter-spacing: 0.5px !important;">Категории</h3>
                        </div>
                        <div class="card-body p-0" style="background-color: white !important;">
                            <ul class="categories-list list-group list-group-flush" style="background-color: white !important; border: none !important;">
                                <li class="list-group-item<?php echo $currentCategory === 'all' ? ' active' : ''; ?>" style="background-color: white !important;">
                                    <a class="text-decoration-none" href="/catalog.php" style="color: #5a5c69 !important;">
                                        <i class="fas fa-th-large me-2"></i> Все категории
                                    </a>
                                </li>
                                <?php foreach ($mainCategories as $category): 
                                    $categoryIcon = $category === 'Android' ? '<i class="fab fa-android me-2"></i>' : '<i class="fab fa-apple me-2"></i>';
                                    // Определяем, должно ли быть открыто меню подкатегорий
                                    $isMenuOpen = $currentCategory === $category; // Открываем меню для текущей категории
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
                                    <div class="subcategories-menu <?php echo $isMenuOpen ? 'show' : ''; ?>" style="<?php echo $isMenuOpen ? 'display: block !important; height: auto !important; opacity: 1 !important;' : ''; ?>">
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
                    
                    <div class="sidebar-widget filters-widget card" style="background-color: white !important; border: none !important; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1) !important;">
                        <div class="card-header filter-header" style="background: #2345c2 !important; background: linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%) !important; color: white !important; border: none !important; box-shadow: 0 3px 8px rgba(28, 53, 163, 0.4) !important;">
                            <h3 class="widget-title m-0 fs-5" style="color: white !important; font-weight: 700 !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important; letter-spacing: 0.5px !important;">Фильтры</h3>
                        </div>
                        <div class="card-body" style="background-color: white !important;">
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
                                            <input type="number" min="0" placeholder="от" name="min_price" id="min-price" value="<?php echo $minPrice !== null ? $minPrice : ''; ?>" class="form-control price-input" style="appearance: textfield !important; border: 1px solid #e1e4e8 !important; border-radius: 6px !important; padding: 8px 15px !important; width: 100% !important;">
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <span class="range-sep">-</span>
                                        </div>
                                        <div class="col">
                                            <input type="number" min="0" placeholder="до" name="max_price" id="max-price" value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>" class="form-control price-input" style="appearance: textfield !important; border: 1px solid #e1e4e8 !important; border-radius: 6px !important; padding: 8px 15px !important; width: 100% !important;">
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
                                    <button type="button" id="apply-filters" class="btn btn-primary" style="background: #2345c2 !important; background: linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%) !important; border: none !important; color: white !important; box-shadow: 0 4px 12px rgba(28, 53, 163, 0.4) !important; font-weight: 600 !important;">Применить</button>
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
                        <div class="catalog-sort d-flex align-items-center" style="background-color: #f8f9fa !important; padding: 15px 20px !important; border-radius: 10px !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important; position: relative !important;">
                            <label for="sort-select" class="me-2" style="font-weight: 600 !important; color: #2d3142 !important;">Сортировать по:</label>
                            <select id="sort-select" class="form-select sort-select" data-current="<?php echo htmlspecialchars($sortBy); ?>" style="appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
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

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 

<!-- Встроенный скрипт для исправления проблем с фильтрацией -->
<script>
// Эта функция запускается после загрузки страницы и всех других скриптов
document.addEventListener('DOMContentLoaded', function() {
    // Задержка для того, чтобы другие скрипты успели инициализироваться
    setTimeout(function() {
        console.log('Запуск дополнительного скрипта для исправления фильтров');
        
        // Исправление работы чекбоксов
        const checkboxes = document.querySelectorAll('.filter-checkbox');
        if (checkboxes.length > 0) {
            console.log('Найдено чекбоксов:', checkboxes.length);
            
            // Повторная инициализация чекбоксов
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    console.log('Изменение чекбокса:', this.id, 'состояние:', this.checked);
                });
            });
        }
        
        // Исправление работы селекта сортировки
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            console.log('Текущее значение селекта сортировки:', sortSelect.value);
            
            // Получаем значение из URL
            const urlParams = new URLSearchParams(window.location.search);
            const sortParam = urlParams.get('sort');
            
            if (sortParam && sortSelect.value !== sortParam) {
                console.log('Несоответствие сортировки! URL:', sortParam, 'Селект:', sortSelect.value);
                console.log('Принудительно устанавливаем значение селекта');
                sortSelect.value = sortParam;
            }
        }
        
        // Проверка работы формы фильтров
        const filterForm = document.getElementById('ajax-filter-form');
        if (filterForm) {
            console.log('Форма фильтров найдена');
            
            // Дополнительный обработчик для кнопки применить
            const applyButton = document.getElementById('apply-filters');
            if (applyButton) {
                applyButton.addEventListener('click', function() {
                    console.log('Нажата кнопка применить фильтры');
                    
                    // Собираем все значения фильтров для отладки
                    const formData = new FormData(filterForm);
                    for (const [key, value] of formData.entries()) {
                        console.log(`Фильтр: ${key} = ${value}`);
                    }
                });
            }
        }
    }, 500);
});
</script> 

<!-- JavaScript для анимации заголовка -->
<script>
$(document).ready(function() {
    // Анимация появления элементов при прокрутке
    function animateElements() {
        $('.animate-fade-up').each(function() {
            var elementPos = $(this).offset().top;
            var topOfWindow = $(window).scrollTop();
            var windowHeight = $(window).height();
            
            if (elementPos < topOfWindow + windowHeight - 50) {
                $(this).css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }
        });
    }
    
    // Запускаем анимацию при загрузке страницы
    setTimeout(function() {
        animateElements();
    }, 100);
    
    // Запускаем анимацию при прокрутке
    $(window).on('scroll', function() {
        animateElements();
    });
});
</script>

<!-- Дополнительные стили для анимации -->
<style>
    /* Анимация появления элементов */
    .animate-fade-up {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.8s ease, transform 0.8s ease;
    }
</style> 