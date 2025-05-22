<?php
// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Заголовок страницы -->
<section class="page-header py-4">
    <div class="container">
        <h1 class="page-title">Контакты</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item active" aria-current="page">Контакты</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Основной контент страницы "Контакты" -->
<section class="contacts-section section py-5">
    <div class="container">
        <!-- Информация о контактах -->
        <div class="row mb-5">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="contact-info-card h-100 p-4 bg-light rounded-3">
                    <h3 class="mb-4">Наши контакты</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon text-primary mb-2">
                            <i class="fas fa-map-marker-alt fa-fw me-2"></i> Адрес:
                        </div>
                        <p class="mb-0">г. Москва, ул. Примерная, д. 123</p>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon text-primary mb-2">
                            <i class="fas fa-phone fa-fw me-2"></i> Телефон:
                        </div>
                        <p class="mb-0">
                            <a href="tel:+74951234567" class="text-body">+7 (495) 123-45-67</a>
                        </p>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="contact-icon text-primary mb-2">
                            <i class="fas fa-envelope fa-fw me-2"></i> E-mail:
                        </div>
                        <p class="mb-0">
                            <a href="mailto:info@x64.ru" class="text-body">info@x64.ru</a>
                        </p>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon text-primary mb-2">
                            <i class="fas fa-clock fa-fw me-2"></i> Режим работы:
                        </div>
                        <p class="mb-0">Пн-Пт: 9:00 - 20:00<br>
                        Сб-Вс: 10:00 - 18:00</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="contact-map h-100 rounded-3 overflow-hidden">
                    <!-- Вставьте здесь карту или изображение местоположения -->
                    <div style="position:relative;overflow:hidden;border-radius:10px;">
                        <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3Aexample&amp;source=constructor" width="100%" height="400" frameborder="0" style="border:0;" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Форма обратной связи -->
        <div class="row">
            <div class="col-md-12">
                <div class="contact-form-container p-4 p-lg-5 border rounded-3">
                    <h3 class="mb-4 text-center">Связаться с нами</h3>
                    <p class="lead text-center mb-4">Оставьте заявку, и наш специалист свяжется с вами в ближайшее время</p>
                    
                    <div class="contact-form-wrapper">
                        <form id="contactForm" class="contact-form" method="POST" action="/ajax/send_contact.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">Ваше имя *</label>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">E-mail *</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Телефон</label>
                                        <input type="tel" id="phone" name="phone" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="requestType" class="form-label">Тип обращения *</label>
                                        <select id="requestType" name="requestType" class="form-select" required>
                                            <option value="" selected disabled>Выберите тип</option>
                                            <option value="support">Техническая поддержка</option>
                                            <option value="order">Вопрос по заказу</option>
                                            <option value="cooperation">Сотрудничество</option>
                                            <option value="other">Другое</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="message" class="form-label">Сообщение *</label>
                                        <textarea id="message" name="message" rows="6" class="form-control" required></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" value="" id="privacyPolicy" required>
                                        <label class="form-check-label" for="privacyPolicy">
                                            Я согласен на обработку персональных данных согласно <a href="/privacy-policy.php" target="_blank">политике конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Отправить сообщение</button>
                                </div>
                            </div>
                            
                            <div class="form-response mt-4" id="formResponse"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ секция -->
        <div class="row mt-5">
            <div class="col-md-12 text-center mb-4">
                <h3 class="section-title">Часто задаваемые вопросы</h3>
                <p class="section-description">Возможно, ответ на ваш вопрос уже есть здесь</p>
            </div>
            
            <div class="col-md-12">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
                                Как долго идет доставка?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Сроки доставки зависят от вашего региона. В Москве и Санкт-Петербурге доставка занимает 1-2 рабочих дня. По России доставка может занять от 3 до 14 рабочих дней в зависимости от удаленности населенного пункта.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                Как выбрать задние крышки для моего смартфона?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>В нашем каталоге вы можете отфильтровать товары по марке и модели вашего устройства. Также вы можете воспользоваться поиском по сайту или оставить заявку, и наши специалисты помогут подобрать подходящий вариант.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                Предоставляете ли вы гарантию на товары?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Да, на все товары в нашем магазине предоставляется гарантия 30 дней с момента получения. Если вы обнаружили заводской брак или дефект, мы заменим товар или вернем деньги.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                Как оплатить заказ?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Мы принимаем оплату банковскими картами, электронными деньгами и наличными при получении заказа. При оформлении заказа вы можете выбрать удобный способ оплаты.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border rounded-3 overflow-hidden">
                        <h2 class="accordion-header" id="faqHeading5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                Можно ли вернуть или обменять товар?
                            </button>
                        </h2>
                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Согласно законодательству РФ, возврат или обмен товара надлежащего качества возможен в течение 14 дней с момента покупки, если товар не был в употреблении, сохранены его товарный вид, потребительские свойства, пломбы и фабричные ярлыки.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Скрипт для обработки формы -->
<script>
$(document).ready(function() {
    // Обработка отправки формы
    $('#contactForm').submit(function(e) {
        e.preventDefault();
        
        // Показываем индикатор загрузки
        $('#formResponse').html('<div class="alert alert-info">Отправка сообщения...</div>');
        
        // Получаем данные формы
        var formData = $(this).serialize();
        
        // Отправляем AJAX запрос
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Очищаем форму при успешной отправке
                    $('#contactForm')[0].reset();
                    $('#formResponse').html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    $('#formResponse').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#formResponse').html('<div class="alert alert-danger">Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону.</div>');
            }
        });
    });
});
</script>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 