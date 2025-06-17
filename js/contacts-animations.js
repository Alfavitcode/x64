/**
 * Анимации для страницы контактов
 */

document.addEventListener('DOMContentLoaded', function() {
    // Функция для проверки, виден ли элемент в области видимости
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Функция для анимации элементов при прокрутке
    function animateOnScroll() {
        // Выбираем все элементы для анимации
        const elements = document.querySelectorAll('.contact-info-card, .contact-form-container');
        const contactItems = document.querySelectorAll('.contact-item');
        const formFloating = document.querySelectorAll('.form-floating');
        const socialIcons = document.querySelector('.social-icons');
        
        // Анимируем основные блоки
        elements.forEach(element => {
            if (isElementInViewport(element) && !element.classList.contains('animated')) {
                element.classList.add('animated');
            }
        });
        
        // Анимируем контактные данные с задержкой
        contactItems.forEach((item, index) => {
            if (isElementInViewport(item) && !item.classList.contains('animated')) {
                setTimeout(() => {
                    item.classList.add('animated');
                }, index * 100);
            }
        });
        
        // Анимируем поля формы с задержкой
        formFloating.forEach((item, index) => {
            if (isElementInViewport(item) && !item.classList.contains('animated')) {
                setTimeout(() => {
                    item.classList.add('animated');
                }, index * 100 + 200);
            }
        });
        
        // Анимируем блок социальных сетей
        if (socialIcons && isElementInViewport(socialIcons) && !socialIcons.classList.contains('animated')) {
            socialIcons.classList.add('animated');
        }
    }
    
    // Запускаем анимацию при загрузке страницы и при скролле
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);
    
    // Анимация для социальных иконок
    const socialIcons = document.querySelectorAll('.social-icon');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.classList.add('animated-icon');
        });
        
        icon.addEventListener('mouseleave', function() {
            setTimeout(() => {
                this.classList.remove('animated-icon');
            }, 300);
        });
    });
    
    // Эффект при наведении на контактную информацию
    const contactItems = document.querySelectorAll('.contact-item');
    contactItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.contact-icon i');
            if (icon) {
                icon.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    icon.style.transform = '';
                }, 300);
            }
        });
    });
    
    // Анимация для кнопки отправки формы
    const submitButton = document.querySelector('.contact-form button[type="submit"]');
    if (submitButton) {
        // Эффекты при наведении
        submitButton.addEventListener('mouseenter', function() {
            this.classList.add('hover-effect');
            this.style.transform = 'translateY(-3px)';
        });
        
        submitButton.addEventListener('mouseleave', function() {
            this.classList.remove('hover-effect');
            this.style.transform = '';
        });
        
        // Эффект при клике
        submitButton.addEventListener('click', function() {
            // Визуальная обратная связь при клике
            const ripple = document.createElement('span');
            ripple.classList.add('btn-ripple');
            this.appendChild(ripple);
            
            // Анимация эффекта нажатия
            const buttonRect = this.getBoundingClientRect();
            const size = Math.max(buttonRect.width, buttonRect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            
            // Удаляем эффект после завершения анимации
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }
    
    // Улучшенная анимация для плавающих меток
    const formControls = document.querySelectorAll('.form-floating input, .form-floating textarea, .form-floating select');
    formControls.forEach(control => {
        // Проверяем начальное состояние при загрузке
        if (control.value) {
            control.parentElement.classList.add('focused');
        }
        
        // Обработчики событий фокуса
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Обработчик изменения значения
        control.addEventListener('input', function() {
            if (this.value) {
                this.parentElement.classList.add('focused');
            } else {
                this.parentElement.classList.remove('focused');
            }
        });
    });
    
    // Анимация для кнопки отправки формы при отправке
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                
                // Восстанавливаем состояние кнопки, если форма не отправилась
                setTimeout(() => {
                    if (!submitBtn.classList.contains('btn-success')) {
                        submitBtn.classList.remove('btn-loading');
                    }
                }, 5000);
            }
        });
    }
}); 