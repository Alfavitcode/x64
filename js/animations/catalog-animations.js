/**
 * Анимации для страницы каталога
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем анимации
    initCatalogAnimations();
    
    // Анимация для карточек товаров
    animateProductCards();
    
    // Анимация для фильтров
    animateFilters();
    
    // Анимация для пагинации
    animatePagination();
    
    // Добавляем интерактивные эффекты
    addInteractiveEffects();
});

/**
 * Инициализация основных анимаций каталога
 */
function initCatalogAnimations() {
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
    
    // Анимация для сайдбара с фильтрами - меняем анимацию, чтобы сайдбар не исчезал
    gsap.from('.catalog-sidebar .sidebar-widget', {
        x: -20,
        opacity: 0.9,
        stagger: 0.2,
        duration: 0.8,
        ease: 'power2.out',
        delay: 0.5,
        onComplete: function() {
            // Восстанавливаем стили после анимации
            document.querySelectorAll('.card-header, .category-header, .filter-header').forEach(header => {
                header.style.background = '#2345c2';
                header.style.backgroundImage = 'linear-gradient(135deg, #2c4cc5 0%, #1c35a3 100%)';
                header.style.backgroundColor = '#2345c2';
                header.style.color = 'white';
            });
            
            // Восстанавливаем отображение подкатегорий для активной категории
            const currentUrl = new URL(window.location.href);
            const categoryParam = currentUrl.searchParams.get('category');
            
            if (categoryParam) {
                document.querySelectorAll('.category-item').forEach(item => {
                    const link = item.querySelector('.d-flex > a');
                    if (link && link.href.includes(`category=${categoryParam}`)) {
                        const menu = item.querySelector('.subcategories-menu');
                        if (menu) {
                            menu.classList.add('show');
                            menu.style.display = 'block';
                            menu.style.height = 'auto';
                            menu.style.opacity = '1';
                        }
                    }
                });
            }
        }
    });
    
    // Анимация для заголовка каталога
    gsap.from('.catalog-header', {
        y: -20,
        opacity: 0,
        duration: 0.7,
        ease: 'power2.out',
        delay: 0.7
    });
}

/**
 * Анимация для карточек товаров
 */
function animateProductCards() {
    // Создаем наблюдатель для появления элементов при прокрутке
    const productCards = document.querySelectorAll('.product-card');
    
    // Используем IntersectionObserver для анимации при прокрутке
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Получаем родительский элемент (колонку)
                const column = entry.target.closest('.col-lg-4, .col-md-6, .col-sm-6');
                
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
                
                // Анимируем бейджи на карточке
                const badges = entry.target.querySelectorAll('.badge');
                if (badges.length > 0) {
                    gsap.fromTo(badges, 
                        { scale: 0, opacity: 0 },
                        { 
                            scale: 1, 
                            opacity: 1, 
                            duration: 0.5, 
                            stagger: 0.1,
                            delay: 0.3,
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
    productCards.forEach(card => {
        observer.observe(card);
    });
    
    // Добавляем эффекты при наведении на карточки
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            gsap.to(this, {
                y: -10,
                boxShadow: '0 15px 30px rgba(0, 0, 0, 0.15)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Анимация изображения при наведении
            const image = this.querySelector('.product-image img');
            if (image) {
                gsap.to(image, {
                    scale: 1.08,
                    duration: 0.5,
                    ease: 'power2.out'
                });
            }
            
            // Анимация кнопки при наведении
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
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.08)',
                duration: 0.3,
                ease: 'power2.out'
            });
            
            // Возврат изображения в исходное состояние
            const image = this.querySelector('.product-image img');
            if (image) {
                gsap.to(image, {
                    scale: 1,
                    duration: 0.5,
                    ease: 'power2.out'
                });
            }
            
            // Возврат кнопки в исходное состояние
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
}

/**
 * Анимация для фильтров и категорий
 */
function animateFilters() {
    // Получаем параметры URL для определения текущей категории
    const currentUrl = new URL(window.location.href);
    const categoryParam = currentUrl.searchParams.get('category');
    
    // Анимация для категорий при клике
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        const toggle = item.querySelector('.subcategory-toggle');
        const menu = item.querySelector('.subcategories-menu');
        const link = item.querySelector('.d-flex > a');
        
        // Определяем, является ли эта категория активной
        const isActiveCategory = categoryParam && link && link.href.includes(`category=${categoryParam}`);
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const icon = this.querySelector('i');
                const isOpen = icon.classList.contains('fa-chevron-up');
                
                // Анимация иконки
                gsap.to(icon, {
                    rotation: isOpen ? 0 : 180,
                    duration: 0.4,
                    ease: 'power2.inOut'
                });
                
                // Анимация меню подкатегорий
                if (isOpen) {
                    // Не закрываем меню, если это активная категория
                    if (!isActiveCategory) {
                        gsap.to(menu, {
                            height: 0,
                            opacity: 0,
                            duration: 0.4,
                            ease: 'power2.inOut',
                            onComplete: () => {
                                menu.style.display = 'none';
                                icon.classList.remove('fa-chevron-up');
                                icon.classList.add('fa-chevron-down');
                            }
                        });
                    }
                } else {
                    menu.style.display = 'block';
                    menu.classList.add('show'); // Добавляем класс show
                    menu.style.height = 'auto';
                    const height = menu.offsetHeight;
                    menu.style.height = '0px';
                    
                    gsap.to(menu, {
                        height: height,
                        opacity: 1,
                        duration: 0.4,
                        ease: 'power2.inOut',
                        onComplete: () => {
                            menu.style.height = 'auto';
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        }
                    });
                }
            });
        }
        
        // Если это активная категория, убедимся, что меню открыто
        if (isActiveCategory && menu) {
            menu.classList.add('show');
            menu.style.display = 'block';
            menu.style.height = 'auto';
            menu.style.opacity = '1';
            
            const icon = toggle ? toggle.querySelector('i') : null;
            if (icon) {
                icon.className = 'fas fa-chevron-up';
            }
        }
    });
    
    // Анимация для фильтров при изменении
    const filterForm = document.getElementById('ajax-filter-form');
    const applyButton = document.getElementById('apply-filters');
    const resetButton = document.getElementById('reset-filters');
    
    if (applyButton) {
        applyButton.addEventListener('click', function() {
            // Анимация кнопки при клике
            gsap.to(this, {
                scale: 0.95,
                duration: 0.1,
                ease: 'power2.in',
                onComplete: () => {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.2,
                        ease: 'power2.out'
                    });
                }
            });
        });
    }
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Анимация кнопки при клике
            gsap.to(this, {
                scale: 0.95,
                duration: 0.1,
                ease: 'power2.in',
                onComplete: () => {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.2,
                        ease: 'power2.out'
                    });
                }
            });
        });
    }
    
    // Анимация для чекбоксов
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                gsap.fromTo(this.closest('.form-check'),
                    { backgroundColor: 'rgba(78, 115, 223, 0.1)' },
                    { 
                        backgroundColor: 'transparent',
                        duration: 0.5,
                        ease: 'power1.out'
                    }
                );
            }
        });
    });
}

