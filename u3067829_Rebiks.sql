-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 22 2025 г., 17:29
-- Версия сервера: 8.0.25-15
-- Версия PHP: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u3067829_Rebiks`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `created_at`) VALUES
(36, 13, '', 2, 1, '2025-03-31 07:40:53'),
(37, 13, '', 3, 1, '2025-03-31 07:40:59'),
(63, 9, '254d90db05961888411f838bad019a84', 10, 1, '2025-05-21 20:27:47'),
(64, 9, '254d90db05961888411f838bad019a84', 9, 1, '2025-05-21 20:27:48'),
(65, 9, '254d90db05961888411f838bad019a84', 11, 1, '2025-05-21 20:28:00'),
(68, 10, 'fa989a4bfb7e2fa1c5d67c4c1afa914e', 11, 3, '2025-05-21 21:05:46'),
(69, 11, '', 10, 1, '2025-05-22 00:34:29'),
(70, 10, '', 10, 1, '2025-05-22 04:55:48');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `parent_id`, `created_at`) VALUES
(1, 'iPhone', 'Смартфоны iPhone от Apple', '/img/categories/iphone.jpg', NULL, '2025-05-20 20:32:35'),
(2, 'Android', 'Смартфоны на операционной системе Android', '/img/categories/android.jpg', NULL, '2025-05-20 20:32:35');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `region` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `delivery_method` varchar(50) NOT NULL,
  `delivery_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `comment` text,
  `cancel_reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `session_id`, `fullname`, `email`, `phone`, `region`, `city`, `address`, `postal_code`, `payment_method`, `delivery_method`, `delivery_cost`, `total_amount`, `status`, `comment`, `cancel_reason`, `created_at`, `updated_at`) VALUES
(1, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '312121', 'card', 'courier', 300.00, 4715.00, 'closed', '', 'бебе с бубу', '2025-05-21 15:31:13', '2025-05-21 16:18:47'),
(2, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '3121212', 'cash', 'post', 250.00, 4235.00, 'completed', 'asas', NULL, '2025-05-21 15:42:27', '2025-05-21 15:58:40'),
(3, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', 'Тургенева 97б', '', 'card', 'courier', 300.00, 1020.00, 'cancelled', '', 'бебе бубу', '2025-05-21 16:19:23', '2025-05-21 16:19:35'),
(4, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', '1212', '', 'card', 'courier', 300.00, 38800.00, 'closed', '', NULL, '2025-05-21 16:23:00', '2025-05-21 16:23:15'),
(5, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '121', 'cash', 'post', 250.00, 3130.00, 'pending', '', NULL, '2025-05-21 16:46:10', '2025-05-21 16:46:10'),
(6, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', '1212', '', 'card', 'courier', 300.00, 1020.00, 'cancelled', '', 'маму ёб', '2025-05-21 16:49:31', '2025-05-21 16:49:50'),
(7, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '4уцывф', '121', 'Тургенева 97б', '', 'card', 'courier', 300.00, 1020.00, 'pending', '', NULL, '2025-05-21 16:50:50', '2025-05-21 16:50:50'),
(8, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'ывы', 'ваы', 'ыфвфыв', '', 'card', 'courier', 300.00, 1020.00, 'processing', '', NULL, '2025-05-21 17:11:30', '2025-05-21 19:51:24'),
(9, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'аваы', 'ываыва', '', 'card', 'courier', 300.00, 35580.00, 'closed', '', NULL, '2025-05-21 17:11:55', '2025-05-21 17:12:19'),
(10, 6, 'fa989a4bfb7e2fa1c5d67c4c1afa914e', 'Калашников Михаил Дмитриевич', 'kmd2005@list.ru', '+7 (919) 200-07-31', 'Орловская оьдл', 'Чернь', 'Тургенева 97б', '', 'card', 'courier', 300.00, 1000.00, 'pending', '', NULL, '2025-05-21 19:51:37', '2025-05-21 19:51:37'),
(11, 9, '254d90db05961888411f838bad019a84', 'в', 'sobaka@mail.ru', '+8 (900) 000-00-00', 'Орловская область', 'Г. Мценсе', 'Улица приключений', '353535', 'cash', 'courier', 300.00, 1855.00, 'pending', '', NULL, '2025-05-21 20:15:02', '2025-05-21 20:15:02');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `price`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 10, 'Крышка iPhone 8 ', 700.00, 2, 1400.00, '2025-05-21 15:31:13'),
(2, 1, 9, 'Крышка iPhone 14', 855.00, 1, 855.00, '2025-05-21 15:31:13'),
(3, 1, 11, 'Крышка iPhone 14', 720.00, 3, 2160.00, '2025-05-21 15:31:13'),
(4, 2, 11, 'Крышка iPhone 14', 720.00, 1, 720.00, '2025-05-21 15:42:27'),
(5, 2, 10, 'Крышка iPhone 8 ', 700.00, 1, 700.00, '2025-05-21 15:42:27'),
(6, 2, 9, 'Крышка iPhone 14', 855.00, 3, 2565.00, '2025-05-21 15:42:27'),
(7, 3, 11, 'Крышка iPhone 14', 720.00, 1, 720.00, '2025-05-21 16:19:23'),
(8, 4, 10, 'Крышка iPhone 8 ', 700.00, 55, 38500.00, '2025-05-21 16:23:00'),
(9, 5, 11, 'Крышка iPhone 14', 720.00, 4, 2880.00, '2025-05-21 16:46:10'),
(10, 6, 11, 'Крышка iPhone 14', 720.00, 1, 720.00, '2025-05-21 16:49:31'),
(11, 7, 11, 'Крышка iPhone 14', 720.00, 1, 720.00, '2025-05-21 16:50:50'),
(12, 8, 11, 'Крышка iPhone 14', 720.00, 1, 720.00, '2025-05-21 17:11:30'),
(13, 9, 11, 'Крышка iPhone 14', 720.00, 49, 35280.00, '2025-05-21 17:11:55'),
(14, 10, 10, 'Крышка iPhone 8 ', 700.00, 1, 700.00, '2025-05-21 19:51:37'),
(15, 11, 9, 'Крышка iPhone 14', 855.00, 1, 855.00, '2025-05-21 20:15:02'),
(16, 11, 10, 'Крышка iPhone 8 ', 700.00, 1, 700.00, '2025-05-21 20:15:02');

-- --------------------------------------------------------

--
-- Структура таблицы `product`
--

CREATE TABLE `product` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `category` varchar(100) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '0',
  `is_bestseller` tinyint(1) NOT NULL DEFAULT '0',
  `discount` int NOT NULL DEFAULT '0',
  `rating` decimal(3,1) NOT NULL DEFAULT '0.0',
  `reviews_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `price`, `category`, `color`, `image`, `sku`, `stock`, `is_new`, `is_bestseller`, `discount`, `rating`, `reviews_count`, `created_at`, `updated_at`) VALUES
