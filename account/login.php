<?php
// Подключаем файлы конфигурации
require_once '../includes/config/db_config.php';
require_once '../includes/config/db_functions.php';

// Подключаем файл управления сессиями
require_once '../includes/config/session.php';

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
        case 'invalid_credentials':
            $error_message = 'Неверный логин или пароль';
            break;
        default:
            $error_message = 'Произошла ошибка при входе';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в аккаунт</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6f42c1;
            --success-color: #1cc88a;
            --background-color: transparent;
            --text-color: #5a5c69;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --input-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }
        
        body {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 0;
            color: var(--text-color);
            position: relative;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 10;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all var(--transition-speed);
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
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
            padding-left: 45px;
            font-size: 16px;
            border: 1px solid #e0e0e0;
            background-color: rgba(250, 250, 250, 0.95);
            box-shadow: var(--input-shadow);
            transition: all var(--transition-speed);
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
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all var(--transition-speed);
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
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        /* Стили для текстовых меток */
        .field-label {
            position: absolute;
            left: 47px;
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
        
        /* Разделитель с текстом */
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #adb5bd;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .divider::before {
            margin-right: 10px;
        }
        
        .divider::after {
            margin-left: 10px;
        }
        
        /* Социальные кнопки */
        .social-login {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        
        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 10px;
            color: white;
            transition: all var(--transition-speed);
            box-shadow: var(--input-shadow);
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .social-btn.google {
            background: #DB4437;
        }
        
        .social-btn.facebook {
            background: #4267B2;
        }
        
        .social-btn.vk {
            background: #4C75A3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-sign-in-alt icon-logo"></i>
                <h4>Вход в аккаунт</h4>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form action="login_process.php" method="post" id="loginForm">
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
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
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">Запомнить меня</label>
                        <a href="#" class="float-end text-decoration-none">Забыли пароль?</a>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-animated">
                            <i class="fas fa-sign-in-alt me-2"></i>Войти
                        </button>
                    </div>
                    
              
                    
                    <div class="text-center mt-3">
                        <span>Нет аккаунта?</span>
                        <a href="register.php" class="text-decoration-none ms-1">Зарегистрироваться</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="../js/animations/background-slider.js"></script>
    <script>
        $(document).ready(function() {
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
            $('#loginForm').on('submit', function(e) {
                const login = $('#login').val().trim();
                const password = $('#password').val();
                
                if (login === '' || password === '') {
                    e.preventDefault();
                    alert('Пожалуйста, заполните все поля');
                }
            });
        });
    </script>
</body>
</html>
<?php
?> 