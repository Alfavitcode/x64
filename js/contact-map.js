/**
 * Интерактивная карта для страницы контактов
 */

document.addEventListener('DOMContentLoaded', function() {
    // Анимация для маркера на карте
    const mapPin = document.querySelector('.map-pin');
    const mapAddress = document.querySelector('.map-address');
    
    if (mapPin && mapAddress) {
        // Инициализация
        mapAddress.style.opacity = '0';
        mapAddress.style.transform = 'translateY(10px)';
        
        // Анимация пульсации маркера
        mapPin.style.animation = 'pulse 2s infinite';
        
        // Обработчик клика по маркеру
        mapPin.addEventListener('click', function(e) {
            e.stopPropagation(); // Предотвращаем всплытие события
            
            // Показываем адрес
            mapAddress.style.opacity = '1';
            mapAddress.style.transform = 'translateY(0)';
            mapAddress.style.transition = 'all 0.3s ease';
            
            // Перезапускаем анимацию пульсации
            mapPin.style.animation = 'none';
            setTimeout(function() {
                mapPin.style.animation = 'pulse 2s infinite';
            }, 10);
        });
        
        // Обработчик клика по карте
        document.querySelector('.contact-map').addEventListener('click', function() {
            // Скрываем адрес
            mapAddress.style.opacity = '0';
            mapAddress.style.transform = 'translateY(10px)';
        });
        
        // Эффект при наведении на маркер
        mapPin.addEventListener('mouseenter', function() {
            this.style.transform = 'rotate(-45deg) scale(1.1)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        mapPin.addEventListener('mouseleave', function() {
            this.style.transform = 'rotate(-45deg) scale(1)';
        });
    }
    
    // Интерактивный эффект масштабирования карты
    const contactMap = document.querySelector('.contact-map');
    const mapImage = document.querySelector('.contact-map-image');
    
    if (contactMap && mapImage) {
        contactMap.addEventListener('mouseenter', function() {
            mapImage.style.transform = 'scale(1.03)';
            mapImage.style.transition = 'transform 0.5s ease';
        });
        
        contactMap.addEventListener('mouseleave', function() {
            mapImage.style.transform = 'scale(1)';
        });
    }
}); 