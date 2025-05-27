<?php
/**
 * Класс для работы с электронной почтой
 * Отправляет уведомления пользователям и администраторам
 */

// Подключаем файл конфигурации для почты
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/mail_config.php';

// Встроенная функция для скачивания и подключения PHPMailer
function initPHPMailer() {
    $phpmailer_dir = $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/';
    
    // Проверяем, существует ли директория или создаем ее
    if (!is_dir($phpmailer_dir)) {
        mkdir($phpmailer_dir, 0777, true);
    }
    
    // Список файлов, которые нужно скачать
    $files = [
        'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
        'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
        'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
    ];
    
    // Скачиваем файлы, если их нет
    foreach ($files as $filename => $url) {
        $file_path = $phpmailer_dir . $filename;
        
        if (!file_exists($file_path)) {
            file_put_contents($file_path, file_get_contents($url));
        }
    }
    
    // Подключаем файлы напрямую, а не через автозагрузчик
    require_once $phpmailer_dir . 'Exception.php';
    require_once $phpmailer_dir . 'PHPMailer.php';
    require_once $phpmailer_dir . 'SMTP.php';
}

// Инициализируем PHPMailer
initPHPMailer();

/**
 * Класс для отправки электронных писем
 */
class Mailer {
    // Настройки SMTP из файла конфигурации
    private $host;
    private $username;
    private $password;
    private $port;
    private $encryption;
    
    // Настройки отправителя из файла конфигурации
    private $sender_email;
    private $sender_name;
    
    // Настройки для администратора из файла конфигурации
    private $admin_email;
    
    /**
     * Конструктор класса
     * Инициализирует настройки из файла конфигурации
     */
    public function __construct() {
        // Загружаем настройки из файла конфигурации
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = MAIL_PASSWORD;
        $this->encryption = MAIL_ENCRYPTION;
        
        $this->sender_email = MAIL_FROM_EMAIL;
        $this->sender_name = MAIL_FROM_NAME;
        
        $this->admin_email = ADMIN_EMAIL;
    }
    