(9, 'Крышка iPhone 14', 'Крышка iPhone 14 RED', 900.00, 'iPhone', 'red', '/img/products/14 red.jpg', '1', 500, 1, 0, 5, 0.0, 0, '2025-05-20 20:33:59', '2025-05-21 20:20:54'),
(10, 'Крышка iPhone 8 ', 'Задняя крышка для телефона iPhone 8 Розовое золото', 700.00, 'iPhone', 'black', '/img/products/8 розовое золото.PNG', '2', 500, 1, 0, 0, 0.0, 0, '2025-05-21 08:36:49', '2025-05-21 20:20:47'),
(11, 'Крышка iPhone 14', 'Задняя крышка iPhone 14 синяя', 800.00, 'iPhone', 'red', '/img/products/8 красный.PNG', '3', 500, 1, 0, 10, 0.0, 0, '2025-05-21 09:06:46', '2025-05-21 20:20:34');

-- --------------------------------------------------------

--
-- Структура таблицы `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `sku` varchar(50) NOT NULL COMMENT 'Артикул для группировки вариантов одного товара',
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(20) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` text COMMENT 'Описание для конкретного цветового варианта',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Цена для конкретного цветового варианта',
  `is_default` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `sku`, `color_name`, `color_code`, `image_path`, `description`, `price`, `is_default`) VALUES
