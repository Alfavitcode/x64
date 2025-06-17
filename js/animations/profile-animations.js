/**
 * Анимации для страницы профиля
 */

document.addEventListener('DOMContentLoaded', function() {
    // Анимация появления элементов профиля
    animateProfileAppearance();
    
    // Анимация карточек действий
    setupActionCardsAnimations();
    
    // Анимация статистики
    animateStatistics();
    
    // Анимация информации профиля
    animateProfileInfo();
    
    // Анимация таблицы заказов
    animateOrdersTable();
    
    // Добавляем интерактивный фон
    setupProfileBackground();
});

/**
 * Анимация появления элементов профиля
 */
function animateProfileAppearance() {
    // Основная карточка профиля
    const mainCard = document.querySelector('.profile-main-card');
    const profileHeader = document.querySelector('.profile-header');
    const profileStats = document.querySelector('.profile-stats-wrapper');
    const profileBody = document.querySelector('.profile-body');
    
    if (!mainCard) return;
    
    // Начальное состояние
    gsap.set(mainCard, { 
        opacity: 0, 
        y: 30
    });
    
    // Анимация основной карточки
    gsap.to(mainCard, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power3.out'
    });
    
    // Анимация заголовка
    gsap.from(profileHeader.children, {
        opacity: 0,
        y: 20,
        stagger: 0.15,
        duration: 0.6,
        delay: 0.3,
        ease: 'power2.out'
    });
    
    // Анимация статистики
    gsap.from(profileStats, {
        opacity: 0,
        scale: 0.9,
        duration: 0.8,
        delay: 0.6,
        ease: 'elastic.out(1, 0.5)'
    });
}

/**
 * Анимация карточек действий
 */
function setupActionCardsAnimations() {
    const actionCards = document.querySelectorAll('.profile-body .card');
    
    if (actionCards.length === 0) return;
    
    // Начальное состояние
    gsap.set(actionCards, { 
        opacity: 0, 
        y: 30,
        scale: 0.95
    });
    
    // Анимация появления карточек
    gsap.to(actionCards, {
        opacity: 1,
        y: 0,
        scale: 1,
        stagger: 0.2,
        duration: 0.7,
        delay: 0.8,
        ease: 'power3.out'
    });
    
    // Интерактивные анимации при наведении
    actionCards.forEach(card => {
        const icon = card.querySelector('.rounded-circle');
        const title = card.querySelector('.card-title');
        const arrow = card.querySelector('.fa-arrow-right');
        
        // Создаем временную шкалу для каждой карточки
        const tl = gsap.timeline({ paused: true });
        
        tl.to(card, {
            y: -10,
            boxShadow: '0 15px 30px rgba(0, 0, 0, 0.1)',
            duration: 0.4,
            ease: 'power2.out'
        }, 0);
        
        tl.to(icon, {
            scale: 1.1,
            rotation: 5,
            duration: 0.4,
            ease: 'power2.out'
        }, 0);
        
        tl.to(title, {
            scale: 1.05,
            color: '#4e73df',
            duration: 0.3,
            ease: 'power2.out'
        }, 0);
        
        if (arrow) {
            tl.to(arrow, {
                x: 5,
                opacity: 1,
                duration: 0.3,
                ease: 'power2.out'
            }, 0);
        }
        
        // Привязываем анимацию к событиям мыши
        card.addEventListener('mouseenter', () => tl.play());
        card.addEventListener('mouseleave', () => tl.reverse());
        card.addEventListener('mousemove', (e) => {
            // Эффект 3D-наклона
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const tiltX = (y - centerY) / 10;
            const tiltY = (centerX - x) / 10;
            
            gsap.to(card, {
                rotationX: tiltX,
                rotationY: tiltY,
                duration: 0.5,
                ease: 'power2.out'
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                rotationX: 0,
                rotationY: 0,
                duration: 0.5,
                ease: 'power2.out'
            });
        });
    });
}

/**
 * Анимация статистики
 */
