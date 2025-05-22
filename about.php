<?php
// Подключение файла с функциями для работы с базой данных
require_once 'includes/config/db_functions.php';

// Подключение хедера
include_once 'includes/header/header.php';
?>

<!-- Заголовок страницы -->
<section class="page-header py-4">
    <div class="container">
        <h1 class="page-title">О нас</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item active" aria-current="page">О нас</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Основной контент страницы "О нас" -->
<section class="about-section section py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-image position-relative">
                    <img src="/img/about/about-main.jpg" alt="О нашем магазине" class="img-fluid rounded-3 shadow">
                    <div class="experience-badge position-absolute bg-primary text-white py-3 px-4 rounded-3 shadow">
                        <span class="d-block fs-1 fw-bold text-center"><?php echo date('Y') - 2017; ?></span>
                        <span class="d-block text-center">лет опыта</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2 class="section-title mb-4">Мы создаем стиль для вашего смартфона с 2017 года</h2>
                    <p class="lead mb-4">Добро пожаловать в X64 – специализированный магазин задних крышек для смартфонов, где стиль встречается с защитой.</p>
                    <p class="mb-4">Наша компания начала свой путь в 2017 году с небольшого онлайн-магазина, предлагающего качественные задние крышки для популярных моделей iPhone. За несколько лет мы значительно расширили ассортимент и теперь предлагаем решения для всех популярных брендов смартфонов – от флагманов Apple до широкого спектра Android-устройств.</p>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="feature-item d-flex align-items-center">
                                <div class="feature-icon bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-check text-primary"></i>
                                </div>
                                <div class="feature-text">
                                    <h5 class="mb-0">Качественные материалы</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item d-flex align-items-center">
                                <div class="feature-icon bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-check text-primary"></i>
                                </div>
                                <div class="feature-text">
                                    <h5 class="mb-0">Точная совместимость</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item d-flex align-items-center">
                                <div class="feature-icon bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-check text-primary"></i>
                                </div>
                                <div class="feature-text">
                                    <h5 class="mb-0">Отличный дизайн</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item d-flex align-items-center">
                                <div class="feature-icon bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-check text-primary"></i>
                                </div>
                                <div class="feature-text">
                                    <h5 class="mb-0">Быстрая доставка</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Миссия компании -->
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="mission-block bg-light p-4 p-lg-5 rounded-3">
                    <h3 class="mb-4 text-center">Наша миссия</h3>
                    <p class="lead text-center mb-0">Мы стремимся предоставить нашим клиентам возможность персонализировать свои устройства, сохраняя при этом их функциональность и защиту. Каждая задняя крышка, предлагаемая в нашем магазине, сочетает в себе качество, стиль и доступность.</p>
                </div>
            </div>
        </div>
        
        <!-- Почему нас выбирают -->
        <div class="row mb-5">
            <div class="col-md-12 text-center mb-4">
                <h3 class="section-title">Почему клиенты выбирают нас</h3>
                <p class="section-description">Мы гордимся тем, что предлагаем нашим клиентам только лучшее</p>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="advantage-card text-center h-100 p-4 border rounded-3">
                    <div class="advantage-icon mb-3">
                        <i class="fas fa-gem fa-3x text-primary"></i>
                    </div>
                    <h4 class="advantage-title">Широкий ассортимент</h4>
                    <p class="advantage-text mb-0">Более 1000 моделей задних крышек для различных брендов и моделей смартфонов, от классических до эксклюзивных дизайнов.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="advantage-card text-center h-100 p-4 border rounded-3">
                    <div class="advantage-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h4 class="advantage-title">Гарантия качества</h4>
                    <p class="advantage-text mb-0">Мы предоставляем гарантию на все наши товары и проверяем каждое изделие перед отправкой клиенту.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="advantage-card text-center h-100 p-4 border rounded-3">
                    <div class="advantage-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h4 class="advantage-title">Профессиональная поддержка</h4>
                    <p class="advantage-text mb-0">Наши специалисты помогут подобрать идеальную заднюю крышку для вашего устройства и ответят на все вопросы.</p>
                </div>
            </div>
        </div>
        
        <!-- Наша история -->
        <div class="row mb-5">
            <div class="col-md-12 text-center mb-4">
                <h3 class="section-title">История нашего развития</h3>
            </div>
            
            <div class="col-md-12">
                <div class="timeline">
                    <div class="timeline-item mb-4 row">
                        <div class="col-md-6 order-md-1 order-2">
                            <div class="timeline-content p-4 bg-light rounded-3 h-100">
                                <h4 class="timeline-year text-primary mb-3">2017</h4>
                                <h5 class="timeline-title mb-3">Основание компании</h5>
                                <p class="timeline-text mb-0">Основание онлайн-магазина X64 с фокусом на задние крышки для iPhone. Начало формирования базы постоянных клиентов и первые партнерства с поставщиками.</p>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-2 order-1 mb-3 mb-md-0">
                            <div class="timeline-image">
                                <img src="/img/about/timeline-2017.jpg" alt="Основание компании" class="img-fluid rounded-3 shadow">
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item mb-4 row">
                        <div class="col-md-6 order-2">
                            <div class="timeline-content p-4 bg-light rounded-3 h-100">
                                <h4 class="timeline-year text-primary mb-3">2019</h4>
                                <h5 class="timeline-title mb-3">Расширение ассортимента</h5>
                                <p class="timeline-text mb-0">Значительное расширение ассортимента. Добавление задних крышек для Android-устройств ведущих производителей: Samsung, Xiaomi, Huawei и других.</p>
                            </div>
                        </div>
                        <div class="col-md-6 order-1 mb-3 mb-md-0">
                            <div class="timeline-image">
                                <img src="/img/about/timeline-2019.jpg" alt="Расширение ассортимента" class="img-fluid rounded-3 shadow">
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item mb-4 row">
                        <div class="col-md-6 order-md-1 order-2">
                            <div class="timeline-content p-4 bg-light rounded-3 h-100">
                                <h4 class="timeline-year text-primary mb-3">2021</h4>
                                <h5 class="timeline-title mb-3">Запуск собственной линейки</h5>
                                <p class="timeline-text mb-0">Запуск собственной линейки премиальных задних крышек с уникальным дизайном. Открытие первого офлайн-шоурума в Москве для демонстрации продукции.</p>
                            </div>
                        </div>
                        <div class="col-md-6 order-md-2 order-1 mb-3 mb-md-0">
                            <div class="timeline-image">
                                <img src="/img/about/timeline-2021.jpg" alt="Запуск собственной линейки" class="img-fluid rounded-3 shadow">
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item mb-4 row">
                        <div class="col-md-6 order-2">
                            <div class="timeline-content p-4 bg-light rounded-3 h-100">
                                <h4 class="timeline-year text-primary mb-3">2023</h4>
                                <h5 class="timeline-title mb-3">Современность</h5>
                                <p class="timeline-text mb-0">Сегодня X64 - это команда профессионалов, тысячи довольных клиентов и партнерская сеть по всей России. Мы постоянно развиваемся и стремимся предложить лучшие решения для наших покупателей.</p>
                            </div>
                        </div>
                        <div class="col-md-6 order-1 mb-3 mb-md-0">
                            <div class="timeline-image">
                                <img src="/img/about/timeline-2023.jpg" alt="Современность" class="img-fluid rounded-3 shadow">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Подключение футера
include_once 'includes/footer/footer.php';
?> 