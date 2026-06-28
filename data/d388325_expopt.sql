-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Počítač: md424.wedos.net:3306
-- Vygenerováno: Ned 28. čen 2026, 14:33
-- Verze serveru: 10.11.16-MariaDB-log
-- Verze PHP: 5.4.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `d388325_expopt`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `admin_users`
--

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'admin',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=9 ;

--
-- Vypisuji data pro tabulku `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `role`, `active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'robert.vajdik@gmail.com', 'REPLACE_WITH_HASH', 'admin', 1, NULL, '2026-06-28 12:17:30'),
(2, 'eshop', 'eshop@exportoptic.at', 'REPLACE_WITH_HASH', 'admin', 1, NULL, '2026-06-28 12:17:30');

-- --------------------------------------------------------

--
-- Struktura tabulky `bookings`
--

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `service` varchar(100) NOT NULL,
  `booked_at` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booked_at` (`booked_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` char(2) NOT NULL DEFAULT 'AT',
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `status` enum('new','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `fk_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` varchar(32) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cat` varchar(50) NOT NULL,
  `price` varchar(20) NOT NULL,
  `price_net` varchar(20) DEFAULT NULL,
  `color` char(7) NOT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `img_ai` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cat` (`cat`),
  KEY `idx_brand` (`brand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `products`
--

INSERT INTO `products` (`id`, `brand`, `name`, `cat`, `price`, `price_net`, `color`, `tag`, `description`, `img`, `img_ai`, `active`, `created_at`) VALUES
('bf-aria', 'Blackfin', 'Aria', 'Optische Brillen', '€ 420,–', NULL, '#3b342d', NULL, NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('clic-exec', 'CliC', 'Executive', 'Lesebrillen', '€ 99,–', NULL, '#5b7d91', NULL, NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('etn-roma', 'Etnia Barcelona', 'Roma', 'Optische Brillen', '€ 245,–', NULL, '#a45e2a', NULL, NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('glo-g3', 'Gloryfy', 'G3 Unbreakable', 'Sportbrillen', '€ 159,–', NULL, '#2f5247', 'Bruchsicher', NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('icb-haku', 'IC! Berlin', 'Haku', 'Optische Brillen', '€ 389,–', NULL, '#2f5247', 'Neu', NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('nan-vivi', 'Nannini', 'Vivido', 'Lesebrillen', '€ 79,–', NULL, '#a45e2a', 'Festpreis', NULL, NULL, NULL, 1, '2026-06-28 12:17:30'),
('ser-drive', 'Serengeti', 'Strato', 'Sonnenbrillen', '€ 235,–', '€ 195,83', '#6f3d1c', 'Polarisiert', 'Strato in Satin Silver-Rahmen mit Polar PhD 555NM Linsen', 'assets/24-23-thickbox.jpg', 'assets/24-23-thickbox_ai.jpg', 1, '2026-06-28 12:17:30'),
('vua-glac', 'Vuarnet', 'Glacier', 'Sonnenbrillen', '€ 320,–', NULL, '#1b1714', NULL, NULL, NULL, NULL, 1, '2026-06-28 12:17:30');

-- --------------------------------------------------------

--
-- Struktura tabulky `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `key` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('about', 'Naše brýlové studio je to pro Vás, osobně a exkluzivně. Naším výběrem výjimečného a vybraného designu splňujeme nejvyšší nároky. Domluvte si zdarma poradenský rozhovor.'),
('address', 'Hlavní 131'),
('children_screening', 'Screening očí pro děti. Krátkozrakost/dalekozrakost – včasné rozpoznání a napravení této oční vady je pro Vaše dítě velice důležité a ve spoustě případech rozhoduje o úspěšném studiu, po nástupu dětí do školy.'),
('company_name', 'expertoptic'),
('company_subtitle', 'Optické a optometristické studio'),
('country', 'CZ'),
('email', 'brno@tstoptik.com'),
('equipment', 'moderní zařízení pro test zraku, počítačový autorefaktometr, fundus kamera, tonometr, státně certifikovaný personál'),
('facebook', 'https://www.facebook.com/profile.php?id=100054584974326'),
('hours_fri', '11.30–17.00'),
('hours_wed_thu', '12.30–18.00'),
('img_children', 'schulkinder2.JPG'),
('img_interior', 'eo-ueu.jpg'),
('img_parking', 'parkplatz.png'),
('img_storefront', 'expertoptic-brno.jpg'),
('img_studio_1', 'assets/studio/jdlj-5udun6iryud8-zbcw1.jpg'),
('img_studio_2', 'assets/studio/jdlj-yakkbos6rov-q529b.jpg'),
('img_studio_3', 'assets/studio/jdlj-5ey5rtoefv6-49m53.jpg'),
('img_studio_4', 'assets/studio/jdlj-p1svrqt61ua-2u7d.jpg'),
('img_studio_5', 'assets/studio/jdlj-2eh3hs2b7wdk-b1u9o.jpg'),
('owner', 'Thomas Scheibl'),
('page_unikaty', 'https://www.expertoptic.eu/unikaty.html'),
('parking', 'Bezplatné parkoviště pro zákazníky'),
('phone', '+420 603 419 882'),
('postal_city', '624 00 Brno'),
('slogan', 'Pokud máte dost nudných obrub… Design your face!'),
('tagline', 'Vaše úplně soukromé brýlové studio'),
('transport_bus', '30, 36, s88'),
('transport_stop', 'Zastávka „Svratecká"'),
('transport_tram', '1, 3, 11'),
('website', 'www.expertoptic.eu');

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
