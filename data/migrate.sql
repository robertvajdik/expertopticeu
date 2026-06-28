-- Migration for d388325_expopt (md424.wedos.net)
-- Safe to run against the live database exported 2026-06-28
-- Uses IF NOT EXISTS / IF EXISTS / INSERT IGNORE for idempotency

USE d388325_expopt;

-- --------------------------------------------------------
-- 1. orders: drop old address columns, add shipping columns
-- --------------------------------------------------------

ALTER TABLE `orders`
  DROP COLUMN IF EXISTS `address`,
  DROP COLUMN IF EXISTS `city`,
  DROP COLUMN IF EXISTS `postal_code`,
  DROP COLUMN IF EXISTS `country`;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `shipping_method`   ENUM('personal','balikovna','balikovna_home') NOT NULL DEFAULT 'personal' AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `shipping_cost`     DECIMAL(10,2) NOT NULL DEFAULT 0              AFTER `shipping_method`,
  ADD COLUMN IF NOT EXISTS `pickup_point_id`   VARCHAR(20)   DEFAULT NULL                   AFTER `shipping_cost`,
  ADD COLUMN IF NOT EXISTS `pickup_point_name` VARCHAR(200)  DEFAULT NULL                   AFTER `pickup_point_id`,
  ADD COLUMN IF NOT EXISTS `delivery_address`  VARCHAR(255)  DEFAULT NULL                   AFTER `pickup_point_name`,
  ADD COLUMN IF NOT EXISTS `tracking_number`   VARCHAR(50)   DEFAULT NULL                   AFTER `delivery_address`;

-- If shipping_method column already existed with old ENUM, update it:
-- ALTER TABLE `orders` MODIFY COLUMN `shipping_method` ENUM('personal','balikovna','balikovna_home') NOT NULL DEFAULT 'personal';

-- --------------------------------------------------------
-- 2. carts
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `carts` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(128)  NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` DATETIME      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session` (`session_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. cart_items
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cart_items` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `cart_id`    INT UNSIGNED     NOT NULL,
  `product_id` VARCHAR(32)      NOT NULL,
  `quantity`   TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `price`      DECIMAL(10,2)    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_product` (`cart_id`, `product_id`),
  CONSTRAINT `fk_cart_items_cart`    FOREIGN KEY (`cart_id`)    REFERENCES `carts`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. balikovna pickup points cache
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `balikovna_points` (
  `id`        VARCHAR(20)   NOT NULL,
  `name`      VARCHAR(200)  NOT NULL,
  `street`    VARCHAR(200)  DEFAULT NULL,
  `city`      VARCHAR(100)  DEFAULT NULL,
  `zip`       VARCHAR(10)   DEFAULT NULL,
  `hours`     VARCHAR(255)  DEFAULT NULL,
  `lat`       DECIMAL(10,7) DEFAULT NULL,
  `lng`       DECIMAL(10,7) DEFAULT NULL,
  `active`    TINYINT(1)    NOT NULL DEFAULT 1,
  `synced_at` TIMESTAMP     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_city` (`city`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. settings: update changed values + add new
-- --------------------------------------------------------

UPDATE `settings` SET `value` = 'Expert OPTIC Brýlové studio' WHERE `key` = 'company_name';
UPDATE `settings` SET `value` = 'Brno-Komín, Hlavní 131'      WHERE `key` = 'address';

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('company_subtitle',      'Optické a optometristické studio'),
  ('owner',                 'Thomas Scheibl'),
  ('transport_stop',        'Zastávka „Svratecká"'),
  ('transport_tram',        '1, 3, 11'),
  ('transport_bus',         '30, 36, s88'),
  ('parking',               'Bezplatné parkoviště pro zákazníky'),
  ('tagline',               'Vaše úplně soukromé brýlové studio'),
  ('slogan',                'Pokud máte dost nudných obrub… Design your face!'),
  ('page_unikaty',          'https://www.expertoptic.eu/unikaty.html'),
  ('img_studio_1',          'assets/studio/jdlj-5udun6iryud8-zbcw1.jpg'),
  ('img_studio_2',          'assets/studio/jdlj-yakkbos6rov-q529b.jpg'),
  ('img_studio_3',          'assets/studio/jdlj-5ey5rtoefv6-49m53.jpg'),
  ('img_studio_4',          'assets/studio/jdlj-p1svrqt61ua-2u7d.jpg'),
  ('img_studio_5',          'assets/studio/jdlj-2eh3hs2b7wdk-b1u9o.jpg'),
  -- Shipping prices (Kč)
  ('shipping_personal',     '0'),
  ('shipping_balikovna',    '89'),
  ('shipping_balikovna_home','119');

-- --------------------------------------------------------
-- 6. admin_users: fix eshop email
-- --------------------------------------------------------

UPDATE `admin_users` SET `email` = 'brno@tstoptik.com' WHERE `username` = 'eshop';
