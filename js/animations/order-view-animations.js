/**
 * Анимации для страницы просмотра заказа
 */

document.addEventListener('DOMContentLoaded', function() {
    // Регистрируем плагин ScrollTrigger, если он доступен
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }
    
    // Убираем скрытие контента перед анимацией
    prepareElementsForAnimation();
    
    // Анимация появления элементов страницы
    animateOrderViewAppearance();
    
    // Анимация прогресса заказа
    animateOrderProgress();
    
    // Анимация информационных карточек
    animateInfoCards();
    
    // Анимация товаров в заказе
    animateOrderItems();
    
    // Анимация итоговой суммы
    animateOrderSummary();
    
    // Добавляем интерактивный фон
    setupOrderViewBackground();
    
    // Добавляем интерактивные эффекты при прокрутке
    setupScrollAnimations();
});

/**
 * Подготовка элементов для анимации без скрытия текста
 */
function prepareElementsForAnimation() {
    // Получаем все контейнеры, которые будем анимировать
    const container = document.querySelector('.order-detail-container');
    const progressWrapper = document.querySelector('.order-progress-wrapper');
    const infoCards = document.querySelectorAll('.info-card');
    const orderItems = document.querySelectorAll('.order-item');
    const orderSummary = document.querySelector('.order-summary');
    
    // Устанавливаем начальное состояние для контейнеров с сохранением видимости контента
    if (container) {
        gsap.set(container, { 
            autoAlpha: 1, // Используем autoAlpha вместо opacity
            y: 30,
            transformOrigin: 'center top'
        });
    }
    
    if (progressWrapper) {
        gsap.set(progressWrapper, {
            autoAlpha: 1,
            y: 20
        });
        
        const progressSteps = document.querySelectorAll('.progress-step');
        gsap.set(progressSteps, {
            autoAlpha: 1,
            scale: 0.8,
            y: 10
        });
        
        const progressLine = document.querySelector('.progress-line-inner');
        if (progressLine) {
            // Сохраняем исходную ширину
            const originalWidth = progressLine.style.width;
            gsap.set(progressLine, {
                width: 0,
                autoAlpha: 1
            });
            // Запоминаем исходную ширину для последующей анимации
            progressLine.setAttribute('data-target-width', originalWidth);
        }
    }
    
    if (infoCards.length > 0) {
        gsap.set(infoCards, {
            autoAlpha: 1,
            y: 30,
            transformOrigin: 'center top'
        });
    }
    
    if (orderItems.length > 0) {
        gsap.set(orderItems, {
            autoAlpha: 1,
            y: 30,
            scale: 0.98,
            transformOrigin: 'center top'
        });
    }
    
    if (orderSummary) {
        gsap.set(orderSummary, {
            autoAlpha: 1,
            y: 30,
            transformOrigin: 'center top'
        });
    }
}

/**
 * Анимация появления элементов страницы
 */
