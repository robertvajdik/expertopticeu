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

<div class="auth-wrap">
  <h1 class="auth-title"><?= htmlspecialchars($t['auth_login_title'] ?? 'Přihlášení') ?></h1>
  <p class="auth-subtitle"><?= htmlspecialchars($t['auth_login_lead'] ?? 'Přihlaste se ke svému účtu.') ?></p>

  <?php if ($error): ?>
    <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-card">
    <div class="form-field">
      <label for="log-email"><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?></label>
      <input id="log-email" type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="log-password"><?= htmlspecialchars($t['auth_password'] ?? 'Heslo') ?></label>
      <input id="log-password" type="password" name="password" required>
    </div>
    <button type="submit" name="login" value="1" class="btn btn--primary btn--lg btn--block">
      <?= htmlspecialchars($t['auth_login_btn'] ?? 'Přihlásit') ?>
    </button>
  </form>

  <p class="auth-footer">
    <?= htmlspecialchars($t['auth_no_account'] ?? 'Nemáte účet?') ?>
    <a href="?p=register&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_register_here'] ?? 'Zaregistrovat se') ?></a>
  </p>
</div>
