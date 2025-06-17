<style>
/* Стили для мобильной адаптивности */
@media (max-width: 767px) {
    .profile-content .card {
        margin-bottom: 15px;
    }
    
    .profile-stats {
        flex-direction: row;
        justify-content: space-around;
    }
    
    .stat-item {
        text-align: center;
        padding: 0 10px;
    }
    
    .profile-info-row {
        flex-direction: column;
        padding: 8px 0;
    }
    
    .profile-info-label {
        margin-bottom: 5px;
    }
    
    .profile-info-value {
        padding-left: 0;
    }
    
    .profile-main-card {
        padding: 15px;
    }
    
    .profile-header {
        padding: 15px;
    }
    
    .profile-body {
        padding: 15px;
    }
    
    .orders-table {
        font-size: 14px;
    }
    
    .orders-table th, 
    .orders-table td {
        padding: 10px 5px;
    }
    
    .status-badge {
        padding: 5px 8px;
        font-size: 12px;
    }
    
    .view-order-btn {
        padding: 5px;
    }
}

/* Улучшения для всех устройств */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Улучшенные стили для профиля и заголовков */
.profile-section {
    position: relative;
    padding: 40px 0 120px 0;
    background-color: #f8f9fc;
    margin-bottom: -25px;
}

.profile-container {
    position: relative;
    z-index: 1;
}

.profile-main-card {
    background: linear-gradient(135deg, #5165F6 0%, #4e73df 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(81, 101, 246, 0.15);
    overflow: hidden;
    margin-bottom: 30px;
    transition: all 0.4s ease;
    position: relative;
}

.profile-main-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    z-index: 0;
}

.profile-main-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    z-index: 0;
}

.profile-header {
    padding: 35px 40px 25px;
    color: white;
    position: relative;
    z-index: 1;
}

.profile-welcome {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 5px;
    opacity: 0.9;
    letter-spacing: 0.5px;
}

.profile-name {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    background: linear-gradient(to right, #ffffff, #e6e9ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

.profile-title, .profile-description {
    color: white;
}

.profile-body {
    background-color: #fff;
    padding: 30px 40px;
    border-radius: 0 0 20px 20px;
    position: relative;
    z-index: 1;
}

/* Улучшенные стили для статистики */
.profile-stats-wrapper {
    margin: 25px 0;
}

.profile-stats {
    display: flex;
    justify-content: flex-start;
}

.stat-item {
    background: linear-gradient(135deg, #5a72ff 0%, #4e73df 100%);
    border-radius: 15px;
    padding: 20px 35px;
    text-align: center;
    min-width: 150px;
    box-shadow: 0 10px 20px rgba(81, 101, 246, 0.25);
    transition: all 0.4s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 50%);
    z-index: 0;
}

.stat-value {
    font-size: 42px;
    font-weight: 700;
    color: white;
    margin-bottom: 5px;
    display: block;
    line-height: 1.2;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1.5px;
    position: relative;
    z-index: 1;
}

/* Улучшенные стили для карточек действий */
.profile-section-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 25px;
    color: #333;
    position: relative;
    display: inline-block;
}

.profile-section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(to right, #5165F6, #4e73df);
    border-radius: 3px;
}

.card {
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05) !important;
    transition: all 0.4s ease !important;
    overflow: hidden;
    height: 100%;
}

.card-body {
    padding: 25px !important;
}

.card .rounded-circle {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s ease;
}

