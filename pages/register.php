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
    'email'         => $t['auth_err_email']         ?? 'Neplatný e-mail.',
    'name'          => $t['auth_err_name']          ?? 'Zadejte jméno.',
    'password_short'=> $t['auth_err_pw_short']      ?? 'Heslo musí mít alespoň 8 znaků.',
    'email_taken'   => $t['auth_err_email_taken']   ?? 'Tento e-mail je již registrovaný.',
    'db'            => $t['auth_err_db']            ?? 'Registrace se nezdařila. Zkuste to znovu.',
];
?>

<div class="page-wrap" style="max-width:520px">
  <h1 class="cart-page-title"><?= htmlspecialchars($t['auth_register_title'] ?? 'Registrace') ?></h1>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert--danger" style="margin-bottom:1rem"><?= htmlspecialchars($err_msg[$e] ?? $e) ?></div>
  <?php endforeach; ?>

  <form method="post" class="card" style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem">
    <label>
      <div><?= htmlspecialchars($t['auth_name'] ?? 'Jméno') ?> *</div>
      <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?> *</div>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_password'] ?? 'Heslo') ?> * (min. 8)</div>
      <input type="password" name="password" required minlength="8">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_phone'] ?? 'Telefon') ?></div>
      <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_street'] ?? 'Ulice') ?></div>
      <input type="text" name="street" value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_city_postal'] ?? 'PSČ a město') ?></div>
      <input type="text" name="city_postal" value="<?= htmlspecialchars($_POST['city_postal'] ?? '') ?>">
    </label>
    <button type="submit" name="register" value="1" class="btn btn--primary btn--lg btn--block">
      <?= htmlspecialchars($t['auth_register_btn'] ?? 'Registrovat') ?>
    </button>
    <p style="text-align:center;margin:0">
      <?= htmlspecialchars($t['auth_have_account'] ?? 'Máte účet?') ?>
      <a href="?p=login&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_login_here'] ?? 'Přihlásit') ?></a>
    </p>
  </form>
</div>
