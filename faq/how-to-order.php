<?php
// Задаем заголовок страницы
$page_title = 'Как сделать заказ';

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/faq.css">';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<main class="main-content">
    <div class="container faq-container">
        <div class="faq-header animate-on-load">
            <h1>Как сделать заказ</h1>
            <p>Простая пошаговая инструкция для быстрого и удобного оформления заказа в нашем интернет-магазине</p>
        </div>
        
        <div class="faq-card animate-on-load">
            <div class="faq-card__header">
                <h2>Оформление заказа в интернет-магазине X64</h2>
            </div>
            <div class="faq-card__body">
                <p>Мы постарались сделать процесс заказа максимально простым и понятным. Следуйте этой инструкции, чтобы быстро оформить заказ и получить нужные вам товары.</p>
                
                <div class="faq-card__image animate-on-scroll slide-in-left-scroll">
                    <img src="/faq/images/how-to-order-main.jpg" alt="Оформление заказа">
                </div>
                
                <h3 class="mt-4 mb-3">Пошаговая инструкция</h3>
                
                <ol class="faq-steps">
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Выбор товара</h4>
                        <p>Воспользуйтесь каталогом или поиском на сайте, чтобы найти интересующий вас товар. На странице товара вы можете ознакомиться с его описанием, техническими характеристиками, отзывами других покупателей и фотографиями.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/product-selection.jpg" alt="Выбор товара">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Добавление в корзину</h4>
                        <p>После выбора товара нажмите кнопку «Добавить в корзину». Вы можете продолжить покупки и добавить в корзину другие товары или сразу перейти к оформлению заказа, нажав на значок корзины в верхнем правом углу сайта.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/add-to-cart.jpg" alt="Добавление в корзину">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Проверка корзины</h4>
                        <p>В корзине вы можете проверить список выбранных товаров, изменить их количество или удалить товары, которые вы решили не покупать. Также здесь отображается общая стоимость заказа.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/cart-review.jpg" alt="Проверка корзины">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Оформление заказа</h4>
                        <p>Для перехода к оформлению заказа нажмите кнопку «Оформить заказ». Если вы уже зарегистрированы на сайте, авторизуйтесь. Если нет, вы можете создать аккаунт или оформить заказ без регистрации.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/checkout.jpg" alt="Оформление заказа">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Ввод контактной информации</h4>
                        <p>Заполните форму с вашими контактными данными: ФИО, телефон, email. Эти данные необходимы для связи с вами по поводу заказа и доставки.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/contact-info.jpg" alt="Ввод контактной информации">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Выбор способа доставки</h4>
                        <p>Выберите удобный для вас способ доставки: курьерская доставка, самовывоз из магазина или доставка через почтовые службы. В зависимости от выбранного способа, укажите адрес доставки или выберите пункт самовывоза.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/delivery-selection.jpg" alt="Выбор способа доставки">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Выбор способа оплаты</h4>
                        <p>Выберите подходящий способ оплаты: банковской картой онлайн, наличными при получении, через электронные кошельки или другие доступные способы.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/payment-selection.jpg" alt="Выбор способа оплаты">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Проверка и подтверждение заказа</h4>
                        <p>Перед подтверждением заказа проверьте правильность указанных данных, состав заказа и общую стоимость. При необходимости, вы можете оставить комментарий к заказу.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/order-confirmation.jpg" alt="Подтверждение заказа">
                        </div>
                    </li>
                    
                    <li class="faq-step animate-on-scroll fade-in">
                        <h4>Завершение оформления</h4>
                        <p>Нажмите кнопку «Подтвердить заказ». После этого вы увидите страницу с подтверждением и номером вашего заказа. Информация о заказе также будет отправлена на указанный вами email.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/order-complete.jpg" alt="Завершение оформления">
                        </div>
                    </li>
                </ol>
                
                <div class="alert alert-info mt-4 animate-on-scroll fade-in">
                    <h4><i class="fas fa-info-circle me-2"></i>Полезная информация</h4>
                    <p>После оформления заказа вы можете отслеживать его статус в личном кабинете. Если у вас возникнут вопросы, наша служба поддержки всегда готова помочь.</p>
                </div>
                
                <div class="faq-accordion mt-5 animate-on-scroll">
                    <h3 class="mb-3">Частые вопросы об оформлении заказа</h3>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Можно ли изменить заказ после его оформления?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Да, вы можете внести изменения в заказ, если он еще не передан в службу доставки. Для этого необходимо связаться с нашей службой поддержки по телефону или через форму обратной связи на сайте.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Обязательно ли регистрироваться для оформления заказа?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Нет, вы можете оформить заказ без регистрации. Однако регистрация дает ряд преимуществ: возможность отслеживать статус заказов, просматривать историю покупок, быстрее оформлять новые заказы.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Как узнать, есть ли товар в наличии?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Наличие товара отображается на странице товара. Если товар доступен для заказа, вы увидите кнопку "Добавить в корзину". Если товара нет в наличии, будет указан статус "Нет в наличии" или "Под заказ" с указанием примерных сроков поставки.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Можно ли оформить заказ по телефону?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Да, вы можете оформить заказ по телефону, позвонив в нашу службу поддержки. Менеджер поможет вам выбрать товар и оформит заказ.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="faq-navigation">
            <a href="/faq" class="faq-nav-btn prev"><i class="fas fa-arrow-left"></i> Назад к FAQ</a>
            <a href="/faq/delivery.php" class="faq-nav-btn next">Информация о доставке <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</main>

<script src="/js/faq.js"></script>

<?php
// Подключаем футер
include_once '../includes/footer/footer.php';
?> 