/**
 * Background Slider Animation
 * Бесконечная плавная прокрутка фоновых изображений
 */

document.addEventListener('DOMContentLoaded', function() {
    // Конфигурация анимации
    const config = {
        scrollSpeed: 120, // Скорость прокрутки (секунд на полный цикл)
        direction: 'right', // Направление прокрутки: 'left' или 'right'
        transitionDuration: 2, // Длительность перехода между слайдами в секундах
        blurAmount: '5px', // Размытие фона (средняя размытость)
        darkenAmount: 0.3, // Затемнение фона (0 - без затемнения, 1 - полностью темный)
        overlayOpacity: 0 // Прозрачность белого оверлея (0 = полностью прозрачный)
    };
    
    // Определяем устройство
    const isMobile = window.innerWidth <= 768;
    
    // Определяем путь к изображению в зависимости от текущей страницы и устройства
    let baseDir = 'img/backgrounds/';
    let imageName = isMobile ? 'mobile.PNG' : 'slider.png';
    
    // Проверяем, находимся ли мы в директории account
    if (window.location.pathname.includes('/account/')) {
        baseDir = '../img/backgrounds/';
    }
    
    const imagePath = baseDir + imageName;
    console.log('Using image path:', imagePath);
    console.log('Is mobile device:', isMobile);
    
    // Создаем контейнер для фона, если он еще не существует
    let backgroundContainer = document.querySelector('.background-slider');
    if (!backgroundContainer) {
        backgroundContainer = document.createElement('div');
        backgroundContainer.className = 'background-slider';
        document.body.insertBefore(backgroundContainer, document.body.firstChild);
        
        // Создаем стили для контейнера
        const style = document.createElement('style');
        style.textContent = `
            .background-slider {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                overflow: hidden;
            }
            
            .background-panorama {
                position: absolute;
                top: 0;
                left: 0;
                width: 200%; /* Двойная ширина для бесконечной прокрутки */
                height: 100%;
                background-repeat: repeat-x;
                will-change: transform;
                filter: blur(${config.blurAmount}); /* Добавляем размытие */
            }
            
            .background-darken {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, ${config.darkenAmount}); /* Затемнение */
                z-index: 0;
            }
            
            .background-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, ${config.overlayOpacity});
                z-index: 1;
            }
            
            /* Адаптация для мобильных устройств */
            @media (max-width: 768px) {
                .background-panorama {
                    filter: blur(calc(${config.blurAmount} * 1.5)); /* Увеличиваем размытие на мобильных */
                }
            }
            
            /* CSS анимация для случая, если GSAP не работает */
            @keyframes slideBackground {
                0% { transform: translateX(0); }
                100% { transform: translateX(${config.direction === 'left' ? 50 : -50}%); }
            }
            
            .animate-bg {
                animation: slideBackground ${config.scrollSpeed}s linear infinite;
            }
        `;
        document.head.appendChild(style);
        
        // Создаем панораму для бесконечной прокрутки
        const panorama = document.createElement('div');
        panorama.className = 'background-panorama animate-bg';
        
        // Устанавливаем фоновое изображение напрямую через style
        panorama.style.backgroundImage = `url('${imagePath}')`;
        panorama.style.backgroundSize = '50% 100%';
        
        // Добавляем обработчик ошибки загрузки изображения
        const handleImageError = function() {
            console.error('Failed to load background image:', imagePath);
            panorama.style.backgroundImage = 'linear-gradient(45deg, #4e73df, #6f42c1, #4e73df)';
            panorama.style.backgroundSize = '200% 100%';
        };
        
        // Проверяем загрузку изображения
        const testImg = new Image();
        testImg.onerror = handleImageError;
        testImg.src = imagePath;
        
        backgroundContainer.appendChild(panorama);
        
        // Создаем затемнение
        const darken = document.createElement('div');
        darken.className = 'background-darken';
        backgroundContainer.appendChild(darken);
        
        // Создаем полупрозрачный оверлей (только если нужен)
        if (config.overlayOpacity > 0) {
            const overlay = document.createElement('div');
            overlay.className = 'background-overlay';
            backgroundContainer.appendChild(overlay);
        }
        
        // Запускаем анимацию бесконечной прокрутки
        animateBackground(panorama);
        
        // Обработчик изменения размера окна для адаптации к мобильным устройствам
        window.addEventListener('resize', function() {
            const newIsMobile = window.innerWidth <= 768;
            if (newIsMobile !== isMobile) {
                // Перезагрузить страницу для применения новой версии изображения
                window.location.reload();
            }
        });
        
        // Выводим отладочную информацию в консоль
        console.log('Background slider initialized');
        console.log('Using GSAP:', typeof gsap !== 'undefined');
    }
    
    // Функция анимации бесконечной прокрутки
    function animateBackground(element) {
        const direction = config.direction === 'left' ? 1 : -1; // 1 для влево, -1 для вправо
        
        try {
            // Используем GSAP, если доступен
            if (typeof gsap !== 'undefined') {
                console.log('Animating with GSAP');
                // Создаем бесконечную анимацию
                gsap.to(element, {
                    x: direction * '50%', // Смещение на половину ширины (одно изображение)
                    duration: config.scrollSpeed,
                    ease: "none", // Линейное движение
                    repeat: -1, // Бесконечное повторение
                    onRepeat: function() {
                        // Сбрасываем позицию для создания иллюзии бесконечности
                        gsap.set(element, { x: 0 });
                    }
                });
            } else {
                console.log('GSAP not found, using CSS animation');
                // CSS анимация уже применена через класс animate-bg
            }
        } catch (e) {
            console.warn('Animation error:', e);
            // CSS анимация уже применена через класс animate-bg
        }
    }
}); 