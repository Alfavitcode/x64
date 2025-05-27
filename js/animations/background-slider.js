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
        overlayOpacity: 0 // Прозрачность белого оверлея (0 = полностью прозрачный)
    };
    
    // Путь к изображению для фона (проверяем разные пути)
    const paths = [
        '../img/backgrounds/slider.png', // Относительный путь от account/
        '/img/backgrounds/slider.png', // Путь от корня сайта
        'img/backgrounds/slider.png' // Альтернативный путь
    ];
    
    // Создаем контейнер для фона, если он еще не существует
    let backgroundContainer = document.querySelector('.background-slider');
    if (!backgroundContainer) {
        backgroundContainer = document.createElement('div');
        backgroundContainer.className = 'background-slider';
        document.body.insertBefore(backgroundContainer, document.body.firstChild);
        
        // Проверяем, какой путь работает
        const img = new Image();
        let validPath = paths[0]; // По умолчанию используем первый путь
        
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
        
        // Создаем панораму из дублированного изображения для бесконечной прокрутки
        const panorama = document.createElement('div');
        panorama.className = 'background-panorama';
        
        // Пробуем разные пути к изображению
        for (let i = 0; i < paths.length; i++) {
            panorama.style.backgroundImage = `url('${paths[i]}')`;
            panorama.style.backgroundSize = '50% 100%'; // Размер одной картинки
            
            // Если последний путь, добавляем класс для CSS анимации как запасной вариант
            if (i === paths.length - 1) {
                panorama.classList.add('animate-bg');
            }
        }
        
        backgroundContainer.appendChild(panorama);
        
        // Создаем полупрозрачный оверлей (только если нужен)
        if (config.overlayOpacity > 0) {
            const overlay = document.createElement('div');
            overlay.className = 'background-overlay';
            backgroundContainer.appendChild(overlay);
        }
        
        // Запускаем анимацию бесконечной прокрутки
        animateBackground(panorama);
        
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