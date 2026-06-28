<?php
if (!defined('DB_HOST')) {
    define('DB_HOST',    'md424.wedos.net');
    define('DB_NAME',    'd388325_expopt');
    define('DB_USER',    'a388325_expopt');
    define('DB_PASS',    'Tj6QNAaS');
    define('DB_CHARSET', 'utf8mb4');
}

if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return $pdo;
    }
}

/* Parse formatted price string "€ 389,–" or "€ 195,83" → float */
function parse_price(string $price): float {
    preg_match('/(\d+)(?:,(\d{2}))?/', $price, $m);
    if (!$m) return 0.0;
    return (float)(($m[1] ?? '0') . '.' . ($m[2] ?? '00'));
}

/* ── Cart helpers ── */

function cart_get_or_create(): int {
    $sid  = session_id();
    $stmt = db()->prepare('SELECT id FROM carts WHERE session_id = ? AND expires_at > NOW()');
    $stmt->execute([$sid]);
    $row = $stmt->fetch();
    if ($row) return (int)$row['id'];

    db()->prepare('INSERT INTO carts (session_id, expires_at) VALUES (?, DATE_ADD(NOW(), INTERVAL 7 DAY))')
        ->execute([$sid]);
    return (int)db()->lastInsertId();
}

function cart_add(string $product_id, float $price, int $qty = 1): void {
    $cart_id = cart_get_or_create();
    db()->prepare('
        INSERT INTO cart_items (cart_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ')->execute([$cart_id, $product_id, $qty, $price]);
}

function cart_items(): array {
    $stmt = db()->prepare('
        SELECT ci.id, ci.quantity, ci.price,
               p.id AS product_id, p.brand, p.name, p.cat, p.img, p.color, p.tag
        FROM carts c
        JOIN cart_items ci ON ci.cart_id = c.id
        JOIN products   p  ON p.id = ci.product_id
        WHERE c.session_id = ? AND c.expires_at > NOW()
    ');
    $stmt->execute([session_id()]);
    return $stmt->fetchAll();
}

function cart_count(): int {
    $stmt = db()->prepare('
        SELECT COALESCE(SUM(ci.quantity), 0) AS n
        FROM carts c
        JOIN cart_items ci ON ci.cart_id = c.id
        WHERE c.session_id = ? AND c.expires_at > NOW()
    ');
    $stmt->execute([session_id()]);
    return (int)$stmt->fetch()['n'];
}

function cart_update_qty(int $item_id, int $qty): void {
    if ($qty <= 0) {
        db()->prepare('
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            WHERE ci.id = ? AND c.session_id = ?
        ')->execute([$item_id, session_id()]);
    } else {
        db()->prepare('
            UPDATE cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            SET ci.quantity = ?
            WHERE ci.id = ? AND c.session_id = ?
        ')->execute([$qty, $item_id, session_id()]);
    }
}

function cart_remove(int $item_id): void {
    cart_update_qty($item_id, 0);
}

function cart_clear(): void {
    db()->prepare('DELETE FROM carts WHERE session_id = ?')->execute([session_id()]);
}

function cart_total(array $items): float {
    return array_reduce($items, fn($s, $i) => $s + $i['price'] * $i['quantity'], 0.0);
}

function fmt_price(float $amount): string {
    return number_format($amount, 2, ',', '.') . ' Kč';
}
