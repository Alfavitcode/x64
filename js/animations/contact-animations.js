/**
 * Анимации для страницы контактов
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем основные анимации
    initContactPageAnimations();
    
    // Анимация для карточек
    animateContactCards();
    
    // Анимация для формы контактов
    animateContactForm();
    
    // Анимация для карты
    animateMap();
    
    // Добавляем интерактивные эффекты
    addInteractiveEffects();
});

/**
 * Инициализация основных анимаций страницы
 */
function initContactPageAnimations() {
    // Анимация для заголовка страницы
    gsap.from('.page-title', {
        y: -30,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    });
    
    // Анимация для хлебных крошек
    gsap.from('.breadcrumb-item', {
        x: -20,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6,
        ease: 'power2.out',
        delay: 0.3
    });
}

/**
 * Анимация для карточек контактов
 */
function animateContactCards() {
    // Создаем наблюдатель для появления элементов при прокрутке
    const contactCards = document.querySelectorAll('.contact-card');
    
    // Используем IntersectionObserver для анимации при прокрутке
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Получаем родительский элемент (колонку)
                const column = entry.target.closest('.col-lg-5, .col-lg-7');
                
                // Анимируем появление карточки
                gsap.fromTo(entry.target, 
                    { y: 50, opacity: 0, scale: 0.95 },
                    { 
                        y: 0, 
                        opacity: 1, 
                        scale: 1,
                        duration: 0.7, 
                        ease: 'power3.out',
                        clearProps: 'all'  // Очищаем свойства после анимации
                    }
                );
                
                // Анимируем контактные блоки с задержкой
                if (column && column.classList.contains('col-lg-5')) {
                    const contactBlocks = entry.target.querySelectorAll('.contact-block');
                    gsap.fromTo(contactBlocks, 
                        { x: -20, opacity: 0 },
                        { 
                            x: 0, 
                            opacity: 1,
                            duration: 0.5,
                            stagger: 0.15,
                            delay: 0.3,
                            ease: 'power2.out'
                        }
                    );
                    
                    // Анимируем социальные иконки с волновым эффектом
                    const socialBtns = entry.target.querySelectorAll('.social-btn');
                    gsap.fromTo(socialBtns, 
                        { y: 20, opacity: 0 },
                        { 
                            y: 0, 
                            opacity: 1,
                            duration: 0.5,
                            stagger: 0.1,
                            delay: 0.8,
                            ease: 'back.out(1.7)'
                        }
                    );
                }
                
                // Перестаем наблюдать за этим элементом
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    
    // Начинаем наблюдать за всеми карточками
    contactCards.forEach(card => {
        observer.observe(card);
    });
}

/**
 * Анимация для формы контактов
 */
function animateContactForm() {
    // Анимируем поля формы при загрузке
    const formElements = document.querySelectorAll('.form-floating, .form-check, .submit-btn');
    
    // Используем IntersectionObserver для анимации при прокрутке
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Получаем все элементы формы внутри видимой карточки
                const elements = entry.target.querySelectorAll('.form-floating, .form-check, .submit-btn');
                
                // Анимируем появление элементов формы
                gsap.fromTo(elements, 
                    { y: 20, opacity: 0 },
                    { 
                        y: 0, 
                        opacity: 1,
                        duration: 0.5,
                        stagger: 0.08,
                        delay: 0.4,
                        ease: 'power2.out'
                    }
                );
                
                // Перестаем наблюдать за этой формой
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    
    // Начинаем наблюдать за формой
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        const formCard = contactForm.closest('.contact-card');
        if (formCard) {
            observer.observe(formCard);
        }
    }
}

/**
 * Анимация для карты
 */
