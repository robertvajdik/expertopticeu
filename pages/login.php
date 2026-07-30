<?php
require_once __DIR__ . '/../includes/customer_auth.php';

$action = $_GET['action'] ?? '';
$error  = '';
$notice = '';

/* ─── Forgot password ─── */
if ($action === 'forgot') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot'])) {
        customer_password_reset_request($_POST['email'] ?? '', $lang, $t);
        /* Always show the same success notice to avoid revealing which emails exist. */
        $notice = $t['auth_reset_sent'] ?? 'Pokud e-mail existuje, odeslali jsme odkaz pro obnovu hesla.';
        sleep(1);
    }
}

/* ─── Reset with token ─── */
$reset_user  = null;
$reset_token = trim($_GET['token'] ?? $_POST['token'] ?? '');
if ($action === 'reset') {
    $reset_user = customer_password_reset_verify($reset_token);
    if (!$reset_user) {
        $error = $t['auth_reset_invalid'] ?? 'Neplatný nebo expirovaný odkaz.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
        $pw1 = (string)($_POST['password']  ?? '');
        $pw2 = (string)($_POST['password2'] ?? '');
        if ($pw1 !== $pw2) {
            $error = $t['auth_reset_mismatch'] ?? 'Hesla se neshodují.';
        } else {
            $err = customer_password_reset_complete((int)$reset_user['id'], $pw1);
            if ($err === 'short') $error = $t['auth_err_pw_short'] ?? 'Heslo musí mít alespoň 8 znaků.';
            elseif ($err === 'weak') $error = $t['auth_err_pw_weak'] ?? 'Heslo musí obsahovat alespoň jedno písmeno a jednu číslici.';
            elseif ($err) $error = $t['auth_err_db'] ?? 'Chyba, zkuste to znovu.';
            else {
                $notice = $t['auth_reset_done'] ?? 'Heslo bylo změněno. Nyní se můžete přihlásit.';
                $reset_user = null;
                $action = '';
            }
        }
    }
}

/* ─── Normal login ─── */
if ($action !== 'forgot' && $action !== 'reset'
    && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
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
if ($action !== 'reset' && customer_current()) {
    header('Location: ?p=account&lang=' . $lang);
    exit;
}
?>

<div class="auth-wrap">
  <?php if ($action === 'forgot'): ?>

    <h1 class="auth-title"><?= htmlspecialchars($t['auth_forgot_title'] ?? 'Zapomenuté heslo') ?></h1>
    <p class="auth-subtitle"><?= htmlspecialchars($t['auth_forgot_lead'] ?? 'Zadejte e-mail účtu a pošleme vám odkaz pro obnovu.') ?></p>

    <?php if ($error): ?>
      <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($notice): ?>
      <div class="auth-notice"><i data-lucide="check-circle"></i><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-card">
      <div class="form-field">
        <label for="fg-email"><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?></label>
        <input id="fg-email" type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <button type="submit" name="forgot" value="1" class="btn btn--primary btn--lg btn--block">
        <?= htmlspecialchars($t['auth_forgot_btn'] ?? 'Odeslat odkaz') ?>
      </button>
    </form>

    <p class="auth-footer">
      <a href="?p=login&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_back_to_login'] ?? 'Zpět na přihlášení') ?></a>
    </p>

  <?php elseif ($action === 'reset' && $reset_user): ?>

    <h1 class="auth-title"><?= htmlspecialchars($t['auth_reset_title'] ?? 'Nové heslo') ?></h1>
    <p class="auth-subtitle"><?= htmlspecialchars($t['auth_reset_lead'] ?? 'Zvolte nové heslo ke svému účtu.') ?></p>

    <?php if ($error): ?>
      <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-card">
      <input type="hidden" name="token" value="<?= htmlspecialchars($reset_token) ?>">
      <div class="form-field">
        <label><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?></label>
        <input type="email" value="<?= htmlspecialchars($reset_user['email']) ?>" disabled>
      </div>
      <div class="form-field">
        <label for="rs-pw"><?= htmlspecialchars($t['auth_password'] ?? 'Heslo') ?></label>
        <input id="rs-pw" type="password" name="password" required minlength="8" autofocus>
        <small class="form-hint"><?= htmlspecialchars($t['auth_pw_hint'] ?? 'Minimálně 8 znaků, alespoň jedno písmeno a jedna číslice.') ?></small>
      </div>
      <div class="form-field">
        <label for="rs-pw2"><?= htmlspecialchars($t['auth_password_confirm'] ?? 'Heslo znovu') ?></label>
        <input id="rs-pw2" type="password" name="password2" required minlength="8">
      </div>
      <button type="submit" name="reset" value="1" class="btn btn--primary btn--lg btn--block">
        <?= htmlspecialchars($t['auth_reset_btn'] ?? 'Nastavit heslo') ?>
      </button>
    </form>

  <?php elseif ($action === 'reset'): ?>

    <h1 class="auth-title"><?= htmlspecialchars($t['auth_reset_title'] ?? 'Nové heslo') ?></h1>
    <?php if ($error): ?>
      <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <p class="auth-footer">
      <a href="?p=login&amp;action=forgot&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_reset_request_new'] ?? 'Vyžádat nový odkaz') ?></a>
      &middot;
      <a href="?p=login&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_back_to_login'] ?? 'Zpět na přihlášení') ?></a>
    </p>

  <?php else: ?>

    <h1 class="auth-title"><?= htmlspecialchars($t['auth_login_title'] ?? 'Přihlášení') ?></h1>
    <p class="auth-subtitle"><?= htmlspecialchars($t['auth_login_lead'] ?? 'Přihlaste se ke svému účtu.') ?></p>

    <?php if ($error): ?>
      <div class="auth-alert"><i data-lucide="alert-triangle"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($notice): ?>
      <div class="auth-notice"><i data-lucide="check-circle"></i><?= htmlspecialchars($notice) ?></div>
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
      <div class="auth-forgot-row">
        <a href="?p=login&amp;action=forgot&amp;lang=<?= $lang ?>" class="auth-forgot-link">
          <?= htmlspecialchars($t['auth_forgot_link'] ?? 'Zapomenuté heslo?') ?>
        </a>
      </div>
      <button type="submit" name="login" value="1" class="btn btn--primary btn--lg btn--block">
        <?= htmlspecialchars($t['auth_login_btn'] ?? 'Přihlásit') ?>
      </button>
    </form>

    <p class="auth-footer">
      <?= htmlspecialchars($t['auth_no_account'] ?? 'Nemáte účet?') ?>
      <a href="?p=register&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['auth_register_here'] ?? 'Zaregistrovat se') ?></a>
    </p>

  <?php endif; ?>
</div>
