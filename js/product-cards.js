/**
 * Функция для инициализации карточек товаров
 * Применяет общие стили и интерактивные эффекты к карточкам товаров
 */
function initProductCards() {
    console.log('Инициализация карточек товаров...');
    
    // Проверяем, загружены ли общие стили для карточек
    const styleLoaded = document.querySelector('link[href="/css/components/product-card.css"]');
    if (!styleLoaded) {
        console.warn('Общие стили для карточек товаров не загружены. Добавляем их динамически.');
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/css/components/product-card.css';
        document.head.appendChild(link);
    }
    
    // Применяем дополнительные интерактивные эффекты к карточкам товаров
    document.querySelectorAll('.product-card').forEach(card => {
        // Добавляем обработчики для анимации при наведении
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.04)';
        });
        
        // Добавляем анимацию для изображения при наведении
        const image = card.querySelector('.product-image');
        if (image) {
            const img = image.querySelector('img');
            if (img) {
                image.addEventListener('mouseenter', function() {
                    if (img) img.style.transform = 'scale(1.05)';
                });
                
                image.addEventListener('mouseleave', function() {
                    if (img) img.style.transform = 'scale(1)';
                });
            }
        }
    });
}

/**
 * Функция для проверки загрузки jQuery с повторными попытками
 * Используется для инициализации фильтров товаров
 */
function checkJQuery(attempts) {
    attempts = attempts || 0;
    
    if (typeof jQuery !== 'undefined') {
        console.log('jQuery успешно загружен, версия:', jQuery.fn.jquery);
        initFilters();
    } else {
        if (attempts < 5) {
            console.log('jQuery еще не загружен, попытка ' + (attempts + 1) + ' из 5');
            setTimeout(function() {
                checkJQuery(attempts + 1);
            }, 500);
        } else {
            console.error('jQuery не загружен после 5 попыток! Подключаем его вручную...');
            var script = document.createElement('script');
            script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
            script.onload = initFilters;
            document.head.appendChild(script);
        }
    }
}

/**
 * Функция инициализации фильтров товаров
 */
function initFilters() {
    console.log('Инициализация фильтров товаров...');
    
    // Находим все кнопки фильтров
    var $filterButtons = $('.tabs .tab');
    console.log('Найдено кнопок фильтров:', $filterButtons.length);
    
    if ($filterButtons.length === 0) {
        console.error('Кнопки фильтров не найдены! Пробуем другой селектор...');
        $filterButtons = $('.tab');
        console.log('Найдено кнопок с селектором .tab:', $filterButtons.length);
    }
    
    // Добавляем обработчик клика для каждой кнопки фильтра
    $filterButtons.on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var selectedCategory = $this.attr('data-category');
        
        console.log('Клик по фильтру:', $this.text(), 'с категорией:', selectedCategory);
        
        // Удаляем активный класс у всех кнопок и добавляем текущей
        $filterButtons.removeClass('active');
        $this.addClass('active');
        
        // Находим все карточки товаров
        var $productItems = $('.product-item');
        console.log('Найдено элементов товаров:', $productItems.length);
        
        if ($productItems.length === 0) {
            console.error('Элементы товаров не найдены! Пробуем другой селектор...');
            $productItems = $('.col-lg-3');
            console.log('Найдено элементов с селектором .col-lg-3:', $productItems.length);
        }
        
        // Фильтруем товары
        $productItems.each(function() {
            var $item = $(this);
            var $card = $item.find('.product-card');
            
            if ($card.length === 0) {
                console.error('Карточка товара не найдена внутри:', $item);
                return;
            }
            
            var cardCategories = $card.attr('data-category') || '';
            console.log('Категории карточки:', cardCategories);
            
            // Проверяем соответствие выбранной категории
            if (selectedCategory === 'all' || cardCategories.indexOf(selectedCategory) !== -1) {
                $item.fadeIn(300);
                console.log('Показываем товар');
            } else {
                $item.fadeOut(300);
                console.log('Скрываем товар');
            }
        });
    });
    
    // Проверяем, есть ли активная кнопка по умолчанию
    var $activeButton = $('.tabs .tab.active');
    if ($activeButton.length === 0) {
        console.log('Активная кнопка не найдена по селектору .tabs .tab.active, пробуем .tab.active');
        $activeButton = $('.tab.active');
    }
    
    if ($activeButton.length) {
        console.log('Найдена активная кнопка по умолчанию:', $activeButton.text());
        // Имитируем клик по активной кнопке для инициализации фильтра
        $activeButton.trigger('click');
    } else {
        console.log('Активная кнопка по умолчанию не найдена, активируем первую кнопку');
        // Если нет активной кнопки, активируем первую
        $filterButtons.first().trigger('click');
    }
}

// Запускаем инициализацию карточек товаров при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем карточки товаров
    initProductCards();
    
    // Проверяем наличие фильтров на странице
    if (document.querySelector('.tabs') || document.querySelector('.tab')) {
        checkJQuery();
    }
}); 