function animateMap() {
    // Создаем наблюдатель для появления карты при прокрутке
    const mapContainer = document.querySelector('.map-container');
    
    if (mapContainer) {
        const mapObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Получаем родительскую карточку
                    const card = entry.target.closest('.contact-card');
                    
                    // Анимируем появление карты с масштабированием
                    gsap.fromTo(entry.target, 
                        { opacity: 0, scale: 0.9 },
                        { 
                            opacity: 1, 
                            scale: 1,
                            duration: 0.8, 
                            ease: 'power3.out',
                            clearProps: 'all'
                        }
                    );
                    
                    // Анимируем волновой эффект от центра карты
                    const overlay = document.createElement('div');
                    overlay.style.position = 'absolute';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.background = 'radial-gradient(circle, rgba(77, 97, 252, 0.3) 0%, rgba(255, 255, 255, 0) 60%)';
                    overlay.style.borderRadius = '10px';
                    overlay.style.pointerEvents = 'none';
                    
                    entry.target.style.position = 'relative';
                    entry.target.appendChild(overlay);
                    
                    // Анимируем волновой эффект
                    gsap.fromTo(overlay, 
                        { opacity: 1, scale: 0 },
                        { 
                            opacity: 0, 
                            scale: 2,
                            duration: 1.5, 
                            ease: 'power2.out',
                            onComplete: () => {
                                overlay.remove();
                            }
                        }
                    );
                    
                    // Добавляем эффект наведения на карту
                    if (card) {
                        card.addEventListener('mouseenter', function() {
                            gsap.to(entry.target, {
                                boxShadow: '0 15px 30px rgba(77, 97, 252, 0.15)',
                                duration: 0.3,
                                ease: 'power2.out'
                            });
                        });
                        
                        card.addEventListener('mouseleave', function() {
                            gsap.to(entry.target, {
                                boxShadow: 'none',
                                duration: 0.3,
                                ease: 'power2.out'
                            });
                        });
                    }
                    
                    // Перестаем наблюдать за картой
                    mapObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        
        // Начинаем наблюдать за картой
        mapObserver.observe(mapContainer);
    }
}

/**
 * Добавление интерактивных эффектов для элементов страницы
 */
function addInteractiveEffects() {
    // Эффект наведения для контактных блоков
    const contactBlocks = document.querySelectorAll('.contact-block');
    contactBlocks.forEach(block => {
        block.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.contact-icon');
            if (icon) {
                gsap.to(icon, {
                    scale: 1.15,
                    backgroundColor: 'var(--primary-color)',
                    color: 'white',
                    boxShadow: '0 5px 12px rgba(77, 97, 252, 0.25)',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        block.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.contact-icon');
            if (icon) {
                gsap.to(icon, {
                    scale: 1,
                    backgroundColor: 'rgba(77, 97, 252, 0.12)',
                    color: 'var(--primary-color)',
                    boxShadow: 'none',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
    
    // Эффект наведения для социальных иконок
    const socialBtns = document.querySelectorAll('.social-btn');
    socialBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            gsap.to(this, {
                y: -5,
                boxShadow: '0 8px 15px rgba(77, 97, 252, 0.25)',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        btn.addEventListener('mouseleave', function() {
            gsap.to(this, {
                y: 0,
                boxShadow: 'none',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    });
    
    // Эффект для полей формы
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            const formFloating = this.closest('.form-floating');
            if (formFloating) {
                gsap.to(formFloating, {
                    y: -3,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        control.addEventListener('blur', function() {
            const formFloating = this.closest('.form-floating');
            if (formFloating) {
                gsap.to(formFloating, {
                    y: 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
    
    // Эффект для кнопки отправки
    const submitBtn = document.querySelector('.submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('mouseenter', function() {
            gsap.to(this, {
                y: -3,
                boxShadow: '0 10px 20px rgba(77, 97, 252, 0.3)',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        submitBtn.addEventListener('mouseleave', function() {
            gsap.to(this, {
                y: 0,
                boxShadow: '0 6px 15px rgba(77, 97, 252, 0.2)',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    }
}

// Функция для анимации успешной отправки формы
function animateFormSuccess() {
    const contactCards = document.querySelectorAll('.contact-card');
    
    gsap.to(contactCards, {
        boxShadow: '0 0 0 15px rgba(40, 167, 69, 0)',
        repeat: 1,
        duration: 0.75,
        ease: 'power2.inOut',
        yoyo: true
    });
} 