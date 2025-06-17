<?php
// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Если пользователь уже вошел в систему, перенаправляем его в личный кабинет
if(isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

// Получаем сообщение об ошибке, если оно есть
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields':
            $error_message = 'Пожалуйста, заполните все поля';
            break;
        case 'password_mismatch':
            $error_message = 'Пароли не совпадают';
            break;
        case 'registration_failed':
            $error_message = isset($_GET['message']) ? $_GET['message'] : 'Произошла ошибка при регистрации';
            break;
        default:
            $error_message = 'Произошла ошибка при регистрации';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6f42c1;
            --success-color: #1cc88a;
            --background-color: transparent; /* Изменено с f8f9fc на transparent */
            --text-color: #5a5c69;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --input-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }
        
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 0;
            color: var(--text-color);
            position: relative; /* Добавлено для корректного позиционирования */
        }
        
        /* Добавляем специальные стили для мобильных устройств */
        @media (max-width: 768px) {
            body {
                background: #ffffff !important;
            }
            
            /* Удаляем любые фоновые изображения */
            body:before, body:after,
            .background-slider, .background-panorama,
            .background-darken, .background-overlay {
                display: none !important;
            }
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            position: relative; /* Добавлено для корректного позиционирования */
            z-index: 10; /* Добавлено для отображения поверх фона */
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            will-change: transform, opacity;
            background-color: #ffffff;
        }
        
        .card:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        
        .card-header {
            background: var(--primary-color);
            color: white;
            border-bottom: none;
            padding: 25px;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            padding-left: 45px; /* Увеличиваем отступ слева для иконки */
            font-size: 16px;
            border: 1px solid #e0e0e0;
            background-color: rgba(250, 250, 250, 0.95); /* Немного прозрачный фон для полей ввода */
            box-shadow: var(--input-shadow);
            transition: all 0.3s ease;
            will-change: transform, box-shadow;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
            border-color: #bac8f3;
            background-color: #ffffff;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 10;
            font-size: 16px;
            transition: all 0.3s ease;
            will-change: transform, color;
        }
        
        /* Больше не нужно, т.к. отступ задан для самого input */
        /*.icon-input {
            padding-left: 45px;
        }*/
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            will-change: transform, background-color;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(78, 115, 223, 0.6);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
            box-shadow: var(--input-shadow);
        }
        
        .text-decoration-none {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 500;
            transition: color var(--transition-speed);
        }
        
        .text-decoration-none:hover {
            color: var(--secondary-color);
        }
        
        .text-muted {
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        /* Стили для текстовых меток */
        .field-label {
            position: absolute;
            left: 47px; /* Отступ для метки после иконки */
            top: 50%;
            transform: translateY(-50%);
            transition: 0.2s ease all;
            color: #adb5bd;
            pointer-events: none;
            font-size: 16px;
        }
        
        /* Скрываем метку при фокусе или когда поле не пустое */
        .form-control:focus ~ .field-label,
        .form-control:not(:placeholder-shown) ~ .field-label {
            opacity: 0;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            cursor: pointer;
            z-index: 10;
        }
        
        /* Индикатор силы пароля */
        .password-strength {
            height: 5px;
            margin-top: 8px;
            border-radius: 5px;
            display: flex;
            overflow: hidden;
        }
        
        .strength-segment {
            height: 100%;
            width: 25%;
            margin-right: 2px;
            background-color: #e0e0e0;
            transition: background-color 0.5s ease, width 0.5s ease;
        }
        
        .strength-weak .segment-1 {
            background-color: #f44336;
        }
        
        .strength-medium .segment-1,
        .strength-medium .segment-2 {
            background-color: #ff9800;
        }
        
        .strength-good .segment-1,
        .strength-good .segment-2,
        .strength-good .segment-3 {
            background-color: #4caf50;
        }
        
        .strength-strong .segment-1,
        .strength-strong .segment-2,
        .strength-strong .segment-3,
        .strength-strong .segment-4 {
            background-color: var(--success-color);
        }
        
        /* Анимация кнопки */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .btn-animated:hover {
            animation: pulse 1.5s infinite;
        }
        
        /* Логотип или иконка */
        .icon-logo {
            font-size: 40px;
            margin-bottom: 15px;
            color: white;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Добавляем стиль для кнопки возврата на главную */
        .back-to-home {
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 10px 15px;
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(77, 97, 252, 0.3);
            z-index: 100;
            display: flex;
            align-items: center;
        }
        
        .back-to-home:hover {
            background: #3a4cd1; /* Более темный оттенок синего */
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(77, 97, 252, 0.4);
            color: white;
        }
        
        .back-to-home i {
            margin-right: 8px;
        }
        
        /* Стили для анимаций */
        .form-control {
            transition: all 0.3s ease;
            will-change: transform, box-shadow;
        }
        
        .input-icon {
            transition: all 0.3s ease;
            will-change: transform, color;
        }
        
        .btn {
            transition: all 0.3s ease;
            will-change: transform, background-color;
            position: relative;
            overflow: hidden;
        }
        
        /* Эффект пульсации для кнопки отправки формы */
        @keyframes pulse-shadow {
            0% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(78, 115, 223, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(78, 115, 223, 0);
            }
        }
        
        .btn-primary[type="submit"] {
            animation: pulse-shadow 2s infinite;
        }
        
        /* Индикатор силы пароля с анимацией */
        .strength-segment {
            transition: background-color 0.5s ease, width 0.5s ease;
        }
        
        /* Добавляем специальные стили для мобильных устройств */
        @media (max-width: 768px) {
            body {
                background: #ffffff !important;
            }
            
            /* Удаляем любые фоновые изображения */
            body:before, body:after,
            .background-slider, .background-panorama,
            .background-darken, .background-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Вернуться на главную
    </a>
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus icon-logo"></i>
                <h4>Создайте аккаунт</h4>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form action="register_process.php" method="post" id="registerForm">
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="" required>
                            <span class="field-label">ФИО</span>
                        </div>
                        <small class="text-muted">Отчество не обязательно</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="" required>
                            <span class="field-label">Телефон</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" class="form-control" id="email" name="email" placeholder="" required>
                            <span class="field-label">Email</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user-tag input-icon"></i>
                            <input type="text" class="form-control" id="login" name="login" placeholder="" required>
                            <span class="field-label">Логин</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="" required>
                            <span class="field-label">Пароль</span>
                            <span class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-segment segment-1"></div>
                            <div class="strength-segment segment-2"></div>
                            <div class="strength-segment segment-3"></div>
                            <div class="strength-segment segment-4"></div>
                        </div>
                        <small id="passwordHint" class="text-muted"></small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary btn-animated">
                            <i class="fas fa-paper-plane me-2"></i>Зарегистрироваться
                        </button>
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>Уже есть аккаунт?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="../js/libs/particles.min.js"></script>
    <script src="../js/animations/auth-animations.js"></script>
    <script>
        $(document).ready(function() {
            // Маска для телефона
            $('#phone').on('input', function() {
                let phone = $(this).val().replace(/\D/g, '');
                if (phone.length === 0) {
                    $(this).val('');
                } else if (phone.length <= 1) {
                    $(this).val('+' + phone);
                } else if (phone.length <= 4) {
                    $(this).val('+' + phone.substring(0, 1) + ' (' + phone.substring(1));
                } else if (phone.length <= 7) {
                    $(this).val('+' + phone.substring(0, 1) + ' (' + phone.substring(1, 4) + ') ' + phone.substring(4));
                } else if (phone.length <= 9) {
                    $(this).val('+' + phone.substring(0, 1) + ' (' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7));
                } else {
                    $(this).val('+' + phone.substring(0, 1) + ' (' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7, 9) + '-' + phone.substring(9, 11));
                }
            });
            
            // Переключатель видимости пароля
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const icon = $(this).find('i');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Проверка силы пароля
            $('#password').on('input', function() {
                const password = $(this).val();
                const strength = calculatePasswordStrength(password);
                const strengthContainer = $('.password-strength');
                
                // Удаляем предыдущие классы
                strengthContainer.removeClass('strength-weak strength-medium strength-good strength-strong');
                
                // Устанавливаем подсказку и класс в зависимости от силы пароля
                if (password.length === 0) {
                    $('#passwordHint').text('');
                } else if (strength < 2) {
                    strengthContainer.addClass('strength-weak');
                    $('#passwordHint').text('Слабый пароль');
                } else if (strength < 3) {
                    strengthContainer.addClass('strength-medium');
                    $('#passwordHint').text('Средний пароль');
                } else if (strength < 4) {
                    strengthContainer.addClass('strength-good');
                    $('#passwordHint').text('Хороший пароль');
                } else {
                    strengthContainer.addClass('strength-strong');
                    $('#passwordHint').text('Отличный пароль');
                }
            });
            
            // Функция для расчета силы пароля
            function calculatePasswordStrength(password) {
                let strength = 0;
                
                // Длина пароля
                if (password.length >= 8) strength++;
                
                // Наличие цифр
                if (/\d/.test(password)) strength++;
                
                // Наличие строчных и заглавных букв
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                
                // Наличие специальных символов
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                return strength;
            }
            
            // Анимация для полей ввода
            $('.form-control').each(function() {
                if ($(this).val() !== '') {
                    $(this).addClass('not-empty');
                }
            });
            
            $('.form-control').on('input', function() {
                if ($(this).val() !== '') {
                    $(this).addClass('not-empty');
                } else {
                    $(this).removeClass('not-empty');
                }
            });
            
            // Валидация формы перед отправкой
            $('#registerForm').on('submit', function(e) {
                let isValid = true;
                const fullname = $('#fullname').val().trim();
                const phone = $('#phone').val().trim();
                const email = $('#email').val().trim();
                const login = $('#login').val().trim();
                const password = $('#password').val();
                
                // Проверка полей
                if (fullname === '' || phone === '' || email === '' || login === '' || password === '') {
                    isValid = false;
                    alert('Пожалуйста, заполните все поля');
                }
                
                // Проверка email
                if (!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email) && email !== '') {
                    isValid = false;
                    alert('Пожалуйста, введите корректный email');
                }
                
                // Проверка телефона
                if (phone !== '' && phone.replace(/\D/g, '').length < 11) {
                    isValid = false;
                    alert('Пожалуйста, введите корректный номер телефона');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
// Мы заменили обычное подключение шапки и подвала сайта на собственный HTML код
// include_once '../includes/header/header.php';
// include_once '../includes/footer/footer.php';
?> 