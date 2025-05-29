document.addEventListener('DOMContentLoaded', function() {
    // Добавление 3D-эффекта для отображения задних крышек телефонов
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Добавляем класс для включения 3D-эффектов
        card.classList.add('product-3d-effect');
        
        // Добавляем обработчик для эффекта наклонаа
        card.addEventListener('mousemove', function(e) {
            const cardRect = card.getBoundingClientRect();
            const cardWidth = cardRect.width;
            const cardHeight = cardRect.height;
            
            const mouseX = e.clientX - cardRect.left;
            const mouseY = e.clientY - cardRect.top;
            
            // Уменьшаем угол наклона для более мягкого эффекта
            const rotateY = ((mouseX / cardWidth) - 0.5) * 10; // Максимальный наклон 10 градусов вместо 15
            const rotateX = ((0.5 - (mouseY / cardHeight)) * 10);
            
            // Добавляем мягкое закругление для более плавного эффекта
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            card.style.transition = 'transform 0.1s cubic-bezier(0.25, 0.8, 0.25, 1)';
            
            // Добавляем эффект блика
            const glare = card.querySelector('.glare') || document.createElement('div');
            if (!card.querySelector('.glare')) {
                glare.className = 'glare';
                card.appendChild(glare);
            }
            
            // Расчет положения блика
            const glareX = (mouseX / cardWidth) * 100;
            const glareY = (mouseY / cardHeight) * 100;
            glare.style.background = `radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%)`;
            // Добавляем скругление для блика
            glare.style.borderRadius = '16px';
        });
        
        // Возвращаем карточку в исходное положение при уходе мыши
        card.addEventListener('mouseleave', function() {
            // Используем более плавный переход при возврате
            card.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1)';
            card.style.transform = '';
            const glare = card.querySelector('.glare');
            if (glare) {
                glare.style.background = '';
            }
        });
        
        // Добавляем эффект нажатия
        card.addEventListener('mousedown', function() {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale(0.98)`;
            card.style.transition = 'transform 0.1s cubic-bezier(0.25, 0.8, 0.25, 1)';
        });
        
        card.addEventListener('mouseup', function() {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg)`;
        });
    });
    
    // Добавляем стили для 3D-эффектов
    const style = document.createElement('style');
    style.textContent = `
        .product-3d-effect {
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            border-radius: 16px; /* Добавляем скругление */
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .product-3d-effect:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }
        
        .product-3d-effect .product-image {
            transform: translateZ(20px);
            border-radius: 16px 16px 0 0; /* Скругляем углы изображения */
            overflow: hidden;
        }
        
        .product-3d-effect .product-title {
            transform: translateZ(15px);
            margin-top: 15px;
            padding: 0 15px;
            font-weight: 500;
        }
        
        .product-3d-effect .product-price {
            transform: translateZ(10px);
            padding: 0 15px;
        }
        
        .product-3d-effect .btn-add-to-cart {
            transform: translateZ(5px);
            border-radius: 50px;
            width: 85%;
            margin: 15px auto;
            padding: 12px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .glare {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 10;
            border-radius: 16px; /* Скругляем края блика */
        }
        
        /* Улучшения для отображения задних крышек телефонов */
        .product-image img {
            transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            will-change: transform;
            border-radius: 16px 16px 0 0; /* Скругляем углы изображения */
        }
        
        .product-card:hover .product-image img {
            /* transform: scale(1.08) translateZ(30px); */
        }
        
        /* Добавление специальных теней для создания эффекта глубины */
        .product-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40%;
            background: linear-gradient(to top, rgba(0,0,0,0.05), rgba(0,0,0,0));
            z-index: 1;
            opacity: 0;
            transition: opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 0 0 16px 16px; /* Скругляем углы тени */
        }
        
        .product-card:hover .product-image::after {
            opacity: 1;
        }
        
        /* Добавляем эффект "подсвечивания" краев */
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid rgba(255,255,255,0);
            border-radius: 16px; /* Скругляем края свечения */
            transition: border-color 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            z-index: 2;
            pointer-events: none;
        }
        
        .product-card:hover::before {
            border-color: rgba(255,255,255,0.3);
        }
        
        /* Добавляем более мягкие тени и закругленные углы для информации о продукте */
        .product-info {
            padding: 15px;
            background: linear-gradient(to bottom, rgba(255,255,255,0.9), rgba(255,255,255,1));
            border-radius: 0 0 16px 16px;
        }
        
        /* Стили для значков */
        .badge {
            border-radius: 50px;
            padding: 5px 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            font-weight: 500;
        }
        
        /* Стили для кнопок действий */
        .product-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 5;
        }
        
        /* Скрываем кнопку избранного */
        .wishlist-btn {
            display: none !important;
        }
        
        /* Стили для кнопки быстрого просмотра */
        .action-btn {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            margin: 0 5px;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
            opacity: 1;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            background: #4285f4;
            color: white;
        }
        
        /* Медиа-запрос для мобильных устройств */
        @media (max-width: 768px) {
            .product-actions {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
            
            .action-btn {
                width: 36px;
                height: 36px;
                font-size: 14px;
                background: rgba(255, 255, 255, 0.9);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Добавление функции быстрого просмотра
    const quickviewButtons = document.querySelectorAll('.quickview-btn');
    
    // Удаляем все кнопки "Добавить в избранное" из карточек товаров
    const removeWishlistButtons = () => {
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        wishlistButtons.forEach(button => {
            button.remove();
        });
    };
    
    // Запускаем удаление сразу после загрузки страницы
    removeWishlistButtons();
    
    // Создаём MutationObserver для обработки динамически добавленных элементов
    const observer = new MutationObserver(mutations => {
        for (let mutation of mutations) {
            if (mutation.type === 'childList') {
                // Проверяем, были ли добавлены новые карточки товаров
                const newCards = document.querySelectorAll('.product-card');
                if (newCards.length > 0) {
                    removeWishlistButtons();
                }
            }
        }
    });
    
    // Настраиваем наблюдение за изменениями в DOM
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
    
    quickviewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productCard = button.closest('.product-card');
            const productId = button.getAttribute('data-product-id');
            const productImage = productCard.querySelector('.product-image img').src;
            const productTitle = productCard.querySelector('.product-title').textContent;
            const productPrice = productCard.querySelector('.current-price').textContent;
            
            // Создаем модальное окно с предварительным изображением крышки телефона
            const modal = document.createElement('div');
            modal.className = 'product-quickview-modal';
            
            // Сохраняем текущую позицию скролла перед открытием модального окна
            const savedScrollPosition = window.scrollY || window.pageYOffset;
            
            // Создаем базовую структуру модального окна
            modal.innerHTML = `
                <div class="product-quickview-content">
                    <button class="close-modal">&times;</button>
                    <div class="product-quickview-gallery">
                        <div class="product-image-container">
                            <img src="${productImage}" alt="${productTitle}" class="product-preview-image">
                            <div class="loading-overlay">
                                <div class="spinner"></div>
                                <div class="loading-text">Загрузка вариантов...</div>
                            </div>
                        </div>
                    </div>
                    <div class="product-quickview-info">
                        <h2>${productTitle}</h2>
                        <div class="product-quickview-price">${productPrice}</div>
                        <div class="product-quickview-description">
                            Высококачественная задняя крышка для вашего телефона. Надежная защита и стильный дизайн.
                        </div>
                        <div class="product-quickview-options">
                            <div class="option-group">
                                <label>Цвет:</label>
                                <div class="color-options">
                                    <div class="color-loading">Загрузка цветов...</div>
                                </div>
                            </div>
                            </div>
                        <button class="btn-add-to-cart">Добавить в корзину</button>
                        <div class="product-quickview-meta">
                            <div class="meta-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Гарантия качества</span>
                        </div>
                            <div class="meta-item">
                                <i class="fas fa-truck"></i>
                                <span>Быстрая доставка</span>
                        </div>
                            <div class="meta-item">
                                <i class="fas fa-undo"></i>
                                <span>Возврат в течение 14 дней</span>
                    </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Предотвращаем скролл страницы при открытом модальном окне
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${savedScrollPosition}px`;
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            
            // Добавляем стили для модального окна
            const modalStyle = document.createElement('style');
            modalStyle.textContent = `
                .product-quickview-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: rgba(0,0,0,0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    opacity: 0;
                    transition: opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                }
                
                .product-quickview-content {
                    background-color: white;
                    border-radius: 24px;
                    width: 90%;
                    max-width: 1000px;
                    display: flex;
                    max-height: 85vh;
                    transform: translateY(30px);
                    opacity: 0;
                    transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
                    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
                    overflow: hidden;
                }
                
                .product-quickview-gallery {
                    flex: 1;
                    padding: 30px;
                    display: flex;
                    flex-direction: column;
                    background-color: #f9f9f9;
                    border-radius: 24px 0 0 24px;
                    align-items: center;
                    justify-content: center;
                }
                
                .product-image-container {
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    position: relative;
                }
                
                .loading-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(249, 249, 249, 0.8);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    z-index: 5;
                }
                
                .spinner {
                    width: 40px;
                    height: 40px;
                    border: 4px solid rgba(0,0,0,0.1);
                    border-radius: 50%;
                    border-top-color: #4285f4;
                    animation: spin 1s linear infinite;
                    margin-bottom: 10px;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .loading-text {
                    font-size: 14px;
                    color: #333;
                }
                
                .color-loading {
                    font-size: 14px;
                    color: #666;
                    margin: 10px 0;
                }
                
                .product-preview-image {
                    max-width: 100%;
                    max-height: 500px;
                    object-fit: contain;
                    border-radius: 16px;
                    transition: transform 0.3s ease, opacity 0.3s ease;
                }
                
                .product-preview-image:hover {
                    transform: scale(1.05);
                }
                
                .product-quickview-info {
                    flex: 1;
                    padding: 40px;
                    display: flex;
                    flex-direction: column;
                    overflow-y: auto;
                }
                
                .product-quickview-info h2 {
                    font-size: 28px;
                    font-weight: 600;
                    margin: 0 0 15px 0;
                    color: #333;
                }
                
                .product-quickview-price {
                    font-size: 24px;
                    font-weight: 700;
                    color: #ff5252;
                    margin-bottom: 20px;
                }
                
                .product-quickview-description {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #666;
                    margin-bottom: 25px;
                    padding-bottom: 25px;
                    border-bottom: 1px solid #eee;
                }
                
                .product-quickview-options {
                    margin-bottom: 30px;
                }
                
                .option-group {
                    margin-bottom: 20px;
                }
                
                .option-group label {
                    display: block;
                    font-size: 16px;
                    font-weight: 500;
                    margin-bottom: 10px;
                    color: #333;
                }
                
                .color-options {
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                }
                
                .color-option {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    border: 2px solid transparent;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    position: relative;
                }
                
                .color-option.active {
                    border-color: #333;
                    transform: scale(1.1);
                }
                
                .color-option.active:after {
                    content: '';
                    position: absolute;
                    top: -6px;
                    left: -6px;
                    right: -6px;
                    bottom: -6px;
                    border: 2px solid #f0f0f0;
                    border-radius: 50%;
                    pointer-events: none;
                }
                
                .color-name-tooltip {
                    position: absolute;
                    top: -30px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(0,0,0,0.7);
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    opacity: 0;
                    transition: opacity 0.2s ease;
                    pointer-events: none;
                    white-space: nowrap;
                }
                
                .color-option:hover .color-name-tooltip {
                    opacity: 1;
                }
                
                .btn-add-to-cart {
                    background-color: #ff5252;
                    color: white;
                    border: none;
                    border-radius: 12px;
                    padding: 16px 24px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    margin-bottom: 30px;
                    box-shadow: 0 4px 12px rgba(255, 82, 82, 0.2);
                    width: 100%;
                }
                
                .btn-add-to-cart:hover {
                    background-color: #ff3838;
                    transform: translateY(-2px);
                    box-shadow: 0 6px 16px rgba(255, 82, 82, 0.25);
                }
                
                .btn-add-to-cart:active {
                    transform: translateY(0);
                }
                
                .product-quickview-meta {
                    margin-top: auto;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }
                
                .meta-item {
                    display: flex;
                    align-items: center;
                    margin-bottom: 12px;
                    color: #666;
                    font-size: 14px;
                }
                
                .meta-item i {
                    margin-right: 10px;
                    color: #4285f4;
                    font-size: 18px;
                }
                
                .close-modal {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background-color: white;
                    border: none;
                    font-size: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    z-index: 10;
                    transition: all 0.2s ease;
                }
                
                .close-modal:hover {
                    background-color: #f5f5f5;
                    transform: rotate(90deg);
                }
                
                /* Адаптивность для мобильных устройств */
                @media (max-width: 992px) {
                    .product-quickview-content {
                        flex-direction: column;
                        max-height: 90vh;
                        overflow-y: auto;
                        width: 95%; /* Немного увеличиваем ширину */
                    }
                    
                    .product-quickview-gallery {
                        border-radius: 24px 24px 0 0;
                        padding: 20px;
                    }
                    
                    .product-preview-image {
                        max-height: 300px;
                    }
                    
                    .product-quickview-info {
                        padding: 30px 20px;
                    }

                    /* Улучшаем стили для кнопки "Добавить в корзину" на мобильных */
                    .btn-add-to-cart {
                        padding: 14px 20px;
                        margin-top: 10px;
                        margin-bottom: 20px;
                        position: relative;
                        z-index: 5;
                        width: 100%;
                        font-size: 16px;
                        border-radius: 10px;
                    }

                    /* Улучшаем стили для закрытия модального окна */
                    .close-modal {
                        top: 10px;
                        right: 10px;
                        width: 36px;
                        height: 36px;
                        font-size: 20px;
                    }
                }

                /* Стили для очень маленьких экранов */
                @media (max-width: 576px) {
                    .product-quickview-content {
                        width: 98%;
                        border-radius: 16px;
                    }
                    
                    .product-quickview-gallery {
                        padding: 15px;
                        border-radius: 16px 16px 0 0;
                    }
                    
                    .product-preview-image {
                        max-height: 250px;
                    }
                    
                    .product-quickview-info {
                        padding: 20px 15px;
                    }
                    
                    .product-quickview-info h2 {
                        font-size: 22px;
                        margin-bottom: 10px;
                    }
                    
                    .product-quickview-price {
                        font-size: 20px;
                        margin-bottom: 15px;
                    }
                    
                    .product-quickview-description {
                        font-size: 14px;
                        margin-bottom: 20px;
                        padding-bottom: 20px;
                    }
                    
                    .btn-add-to-cart {
                        padding: 12px 16px;
                        font-size: 16px;
                        margin-bottom: 15px;
                    }
                }
            `;
            document.head.appendChild(modalStyle);
            
            // Анимация появления модального окна
            setTimeout(() => {
                modal.style.opacity = '1';
                const content = modal.querySelector('.product-quickview-content');
                content.style.transform = 'translateY(0)';
                content.style.opacity = '1';
            }, 10);
            
            // Обработчики для модального окна
            const closeButton = modal.querySelector('.close-modal');
            closeButton.addEventListener('click', function() {
                closeModal();
            });
            
            // Закрытие по клику вне контента
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            
            // Функция для закрытия модального окна
            function closeModal() {
                modal.style.opacity = '0';
                modal.querySelector('.product-quickview-content').style.transform = 'translateY(30px)';
                modal.querySelector('.product-quickview-content').style.opacity = '0';
                
                // Сохраняем значение скролла из style.top для восстановления
                const scrollPosition = Math.abs(parseInt(document.body.style.top || '0'));
                
                // Восстанавливаем скролл страницы при закрытии модального окна
                setTimeout(() => {
                    // Сначала убираем все стили, которые блокируют скролл
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    document.body.style.height = '';
                    
                    // Затем восстанавливаем позицию скролла
                    if (scrollPosition) {
                        window.scrollTo(0, scrollPosition);
                    } else {
                        // Запасной вариант - используем сохраненное значение
                        window.scrollTo(0, savedScrollPosition);
                    }
                }, 50);
                
                setTimeout(() => {
                    modal.remove();
                    modalStyle.remove();
                }, 500);
            }
            
            // Загрузка информации о цветах продукта
            if (productId) {
                // Элементы, которые нужно обновить после загрузки данных
                const productPreviewImage = modal.querySelector('.product-preview-image');
                const loadingOverlay = modal.querySelector('.loading-overlay');
                const colorOptionsContainer = modal.querySelector('.color-options');
                const productTitle = modal.querySelector('.product-quickview-info h2');
                const productPrice = modal.querySelector('.product-quickview-price');
                const productDescription = modal.querySelector('.product-quickview-description');
                
                // Отправляем AJAX-запрос для получения информации о цветах продукта
                fetch('/ajax/product_colors.php?product_id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.colors && data.colors.length > 0) {
                            // Очищаем контейнер цветов
                            colorOptionsContainer.innerHTML = '';
                            
                            let defaultColorData = null;
                            let productSku = null;
                            
                            // Добавляем цвета
                            data.colors.forEach((color, index) => {
                                const colorOption = document.createElement('button');
                                colorOption.className = 'color-option' + (color.is_default == 1 ? ' active' : '');
                                colorOption.style.backgroundColor = color.color_code;
                                colorOption.setAttribute('data-color', color.color_code);
                                colorOption.setAttribute('data-color-name', color.color_name);
                                colorOption.setAttribute('data-image', color.image_path);
                                colorOption.setAttribute('data-description', color.description);
                                // Не устанавливаем цену, она берется из product
                                colorOption.setAttribute('data-sku', color.sku);
                                
                                // Сохраняем SKU для группировки вариантов
                                if (!productSku && color.sku) {
                                    productSku = color.sku;
                                }
                                
                                // Добавляем всплывающую подсказку с названием цвета
                                const tooltip = document.createElement('span');
                                tooltip.className = 'color-name-tooltip';
                                tooltip.textContent = color.color_name;
                                colorOption.appendChild(tooltip);
                                
                                colorOptionsContainer.appendChild(colorOption);
                                
                                // Если это цвет по умолчанию, сохраняем его данные
                                if (color.is_default == 1) {
                                    defaultColorData = color;
                                }
                            });
                            
                            // Устанавливаем данные цвета по умолчанию
                            if (defaultColorData) {
                                productPreviewImage.src = defaultColorData.image_path;
                                productTitle.textContent = data.product.name + ' - ' + defaultColorData.color_name;
                                // Если у цвета нет описания, используем описание из основного продукта
                                productDescription.textContent = defaultColorData.description || data.product.description;
                                productPrice.textContent = data.product.price_formatted; // Используем цену из таблицы product
                            }
                            
                            // Обработчик изменения цвета
                            const colorOptions = modal.querySelectorAll('.color-option');
                            
                            colorOptions.forEach(option => {
                                option.addEventListener('click', function() {
                                    // Убираем класс active со всех кнопок
                                    colorOptions.forEach(btn => btn.classList.remove('active'));
                                    
                                    // Добавляем класс active к выбранной кнопке
                                    this.classList.add('active');
                                    
                                    // Получаем данные о выбранном цвете
                                    const newImageSrc = this.getAttribute('data-image');
                                    const colorName = this.getAttribute('data-color-name');
                                    const description = this.getAttribute('data-description');
                                    const price = parseFloat(this.getAttribute('data-price'));
                                    
                                    // Применяем изменения ко всем элементам
                                    if (newImageSrc) {
                                        productPreviewImage.style.opacity = '0';
                                        
                                        setTimeout(() => {
                                            productPreviewImage.src = newImageSrc;
                                            productPreviewImage.style.opacity = '1';
                                        }, 300);
                                    }
                                    
                                    // Обновляем заголовок
                                    productTitle.textContent = data.product.name + ' - ' + colorName;
                                    
                                    // Обновляем описание с плавной анимацией
                                    productDescription.style.opacity = '0';
                                    setTimeout(() => {
                                        // Если у цвета нет описания, используем описание из основного продукта
                                        productDescription.textContent = description || data.product.description;
                                        productDescription.style.opacity = '1';
                                    }, 300);
                                    
                                    // Не обновляем цену, она должна браться из таблицы product
                                    
                                    // Обновляем активный вариант в сетке всех вариантов, если она открыта
                                    const variantCards = modal.querySelectorAll('.product-variant-card');
                                    if (variantCards.length > 0) {
                                        const colorCode = this.getAttribute('data-color');
                                        
                                        variantCards.forEach(card => {
                                            card.classList.remove('active');
                                            if (card.getAttribute('data-color') === colorCode) {
                                                card.classList.add('active');
                                            }
                                        });
                                    }
                                });
                            });
                            
                            // Удаляем кнопку "Смотреть все варианты", как и запрашивал пользователь
                            
                            // Обработчик кнопки "Добавить в корзину"
                            const addToCartButton = modal.querySelector('.btn-add-to-cart');
                            addToCartButton.addEventListener('click', function() {
                                // Получаем выбранный цвет
                                const selectedColor = modal.querySelector('.color-option.active');
                                
                                if (!selectedColor) {
                                    alert('Пожалуйста, выберите цвет товара!');
                                    return;
                                }
                                
                                const colorName = selectedColor.getAttribute('data-color-name');
                                const colorCode = selectedColor.getAttribute('data-color');
                                const sku = selectedColor.getAttribute('data-sku');
                                const price = data.product.price; // Используем цену из таблицы product
                                
                                // Здесь можно добавить код для добавления товара в корзину
                                alert(`Товар "${data.product.name} - ${colorName}" добавлен в корзину!`);
                                
                                // Закрываем модальное окно
                                closeModal();
                            });
                        } else {
                            colorOptionsContainer.innerHTML = '<div class="color-loading">Нет доступных вариантов цветов</div>';
                        }
                        
                        // Скрываем индикатор загрузки
                        loadingOverlay.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Ошибка при загрузке цветов:', error);
                        colorOptionsContainer.innerHTML = '<div class="color-loading">Ошибка при загрузке вариантов цветов</div>';
                        loadingOverlay.style.display = 'none';
                    });
            } else {
                // Если ID продукта не найден, используем стандартные цвета
                const colorOptionsContainer = modal.querySelector('.color-options');
                const loadingOverlay = modal.querySelector('.loading-overlay');
                
                colorOptionsContainer.innerHTML = `
                    <button class="color-option active" data-color="#ff5252" data-color-name="Красный" data-image="/img/products/8 красный.PNG" style="background-color: #ff5252;">
                        <span class="color-name-tooltip">Красный</span>
                    </button>
                    <button class="color-option" data-color="#4285f4" data-color-name="Синий" data-image="/img/products/8 белый.PNG" style="background-color: #4285f4;">
                        <span class="color-name-tooltip">Синий</span>
                    </button>
                    <button class="color-option" data-color="#4caf50" data-color-name="Зеленый" data-image="/img/products/8 розовое золото.PNG" style="background-color: #4caf50;">
                        <span class="color-name-tooltip">Зеленый</span>
                    </button>
                    <button class="color-option" data-color="#ffeb3b" data-color-name="Желтый" data-image="/img/products/14 red.jpg" style="background-color: #ffeb3b;">
                        <span class="color-name-tooltip">Желтый</span>
                    </button>
                `;
                
                // Скрываем индикатор загрузки
                loadingOverlay.style.display = 'none';
                
                // Обработчики для стандартных цветов
                const colorOptions = modal.querySelectorAll('.color-option');
                const productPreviewImage = modal.querySelector('.product-preview-image');
                
                colorOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        // Убираем класс active со всех кнопок
                        colorOptions.forEach(btn => btn.classList.remove('active'));
                        
                        // Добавляем класс active к выбранной кнопке
                        this.classList.add('active');
                        
                        // Меняем изображение товара
                        const newImageSrc = this.getAttribute('data-image');
                        if (newImageSrc) {
                            productPreviewImage.style.opacity = '0';
                            
                            setTimeout(() => {
                                productPreviewImage.src = newImageSrc;
                                productPreviewImage.style.opacity = '1';
                            }, 300);
                        }
                    });
                });
                
                // Обработчик кнопки "Добавить в корзину"
                const addToCartButton = modal.querySelector('.btn-add-to-cart');
                addToCartButton.addEventListener('click', function() {
                    // Получаем выбранный цвет
                    const selectedColor = modal.querySelector('.color-option.active');
                    
                    if (!selectedColor) {
                        alert('Пожалуйста, выберите цвет товара!');
                        return;
                    }
                    
                    const colorName = selectedColor.getAttribute('data-color-name');
                    
                    // Здесь можно добавить код для добавления товара в корзину
                    alert(`Товар "${productTitle}" (цвет: ${colorName}) добавлен в корзину!`);
                    
                    // Закрываем модальное окно
                    closeModal();
                });
            }
        });
    });
});

// Создаем функцию для работы с вариантами товаров
function loadProductVariants(sku, callback) {
    // Отправляем AJAX-запрос для получения всех вариантов товара с указанным артикулом
    fetch('/ajax/product_variants.php?sku=' + sku)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.variants && data.variants.length > 0) {
                if (typeof callback === 'function') {
                    callback(data);
                }
                } else {
                console.error('Ошибка при загрузке вариантов товара:', data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка при выполнении запроса:', error);
        });
} 