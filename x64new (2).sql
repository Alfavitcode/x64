-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 31 2025 г., 13:49
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `x64new`
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
(71, 12, '', 11, 7, '2025-05-22 19:17:57'),
(72, 12, '', 10, 7, '2025-05-22 19:17:57'),
(73, 12, '', 9, 7, '2025-05-22 19:17:58'),
(91, 11, '', 11, 1, '2025-05-23 22:28:01'),
(107, 13, '', 10, 1, '2025-05-28 08:09:16');

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
(2, 'Android', 'Смартфоны на операционной системе Android', '/img/categories/android.jpg', NULL, '2025-05-20 20:32:35'),
(7, 'Чехлы для iPhone', 'Защитные чехлы для iPhone', '', 1, '2025-05-31 10:10:46'),
(8, 'Зарядные устройства для iPhone', 'Зарядные устройства для iPhone', '', 1, '2025-05-31 10:10:46'),
(9, 'Защитные стекла для iPhone', 'Защитные стекла для iPhone', '', 1, '2025-05-31 10:10:46'),
(10, 'Наушники Apple', 'Наушники для iPhone', '', 1, '2025-05-31 10:10:46'),
(11, 'Аксессуары для iPhone', 'Другие аксессуары для iPhone', '', 1, '2025-05-31 10:10:46'),
(12, 'Чехлы для Android', 'Защитные чехлы для Android', '', 2, '2025-05-31 10:10:46'),
(13, 'Зарядные устройства для Android', 'Зарядные устройства для Android', '', 2, '2025-05-31 10:10:46'),
(14, 'Защитные стекла для Android', 'Защитные стекла для Android', '', 2, '2025-05-31 10:10:46'),
(15, 'Наушники Android', 'Наушники для Android', '', 2, '2025-05-31 10:10:46'),
(16, 'Аксессуары для Android', 'Другие аксессуары для Android', '', 2, '2025-05-31 10:10:46'),
(20, 'Михаил', 'авываав', '', NULL, '2025-05-31 10:48:25'),
(21, 'авва', 'аывыва', '', 20, '2025-05-31 10:48:34');

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
(1, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '312121', 'card', 'courier', '300.00', '4715.00', 'closed', '', 'бебе с бубу', '2025-05-21 15:31:13', '2025-05-21 16:18:47'),
(2, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '3121212', 'cash', 'post', '250.00', '4235.00', 'closed', 'asas', NULL, '2025-05-21 15:42:27', '2025-05-28 09:51:58'),
(3, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', 'Тургенева 97б', '', 'card', 'courier', '300.00', '1020.00', 'cancelled', '', 'бебе бубу', '2025-05-21 16:19:23', '2025-05-21 16:19:35'),
(4, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', '1212', '', 'card', 'courier', '300.00', '38800.00', 'closed', '', NULL, '2025-05-21 16:23:00', '2025-05-21 16:23:15'),
(5, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'Тульская область', 'Чернь', 'Тургенева 97б', '121', 'cash', 'post', '250.00', '3130.00', 'pending', '', NULL, '2025-05-21 16:46:10', '2025-05-21 16:46:10'),
(6, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'Чернь', '1212', '', 'card', 'courier', '300.00', '1020.00', 'cancelled', '', 'маму ёб', '2025-05-21 16:49:31', '2025-05-21 16:49:50'),
(7, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '4уцывф', '121', 'Тургенева 97б', '', 'card', 'courier', '300.00', '1020.00', 'pending', '', NULL, '2025-05-21 16:50:50', '2025-05-21 16:50:50'),
(8, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', 'ывы', 'ваы', 'ыфвфыв', '', 'card', 'courier', '300.00', '1020.00', 'processing', '', NULL, '2025-05-21 17:11:30', '2025-05-21 19:51:24'),
(9, 7, '12ar11u3nl40144sq5t86j2mnvkn3s6v', 'Калашников Михаил Дмитриевич', 'acv@mail.ru', '+7 (919) 200-07-31', '', 'аваы', 'ываыва', '', 'card', 'courier', '300.00', '35580.00', 'closed', '', NULL, '2025-05-21 17:11:55', '2025-05-21 17:12:19'),
(10, 6, 'fa989a4bfb7e2fa1c5d67c4c1afa914e', 'Калашников Михаил Дмитриевич', 'kmd2005@list.ru', '+7 (919) 200-07-31', 'Орловская оьдл', 'Чернь', 'Тургенева 97б', '', 'card', 'courier', '300.00', '1000.00', 'pending', '', NULL, '2025-05-21 19:51:37', '2025-05-21 19:51:37'),
(11, 9, '254d90db05961888411f838bad019a84', 'в', 'sobaka@mail.ru', '+8 (900) 000-00-00', 'Орловская область', 'Г. Мценсе', 'Улица приключений', '353535', 'cash', 'courier', '300.00', '1855.00', 'pending', '', NULL, '2025-05-21 20:15:02', '2025-05-21 20:15:02'),
(12, 10, '5c88560227e1779c5f750d043e047b92', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'Тульская область', 'Чернь', 'Тургенева 97б', '323232323', 'card', 'courier', '300.00', '3500.00', 'pending', 'dfdfd', NULL, '2025-05-26 12:54:50', '2025-05-26 12:54:50'),
(13, 10, '721bf55ebe946f9d5eacc8f1b799d1d0', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'Тульская область', 'Чернь', 'Тургенева 97б', '', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-27 15:43:44', '2025-05-27 15:43:44'),
(14, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', '222', '222', '22', '222222', 'card', 'courier', '300.00', '2700.00', 'closed', '', NULL, '2025-05-27 22:35:02', '2025-05-28 08:24:58'),
(15, 14, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+79622763927', 'Орловская обл.', 'Мценск', 'г. Мценск, Улица Машиностроителей, 2', '303031', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-27 23:44:03', '2025-05-27 23:44:03'),
(16, 14, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+79622763927', 'Орловская обл.', 'Мценск', 'г. Мценск, Улица Машиностроителей, 2', '303031', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-27 23:44:19', '2025-05-27 23:44:19'),
(17, 14, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+79622763927', '1', '1', 'vlad i vostok', '1', 'card', 'post', '300.00', '1200.00', 'pending', '<3', NULL, '2025-05-27 23:52:01', '2025-05-28 09:58:43'),
(18, 14, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+79622763927', '1', '1', '1', '1', 'card', 'courier', '300.00', '1200.00', 'processing', '<3', NULL, '2025-05-28 00:01:43', '2025-05-28 09:54:32'),
(19, 14, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+79622763927', 'вфы', 'фыв', 'г. Мценск, Улица Машиностроителей, 2', '303031', 'card', 'courier', '300.00', '1000.00', 'closed', '<3', NULL, '2025-05-28 00:02:50', '2025-05-28 09:54:44'),
(20, 10, 'a133e8c8216002f14393809272b03690', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'gfhg', 'hgdghgh', 'fghff', 'ddggfgf', 'card', 'courier', '300.00', '8300.00', 'closed', '', NULL, '2025-05-28 09:53:16', '2025-05-28 09:54:22'),
(21, 10, 'a133e8c8216002f14393809272b03690', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'uyhg', 'hghghg', 'fghfghfgh', '466576', 'card', 'courier', '300.00', '135300.00', 'completed', '', NULL, '2025-05-28 10:02:30', '2025-05-28 10:02:41'),
(22, 15, '9eb04a5da886711faeb9699d9048c9f3', 'Марина', 'prosekova.mp@bk.ru', '+7 999582-69-43', '', 'Москва', 'Москва', '', 'card', 'courier', '300.00', '1000.00', 'processing', '', NULL, '2025-05-28 11:09:44', '2025-05-28 15:45:48'),
(23, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'sad2', '111', '222', '222', 'card', 'courier', '300.00', '1100.00', 'pending', '', NULL, '2025-05-29 01:09:00', '2025-05-29 01:56:47'),
(24, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'asdsdadsa', 'cxz', 'dsa', 'asdsda', 'card', 'courier', '300.00', '1000.00', 'pending', '', NULL, '2025-05-29 01:18:06', '2025-05-29 01:42:39'),
(25, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'asdasd', 'dsa', 'ads', 'asd', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-29 01:25:39', '2025-05-29 01:31:02'),
(26, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'sda', 'asd', 'asd', 'asd', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-29 01:40:24', '2025-05-29 01:42:30'),
(27, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', '222', '22', '222', '222', 'card', 'courier', '300.00', '1200.00', 'pending', '', NULL, '2025-05-29 01:51:08', '2025-05-29 01:53:40'),
(28, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'das', 'dasads', 'sad', 'dsa', 'card', 'courier', '300.00', '1000.00', 'pending', '', NULL, '2025-05-29 02:22:23', '2025-05-29 02:22:52'),
(29, 8, 'edd859ad3788c23b27361fbd669d421d', 'Филимонов Денис Юрьевич', 'Alfavit@gmail.ru', '+79053024129', 'asd', 'dsaasd', 'sad', 'ads', 'card', 'courier', '300.00', '1200.00', 'closed', '', 'вывыыв', '2025-05-29 02:24:02', '2025-05-30 14:01:41'),
(30, 10, '01a4e8f06759b9e9f777e1b9576a640a', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', '', 'Мценск', 'пррп', '303030', 'cash', 'courier', '300.00', '1100.00', 'processing', '', NULL, '2025-05-30 08:26:19', '2025-05-30 08:28:16'),
(31, 10, 'c902ebe7f9f327cab483157d0bf7a0b7', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', '', 'авыава', 'вавава', '', 'card', 'post', '250.00', '2650.00', 'pending', '', NULL, '2025-05-30 14:02:18', '2025-05-30 14:02:18'),
(32, 10, 'c902ebe7f9f327cab483157d0bf7a0b7', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'выаывыв', 'ывыв', 'ывывы', 'ывы321212', 'card', 'courier', '300.00', '2700.00', 'closed', '', NULL, '2025-05-30 14:02:41', '2025-05-30 14:03:57'),
(33, 16, 'b59f1c2572e9e4da6218b0aca1c40bc6', 'Agagaba', 'kogda_ymryt_evrey@mail.ru', '89000000000', 'Da', 'Da', 'Da', 'Da', 'card', 'courier', '300.00', '5100.00', 'pending', '', NULL, '2025-05-30 18:20:45', '2025-05-30 18:20:45'),
(34, 10, 'n7qfcithdh79inrgs496u3tan3esh6j1', 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'аывваыаыв', 'ваыавывыа', 'ыавыав', 'ыавыав', 'card', 'courier', '300.00', '1900.00', 'pending', '', NULL, '2025-05-31 10:30:54', '2025-05-31 10:30:54');

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
(1, 1, 10, 'Крышка iPhone 8 ', '700.00', 2, '1400.00', '2025-05-21 15:31:13'),
(2, 1, 9, 'Крышка iPhone 14', '855.00', 1, '855.00', '2025-05-21 15:31:13'),
(3, 1, 11, 'Крышка iPhone 14', '720.00', 3, '2160.00', '2025-05-21 15:31:13'),
(4, 2, 11, 'Крышка iPhone 14', '720.00', 1, '720.00', '2025-05-21 15:42:27'),
(5, 2, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-21 15:42:27'),
(6, 2, 9, 'Крышка iPhone 14', '855.00', 3, '2565.00', '2025-05-21 15:42:27'),
(7, 3, 11, 'Крышка iPhone 14', '720.00', 1, '720.00', '2025-05-21 16:19:23'),
(8, 4, 10, 'Крышка iPhone 8 ', '700.00', 55, '38500.00', '2025-05-21 16:23:00'),
(9, 5, 11, 'Крышка iPhone 14', '720.00', 4, '2880.00', '2025-05-21 16:46:10'),
(10, 6, 11, 'Крышка iPhone 14', '720.00', 1, '720.00', '2025-05-21 16:49:31'),
(11, 7, 11, 'Крышка iPhone 14', '720.00', 1, '720.00', '2025-05-21 16:50:50'),
(12, 8, 11, 'Крышка iPhone 14', '720.00', 1, '720.00', '2025-05-21 17:11:30'),
(13, 9, 11, 'Крышка iPhone 14', '720.00', 49, '35280.00', '2025-05-21 17:11:55'),
(14, 10, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-21 19:51:37'),
(15, 11, 9, 'Крышка iPhone 14', '855.00', 1, '855.00', '2025-05-21 20:15:02'),
(16, 11, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-21 20:15:02'),
(17, 12, 11, 'Крышка iPhone 14', '800.00', 4, '3200.00', '2025-05-26 12:54:50'),
(18, 13, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-27 15:43:44'),
(19, 14, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-27 22:35:02'),
(20, 14, 11, 'Крышка iPhone 14', '800.00', 1, '800.00', '2025-05-27 22:35:02'),
(21, 14, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-27 22:35:02'),
(22, 15, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-27 23:44:03'),
(23, 16, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-27 23:44:19'),
(24, 17, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-27 23:52:01'),
(25, 18, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-28 00:01:43'),
(26, 19, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-28 00:02:50'),
(27, 20, 11, 'Крышка iPhone 14', '800.00', 10, '8000.00', '2025-05-28 09:53:16'),
(28, 21, 9, 'Крышка iPhone 14', '900.00', 150, '135000.00', '2025-05-28 10:02:30'),
(29, 22, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-28 11:09:44'),
(30, 23, 11, 'Крышка iPhone 14', '800.00', 1, '800.00', '2025-05-29 01:09:00'),
(31, 24, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-29 01:18:06'),
(32, 25, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-29 01:25:39'),
(33, 26, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-29 01:40:24'),
(34, 27, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-29 01:51:08'),
(35, 28, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-29 02:22:23'),
(36, 29, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-29 02:24:02'),
(37, 30, 11, 'Крышка iPhone 14', '800.00', 1, '800.00', '2025-05-30 08:26:19'),
(38, 31, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-30 14:02:18'),
(39, 31, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-30 14:02:18'),
(40, 31, 11, 'Крышка iPhone 14', '800.00', 1, '800.00', '2025-05-30 14:02:18'),
(41, 32, 11, 'Крышка iPhone 14', '800.00', 1, '800.00', '2025-05-30 14:02:41'),
(42, 32, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-30 14:02:41'),
(43, 32, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-30 14:02:41'),
(44, 33, 11, 'Крышка iPhone 14', '800.00', 2, '1600.00', '2025-05-30 18:20:45'),
(45, 33, 10, 'Крышка iPhone 8 ', '700.00', 2, '1400.00', '2025-05-30 18:20:45'),
(46, 33, 9, 'Крышка iPhone 14', '900.00', 2, '1800.00', '2025-05-30 18:20:45'),
(47, 34, 9, 'Крышка iPhone 14', '900.00', 1, '900.00', '2025-05-31 10:30:54'),
(48, 34, 10, 'Крышка iPhone 8 ', '700.00', 1, '700.00', '2025-05-31 10:30:54');

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
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `subcategory_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `price`, `category`, `color`, `image`, `sku`, `stock`, `is_new`, `is_bestseller`, `discount`, `rating`, `reviews_count`, `created_at`, `updated_at`, `subcategory_id`) VALUES
(9, 'Крышка iPhone 14', 'Крышка iPhone 14 RED', '900.00', 'iPhone', 'red', '/img/products/14 red.jpg', '1', 335, 1, 0, 5, '0.0', 0, '2025-05-20 20:33:59', '2025-05-31 10:30:54', NULL),
(10, 'Крышка iPhone 8 ', 'Задняя крышка для телефона iPhone 8 Розовое золото', '700.00', 'iPhone', 'black', '/img/products/8 розовое золото.PNG', '2', 490, 1, 0, 0, '0.0', 0, '2025-05-21 08:36:49', '2025-05-31 10:30:54', NULL),
(11, 'Крышка iPhone 14', 'Задняя крышка iPhone 14 синяя', '800.00', 'iPhone', 'red', '/img/products/8 красный.PNG', '3', 479, 1, 0, 10, '0.0', 0, '2025-05-21 09:06:46', '2025-05-30 18:20:45', NULL),
(13, 'Михаил', 'dsdsd', '5000.00', 'иуи', NULL, '/img/products/Добавление товара.drawio.png', '3', 150, 1, 1, 50, '0.0', 0, '2025-05-31 10:24:31', '2025-05-31 10:24:31', NULL);

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
(1, 1, 'IPHONE14CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Чехол для iPhone 14 красного цвета. Изготовлен из высококачественного силикона с мягкой подкладкой из микрофибры.', '1990.00', 1),
(2, 1, 'IPHONE14CASE', 'Синий', '#4285f4', '/img/products/8 белый.PNG', 'Чехол для iPhone 14 синего цвета. Защищает от царапин и небольших падений. Тонкий дизайн.', '1990.00', 0),
(3, 1, 'IPHONE14CASE', 'Зеленый', '#4caf50', '/img/products/8 розовое золото.PNG', 'Чехол для iPhone 14 зеленого цвета. Прочное покрытие, устойчивое к отпечаткам пальцев.', '1990.00', 0),
(4, 1, 'IPHONE14CASE', 'Желтый', '#ffeb3b', '/img/products/14 red.jpg', 'Яркий желтый чехол для iPhone 14. Легкий и удобный в использовании.', '1990.00', 0),
(5, 2, 'IPHONE13CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Премиум чехол для iPhone 13 красного цвета. Защита от ударов по всему периметру.', '1590.00', 1),
(6, 2, 'IPHONE13CASE', 'Голубой', '#4285f4', '/img/products/8 белый.PNG', 'Голубой чехол для iPhone 13. Повышенная прочность при небольшой толщине.', '1590.00', 0),
(7, 2, 'IPHONE13CASE', 'Розовое золото', '#ffc0cb', '/img/products/8 розовое золото.PNG', 'Элегантный чехол цвета розовое золото для iPhone 13. Идеальная совместимость со всеми функциями.', '1790.00', 0),
(8, 3, 'GALAXYS23CASE', 'Красный', '#ff5252', '/img/products/8 красный.PNG', 'Чехол для Samsung Galaxy S23 красного цвета. Точное соответствие всем портам и кнопкам.', '1490.00', 0),
(9, 3, 'GALAXYS23CASE', 'Белый', '#ffffff', '/img/products/8 белый.PNG', 'Белый чехол для Samsung Galaxy S23. Минималистичный дизайн, максимальная защита.', '1490.00', 1),
(10, 3, 'GALAXYS23CASE', 'Розовое золото', '#ffc0cb', '/img/products/8 розовое золото.PNG', 'Чехол цвета розовое золото для Samsung Galaxy S23. Улучшенная защита камеры.', '1690.00', 0),
(11, 4, 'XIAOMI13CASE', 'Красный', '#ff5252', '/img/products/14 red.jpg', 'Стильный красный чехол для Xiaomi 13. Прочный материал и отличное качество сборки.', '1290.00', 1),
(12, 4, 'XIAOMI13CASE', 'Черный', '#000000', '/img/products/8 розовое золото.PNG', 'Классический черный чехол для Xiaomi 13. Идеально подходит для повседневного использования.', '1290.00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(10, 'Михаил Калашников', 'reb@mail.ru', '+79053024129', 'Rebiks', '$2y$10$kAmZdsq8N/pY7rTK5bJcFulB4p/1aPHxWiPKKV47VGjia5OjNxDWm', 'Администратор', '1815051685', 'MihanKT', NULL, NULL),
(11, 'Филимонов Денис Юрьевич', 'Alfavitden@gmail.com', '+7 (962) 276-39-27', 'Alfavit', '$2y$10$LRXHaw7zHfHo3dbg2fYkXujBJ8u7WvPe2eLNbHrcNhQVvct.pz1Ae', 'Администратор', NULL, NULL, NULL, NULL),
(12, 'd', 'sobak12a@mail.ru', '+8 (900) 000-00-00', 'D', '$2y$10$XBNAw/lYDjuHNVZX97/TFeAvzRTMhVYSe.K4sFiqK8y.pxTVcGTnq', 'Администратор', NULL, NULL, NULL, NULL),
(13, 'sfsdgdsg', 'mail@mail.ru', '+7 (948) 634-86-43', 'MGE_BRAT', '$2y$10$sUezBqxBkjrmfkgMm1xEJu7LQJ.2/BPuE.3xI15zncH.GOI7YJqc2', 'user', '1356118709', 'chelovek_rek', NULL, NULL),
(14, 'Филимонов Денис Юрьевич', 'den.filia9@mail.ru', '+7 (962) 276-39-27', 'testme', '$2y$10$B5XiqhX5tjYq4vS9Wuv2H.V6ssoVe5JPg9hYEdOwZvlc1feM3iZVu', 'user', NULL, NULL, NULL, NULL),
(15, 'Марина', 'prosekova.mp@bk.ru', '+7 (999) 582-69-43', 'prosekova.mp', '$2y$10$MpIyAjly6XwV49QpuXbZWO1GRY11jp1gUq4/QgRMfra6xmQWD8Nze', 'user', '970596360', 'prosekova_mp', NULL, NULL);

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
-- Индексы таблицы `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT для таблицы `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `telegram_verification_codes`
--
ALTER TABLE `telegram_verification_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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

--
-- Ограничения внешнего ключа таблицы `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
