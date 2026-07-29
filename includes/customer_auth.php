<?php
/* Customer (front-end) auth. Distinct from admin auth (admin/config.php).
   Session key: $_SESSION['customer_id']. */

require_once __DIR__ . '/db.php';

function customer_current(): ?array {
    static $cache = null;
    if ($cache !== null) return $cache ?: null;
    $id = (int)($_SESSION['customer_id'] ?? 0);
    if (!$id) { $cache = false; return null; }
    try {
        $s = db()->prepare('SELECT id, email, name, phone, street, city_postal, lang
                            FROM customers WHERE id = ? AND active = 1');
        $s->execute([$id]);
        $row = $s->fetch();
    } catch (Exception $e) { $row = false; }
    if (!$row) { unset($_SESSION['customer_id']); $cache = false; return null; }
    $cache = $row;
    return $row;
}

function customer_login(string $email, string $password): ?array {
    try {
        $s = db()->prepare('SELECT id, email, name, password, active FROM customers WHERE email = ?');
        $s->execute([$email]);
        $row = $s->fetch();
    } catch (Exception $e) { return null; }
    if (!$row || !$row['active'] || !password_verify($password, $row['password'])) return null;
    db()->prepare('UPDATE customers SET last_login = NOW() WHERE id = ?')->execute([$row['id']]);
    $_SESSION['customer_id'] = (int)$row['id'];
    session_regenerate_id(true);
    return $row;
}

function customer_logout(): void {
    unset($_SESSION['customer_id']);
    session_regenerate_id(true);
}

function customer_register(array $d, string $lang): array {
    $email = strtolower(trim($d['email'] ?? ''));
    $name  = trim($d['name'] ?? '');
    $pass  = (string)($d['password'] ?? '');
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
    if ($name === '')       $errors[] = 'name';
    if (strlen($pass) < 8)  $errors[] = 'password_short';
    if ($errors) return ['errors' => $errors];

    try {
        $dup = db()->prepare('SELECT id FROM customers WHERE email = ?');
        $dup->execute([$email]);
        if ($dup->fetch()) return ['errors' => ['email_taken']];
        db()->prepare('INSERT INTO customers (email, password, name, phone, street, city_postal, lang)
                       VALUES (?,?,?,?,?,?,?)')
            ->execute([
                $email,
                password_hash($pass, PASSWORD_BCRYPT),
                $name,
                trim($d['phone'] ?? '')       ?: null,
                trim($d['street'] ?? '')      ?: null,
                trim($d['city_postal'] ?? '') ?: null,
                $lang,
            ]);
        $id = (int)db()->lastInsertId();
    } catch (Exception $e) {
        return ['errors' => ['db']];
    }
    $_SESSION['customer_id'] = $id;
    session_regenerate_id(true);
    return ['id' => $id];
}

/* ─── Voucher helpers ─── */

/* Return voucher row if $code is valid for the given cart_total_eur, else null.
   Reason returned via $reason for UI messaging. */
function voucher_lookup(string $code, float $cart_total_eur, ?string &$reason = null): ?array {
    $code = strtoupper(trim($code));
    if ($code === '') { $reason = 'empty'; return null; }
    try {
        $s = db()->prepare('SELECT * FROM vouchers WHERE code = ?');
        $s->execute([$code]);
        $v = $s->fetch();
    } catch (Exception $e) { $reason = 'db'; return null; }
    if (!$v)                            { $reason = 'notfound'; return null; }
    if (!$v['active'])                  { $reason = 'inactive'; return null; }
    if ($v['valid_until'] && $v['valid_until'] < date('Y-m-d')) { $reason = 'expired'; return null; }
    if ($v['usage_limit'] !== null && (int)$v['used_count'] >= (int)$v['usage_limit']) { $reason = 'used_up'; return null; }
    /* Voucher min_order is stored in the base internal currency (EUR). */
    if ((float)$v['min_order'] > 0 && $cart_total_eur < (float)$v['min_order']) { $reason = 'min_order'; return null; }
    return $v;
}

function voucher_discount_eur(array $v, float $cart_total_eur): float {
    if ($v['type'] === 'percent') {
        $d = $cart_total_eur * ((float)$v['amount'] / 100);
    } else {
        $d = (float)$v['amount'];
    }
    return max(0, min($d, $cart_total_eur));
}

function voucher_apply(string $code): void {
    $_SESSION['voucher_code'] = strtoupper(trim($code));
}
function voucher_clear(): void { unset($_SESSION['voucher_code']); }
function voucher_current_code(): ?string {
    return isset($_SESSION['voucher_code']) && $_SESSION['voucher_code'] !== ''
        ? $_SESSION['voucher_code'] : null;
}

function voucher_mark_used(string $code): void {
    try {
        db()->prepare('UPDATE vouchers SET used_count = used_count + 1 WHERE code = ?')
            ->execute([strtoupper(trim($code))]);
    } catch (Exception $e) {}
}
