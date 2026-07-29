<?php
require_once __DIR__ . '/../includes/customer_auth.php';

$errors = [];
$done   = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $r = customer_register($_POST, $lang);
    if (!empty($r['errors'])) {
        $errors = $r['errors'];
    } else {
        $done = true;
        header('Location: ?p=account&lang=' . $lang);
        exit;
    }
}
$cur = customer_current();
if ($cur) {
    header('Location: ?p=account&lang=' . $lang);
    exit;
}

$err_msg = [
    'email'         => $t['auth_err_email']         ?? 'Zadejte platný e-mail.',
    'name'          => $t['auth_err_name']          ?? 'Zadejte jméno (alespoň 2 znaky).',
    'password_short'=> $t['auth_err_pw_short']      ?? 'Heslo musí mít alespoň 8 znaků.',
    'password_weak' => $t['auth_err_pw_weak']       ?? 'Heslo musí obsahovat písmeno i číslici.',
    'phone'         => $t['auth_err_phone']         ?? 'Zadejte platné telefonní číslo.',
    'street'        => $t['auth_err_street']        ?? 'Ulice je příliš dlouhá.',
    'city_postal'   => $t['auth_err_city']          ?? 'PSČ a město jsou příliš dlouhé.',
    'email_taken'   => $t['auth_err_email_taken']   ?? 'Tento e-mail je již registrovaný.',
    'db_schema'     => $t['auth_err_db_schema']     ?? 'Databáze není připravena. Kontaktujte správce.',
    'db'            => $t['auth_err_db']            ?? 'Registrace se nezdařila. Zkuste to znovu.',
];

$has = fn(string $k): bool => in_array($k, $errors, true);
$err_for = function(string $field) use ($errors, $err_msg): string {
    foreach ($errors as $e) {
        if ($e === $field || ($field === 'password' && ($e === 'password_short' || $e === 'password_weak'))) {
            return $err_msg[$e] ?? $e;
        }
    }
    return '';
};
?>

<div class="auth-wrap auth-wrap--wide">
  <h1 class="auth-title"><?= htmlspecialchars($t['auth_register_title'] ?? 'Registrace') ?></h1>
  <p class="auth-subtitle"><?= htmlspecialchars($t['auth_register_lead'] ?? 'Vytvořte si účet pro rychlejší objednávky a slevy.') ?></p>

  <?php if ($has('db') || $has('db_schema')): ?>
    <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($err_msg[$has('db_schema') ? 'db_schema' : 'db']) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-card" novalidate>
    <div class="form-field<?= $err_for('name') ? ' form-field--error' : '' ?>">
      <label for="reg-name"><?= htmlspecialchars($t['auth_name'] ?? 'Jméno') ?> *</label>
      <input id="reg-name" type="text" name="name" required minlength="2" maxlength="150"
             value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      <?php if ($e = $err_for('name')): ?><p class="form-field__error"><?= htmlspecialchars($e) ?></p><?php endif; ?>
    </div>

    <div class="form-field<?= $err_for('email') || $err_for('email_taken') ? ' form-field--error' : '' ?>">
      <label for="reg-email"><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?> *</label>
      <input id="reg-email" type="email" name="email" required maxlength="150"
             autocomplete="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <?php if ($e = ($err_for('email') ?: $err_for('email_taken'))): ?><p class="form-field__error"><?= htmlspecialchars($e) ?></p><?php endif; ?>
    </div>

    <div class="form-field<?= $err_for('password') ? ' form-field--error' : '' ?>">
      <label for="reg-password"><?= htmlspecialchars($t['auth_password'] ?? 'Heslo') ?> *</label>
      <input id="reg-password" type="password" name="password" required minlength="8"
             autocomplete="new-password"
             pattern="^(?=.*[A-Za-zÀ-ž])(?=.*\d).{8,}$">
      <?php if ($e = $err_for('password')): ?>
        <p class="form-field__error"><?= htmlspecialchars($e) ?></p>
      <?php else: ?>
        <p class="auth-hint"><?= htmlspecialchars($t['auth_pw_hint'] ?? 'Minimálně 8 znaků, alespoň jedno písmeno a jedna číslice.') ?></p>
      <?php endif; ?>
    </div>

    <div class="form-field<?= $err_for('phone') ? ' form-field--error' : '' ?>">
      <label for="reg-phone"><?= htmlspecialchars($t['auth_phone'] ?? 'Telefon') ?></label>
      <input id="reg-phone" type="tel" name="phone" maxlength="25"
             autocomplete="tel"
             pattern="^\+?[0-9 \-().]{6,25}$"
             value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      <?php if ($e = $err_for('phone')): ?><p class="form-field__error"><?= htmlspecialchars($e) ?></p><?php endif; ?>
    </div>

    <div class="form-field">
      <label for="reg-street"><?= htmlspecialchars($t['auth_street'] ?? 'Ulice') ?></label>
      <input id="reg-street" type="text" name="street" maxlength="200"
             autocomplete="street-address"
             value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
    </div>

    <div class="form-field">
      <label for="reg-city"><?= htmlspecialchars($t['auth_city_postal'] ?? 'PSČ a město') ?></label>
      <input id="reg-city" type="text" name="city_postal" maxlength="120"
             autocomplete="postal-code"
             value="<?= htmlspecialchars($_POST['city_postal'] ?? '') ?>">
    </div>

    <button type="submit" name="register" value="1" class="btn btn--primary btn--lg btn--block">
      <?= htmlspecialchars($t['auth_register_btn'] ?? 'Registrovat') ?>
    </button>
  </form>

  <p class="auth-footer">
    <?= htmlspecialchars($t['auth_have_account'] ?? 'Máte účet?') ?>
    <a href="?p=login&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_login_here'] ?? 'Přihlásit') ?></a>
  </p>
