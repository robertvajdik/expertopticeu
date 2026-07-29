-- Migration for d388325_expopt (md424.wedos.net)
-- Idempotent: uses IF NOT EXISTS / IF EXISTS / INSERT IGNORE.

USE d388325_expopt;

-- --------------------------------------------------------
-- 1. orders: shipping + lang + customer + voucher columns
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
  ADD COLUMN IF NOT EXISTS `tracking_number`   VARCHAR(50)   DEFAULT NULL                   AFTER `delivery_address`,
  ADD COLUMN IF NOT EXISTS `lang`              CHAR(2)       NOT NULL DEFAULT 'cz'          AFTER `tracking_number`,
  ADD COLUMN IF NOT EXISTS `customer_id`       INT UNSIGNED  DEFAULT NULL                   AFTER `lang`,
  ADD COLUMN IF NOT EXISTS `voucher_code`      VARCHAR(40)   DEFAULT NULL                   AFTER `customer_id`,
  ADD COLUMN IF NOT EXISTS `voucher_discount`  DECIMAL(10,2) NOT NULL DEFAULT 0              AFTER `voucher_code`;

-- --------------------------------------------------------
-- 2. carts / cart_items / balikovna_points
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
-- 3. customers (front-end user accounts, separate from admin_users)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customers` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(150) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `name`          VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(40)  DEFAULT NULL,
  `street`        VARCHAR(200) DEFAULT NULL,
  `city_postal`   VARCHAR(120) DEFAULT NULL,
  `lang`          CHAR(2)      NOT NULL DEFAULT 'cz',
  `active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login`    DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. vouchers (discount codes applied at checkout)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `vouchers` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(40)  NOT NULL,
  `type`          ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `amount`        DECIMAL(10,2) NOT NULL DEFAULT 0,
  `min_order`     DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valid_until`   DATE         DEFAULT NULL,
  `usage_limit`   INT UNSIGNED DEFAULT NULL,
  `used_count`    INT UNSIGNED NOT NULL DEFAULT 0,
  `active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. newsletter subscribers (opt-in list)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(150) NOT NULL,
  `name`         VARCHAR(150) DEFAULT NULL,
  `lang`         CHAR(2)      NOT NULL DEFAULT 'cz',
  `active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. settings + admin_users fixups
-- --------------------------------------------------------

UPDATE `settings` SET `value` = 'Expert OPTIC Brýlové studio' WHERE `key` = 'company_name';
UPDATE `settings` SET `value` = 'Brno-Komín, Hlavní 131'      WHERE `key` = 'address';

INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('shipping_personal',     '0'),
  ('shipping_balikovna',    '89'),
  ('shipping_balikovna_home','119');

UPDATE `admin_users` SET `email` = 'brno@tstoptik.com' WHERE `username` = 'eshop';
