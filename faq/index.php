<?php
// Задаем заголовок страницы
$page_title = 'Вопросы и ответы';

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/faq-enhanced.css">';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<main class="main-content">
    <div class="container faq-container">
        <div class="faq-header animate-on-load">
            <h1>Вопросы и ответы</h1>
            <p>Здесь вы найдете ответы на самые распространенные вопросы о нашем магазине, способах оплаты, доставке и возврате товаров.</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-shopping-cart me-2"></i>Как сделать заказ</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Подробная инструкция о том, как выбрать товар и оформить заказ в нашем интернет-магазине.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/how-to-order.jpg" alt="Как сделать заказ">
                        </div>
                        <a href="/faq/how-to-order.php" class="faq-btn">Подробнее <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-truck me-2"></i>Доставка</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Информация о способах доставки, сроках и стоимости доставки в различные регионы.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/delivery.jpg" alt="Доставка">
                        </div>
                        <a href="/faq/delivery.php" class="faq-btn">Подробнее <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-credit-card me-2"></i>Оплата</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Доступные способы оплаты заказов и подробная информация о безопасности платежей.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/payment.jpg" alt="Оплата">
                        </div>
                        <a href="/faq/payment.php" class="faq-btn">Подробнее <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-undo me-2"></i>Возврат</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Правила и условия возврата товаров, а также информация о гарантийном обслуживании.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/return.jpg" alt="Возврат">
                        </div>
                        <a href="/faq/return.php" class="faq-btn">Подробнее <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-question-circle me-2"></i>Частые вопросы</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Ответы на часто задаваемые вопросы о работе магазина, товарах и услугах.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/faq.jpg" alt="Частые вопросы">
                        </div>
                        <a href="/faq/questions.php" class="faq-btn">Подробнее <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-load">
                <div class="faq-card">
                    <div class="faq-card__header">
                        <h2><i class="fas fa-headset me-2"></i>Связаться с нами</h2>
                    </div>
                    <div class="faq-card__body">
                        <p>Если вы не нашли ответа на свой вопрос, свяжитесь с нашей службой поддержки.</p>
                        <div class="faq-card__image">
                            <img src="/faq/images/contact.jpg" alt="Связаться с нами">
                        </div>
                        <a href="/contacts.php" class="faq-btn">Перейти <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="faq-accordion mt-5 animate-on-scroll">
            <h2 class="mb-4">Популярные вопросы</h2>
            
            <div class="faq-accordion-item">
                <div class="faq-accordion-header">
                    <h3>Как отследить статус моего заказа?</h3>
                </div>
                <div class="faq-accordion-body">
                    <p>Вы можете отследить статус вашего заказа в личном кабинете на сайте. После входа в аккаунт перейдите в раздел "Мои заказы", где будет отображаться текущий статус. Также вы будете получать уведомления об изменении статуса заказа на указанный при оформлении email.</p>
                </div>
            </div>
            
            <div class="faq-accordion-item">
                <div class="faq-accordion-header">
                    <h3>Сколько времени занимает доставка?</h3>
                </div>
                <div class="faq-accordion-body">
                    <p>Сроки доставки зависят от выбранного способа доставки и вашего региона:</p>
                    <ul>
                        <li>Курьерская доставка по Москве и Санкт-Петербургу: 1-2 рабочих дня</li>
                        <li>Доставка в регионы: 3-7 рабочих дней</li>
                        <li>Международная доставка: 7-14 рабочих дней</li>
                    </ul>
                    <p>Подробную информацию о сроках доставки в ваш регион вы можете узнать на странице оформления заказа или в разделе <a href="/faq/delivery.php">Доставка</a>.</p>
                </div>
            </div>
            
            <div class="faq-accordion-item">
                <div class="faq-accordion-header">
                    <h3>Какие способы оплаты доступны?</h3>
                </div>
                <div class="faq-accordion-body">
                    <p>Мы предлагаем несколько способов оплаты:</p>
                    <ul>
                        <li>Оплата банковской картой онлайн (Visa, MasterCard, МИР)</li>
                        <li>Оплата через электронные кошельки (Яндекс.Деньги, WebMoney)</li>
                        <li>Оплата наличными при получении (для курьерской доставки)</li>
                        <li>Оплата через системы быстрых платежей (Apple Pay, Google Pay)</li>
                    </ul>
                    <p>Подробнее о каждом способе оплаты вы можете узнать в разделе <a href="/faq/payment.php">Оплата</a>.</p>
                </div>
            </div>
            
            <div class="faq-accordion-item">
                <div class="faq-accordion-header">
                    <h3>Как оформить возврат товара?</h3>
                </div>
                <div class="faq-accordion-body">
                    <p>Для возврата товара необходимо:</p>
                    <ol>
                        <li>Заполнить заявление на возврат в личном кабинете или скачать форму с сайта</li>
                        <li>Связаться с нашей службой поддержки для согласования деталей возврата</li>
                        <li>Упаковать товар в оригинальную упаковку со всеми комплектующими</li>
                        <li>Отправить товар на указанный адрес или вызвать курьера для его забора</li>
                    </ol>
                    <p>Более подробную информацию о правилах возврата вы можете найти в разделе <a href="/faq/return.php">Возврат</a>.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Напрямую подключаем скрипт для страницы FAQ -->
<script src="/js/faq-enhanced.js"></script>

<?php
// Подключаем футер
include_once '../includes/footer/footer.php';
?> 