    /**
     * Отправляет электронное письмо
     * 
     * @param string $to Email получателя
     * @param string $subject Тема письма
     * @param string $body Тело письма (HTML)
     * @param array $attachments Массив с путями к прикрепляемым файлам
     * @return array Результат отправки ['success' => bool, 'message' => string]
     */
    public function send($to, $subject, $body, $attachments = []) {
        // Создаем экземпляр PHPMailer с использованием пространства имен
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Настройки сервера
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = $this->encryption;
            $mail->Port = $this->port;
            $mail->CharSet = 'UTF-8';
            
            // Режим отладки (при необходимости)
            // $mail->SMTPDebug = 2;
            
            // Отправитель
            $mail->setFrom($this->sender_email, $this->sender_name);
            
            // Получатели
            $mail->addAddress($to);
            
            // Тема и тело письма
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Добавляем вложения, если есть
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment)) {
                        $mail->addAttachment($attachment);
                    }
                }
            }
            
            // Отправляем письмо
            $mail->send();
            
            return [
                'success' => true,
                'message' => 'Письмо успешно отправлено'
            ];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ошибка при отправке письма: ' . $mail->ErrorInfo
            ];
        } catch (\Exception $e) {
            error_log('General Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Общая ошибка: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Отправляет уведомление о новом заказе клиенту
     * 
     * @param array $order Данные заказа
     * @param array $order_items Товары в заказе
     * @return array Результат отправки
     */
    public function sendOrderConfirmation($order, $order_items) {
        // Формируем тему письма
        $subject = 'Заказ #' . $order['id'] . ' успешно оформлен';
        
        // Формируем тело письма
        $body = $this->getOrderEmailTemplate($order, $order_items);
        
        // Отправляем письмо клиенту
        return $this->send($order['email'], $subject, $body);
    }
    
    /**
     * Отправляет уведомление о новом заказе администратору
     * 
     * @param array $order Данные заказа
     * @param array $order_items Товары в заказе
     * @return array Результат отправки
     */
    public function sendOrderNotificationToAdmin($order, $order_items) {
        // Формируем тему письма
        $subject = 'Новый заказ #' . $order['id'] . ' на сайте';
        
        // Формируем тело письма с информацией для администратора
        $body = $this->getOrderAdminEmailTemplate($order, $order_items);
        
        // Отправляем письмо администратору
        return $this->send($this->admin_email, $subject, $body);
    }
    
    /**
     * Формирует HTML-шаблон письма с подтверждением заказа
     * 
     * @param array $order Данные заказа
     * @param array $order_items Товары в заказе
     * @return string HTML-код письма
     */
    private function getOrderEmailTemplate($order, $order_items) {
        // Статус заказа на русском
        $statuses = [
            'pending' => 'Ожидает обработки',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен',
            'closed' => 'Закрыт'
        ];
        
        $status_text = isset($statuses[$order['status']]) ? $statuses[$order['status']] : 'Неизвестно';
        
        // Способ оплаты на русском
        $payment_methods = [
            'card' => 'Банковской картой онлайн',
            'cash' => 'Наличными при получении',
            'wallet' => 'Электронные кошельки'
        ];
        
        $payment_method = isset($payment_methods[$order['payment_method']]) 
            ? $payment_methods[$order['payment_method']] 
            : $order['payment_method'];
        
        // Способ доставки на русском
        $delivery_methods = [
            'courier' => 'Курьерская доставка',
            'pickup' => 'Самовывоз из магазина',
            'post' => 'Почта России'
        ];
        
        $delivery_method = isset($delivery_methods[$order['delivery_method']]) 
            ? $delivery_methods[$order['delivery_method']] 
            : $order['delivery_method'];
        
        // Формируем адрес доставки
        $address = [];
        if (!empty($order['region'])) $address[] = htmlspecialchars($order['region']);
        if (!empty($order['city'])) $address[] = htmlspecialchars($order['city']);
        if (!empty($order['address'])) $address[] = htmlspecialchars($order['address']);
        if (!empty($order['postal_code'])) $address[] = htmlspecialchars($order['postal_code']);
        $address_text = implode(', ', $address);
        
        // Формируем таблицу с товарами
        $items_html = '';
        $subtotal = 0;
        
        foreach ($order_items as $item) {
            $items_html .= '
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['name']) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . $item['quantity'] . ' шт.</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item['price'], 0, '.', ' ') . ' ₽</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item['subtotal'], 0, '.', ' ') . ' ₽</td>
            </tr>';
            
            $subtotal += $item['subtotal'];
        }
        
        // Формируем HTML-шаблон письма
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Заказ #' . $order['id'] . ' успешно оформлен</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #5165F6; margin-bottom: 10px;">Заказ успешно оформлен</h1>
                <p style="font-size: 18px; color: #666;">Спасибо за ваш заказ в магазине ' . MAIL_FROM_NAME . '!</p>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Информация о заказе #' . $order['id'] . '</h2>
                <p style="margin: 5px 0;"><strong>Дата заказа:</strong> ' . date('d.m.Y H:i', strtotime($order['created_at'])) . '</p>
                <p style="margin: 5px 0;"><strong>Статус заказа:</strong> ' . $status_text . '</p>
                <p style="margin: 5px 0;"><strong>Способ оплаты:</strong> ' . $payment_method . '</p>
                <p style="margin: 5px 0;"><strong>Способ доставки:</strong> ' . $delivery_method . '</p>';
                
        if (!empty($order['comment'])) {
            $html .= '<p style="margin: 5px 0;"><strong>Комментарий к заказу:</strong> ' . htmlspecialchars($order['comment']) . '</p>';
        }
                
        $html .= '
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Адрес доставки</h2>
                <p style="margin: 5px 0;"><strong>Получатель:</strong> ' . htmlspecialchars($order['fullname']) . '</p>
                <p style="margin: 5px 0;"><strong>Телефон:</strong> ' . htmlspecialchars($order['phone']) . '</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
                <p style="margin: 5px 0;"><strong>Адрес:</strong> ' . $address_text . '</p>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Товары в заказе</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f0f0f0;">
                            <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Наименование</th>
                            <th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Кол-во</th>
                            <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Цена</th>
                            <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $items_html . '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding: 10px; text-align: right;"><strong>Стоимость товаров:</strong></td>
                            <td style="padding: 10px; text-align: right;">' . number_format($subtotal, 0, '.', ' ') . ' ₽</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding: 10px; text-align: right;"><strong>Стоимость доставки:</strong></td>
                            <td style="padding: 10px; text-align: right;">' . 
                                ($order['delivery_cost'] > 0 
                                    ? number_format($order['delivery_cost'], 0, '.', ' ') . ' ₽' 
                                    : 'Бесплатно') . 
                            '</td>
                        </tr>
                        <tr style="font-size: 18px; font-weight: bold;">
                            <td colspan="3" style="padding: 10px; text-align: right; border-top: 2px solid #ddd;">Итого:</td>
                            <td style="padding: 10px; text-align: right; border-top: 2px solid #ddd; color: #5165F6;">' . 
                                number_format($order['total_amount'], 0, '.', ' ') . ' ₽' . 
                            '</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Что дальше?</h2>
                <p style="margin: 5px 0;">Мы начали обработку вашего заказа. Вы будете получать уведомления об изменении статуса заказа на email.</p>
                <p style="margin: 5px 0;">Если у вас возникли вопросы, свяжитесь с нами по телефону <strong>' . SHOP_PHONE . '</strong> или по email <strong>' . SHOP_SUPPORT_EMAIL . '</strong>.</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;">
                <p>&copy; ' . date('Y') . ' ' . MAIL_FROM_NAME . '. Все права защищены.</p>
                <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Формирует HTML-шаблон письма для администратора о новом заказе
     * 
     * @param array $order Данные заказа
     * @param array $order_items Товары в заказе
     * @return string HTML-код письма
     */
    private function getOrderAdminEmailTemplate($order, $order_items) {
        // Создаем таблицу товаров как в методе выше
        $items_html = '';
        $subtotal = 0;
        
        foreach ($order_items as $item) {
            $items_html .= '
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['name']) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . $item['quantity'] . ' шт.</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item['price'], 0, '.', ' ') . ' ₽</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item['subtotal'], 0, '.', ' ') . ' ₽</td>
            </tr>';
            
            $subtotal += $item['subtotal'];
        }
        
        // Шаблон для администратора
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Новый заказ #' . $order['id'] . '</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #5165F6; margin-bottom: 10px;">Новый заказ на сайте!</h1>
                <p style="font-size: 18px; color: #666;">Поступил новый заказ #' . $order['id'] . ' от клиента.</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="' . SHOP_WEBSITE . '/admin/index.php?tab=orders&order_id=' . $order['id'] . '" style="display: inline-block; background-color: #5165F6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;">
                    Перейти к управлению заказом
                </a>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Информация о клиенте</h2>
                <p style="margin: 5px 0;"><strong>ФИО:</strong> ' . htmlspecialchars($order['fullname']) . '</p>
                <p style="margin: 5px 0;"><strong>Телефон:</strong> ' . htmlspecialchars($order['phone']) . '</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Информация о заказе #' . $order['id'] . '</h2>
                <p style="margin: 5px 0;"><strong>Дата заказа:</strong> ' . date('d.m.Y H:i', strtotime($order['created_at'])) . '</p>
                <p style="margin: 5px 0;"><strong>Способ оплаты:</strong> ' . $order['payment_method'] . '</p>
                <p style="margin: 5px 0;"><strong>Способ доставки:</strong> ' . $order['delivery_method'] . '</p>';
                
        // Формируем адрес доставки
        $address = [];
        if (!empty($order['region'])) $address[] = htmlspecialchars($order['region']);
        if (!empty($order['city'])) $address[] = htmlspecialchars($order['city']);
        if (!empty($order['address'])) $address[] = htmlspecialchars($order['address']);
        if (!empty($order['postal_code'])) $address[] = htmlspecialchars($order['postal_code']);
        $address_text = implode(', ', $address);
        
        $html .= '<p style="margin: 5px 0;"><strong>Адрес доставки:</strong> ' . $address_text . '</p>';
                
        if (!empty($order['comment'])) {
            $html .= '<p style="margin: 5px 0;"><strong>Комментарий к заказу:</strong> ' . htmlspecialchars($order['comment']) . '</p>';
        }
                
        $html .= '
            </div>
            
            <div style="margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Товары в заказе</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f0f0f0;">
                            <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Наименование</th>
                            <th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Кол-во</th>
                            <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Цена</th>
                            <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $items_html . '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding: 10px; text-align: right;"><strong>Стоимость товаров:</strong></td>
                            <td style="padding: 10px; text-align: right;">' . number_format($subtotal, 0, '.', ' ') . ' ₽</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding: 10px; text-align: right;"><strong>Стоимость доставки:</strong></td>
                            <td style="padding: 10px; text-align: right;">' . 
                                ($order['delivery_cost'] > 0 
                                    ? number_format($order['delivery_cost'], 0, '.', ' ') . ' ₽' 
                                    : 'Бесплатно') . 
                            '</td>
                        </tr>
                        <tr style="font-size: 18px; font-weight: bold;">
                            <td colspan="3" style="padding: 10px; text-align: right; border-top: 2px solid #ddd;">Итого:</td>
                            <td style="padding: 10px; text-align: right; border-top: 2px solid #ddd; color: #5165F6;">' . 
                                number_format($order['total_amount'], 0, '.', ' ') . ' ₽' . 
                            '</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;">
                <p>&copy; ' . date('Y') . ' ' . MAIL_FROM_NAME . '. Все права защищены.</p>
                <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Отправляет тестовое письмо для проверки настроек
     * 
     * @param string $to Email получателя
     * @return array Результат отправки
     */
    public function sendTestEmail($to) {
        $subject = 'Тестовое письмо от ' . MAIL_FROM_NAME;
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Тестовое письмо</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #5165F6; margin-bottom: 10px;">Тестовое письмо</h1>
                <p style="font-size: 18px; color: #666;">Это тестовое письмо для проверки настроек отправки.</p>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 15px;">Информация о настройках</h2>
                <p style="margin: 5px 0;"><strong>SMTP сервер:</strong> ' . $this->host . '</p>
                <p style="margin: 5px 0;"><strong>Порт:</strong> ' . $this->port . '</p>
                <p style="margin: 5px 0;"><strong>Шифрование:</strong> ' . $this->encryption . '</p>
                <p style="margin: 5px 0;"><strong>Отправитель:</strong> ' . $this->sender_email . '</p>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 10px; padding: 20px;">
                <p style="margin: 5px 0;">Если вы получили это письмо, значит настройки отправки писем работают корректно.</p>
                <p style="margin: 5px 0;">Вы можете использовать систему для отправки уведомлений о заказах.</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;">
                <p>&copy; ' . date('Y') . ' ' . MAIL_FROM_NAME . '. Все права защищены.</p>
                <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
            </div>
        </body>
        </html>';
        
        return $this->send($to, $subject, $body);
    }
} 