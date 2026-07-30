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

function customer_update(int $id, array $d): array {
    $name   = trim($d['name']  ?? '');
    $phone  = trim($d['phone']       ?? '');
    $street = trim($d['street']      ?? '');
    $city   = trim($d['city_postal'] ?? '');
    $pw_new = (string)($d['password_new'] ?? '');
    $pw_cur = (string)($d['password_current'] ?? '');

    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 150) $errors[] = 'name';
    if ($phone !== '' && !preg_match('/^\+?[0-9 \-().]{6,25}$/', $phone)) $errors[] = 'phone';
    if (mb_strlen($street) > 200) $errors[] = 'street';
    if (mb_strlen($city)   > 120) $errors[] = 'city_postal';

    if ($pw_new !== '') {
        if (strlen($pw_new) < 8) $errors[] = 'password_short';
        elseif (!preg_match('/[A-Za-zÀ-ž]/u', $pw_new) || !preg_match('/\d/', $pw_new)) $errors[] = 'password_weak';

        try {
            $s = db()->prepare('SELECT password FROM customers WHERE id = ?');
            $s->execute([$id]);
            $row = $s->fetch();
            if (!$row || !password_verify($pw_cur, $row['password'])) $errors[] = 'password_current';
        } catch (Exception $e) { $errors[] = 'db'; }
    }
    if ($errors) return ['errors' => $errors];

    try {
        if ($pw_new !== '') {
            db()->prepare('UPDATE customers
                             SET name = ?, phone = ?, street = ?, city_postal = ?, password = ?
                           WHERE id = ?')
                ->execute([$name, $phone ?: null, $street ?: null, $city ?: null,
                           password_hash($pw_new, PASSWORD_BCRYPT), $id]);
        } else {
            db()->prepare('UPDATE customers
                             SET name = ?, phone = ?, street = ?, city_postal = ?
                           WHERE id = ?')
                ->execute([$name, $phone ?: null, $street ?: null, $city ?: null, $id]);
        }
    } catch (Exception $e) {
        error_log('customer_update: ' . $e->getMessage());
        return ['errors' => ['db']];
    }
    return ['ok' => true];
}

function customer_logout(): void {
    unset($_SESSION['customer_id']);
    session_regenerate_id(true);
}

/* ─── Password reset ─── */

/* One-shot schema migration for the reset columns. Called by the reset flow. */
function _customer_reset_migrate(): void {
    try {
        db()->exec("ALTER TABLE customers
                    ADD COLUMN IF NOT EXISTS reset_token_hash VARCHAR(64) NULL,
                    ADD COLUMN IF NOT EXISTS reset_expires DATETIME NULL");
    } catch (Exception $e) {}
}

/* Send a password-reset email if $email matches an active customer.
   Always silent — caller must show the same UI regardless, to avoid enumeration. */
function customer_password_reset_request(string $email, string $lang, array $t): void {
    _customer_reset_migrate();
    $email = strtolower(trim($email));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;
    try {
        $s = db()->prepare('SELECT id, email, name FROM customers WHERE email = ? AND active = 1');
        $s->execute([$email]);
        $row = $s->fetch();
        if (!$row) return;

        $raw     = bin2hex(random_bytes(32));
        $hash    = hash('sha256', $raw);
        $expires = date('Y-m-d H:i:s', time() + 3600);
        db()->prepare('UPDATE customers SET reset_token_hash = ?, reset_expires = ? WHERE id = ?')
            ->execute([$hash, $expires, $row['id']]);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'exportoptic.eu';
        $path   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $link   = $scheme . '://' . $host . $path . '?p=login&action=reset&lang=' . $lang . '&token=' . $raw;

        require_once __DIR__ . '/mail.php';
        $from    = defined('SMTP_FROM') && SMTP_FROM ? SMTP_FROM : ('no-reply@' . $host);
        $subject = $t['auth_reset_email_subject'] ?? 'Obnovení hesla';
        $body    = _mail_render(
            $t['auth_reset_email_body'] ?? "Dobrý den {name},\r\n\r\npro obnovení hesla klikněte na následující odkaz. Odkaz platí 1 hodinu.\r\n\r\n{link}\r\n\r\nPokud jste o obnovení nežádali, tento e-mail ignorujte.",
            ['name' => $row['name'] ?: $row['email'], 'link' => $link]
        );
        _mail_send($row['email'], $subject, $body, $from);
    } catch (Exception $e) {}
}

/* Verify a raw reset token. Returns customer row (id, email, name) or null. */
function customer_password_reset_verify(string $token): ?array {
    _customer_reset_migrate();
    if ($token === '') return null;
    try {
        $hash = hash('sha256', $token);
        $s = db()->prepare('SELECT id, email, name FROM customers
                             WHERE reset_token_hash = ? AND reset_expires > NOW() AND active = 1
                             LIMIT 1');
        $s->execute([$hash]);
        $row = $s->fetch();
        return $row ?: null;
    } catch (Exception $e) { return null; }
}

/* Finalize the reset. Returns error code ('short' | 'weak' | 'db') or null on success. */
function customer_password_reset_complete(int $id, string $new_password): ?string {
    if (strlen($new_password) < 8) return 'short';
    if (!preg_match('/[A-Za-zÀ-ž]/u', $new_password) || !preg_match('/\d/', $new_password)) return 'weak';
    try {
        db()->prepare('UPDATE customers
                          SET password = ?, reset_token_hash = NULL, reset_expires = NULL
                        WHERE id = ?')
            ->execute([password_hash($new_password, PASSWORD_BCRYPT), $id]);
    } catch (Exception $e) { return 'db'; }
    return null;
}

function customer_register(array $d, string $lang): array {
    $email  = strtolower(trim($d['email']  ?? ''));
    $name   = trim($d['name']  ?? '');
    $pass   = (string)($d['password'] ?? '');
    $phone  = trim($d['phone']       ?? '');
    $street = trim($d['street']      ?? '');
    $city   = trim($d['city_postal'] ?? '');

    $errors = [];

    /* Email */
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'email';
    }

    /* Name — min 2 chars after trim, max 150 */
    if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
        $errors[] = 'name';
    }

    /* Password — min 8 chars, must contain at least one letter and one digit */
    if (strlen($pass) < 8) {
        $errors[] = 'password_short';
    } elseif (!preg_match('/[A-Za-zÀ-ž]/u', $pass) || !preg_match('/\d/', $pass)) {
        $errors[] = 'password_weak';
    }

    /* Phone — optional, but if provided must look like a phone number */
    if ($phone !== '' && !preg_match('/^\+?[0-9 \-().]{6,25}$/', $phone)) {
        $errors[] = 'phone';
    }

    /* Street / city_postal — length caps if provided */
    if (mb_strlen($street) > 200) $errors[] = 'street';
    if (mb_strlen($city)   > 120) $errors[] = 'city_postal';

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
                $phone ?: null,
                $street ?: null,
                $city ?: null,
                $lang,
            ]);
        $id = (int)db()->lastInsertId();
    } catch (Exception $e) {
        /* Detect missing table (migration not run) so the admin can see a clearer error. */
        $msg = $e->getMessage();
        if (stripos($msg, "doesn't exist") !== false || stripos($msg, 'no such table') !== false) {
            return ['errors' => ['db_schema']];
        }
        if (stripos($msg, 'Duplicate') !== false) {
            return ['errors' => ['email_taken']];
        }
        error_log('customer_register: ' . $msg);
        return ['errors' => ['db']];
    }
    $_SESSION['customer_id'] = $id;
    session_regenerate_id(true);
    return ['id' => $id];
}

