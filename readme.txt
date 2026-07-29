expert·optic — exportoptic.eu
================================

Trilingual (AT / EN / CZ) e-shop and booking site for the expert·optic
eyewear studio in Brno-Komín, with a lightweight PHP admin.


Requirements
------------
- PHP 7.4+ (uses arrow functions, typed properties, `match`)
- MySQL / MariaDB with utf8mb4
- Web server that maps `/` to `index.php` (Apache, nginx, WEDOS shared)


Directory layout
----------------
  index.php               Front-end router (?p=<page>&lang=<at|en|cz>)
  includes/
    db.php                PDO connection, cart helpers, price formatters
    header.php            Site header, nav, lang switcher
    footer.php            Site footer
  pages/
    home.php              Landing page
    collection.php        Product grid, filter, sort
    product.php           Product detail + studio-booking modal
    booking.php           General eye-test / consultation booking
    cart.php              Cart view (qty, remove, subtotal)
    checkout.php          Contact + shipping + order placement
    order-confirm.php     Post-order confirmation + payment QR
    contact-lenses.php    Editorial page
    sport-glasses.php     Editorial page
  lang/
    at.php  en.php  cz.php   UI translations + shipping labels
  admin/
    index.php             Admin router (login-gated)
    login.php  logout.php
    config.php            Session / auth bootstrap
    pages/
      dashboard.php  products.php  orders.php  bookings.php
      users.php  settings.php  sitemap.php
    lang/                 Admin UI translations (cz, en, de)
  assets/                 Product photos, studio photos, logo
  css/                    styles.css, site.css
  data/
    d388325_expopt.sql    Baseline schema + seed data
    migrate.sql           Incremental migration
    settings.json         Runtime overrides (contact, shipping, bank)
    bookings.json         Booking submissions (file-based)


Database
--------
Connection constants are defined in `includes/db.php` (host / name / user /
pass). Import in this order:

  1. data/d388325_expopt.sql   — base schema and seed
  2. data/migrate.sql          — post-launch adjustments

Main tables:
  products         id, brand, name, cat, price, price_net, color, tag,
                   description, img, img_ai, active, created_at
  carts            id, session_id, expires_at (7-day TTL)
  cart_items      id, cart_id, product_id, quantity, price (DECIMAL 10,2)
  orders           id, order_number, customer_name, email, phone, total,
                   shipping_method, shipping_cost, pickup_point_id,
                   pickup_point_name, delivery_address, notes, created_at
  orders           …also has `lang` CHAR(2) for language-aware paid
                   confirmation emails (see includes/mail.php)
  order_items      id, order_id, product_id, brand, name, price, quantity
  settings         key/value overrides shown as CMS content
  admin_users      id, username, password_hash, email, role
  balikovna_points cached Czech Post pickup-point list

`products.price` and `products.price_net` are stored as human-formatted
EUR strings (e.g. "€ 420,–", "€ 195,83"). `parse_price()` in
`includes/db.php` extracts the numeric part into a float.


Currency & prices
-----------------
Base internal currency = EUR. Product prices, cart line prices, and order
totals are all held as EUR floats.

`includes/db.php` provides:

  EUR_TO_CZK               constant, currently 25.0
  fmt_display($eur, $lang) → "€ 420,00" for at/en, "10.500 Kč" for cz
  fmt_price_display($str, $lang)  same, but takes the stored EUR string

Display rules:
  lang = at  → EUR, symbol prefix, 2 decimals, comma decimal separator
  lang = en  → EUR (same format as AT)
  lang = cz  → CZK, symbol suffix, whole number, dot thousands separator

Checkout shipping normalisation
  - Admin-set shipping values (data/settings.json) are authored in CZK
    (per the settings.php label) → divided by EUR_TO_CZK internally.
  - Language fallback values (lang/*.php `co_ship_*_cost`) are in the
    language's display currency → converted to EUR only for `cz`.
  - `$cart_total`, `$shipping_cost`, and stored `orders.total` are EUR.

To change the exchange rate, edit `EUR_TO_CZK` in `includes/db.php`.


Routing
-------
Front-end pages via `?p=<page>` (see `$allowed_pages` in index.php):
  home | collection | product | booking | cart | checkout | order-confirm
  contact-lenses | sport-glasses

Language via `?lang=<at|en|cz>` (persisted in session, default `at`).

Admin at `/admin/` — not linked in the visible nav (hidden anchor at
the bottom of `index.php`).


Cart & orders
-------------
- Sessions store `cart_count` for the header badge.
- Carts are keyed by `session_id`, expire after 7 days.
- Placing an order:
    - Writes `orders` + `order_items` in a transaction (incl. customer `lang`).
    - Generates `order_number` as `EO<year><zero-padded id>`.
    - Clears the cart, stashes summary in `$_SESSION['last_order']`.
    - Sends two emails via `includes/mail.php`:
        · customer   — order confirmation with bank details / QR pointer,
                       in the customer's language
        · admin      — new-order notification to `site_email` (Czech)
    - Redirects to `?p=order-confirm`.
- The confirmation page renders a payment QR:
    - `€` → SEPA EPC (EPC002-12)
    - Otherwise → Czech SPD 1.0
- Admin marks payment as `paid` in `admin/pages/orders.php`. On the
  transition unpaid → paid, the customer receives a "payment received"
  email in their language (looked up from `orders.lang`).


Shipping
--------
Three methods, individually toggleable in admin settings:
  personal        Free pickup at the store
  balikovna       Czech Post pickup point (iframe selector)
  balikovna_home  Home delivery via Czech Post

Free-shipping threshold (also authored in CZK) waives shipping when
cart subtotal reaches it.


Booking
-------
Two paths:
  - `pages/booking.php`   General eye test / consultation booking form.
  - `pages/product.php`   Studio try-on modal (per-product booking).

Both append to `data/bookings.json`. Admin lists them under
`admin/pages/bookings.php`.


Admin
-----
Login at `/admin/login.php`. Authenticated area covers:
  Dashboard    KPIs, recent activity
  Products     CRUD + image upload
  Orders       List, detail, status
  Bookings     List of eye-test / studio bookings
  Users        Admin user CRUD (password-hashed)
  Settings     Site info, shipping, bank, GA ID
  Sitemap      Static sitemap generator

Passwords are hashed with `password_hash()`; sessions authenticated in
`admin/config.php`.


Site settings (data/settings.json)
----------------------------------
Editable via the admin Settings page. Keys used at runtime include:
  site_studio_name, site_owner_name, site_street, site_city_postal,
  site_phone, site_email, site_ga_id
  ship_balikovna_cost, ship_balikovna_home_cost, ship_free_threshold
  ship_personal_enabled, ship_balikovna_enabled, ship_balikovna_home_enabled
  ship_personal_address, ship_personal_hours
  bank_name, bank_iban, bank_bic, bank_ref, bank_note


Accessibility
-------------
- Skip-link, aria-labels on nav / icons.
- High-contrast toggle (persisted in localStorage).
- Text-zoom A / AA / AAA (persisted in localStorage).
- Applied before first paint to avoid FOUC.


Deployment notes
----------------
- Runs on WEDOS shared hosting (`md424.wedos.net`).
- No composer dependencies; only vanilla PHP + PDO.
- Uses CDN scripts: lucide icons, qrcodejs on the confirmation page.
- `mail()` for order confirmations — check the server's SMTP config.


Local development
-----------------
- Serve `/exportoptic.eu` with any PHP-capable server.
- Import both SQL files into a local MySQL DB and update the constants
  in `includes/db.php` (or wrap them in an environment guard).
