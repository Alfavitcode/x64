    </main>
    
    <footer class="footer pt-5">
        <div class="footer-pattern-overlay"></div>
        <div class="container position-relative">
            <div class="row gy-4 mb-5 text-center">
                <div class="col-lg-4 col-md-6 mx-auto">
                    <div class="footer__logo mb-4 d-flex justify-content-center">
                        <a href="/" class="logo">x64</a>
                    </div>
                    <p class="footer__description text-muted mb-4">
                        Интернет-магазин качественных товаров для вашего комфорта и удовольствия.
                        Доставка по всей России и странам СНГ.
                    </p>
                    <div class="footer__social d-flex gap-3 justify-content-center">
                        <a href="#" class="social-link btn btn-outline-primary rounded-circle"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link btn btn-outline-danger rounded-circle"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link btn btn-outline-primary rounded-circle"><i class="fab fa-vk"></i></a>
                        <a href="#" class="social-link btn btn-outline-info rounded-circle"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mx-auto">
                    <h3 class="footer__title h5 mb-4 position-relative text-center">Покупателям</h3>
                    <ul class="footer__menu list-unstyled">
                        <li class="mb-3 text-center"><a href="/faq/how-to-order.php" class="text-decoration-none text-secondary">Как сделать заказ</a></li>
                        <li class="mb-3 text-center"><a href="/faq/delivery.php" class="text-decoration-none text-secondary">Доставка</a></li>
                        <li class="mb-3 text-center"><a href="/faq/payment.php" class="text-decoration-none text-secondary">Оплата</a></li>
                        <li class="mb-3 text-center"><a href="/faq/return.php" class="text-decoration-none text-secondary">Возврат</a></li>
                        <li class="mb-3 text-center"><a href="/faq/questions.php" class="text-decoration-none text-secondary">Вопросы и ответы</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6 mx-auto">
                    <h3 class="footer__title h5 mb-4 position-relative text-center">Контакты</h3>
                    <ul class="footer__contacts list-unstyled">
                        <li class="mb-3 d-flex align-items-center text-secondary justify-content-center">
                            <i class="fas fa-map-marker-alt me-3 text-primary"></i> г. Москва, ул. Примерная, д. 123
                        </li>
                        <li class="mb-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-phone-alt me-3 text-primary"></i> 
                            <a href="tel:+74951234567" class="text-decoration-none text-secondary">+7 (495) 123-45-67</a>
                        </li>
                        <li class="mb-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-envelope me-3 text-primary"></i> 
                            <a href="mailto:info@x64.ru" class="text-decoration-none text-secondary">info@x64.ru</a>
                        </li>
                    </ul>
                    <div class="footer__payments d-flex flex-wrap gap-3 mt-4 justify-content-center">
                        <i class="fab fa-cc-visa fa-2x text-secondary"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-secondary"></i>
                        <i class="fab fa-cc-paypal fa-2x text-secondary"></i>
                        <i class="fab fa-apple-pay fa-2x text-secondary"></i>
                        <i class="fab fa-google-pay fa-2x text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer__bottom py-4 border-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12 text-center mb-3">
                        <div class="footer__copyright text-muted">
                            © 2025 x64. Все права защищены.
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        <div class="footer__links">
                            <a href="/legal/privacy.php" class="text-decoration-none text-secondary me-4">Политика конфиденциальности</a>
                            <a href="/legal/terms.php" class="text-decoration-none text-secondary">Пользовательское соглашение</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <button class="back-to-top btn btn-primary rounded-circle position-fixed" style="bottom: 20px; right: 20px; width: 50px; height: 50px; display: none;">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/db.js"></script>
    <script src="/js/animations/animations.js"></script>
    <script src="/js/phone-case-effects.js"></script>
    <!-- Скрипт для работы с корзиной -->
    <script src="/js/cart.js"></script>
    <!-- Скрипт для скрытия/показа хедера при скролле -->
    <script src="/js/header-scroll.js"></script>
    
    <!-- Скрипт для кнопки "Вернуться наверх" -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.querySelector('.back-to-top');
            
            // Показываем/скрываем кнопку при скролле
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });
            
            // Прокручиваем страницу наверх при клике на кнопку
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
    
    <!-- Отладочный скрипт для проверки элементов корзины -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== ОТЛАДКА КОРЗИНЫ ===');
            const cartCountElements = document.querySelectorAll('.cart-count');
            console.log('Элементы .cart-count найдены:', cartCountElements.length);
            
            cartCountElements.forEach((element, index) => {
                console.log(`Элемент #${index}:`, element.textContent, element);
            });
            
            // Проверяем, загружен ли объект Cart
            if (window.Cart) {
                console.log('Объект Cart загружен:', window.Cart);
                console.log('Пробуем принудительно обновить счетчик...');
                setTimeout(function() {
                    window.Cart.getCount();
                }, 1000);
            } else {
                console.error('Объект Cart не найден в глобальной области видимости!');
            }
            
            // Проверяем, есть ли доступ к AJAX
            console.log('Проверка доступа к AJAX...');
            fetch('/ajax/get_cart_count.php')
                .then(response => response.text())
                .then(data => {
                    console.log('Ответ от get_cart_count.php:', data);
                    try {
                        const jsonData = JSON.parse(data);
                        console.log('Распарсенные данные:', jsonData);
                    } catch (e) {
                        console.error('Ошибка парсинга JSON:', e);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при запросе к серверу:', error);
                });
        });
    </script>
</body>
</html> 