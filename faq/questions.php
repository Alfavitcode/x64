<?php
// Задаем заголовок страницы
$page_title = 'Вопросы и ответы';

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/faq.css">';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<main class="main-content">
    <div class="container faq-container">
        <div class="faq-header animate-on-load">
            <h1>Частые вопросы</h1>
            <p>Ответы на самые популярные вопросы о нашем интернет-магазине, товарах и услугах</p>
        </div>
        
        <div class="faq-card animate-on-load">
            <div class="faq-card__header">
                <h2><i class="fas fa-question-circle me-2"></i>Часто задаваемые вопросы</h2>
            </div>
            <div class="faq-card__body">
                <p>Здесь вы найдете ответы на наиболее популярные вопросы наших клиентов. Если вы не нашли ответа на свой вопрос, пожалуйста, свяжитесь с нашей службой поддержки.</p>
                
                <div class="faq-card__image animate-on-scroll slide-in-left-scroll">
                    <img src="/faq/images/faq-main.jpg" alt="Вопросы и ответы">
                </div>
                
                <div class="row mt-5">
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-shopping-cart me-2"></i>Заказы и покупки</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как проверить статус заказа?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Статус вашего заказа можно проверить в личном кабинете на сайте в разделе "Мои заказы". Также информация о статусе заказа отправляется на указанный при оформлении email.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Можно ли изменить заказ после оформления?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Да, вы можете внести изменения в заказ, если он еще не передан в службу доставки. Для этого необходимо связаться с нашей службой поддержки по телефону или через форму обратной связи на сайте.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как оформить заказ без регистрации?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>На странице оформления заказа выберите опцию "Оформить заказ без регистрации" и заполните необходимые поля. Обратите внимание, что без регистрации вы не сможете отслеживать статус заказа в личном кабинете.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-truck me-2"></i>Доставка и получение</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как долго идет доставка?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Сроки доставки зависят от выбранного способа доставки и вашего региона:</p>
                                            <ul>
                                                <li>Курьерская доставка по Москве: 1-2 рабочих дня</li>
                                                <li>Доставка в регионы: 3-7 рабочих дней</li>
                                                <li>Международная доставка: 7-14 рабочих дней</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Можно ли выбрать время доставки?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Да, при выборе курьерской доставки вы можете указать предпочтительное время доставки в комментарии к заказу. Наш курьер свяжется с вами для согласования точного времени.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Что делать, если посылка не пришла в срок?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Если посылка не пришла в указанный срок, пожалуйста, свяжитесь с нашей службой поддержки. Мы отследим ваш заказ и предоставим информацию о его местонахождении.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-credit-card me-2"></i>Оплата и скидки</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Какие способы оплаты доступны?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Мы предлагаем несколько способов оплаты:</p>
                                            <ul>
                                                <li>Банковской картой онлайн</li>
                                                <li>Электронными деньгами</li>
                                                <li>Наличными при получении</li>
                                                <li>Через системы быстрых платежей</li>
                                            </ul>
                                            <p>Подробнее о каждом способе оплаты вы можете узнать в разделе <a href="/faq/payment.php">Оплата</a>.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как получить скидку?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Скидки доступны по следующим программам:</p>
                                            <ul>
                                                <li>Накопительная система скидок для постоянных клиентов</li>
                                                <li>Скидки по промокодам (следите за нашими новостями)</li>
                                                <li>Сезонные распродажи и акции</li>
                                                <li>Скидка 5% при подписке на нашу рассылку</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Безопасно ли оплачивать на сайте?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Да, все платежи на нашем сайте защищены современными технологиями шифрования. Мы используем SSL-сертификаты и защиту 3D-Secure. Мы не храним данные ваших карт - все платежи обрабатываются напрямую платежным шлюзом.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-exchange-alt me-2"></i>Возврат и обмен</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как вернуть товар?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Для возврата товара необходимо:</p>
                                            <ol>
                                                <li>Связаться с нашей службой поддержки</li>
                                                <li>Заполнить заявление на возврат</li>
                                                <li>Отправить товар на указанный адрес</li>
                                            </ol>
                                            <p>Подробную информацию о процедуре возврата вы найдете в разделе <a href="/faq/return.php">Возврат</a>.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>В течение какого срока можно вернуть товар?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Товар надлежащего качества можно вернуть в течение 14 дней с момента получения. Товар ненадлежащего качества можно вернуть в течение гарантийного срока или в течение 2 лет, если гарантийный срок не установлен.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как обменять товар?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Обмен товара осуществляется по той же процедуре, что и возврат. Свяжитесь с нашей службой поддержки, уточните наличие нужного товара для обмена и следуйте инструкциям менеджера.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-info-circle me-2"></i>О товарах</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Соответствуют ли товары на фото реальным?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Да, мы стремимся предоставить максимально точные фотографии товаров. Однако, возможны незначительные отличия в цвете из-за особенностей цветопередачи монитора.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как узнать, есть ли товар в наличии?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Информация о наличии товара отображается на странице товара. Если товар доступен для заказа, вы увидите кнопку "Добавить в корзину". Если товара нет в наличии, будет указан статус "Нет в наличии" или "Под заказ".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Предоставляется ли гарантия на товары?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Да, на большинство товаров предоставляется гарантия производителя. Срок гарантии указан в гарантийном талоне или в информации о товаре на сайте.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 animate-on-scroll fade-in">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-user-circle me-2"></i>Личный кабинет</h3>
                            </div>
                            <div class="card-body">
                                <div class="faq-accordion">
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как зарегистрироваться на сайте?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Для регистрации на сайте нажмите на кнопку "Войти" в верхнем правом углу сайта, затем выберите "Регистрация" и заполните необходимые поля. После заполнения формы вам на email придет ссылка для подтверждения регистрации.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Что делать, если я забыл пароль?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>На странице входа нажмите на ссылку "Забыли пароль?". Введите email, указанный при регистрации, и вам будет отправлена ссылка для восстановления пароля.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="faq-accordion-item">
                                        <div class="faq-accordion-header">
                                            <h3>Как изменить личные данные?</h3>
                                        </div>
                                        <div class="faq-accordion-body">
                                            <p>Для изменения личных данных войдите в личный кабинет, перейдите в раздел "Профиль" или "Личные данные" и внесите необходимые изменения. После внесения изменений не забудьте нажать кнопку "Сохранить".</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-primary mt-5 animate-on-scroll fade-in">
                    <h4><i class="fas fa-headset me-2"></i>Не нашли ответа на свой вопрос?</h4>
                    <p>Если вы не нашли ответа на свой вопрос, пожалуйста, свяжитесь с нашей службой поддержки одним из следующих способов:</p>
                    <ul class="mb-0">
                        <li>По телефону: +7 (495) 123-45-67 (ежедневно с 9:00 до 21:00)</li>
                        <li>По email: support@x64.ru</li>
                        <li>Через форму обратной связи на сайте</li>
                        <li>В чате на сайте (в правом нижнем углу)</li>
                    </ul>
                </div>
                
                <div class="faq-card__image mt-5 animate-on-scroll slide-in-right-scroll">
                    <img src="/faq/images/customer-support.jpg" alt="Служба поддержки">
                </div>
            </div>
        </div>
        
        <div class="faq-navigation">
            <a href="/faq/return.php" class="faq-nav-btn prev"><i class="fas fa-arrow-left"></i> Возврат товаров</a>
            <a href="/faq" class="faq-nav-btn next">Вернуться к FAQ <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</main>

<script src="/js/faq.js"></script>

<?php
// Подключаем футер
include_once '../includes/footer/footer.php';
?> 