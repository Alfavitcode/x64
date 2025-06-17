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
        /* Определение CSS переменных для всего сайта */
        :root {
            --primary-color: #4d61fc;
            --primary-color-hover: #3a4cd1;
            --secondary-color: #6c757d;
            --primary-color-rgb: 77, 97, 252;
            --secondary-color-rgb: 108, 117, 125;
        }
        
        /* Стили для эффекта печати в логотипе */
        .typing-effect {
            position: relative;
            display: inline-block;
            font-weight: bold;
        }
        
        .cursor {
            display: inline-block;
            font-weight: bold;
            font-size: 1.2em;
            animation: cursor-blink 1s infinite;
        }
        
        @keyframes cursor-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        
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

        /* Встроенные стили для мобильного меню - для гарантированной работы */
        .mobile-menu-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            z-index: 9998 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: opacity 0.3s ease, visibility 0.3s ease !important;
        }
        
        .mobile-menu-overlay.active {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .mobile-menu {
            position: fixed !important;
            top: 0 !important;
            right: -350px !important;
            width: 320px !important;
            max-width: 90% !important;
            height: 100% !important;
            background-color: white !important;
            z-index: 9999 !important;
            transition: right 0.3s ease !important;
            display: flex !important;
            flex-direction: column !important;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        .mobile-menu-overlay.active .mobile-menu {
            right: 0 !important;
        }
        
        .mobile-menu__header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 15px !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        .mobile-menu-close {
            background: none !important;
            border: none !important;
            color: #212529 !important;
            font-size: 20px !important;
            cursor: pointer !important;
            width: 40px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .mobile-menu__body {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 0 !important;
        }
        
        .mobile-menu__nav {
            padding: 20px 0 !important;
        }
        
        .mobile-menu__list {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .mobile-menu__list li {
            margin-bottom: 5px !important;
        }
        
        .mobile-menu__list a {
            display: block !important;
            padding: 15px 20px !important;
            color: #212529 !important;
            font-weight: 500 !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
        }
        
        .mobile-menu__list a.active {
            color: #4d61fc !important;
            font-weight: 600 !important;
        }
        
        .mobile-menu__actions {
            padding: 15px 0 !important;
            border-top: 1px solid #e9ecef !important;
        }
        
        .mobile-action {
            display: flex !important;
            align-items: center !important;
            padding: 15px 20px !important;
            color: #212529 !important;
            font-weight: 500 !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
        }
        
        .mobile-action i {
            margin-right: 15px !important;
            width: 20px !important;
            text-align: center !important;
            font-size: 18px !important;
        }
        
        .mobile-menu-toggle {
            display: none !important;
            background-color: transparent !important;
            border: none !important;
            cursor: pointer !important;
            width: 40px !important;
            height: 40px !important;
            position: relative !important;
            padding: 0 !important;
        }
        
        .mobile-menu-toggle span {
            display: block !important;
            width: 24px !important;
            height: 2px !important;
            background-color: #212529 !important;
            margin: 5px auto !important;
            transition: all 0.3s !important;
        }
        
        @media (max-width: 991px) {
            .mobile-menu-toggle {
                display: block !important;
            }
        }
        
        @media (min-width: 992px) {
            .mobile-menu-toggle {
                display: none !important;
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
    <!-- Мобильное меню -->
    <script src="/js/mobile-menu.js"></script>
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
                    <li class="nav-menu__item"><a href="/contact.php" class="nav-menu__link<?php echo $currentFile === 'contact.php' ? ' active' : ''; ?>">Контакты</a></li>
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
            
            <button class="mobile-menu-toggle" type="button" id="openMobileMenu" onclick="document.getElementById('mobileMenuOverlay').style.opacity='1'; document.getElementById('mobileMenuOverlay').style.visibility='visible'; document.querySelector('.mobile-menu').style.transform='translateX(0)'; document.body.style.overflow='hidden'; return false;" style="background-color: transparent; border: none; cursor: pointer; width: 40px; height: 40px; position: relative; padding: 0; z-index: 100; outline: none;">
                <span style="display: block; width: 24px; height: 2px; background-color: #212529; margin: 5px auto; transition: all 0.3s;"></span>
                <span style="display: block; width: 24px; height: 2px; background-color: #212529; margin: 5px auto; transition: all 0.3s;"></span>
                <span style="display: block; width: 24px; height: 2px; background-color: #212529; margin: 5px auto; transition: all 0.3s;"></span>
            </button>
        </div>
    </header>
    
    <!-- Мобильное меню (не модальное окно) -->
    <div id="mobileMenuOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9998; visibility: hidden; opacity: 0; transition: opacity 0.3s ease, visibility 0.3s ease;">
        <div class="mobile-menu" style="position: fixed; top: 0; right: 0; width: 320px; max-width: 90%; height: 100%; background-color: white; z-index: 9999; transform: translateX(100%); transition: transform 0.3s ease; display: flex !important; flex-direction: column; box-shadow: -5px 0 15px rgba(0,0,0,0.1);">
            <div class="mobile-menu__header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #e9ecef;">
                <div class="logo logo-static">x64</div>
                <button type="button" id="closeMobileMenu" onclick="document.getElementById('mobileMenuOverlay').style.opacity='0'; document.getElementById('mobileMenuOverlay').style.visibility='hidden'; document.querySelector('.mobile-menu').style.transform='translateX(100%)'; document.body.style.overflow=''; return false;" style="background: none; border: none; color: #212529; font-size: 20px; cursor: pointer; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="flex: 1; overflow-y: auto; padding: 0;">
                <nav style="padding: 20px 0;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="/" style="display: block; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none; <?php echo $currentFile === 'index.php' ? 'color: #4d61fc; font-weight: 600;' : ''; ?>">Главная</a></li>
                        <li><a href="/catalog.php" style="display: block; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none; <?php echo $currentFile === 'catalog.php' ? 'color: #4d61fc; font-weight: 600;' : ''; ?>">Каталог</a></li>
                        <li><a href="/contact.php" style="display: block; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none; <?php echo $currentFile === 'contact.php' ? 'color: #4d61fc; font-weight: 600;' : ''; ?>">Контакты</a></li>
                        <?php if ($isAdmin): ?>
                        <li><a href="/admin/" style="display: block; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none; <?php echo strpos($currentFile, 'admin') !== false ? 'color: #4d61fc; font-weight: 600;' : ''; ?>">Админ-панель</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div style="padding: 15px 0; border-top: 1px solid #e9ecef;">
                    <a href="<?php echo $accountUrl; ?>" style="display: flex; align-items: center; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none;">
                        <i class="fas fa-user" style="margin-right: 15px; width: 20px; text-align: center; font-size: 18px;"></i> <?php echo $isLoggedIn ? 'Личный кабинет' : 'Войти'; ?>
                    </a>
                    <a href="/cart" style="display: flex; align-items: center; padding: 15px 20px; color: #212529; font-weight: 500; font-size: 16px; transition: all 0.3s ease; text-decoration: none;">
                        <div style="position: relative; margin-right: 15px;">
                            <i class="fas fa-shopping-cart" style="margin-right: 0; width: 20px; text-align: center; font-size: 18px;"></i>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        </div>
                        Корзина
                    </a>
                </div>
            </div>
        </div>
    </div>
    
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
    
    <main class="main-content"> 

<!-- Упрощенный скрипт для мобильного меню - управление стилями напрямую -->
<script>
// Глобальные функции для прямого управления мобильным меню
function openMobileMenuDirect() {
    var overlay = document.getElementById('mobileMenuOverlay');
    var menu = document.querySelector('.mobile-menu');
    
    if (overlay && menu) {
        console.log('Открываем меню напрямую');
        // Прямая установка стилей
        overlay.style.visibility = 'visible';
        overlay.style.opacity = '1';
        menu.style.transform = 'translateX(0)';
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Элементы не найдены', {overlay, menu});
    }
}

function closeMobileMenuDirect() {
    var overlay = document.getElementById('mobileMenuOverlay');
    var menu = document.querySelector('.mobile-menu');
    
    if (overlay && menu) {
        console.log('Закрываем меню напрямую');
        // Прямая установка стилей
        overlay.style.visibility = 'hidden';
        overlay.style.opacity = '0';
        menu.style.transform = 'translateX(100%)';
        document.body.style.overflow = '';
    }
}

// Инициализация после загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    // Получаем элементы
    var openBtn = document.getElementById('openMobileMenu');
    var closeBtn = document.getElementById('closeMobileMenu');
    var overlay = document.getElementById('mobileMenuOverlay');
    
    console.log('Инициализация прямого меню:', {openBtn, closeBtn, overlay});
    
    // Установка обработчиков
    if (openBtn) {
        openBtn.onclick = function(e) {
            e.preventDefault();
            openMobileMenuDirect();
        };
    }
    
    if (closeBtn) {
        closeBtn.onclick = function(e) {
            e.preventDefault();
            closeMobileMenuDirect();
        };
    }
    
    if (overlay) {
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                closeMobileMenuDirect();
            }
        };
    }
});
</script>

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
                    
                    html += `</div>`;
                    
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
                    
                    html += `</div>`;
                    
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

<!-- Функция для анимации печати логотипа -->
<script>
function animateLogo() {
    const logoElement = document.querySelector('.typing-effect');
    const originalText = logoElement.getAttribute('data-text');
    let isDeleting = false;
    let charIndex = 0;
    
    function updateLogo() {
        // Получаем текущий текст в зависимости от индекса
        let currentText = originalText.substring(0, charIndex);
        
        // Добавляем курсор после текущего текста
        logoElement.innerHTML = currentText + '<span class="cursor">|</span>';
        
        if (!isDeleting && charIndex <= originalText.length) {
            // Добавляем символы
            charIndex++;
            
            // Когда весь текст напечатан, подождать 1 секунду и начать удаление
            if (charIndex > originalText.length) {
                isDeleting = true;
                setTimeout(updateLogo, 1000); // Ждем 1 секунду перед началом удаления
                return;
            }
            
            // Добавляем случайную задержку для эффекта реального печатания
            const typingSpeed = Math.floor(Math.random() * 150) + 350; // 350-500ms
            setTimeout(updateLogo, typingSpeed);
        } else if (isDeleting && charIndex >= 0) {
            // Удаляем символы
            charIndex--;
            
            // Когда весь текст удален, начать заново
            if (charIndex < 0) {
                isDeleting = false;
                charIndex = 0;
                setTimeout(updateLogo, 500); // Небольшая пауза перед новым циклом
                return;
            }
            
            // Скорость удаления немного быстрее печатания
            const deletingSpeed = Math.floor(Math.random() * 100) + 350; // 350-450ms
            setTimeout(updateLogo, deletingSpeed);
        }
    }
    
    // Запускаем анимацию
    updateLogo();
}

// Запуск анимации после загрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    animateLogo();
});
</script> 
</body>
</html> 