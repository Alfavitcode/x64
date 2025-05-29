<?php
// Задаем заголовок страницы
$page_title = 'Оплата';

// Подключаем дополнительные стили
$additional_styles = '<link rel="stylesheet" href="/css/faq.css">';

// Подключаем шапку сайта
include_once '../includes/header/header.php';
?>

<main class="main-content">
    <div class="container faq-container">
        <div class="faq-header animate-on-load">
            <h1>Способы оплаты</h1>
            <p>Информация о доступных способах оплаты заказов и безопасности платежей</p>
        </div>
        
        <div class="faq-card animate-on-load">
            <div class="faq-card__header">
                <h2><i class="fas fa-credit-card me-2"></i>Варианты оплаты</h2>
            </div>
            <div class="faq-card__body">
                <p>Мы предлагаем различные способы оплаты заказов, чтобы вы могли выбрать наиболее удобный для вас вариант. Все платежи защищены современными технологиями шифрования для обеспечения безопасности ваших данных.</p>
                
                <div class="faq-card__image animate-on-scroll slide-in-left-scroll">
                    <img src="/faq/images/payment-main.jpg" alt="Способы оплаты">
                </div>
                
                <div class="row mt-5">
                    <div class="col-md-6 animate-on-scroll fade-in">
                        <div class="card mb-4 h-100">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-credit-card me-2"></i>Банковская карта онлайн</h3>
                            </div>
                            <div class="card-body">
                                <p>Оплата банковской картой через безопасный платежный шлюз. Принимаем карты Visa, MasterCard, МИР.</p>
                                
                                <h4 class="h6 mt-3">Преимущества:</h4>
                                <ul>
                                    <li>Мгновенное подтверждение платежа</li>
                                    <li>Безопасный процесс оплаты</li>
                                    <li>Без комиссии</li>
                                    <li>Возможность получить кэшбэк от банка</li>
                                </ul>
                                
                                <div class="d-flex justify-content-around mt-3">
                                    <div class="payment-icon"><i class="fab fa-cc-visa fa-2x text-primary"></i></div>
                                    <div class="payment-icon"><i class="fab fa-cc-mastercard fa-2x text-danger"></i></div>
                                    <div class="payment-icon"><img src="/faq/images/mir-logo.png" alt="МИР" height="30"></div>
                                </div>
                                
                                <div class="faq-card__image mt-3">
                                    <img src="/faq/images/card-payment.jpg" alt="Оплата картой">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 animate-on-scroll fade-in">
                        <div class="card mb-4 h-100">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-mobile-alt me-2"></i>Системы быстрых платежей</h3>
                            </div>
                            <div class="card-body">
                                <p>Быстрая оплата через современные платежные системы с помощью мобильных устройств.</p>
                                
                                <h4 class="h6 mt-3">Доступные методы:</h4>
                                <ul>
                                    <li>Apple Pay</li>
                                    <li>Google Pay</li>
                                    <li>Samsung Pay</li>
                                    <li>Система быстрых платежей (СБП)</li>
                                </ul>
                                
                                <div class="d-flex justify-content-around mt-3">
                                    <div class="payment-icon"><i class="fab fa-apple-pay fa-2x"></i></div>
                                    <div class="payment-icon"><i class="fab fa-google-pay fa-2x"></i></div>
                                    <div class="payment-icon"><img src="/faq/images/sbp-logo.png" alt="СБП" height="30"></div>
                                </div>
                                
                                <div class="faq-card__image mt-3">
                                    <img src="/faq/images/mobile-payment.jpg" alt="Мобильная оплата">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 animate-on-scroll fade-in">
                        <div class="card mb-4 h-100">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-wallet me-2"></i>Электронные кошельки</h3>
                            </div>
                            <div class="card-body">
                                <p>Оплата с помощью популярных электронных платежных систем.</p>
                                
                                <h4 class="h6 mt-3">Поддерживаемые сервисы:</h4>
                                <ul>
                                    <li>ЮMoney (бывшие Яндекс.Деньги)</li>
                                    <li>WebMoney</li>
                                    <li>QIWI</li>
                                </ul>
                                
                                <div class="alert alert-info mt-3">
                                    <small>Обратите внимание, что некоторые электронные кошельки могут взимать комиссию за перевод средств</small>
                                </div>
                                
                                <div class="faq-card__image mt-3">
                                    <img src="/faq/images/ewallet-payment.jpg" alt="Электронные кошельки">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 animate-on-scroll fade-in">
                        <div class="card mb-4 h-100">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Наличными при получении</h3>
                            </div>
                            <div class="card-body">
                                <p>Оплата наличными курьеру при доставке или в пункте самовывоза.</p>
                                
                                <h4 class="h6 mt-3">Особенности:</h4>
                                <ul>
                                    <li>Доступно для курьерской доставки и самовывоза</li>
                                    <li>Возможность проверить товар перед оплатой</li>
                                    <li>Без предоплаты</li>
                                </ul>
                                
                                <div class="alert alert-warning mt-3">
                                    <small>При оплате наличными, пожалуйста, подготовьте точную сумму, так как у курьера может не быть сдачи</small>
                                </div>
                                
                                <div class="faq-card__image mt-3">
                                    <img src="/faq/images/cash-payment.jpg" alt="Оплата наличными">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 animate-on-scroll fade-in">
                    <h3>Безопасность платежей</h3>
                    <p>Мы уделяем особое внимание безопасности ваших платежей и конфиденциальности личной информации:</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="faq-card__image">
                                <img src="/faq/images/payment-security.jpg" alt="Безопасность платежей">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    <strong>SSL-шифрование</strong> - все данные передаются через защищенное соединение
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-lock text-primary me-2"></i>
                                    <strong>Защита 3D-Secure</strong> - дополнительная проверка платежей для предотвращения мошенничества
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-user-shield text-primary me-2"></i>
                                    <strong>PCI DSS сертификация</strong> - соответствие международным стандартам безопасности платежных данных
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-credit-card text-primary me-2"></i>
                                    <strong>Мы не храним данные карт</strong> - все платежи обрабатываются напрямую платежным шлюзом
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-history text-primary me-2"></i>
                                    <strong>Регулярный аудит безопасности</strong> - постоянная проверка и обновление систем защиты
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-primary mt-5 animate-on-scroll fade-in">
                    <h4><i class="fas fa-info-circle me-2"></i>Когда происходит списание средств?</h4>
                    <p>При оплате банковской картой онлайн средства списываются сразу после подтверждения заказа. При оплате наличными или через терминал оплата производится в момент получения товара.</p>
                </div>
                
                <div class="faq-accordion mt-5 animate-on-scroll">
                    <h3 class="mb-3">Частые вопросы об оплате</h3>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Можно ли изменить способ оплаты после оформления заказа?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Да, вы можете изменить способ оплаты, если заказ еще не оплачен и не передан в службу доставки. Для этого необходимо связаться с нашей службой поддержки по телефону или через форму обратной связи на сайте.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Как получить кассовый чек?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>При оплате онлайн электронный кассовый чек будет отправлен на указанный вами email. При оплате наличными курьер предоставит вам бумажный кассовый чек. В случае самовывоза чек будет выдан в пункте выдачи заказов.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Что делать, если произошла ошибка при оплате?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Если при оплате произошла ошибка или средства были списаны, но заказ не был подтвержден, не волнуйтесь. Обычно деньги возвращаются автоматически в течение 5-14 рабочих дней (зависит от банка). Если возврат не произошел в указанный срок, пожалуйста, свяжитесь с нашей службой поддержки, предоставив номер заказа и детали платежа.</p>
                        </div>
                    </div>
                    
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h3>Возможна ли оплата по частям или в рассрочку?</h3>
                        </div>
                        <div class="faq-accordion-body">
                            <p>Да, мы сотрудничаем с несколькими банками и предлагаем возможность оформления рассрочки или кредита на покупку. Доступные варианты будут предложены на странице оформления заказа. Для оформления рассрочки потребуется предоставить необходимые документы и пройти процедуру одобрения от банка.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="faq-navigation">
            <a href="/faq/delivery.php" class="faq-nav-btn prev"><i class="fas fa-arrow-left"></i> Информация о доставке</a>
            <a href="/faq/return.php" class="faq-nav-btn next">Возврат товаров <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</main>

<script src="/js/faq.js"></script>

<?php
// Подключаем футер
include_once '../includes/footer/footer.php';
?>