(1, 1, 'IPHONE14CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Чехол для iPhone 14 красного цвета. Изготовлен из высококачественного силикона с мягкой подкладкой из микрофибры.', 1990.00, 1),
(2, 1, 'IPHONE14CASE', 'Синий', '#4285f4', '/img/products/8 белый.PNG', 'Чехол для iPhone 14 синего цвета. Защищает от царапин и небольших падений. Тонкий дизайн.', 1990.00, 0),
(3, 1, 'IPHONE14CASE', 'Зеленый', '#4caf50', '/img/products/8 розовое золото.PNG', 'Чехол для iPhone 14 зеленого цвета. Прочное покрытие, устойчивое к отпечаткам пальцев.', 1990.00, 0),
(4, 1, 'IPHONE14CASE', 'Желтый', '#ffeb3b', '/img/products/14 red.jpg', 'Яркий желтый чехол для iPhone 14. Легкий и удобный в использовании.', 1990.00, 0),
(5, 2, 'IPHONE13CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Премиум чехол для iPhone 13 красного цвета. Защита от ударов по всему периметру.', 1590.00, 1),
(6, 2, 'IPHONE13CASE', 'Голубой', '#4285f4', '/img/products/8 белый.PNG', 'Голубой чехол для iPhone 13. Повышенная прочность при небольшой толщине.', 1590.00, 0),
(7, 2, 'IPHONE13CASE', 'Розовое золото', '#ffc0cb', '/img/products/8 розовое золото.PNG', 'Элегантный чехол цвета розовое золото для iPhone 13. Идеальная совместимость со всеми функциями.', 1790.00, 0),
(8, 3, 'GALAXYS23CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Чехол для Samsung Galaxy S23 красного цвета. Точное соответствие всем портам и кнопкам.', 1490.00, 0),
(9, 3, 'GALAXYS23CASE', 'Белый', '#ffffff', '/img/products/8 белый.PNG', 'Белый чехол для Samsung Galaxy S23. Минималистичный дизайн, максимальная защита.', 1490.00, 1),
(10, 3, 'GALAXYS23CASE', 'Розовое золото', '#ffc0cb', '/img/products/8 розовое золото.PNG', 'Чехол цвета розовое золото для Samsung Galaxy S23. Улучшенная защита камеры.', 1690.00, 0),
(11, 4, 'XIAOMI13CASE', 'Красный', '#ff5252', '/img/products/14 red.jpg', 'Стильный красный чехол для Xiaomi 13. Прочный материал и отличное качество сборки.', 1290.00, 1),
(12, 4, 'XIAOMI13CASE', 'Черный', '#000000', '/img/products/8 розовое золото.PNG', 'Классический черный чехол для Xiaomi 13. Идеально подходит для повседневного использования.', 1290.00, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rating` int NOT NULL,
  `text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `name`, `email`, `rating`, `text`, `created_at`) VALUES
