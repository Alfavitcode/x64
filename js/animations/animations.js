$(document).ready(function() {
    // Функция для проверки, находится ли элемент в видимой области
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom >= 0
        );
    }
    
    // Анимация при прокрутке
    function animateOnScroll() {
        // Анимация для категорий
        $('.category-card').each(function() {
            if (isInViewport(this) && !$(this).hasClass('animated')) {
                $(this).addClass('animated');
                $(this).css({
                    'animation': 'fadeInUp 0.6s ease forwards',
                    'opacity': '0',
                    'transform': 'translateY(30px)'
                });
                // Добавляем задержку для каждого следующего элемента
                const delay = $(this).index() * 0.1;
                $(this).css('animation-delay', delay + 's');
            }
        });
        
        // Анимация для товаров
        $('.product-card').each(function() {
            if (isInViewport(this) && !$(this).hasClass('animated')) {
                $(this).addClass('animated');
                $(this).css({
                    'animation': 'fadeInUp 0.6s ease forwards',
                    'opacity': '0',
                    'transform': 'translateY(30px)'
                });
                // Добавляем задержку для каждого следующего элемента
                const delay = $(this).index() * 0.1;
                $(this).css('animation-delay', delay + 's');
            }
        });
        
        // Анимация для баннеров
        $('.banner').each(function() {
            if (isInViewport(this) && !$(this).hasClass('animated')) {
                $(this).addClass('animated');
                $(this).css({
                    'animation': 'fadeIn 0.8s ease forwards',
                    'opacity': '0'
                });
            }
        });
        
        // Анимация для преимуществ
        $('.feature-card').each(function() {
            if (isInViewport(this) && !$(this).hasClass('animated')) {
                $(this).addClass('animated');
                $(this).css({
                    'animation': 'fadeInUp 0.6s ease forwards',
                    'opacity': '0',
                    'transform': 'translateY(30px)'
                });
                // Добавляем задержку для каждого следующего элемента
                const delay = $(this).index() * 0.15;
                $(this).css('animation-delay', delay + 's');
            }
        });
    }
    
    // Вызываем функцию анимации при прокрутке страницы
    $(window).on('scroll', function() {
        animateOnScroll();
    });
    
    // Вызываем функцию анимации при загрузке страницы
    animateOnScroll();
    
    // Анимация при наведении на категории
    $('.category-card').on('mouseenter', function() {
        $(this).find('.category-icon').css({
            'animation': 'pulse 0.5s ease'
        });
    }).on('mouseleave', function() {
        $(this).find('.category-icon').css({
            'animation': 'none'
        });
    });
    
    // Анимация кнопки добавления в корзину
    $('.btn-add-to-cart').on('mouseenter', function() {
        $(this).find('i').css({
            'animation': 'swing 0.5s ease'
        });
    }).on('mouseleave', function() {
        $(this).find('i').css({
            'animation': 'none'
        });
    });
    
    // Анимация для бейджей (ограничиваем пульсацию)
    $('.product-card .badge').each(function() {
        $(this).css({
            'transform-origin': 'center',
            'max-width': '100px',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis',
            'white-space': 'nowrap'
        });
    });

    // Заменяем старую анимацию pulse на badge-pulse более локализованного эффекта
    const updatedStyleSheet = document.createElement('style');
    updatedStyleSheet.type = 'text/css';
    updatedStyleSheet.innerText = `
        @keyframes badge-pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 3px 7px rgba(0, 0, 0, 0.15);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
        }
        
        .badge {
            max-width: 100px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            animation: badge-pulse 2s infinite !important;
        }
    `;
    document.head.appendChild(updatedStyleSheet);
    
    // Добавление стилей для анимаций
    const styleSheet = document.createElement('style');
    styleSheet.type = 'text/css';
    styleSheet.innerText = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        @keyframes swing {
            20% {
                transform: rotate(15deg);
            }
            40% {
                transform: rotate(-10deg);
            }
            60% {
                transform: rotate(5deg);
            }
            80% {
                transform: rotate(-5deg);
            }
            100% {
                transform: rotate(0deg);
            }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInLeft {
            from {
                transform: translateX(-100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Анимация для уведомлений */
        .notifications-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .notification {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            transform: translateX(100%);
            opacity: 0;
            animation: slideInRight 0.3s forwards;
        }
        
        .notification.hide {
            animation: slideOutRight 0.3s forwards;
        }
        
        @keyframes slideOutRight {
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        /* Анимация для кнопки добавления в корзину */
        .btn-add-to-cart.adding {
            background-color: var(--warning-color) !important;
        }
        
        .btn-add-to-cart.added {
            background-color: var(--success-color) !important;
        }
        
        /* Анимация для мобильного меню */
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        body.no-scroll {
            overflow: hidden;
        }
    `;
    document.head.appendChild(styleSheet);
    
    // Дополнительная анимация для слайдера
    $('.hero-slider').on('mouseenter', function() {
        $('.slider-controls').css({
            'opacity': '1',
            'transform': 'translateY(0)'
        });
    }).on('mouseleave', function() {
        $('.slider-controls').css({
            'opacity': '0.7',
            'transform': 'translateY(10px)'
        });
    });
    
    // Инициализация начальных анимаций
    $('.slider-controls').css({
        'opacity': '0.7',
        'transform': 'translateY(10px)',
        'transition': 'all 0.3s ease'
    });
    
    // Анимация для появления хедера при скролле вверх
    let lastScrollTop = 0;
    $(window).on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        
        if (scrollTop > 200) {
            if (scrollTop > lastScrollTop) {
                // Скролл вниз
                $('.header').css({
                    'transform': 'translateY(-100%)'
                });
            } else {
                // Скролл вверх
                $('.header').css({
                    'transform': 'translateY(0)',
                    'box-shadow': '0 4px 15px rgba(0, 0, 0, 0.1)'
                });
            }
        } else {
            $('.header').css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 10px rgba(0, 0, 0, 0.1)'
            });
        }
        
        lastScrollTop = scrollTop;
    });
}); 