function animateOrderViewAppearance() {
    // Основной контейнер
    const container = document.querySelector('.order-detail-container');
    const header = document.querySelector('.order-detail-header');
    
    if (!container) return;
    
    // Анимация основного контейнера без скрытия контента
    gsap.to(container, {
        y: 0,
        duration: 0.8,
        ease: 'power3.out'
    });
    
    // Анимация заголовка без скрытия контента
    if (header) {
        gsap.from(header.children, {
            y: 20,
            stagger: 0.15,
            duration: 0.6,
            delay: 0.3,
            ease: 'power2.out',
            clearProps: 'all' // Очищаем свойства после анимации
        });
    }
    
    // Анимация кнопки "Назад"
    const backButton = document.querySelector('.back-btn');
    if (backButton) {
        gsap.from(backButton, {
            x: -20,
            duration: 0.6,
            delay: 0.5,
            ease: 'power2.out',
            clearProps: 'transform' // Очищаем только transform после анимации
        });
        
        // Добавляем анимацию при наведении
        backButton.addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.05,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            gsap.to(this.querySelector('i'), {
                x: -3,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        backButton.addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            gsap.to(this.querySelector('i'), {
                x: 0,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    }
    
    // Анимация статуса заказа
    const statusBadge = document.querySelector('.order-meta .status-badge');
    if (statusBadge) {
        gsap.from(statusBadge, {
            scale: 0.8,
            duration: 0.8,
            delay: 0.6,
            ease: 'elastic.out(1, 0.5)',
            clearProps: 'transform' // Очищаем только transform после анимации
        });
    }
}

/**
 * Анимация прогресса заказа (новый компонент)
 */
function animateOrderProgress() {
    const progressWrapper = document.querySelector('.order-progress-wrapper');
    const progressSteps = document.querySelectorAll('.progress-step');
    const progressLine = document.querySelector('.progress-line-inner');
    
    if (!progressWrapper || !progressLine) return;
    
    // Анимация появления контейнера прогресса
    gsap.to(progressWrapper, {
        y: 0,
        duration: 0.8,
        delay: 0.5,
        ease: 'power3.out'
    });
    
    // Анимация шагов
    gsap.to(progressSteps, {
        scale: 1,
        y: 0,
        stagger: 0.1,
        duration: 0.6,
        delay: 0.8,
        ease: 'back.out(1.7)'
    });
    
    // Анимация линии прогресса
    const targetWidth = progressLine.getAttribute('data-target-width') || progressLine.style.width;
    gsap.to(progressLine, {
        width: targetWidth,
        duration: 1.5,
        delay: 1.3,
        ease: 'power2.inOut'
    });
    
    // Добавляем пульсацию активным шагам
    const activeStepIcons = document.querySelectorAll('.progress-step.active .step-icon');
    if (activeStepIcons.length > 0) {
        const lastActiveIcon = activeStepIcons[activeStepIcons.length - 1];
        
        gsap.to(lastActiveIcon, {
            scale: 1.1,
            boxShadow: '0 0 15px rgba(81, 101, 246, 0.5)',
            duration: 0.8,
            repeat: 1,
            yoyo: true,
            delay: 2,
            ease: 'power2.inOut'
        });
    }
}

/**
 * Анимация информационных карточек
 */
function animateInfoCards() {
    const infoCards = document.querySelectorAll('.info-card');
    
    if (infoCards.length === 0) return;
    
    // Анимация карточек без скрытия контента
    gsap.to(infoCards, {
        y: 0,
        stagger: 0.2,
        duration: 0.8,
        delay: 0.7,
        ease: 'power3.out'
    });
    
    // Анимация иконок карточек
    const cardIcons = document.querySelectorAll('.info-card-icon');
    gsap.from(cardIcons, {
        scale: 0.5,
        rotation: -10,
        stagger: 0.2,
        duration: 0.6,
        delay: 0.9,
        ease: 'back.out(1.7)',
        clearProps: 'transform'
    });
    
    // Анимация заголовков карточек
    const cardTitles = document.querySelectorAll('.info-card-title');
    gsap.from(cardTitles, {
        y: 10,
        stagger: 0.2,
        duration: 0.6,
        delay: 0.9,
        ease: 'power2.out',
        clearProps: 'transform'
    });
    
    // Анимация элементов информации
    infoCards.forEach(card => {
        const infoItems = card.querySelectorAll('.info-item');
        
        gsap.from(infoItems, {
            x: -20,
            stagger: 0.1,
            duration: 0.5,
            delay: 1.1,
            ease: 'power2.out',
            clearProps: 'transform'
        });
    });
    
    // Добавляем анимацию при наведении на карточки
    infoCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            gsap.to(this, {
                backgroundColor: '#f2f4ff',
                boxShadow: '0 5px 15px rgba(81, 101, 246, 0.1)',
                y: -5,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Анимация иконки при наведении
            const icon = this.querySelector('.info-card-icon');
            if (icon) {
                gsap.to(icon, {
                    rotation: 10,
                    scale: 1.2,
                    opacity: 0.4,
                    duration: 0.4,
                    ease: 'power2.out'
                });
            }
        });
        
        card.addEventListener('mouseleave', function() {
            gsap.to(this, {
                backgroundColor: '#f8f9fa',
                boxShadow: 'none',
                y: 0,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Возврат иконки
            const icon = this.querySelector('.info-card-icon');
            if (icon) {
                gsap.to(icon, {
                    rotation: 0,
                    scale: 1,
                    opacity: 0.2,
                    duration: 0.4,
                    ease: 'power2.out'
                });
            }
        });
    });
}

/**
 * Анимация товаров в заказе
 */
function animateOrderItems() {
    const sectionHeader = document.querySelector('.order-items-section .section-header');
    const sectionTitle = document.querySelector('.order-items-section .section-title');
    const sectionIcon = document.querySelector('.order-items-section .section-icon');
    const orderItems = document.querySelectorAll('.order-item');
    
    if (!sectionHeader || orderItems.length === 0) return;
    
    // Анимация заголовка раздела
    gsap.from(sectionHeader, {
        y: 20,
        duration: 0.6,
        delay: 1.3,
        ease: 'power2.out',
        clearProps: 'transform'
    });
    
    // Отдельная анимация для иконки
    if (sectionIcon) {
        gsap.from(sectionIcon, {
            scale: 0,
            rotation: -30,
            duration: 0.7,
            delay: 1.4,
            ease: 'back.out(1.7)',
            clearProps: 'transform'
        });
    }
    
    // Анимация товаров
    gsap.to(orderItems, {
        y: 0,
        scale: 1,
        stagger: 0.15,
        duration: 0.7,
        delay: 1.5,
        ease: 'power3.out'
    });
    
    // Добавляем анимацию при наведении на товары
    orderItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            gsap.to(this, {
                backgroundColor: '#f9faff',
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.05)',
                y: -5,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Добавляем подсветку левой полосы
            const pseudoElement = window.getComputedStyle(this, ':before');
            if (pseudoElement) {
                gsap.to(this, {
                    '--before-opacity': 1, // Используем CSS переменную
                    duration: 0.3
                });
            }
            
            // Анимация изображения
            const image = this.querySelector('.item-image');
            if (image) {
                gsap.to(image, {
                    scale: 1.05,
                    boxShadow: '0 8px 20px rgba(0, 0, 0, 0.1)',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Анимация названия товара
            const name = this.querySelector('.item-name');
            if (name) {
                gsap.to(name, {
                    color: '#5165F6',
                    x: 3,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Анимация итоговой суммы
            const totalValue = this.querySelector('.total-value');
            if (totalValue) {
                gsap.to(totalValue, {
                    scale: 1.1,
                    color: '#4a5be8',
                    textShadow: '0 0 5px rgba(81, 101, 246, 0.3)',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        item.addEventListener('mouseleave', function() {
            gsap.to(this, {
                backgroundColor: '#fff',
                boxShadow: 'none',
                y: 0,
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Сброс подсветки левой полосы
            gsap.to(this, {
                '--before-opacity': 0, // Используем CSS переменную
                duration: 0.3
            });
            
            // Возврат изображения
            const image = this.querySelector('.item-image');
            if (image) {
                gsap.to(image, {
                    scale: 1,
                    boxShadow: '0 5px 15px rgba(0, 0, 0, 0.05)',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Возврат названия товара
            const name = this.querySelector('.item-name');
            if (name) {
                gsap.to(name, {
                    color: '#333',
                    x: 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Возврат итоговой суммы
            const totalValue = this.querySelector('.total-value');
            if (totalValue) {
                gsap.to(totalValue, {
                    scale: 1,
                    color: '#5165F6',
                    textShadow: 'none',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
}

/**
 * Анимация итоговой суммы
 */
function animateOrderSummary() {
    const orderSummary = document.querySelector('.order-summary');
    const summaryItems = document.querySelectorAll('.summary-item');
    
    if (!orderSummary || summaryItems.length === 0) return;
    
    // Анимация контейнера
    gsap.to(orderSummary, {
        y: 0,
        duration: 0.8,
        delay: 1.8,
        ease: 'power3.out'
    });
    
    // Анимация элементов
    gsap.from(summaryItems, {
        x: 20,
        stagger: 0.15,
        duration: 0.6,
        delay: 2.0,
        ease: 'power2.out',
        clearProps: 'transform'
    });
    
    // Особая анимация для итоговой суммы
    const totalItem = document.querySelector('.summary-item.total');
    if (totalItem) {
        gsap.from(totalItem, {
            backgroundColor: 'rgba(81, 101, 246, 0.1)',
            duration: 1,
            delay: 2.3,
            ease: 'power2.inOut',
            clearProps: 'backgroundColor'
        });
        
        const totalValue = totalItem.querySelector('.summary-value');
        if (totalValue) {
            gsap.from(totalValue, {
                scale: 1.2,
                duration: 0.8,
                delay: 2.3,
                ease: 'elastic.out(1, 0.5)',
                clearProps: 'transform'
            });
        }
    }
    
    // Добавляем анимацию при наведении на сводку заказа
    if (orderSummary) {
        orderSummary.addEventListener('mouseenter', function() {
            gsap.to(this, {
                boxShadow: '0 8px 25px rgba(81, 101, 246, 0.1)',
                y: -5,
                duration: 0.4,
                ease: 'power2.out'
            });
            
            // Добавляем подсветку итоговой суммы
            const totalValue = this.querySelector('.summary-item.total .summary-value');
            if (totalValue) {
                gsap.to(totalValue, {
                    scale: 1.05,
                    color: '#4354e8',
                    textShadow: '0 0 5px rgba(81, 101, 246, 0.3)',
                    duration: 0.4,
                    ease: 'power2.out'
                });
            }
        });
        
        orderSummary.addEventListener('mouseleave', function() {
            gsap.to(this, {
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.03)',
                y: 0,
                duration: 0.4,
                ease: 'power2.out'
            });
            
            // Возврат итоговой суммы
            const totalValue = this.querySelector('.summary-item.total .summary-value');
            if (totalValue) {
                gsap.to(totalValue, {
                    scale: 1,
                    color: '#5165F6',
                    textShadow: 'none',
                    duration: 0.4,
                    ease: 'power2.out'
                });
            }
        });
    }
}

/**
 * Настройка интерактивного фона
 */
function setupOrderViewBackground() {
    // Создаем градиентный фон с анимацией
    const profileSection = document.querySelector('.profile-section');
    
    if (!profileSection) return;
    
    // Добавляем декоративные элементы
    const decorContainer = document.createElement('div');
    decorContainer.className = 'order-view-decorations';
    decorContainer.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        z-index: -1;
    `;
    
    // Создаем декоративные круги
    for (let i = 0; i < 5; i++) {
        const circle = document.createElement('div');
        circle.className = 'decor-circle';
        circle.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(81, 101, 246, 0.15) 0%, rgba(81, 101, 246, 0) 70%);
            opacity: 0.6;
        `;
        
        // Случайный размер и позиция
        const size = Math.random() * 300 + 100;
        circle.style.width = size + 'px';
        circle.style.height = size + 'px';
        
        circle.style.top = Math.random() * 100 + '%';
        circle.style.left = Math.random() * 100 + '%';
        
        decorContainer.appendChild(circle);
        
        // Анимация кругов
        gsap.to(circle, {
            x: Math.random() * 100 - 50,
            y: Math.random() * 100 - 50,
            scale: Math.random() * 0.5 + 0.8,
            opacity: Math.random() * 0.5 + 0.3,
            duration: Math.random() * 20 + 10,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut'
        });
    }
    
    profileSection.prepend(decorContainer);
}

/**
 * Настройка анимаций при прокрутке
 */
function setupScrollAnimations() {
    // Проверяем доступность ScrollTrigger
    if (typeof ScrollTrigger === 'undefined') return;
    
    // Добавляем эффект параллакса для информационных карточек
    const infoCards = document.querySelectorAll('.info-card');
    infoCards.forEach((card, index) => {
        const direction = index % 2 === 0 ? 1 : -1;
        
        gsap.to(card, {
            y: 10 * direction,
            scrollTrigger: {
                trigger: card,
                start: 'top bottom',
                end: 'bottom top',
                scrub: true,
                toggleActions: 'play none none reverse'
            }
        });
    });
    
    // Эффект при прокрутке для товаров
    const orderItems = document.querySelectorAll('.order-item');
    orderItems.forEach((item, index) => {
        gsap.to(item, {
            scale: 1.02,
            boxShadow: '0 8px 20px rgba(0, 0, 0, 0.08)',
            scrollTrigger: {
                trigger: item,
                start: 'top bottom-=100',
                end: 'bottom center',
                scrub: 1,
                toggleActions: 'play none none reverse'
            }
        });
    });
    
    // Эффект для сводки заказа
    const orderSummary = document.querySelector('.order-summary');
    if (orderSummary) {
        gsap.to(orderSummary, {
            y: -15,
            boxShadow: '0 15px 30px rgba(81, 101, 246, 0.1)',
            scrollTrigger: {
                trigger: orderSummary,
                start: 'top bottom-=50',
                end: 'bottom center',
                scrub: 1,
                toggleActions: 'play none none reverse'
            }
        });
    }
} 