document.addEventListener('DOMContentLoaded', function() {
    // Функция для плавного появления элементов при скролле
    function revealOnScroll() {
        const banners = document.querySelectorAll('.animated-banner');
        
        banners.forEach((banner, index) => {
            // Добавляем небольшую задержку для каждого баннера, чтобы они появлялись последовательно
            setTimeout(() => {
                banner.classList.add('reveal-visible');
            }, index * 200); // Увеличиваем задержку для более плавного появления
        });
    }
    
    // Активируем эффект параллакса для фоновых изображений
    function parallaxEffect() {
        const banners = document.querySelectorAll('.animated-banner');
        
        banners.forEach(banner => {
            banner.addEventListener('mousemove', (e) => {
                // Проверяем ширину экрана, чтобы отключить эффект на мобильных устройствах
                if (window.innerWidth <= 768) return;
                
                const bannerRect = banner.getBoundingClientRect();
                const mouseX = e.clientX - bannerRect.left;
                const mouseY = e.clientY - bannerRect.top;
                
                const centerX = bannerRect.width / 2;
                const centerY = bannerRect.height / 2;
                
                // Делаем движение более плавным
                const moveX = (mouseX - centerX) / 30;
                const moveY = (mouseY - centerY) / 30;
                
                const bannerImage = banner.querySelector('.banner-image');
                bannerImage.style.transform = `scale(1.05) translate(${-moveX}px, ${-moveY}px)`;
                
                // Добавляем эффект тени при наведении
                banner.style.boxShadow = `0 15px 35px rgba(0,0,0,${0.1 + Math.abs(moveX + moveY) / 200})`;
            });
            
            // Возвращаем изображение в нормальное положение при уходе мыши
            banner.addEventListener('mouseleave', () => {
                const bannerImage = banner.querySelector('.banner-image');
                bannerImage.style.transform = '';
                banner.style.boxShadow = '';
            });
            
            // Добавляем обработчик для touch-событий на мобильных устройствах
            banner.addEventListener('touchstart', () => {
                if (window.innerWidth <= 768) {
                    const bannerImage = banner.querySelector('.banner-image');
                    bannerImage.style.transform = 'scale(1.03)';
                    banner.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
                    
                    // Возвращаем в исходное состояние через 500мс
                    setTimeout(() => {
                        bannerImage.style.transform = '';
                        banner.style.boxShadow = '';
                    }, 500);
                }
            });
        });
    }
    
    // Добавляем интерактивные эффекты для кнопок
    function enhanceButtons() {
        // Упрощенные эффекты для мобильных устройств
        const isMobile = window.innerWidth <= 768;
        
        // Эффект пульсации для pulse-button
        const pulseButtons = document.querySelectorAll('.pulse-button');
        pulseButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const rect = button.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                button.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 800);
            });
            
            // Добавляем волнистое свечение по наведению только на десктопах
            if (!isMobile) {
                button.addEventListener('mouseenter', function() {
                    button.classList.add('pulse-hover');
                });
                
                button.addEventListener('mouseleave', function() {
                    button.classList.remove('pulse-hover');
                });
            }
        });
        
        // Добавляем случайные блики для glow-button
        const glowButtons = document.querySelectorAll('.glow-button');
        glowButtons.forEach(button => {
            if (!isMobile) {
                setInterval(() => {
                    if (Math.random() > 0.7) {
                        button.classList.add('glow-pulse');
                        setTimeout(() => {
                            button.classList.remove('glow-pulse');
                        }, 700);
                    }
                }, 2000);
                
                // Добавляем волнистое свечение при наведении
                button.addEventListener('mouseenter', function() {
                    const glow = document.createElement('div');
                    glow.className = 'button-glow';
                    button.appendChild(glow);
                });
                
                button.addEventListener('mouseleave', function() {
                    const glow = button.querySelector('.button-glow');
                    if (glow) {
                        glow.remove();
                    }
                });
            }
        });
        
        // Добавляем эффект для slide-button
        const slideButtons = document.querySelectorAll('.slide-button');
        slideButtons.forEach(button => {
            if (!isMobile) {
                button.addEventListener('mouseenter', function() {
                    button.classList.add('slide-active');
                });
                
                button.addEventListener('mouseleave', function() {
                    button.classList.remove('slide-active');
                });
            }
        });
    }
    
    // Отслеживаем изменение размера окна для подстройки эффектов
    window.addEventListener('resize', function() {
        // Обновляем эффекты при изменении размера окна
        enhanceButtons();
        
        // Если окно стало мобильным, сбрасываем все активные эффекты
        if (window.innerWidth <= 768) {
            const bannerImages = document.querySelectorAll('.banner-image');
            bannerImages.forEach(img => {
                img.style.transform = '';
            });
            
            const banners = document.querySelectorAll('.animated-banner');
            banners.forEach(banner => {
                banner.style.boxShadow = '';
            });
        }
    });
    
    // Добавляем CSS-классы для дополнительных анимаций
    function addExtraStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .animated-banner {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.8s cubic-bezier(0.25, 0.8, 0.25, 1), 
                            transform 0.8s cubic-bezier(0.25, 0.8, 0.25, 1),
                            box-shadow 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            
            .reveal-visible {
                opacity: 1;
                transform: translateY(0);
            }
            
            .ripple-effect {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.4);
                width: 20px;
                height: 20px;
                transform: scale(0);
                animation: ripple 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
                pointer-events: none;
            }
            
            @keyframes ripple {
                to {
                    transform: scale(15);
                    opacity: 0;
                }
            }
            
            .glow-pulse {
                animation: glow-pulse 0.7s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            @keyframes glow-pulse {
                0% { box-shadow: 0 0 5px rgba(255,255,255,0.5); }
                50% { box-shadow: 0 0 25px rgba(255,255,255,0.8); }
                100% { box-shadow: 0 0 5px rgba(255,255,255,0.5); }
            }
            
            .banner-badge {
                animation: badge-float 5s infinite ease-in-out;
                box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3);
                transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), 
                            box-shadow 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                max-width: 70px;
                max-height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                overflow: hidden;
            }
            
            @keyframes badge-float {
                0%, 100% { transform: translateY(0) rotate(5deg); }
                50% { transform: translateY(-5px) rotate(-3deg); }
            }
            
            .animated-banner:hover .banner-badge {
                transform: rotate(0deg) scale(1.05);
                box-shadow: 0 8px 25px rgba(255, 51, 102, 0.4);
            }
            
            .banner-tag {
                animation: tag-float 4s infinite alternate;
                box-shadow: 0 3px 10px rgba(51, 204, 102, 0.3);
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            @keyframes tag-float {
                0% { transform: translateY(-3px); opacity: 0.9; }
                100% { transform: translateY(0); opacity: 1; }
            }
            
            .animated-banner:hover .banner-tag {
                transform: translateY(0) scale(1.02);
                box-shadow: 0 5px 15px rgba(51, 204, 102, 0.4);
            }
            
            /* Улучшенные кнопки с анимациями */
            .pulse-button {
                position: relative;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .pulse-hover {
                animation: pulse-hover 1.5s infinite;
            }
            
            @keyframes pulse-hover {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .pulse-button:hover {
                transform: scale(1.05);
                box-shadow: 0 0 15px rgba(255,255,255,0.5);
            }
            
            .pulse-button:before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                opacity: 0;
            }
            
            .pulse-button:hover:before {
                animation: pulse 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .button-glow {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: radial-gradient(circle at center, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
                border-radius: 50px;
                opacity: 0;
                animation: button-glow 2s infinite alternate;
            }
            
            @keyframes button-glow {
                0% { opacity: 0; }
                100% { opacity: 1; }
            }
            
            .slide-active:before {
                animation: slide-animation 1.2s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            @keyframes slide-animation {
                0% { left: -100%; }
                100% { left: 100%; }
            }
            
            /* Обновленные стили для баннеров */
            .banner-image {
                border-radius: 16px;
                transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .banner-content {
                padding: 25px;
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .banner-title {
                margin-bottom: 10px;
                position: relative;
                display: inline-block;
            }
            
            .banner-title:after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 0;
                height: 2px;
                background-color: white;
                transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .animated-banner:hover .banner-title:after {
                width: 100%;
            }
            
            .banner-text {
                opacity: 0.9;
                margin-bottom: 20px;
                transform: translateY(5px);
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            .animated-banner:hover .banner-text {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }
    
    // Добавляем эффект изменения глубины при скролле
    function scrollParallax() {
        const banners = document.querySelectorAll('.animated-banner');
        
        window.addEventListener('scroll', () => {
            banners.forEach(banner => {
                const rect = banner.getBoundingClientRect();
                const isInView = (rect.top <= window.innerHeight && rect.bottom >= 0);
                
                if (isInView) {
                    const scrollPercentage = 1 - (rect.top + rect.height) / (window.innerHeight + rect.height);
                    const scale = 1 + (scrollPercentage * 0.05);
                    const translateY = scrollPercentage * -15;
                    
                    banner.style.transform = `translateY(${translateY}px) scale(${scale})`;
                    
                    // Обновляем тени в зависимости от позиции прокрутки
                    const shadowBlur = 10 + (scrollPercentage * 20);
                    banner.style.boxShadow = `0 ${shadowBlur}px ${shadowBlur * 2}px rgba(0,0,0,${0.1 + scrollPercentage * 0.05})`;
                }
            });
        });
    }
    
    // Инициализируем все анимации
    function initAnimations() {
        addExtraStyles();
        revealOnScroll();
        parallaxEffect();
        enhanceButtons();
        scrollParallax();
        
        // Активируем баннеры с небольшой задержкой после загрузки страницы
        setTimeout(() => {
            document.querySelectorAll('.animated-banner').forEach((banner, index) => {
                setTimeout(() => {
                    banner.classList.add('reveal-visible');
                }, index * 200);
            });
        }, 300);
    }
    
    // Запускаем все анимации
    initAnimations();
}); 