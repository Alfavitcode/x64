/**
 * Background Slider Animation
 * Бесконечная плавная прокрутка фоновых изображений
 */

document.addEventListener('DOMContentLoaded', function() {
    // Конфигурация анимации
    const config = {
        scrollSpeed: 120, // Скорость прокрутки (секунд на полный цикл) - увеличена для более медленного движения
        direction: 'right', // Направление прокрутки: 'left' или 'right'
        transitionDuration: 2, // Длительность перехода между слайдами в секундах
        overlayOpacity: 0.85 // Прозрачность белого оверлея
    };
    
    // Путь к изображению для фона
    const backgroundImage = '../img/backgrounds/slider.png';
    
    // Создаем контейнер для фона, если он еще не существует
    let backgroundContainer = document.querySelector('.background-slider');
    if (!backgroundContainer) {
        backgroundContainer = document.createElement('div');
        backgroundContainer.className = 'background-slider';
        document.body.insertBefore(backgroundContainer, document.body.firstChild);
        
        // Добавляем стили для контейнера
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
                background-image: url('${backgroundImage}');
                background-size: 50% 100%; /* Размер одной картинки */
                background-repeat: repeat-x;
                will-change: transform;
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
                .background-overlay {
                    background: rgba(255, 255, 255, 0.9);
                }
            }
        `;
        document.head.appendChild(style);
        
        // Создаем панораму из дублированного изображения для бесконечной прокрутки
        const panorama = document.createElement('div');
        panorama.className = 'background-panorama';
        backgroundContainer.appendChild(panorama);
        
        // Создаем полупрозрачный оверлей
        const overlay = document.createElement('div');
        overlay.className = 'background-overlay';
        backgroundContainer.appendChild(overlay);
        
        // Запускаем анимацию бесконечной прокрутки
        animateBackground(panorama);
    }
    
    // Функция анимации бесконечной прокрутки
    function animateBackground(element) {
        const direction = config.direction === 'left' ? 1 : -1; // 1 для влево, -1 для вправо
        
        try {
            // Используем GSAP, если доступен
            if (typeof gsap !== 'undefined') {
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
                // Запасной вариант с CSS анимацией, если GSAP не доступен
                const styleAnimation = document.createElement('style');
                styleAnimation.textContent = `
                    @keyframes slideBackground {
                        0% { transform: translateX(0); }
                        100% { transform: translateX(${direction * 50}%); }
                    }
                    
                    .background-panorama {
                        animation: slideBackground ${config.scrollSpeed}s linear infinite;
                    }
                `;
                document.head.appendChild(styleAnimation);
            }
        } catch (e) {
            console.warn('Ошибка при создании анимации:', e);
            
            // Запасной вариант с CSS анимацией при ошибке
            const styleAnimation = document.createElement('style');
            styleAnimation.textContent = `
                @keyframes slideBackground {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(${direction * 50}%); }
                }
                
                .background-panorama {
                    animation: slideBackground ${config.scrollSpeed}s linear infinite;
                }
            `;
            document.head.appendChild(styleAnimation);
        }
    }
}); 