<?php
/* Google reCAPTCHA v2 (checkbox) helper.
   Keys are defined as constants in admin/config.php:
     define('RECAPTCHA_SITE_KEY',   '...');
     define('RECAPTCHA_SECRET_KEY', '...');
   Leave both empty to disable reCAPTCHA. */

if (!defined('RECAPTCHA_SITE_KEY')) {
    @include_once __DIR__ . '/../admin/config.php';
}

function recaptcha_site_key(): string {
    return defined('RECAPTCHA_SITE_KEY') ? trim((string)RECAPTCHA_SITE_KEY) : '';
}

function recaptcha_secret_key(): string {
    return defined('RECAPTCHA_SECRET_KEY') ? trim((string)RECAPTCHA_SECRET_KEY) : '';
}

function recaptcha_enabled(): bool {
    return recaptcha_site_key() !== '' && recaptcha_secret_key() !== '';
}

/** Server-side verification. Returns true when reCAPTCHA disabled or token valid. */
function recaptcha_verify(?string $token, ?string $remoteIp = null): bool {
    if (!recaptcha_enabled()) return true;
    if (!$token) return false;

    $payload = http_build_query([
        'secret'   => recaptcha_secret_key(),
        'response' => $token,
        'remoteip' => $remoteIp ?? ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => $payload,
            'timeout'       => 5,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) return false;
    $data = json_decode($raw, true);
    return is_array($data) && !empty($data['success']);
}

function recaptcha_script_tag(): string {
    if (!recaptcha_enabled()) return '';
    return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}

function recaptcha_widget(string $theme = 'light', string $size = 'normal'): string {
    if (!recaptcha_enabled()) return '';
    return sprintf(
        '<div class="g-recaptcha" data-sitekey="%s" data-theme="%s" data-size="%s"></div>',
        htmlspecialchars(recaptcha_site_key(), ENT_QUOTES),
        htmlspecialchars($theme, ENT_QUOTES),
        htmlspecialchars($size, ENT_QUOTES)
    );
}
