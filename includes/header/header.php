<?php
// Подключаем файл управления сессиями
require_once __DIR__ . '/../config/session.php';

// Получаем имя текущего файла для определения активной страницы
$currentFile = basename($_SERVER['PHP_SELF']);

// Проверяем, авторизован ли пользователь
$isLoggedIn = isset($_SESSION['user_id']);

// Проверяем, является ли пользователь администратором
$isAdmin = false;
if ($isLoggedIn) {
    require_once __DIR__ . '/../config/db_config.php';
    require_once __DIR__ . '/../config/db_functions.php';
    $user = getUserById($_SESSION['user_id']);
    if ($user && $user['role'] === 'Администратор') {
        $isAdmin = true;
    }
}

// Определяем URL для кнопки "Кабинет"
$accountUrl = $isLoggedIn ? "/account/profile.php" : "/account/register.php";

// Получаем количество товаров в корзине
if (!isset($conn)) {
    require_once __DIR__ . '/../config/db_config.php';
    require_once __DIR__ . '/../config/db_functions.php';
}
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();
$cart_count = getCartItemCount($session_id, $user_id);

// Проверяем флаг для стандартного хедера (по умолчанию false)
$useStandardHeader = isset($useStandardHeader) ? $useStandardHeader : false;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Meta-тег для улучшения отображения WebGL контента -->
    <meta name="hardware-accelerated" content="true">
    <title>x64 - Интернет-магазин товаров</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/components/home.css">
    <link rel="stylesheet" href="/css/search-fix.css">
    <link rel="stylesheet" href="/css/fullscreen-search.css">
    <link rel="stylesheet" href="/css/notifications.css">
    <link rel="stylesheet" href="/css/header-scroll.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Принудительные стили для мобильного поиска */
        html body .mobile-search .search-results {
            position: fixed !important;
            width: 300px !important;
            min-width: 280px !important;
            max-width: 90vw !important;
            box-sizing: border-box !important;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            padding: 10px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            display: block !important;
            background-color: white !important;
            border-radius: 12px !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15) !important;
        }

        html body .search-result-item {
            padding: 12px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            display: flex !important;
        }

        html body .search-result-price {
            width: 100% !important;
            text-align: left !important;
            margin-top: 4px !important;
            overflow: visible !important;
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
        }

        html body .search-result-price .current-price {
            font-size: 16px !important;
            font-weight: bold !important;
            letter-spacing: -0.3px !important;
            white-space: nowrap !important;
            display: inline-block !important;
        }

        @media (max-width: 575px) {
            html body .mobile-search .search-results {
                width: 350px !important;
                min-width: 350px !important;
            }
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- Подключаем Three.js для 3D моделей -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/loaders/GLTFLoader.js"></script>
    <!-- GSAP для плавных анимаций -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
</head>
<body>
    <header class="header<?php echo ($useStandardHeader || $currentFile !== 'index.php') ? ' standard-header' : ''; ?>">
        <div class="container header__container">
            <div class="header__logo">
                <a href="/" class="logo typing-effect" data-text="x64">x64</a>
            </div>
            
            <!-- Поисковая строка для десктопа -->
            <div class="header__search desktop-search">
                <div class="search-wrapper">
                    <form action="/search.php" method="GET" class="search-form">
                        <input type="text" name="query" id="live-search" autocomplete="off" placeholder="Поиск товаров..." class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div class="search-results" id="search-results">
                        <!-- Здесь будут появляться результаты поиска -->
                    </div>
                </div>
            </div>
            
            <!-- Кнопка поиска для мобильных устройств -->
            <button class="search-toggle" id="search-toggle">
                <i class="fas fa-search"></i>
            </button>
            
            <nav class="header__nav">
                <ul class="nav-menu">
                    <li class="nav-menu__item"><a href="/" class="nav-menu__link<?php echo $currentFile === 'index.php' ? ' active' : ''; ?>">Главная</a></li>
                    <li class="nav-menu__item nav-menu__item--dropdown">
                        <a href="/catalog.php" class="nav-menu__link<?php echo $currentFile === 'catalog.php' ? ' active' : ''; ?>">Каталог</a>
                    </li>
                    <li class="nav-menu__item"><a href="/contacts.php" class="nav-menu__link<?php echo $currentFile === 'contacts.php' ? ' active' : ''; ?>">Контакты</a></li>
                </ul>
            </nav>
            
            <div class="header__actions">
                <?php if ($isAdmin): ?>
                <a href="/admin/" class="header__action">
                    <i class="fas fa-shield-alt"></i>
                    <span class="action-text">Админ-панель</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo $accountUrl; ?>" class="header__action">
                    <i class="fas fa-user"></i>
                    <span class="action-text"><?php echo $isLoggedIn ? 'Кабинет' : 'Войти'; ?></span>
                </a>
                <a href="/cart/index.php" class="header__action header__cart">
                    <div class="cart-icon-container">
                    <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </div>
                    <span class="action-text">Корзина</span>
                </a>
            </div>
            
            <button class="mobile-menu-toggle" type="button" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-controls="mobileMenuModal">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    
    <!-- Полноэкранный поиск -->
    <div class="fullscreen-search" id="fullscreen-search">
        <button class="fullscreen-search-close" id="fullscreen-search-close">
            <i class="fas fa-times"></i>
        </button>
        <div class="fullscreen-search-container">
            <form action="/search.php" method="GET" class="fullscreen-search-form">
                <input type="text" name="query" id="fullscreen-live-search" autocomplete="off" placeholder="Введите запрос для поиска..." class="fullscreen-search-input">
                <button type="submit" class="fullscreen-search-button">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div class="fullscreen-search-results" id="fullscreen-search-results">
                <!-- Здесь будут появляться результаты поиска -->
            </div>
        </div>
    </div>
    
    <!-- Модальное окно для мобильного меню -->
    <div class="modal fade" id="mobileMenuModal" tabindex="-1" aria-labelledby="mobileMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-end mobile-menu-modal">
            <div class="modal-content mobile-menu">
                <div class="modal-header mobile-menu__header">
                    <div class="logo logo-static">x64</div>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <nav class="mobile-menu__nav">
                        <ul class="mobile-menu__list">
                            <li><a href="/"<?php echo $currentFile === 'index.php' ? ' class="active"' : ''; ?>>Главная</a></li>
                            <li><a href="/catalog.php"<?php echo $currentFile === 'catalog.php' ? ' class="active"' : ''; ?>>Каталог</a></li>
                            <li><a href="/contacts.php"<?php echo $currentFile === 'contacts.php' ? ' class="active"' : ''; ?>>Контакты</a></li>
                            <?php if ($isAdmin): ?>
                            <li><a href="/admin/"<?php echo strpos($currentFile, 'admin') !== false ? ' class="active"' : ''; ?>>Админ-панель</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="mobile-menu__actions">
                        <a href="<?php echo $accountUrl; ?>" class="mobile-action">
                            <i class="fas fa-user"></i> <?php echo $isLoggedIn ? 'Личный кабинет' : 'Войти'; ?>
                        </a>
                        <a href="/wishlist" class="mobile-action"><i class="fas fa-heart"></i> Избранное</a>
                        <a href="/cart" class="mobile-action">
                            <div class="cart-icon-container">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            </div>
                            Корзина
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <main class="main-content"> 
    
<!-- Добавляем скрипт для живого поиска -->
<script>
$(document).ready(function() {
    // Загрузка количества товаров в корзине происходит в cart.js
    
    let searchTimeout;
    let searchResults = $('#search-results');
    let searchInput = $('#live-search');
    let fullscreenSearchResults = $('#fullscreen-search-results');
    let fullscreenSearchInput = $('#fullscreen-live-search');
    
    // Полноэкранный поиск
    const searchToggle = $('#search-toggle');
    const fullscreenSearch = $('#fullscreen-search');
    const fullscreenSearchClose = $('#fullscreen-search-close');
    
    // Открыть полноэкранный поиск
    searchToggle.on('click', function() {
        fullscreenSearch.addClass('active');
        fullscreenSearchInput.focus();
        $('body').addClass('overflow-hidden');
    });
    
    // Закрыть полноэкранный поиск
    fullscreenSearchClose.on('click', function() {
        fullscreenSearch.removeClass('active');
        $('body').removeClass('overflow-hidden');
    });
    
    // Закрыть по клику на Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && fullscreenSearch.hasClass('active')) {
            fullscreenSearch.removeClass('active');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Глобальная функция для полного восстановления прокрутки страницы
    function enableScroll() {
        $('body').removeClass('overflow-hidden');
        $('body').removeClass('modal-open');
        $('body').css({
            'position': '',
            'width': '',
            'height': '',
            'overflow': '',
            'padding-right': '0'
        });
        $('.modal-backdrop').remove();
    }
    
    // Мобильное меню - блокировка скролла
    $('#mobileMenuModal').on('show.bs.modal', function () {
        $('body').addClass('overflow-hidden');
    });
    
    $('#mobileMenuModal').on('hidden.bs.modal', function () {
        setTimeout(enableScroll, 10);
    });
    
    // Принудительно снимаем скролл-лок при клике на кнопку закрытия
    $('.btn-close').on('click', function() {
        setTimeout(enableScroll, 300);
    });
    
    // Добавляем обработчик на клик по фону (закрытие модалки)
    $(document).on('click', '.modal-backdrop', function() {
        setTimeout(enableScroll, 10);
    });
    
    // Проверяем и исправляем скролл при загрузке страницы
    setTimeout(enableScroll, 1000);
    
    // Функция для поиска (десктоп)
    function performSearch() {
        let query = searchInput.val().trim();
        
        // Если запрос слишком короткий, скрываем результаты
        if (query.length < 2) {
            searchResults.empty().hide();
            return;
        }
        
        // Показываем загрузку
        searchResults.html('<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Поиск...</div>').show();
        
        // Отправляем AJAX запрос
        $.ajax({
            url: '/ajax/search.php',
            method: 'GET',
            data: { query: query },
            dataType: 'json',
            success: function(response) {
                // Очищаем контейнер результатов
                searchResults.empty();
                
                // Если есть результаты, выводим их
                if (response.status === 'success' && response.count > 0) {
                    let html = '<div class="search-results-list">';
                    
                    // Добавляем каждый товар
                    $.each(response.results, function(index, product) {
                        html += `
                            <a href="${product.url}" class="search-result-item">
                                <div class="search-result-image">
                                    <img src="${product.image}" alt="${product.name}">
                                </div>
                                <div class="search-result-info">
                                    <div class="search-result-name">${product.name}</div>
                                    <div class="search-result-category">${product.category}</div>
                                    <div class="search-result-price">
                                        <span class="current-price">${product.price}</span>
                                        ${product.oldPrice ? '<span class="old-price">' + product.oldPrice + '</span>' : ''}
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    
                    // Добавляем ссылку на все результаты поиска
                    html += `
                        <a href="/search.php?query=${encodeURIComponent(query)}" class="search-all-results">
                            Показать все результаты <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>`;
                    
                    searchResults.html(html).show();
                } else {
                    // Если результатов нет, выводим соответствующее сообщение
                    searchResults.html('<div class="search-no-results">По вашему запросу ничего не найдено</div>').show();
                }
            },
            error: function() {
                // В случае ошибки выводим сообщение
                searchResults.html('<div class="search-error">Произошла ошибка при поиске</div>').show();
            }
        });
    }
    
    // Функция для полноэкранного поиска
    function performFullscreenSearch() {
        let query = fullscreenSearchInput.val().trim();
        
        // Если запрос слишком короткий, скрываем результаты
        if (query.length < 2) {
            fullscreenSearchResults.empty().hide();
            return;
        }
        
        // Показываем загрузку
        fullscreenSearchResults.html('<div class="fullscreen-search-loading"><i class="fas fa-spinner fa-spin"></i> Поиск...</div>').show();
        
        // Отправляем AJAX запрос
        $.ajax({
            url: '/ajax/search.php',
            method: 'GET',
            data: { query: query },
            dataType: 'json',
            success: function(response) {
                // Очищаем контейнер результатов
                fullscreenSearchResults.empty();
                
                // Если есть результаты, выводим их
                if (response.status === 'success' && response.count > 0) {
                    let html = '<div class="fullscreen-search-results-list">';
                    
                    // Добавляем каждый товар
                    $.each(response.results, function(index, product) {
                        html += `
                            <a href="${product.url}" class="fullscreen-search-result-item">
                                <div class="fullscreen-search-result-image">
                                    <img src="${product.image}" alt="${product.name}">
                                </div>
                                <div class="fullscreen-search-result-info">
                                    <div class="fullscreen-search-result-name">${product.name}</div>
                                    <div class="fullscreen-search-result-category">${product.category}</div>
                                    <div class="fullscreen-search-result-price">
                                        <span class="current-price">${product.price}</span>
                                        ${product.oldPrice ? '<span class="old-price">' + product.oldPrice + '</span>' : ''}
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    
                    // Добавляем ссылку на все результаты поиска
                    html += `
                        <a href="/search.php?query=${encodeURIComponent(query)}" class="fullscreen-search-all-results">
                            Показать все результаты <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>`;
                    
                    fullscreenSearchResults.html(html).show();
                } else {
                    // Если результатов нет, выводим соответствующее сообщение
                    fullscreenSearchResults.html('<div class="fullscreen-search-no-results">По вашему запросу ничего не найдено</div>').show();
                }
            },
            error: function() {
                // В случае ошибки выводим сообщение
                fullscreenSearchResults.html('<div class="fullscreen-search-error">Произошла ошибка при поиске</div>').show();
            }
        });
    }
    
    // Обрабатываем ввод в поле поиска (десктоп)
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300);
    });
    
    // Обрабатываем ввод в поле полноэкранного поиска
    fullscreenSearchInput.on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performFullscreenSearch, 300);
    });
    
    // Обрабатываем клик вне поля поиска для скрытия результатов
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            searchResults.hide();
        }
    });
    
    // Не отправляем форму, если пустой запрос
    $('.search-form, .fullscreen-search-form').on('submit', function(e) {
        let input = $(this).find('input[name="query"]');
        if (input.val().trim().length < 2) {
            e.preventDefault();
        }
    });
});
</script> 