/**
 * Анимация для пагинации
 */
function animatePagination() {
    const paginationItems = document.querySelectorAll('.page-item');
    
    gsap.from(paginationItems, {
        y: 20,
        opacity: 0,
        stagger: 0.05,
        duration: 0.5,
        ease: 'power2.out',
        delay: 1
    });
    
    // Анимация при наведении на элементы пагинации
    paginationItems.forEach(item => {
        if (!item.classList.contains('active') && !item.classList.contains('disabled')) {
            const link = item.querySelector('.page-link');
            
            if (link) {
                link.addEventListener('mouseenter', function() {
                    gsap.to(this, {
                        backgroundColor: '#4e73df',
                        color: '#fff',
                        scale: 1.05,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
                
                link.addEventListener('mouseleave', function() {
                    gsap.to(this, {
                        backgroundColor: '#fff',
                        color: '#4e73df',
                        scale: 1,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
            }
        }
    });
}

/**
 * Добавление интерактивных эффектов
 */
function addInteractiveEffects() {
    // Анимация для селекта сортировки
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            gsap.fromTo(this,
                { borderColor: '#4e73df' },
                { 
                    borderColor: '#ced4da',
                    duration: 1,
                    ease: 'power2.out'
                }
            );
        });
    }
    
    // Анимация для кнопок добавления в корзину
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Анимация кнопки
            gsap.to(this, {
                scale: 0.9,
                duration: 0.1,
                ease: 'power2.in',
                onComplete: () => {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.3,
                        ease: 'elastic.out(1.2, 0.4)'
                    });
                }
            });
            
            // Анимация иконки корзины
            const icon = this.querySelector('i');
            if (icon) {
                gsap.to(icon, {
                    rotation: 360,
                    duration: 0.5,
                    ease: 'power2.out'
                });
            }
            
            // Создаем эффект "добавлено в корзину"
            createAddToCartEffect(this);
        });
    });
    
    // Анимация для поля поиска
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            gsap.to(this, {
                boxShadow: '0 0 0 0.25rem rgba(78, 115, 223, 0.25)',
                borderColor: '#4e73df',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        searchInput.addEventListener('blur', function() {
            gsap.to(this, {
                boxShadow: 'none',
                borderColor: '#ced4da',
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    }
}

/**
 * Создает эффект добавления товара в корзину
 */
function createAddToCartEffect(button) {
    // Создаем элемент для анимации
    const productCard = button.closest('.product-card');
    if (!productCard) return;
    
    const productImage = productCard.querySelector('.product-image img');
    if (!productImage) return;
    
    // Создаем клон изображения для анимации
    const clone = productImage.cloneNode();
    const rect = productImage.getBoundingClientRect();
    
    // Стилизуем клон
    clone.style.position = 'fixed';
    clone.style.left = rect.left + 'px';
    clone.style.top = rect.top + 'px';
    clone.style.width = rect.width + 'px';
    clone.style.height = rect.height + 'px';
    clone.style.objectFit = 'contain';
    clone.style.zIndex = '9999';
    clone.style.borderRadius = '50%';
    clone.style.pointerEvents = 'none';
    
    // Добавляем клон в body
    document.body.appendChild(clone);
    
    // Находим иконку корзины в хедере
    const cartIcon = document.querySelector('.header-cart-icon');
    if (!cartIcon) {
        document.body.removeChild(clone);
        return;
    }
    
    const cartRect = cartIcon.getBoundingClientRect();
    
    // Анимируем перемещение клона к иконке корзины
    gsap.to(clone, {
        left: cartRect.left + cartRect.width / 2,
        top: cartRect.top + cartRect.height / 2,
        width: 20,
        height: 20,
        opacity: 0.7,
        duration: 0.8,
        ease: 'power3.inOut',
        onComplete: () => {
            // Удаляем клон после завершения анимации
            document.body.removeChild(clone);
            
            // Анимируем иконку корзины
            gsap.fromTo(cartIcon, 
                { scale: 1.4, color: '#4e73df' },
                { scale: 1, color: '', duration: 0.5, ease: 'elastic.out(1.2, 0.4)' }
            );
        }
    });
} 