</div>

<script>
/* Register form — client-side validation with inline error hints. */
(function () {
  var form = document.querySelector('form.auth-card');
  if (!form) return;

  function labelText(field) {
    var lbl = form.querySelector('label[for="' + field.id + '"]');
    return lbl ? lbl.textContent.replace(/\s*\*\s*$/, '').trim() : '';
  }
  function showError(field, msg) {
    var wrap = field.closest('.form-field');
    if (!wrap) return;
    wrap.classList.add('form-field--error');
    var existing = wrap.querySelector('.form-field__error');
    if (existing) { existing.textContent = msg; return; }
    var p = document.createElement('p');
    p.className = 'form-field__error';
    p.textContent = msg;
    wrap.appendChild(p);
  }
  function clearError(field) {
    var wrap = field.closest('.form-field');
    if (!wrap) return;
    wrap.classList.remove('form-field--error');
    var e = wrap.querySelector('.form-field__error');
    if (e) e.remove();
  }

  form.addEventListener('submit', function (ev) {
    var ok = true;
    var name = form.querySelector('#reg-name');
    var email = form.querySelector('#reg-email');
    var pass = form.querySelector('#reg-password');
    var phone = form.querySelector('#reg-phone');

    [name, email, pass, phone].forEach(clearError);

    if (name.value.trim().length < 2) {
      showError(name, <?= json_encode($err_msg['name']) ?>);
      ok = false;
    }
    var emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRx.test(email.value.trim())) {
      showError(email, <?= json_encode($err_msg['email']) ?>);
      ok = false;
    }
    if (pass.value.length < 8) {
      showError(pass, <?= json_encode($err_msg['password_short']) ?>);
      ok = false;
    } else if (!/[A-Za-zÀ-ž]/.test(pass.value) || !/\d/.test(pass.value)) {
      showError(pass, <?= json_encode($err_msg['password_weak']) ?>);
      ok = false;
    }
    if (phone.value.trim() !== '' && !/^\+?[0-9 \-().]{6,25}$/.test(phone.value.trim())) {
      showError(phone, <?= json_encode($err_msg['phone']) ?>);
      ok = false;
    }

    if (!ok) {
      ev.preventDefault();
      var first = form.querySelector('.form-field--error input');
      if (first) first.focus();
    }
  });

  form.querySelectorAll('input').forEach(function (inp) {
    inp.addEventListener('input', function () { clearError(inp); });
  });
})();
</script>
