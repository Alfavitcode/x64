/**
 * Скрипт для управления отображением хедера при скролле
 * Хедер исчезает при скролле вниз и появляется при скролле вверх
 */
(function() {
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight;
    let isHeaderVisible = true;
    let isScrolling = false;
    
    // Добавляем класс для анимации хедера
    header.classList.add('header-scroll-transition');
    
    // Функция для обработки события скролла с оптимизацией производительности
    function handleScroll() {
        if (!isScrolling) {
            window.requestAnimationFrame(function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Если скролл больше высоты хедера, начинаем управлять видимостью
                if (scrollTop > headerHeight) {
                    // Скролл вниз - скрываем хедер
                    if (scrollTop > lastScrollTop && isHeaderVisible) {
                        header.classList.add('header-hidden');
                        isHeaderVisible = false;
                    } 
                    // Скролл вверх - показываем хедер
                    else if (scrollTop < lastScrollTop && !isHeaderVisible) {
                        header.classList.remove('header-hidden');
                        isHeaderVisible = true;
                    }
                } else {
                    // Когда мы в верхней части страницы, хедер всегда виден
                    header.classList.remove('header-hidden');
                    isHeaderVisible = true;
                }
                
                lastScrollTop = scrollTop;
                isScrolling = false;
            });
        }
        isScrolling = true;
    }
    
    // Слушаем событие скролла
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Проверяем начальное положение скролла
        const initialScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (initialScrollTop > headerHeight && initialScrollTop > lastScrollTop) {
            header.classList.add('header-hidden');
            isHeaderVisible = false;
        }
        
        // Устанавливаем lastScrollTop в начальное положение
        lastScrollTop = initialScrollTop;
    });
})(); 