/* ─── Newsletter helpers ─── */

function newsletter_subscribe(string $email, string $lang = 'cz', ?string $name = null): array {
    $email = strtolower(trim($email));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        return ['errors' => ['email']];
    }
    try {
        db()->prepare('INSERT INTO newsletter_subscribers (email, name, lang, active)
                       VALUES (?, ?, ?, 1)
                       ON DUPLICATE KEY UPDATE active = 1, lang = VALUES(lang), name = COALESCE(VALUES(name), name)')
            ->execute([$email, $name ?: null, $lang]);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (stripos($msg, "doesn't exist") !== false) return ['errors' => ['db_schema']];
        error_log('newsletter_subscribe: ' . $msg);
        return ['errors' => ['db']];
    }
    return ['ok' => true];
}

function newsletter_unsubscribe(string $email): void {
    try {
        db()->prepare('UPDATE newsletter_subscribers SET active = 0 WHERE email = ?')
            ->execute([strtolower(trim($email))]);
    } catch (Exception $e) {}
}

function newsletter_set(int $customer_id, bool $on): void {
    try {
        $s = db()->prepare('SELECT email, name, lang FROM customers WHERE id = ?');
        $s->execute([$customer_id]);
        $row = $s->fetch();
        if (!$row) return;
        if ($on) newsletter_subscribe($row['email'], $row['lang'] ?? 'cz', $row['name']);
        else     newsletter_unsubscribe($row['email']);
    } catch (Exception $e) {}
}

function newsletter_is_subscribed(int $customer_id): bool {
    try {
        $s = db()->prepare('SELECT n.active FROM customers c
                            LEFT JOIN newsletter_subscribers n ON n.email = c.email
                            WHERE c.id = ?');
        $s->execute([$customer_id]);
        $row = $s->fetch();
        return $row && (int)$row['active'] === 1;
    } catch (Exception $e) { return false; }
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
