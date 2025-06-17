/**
 * Скрипт для отладки фильтров в каталоге
 */

// Функция для отслеживания параметров URL и отображения их в консоли
function debugFilterParams() {
    const urlParams = new URLSearchParams(window.location.search);
    console.group('Текущие параметры фильтрации:');
    
    // Выводим все параметры из URL
    for (const [key, value] of urlParams.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    // Специальная проверка для чекбоксов
    const typeValues = urlParams.getAll('type[]');
    if (typeValues.length > 0) {
        console.log('Выбранные типы товаров:', typeValues);
    }
    
    console.groupEnd();
    
    // Отображение блока отладки отключено
    // Оставляем только отладку в консоли
}

// Проверка состояния чекбоксов и соответствия параметрам URL
function checkFilterConsistency() {
    const urlParams = new URLSearchParams(window.location.search);
    const typeValues = urlParams.getAll('type[]');
    
    // Проверяем соответствие чекбоксов и параметров URL
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    checkboxes.forEach(checkbox => {
        const isCheckedInUrl = typeValues.includes(checkbox.value);
        if (checkbox.checked !== isCheckedInUrl) {
            console.warn(`Несоответствие: чекбокс ${checkbox.id} (${checkbox.value}) имеет состояние ${checkbox.checked}, но в URL его значение ${isCheckedInUrl}`);
        }
    });
    
    // Проверяем соответствие полей цены
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    
    if (minPriceInput) {
        const urlMinPrice = urlParams.get('min_price');
        if ((minPriceInput.value && !urlMinPrice) || (urlMinPrice && minPriceInput.value !== urlMinPrice)) {
            console.warn(`Несоответствие: минимальная цена в поле ${minPriceInput.value}, в URL ${urlMinPrice}`);
        }
    }
    
    if (maxPriceInput) {
        const urlMaxPrice = urlParams.get('max_price');
        if ((maxPriceInput.value && !urlMaxPrice) || (urlMaxPrice && maxPriceInput.value !== urlMaxPrice)) {
            console.warn(`Несоответствие: максимальная цена в поле ${maxPriceInput.value}, в URL ${urlMaxPrice}`);
        }
    }
}

// Запускаем отладку при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    debugFilterParams();
    checkFilterConsistency();
    
    // Добавляем обработчики для отслеживания изменений
    const applyButton = document.getElementById('apply-filters');
    if (applyButton) {
        applyButton.addEventListener('click', function() {
            setTimeout(function() {
                debugFilterParams();
                checkFilterConsistency();
            }, 500);
        });
    }
});

// Обновляем информацию при изменении URL
window.addEventListener('popstate', function() {
    debugFilterParams();
    checkFilterConsistency();
}); 