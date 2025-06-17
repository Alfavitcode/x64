/**
 * Улучшенная версия мобильного меню с множественными резервными методами
 * Версия: 2.0
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Mobile menu script loaded');
    
    // Получаем все необходимые элементы
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const mobileMenu = document.querySelector('.mobile-menu');
    const openMenuBtn = document.getElementById('openMobileMenu');
    const closeMenuBtn = document.getElementById('closeMobileMenu');
    
    if (!mobileMenuOverlay || !mobileMenu) {
        console.error('Mobile menu elements not found!', {mobileMenuOverlay, mobileMenu});
        return;
    }
    
    console.log('Mobile menu elements found', {mobileMenuOverlay, mobileMenu});
    
    // Функция открытия меню с множественными резервными методами
    function openMobileMenu() {
        console.log('Opening mobile menu');
        
        // Метод 1: CSS классы
        mobileMenuOverlay.classList.add('active');
        
        // Метод 2: Прямые inline стили с !important для перекрытия
        mobileMenuOverlay.style.cssText = 'visibility: visible !important; opacity: 1 !important;';
        mobileMenu.style.cssText = 'transform: translateX(0) !important; right: 0 !important;';
        
        // Метод 3: setAttribute для стилей
        mobileMenuOverlay.setAttribute('style', 'visibility: visible !important; opacity: 1 !important;');
        mobileMenu.setAttribute('style', 'transform: translateX(0) !important; right: 0 !important;');
        
        // Блокируем прокрутку страницы
        document.body.style.overflow = 'hidden';
        
        // Логируем текущие стили для отладки
        console.log('Menu styles after opening:', {
            overlayVisibility: getComputedStyle(mobileMenuOverlay).visibility,
            overlayOpacity: getComputedStyle(mobileMenuOverlay).opacity,
            menuTransform: getComputedStyle(mobileMenu).transform,
            menuRight: getComputedStyle(mobileMenu).right
        });
    }
    
    // Функция закрытия меню
    function closeMobileMenu() {
        console.log('Closing mobile menu');
        
        // Метод 1: CSS классы
        mobileMenuOverlay.classList.remove('active');
        
        // Метод 2: Прямые inline стили с !important для перекрытия
        mobileMenuOverlay.style.cssText = 'visibility: hidden !important; opacity: 0 !important;';
        mobileMenu.style.cssText = 'transform: translateX(100%) !important; right: -350px !important;';
        
        // Метод 3: setAttribute для стилей
        mobileMenuOverlay.setAttribute('style', 'visibility: hidden !important; opacity: 0 !important;');
        mobileMenu.setAttribute('style', 'transform: translateX(100%) !important; right: -350px !important;');
        
        // Разблокируем прокрутку страницы
        document.body.style.overflow = '';
        
        // Логируем текущие стили для отладки
        console.log('Menu styles after closing:', {
            overlayVisibility: getComputedStyle(mobileMenuOverlay).visibility,
            overlayOpacity: getComputedStyle(mobileMenuOverlay).opacity,
            menuTransform: getComputedStyle(mobileMenu).transform,
            menuRight: getComputedStyle(mobileMenu).right
        });
    }
    
    // Назначаем обработчики событий
    if (openMenuBtn) {
        console.log('Adding click handler to open button');
        openMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openMobileMenu();
        });
    }
    
    if (closeMenuBtn) {
        console.log('Adding click handler to close button');
        closeMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeMobileMenu();
        });
    }
    
    // Закрытие по клику на оверлей
    if (mobileMenuOverlay) {
        console.log('Adding click handler to overlay');
        mobileMenuOverlay.addEventListener('click', function(e) {
            // Закрываем только если клик был непосредственно на оверлее
            if (e.target === mobileMenuOverlay) {
                closeMobileMenu();
            }
        });
    }
    
    // Резервное закрытие по клавише Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });
    
    // Также назначаем обработчики через атрибуты onclick
    if (openMenuBtn) {
        openMenuBtn.onclick = function(e) {
            e.preventDefault();
            openMobileMenu();
            return false;
        };
    }
    
    if (closeMenuBtn) {
        closeMenuBtn.onclick = function(e) {
            e.preventDefault();
            closeMobileMenu();
            return false;
        };
    }
    
    // Экспортируем функции в глобальную область видимости
    window.openMobileMenu = openMobileMenu;
    window.closeMobileMenu = closeMobileMenu;
    
    // Отладочная информация
    console.log('Mobile menu initialized. Use window.openMobileMenu() and window.closeMobileMenu() to control menu.');
}); 