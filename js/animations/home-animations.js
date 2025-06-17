/**
 * Анимации для главной страницы
 */

document.addEventListener('DOMContentLoaded', function() {
    // Анимация элементов главной страницы
    animateHomeElements();
    
    // Анимация карточек товаров
    setupProductCardAnimations();
    
    // Убираем интерактивные частицы на фоне
    // setupParticlesBackground();
});

/**
 * Анимация элементов главной страницы
 */
function animateHomeElements() {
    // Анимация заголовков секций
    const sectionTitles = document.querySelectorAll('.section-title');
    
    sectionTitles.forEach(title => {
        // Создаем наблюдатель для появления элементов при прокрутке
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    gsap.fromTo(entry.target, 
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' }
                    );
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        
        observer.observe(title);
    });
    
    // Анимация вкладок
    const tabs = document.querySelectorAll('.tab');
    gsap.fromTo(tabs, 
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', delay: 0.3 }
    );
}

/**
 * Настройка анимаций для карточек товаров
 */
function setupProductCardAnimations() {
    const productCards = document.querySelectorAll('.product-card');
    
    // Анимация появления карточек при прокрутке
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                gsap.fromTo(entry.target, 
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }
                );
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });
    
    productCards.forEach(card => {
        observer.observe(card);
        
        // Эффект при наведении
        card.addEventListener('mouseenter', function() {
            gsap.to(this, {
                y: -10,
                boxShadow: '0 15px 30px rgba(0, 0, 0, 0.1)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Анимация изображения
            const image = this.querySelector('.product-image img');
            if (image) {
                gsap.to(image, {
                    scale: 1.05,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Анимация кнопки
            const button = this.querySelector('.btn-add-to-cart');
            if (button) {
                gsap.to(button, {
                    scale: 1.05,
                    backgroundColor: '#3a5ccc',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        card.addEventListener('mouseleave', function() {
            gsap.to(this, {
                y: 0,
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.05)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Возврат изображения
            const image = this.querySelector('.product-image img');
            if (image) {
                gsap.to(image, {
                    scale: 1,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
            
            // Возврат кнопки
            const button = this.querySelector('.btn-add-to-cart');
            if (button) {
                gsap.to(button, {
                    scale: 1,
                    backgroundColor: '#4e73df',
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
    
    // Анимация для кнопок добавления в корзину
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            gsap.to(this, {
                scale: 0.9,
                duration: 0.1,
                ease: 'power2.in',
                onComplete: () => {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.2,
                        ease: 'elastic.out(1, 0.3)'
                    });
                }
            });
        });
    });
}

/**
 * Добавление интерактивного фона с частицами
 */
function setupParticlesBackground() {
    // Создаем контейнер для частиц
    const particlesContainer = document.createElement('div');
    particlesContainer.id = 'particles-js';
    particlesContainer.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        pointer-events: none;
    `;
    document.body.prepend(particlesContainer);
    
    // Проверяем, загружена ли библиотека particles.js
    if (typeof particlesJS !== 'undefined') {
        // Настройка частиц для главной страницы (более легкая конфигурация)
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 50,
                    "density": {
                        "enable": true,
                        "value_area": 1000
                    }
                },
                "color": {
                    "value": "#4e73df"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.3,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 0.5,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 2,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#4e73df",
                    "opacity": 0.2,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 1,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 0.6
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });
    } else {
        console.error('particles.js не загружен');
    }
} 