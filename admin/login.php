<?php
session_start();
require_once __DIR__ . '/config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $authed = false;

    /* Try DB users first */
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

    /* Fallback: config credentials */
    if (!$authed && $user === ADMIN_USERNAME && $pass === ADMIN_PASSWORD) {
        $authed = true;
        $_SESSION['admin_role'] = 'admin';
    }

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
  </div>
</div>
</body>
</html>
