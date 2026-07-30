<?php
/* Google reCAPTCHA v3 helper (invisible, score-based).
   Keys are defined as constants in admin/config.php:
     define('RECAPTCHA_SITE_KEY',   '...');
     define('RECAPTCHA_SECRET_KEY', '...');
     define('RECAPTCHA_MIN_SCORE',  0.5);  // optional, defaults to 0.5
   Leave the site/secret empty to disable reCAPTCHA. */

if (!defined('RECAPTCHA_SITE_KEY')) {
    @include_once __DIR__ . '/../admin/config.php';
}

function recaptcha_site_key(): string {
    return defined('RECAPTCHA_SITE_KEY') ? trim((string)RECAPTCHA_SITE_KEY) : '';
}

function recaptcha_secret_key(): string {
    return defined('RECAPTCHA_SECRET_KEY') ? trim((string)RECAPTCHA_SECRET_KEY) : '';
}

function recaptcha_min_score(): float {
    if (defined('RECAPTCHA_MIN_SCORE')) {
        $v = (float)RECAPTCHA_MIN_SCORE;
        if ($v > 0 && $v <= 1) return $v;
    }
    return 0.5;
}

function recaptcha_enabled(): bool {
    return recaptcha_site_key() !== '' && recaptcha_secret_key() !== '';
}

/* Server-side verification.
   Returns true when reCAPTCHA is disabled, or when the token is valid,
   the action matches, and the score is above RECAPTCHA_MIN_SCORE.
   Pass $expectedAction to verify the token was created for the right form
   (e.g. 'contact', 'newsletter', 'booking'). Pass null to skip the check. */
function recaptcha_verify(?string $token, ?string $expectedAction = null, ?string $remoteIp = null): bool {
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
    if (!is_array($data) || empty($data['success'])) return false;

    /* v3-specific checks */
    if ($expectedAction !== null && (($data['action'] ?? '') !== $expectedAction)) return false;
    $score = isset($data['score']) ? (float)$data['score'] : 0.0;
    if ($score < recaptcha_min_score()) return false;

    return true;
}

/* Emit the reCAPTCHA v3 script once per response. Safe to call multiple times. */
function recaptcha_script_tag(): string {
    if (!recaptcha_enabled()) return '';
    static $emitted = false;
    if ($emitted) return '';
    $emitted = true;
    return '<script src="https://www.google.com/recaptcha/api.js?render='
         . htmlspecialchars(recaptcha_site_key(), ENT_QUOTES)
         . '" async defer></script>';
}

/* Emit a hidden input + submit-handler snippet for a specific form action.
   Place this INSIDE the <form> element. The action name identifies the form
   in the Google admin dashboard analytics; pick something short like
   'contact', 'newsletter', 'booking', 'login'. */
function recaptcha_field(string $action = 'submit'): string {
    if (!recaptcha_enabled()) return '';
    $site = htmlspecialchars(recaptcha_site_key(), ENT_QUOTES);
    $act  = preg_replace('/[^a-z0-9_]/i', '', $action) ?: 'submit';
    /* A unique id per (action, call-count) so multiple forms on one page still work. */
    static $seq = 0;
    $seq++;
    $id = "g-recaptcha-response-{$act}-{$seq}";
    return <<<HTML
<input type="hidden" name="g-recaptcha-response" id="{$id}">
<script>
(function(){
  var input = document.getElementById('{$id}');
  if (!input) return;
  var form = input.closest('form');
  if (!form) return;
  var submitted = false;
  form.addEventListener('submit', function(ev){
    if (submitted) return;
    if (typeof grecaptcha === 'undefined' || !grecaptcha.execute) return;
    ev.preventDefault();
    grecaptcha.ready(function(){
      grecaptcha.execute('{$site}', {action: '{$act}'}).then(function(token){
        input.value = token;
        submitted = true;
        if (typeof form.requestSubmit === 'function') {
          /* Preserve which submit button was clicked. */
          var btn = form.querySelector('button[type=submit]:focus, input[type=submit]:focus');
          form.requestSubmit(btn || undefined);
        } else {
          form.submit();
        }
      }).catch(function(){ submitted = true; form.submit(); });
    });
  }, {capture: true});
})();
</script>
HTML;
}

/* Backward-compat alias for old v2 callers (renders a v3 field instead).
   New code should call recaptcha_field(\$action) directly. */
function recaptcha_widget(string $action = 'submit'): string {
    return recaptcha_field($action);
}