.card .fa-shopping-bag,
.card .fa-cog,
.card .fa-telegram-plane {
    font-size: 24px;
    transition: all 0.4s ease;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.card-text {
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

.btn-outline-primary,
.btn-outline-success {
    border-width: 2px;
    font-weight: 500;
    padding: 8px 20px;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover,
.btn-outline-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.fa-arrow-right {
    transition: transform 0.3s ease;
}

.btn:hover .fa-arrow-right {
    transform: translateX(4px);
}

/* Стили для таблицы заказов */
.profile-info-card {
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
    transition: all 0.4s ease;
}

.profile-info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f0f7;
    background: linear-gradient(to right, #f9fafc, #f5f7ff);
}

.profile-info-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #333;
    position: relative;
    transition: all 0.3s ease;
}

.profile-view-all-btn, .profile-edit-btn {
    display: inline-flex;
    align-items: center;
    color: #5165F6;
    font-weight: 500;
    text-decoration: none;
    padding: 8px 18px;
    border-radius: 30px;
    background-color: rgba(81, 101, 246, 0.1);
    transition: all 0.3s ease;
}

.profile-view-all-btn:hover, .profile-edit-btn:hover {
    background-color: #5165F6;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.2);
}

.profile-view-all-btn i, .profile-edit-btn i {
    margin-right: 8px;
    transition: all 0.3s ease;
}

.profile-view-all-btn:hover i, .profile-edit-btn:hover i {
    transform: scale(1.2);
}

.profile-info-body {
    padding: 0;
}

/* Улучшенные стили для информации профиля */
.profile-info-row {
    display: flex;
    padding: 15px 28px;
    border-bottom: 1px solid #f0f0f7;
    transition: all 0.3s ease;
}

.profile-info-row:last-child {
    border-bottom: none;
}

.profile-info-row:hover {
    background-color: rgba(81, 101, 246, 0.03);
}

.profile-info-label {
    width: 120px;
    min-width: 120px;
    color: #666;
    font-weight: 500;
    font-size: 15px;
}

.profile-info-value {
    flex: 1;
    color: #333;
    font-weight: 500;
    padding-left: 20px;
    font-size: 15px;
}

.orders-table-container {
    width: 100%;
    overflow: hidden !important; /* Запрещаем скролл таблицы */
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    table-layout: fixed; /* Фиксированная ширина колонок */
}

.orders-table th {
    color: #666;
    font-weight: 500;
    padding: 16px 22px;
    border-bottom: 1px solid #f0f0f7;
    font-size: 14px;
    background-color: #f9fafc;
}

.orders-table td {
    padding: 16px 22px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f7;
    transition: all 0.3s ease;
    white-space: nowrap; /* Запрещаем перенос текста */
    overflow: hidden; /* Скрываем выходящий за границы текст */
    text-overflow: ellipsis; /* Добавляем многоточие для обрезанного текста */
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.orders-table tr {
    transition: all 0.3s ease;
}

.orders-table tr:hover {
    background-color: rgba(81, 101, 246, 0.03);
    transform: none; /* Отменяем трансформацию при наведении */
}

.order-id {
    font-weight: 600;
    color: #333;
}

.order-date {
    color: #666;
}

.order-amount {
    font-weight: 600;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 500;
    text-align: center;
    min-width: 110px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.status-pending {
    background: linear-gradient(135deg, #FFD166 0%, #FFC233 100%);
    color: #333;
}

.status-processing {
    background: linear-gradient(135deg, #06AED5 0%, #0095B6 100%);
    color: white;
}

.status-completed {
    background: linear-gradient(135deg, #42BA96 0%, #2D9D7A 100%);
    color: white;
}

.status-cancelled {
    background: linear-gradient(135deg, #DF4759 0%, #C9364A 100%);
    color: white;
}

.status-closed {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.status-default {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.status-shipped {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.status-delivered {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    color: white;
}

.order-actions {
    text-align: center;
}

.view-order-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: rgba(81, 101, 246, 0.1);
    color: #5165F6;
    transition: all 0.3s ease;
}

.view-order-btn:hover {
    background-color: #5165F6;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.2);
}

/* Адаптация для разных устройств */
@media (max-width: 991px) {
    .profile-stats {
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .stat-item {
        padding: 15px 25px;
        min-width: 120px;
    }
    
    .stat-value {
        font-size: 30px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .profile-info-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .profile-view-all-btn {
        margin-top: 10px;
    }
    
    .orders-table th:nth-child(2), 
    .orders-table td:nth-child(2) {
        display: none;
    }
    
    /* Дополнительно запрещаем скролл таблицы на мобильных устройствах */
    .orders-table-container {
        overflow: hidden !important;
        max-width: 100%;
    }
    
    .orders-table {
        width: 100%;
        table-layout: fixed;
    }
    
    .orders-table th, 
    .orders-table td {
        padding: 12px 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Настраиваем ширину колонок для мобильных устройств */
    .orders-table th:nth-child(1),
    .orders-table td:nth-child(1) {
        width: 25%;
    }
    
    .orders-table th:nth-child(3),
    .orders-table td:nth-child(3) {
        width: 30%;
    }
    
    .orders-table th:nth-child(4),
    .orders-table td:nth-child(4) {
        width: 35%;
    }
    
    .orders-table th:nth-child(5),
    .orders-table td:nth-child(5) {
        width: 10%;
        text-align: center;
    }
    
    .status-badge {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 80px;
    }
}

/* Анимации для карточек */
.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(77, 97, 252, 0.15) !important;
}

/* Стили для страницы заказов */
.orders-container {
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 25px;
    padding: 35px;
}

.orders-header {
    margin-bottom: 30px;
}

.orders-header h2 {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #333;
}

.orders-description {
    color: #666;
    margin-bottom: 25px;
    font-size: 15px;
    line-height: 1.6;
}

.orders-count {
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
}

.orders-count .stat-item {
    background: linear-gradient(135deg, #5a72ff 0%, #4e73df 100%);
    border-radius: 15px;
    padding: 20px 35px;
    text-align: center;
    min-width: 150px;
    box-shadow: 0 10px 20px rgba(81, 101, 246, 0.25);
    transition: all 0.4s ease;
}

.empty-orders {
    text-align: center;
    padding: 60px 20px;
}

.empty-orders-icon {
    font-size: 70px;
    color: #5165F6;
    opacity: 0.2;
    margin-bottom: 25px;
}

.empty-orders h3 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 18px;
    color: #333;
}

.empty-orders p {
    color: #666;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    font-size: 15px;
    line-height: 1.6;
}

.btn-primary {
    display: inline-block;
    background: linear-gradient(135deg, #5a72ff 0%, #4e73df 100%);
    color: white;
    border: none;
    border-radius: 30px;
    padding: 12px 32px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(81, 101, 246, 0.2);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #4e73df 0%, #3a4ccc 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(81, 101, 246, 0.3);
    color: white;
}

/* Адаптивность для страницы заказов */
@media (max-width: 767px) {
    .orders-container {
        padding: 20px 15px;
    }
    
    .orders-header h2 {
        font-size: 22px;
    }
    
    .orders-table th:nth-child(2), 
    .orders-table td:nth-child(2) {
        display: none;
    }
    
    .orders-table th, 
    .orders-table td {
        padding: 12px 10px;
    }
    
    .status-badge {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 80px;
    }
}

/* Стили для страницы привязки Telegram */
.step-number {
    width: 30px;
    height: 30px;
    min-width: 30px;
    font-weight: bold;
}

.verification-code {
    letter-spacing: 3px;
    font-family: monospace;
}

code {
    background-color: #f8f9fa;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: monospace;
}

.profile-title {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #333;
}

.profile-description {
    color: #666;
    margin-bottom: 25px;
    font-size: 15px;
    line-height: 1.6;
}

/* Стили для страницы настроек */
.form-floating {
    position: relative;
}

.form-floating > .form-control {
    padding: 1.5rem 1rem 0.5rem;
    height: calc(3.5rem + 2px);
}

.form-floating > label {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    padding: 1rem 1rem;
    pointer-events: none;
    border: 1px solid transparent;
    transform-origin: 0 0;
    transition: opacity .1s ease-in-out, transform .1s ease-in-out;
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    opacity: 0.65;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

.form-control {
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
    border-radius: 10px;
    padding: 12px 15px;
}

.form-control:focus {
    border-color: #5165F6;
    box-shadow: 0 0 0 0.25rem rgba(77, 97, 252, 0.15);
}

.password-field {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    color: #5165F6;
}

.btn-danger {
    background: linear-gradient(135deg, #DF4759 0%, #C9364A 100%);
    border: none;
    box-shadow: 0 5px 15px rgba(223, 71, 89, 0.2);
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #C9364A 0%, #B32941 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(223, 71, 89, 0.3);
}

.profile-info-body {
    padding: 20px;
}

/* Декоративные элементы и анимации */
.decor-circle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(81, 101, 246, 0.2) 0%, rgba(81, 101, 246, 0) 70%);
    opacity: 0.6;
    z-index: 0;
    pointer-events: none;
}

/* Эффекты наведения и фокуса для всех интерактивных элементов */
a, button, .btn, .form-control, .card {
    transition: all 0.3s ease;
}

/* Улучшенные стили для сайдбара */
.sidebar-nav {
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.sidebar-header {
    padding: 20px;
    background: linear-gradient(135deg, #5165F6 0%, #4e73df 100%);
    color: white;
    text-align: center;
}

.sidebar-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 5px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    border-bottom: 1px solid #f0f0f7;
}

.sidebar-menu li:last-child {
    border-bottom: none;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
}

.sidebar-menu a:hover {
    background-color: rgba(81, 101, 246, 0.05);
    color: #5165F6;
}

.sidebar-menu a.active {
    background-color: rgba(81, 101, 246, 0.1);
    color: #5165F6;
    border-left: 3px solid #5165F6;
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    font-size: 16px;
    transition: all 0.3s ease;
}

.sidebar-menu a:hover i {
    transform: scale(1.2);
}

.sidebar-menu .logout-link {
    color: #DF4759;
}

.sidebar-menu .logout-link:hover {
    background-color: rgba(223, 71, 89, 0.05);
    color: #DF4759;
}

/* Убираем белый пробел перед футером */
.footer {
    margin-top: 0 !important;
    position: relative;
    z-index: 1;
}

/* Дополнительные фиксы для устранения белой полоски */
main {
    background-color: #f8f9fc;
    padding-bottom: 0;
    margin-bottom: 0;
}

body {
    background-color: #f8f9fc;
}
</style>

<script>
// Функция для переключения видимости пароля
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.password-toggle i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script> 