function animateStatistics() {
    const statValue = document.querySelector('.stat-value');
    
    if (!statValue) return;
    
    // Анимация числа
    const finalValue = parseInt(statValue.textContent);
    
    gsap.fromTo(statValue, 
        { textContent: 0 }, 
        {
            textContent: finalValue,
            duration: 2,
            delay: 1,
            ease: 'power2.out',
            snap: { textContent: 1 },
            onUpdate: function() {
                statValue.textContent = Math.round(this.targets()[0].textContent);
            }
        }
    );
    
    // Анимация пульсации
    gsap.to('.stat-item', {
        scale: 1.05,
        duration: 0.8,
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut'
    });
}

/**
 * Анимация информации профиля
 */
function animateProfileInfo() {
    const infoCard = document.querySelector('.profile-info-card');
    const infoRows = document.querySelectorAll('.profile-info-row');
    
    if (!infoCard || infoRows.length === 0) return;
    
    // Начальное состояние
    gsap.set(infoCard, { 
        opacity: 0, 
        y: 30
    });
    
    gsap.set(infoRows, { 
        opacity: 0, 
        x: -20
    });
    
    // Анимация карточки
    gsap.to(infoCard, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        delay: 1.2,
        ease: 'power3.out'
    });
    
    // Анимация строк информации
    gsap.to(infoRows, {
        opacity: 1,
        x: 0,
        stagger: 0.1,
        duration: 0.5,
        delay: 1.5,
        ease: 'power2.out'
    });
    
    // Анимация заголовка при наведении
    const infoTitle = document.querySelector('.profile-info-title');
    if (infoTitle) {
        infoTitle.addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.05,
                color: '#4e73df',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        infoTitle.addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                color: '#333',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    }
}

/**
 * Анимация таблицы заказов
 */
function animateOrdersTable() {
    const ordersCard = document.querySelector('.profile-info-card.mt-4');
    const tableRows = document.querySelectorAll('.orders-table tbody tr');
    const tableContainer = document.querySelector('.orders-table-container');
    
    if (!ordersCard || tableRows.length === 0) return;
    
    // Начальное состояние
    gsap.set(ordersCard, { 
        opacity: 0, 
        y: 30
    });
    
    gsap.set(tableRows, { 
        opacity: 0, 
        y: 20
    });
    
    // Анимация карточки
    gsap.to(ordersCard, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        delay: 1.8,
        ease: 'power3.out'
    });
    
    // Анимация строк таблицы
    gsap.to(tableRows, {
        opacity: 1,
        y: 0,
        stagger: 0.15,
        duration: 0.5,
        delay: 2.1,
        ease: 'power2.out'
    });
    
    // Предотвращаем скролл таблицы при наведении
    if (tableContainer) {
        // Запрещаем скролл
        tableContainer.style.overflow = 'hidden';
        
        // Предотвращаем скролл при событиях колеса мыши
        tableContainer.addEventListener('wheel', function(e) {
            e.preventDefault();
        }, { passive: false });
        
        // Предотвращаем скролл при касании (для мобильных устройств)
        tableContainer.addEventListener('touchmove', function(e) {
            e.preventDefault();
        }, { passive: false });
    }
    
    // Интерактивная анимация строк при наведении
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            gsap.to(this, {
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Анимация статуса
            const statusBadge = this.querySelector('.status-badge');
            if (statusBadge) {
                gsap.to(statusBadge, {
                    scale: 1.1,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        row.addEventListener('mouseleave', function() {
            gsap.to(this, {
                backgroundColor: 'transparent',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Возврат статуса
            const statusBadge = this.querySelector('.status-badge');
            if (statusBadge) {
                gsap.to(statusBadge, {
                    scale: 1,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
}

/**
 * Настройка интерактивного фона
 */
function setupProfileBackground() {
    // Создаем градиентный фон с анимацией
    const profileSection = document.querySelector('.profile-section');
    
    if (!profileSection) return;
    
    // Добавляем декоративные элементы
    const decorContainer = document.createElement('div');
    decorContainer.className = 'profile-decorations';
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
    for (let i = 0; i < 6; i++) {
        const circle = document.createElement('div');
        circle.className = 'decor-circle';
        circle.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(81, 101, 246, 0.2) 0%, rgba(81, 101, 246, 0) 70%);
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