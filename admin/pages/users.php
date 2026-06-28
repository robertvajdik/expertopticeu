<?php
$msg   = '';
$error = '';
$edit  = null;

/* ── Handle POST actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Save (add or edit) ── */
    if ($action === 'save') {
        $uid      = (int)($_POST['uid'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $pass     = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['admin', 'editor']) ? $_POST['role'] : 'admin';
        $active   = isset($_POST['active']) ? 1 : 0;

        if (!$username || !$email || (!$uid && !$pass)) {
            $error = $al['usr_err_required'];
        } elseif ($pass && strlen($pass) < 8) {
            $error = $al['usr_err_password'];
        } else {
            try {
                if ($uid) {
                    /* Check for duplicate username/email (excluding self) */
                    $dup = db()->prepare('SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?');
                    $dup->execute([$username, $email, $uid]);
                    $dup_row = $dup->fetch();
                    if ($dup_row) {
                        $error = $al['usr_err_username'];
                    } else {
                        if ($pass) {
                            db()->prepare('UPDATE admin_users SET username=?, email=?, password=?, role=?, active=? WHERE id=?')
                                ->execute([$username, $email, password_hash($pass, PASSWORD_BCRYPT), $role, $active, $uid]);
                        } else {
                            db()->prepare('UPDATE admin_users SET username=?, email=?, role=?, active=? WHERE id=?')
                                ->execute([$username, $email, $role, $active, $uid]);
                        }
                        $msg = $al['usr_saved_msg'];
                    }
                } else {
                    /* New user — check duplicates */
                    $dup = db()->prepare('SELECT id FROM admin_users WHERE username = ? OR email = ?');
                    $dup->execute([$username, $email]);
                    if ($dup->fetch()) {
                        $error = $al['usr_err_username'];
                    } else {
                        db()->prepare('INSERT INTO admin_users (username, email, password, role, active) VALUES (?,?,?,?,?)')
                            ->execute([$username, $email, password_hash($pass, PASSWORD_BCRYPT), $role, $active]);
                        $msg = $al['usr_added_msg'];
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    /* ── Toggle active ── */
    if ($action === 'toggle') {
        $tid = (int)($_POST['toggle_id'] ?? 0);
        try {
            db()->prepare('UPDATE admin_users SET active = NOT active WHERE id = ?')->execute([$tid]);
        } catch (Exception $e) {}
    }

    /* ── Delete ── */
    if ($action === 'delete') {
        $del_id = (int)($_POST['del_id'] ?? 0);
        $my_id  = (int)($_SESSION['admin_user_id'] ?? 0);
        if ($del_id && $del_id === $my_id) {
            $error = $al['usr_err_self'];
        } elseif ($del_id) {
            try {
                db()->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$del_id]);
                $msg = $al['usr_deleted_msg'];
            } catch (Exception $e) {}
        }
    }
}

/* ── Load all users ── */
$users = [];
try {
    $users = db()->query('SELECT id, username, email, role, active, last_login, created_at FROM admin_users ORDER BY id')->fetchAll();
} catch (Exception $e) {}

/* ── Pre-fill edit form ── */
$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id) {
    foreach ($users as $u) {
        if ($u['id'] === $edit_id) { $edit = $u; break; }
    }
}

$is_new = !$edit;
?>

<?php if ($msg):   ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="a-alert a-alert--error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Add / Edit form -->
<div class="a-card" id="user-form-card"
     style="<?= ($edit || $error) ? '' : 'display:none' ?>;margin-bottom:1.375rem">
  <div class="a-card-head">
    <h2 id="usr-form-title">
      <?= $edit ? htmlspecialchars($al['usr_edit_title']) : htmlspecialchars($al['usr_new_title']) ?>
    </h2>
    <button class="a-btn a-btn--outline" onclick="hideUserForm()">
      <?= htmlspecialchars($al['usr_cancel']) ?>
    </button>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="uid"    value="<?= (int)($edit['id'] ?? 0) ?>">

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['usr_username']) ?></label>
          <input type="text" name="username"
                 value="<?= htmlspecialchars($edit['username'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['usr_username_ph']) ?>" required>
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['usr_email']) ?></label>
          <input type="email" name="email"
                 value="<?= htmlspecialchars($edit['email'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['usr_email_ph']) ?>" required>
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= $edit ? htmlspecialchars($al['usr_password']) : htmlspecialchars($al['usr_password_new']) ?></label>
          <input type="password" name="password" autocomplete="new-password"
                 placeholder="<?= $edit ? htmlspecialchars($al['usr_password_ph']) : '' ?>"
                 <?= $edit ? '' : 'required' ?>>
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['usr_role']) ?></label>
          <select name="role">
            <option value="admin"  <?= ($edit['role'] ?? 'admin') === 'admin'  ? 'selected' : '' ?>>
              <?= htmlspecialchars($al['role_admin']) ?>
            </option>
            <option value="editor" <?= ($edit['role'] ?? '') === 'editor' ? 'selected' : '' ?>>
              <?= htmlspecialchars($al['role_editor']) ?>
            </option>
          </select>
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label style="flex-direction:row;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox" name="active" value="1"
                   <?= ($edit['active'] ?? 1) ? 'checked' : '' ?>>
            <?= htmlspecialchars($al['usr_active']) ?>
          </label>
        </div>
      </div>

      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['usr_save']) ?>
        </button>
        <button type="button" class="a-btn a-btn--outline" onclick="hideUserForm()">
          <?= htmlspecialchars($al['usr_cancel']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Users table -->
<div class="a-card">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['usr_all_title']) ?> (<?= count($users) ?>)</h2>
    <button class="a-btn a-btn--primary" onclick="showUserForm()">
      <?= icon('plus') ?> <?= htmlspecialchars($al['usr_new_title']) ?>
    </button>
  </div>
  <?php if (empty($users)): ?>
    <div class="a-empty">
      <?= icon('users') ?>
      <p><?= htmlspecialchars($al['usr_empty']) ?></p>
    </div>
  <?php else: ?>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead>
          <tr>
            <th><?= htmlspecialchars($al['col_username']) ?></th>
            <th><?= htmlspecialchars($al['col_email']) ?></th>
            <th><?= htmlspecialchars($al['col_role']) ?></th>
            <th><?= htmlspecialchars($al['col_active']) ?></th>
            <th><?= htmlspecialchars($al['col_last_login']) ?></th>
            <th class="a-col-actions"><?= htmlspecialchars($al['col_actions']) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u):
            $is_me = ($u['id'] === (int)($_SESSION['admin_user_id'] ?? -1)); ?>
            <tr>
              <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
              <td style="color:var(--a-ink-500)"><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="a-badge a-badge--cat"><?= htmlspecialchars($u['role'] === 'admin' ? $al['role_admin'] : $al['role_editor']) ?></span>
              </td>
              <td>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action"    value="toggle">
                  <input type="hidden" name="toggle_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="a-badge <?= $u['active'] ? 'a-badge--ok' : 'a-badge--off' ?>"
                          style="border:none;cursor:pointer">
                    <?= $u['active'] ? '✓' : '✗' ?>
                  </button>
                </form>
              </td>
              <td style="color:var(--a-ink-500);font-size:.85rem">
                <?= $u['last_login'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($u['last_login']))) : '—' ?>
              </td>
              <td class="a-col-actions">
                <a href="?page=users&edit=<?= $u['id'] ?>" class="a-btn a-btn--outline">
                  <?= icon('edit') ?> <?= htmlspecialchars($al['btn_edit']) ?>
                </a>
                <?php if (!$is_me): ?>
                  <form method="post" style="display:inline"
                        onsubmit="return confirm(<?= json_encode($al['usr_delete_confirm']) ?>)">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="del_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="a-btn a-btn--danger"><?= icon('trash') ?></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
const _usrNewTitle  = <?= json_encode($al['usr_new_title']) ?>;
const _usrEditTitle = <?= json_encode($al['usr_edit_title']) ?>;

function showUserForm(reset) {
  const card = document.getElementById('user-form-card');
  if (reset !== false) {
    card.querySelector('form').reset();
    card.querySelector('#usr-form-title').textContent = _usrNewTitle;
    card.querySelector('[name=uid]').value = '0';
    const passInput = card.querySelector('[name=password]');
    passInput.required = true;
    passInput.placeholder = '';
  }
  card.style.display = '';
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function hideUserForm() {
  document.getElementById('user-form-card').style.display = 'none';
}
</script>
