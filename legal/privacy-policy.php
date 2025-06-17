<?php
// Заголовок страницы
$page_title = 'Политика конфиденциальности';
$page_description = 'Политика конфиденциальности интернет-магазина x64';

// Подключаем шапку сайта
require_once '../includes/header/header.php';
?>

<!-- Подключение библиотек для анимаций -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>

<!-- Стили для страницы политики конфиденциальности -->
<style>
    /* Основные стили страницы */
    .privacy-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 30px 15px 60px;
    }
    
    .privacy-header {
        background: linear-gradient(135deg, #f1f5ff 0%, #e7eeff 100%);
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .privacy-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(77, 97, 252, 0.08) 0%, transparent 70%);
        transform: rotate(-15deg);
    }
    
    .privacy-header h1 {
        font-size: 36px;
        font-weight: 800;
        color: #2e3a59;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
        font-family: 'Montserrat', sans-serif;
    }
    
    .privacy-header p {
        font-size: 18px;
        color: #566b8d;
        max-width: 750px;
        position: relative;
        z-index: 1;
    }
    
    .privacy-last-updated {
        display: inline-block;
        background-color: rgba(77, 97, 252, 0.1);
        color: var(--primary-color);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    /* Анимированный фон */
    .animated-bg {
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        z-index: 0;
        opacity: 0.7;
    }
    
    /* Стили для навигации */
    .privacy-nav {
        position: sticky;
        top: 80px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 20px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    
    .privacy-nav-title {
        font-weight: 700;
        font-size: 18px;
        color: #2e3a59;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5ff;
    }
    
    .privacy-nav-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }
    
    .privacy-nav-item {
        margin-bottom: 10px;
    }
    
    .privacy-nav-link {
        display: block;
        padding: 8px 15px;
        color: #566b8d;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 15px;
    }
    
    .privacy-nav-link:hover, .privacy-nav-link.active {
        background-color: rgba(77, 97, 252, 0.08);
        color: var(--primary-color);
    }
    
    .privacy-nav-link.active {
        font-weight: 600;
    }
    
    /* Стили для секций */
    .privacy-section {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-bottom: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .privacy-section:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .privacy-section-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .section-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background-color: rgba(77, 97, 252, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: var(--primary-color);
        font-size: 22px;
    }
    
    .privacy-section h2 {
        font-size: 24px;
        font-weight: 700;
        color: #2e3a59;
        margin: 0;
    }
    
    .privacy-section-content {
        padding-left: 65px;
    }
    
    .privacy-section-content p {
        color: #566b8d;
        line-height: 1.7;
        margin-bottom: 15px;
    }
    
    .privacy-section-content ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    
    .privacy-section-content li {
        color: #566b8d;
        margin-bottom: 8px;
        position: relative;
    }
    
    .privacy-section-content li::before {
        content: '•';
        color: var(--primary-color);
        font-weight: bold;
        display: inline-block;
        width: 1em;
        margin-left: -1em;
    }
    
    /* Интерактивные элементы */
    .interactive-element {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .interactive-element:hover {
        background-color: #f1f5ff;
    }
    
    .interactive-element h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #2e3a59;
    }
    
    .interactive-element p {
        margin-bottom: 0;
    }
    
    .expand-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 20px;
        color: var(--primary-color);
        transition: transform 0.3s ease;
    }
    
    .expanded .expand-icon {
        transform: rotate(180deg);
    }
    
    .expandable-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease;
    }
    
    .expanded .expandable-content {
        max-height: 500px;
    }
    
    /* Анимированные элементы */
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .animated {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Кнопка "Наверх" */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 5px 15px rgba(77, 97, 252, 0.3);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .back-to-top.visible {
        opacity: 1;
        visibility: visible;
    }
    
    .back-to-top:hover {
        background-color: #3a4cd1;
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(77, 97, 252, 0.4);
    }
    
    /* Подсветка активной секции */
    .highlight-section {
        border-left: 3px solid var(--primary-color);
        box-shadow: 0 5px 25px rgba(77, 97, 252, 0.15);
    }
    
    /* Адаптивность */
    @media (max-width: 991.98px) {
        .privacy-nav {
            position: relative;
            top: 0;
            margin-bottom: 30px;
        }
    }
    
    @media (max-width: 767.98px) {
        .privacy-header {
            padding: 30px 20px;
        }
        
        .privacy-header h1 {
            font-size: 28px;
        }
        
        .privacy-section {
            padding: 20px;
        }
        
        .privacy-section-content {
            padding-left: 0;
        }
        
        .privacy-section-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .section-icon {
            margin-bottom: 15px;
        }
    }
</style>

<!-- Заголовок страницы -->
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Главная</a></li>
            <li class="breadcrumb-item active" aria-current="page">Политика конфиденциальности</li>
        </ol>
    </nav>
</div>

<div class="privacy-container">
    <div class="privacy-header">
        <div id="privacy-animation" class="animated-bg"></div>
        <span class="privacy-last-updated">Обновлено: 15 мая 2023 г.</span>
        <h1>Политика конфиденциальности</h1>
        <p>Настоящая Политика конфиденциальности определяет, каким образом ООО «x64» собирает, использует, хранит и раскрывает информацию, полученную от пользователей нашего сайта.</p>
    </div>
</div>

<div class="row">
    <!-- Навигация по странице -->
    <div class="col-lg-3 mb-4">
        <div class="privacy-nav">
            <h3 class="privacy-nav-title">Содержание</h3>
            <ul class="privacy-nav-list">
                <li class="privacy-nav-item">
                    <a href="#section-1" class="privacy-nav-link active">1. Общие положения</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-2" class="privacy-nav-link">2. Сбор информации</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-3" class="privacy-nav-link">3. Использование данных</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-4" class="privacy-nav-link">4. Хранение и защита</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-5" class="privacy-nav-link">5. Передача данных</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-6" class="privacy-nav-link">6. Файлы cookie</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-7" class="privacy-nav-link">7. Ваши права</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-8" class="privacy-nav-link">8. Изменения политики</a>
                </li>
                <li class="privacy-nav-item">
                    <a href="#section-9" class="privacy-nav-link">9. Контактная информация</a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Основное содержимое -->
    <div class="col-lg-9">
        <!-- Раздел 1 -->
        <section id="section-1" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>1. Общие положения</h2>
            </div>
            <div class="privacy-section-content">
                <p>1.1. Настоящая Политика конфиденциальности (далее — «Политика») действует в отношении всей информации, включая персональные данные (далее — «Персональные данные»), которую ООО «x64» (далее — «Администрация») может получить о Пользователе во время использования им сайта, расположенного в сети Интернет по адресу: x64.ru (далее — «Сайт»).</p>
                
                <p>1.2. Использование Сайта означает безоговорочное согласие Пользователя с настоящей Политикой и указанными в ней условиями обработки его Персональных данных. В случае несогласия с этими условиями Пользователь должен воздержаться от использования Сайта.</p>
                
                <p>1.3. Настоящая Политика применяется только к Сайту. Администрация не контролирует и не несет ответственность за сайты третьих лиц, на которые Пользователь может перейти по ссылкам, доступным на Сайте.</p>
                
                <p>1.4. Администрация не проверяет достоверность Персональных данных, предоставляемых Пользователем Сайта.</p>
                
                <div class="interactive-element">
                    <h3>Что это значит простыми словами?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Используя наш сайт, вы соглашаетесь с тем, что мы собираем и используем некоторую информацию о вас. Эта политика объясняет, какую именно информацию мы собираем, как ее используем и защищаем. Если вы не согласны с этими условиями, вам не следует пользоваться нашим сайтом.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 2 -->
        <section id="section-2" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h2>2. Сбор информации</h2>
            </div>
            <div class="privacy-section-content">
                <p>2.1. Персональные данные, разрешённые к обработке в рамках настоящей Политики, предоставляются Пользователем путём заполнения форм на Сайте и включают в себя следующую информацию:</p>
                <ul>
                    <li>фамилию, имя, отчество Пользователя;</li>
                    <li>контактный телефон Пользователя;</li>
                    <li>адрес электронной почты (e-mail);</li>
                    <li>адрес доставки Товара;</li>
                    <li>место жительство Пользователя;</li>
                    <li>история заказов.</li>
                </ul>
                
                <p>2.2. Администрация защищает Данные, которые автоматически передаются при посещении страниц:</p>
                <ul>
                    <li>IP адрес;</li>
                    <li>информация из cookies;</li>
                    <li>информация о браузере;</li>
                    <li>время доступа;</li>
                    <li>реферер (адрес предыдущей страницы).</li>
                </ul>
                
                <p>2.3. Отключение cookies может повлечь невозможность доступа к частям Сайта, требующим авторизации.</p>
                
                <p>2.4. Администрация осуществляет сбор статистики об IP-адресах своих посетителей. Данная информация используется с целью выявления и решения технических проблем.</p>
                
                <div class="interactive-element">
                    <h3>Какие данные мы собираем?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Мы собираем информацию, которую вы предоставляете нам при регистрации и оформлении заказа (имя, email, телефон, адрес доставки). Также мы автоматически собираем техническую информацию о вашем устройстве и посещениях (IP-адрес, браузер, время посещения, cookies) для улучшения работы сайта и решения технических проблем.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 3 -->
        <section id="section-3" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h2>3. Использование данных</h2>
            </div>
            <div class="privacy-section-content">
                <p>3.1. Администрация собирает и хранит только те Персональные данные, которые необходимы для предоставления услуг, исполнения соглашений и договоров с Пользователем.</p>
                
                <p>3.2. Персональные данные Пользователя Администрация может использовать в целях:</p>
                <ul>
                    <li>Идентификации Пользователя, зарегистрированного на Сайте, для оформления заказа и (или) заключения Договора купли-продажи товара дистанционным способом;</li>
                    <li>Предоставления Пользователю доступа к персонализированным ресурсам Сайта;</li>
                    <li>Установления с Пользователем обратной связи, включая направление уведомлений, запросов, касающихся использования Сайта, оказания услуг, обработку запросов и заявок от Пользователя;</li>
                    <li>Определения места нахождения Пользователя для обеспечения безопасности, предотвращения мошенничества;</li>
                    <li>Подтверждения достоверности и полноты персональных данных, предоставленных Пользователем;</li>
                    <li>Создания учетной записи для совершения покупок;</li>
                    <li>Уведомления Пользователя Сайта о состоянии Заказа;</li>
                    <li>Обработки и получения платежей;</li>
                    <li>Предоставления Пользователю эффективной клиентской и технической поддержки;</li>
                    <li>Предоставления Пользователю с его согласия, обновлений продукции, специальных предложений, информации о ценах, новостной рассылки и иных сведений;</li>
                    <li>Осуществления рекламной деятельности с согласия Пользователя.</li>
                </ul>
                
                <div class="interactive-element">
                    <h3>Как мы используем ваши данные?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Мы используем собранные данные для обработки ваших заказов, доставки товаров, информирования о статусе заказа, предоставления технической поддержки, а также для улучшения нашего сервиса. С вашего согласия мы также можем отправлять вам информацию о новых продуктах, специальных предложениях и акциях.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 4 -->
        <section id="section-4" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>4. Хранение и защита</h2>
            </div>
            <div class="privacy-section-content">
                <p>4.1. Обработка персональных данных Пользователя осуществляется без ограничения срока, любым законным способом, в том числе в информационных системах персональных данных с использованием средств автоматизации или без использования таких средств.</p>
                
                <p>4.2. При утрате или разглашении персональных данных Администрация информирует Пользователя об утрате или разглашении персональных данных.</p>
                
                <p>4.3. Администрация принимает необходимые организационные и технические меры для защиты персональной информации Пользователя от неправомерного или случайного доступа, уничтожения, изменения, блокирования, копирования, распространения, а также от иных неправомерных действий третьих лиц.</p>
                
                <p>4.4. Администрация совместно с Пользователем принимает все необходимые меры по предотвращению убытков или иных отрицательных последствий, вызванных утратой или разглашением персональных данных Пользователя.</p>
                
                <div class="interactive-element">
                    <h3>Как мы защищаем ваши данные?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Мы применяем современные технические средства защиты для обеспечения безопасности ваших персональных данных. Наши системы регулярно проверяются на наличие уязвимостей и атак, а доступ к вашим данным строго ограничен только теми сотрудниками, которым это необходимо для выполнения своих обязанностей.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 5 -->
        <section id="section-5" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-share-alt"></i>
                </div>
                <h2>5. Передача данных</h2>
            </div>
            <div class="privacy-section-content">
                <p>5.1. Персональные данные Пользователя могут быть переданы уполномоченным органам государственной власти Российской Федерации только по основаниям и в порядке, установленным законодательством Российской Федерации.</p>
                
                <p>5.2. Администрация обязуется не передавать полученные от Пользователя персональные данные третьим лицам, за исключением следующих случаев:</p>
                <ul>
                    <li>Пользователь выразил свое согласие на такие действия;</li>
                    <li>Передача необходима в рамках использования Пользователем определенного Сервиса либо для оказания услуги Пользователю;</li>
                    <li>Передача предусмотрена российским или иным применимым законодательством в рамках установленной законодательством процедуры;</li>
                    <li>Такая передача происходит в рамках продажи или иной передачи бизнеса (полностью или в части), при этом к приобретателю переходят все обязательства по соблюдению условий настоящей Политики.</li>
                </ul>
                
                <div class="interactive-element">
                    <h3>Кому мы можем передавать ваши данные?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Мы передаем ваши данные только в случаях, когда это необходимо для оказания услуг (например, службе доставки для доставки заказа) или когда этого требует закон. Мы не продаем ваши персональные данные третьим лицам. Все наши партнеры, имеющие доступ к вашим данным, обязуются соблюдать конфиденциальность.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 6 -->
        <section id="section-6" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-cookie"></i>
                </div>
                <h2>6. Файлы cookie</h2>
            </div>
            <div class="privacy-section-content">
                <p>6.1. Файлы cookie, передаваемые Администрацией оборудованию Пользователя, могут использоваться для предоставления Пользователю персонализированных сервисов, для таргетирования рекламы, которая показывается Пользователю, в статистических и исследовательских целях, а также для улучшения Сайта.</p>
                
                <p>6.2. Пользователь осознает, что оборудование и программное обеспечение, используемые им для посещения сайтов в сети интернет, могут обладать функцией запрещения операций с файлами cookie, а также удаления ранее полученных файлов cookie.</p>
                
                <p>6.3. Администрация вправе установить, что предоставление определенного сервиса возможно лишь при условии, что прием и получение файлов cookie разрешены Пользователем.</p>
                
                <p>6.4. Структура файла cookie, его содержание и технические параметры определяются Администрацией и могут изменяться без предварительного уведомления Пользователя.</p>
                
                <div class="interactive-element">
                    <h3>Что такое файлы cookie и как мы их используем?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Файлы cookie — это небольшие текстовые файлы, которые сохраняются на вашем устройстве при посещении нашего сайта. Они помогают нам запоминать ваши предпочтения, анализировать эффективность работы сайта и предоставлять вам персонализированный опыт. Вы можете отключить файлы cookie в настройках вашего браузера, но это может ограничить функциональность нашего сайта.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 7 -->
        <section id="section-7" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>7. Ваши права</h2>
            </div>
            <div class="privacy-section-content">
                <p>7.1. Пользователь имеет право на получение информации, касающейся обработки его персональных данных, в том числе содержащей:</p>
                <ul>
                    <li>подтверждение факта обработки персональных данных Администрацией;</li>
                    <li>правовые основания и цели обработки персональных данных;</li>
                    <li>цели и применяемые Администрацией способы обработки персональных данных;</li>
                    <li>сроки обработки персональных данных, в том числе сроки их хранения;</li>
                    <li>информацию об осуществленной или о предполагаемой трансграничной передаче данных.</li>
                </ul>
                
                <p>7.2. Пользователь вправе требовать от Администрации уточнения его персональных данных, их блокирования или уничтожения в случае, если персональные данные являются неполными, устаревшими, неточными, незаконно полученными или не являются необходимыми для заявленной цели обработки.</p>
                
                <p>7.3. Пользователь вправе отозвать согласие на обработку персональных данных в любой момент, направив соответствующее уведомление на электронный адрес Администрации: info@x64.ru</p>
                
                <div class="interactive-element">
                    <h3>Какие права у вас есть в отношении ваших данных?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Вы имеете право запросить доступ к своим персональным данным, которые мы храним, получить информацию о том, как мы их используем, а также потребовать их исправления или удаления. Вы также можете в любой момент отозвать свое согласие на обработку персональных данных, связавшись с нами по электронной почте.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 8 -->
        <section id="section-8" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h2>8. Изменения политики</h2>
            </div>
            <div class="privacy-section-content">
                <p>8.1. Администрация имеет право вносить изменения в настоящую Политику конфиденциальности. При внесении изменений в актуальной редакции указывается дата последнего обновления. Новая редакция Политики вступает в силу с момента ее размещения на Сайте, если иное не предусмотрено новой редакцией Политики.</p>
                
                <p>8.2. Действующая редакция Политики конфиденциальности публикуется на странице по адресу: https://x64.ru/legal/privacy-policy.php</p>
                
                <p>8.3. Используя Сайт, Пользователь подтверждает согласие с настоящей Политикой конфиденциальности и принимает на себя указанные в ней права и обязанности.</p>
                
                <div class="interactive-element">
                    <h3>Как узнать об изменениях в политике конфиденциальности?</h3>
                    <div class="expand-icon"><i class="fas fa-chevron-down"></i></div>
                    <div class="expandable-content">
                        <p>Мы можем периодически обновлять нашу политику конфиденциальности. Все изменения будут публиковаться на этой странице с указанием даты последнего обновления. Рекомендуем периодически проверять эту страницу, чтобы быть в курсе любых изменений. Продолжая использовать наш сайт после изменений, вы подтверждаете свое согласие с обновленной политикой.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Раздел 9 -->
        <section id="section-9" class="privacy-section animate-on-scroll">
            <div class="privacy-section-header">
                <div class="section-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h2>9. Контактная информация</h2>
            </div>
            <div class="privacy-section-content">
                <p>9.1. По всем вопросам, связанным с настоящей Политикой, обработкой и защитой персональных данных, вы можете связаться с нами следующими способами:</p>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-envelope-open-text text-primary me-2"></i> Электронная почта</h5>
                                <p class="card-text">info@x64.ru</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-phone-alt text-primary me-2"></i> Телефон</h5>
                                <p class="card-text">+7 (495) 123-45-67</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-map-marker-alt text-primary me-2"></i> Адрес</h5>
                                <p class="card-text">г. Москва, ул. Примерная, д. 123, офис 456</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p>Мы всегда готовы ответить на ваши вопросы и учесть ваши пожелания.</p>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Кнопка наверх -->
<a href="#" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>
</div>

<!-- JavaScript для анимаций и интерактивности -->
<script>
$(document).ready(function() {
    // Инициализация анимации в шапке
    const animation = lottie.loadAnimation({
        container: document.getElementById('privacy-animation'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'https://assets9.lottiefiles.com/packages/lf20_aohvs9xz.json' // Анимация щита/защиты
    });
    
    // Инициализация ScrollTrigger для GSAP
    gsap.registerPlugin(ScrollTrigger);
    
    // Анимация появления секций при скролле
    const sections = document.querySelectorAll('.animate-on-scroll');
    sections.forEach((section, index) => {
        gsap.fromTo(
            section, 
            { 
                opacity: 0, 
                y: 50 
            },
            { 
                opacity: 1, 
                y: 0,
                duration: 0.8,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: section,
                    start: "top 80%",
                    toggleActions: "play none none none"
                },
                delay: index * 0.1
            }
        );
    });
    
    // Обработка кликов на интерактивных элементах
    $('.interactive-element').on('click', function() {
        $(this).toggleClass('expanded');
        
        // Анимация иконки
        const icon = $(this).find('.expand-icon i');
        if ($(this).hasClass('expanded')) {
            gsap.to(icon, { rotation: 180, duration: 0.3 });
        } else {
            gsap.to(icon, { rotation: 0, duration: 0.3 });
        }
    });
    
    // Подсветка активного раздела в навигации при скролле
    function updateActiveSection() {
        const sections = document.querySelectorAll('.privacy-section');
        const navLinks = document.querySelectorAll('.privacy-nav-link');
        
        let currentSection = '';
        
        sections.forEach((section) => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.pageYOffset >= sectionTop - 200) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navLinks.forEach((link) => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
        
        // Подсветка текущей секции
        sections.forEach((section) => {
            section.classList.remove('highlight-section');
            if (section.getAttribute('id') === currentSection) {
                section.classList.add('highlight-section');
            }
        });
    }
    
    // Плавный скролл к разделам при клике по навигации
    $('.privacy-nav-link').on('click', function(e) {
        e.preventDefault();
        
        const targetId = $(this).attr('href');
        const targetPosition = $(targetId).offset().top - 100;
        
        $('html, body').animate({
            scrollTop: targetPosition
        }, 800, 'swing');
        
        // Обновляем активный класс
        $('.privacy-nav-link').removeClass('active');
        $(this).addClass('active');
    });
    
    // Отображение/скрытие кнопки "Наверх"
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#backToTop').addClass('visible');
        } else {
            $('#backToTop').removeClass('visible');
        }
        
        // Вызываем функцию обновления активного раздела
        updateActiveSection();
    });
    
    // Плавный скролл наверх при клике на кнопку
    $('#backToTop').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 800, 'swing');
    });
    
    // Анимация карточек с контактной информацией
    gsap.from('.card', {
        stagger: 0.2,
        opacity: 0,
        y: 20,
        scale: 0.95,
        duration: 0.6,
        ease: "back.out(1.7)",
        scrollTrigger: {
            trigger: '#section-9',
            start: "top 70%"
        }
    });
    
    // Анимация интерактивных элементов при наведении
    $('.interactive-element').hover(
        function() {
            gsap.to($(this), { 
                backgroundColor: "rgba(77, 97, 252, 0.08)", 
                boxShadow: "0 8px 20px rgba(0, 0, 0, 0.1)",
                duration: 0.3 
            });
        },
        function() {
            if (!$(this).hasClass('expanded')) {
                gsap.to($(this), { 
                    backgroundColor: "#f8f9fa", 
                    boxShadow: "none",
                    duration: 0.3 
                });
            }
        }
    );
    
    // Эффект параллакса для заголовка
    $(window).scroll(function() {
        const scrollTop = $(window).scrollTop();
        const headerBg = $('.privacy-header::before');
        
        gsap.to('.privacy-header h1', {
            y: scrollTop * 0.1,
            opacity: 1 - (scrollTop * 0.002),
            duration: 0.1
        });
        
        gsap.to('.privacy-header p', {
            y: scrollTop * 0.05,
            opacity: 1 - (scrollTop * 0.002),
            duration: 0.1
        });
    });
});
</script>

<?php
// Подключаем футер сайта
require_once '../includes/footer/footer.php';
?> 