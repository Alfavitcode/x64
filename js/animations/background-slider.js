/**
 * Background Slider Animation
 * Плавное переключение фоновых изображений с эффектом плавания
 */

document.addEventListener('DOMContentLoaded', function() {
    // Конфигурация слайдера
    const config = {
        slideDuration: 8, // Длительность показа одного слайда в секундах
        transitionDuration: 2, // Длительность перехода между слайдами в секундах
        floatAmplitude: 30, // Амплитуда плавающего эффекта в пикселях
        floatDuration: 20, // Длительность одного цикла плавания в секундах
    };
    
    // Массив с изображениями для слайдера (пути к изображениям)
    const backgroundImages = [
        '../img/backgrounds/bg-1.jpg',
        '../img/backgrounds/bg-2.jpg',
        '../img/backgrounds/bg-3.jpg',
        '../img/backgrounds/bg-4.jpg',
        '../img/backgrounds/bg-5.jpg'
    ];
    
    // Создаем контейнер для слайдера, если он еще не существует
    let sliderContainer = document.querySelector('.background-slider');
    if (!sliderContainer) {
        sliderContainer = document.createElement('div');
        sliderContainer.className = 'background-slider';
        document.body.insertBefore(sliderContainer, document.body.firstChild);
        
        // Добавляем стили для контейнера слайдера
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
            
            .background-slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-size: cover;
                background-position: center;
                opacity: 0;
                will-change: transform, opacity;
            }
            
            .background-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.85);
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
        
        // Создаем полупрозрачный оверлей
        const overlay = document.createElement('div');
        overlay.className = 'background-overlay';
        sliderContainer.appendChild(overlay);
    }
    
    // Создаем слайды
    backgroundImages.forEach((imgSrc, index) => {
        const slide = document.createElement('div');
        slide.className = 'background-slide';
        slide.style.backgroundImage = `url(${imgSrc})`;
        slide.style.zIndex = -10 - index; // Располагаем слайды в правильном порядке
        sliderContainer.appendChild(slide);
    });
    
    const slides = document.querySelectorAll('.background-slide');
    
    // Инициализация GSAP
    try {
        gsap.registerPlugin(CSSPlugin);
    } catch (e) {
        console.warn('GSAP или CSSPlugin не найден. Используем базовую анимацию.');
        
        // Простая анимация без GSAP
        slides[0].style.opacity = 1;
        let currentIndex = 0;
        
        setInterval(() => {
            const nextIndex = (currentIndex + 1) % slides.length;
            
            // Скрываем текущий слайд
            slides[currentIndex].style.opacity = 0;
            slides[currentIndex].style.transition = `opacity ${config.transitionDuration}s ease-in-out`;
            
            // Показываем следующий слайд
            slides[nextIndex].style.opacity = 1;
            slides[nextIndex].style.transition = `opacity ${config.transitionDuration}s ease-in-out`;
            
            currentIndex = nextIndex;
        }, config.slideDuration * 1000);
        
        return; // Прекращаем выполнение функции, если GSAP не доступен
    }
    
    // Функция для создания плавающего эффекта для слайда
    function createFloatingAnimation(slide) {
        // Случайные начальные значения для разнообразия движения
        const xStart = gsap.utils.random(-config.floatAmplitude/2, config.floatAmplitude/2);
        const yStart = gsap.utils.random(-config.floatAmplitude/2, config.floatAmplitude/2);
        const scale = gsap.utils.random(1.05, 1.15);
        
        // Создаем анимацию плавания
        gsap.set(slide, {
            scale: scale,
            xPercent: xStart,
            yPercent: yStart
        });
        
        // Плавное движение с использованием GSAP timeline
        const tl = gsap.timeline({repeat: -1, yoyo: true});
        
        tl.to(slide, {
            duration: config.floatDuration / 2,
            xPercent: gsap.utils.random(-config.floatAmplitude, config.floatAmplitude),
            yPercent: gsap.utils.random(-config.floatAmplitude, config.floatAmplitude),
            ease: "sine.inOut"
        })
        .to(slide, {
            duration: config.floatDuration / 2,
            xPercent: xStart,
            yPercent: yStart,
            ease: "sine.inOut"
        });
        
        return tl;
    }
    
    // Инициализация слайдера
    function initSlider() {
        // Показываем первый слайд
        gsap.set(slides[0], {opacity: 1});
        
        // Создаем плавающий эффект для каждого слайда
        slides.forEach(slide => {
            createFloatingAnimation(slide);
        });
        
        // Запускаем цикл смены слайдов
        let currentIndex = 0;
        
        function nextSlide() {
            const nextIndex = (currentIndex + 1) % slides.length;
            
            // Плавный переход между слайдами
            gsap.to(slides[currentIndex], {
                opacity: 0,
                duration: config.transitionDuration,
                ease: "power2.inOut"
            });
            
            gsap.to(slides[nextIndex], {
                opacity: 1,
                duration: config.transitionDuration,
                ease: "power2.inOut"
            });
            
            currentIndex = nextIndex;
            
            // Планируем следующую смену слайда
            setTimeout(nextSlide, config.slideDuration * 1000);
        }
        
        // Запускаем цикл с задержкой
        setTimeout(nextSlide, config.slideDuration * 1000);
    }
    
    // Запускаем слайдер
    initSlider();
}); 