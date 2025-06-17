<?php
// Подключаем необходимые файлы
require_once 'includes/config/db_functions.php';

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Подключение библиотек для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="/js/animations/contact-animations.js"></script>

<!-- Стили для заголовка страницы, идентичные каталогу -->
<style>
    /* Заголовок страницы */
    .page-header-bg {
        background: linear-gradient(135deg, #f1f5ff 0%, #e7eeff 100%);
        border-radius: 15px;
        padding: 30px;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .page-header-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(77, 97, 252, 0.05) 0%, transparent 70%);
        transform: rotate(-15deg);
    }
    
    .page-title {
        position: relative;
        z-index: 2;
        font-weight: 700;
        color: #2e3a59;
        margin-bottom: 10px;
        font-size: 32px;
        font-family: 'Montserrat', sans-serif;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    
    .breadcrumb {
        position: relative;
        z-index: 2;
    }

    /* Переопределяем стиль для страницы контактов */
    body {
        font-family: 'Montserrat', sans-serif;
    }
</style>

<!-- Дополнительные стили для страницы контактов -->
<style>
    /* Улучшенные стили для карточек */
    .contact-card {
        transition: transform 0.4s, box-shadow 0.4s;
        border-radius: 15px;
        overflow: hidden;
        border: none;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    }
    
    .contact-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(77, 97, 252, 0.18);
    }
    
    /* Иконки контактов */
    .contact-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(77, 97, 252, 0.12);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        transition: all 0.3s ease;
    }
    
    .contact-block:hover .contact-icon {
        transform: scale(1.15);
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 5px 12px rgba(77, 97, 252, 0.25);
    }
    
    /* Социальные сети */
    .social-btn {
        width: 42px;
        height: 42px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .social-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, #3a4cd1 100%);
        opacity: 0;
        z-index: -1;
        transition: opacity 0.3s ease;
    }
    
    .social-btn:hover {
        color: white;
        transform: translateY(-5px);
    }
    
    .social-btn:hover::before {
        opacity: 1;
    }
    
    /* Улучшенные стили для форм */
    .form-floating {
        position: relative;
        margin-bottom: 20px;
    }

    .form-control, .form-select {
        height: auto;
        padding: 15px 20px;
        font-size: 16px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(77, 97, 252, 0.15);
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }

    .form-floating > label {
        padding: 1rem 1.25rem;
        opacity: 0.65;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        opacity: 0.8;
        transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
        background-color: transparent;
        padding: 0 5px;
        color: var(--primary-color);
        font-weight: 600;
    }

    .form-floating > .form-control:-webkit-autofill ~ label {
        opacity: 0.8;
        transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
        background-color: transparent;
        padding: 0 5px;
    }

    /* Текстовая область с увеличенной высотой */
    textarea.form-control {
        min-height: 160px;
        resize: none;
    }

    /* Улучшенный чекбокс */
    .custom-checkbox {
        padding-left: 10px;
    }

    .custom-checkbox .form-check-input {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: 2px solid #e9ecef;
        background-color: #f8f9fa;
        cursor: pointer;
        margin-top: 0.15em;
    }

    .custom-checkbox .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(77, 97, 252, 0.25);
    }

    .custom-checkbox .form-check-label {
        font-size: 15px;
        padding-left: 8px;
        cursor: pointer;
    }

    /* Улучшенная кнопка отправки */
    .submit-btn {
        padding: 14px 35px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #3a4cd1 100%);
        border: none;
        color: white;
        box-shadow: 0 6px 15px rgba(77, 97, 252, 0.25);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(77, 97, 252, 0.3);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 60%);
        transform: skewX(-25deg);
        transition: all 0.6s ease;
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    /* Улучшенный дизайн полей для выбора (select) */
    .form-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234d61fc' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        padding-right: 45px;
    }
    
    /* Индикатор ввода для текстовых полей */
    .form-control:focus ~ .focus-indicator,
    .form-control:not(:placeholder-shown) ~ .focus-indicator {
        width: 100%;
        opacity: 1;
    }
    
    .focus-indicator {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(to right, var(--primary-color), #3a4cd1);
        transition: width 0.3s ease, opacity 0.3s ease;
        opacity: 0;
        z-index: 3;
    }

    /* Эффекты при вводе данных */
    .form-control:focus::placeholder {
        opacity: 0.5;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }

    /* Оповещения формы */
    .alert {
        border-radius: 12px;
        padding: 15px 20px;
        border: none;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }

    .alert::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .alert-success::before {
        background-color: #4caf50;
    }

    .alert-info {
        background-color: #e3f2fd;
        color: #1565c0;
    }

    .alert-info::before {
        background-color: #2196f3;
    }

    .alert-danger {
        background-color: #ffebee;
        color: #c62828;
    }

    .alert-danger::before {
        background-color: #f44336;
    }

    .alert i {
        margin-right: 10px;
    }

    /* Эффект при загрузке страницы для формы */
    @keyframes formFieldsAppear {
        0% {
            opacity: 0;
            transform: translateY(15px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-floating, .form-check, .submit-btn {
        animation: formFieldsAppear 0.5s ease forwards;
    }

    .form-floating:nth-child(1) { animation-delay: 0.1s; }
    .form-floating:nth-child(2) { animation-delay: 0.2s; }
    .form-floating:nth-child(3) { animation-delay: 0.3s; }
    .form-floating:nth-child(4) { animation-delay: 0.4s; }
    .form-check { animation-delay: 0.5s; }
    .submit-btn { animation-delay: 0.6s; }
</style>

<!-- Заголовок страницы -->
<section class="py-4">
    <div class="container mt-4">
        <div class="page-header-bg">
            <h1 class="page-title">Контакты</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Контакты</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Основной контент страницы "Контакты" -->
<section class="contacts-section py-5">
    <div class="container">
        <div class="row">
            <!-- Блок с контактной информацией -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100 contact-card">
                    <div class="card-body p-4">
                        <h3 class="section-title text-primary">Наши контакты</h3>
                        
                        <div class="mb-4 contact-block">
                            <div class="d-flex align-items-center mb-2">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <strong class="text-primary">Адрес:</strong>
                            </div>
                            <p>г. Москва, ул. Примерная, д. 123</p>
                        </div>
                        
                        <div class="mb-4 contact-block">
                            <div class="d-flex align-items-center mb-2">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <strong class="text-primary">Телефон:</strong>
                            </div>
                            <p class="d-flex align-items-center">
                                <span class="pulse-dot"></span>
                                <a href="tel:+74951234567" class="text-body text-decoration-none">+7 (495) 123-45-67</a>
                            </p>
                        </div>
                        
                        <div class="mb-4 contact-block">
                            <div class="d-flex align-items-center mb-2">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <strong class="text-primary">E-mail:</strong>
                            </div>
                            <p><a href="mailto:info@x64.ru" class="text-body text-decoration-none">info@x64.ru</a></p>
                        </div>
                        
                        <div class="mb-4 contact-block">
                            <div class="d-flex align-items-center mb-2">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <strong class="text-primary">Режим работы:</strong>
                            </div>
                            <p>Пн-Пт: 9:00 - 20:00<br>
                            Сб-Вс: 10:00 - 18:00</p>
                        </div>

                        <div class="mt-5">
                            <h4 class="mb-4 text-primary">Мы в социальных сетях</h4>
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-outline-primary rounded-circle social-btn social-wave" aria-label="Telegram">
                                    <i class="fab fa-telegram-plane"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle social-btn social-wave" aria-label="VK">
                                    <i class="fab fa-vk"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle social-btn social-wave" aria-label="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle social-btn social-wave" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Форма обратной связи -->
            <div class="col-lg-7" style="animation-delay: 0.2s;">
                <div class="card shadow-sm contact-card">
                    <div class="card-body p-4">
                        <h3 class="section-title text-primary text-center">Связаться с нами</h3>
                        <p class="lead text-center mb-4">Оставьте заявку, и наш специалист свяжется с вами в ближайшее время</p>
                        
                        <form id="contactForm" class="contact-form" method="POST" action="/ajax/send_contact.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3 position-relative">
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Ваше имя" required>
                                        <label for="name">Ваше имя *</label>
                                        <div class="focus-indicator"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating mb-3 position-relative">
                                        <input type="email" id="email" name="email" class="form-control" placeholder="E-mail" required>
                                        <label for="email">E-mail *</label>
                                        <div class="focus-indicator"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating mb-3 position-relative">
                                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Телефон">
                                        <label for="phone">Телефон</label>
                                        <div class="focus-indicator"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select id="requestType" name="requestType" class="form-select" required>
                                            <option value="" selected disabled>Выберите тип</option>
                                            <option value="support">Техническая поддержка</option>
                                            <option value="order">Вопрос по заказу</option>
                                            <option value="cooperation">Сотрудничество</option>
                                            <option value="other">Другое</option>
                                        </select>
                                        <label for="requestType">Тип обращения *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-floating mb-3 position-relative">
                                        <textarea id="message" name="message" class="form-control" placeholder="Сообщение" style="height: 150px" required></textarea>
                                        <label for="message">Сообщение *</label>
                                        <div class="focus-indicator"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-check mb-4 custom-checkbox">
                                        <input class="form-check-input" type="checkbox" value="" id="privacyPolicy" required>
                                        <label class="form-check-label" for="privacyPolicy">
                                            Я согласен на обработку персональных данных согласно <a href="/legal/privacy-policy.php" target="_blank">политике конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 submit-btn">
                                        <i class="fas fa-paper-plane me-2"></i>Отправить сообщение
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-response mt-4" id="formResponse"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Карта с нашим расположением -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm contact-card">
                    <div class="card-body p-4">
                        <h3 class="section-title text-primary text-center">Наше расположение</h3>
                        <p class="text-center mb-4">Мы находимся в центре Москвы, к нам удобно добираться на общественном транспорте</p>
                        
                        <!-- Карта -->
                        <div class="map-container" style="height: 450px; border-radius: 10px; overflow: hidden;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2245.3870259171213!2d37.623903376944695!3d55.75639997987254!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54a5b4ca291e9%3A0x4d551192e28099d9!2z0JrRgNCw0YHQvdCw0Y8g0L_Qu9C-0YnQsNC00YwsINCc0L7RgdC60LLQsA!5e0!3m2!1sru!2sru!4v1711013145929!5m2!1sru!2sru" 
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Скрипт для обработки формы и анимаций -->
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
                    $('#formResponse').html('<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>');
                    
                    // Эффект успешной отправки
                    animateFormSuccess(); // Вызываем функцию из contact-animations.js
                } else {
                    $('#formResponse').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + response.message + '</div>');
                }
            },
            error: function() {
                $('#formResponse').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону.</div>');
            }
        });
    });
    
    // Добавляем класс для плавающих меток при загрузке страницы
    $('.form-control, .form-select').each(function() {
        if ($(this).val() !== '') {
            $(this).closest('.form-floating').addClass('filled');
        }
    });
});
</script>

<!-- Дополнительные стили для анимаций -->
<style>
    /* Анимация успешной отправки формы */
    @keyframes successPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    .success-animation {
        animation: successPulse 1.5s ease;
    }
    
    /* Стили для активного состояния формы */
    .form-floating.focused {
        transform: translateY(-3px);
        transition: transform 0.3s ease;
    }
    
    .form-floating.filled label {
        opacity: 0.65;
        transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        background-color: white;
        padding: 0 5px;
        left: 10px;
        top: 0;
        height: auto;
    }
</style>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 