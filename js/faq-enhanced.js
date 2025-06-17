// Улучшенный JavaScript для страниц FAQ и пользовательского соглашения

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация анимаций при загрузке страницы
    initLoadAnimations();
    
    // Инициализация анимаций при скролле
    initScrollAnimations();
    
    // Инициализация аккордеона
    initAccordion();
    
    // Инициализация вкладок
    initTabs();
    
    // Инициализация кнопки "Наверх"
    initBackToTop();
    
    // Инициализация навигации по странице
    initPageNavigation();
    
    // Инициализация анимированных иконок
    initAnimatedIcons();
    
    // Добавление эффектов при наведении на карточки
    initCardHoverEffects();
    
    // Плавный скролл к якорям
    initSmoothScroll();
});

// Анимации при загрузке страницы
function initLoadAnimations() {
    const animateElements = document.querySelectorAll('.animate-on-load');
    
    // Добавляем небольшую задержку для более плавного появления элементов
    setTimeout(() => {
        animateElements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('animate');
            }, index * 150); // Каждый элемент появляется с задержкой
        });
    }, 300);
}

// Анимации при скролле
function initScrollAnimations() {
    const animateElements = document.querySelectorAll('.animate-on-scroll, .section-animate');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                observer.unobserve(entry.target); // Прекращаем наблюдение после анимации
            }
        });
    }, {
        threshold: 0.2 // Элемент анимируется, когда 20% его видно
    });
    
    animateElements.forEach(element => {
        observer.observe(element);
    });
}

// Инициализация аккордеона
function initAccordion() {
    const accordionHeaders = document.querySelectorAll('.faq-accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Получаем родительский элемент (аккордеон-айтем)
            const accordionItem = this.parentElement;
            
            // Проверяем, активен ли текущий элемент
            const isActive = accordionItem.classList.contains('active');
            
            // Если нужно, чтобы только один элемент был открыт, закрываем все
            if (!isActive) {
                const activeItems = document.querySelectorAll('.faq-accordion-item.active');
                activeItems.forEach(item => {
                    item.classList.remove('active');
                });
            }
            
            // Переключаем состояние текущего элемента
            accordionItem.classList.toggle('active');
            
            // Добавляем эффект пульсации при открытии
            if (accordionItem.classList.contains('active')) {
                accordionItem.classList.add('pulse');
                setTimeout(() => {
                    accordionItem.classList.remove('pulse');
                }, 1000);
            }
        });
    });
}

// Инициализация вкладок
function initTabs() {
    const tabLinks = document.querySelectorAll('.faq-tab-link');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Получаем ID вкладки
            const tabId = this.getAttribute('data-tab');
            
            // Удаляем активный класс у всех вкладок
            document.querySelectorAll('.faq-tab-link').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Скрываем все контенты вкладок
            document.querySelectorAll('.faq-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Активируем выбранную вкладку и контент
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

// Инициализация кнопки "Наверх"
function initBackToTop() {
    // Создаем кнопку "Наверх", если её нет на странице
    if (!document.querySelector('.back-to-top')) {
        const backToTopButton = document.createElement('div');
        backToTopButton.className = 'back-to-top';
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(backToTopButton);
    }
    
    const backToTopButton = document.querySelector('.back-to-top');
    
    // Показываем/скрываем кнопку при скролле
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    // Скролл наверх при клике
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Инициализация навигации по странице
function initPageNavigation() {
    const navLinks = document.querySelectorAll('.faq-nav-link, .faq-toc-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Плавный скролл к элементу
                window.scrollTo({
                    top: targetElement.offsetTop - 100, // Отступ сверху
                    behavior: 'smooth'
                });
                
                // Выделяем активную ссылку
                navLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                });
                this.classList.add('active');
                
                // Добавляем эффект подсветки целевого элемента
                targetElement.classList.add('highlight');
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 1500);
            }
        });
    });
    
    // Обновление активной ссылки при скролле
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id], .card[id]');
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.pageYOffset >= sectionTop - 200 && 
                window.pageYOffset < sectionTop + sectionHeight - 200) {
                currentSection = '#' + section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentSection) {
                link.classList.add('active');
            }
        });
    });
}

// Инициализация анимированных иконок
function initAnimatedIcons() {
    // Добавляем класс для анимации иконок
    const icons = document.querySelectorAll('.faq-card i, .faq-accordion-header i');
    
    icons.forEach(icon => {
        if (!icon.classList.contains('animated-icon')) {
            icon.classList.add('animated-icon');
        }
    });
}

// Добавление эффектов при наведении на карточки
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.faq-card, .legal-content .card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
}

