<?php
require_once __DIR__ . '/../includes/customer_auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $u = customer_login(strtolower(trim($_POST['email'] ?? '')), (string)($_POST['password'] ?? ''));
    if ($u) {
        $next = $_GET['next'] ?? 'account';
        $safe = in_array($next, $allowed_pages, true) ? $next : 'account';
        header('Location: ?p=' . $safe . '&lang=' . $lang);
        exit;
    }
    $error = $t['auth_err_login'] ?? 'Nesprávný e-mail nebo heslo.';
    sleep(1);
}
if (customer_current()) {
    header('Location: ?p=account&lang=' . $lang);
    exit;
}
?>

<div class="page-wrap" style="max-width:480px">
  <h1 class="cart-page-title"><?= htmlspecialchars($t['auth_login_title'] ?? 'Přihlášení') ?></h1>

  <?php if ($error): ?>
    <div class="alert alert--danger" style="margin-bottom:1rem"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" class="card" style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem">
    <label>
      <div><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?></div>
      <input type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label>
    <label>
      <div><?= htmlspecialchars($t['auth_password'] ?? 'Heslo') ?></div>
      <input type="password" name="password" required>
    </label>
    <button type="submit" name="login" value="1" class="btn btn--primary btn--lg btn--block">
      <?= htmlspecialchars($t['auth_login_btn'] ?? 'Přihlásit') ?>
    </button>
    <p style="text-align:center;margin:0">
      <?= htmlspecialchars($t['auth_no_account'] ?? 'Nemáte účet?') ?>
      <a href="?p=register&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_register_here'] ?? 'Zaregistrovat se') ?></a>
    </p>
  </form>
</div>
