/**
 * Анимации для страниц авторизации и регистрации
 */

document.addEventListener('DOMContentLoaded', function() {
    // Анимация появления карточки формы
    animateFormAppearance();
    
    // Анимация полей ввода
    setupInputAnimations();
    
    // Анимация кнопок
    setupButtonAnimations();
    
    // Анимация перехода между страницами
    setupPageTransitions();
    
    // Добавляем интерактивные частицы на фон
    setupParticlesBackground();
});

/**
 * Анимация появления формы
 */
function animateFormAppearance() {
    const card = document.querySelector('.card');
    const formElements = document.querySelectorAll('.form-group, .btn, .alert, .text-center, .d-flex');
    
    if (!card) return;
    
    // Начальное состояние
    gsap.set(card, { 
        opacity: 0, 
        y: 30,
        scale: 0.95
    });
    
    gsap.set(formElements, { 
        opacity: 0, 
        y: 20 
    });
    
    // Анимация карточки
    gsap.to(card, {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.8,
        ease: 'power3.out',
        onComplete: () => {
            // Анимация элементов формы после появления карточки
            gsap.to(formElements, {
                opacity: 1,
                y: 0,
                stagger: 0.1,
                duration: 0.5,
                ease: 'power2.out'
            });
        }
    });
    
    // Анимация кнопки "Вернуться на главную"
    const backButton = document.querySelector('.back-to-home');
    if (backButton) {
        gsap.from(backButton, {
            opacity: 0,
            x: -30,
            duration: 0.6,
            delay: 0.3,
            ease: 'power2.out'
        });
    }
}

/**
 * Настройка анимаций для полей ввода
 */
function setupInputAnimations() {
    const inputs = document.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        // Анимация при фокусе
        input.addEventListener('focus', function() {
            gsap.to(this, {
                scale: 1.02,
                boxShadow: '0 0 0 3px rgba(78, 115, 223, 0.25)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Анимация иконки
            const icon = this.parentElement.querySelector('.input-icon');
            if (icon) {
                gsap.to(icon, {
                    color: '#4e73df',
                    scale: 1.2,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
        
        // Возврат к обычному состоянию при потере фокуса
        input.addEventListener('blur', function() {
            gsap.to(this, {
                scale: 1,
                boxShadow: '0 2px 5px rgba(0, 0, 0, 0.1)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Возврат иконки
            const icon = this.parentElement.querySelector('.input-icon');
            if (icon) {
                gsap.to(icon, {
                    color: '#adb5bd',
                    scale: 1,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        });
    });
    
    // Анимация для переключателя видимости пароля
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            gsap.to(this, {
                rotation: '+=180',
                duration: 0.5,
                ease: 'power2.inOut'
            });
        });
    }
}

/**
 * Настройка анимаций для кнопок
 */
function setupButtonAnimations() {
    const buttons = document.querySelectorAll('.btn-primary, .social-btn');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.05,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        button.addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        button.addEventListener('mousedown', function() {
            gsap.to(this, {
                scale: 0.95,
                duration: 0.1,
                ease: 'power2.in'
            });
        });
        
        button.addEventListener('mouseup', function() {
            gsap.to(this, {
                scale: 1.05,
                duration: 0.2,
                ease: 'power2.out'
            });
        });
    });
}

/**
 * Настройка анимаций для переходов между страницами
 */
function setupPageTransitions() {
    // Находим все ссылки на страницы авторизации/регистрации
    const authLinks = document.querySelectorAll('a[href*="login.php"], a[href*="register.php"]');
    
    authLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const href = this.getAttribute('href');
            const currentForm = document.querySelector('.card');
            
            // Анимация исчезновения текущей формы
            gsap.to(currentForm, {
                opacity: 0,
                y: -30,
                scale: 0.9,
                duration: 0.5,
                ease: 'power3.in',
                onComplete: () => {
                    // Переход на другую страницу
                    window.location.href = href;
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
        // Настройка частиц
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
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
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
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
                    "speed": 2,
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
        console.warn('particles.js не загружен. Фон с частицами не будет отображаться.');
    }
} 