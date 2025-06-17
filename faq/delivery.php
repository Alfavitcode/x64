<?php
// Задаем заголовок страницы
$page_title = 'Доставка';

// Подключаем дополнительные стили и скрипты
$additional_styles = '<link rel="stylesheet" href="/css/faq-enhanced.css">';
$additional_scripts = '<script src="/js/faq-enhanced.js" defer></script>';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<main class="main-content page-transition">
    <div class="container faq-container">
        <div class="legal-header animate-on-load">
            <h1>Доставка</h1>
            <p>Информация о способах, сроках и стоимости доставки в различные регионы</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="faq-nav">
                    <div class="faq-nav-title">Навигация</div>
                    <ul class="faq-nav-list">
                        <li class="faq-nav-item"><a href="#delivery-methods" class="faq-nav-link active">Способы доставки</a></li>
                        <li class="faq-nav-item"><a href="#delivery-zones" class="faq-nav-link">Зоны доставки</a></li>
                        <li class="faq-nav-item"><a href="#delivery-faq" class="faq-nav-link">Частые вопросы</a></li>
                    </ul>
                </div>
                
                <div class="faq-quote mt-4 animate-on-scroll">
                    <p>Мы стараемся сделать доставку максимально быстрой и удобной для наших клиентов!</p>
                </div>
            </div>
            
            <div class="col-lg-9">
                <section id="delivery-methods" class="section-animate">
                    <div class="faq-card animate-on-load">
                        <div class="faq-card__header">
                            <h2><i class="fas fa-truck animated-icon me-2"></i>Способы доставки</h2>
                        </div>
                        <div class="faq-card__body">
                            <p>Мы предлагаем несколько способов доставки, чтобы вы могли выбрать наиболее удобный для вас вариант. Сроки и стоимость доставки зависят от выбранного способа и региона.</p>
                            
                            <div class="faq-image animate-on-scroll">
                                <img src="/faq/images/delivery-main.jpg" alt="Доставка">
                            </div>
                            
                            <div class="row mt-5">
                                <div class="col-md-6 animate-on-scroll fade-in">
                                    <div class="card mb-4 h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0"><i class="fas fa-truck animated-icon me-2"></i>Курьерская доставка</h3>
                                        </div>
                                        <div class="card-body">
                                            <p>Доставка курьером до вашей двери. Возможность выбрать удобную дату и время доставки.</p>
                                            
                                            <h4 class="h6 mt-3">Сроки доставки:</h4>
                                            <ul>
                                                <li>Москва и Санкт-Петербург: 1-2 рабочих дня</li>
                                                <li>Другие крупные города: 2-4 рабочих дня</li>
                                                <li>Остальные регионы: 3-7 рабочих дней</li>
                                            </ul>
                                            
                                            <h4 class="h6 mt-3">Стоимость:</h4>
                                            <ul>
                                                <li>Москва: 300 ₽</li>
                                                <li>Санкт-Петербург: 350 ₽</li>
                                                <li>Другие города: от 400 ₽ (зависит от удаленности)</li>
                                            </ul>
                                            
                                            <div class="alert alert-success mt-3">
                                                <small><i class="fas fa-gift me-2"></i>Бесплатная доставка при заказе от 5000 ₽</small>
                                            </div>
                                            
                                            <div class="faq-image mt-3">
                                                <img src="/faq/images/courier-delivery.jpg" alt="Курьерская доставка">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 animate-on-scroll fade-in">
                                    <div class="card mb-4 h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0"><i class="fas fa-store animated-icon me-2"></i>Самовывоз</h3>
                                        </div>
                                        <div class="card-body">
                                            <p>Вы можете забрать заказ самостоятельно из нашего магазина или пунктов выдачи заказов.</p>
                                            
                                            <h4 class="h6 mt-3">Сроки:</h4>
                                            <ul>
                                                <li>Наш магазин: товар можно забрать в день заказа (при наличии на складе)</li>
                                                <li>Пункты выдачи заказов: 1-2 рабочих дня</li>
                                            </ul>
                                            
                                            <h4 class="h6 mt-3">Стоимость:</h4>
                                            <ul>
                                                <li>Бесплатно</li>
                                            </ul>
                                            
                                            <div class="alert alert-info mt-3">
                                                <small><i class="fas fa-clock me-2"></i>Заказ хранится в пункте выдачи 7 дней</small>
                                            </div>
                                            
                                            <div class="faq-image mt-3">
                                                <img src="/faq/images/pickup-point.jpg" alt="Самовывоз">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 animate-on-scroll fade-in">
                                    <div class="card mb-4 h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0"><i class="fas fa-mail-bulk animated-icon me-2"></i>Почта России</h3>
                                        </div>
                                        <div class="card-body">
                                            <p>Доставка в любой населенный пункт России через Почту России.</p>
                                            
                                            <h4 class="h6 mt-3">Сроки доставки:</h4>
                                            <ul>
                                                <li>Крупные города: 5-7 рабочих дней</li>
                                                <li>Остальные регионы: 7-14 рабочих дней</li>
                                            </ul>
                                            
                                            <h4 class="h6 mt-3">Стоимость:</h4>
                                            <ul>
                                                <li>От 250 ₽ (зависит от веса посылки и региона)</li>
                                            </ul>
                                            
                                            <div class="alert alert-warning mt-3">
                                                <small><i class="fas fa-exclamation-triangle me-2"></i>Сроки доставки указаны ориентировочно и могут меняться в зависимости от работы Почты России</small>
                                            </div>
                                            
                                            <div class="faq-image mt-3">
                                                <img src="/faq/images/post-delivery.jpg" alt="Почта России">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 animate-on-scroll fade-in">
                                    <div class="card mb-4 h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0"><i class="fas fa-shipping-fast animated-icon me-2"></i>Транспортные компании</h3>
                                        </div>
                                        <div class="card-body">
                                            <p>Доставка через транспортные компании СДЭК, DPD, Boxberry и другие.</p>
                                            
                                            <h4 class="h6 mt-3">Сроки доставки:</h4>
                                            <ul>
                                                <li>Крупные города: 2-4 рабочих дня</li>
                                                <li>Остальные регионы: 4-8 рабочих дней</li>
                                            </ul>
                                            
                                            <h4 class="h6 mt-3">Стоимость:</h4>
                                            <ul>
                                                <li>От 300 ₽ (рассчитывается автоматически при оформлении заказа)</li>
                                            </ul>
                                            
                                            <div class="alert alert-success mt-3">
                                                <small><i class="fas fa-search-location me-2"></i>Возможность отслеживать посылку по трек-номеру</small>
                                            </div>
                                            
                                            <div class="faq-image mt-3">
                                                <img src="/faq/images/delivery-company.jpg" alt="Транспортные компании">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-primary mt-4 animate-on-scroll fade-in">
                                <h4><i class="fas fa-info-circle animated-icon me-2"></i>Отслеживание заказа</h4>
                                <p>После отправки заказа вы получите уведомление с номером для отслеживания. Вы можете проверить статус доставки в личном кабинете или на сайте выбранной службы доставки.</p>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="delivery-zones" class="mt-5 section-animate">
                    <div class="faq-card animate-on-scroll">
                        <div class="faq-card__header">
                            <h2><i class="fas fa-map-marked-alt animated-icon me-2"></i>Зоны доставки</h2>
                        </div>
                        <div class="faq-card__body">
                            <p>Стоимость и сроки доставки зависят от зоны, в которой находится ваш населенный пункт:</p>
                            
                            <div class="faq-image mt-3 mb-4 animate-on-scroll">
                                <img src="/faq/images/delivery-map.jpg" alt="Карта зон доставки">
                            </div>
                            
                            <div class="table-responsive animate-on-scroll">
                                <table class="faq-table">
                                    <thead>
                                        <tr>
                                            <th>Зона</th>
                                            <th>Регионы</th>
                                            <th>Сроки доставки</th>
                                            <th>Стоимость</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Зона 1</td>
                                            <td>Москва и Московская область</td>
                                            <td>1-2 дня</td>
                                            <td>300 ₽</td>
                                        </tr>
                                        <tr>
                                            <td>Зона 2</td>
                                            <td>Санкт-Петербург, Нижний Новгород, Казань, Екатеринбург, Ростов-на-Дону</td>
                                            <td>2-4 дня</td>
                                            <td>350 ₽</td>
                                        </tr>
                                        <tr>
                                            <td>Зона 3</td>
                                            <td>Другие крупные города европейской части России</td>
                                            <td>3-5 дней</td>
                                            <td>400 ₽</td>
                                        </tr>
                                        <tr>
                                            <td>Зона 4</td>
                                            <td>Сибирь и Дальний Восток</td>
                                            <td>5-10 дней</td>
                                            <td>от 500 ₽</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="delivery-faq" class="mt-5 section-animate">
                    <div class="faq-card animate-on-scroll">
                        <div class="faq-card__header">
                            <h2><i class="fas fa-question-circle animated-icon me-2"></i>Частые вопросы о доставке</h2>
                        </div>
                        <div class="faq-card__body">
                            <div class="faq-accordion">
                                <div class="faq-accordion-item">
                                    <div class="faq-accordion-header">
                                        <h3>Как узнать, когда будет доставлен мой заказ?</h3>
                                    </div>
                                    <div class="faq-accordion-body">
                                        <p>После оформления заказа вы получите email с подтверждением и ориентировочной датой доставки. Когда заказ будет передан в службу доставки, вы получите уведомление с трек-номером для отслеживания. Курьер свяжется с вами для согласования точного времени доставки.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-accordion-item">
                                    <div class="faq-accordion-header">
                                        <h3>Можно ли изменить адрес доставки после оформления заказа?</h3>
                                    </div>
                                    <div class="faq-accordion-body">
                                        <p>Да, вы можете изменить адрес доставки, если заказ еще не передан в службу доставки. Для этого необходимо связаться с нашей службой поддержки по телефону или через форму обратной связи на сайте.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-accordion-item">
                                    <div class="faq-accordion-header">
                                        <h3>Что делать, если курьер не застал меня дома?</h3>
                                    </div>
                                    <div class="faq-accordion-body">
                                        <p>Если курьер не застал вас дома, он свяжется с вами по телефону для согласования повторной доставки. В некоторых случаях может взиматься дополнительная плата за повторную доставку. Чтобы избежать этого, рекомендуем заранее согласовать удобное время доставки.</p>
                                    </div>
                                </div>
                                
                                <div class="faq-accordion-item">
                                    <div class="faq-accordion-header">
                                        <h3>Доставляете ли вы товары за границу?</h3>
                                    </div>
                                    <div class="faq-accordion-body">
                                        <p>Да, мы осуществляем международную доставку в страны СНГ и некоторые другие страны. Стоимость и сроки доставки рассчитываются индивидуально в зависимости от страны и веса посылки. Для уточнения возможности доставки в вашу страну, пожалуйста, свяжитесь с нашей службой поддержки.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <div class="faq-navigation mt-5 animate-on-scroll">
                    <div class="row">
                        <div class="col-6">
                            <a href="/faq/index.php" class="faq-btn faq-btn-prev">
                                <i class="fas fa-arrow-left me-2"></i> Вернуться к FAQ
                            </a>
                        </div>
                        <div class="col-6 text-end">
                            <a href="/faq/payment.php" class="faq-btn faq-btn-next">
                                Способы оплаты <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
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