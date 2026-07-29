<?php
/* ─────────────────────────────────────────────────────────────
   Mail helpers for order lifecycle notifications.
   All emails are plain-text UTF-8. Rendered in the customer's
   language (customer emails) or Czech (admin notifications).
   ───────────────────────────────────────────────────────────── */

require_once __DIR__ . '/db.php';

/* Lazy load per-language translations for mail templates. */
function _mail_t(string $lang): array {
    static $cache = [];
    if (!isset($cache[$lang])) {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $cache[$lang] = file_exists($file) ? require $file : require __DIR__ . '/../lang/cz.php';
    }
    return $cache[$lang];
}

function _mail_settings(): array {
    $file = __DIR__ . '/../data/settings.json';
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

/* Substitute {placeholders} in a template. */
function _mail_render(string $template, array $vars): string {
    foreach ($vars as $k => $v) {
        $template = str_replace('{' . $k . '}', (string)$v, $template);
    }
    return $template;
}

/* Send via SMTP when SMTP_HOST is configured, otherwise PHP mail(). */
function _mail_send(string $to, string $subject, string $body, string $from): bool {
    $enc_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    if (defined('SMTP_HOST') && SMTP_HOST !== '') {
        return _mail_send_smtp($to, $enc_subject, $body, $from);
    }

    $headers = "From: {$from}\r\n"
             . "Reply-To: {$from}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "MIME-Version: 1.0";
    return @mail($to, $enc_subject, $body, $headers);
}

/* Minimal SMTP client (LOGIN auth, optional STARTTLS/SSL). */
function _mail_send_smtp(string $to, string $enc_subject, string $body, string $from_header): bool {
    $host   = SMTP_HOST;
    $port   = (int)SMTP_PORT;
    $secure = defined('SMTP_SECURE') ? SMTP_SECURE : '';
    $env_from = defined('SMTP_FROM') && SMTP_FROM ? SMTP_FROM : $from_header;
    /* Extract bare address from "Name <a@b>". */
    if (preg_match('/<([^>]+)>/', $env_from, $m)) $env_from = $m[1];
    if (preg_match('/<([^>]+)>/', $to, $m))       $to_addr  = $m[1]; else $to_addr = $to;

    $transport = ($secure === 'ssl') ? "ssl://{$host}" : $host;
    $fp = @fsockopen($transport, $port, $errno, $errstr, 15);
    if (!$fp) return false;
    stream_set_timeout($fp, 15);

    $read = function() use ($fp): string {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function(string $c) use ($fp, $read): string { fwrite($fp, $c . "\r\n"); return $read(); };

    $ok = fn(string $r, int $code): bool => (int)substr($r, 0, 3) === $code;

    $greet = $read();
    if (!$ok($greet, 220)) { fclose($fp); return false; }

    $ehlo_host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (!$ok($cmd("EHLO {$ehlo_host}"), 250)) { fclose($fp); return false; }

    if ($secure === 'tls') {
        if (!$ok($cmd('STARTTLS'), 220)) { fclose($fp); return false; }
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
        if (!$ok($cmd("EHLO {$ehlo_host}"), 250)) { fclose($fp); return false; }
    }

    if (SMTP_USER !== '') {
        if (!$ok($cmd('AUTH LOGIN'), 334))                       { fclose($fp); return false; }
        if (!$ok($cmd(base64_encode(SMTP_USER)), 334))           { fclose($fp); return false; }
        if (!$ok($cmd(base64_encode(SMTP_PASS)), 235))           { fclose($fp); return false; }
    }

    if (!$ok($cmd("MAIL FROM:<{$env_from}>"), 250)) { fclose($fp); return false; }
    if (!$ok($cmd("RCPT TO:<{$to_addr}>"),    250)) { fclose($fp); return false; }
    if (!$ok($cmd('DATA'),                    354)) { fclose($fp); return false; }

    $data = "From: {$from_header}\r\n"
          . "To: {$to}\r\n"
          . "Subject: {$enc_subject}\r\n"
          . "Reply-To: {$from_header}\r\n"
          . "MIME-Version: 1.0\r\n"
          . "Content-Type: text/plain; charset=UTF-8\r\n"
          . "\r\n"
          . str_replace("\r\n.", "\r\n..", str_replace("\n", "\r\n", $body))
          . "\r\n.";
    if (!$ok($cmd($data), 250)) { fclose($fp); return false; }

    $cmd('QUIT');
    fclose($fp);
    return true;
}

/* Build the base site config (address, sender) used by every email. */
function _mail_site_config(): array {
    $s = _mail_settings();
    $sender_addr = $s['site_email'] ?? 'brno@tstoptik.com';
    $sender_name = $s['site_studio_name'] ?? 'Expert OPTIC';
    return [
        'sender'   => sprintf('%s <%s>', $sender_name, $sender_addr),
        'admin_to' => $sender_addr,
        'address'  => trim(($s['site_street'] ?? 'Hlavní 131') . ', ' . ($s['site_city_postal'] ?? '624 00 Brno-Komín')),
        'bank'     => [
            'name' => $s['bank_name'] ?? '',
            'iban' => $s['bank_iban'] ?? '',
            'bic'  => $s['bank_bic']  ?? '',
            'ref'  => $s['bank_ref']  ?? 'Objednavka {nr}',
        ],
    ];
}

/* Format items as plain-text lines. */
function _mail_items_block(array $items, string $lang): string {
    $lines = [];
    foreach ($items as $it) {
        $line = sprintf(
            '  · %s %s ×%d — %s',
            $it['brand'],
            $it['name'],
            (int)$it['quantity'],
            fmt_display((float)$it['price'] * (int)$it['quantity'], $lang)
        );
        $lines[] = $line;
    }
    return implode("\n", $lines);
}

/* ─── Customer: new order placed ─── */
function mail_order_placed_customer(array $order, array $items, string $lang): bool {
    $t    = _mail_t($lang);
    $site = _mail_site_config();
    $bank = $site['bank'];

    $ref     = _mail_render($bank['ref'], ['nr' => $order['order_number']]);
    $itemtxt = _mail_items_block($items, $lang);

    $body_lines = [
        _mail_render($t['mail_hello'],        ['name' => $order['customer_name']]),
        '',
        _mail_render($t['mail_placed_intro'], ['nr'   => $order['order_number']]),
        '',
        $t['mail_items_hdr'],
        $itemtxt,
        '',
        $t['mail_shipping_line'] . ' ' . fmt_display((float)$order['shipping_cost'], $lang),
        $t['mail_total_line']    . ' ' . fmt_display((float)$order['total'], $lang),
    ];

    if (!empty($bank['iban'])) {
        $body_lines[] = '';
        $body_lines[] = $t['mail_pay_hdr'];
        if ($bank['name']) $body_lines[] = $t['mail_pay_holder'] . ' ' . $bank['name'];
        $body_lines[] = $t['mail_pay_iban']   . ' ' . $bank['iban'];
        if ($bank['bic'])  $body_lines[] = $t['mail_pay_bic']    . ' ' . $bank['bic'];
        $body_lines[] = $t['mail_pay_amount'] . ' ' . fmt_display((float)$order['total'], $lang);
        $body_lines[] = $t['mail_pay_ref']    . ' ' . $ref;
        $body_lines[] = '';
        $body_lines[] = $t['mail_pay_qr_note'];
    }

    $body_lines[] = '';
    $body_lines[] = _mail_render($t['mail_signoff'], ['address' => $site['address']]);

    return _mail_send(
        $order['email'],
        _mail_render($t['mail_subj_placed'], ['nr' => $order['order_number']]),
        implode("\n", $body_lines),
        $site['sender']
    );
}

/* ─── Admin: new order placed (always Czech) ─── */
function mail_order_placed_admin(array $order, array $items): bool {
    $site = _mail_site_config();
    if (empty($site['admin_to'])) return false;

    $itemtxt = _mail_items_block($items, 'cz');

    $body_lines = [
        'Nová objednávka: ' . $order['order_number'],
        '',
        'Zákazník: ' . $order['customer_name'],
        'E-mail: '   . $order['email'],
    ];
    if (!empty($order['phone']))            $body_lines[] = 'Telefon: '  . $order['phone'];
    if (!empty($order['pickup_point_name'])) $body_lines[] = 'Výdejní místo: ' . $order['pickup_point_name'];
    if (!empty($order['delivery_address']))  $body_lines[] = 'Adresa: '   . $order['delivery_address'];
    if (!empty($order['notes']))            $body_lines[] = 'Poznámka: ' . $order['notes'];

    $body_lines[] = '';
    $body_lines[] = 'Zboží:';
    $body_lines[] = $itemtxt;
    $body_lines[] = '';
    $body_lines[] = 'Doprava: ' . fmt_display((float)$order['shipping_cost'], 'cz');
    $body_lines[] = 'Celkem:  ' . fmt_display((float)$order['total'], 'cz');
    $body_lines[] = '';
    $body_lines[] = 'Zobrazit v administraci: /admin/?page=orders';

    return _mail_send(
        $site['admin_to'],
        'Nová objednávka ' . $order['order_number'],
        implode("\n", $body_lines),
        $site['sender']
    );
}

/* ─── Customer: payment received (triggered from admin) ─── */
function mail_order_paid_customer(array $order, string $lang): bool {
    $t    = _mail_t($lang);
    $site = _mail_site_config();

    $body_lines = [
        _mail_render($t['mail_hello'],     ['name' => $order['customer_name']]),
        '',
        _mail_render($t['mail_paid_body'], ['nr'   => $order['order_number']]),
        '',
        $t['mail_total_line'] . ' ' . fmt_display((float)$order['total'], $lang),
        '',
        _mail_render($t['mail_signoff'], ['address' => $site['address']]),
    ];

    return _mail_send(
        $order['email'],
        _mail_render($t['mail_subj_paid'], ['nr' => $order['order_number']]),
        implode("\n", $body_lines),
        $site['sender']
    );
}
