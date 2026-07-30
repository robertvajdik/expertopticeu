<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/mail.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

/* Ensure reset columns exist. Safe to run on every request — MariaDB / MySQL 8.0.29+
   support IF NOT EXISTS; older versions throw, so we swallow the exception. */
try {
    db()->exec("ALTER TABLE admin_users
                ADD COLUMN IF NOT EXISTS reset_token_hash VARCHAR(64) NULL,
                ADD COLUMN IF NOT EXISTS reset_expires DATETIME NULL");
} catch (Exception $e) {
    /* Ignore — either columns exist or the server lacks IF NOT EXISTS. */
}

$action = $_GET['action'] ?? 'login';
$error  = '';
$notice = '';
$token  = trim($_GET['token'] ?? $_POST['token'] ?? '');

/* ─────────────────────────── forgot password ─────────────────────────── */
if ($action === 'forgot') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        /* Always show the same notice to avoid revealing which emails exist. */
        $notice = 'Wenn ein Konto zu dieser E-Mail existiert, wurde eine Reset-Nachricht gesendet.';
        try {
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stmt = db()->prepare('SELECT id, username, email FROM admin_users WHERE email = ? AND active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $row = $stmt->fetch();
                if ($row) {
                    $raw     = bin2hex(random_bytes(32));
                    $hash    = hash('sha256', $raw);
                    $expires = date('Y-m-d H:i:s', time() + 3600); /* 1 hour */
                    db()->prepare('UPDATE admin_users SET reset_token_hash = ?, reset_expires = ? WHERE id = ?')
                        ->execute([$hash, $expires, $row['id']]);

                    $base = defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : '';
                    $link = $base . '/admin/login.php?action=reset&token=' . $raw;
                    $from = defined('SMTP_FROM') && SMTP_FROM ? SMTP_FROM : 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

                    $subject = 'Passwort zurücksetzen — ' . APP_NAME . ' Admin';
                    $body    = "Hallo {$row['username']},\r\n\r\n"
                             . "über den folgenden Link können Sie ein neues Passwort setzen. Der Link ist 1 Stunde gültig.\r\n\r\n"
                             . "{$link}\r\n\r\n"
                             . "Falls Sie diese Anfrage nicht gestellt haben, ignorieren Sie diese E-Mail.\r\n";
                    _mail_send($row['email'], $subject, $body, $from);
                }
            }
        } catch (Exception $e) {}
        sleep(1);
    }
}

/* ─────────────────────────── reset password ─────────────────────────── */
$reset_user = null;
if ($action === 'reset') {
    if ($token === '') {
        $error = 'Ungültiger oder abgelaufener Link.';
    } else {
        try {
            $hash = hash('sha256', $token);
            $stmt = db()->prepare('SELECT id, username FROM admin_users
                                    WHERE reset_token_hash = ? AND reset_expires > NOW() AND active = 1 LIMIT 1');
            $stmt->execute([$hash]);
            $reset_user = $stmt->fetch();
            if (!$reset_user) $error = 'Ungültiger oder abgelaufener Link.';
        } catch (Exception $e) {
            $error = 'Ungültiger oder abgelaufener Link.';
        }
    }

    if ($reset_user && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pw1 = $_POST['password']  ?? '';
        $pw2 = $_POST['password2'] ?? '';
        if (strlen($pw1) < 8) {
            $error = 'Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($pw1 !== $pw2) {
            $error = 'Die Passwörter stimmen nicht überein.';
        } else {
            $new_hash = password_hash($pw1, PASSWORD_BCRYPT);
            db()->prepare('UPDATE admin_users
                              SET password = ?, reset_token_hash = NULL, reset_expires = NULL
                            WHERE id = ?')
                ->execute([$new_hash, $reset_user['id']]);
            $notice = 'Passwort wurde geändert. Sie können sich jetzt einloggen.';
            $reset_user = null;
            $action = 'login';
        }
    }
}

/* ─────────────────────────── normal login ─────────────────────────── */
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['email']) && !isset($_POST['password2'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $authed = false;

    try {
        $stmt = db()->prepare('SELECT id, username, password, role FROM admin_users WHERE username = ? AND active = 1');
        $stmt->execute([$user]);
        $row = $stmt->fetch();
        if ($row && password_verify($pass, $row['password'])) {
            $authed = true;
            db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')->execute([$row['id']]);
            $_SESSION['admin_user_id'] = (int)$row['id'];
            $_SESSION['admin_role']    = $row['role'];
        }
    } catch (Exception $e) {}

    if ($authed) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: index.php');
        exit;
    }
    $error = 'Benutzername oder Passwort falsch.';
    sleep(1);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — expert·optic</title>
  <link rel="stylesheet" href="admin.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="a-login-wrap">
  <div class="a-login-card">
    <div class="a-login-logo">
      <img src="../assets/logo.png" alt="expert·optic">
      <span>Administration</span>
    </div>

    <?php if ($error): ?>
      <div class="a-login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($notice): ?>
      <div class="a-login-notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <?php if ($action === 'forgot'): ?>
      <form method="post" autocomplete="off">
        <div class="a-form" style="gap:.875rem">
          <div class="a-field">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email" autocomplete="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
          </div>
          <button type="submit" class="a-login-submit">Reset-Link senden</button>
        </div>
      </form>
      <div class="a-login-links">
        <a href="login.php">Zurück zum Login</a>
      </div>

    <?php elseif ($action === 'reset' && $reset_user): ?>
      <form method="post" autocomplete="off">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="a-form" style="gap:.875rem">
          <div class="a-field">
            <label>Benutzer</label>
            <input type="text" value="<?= htmlspecialchars($reset_user['username']) ?>" disabled>
          </div>
          <div class="a-field">
            <label for="password">Neues Passwort</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required minlength="8" autofocus>
          </div>
          <div class="a-field">
            <label for="password2">Passwort wiederholen</label>
            <input type="password" id="password2" name="password2" autocomplete="new-password" required minlength="8">
          </div>
          <button type="submit" class="a-login-submit">Passwort setzen</button>
        </div>
      </form>

    <?php elseif ($action === 'reset'): ?>
      <div class="a-login-links">
        <a href="login.php?action=forgot">Neuen Reset-Link anfordern</a>
        <a href="login.php">Zurück zum Login</a>
      </div>

    <?php else: ?>
      <form method="post" autocomplete="off">
        <div class="a-form" style="gap:.875rem">
          <div class="a-field">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" autocomplete="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
          </div>
          <div class="a-field">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
          </div>
          <button type="submit" class="a-login-submit">Einloggen</button>
        </div>
      </form>
      <div class="a-login-links">
        <a href="login.php?action=forgot">Passwort vergessen?</a>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