// Плавный скролл к якорям
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Проверяем, что это не пустой якорь и не якорь для вкладок
            if (targetId !== '#' && !this.classList.contains('faq-tab-link')) {
                e.preventDefault();
                
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// Функция для создания анимированного оглавления
function createTableOfContents() {
    const tocContainer = document.querySelector('.faq-toc');
    if (!tocContainer) return;
    
    const headings = document.querySelectorAll('h2, h3');
    const tocList = document.createElement('ul');
    tocList.className = 'faq-toc-list';
    
    headings.forEach((heading, index) => {
        // Добавляем ID к заголовку, если его нет
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }
        
        const listItem = document.createElement('li');
        listItem.className = 'faq-toc-item';
        
        const link = document.createElement('a');
        link.className = 'faq-toc-link';
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        
        // Делаем отступ для h3
        if (heading.tagName === 'H3') {
            link.style.paddingLeft = '15px';
        }
        
        listItem.appendChild(link);
        tocList.appendChild(listItem);
    });
    
    tocContainer.appendChild(tocList);
}

// Функция для добавления анимированных пошаговых инструкций
function initStepsAnimation() {
    const steps = document.querySelectorAll('.faq-step');
    
    steps.forEach((step, index) => {
        step.style.transitionDelay = (index * 0.1) + 's';
    });
}

// Функция для создания анимированных счетчиков
function initCounters() {
    const counters = document.querySelectorAll('.faq-counter');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                let count = 0;
                const speed = Math.floor(1000 / target);
                
                const updateCount = () => {
                    if (count < target) {
                        count++;
                        counter.textContent = count;
                        setTimeout(updateCount, speed);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCount();
                observer.unobserve(counter);
            }
        });
    }, {
        threshold: 1
    });
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

// Функция для анимации при переходе между страницами
function initPageTransitions() {
    document.querySelectorAll('a:not([href^="#"])').forEach(link => {
        link.addEventListener('click', function(e) {
            // Проверяем, что ссылка ведет на другую страницу сайта
            const href = this.getAttribute('href');
            
            if (href && href.indexOf(window.location.origin) === 0 || href.startsWith('/')) {
                e.preventDefault();
                
                // Добавляем анимацию исчезновения
                document.body.classList.add('page-transition-out');
                
                // После завершения анимации переходим на новую страницу
                setTimeout(() => {
                    window.location = href;
                }, 300);
            }
        });
    });
    
    // Добавляем анимацию появления при загрузке страницы
    window.addEventListener('load', () => {
        document.body.classList.add('page-transition-in');
    });
}

// Функция для создания анимированных изображений
function initImageAnimations() {
    const images = document.querySelectorAll('.faq-image img');
    
    images.forEach(image => {
        image.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        image.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Функция для добавления эффекта параллакса
function initParallaxEffect() {
    const parallaxElements = document.querySelectorAll('.parallax-element');
    
    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY;
        
        parallaxElements.forEach(element => {
            const speed = element.getAttribute('data-speed') || 0.1;
            element.style.transform = `translateY(${scrollY * speed}px)`;
        });
    });
}

// Функция для анимации прогресс-баров
function initProgressBars() {
    const progressBars = document.querySelectorAll('.faq-progress-bar');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                const value = progressBar.getAttribute('data-value') || 0;
                
                progressBar.style.width = value + '%';
                observer.unobserve(progressBar);
            }
        });
    }, {
        threshold: 0.2
    });
    
    progressBars.forEach(progressBar => {
        observer.observe(progressBar);
    });
}

// Функция для создания анимированных уведомлений
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `faq-notification faq-notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Показываем уведомление
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Скрываем и удаляем уведомление
    setTimeout(() => {
        notification.classList.remove('show');
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, duration);
}

// Функция для анимированного подчеркивания текста
function initTextUnderline() {
    const underlineElements = document.querySelectorAll('.underline-animation');
    
    underlineElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.classList.add('active');
        });
        
        element.addEventListener('mouseleave', function() {
            this.classList.remove('active');
        });
    });
}

// Вызываем дополнительные функции, если они нужны
document.addEventListener('DOMContentLoaded', function() {
    // Создаем оглавление
    if (document.querySelector('.faq-toc')) {
        createTableOfContents();
    }
    
    // Инициализируем анимацию шагов
    if (document.querySelector('.faq-step')) {
        initStepsAnimation();
    }
    
    // Инициализируем счетчики
    if (document.querySelector('.faq-counter')) {
        initCounters();
    }
    
    // Инициализируем анимацию изображений
    if (document.querySelector('.faq-image')) {
        initImageAnimations();
    }
    
    // Инициализируем эффект параллакса
    if (document.querySelector('.parallax-element')) {
        initParallaxEffect();
    }
    
    // Инициализируем прогресс-бары
    if (document.querySelector('.faq-progress-bar')) {
        initProgressBars();
    }
    
    // Инициализируем анимированное подчеркивание
    if (document.querySelector('.underline-animation')) {
        initTextUnderline();
    }
    
    // Инициализируем анимацию при переходе между страницами
    initPageTransitions();
}); 