(1, 1, 'Александр', 'alex@example.com', 5, 'Отличный смартфон! Камера супер, батарея держит долго.', '2023-06-15 07:23:45'),
(2, 1, 'Мария', 'maria@example.com', 4, 'Хороший телефон за свои деньги, но есть небольшие недочеты в работе интерфейса.', '2023-06-22 11:17:32'),
(3, 2, 'Дмитрий', 'dmitry@example.com', 4, 'Удобные часы, но хотелось бы больше функций для спорта.', '2023-07-05 06:45:18'),
(4, 3, 'Елена', 'elena@example.com', 5, 'Звук просто потрясающий! Очень доволен покупкой!', '2023-07-12 13:33:27'),
(5, 3, 'Иван', 'ivan@example.com', 5, 'Наушники супер! Долго держат заряд и отлично сидят в ушах.', '2023-07-18 08:22:49');

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_verification_codes`
--

CREATE TABLE `telegram_verification_codes` (
  `id` int NOT NULL,
  `chat_id` varchar(50) NOT NULL COMMENT 'ID чата в Telegram',
  `username` varchar(100) DEFAULT NULL COMMENT 'Username пользователя в Telegram',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'Имя пользователя в Telegram',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'Фамилия пользователя в Telegram',
  `code` varchar(10) NOT NULL COMMENT 'Код верификации',
  `expires_at` timestamp NOT NULL COMMENT 'Время истечения кода',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время создания записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Коды верификации для привязки Telegram';

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` varchar(255) NOT NULL COMMENT 'ФИО пользователя (отчество не обязательно)',
  `email` varchar(255) NOT NULL COMMENT 'Электронная почта пользователя',
  `phone` varchar(20) NOT NULL COMMENT 'Номер телефона пользователя',
  `login` varchar(255) NOT NULL COMMENT 'Логин пользователя для входа в систему',
  `password` varchar(255) NOT NULL COMMENT 'Хешированный пароль пользователя',
  `role` varchar(20) NOT NULL DEFAULT 'user' COMMENT 'Роль пользователя (по умолчанию "user")',
  `telegram_id` varchar(50) DEFAULT NULL COMMENT 'ID пользователя в Telegram',
  `telegram_username` varchar(100) DEFAULT NULL COMMENT 'Username пользователя в Telegram',
  `telegram_verification_code` varchar(10) DEFAULT NULL COMMENT 'Код для подтверждения привязки Telegram',
  `telegram_code_expires` timestamp NULL DEFAULT NULL COMMENT 'Время истечения кода подтверждения'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица пользователей интернет-магазина';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `login`, `password`, `role`, `telegram_id`, `telegram_username`, `telegram_verification_code`, `telegram_code_expires`) VALUES
(6, 'Калашников Михаил Дмитриевич', 'kmd2005@list.ru', '+7 (919) 200-07-31', 'Rebiks!123', '$2y$10$nCXidPHY/cxgWdwCy4/jAuqxawbGUjNNXDtcu4WupuZ07c2fVeRfe', 'Администратор', NULL, NULL, NULL, NULL),
(7, 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'kirieshka', '$2y$10$y9CL8zIShMAlJKT9q7Yvfe6qehTcTLHB5KmUM0.q63B//zTeqn54C', 'Администратор', NULL, NULL, NULL, NULL),
(8, 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'alfavittt', '$2y$10$wyk0JkgFGdS9iKCtEsgwS.PSkJ7MbDrpap6jX/PL0YhNAxiZhmBRK', 'Администратор', NULL, NULL, NULL, NULL),
(9, 'в', 'sobaka@mail.ru', '+8 (900) 000-00-00', 'Ter_1337', '$2y$10$l2jdcQNc883Sx2EAIdKAUeuV5oB68AkUBdY.0yS6Xll0C4RhtAece', 'user', NULL, NULL, NULL, NULL),
(10, 'Мишаня Кириешка', 'reb@mail.ru', '+79053024129', 'Rebiks', '$2y$10$kO/Y09C6u4IF5qposaI0Be.cSE/iF9c9uA3Y/7x4Z2PSmpG/0DT6e', 'Администратор', NULL, NULL, NULL, NULL),
(11, 'Филимонов Денис Юрьевич', 'Alfavitden@gmail.com', '+7 (962) 276-39-27', 'Alfavit', '$2y$10$LRXHaw7zHfHo3dbg2fYkXujBJ8u7WvPe2eLNbHrcNhQVvct.pz1Ae', 'Администратор', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `added_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `added_at`) VALUES
(3, 2, 10, '2025-05-21 13:14:20'),
(5, 2, 11, '2025-05-21 13:14:41'),
(6, 2, 9, '2025-05-21 13:14:45');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_unique` (`name`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_status` (`status`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Индексы таблицы `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_idx` (`category`),
  ADD KEY `is_new_idx` (`is_new`),
  ADD KEY `is_bestseller_idx` (`is_bestseller`);

--
-- Индексы таблицы `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `sku` (`sku`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id_idx` (`product_id`);

--
-- Индексы таблицы `telegram_verification_codes`
--
ALTER TABLE `telegram_verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chat_id` (`chat_id`),
  ADD UNIQUE KEY `uk_code` (`code`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `idx_telegram_id` (`telegram_id`),
  ADD KEY `idx_telegram_verification_code` (`telegram_verification_code`);

--
-- Индексы таблицы `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `telegram_verification_codes`
--
ALTER TABLE `telegram_verification_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
