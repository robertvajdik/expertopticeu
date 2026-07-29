<?php
$msg   = '';
$error = '';
$edit  = null;
$cats  = ['Optische Brillen','Sonnenbrillen','Sportbrillen','Lesebrillen','Kontaktlinsen'];

/* ── CSV export ── ?export=csv */
if (($_GET['export'] ?? '') === 'csv') {
    while (ob_get_level() > 0) ob_end_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="products-' . date('Ymd') . '.csv"');
    header('Cache-Control: no-store, max-age=0');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

    fputcsv($out, ['id','brand','name','cat','price','color','tag','img'], ';');

    $rows = db()->query('SELECT id, brand, name, cat, price, color, tag, img FROM products ORDER BY brand, name')->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['brand'],
            $r['name'],
            $r['cat'],
            $r['price'],
            $r['color'],
            $r['tag'],
            $r['img'],
        ], ';');
    }
    fclose($out);
    exit;
}

define('UPLOAD_DIR',      __DIR__ . '/../../assets/products/');
define('UPLOAD_WEB_BASE', 'assets/products/');
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

/* ── Ensure upload directory exists ── */
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

/* ── Save a product to DB ── */
function save_product_db(array $p): void {
    db()->prepare('
        INSERT INTO products (id, brand, name, cat, price, color, tag, img)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          brand = VALUES(brand), name  = VALUES(name),
          cat   = VALUES(cat),   price = VALUES(price),
          color = VALUES(color), tag   = VALUES(tag),
          img   = VALUES(img)
    ')->execute([
        $p['id'], $p['brand'], $p['name'], $p['cat'],
        $p['price'], $p['color'], $p['tag'] ?? null, $p['img'] ?? null,
    ]);
}

function delete_product_db(string $id): void {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
}

function reload_products(): array {
    return db()->query('SELECT * FROM products ORDER BY brand, name')->fetchAll();
}

/* ── Handle POST actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Save (add or edit) ── */
    if ($action === 'save') {
        $id    = trim($_POST['id'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $name  = trim($_POST['name']  ?? '');
        $cat   = trim($_POST['cat']   ?? '');
        $price = trim($_POST['price'] ?? '');
        $color = trim($_POST['color'] ?? '#000000');
        $tag   = trim($_POST['tag']   ?? '') ?: null;

        if (!$brand || !$name || !$cat || !$price) {
            $error = $al['prod_required_err'];
        } else {
            /* Determine / generate ID */
            if (!$id) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $brand . '-' . $name));
                $id   = substr(trim($slug, '-'), 0, 24);
            }

            /* Carry forward existing img path */
            $img = null;
            foreach ($products as $p) {
                if ($p['id'] === $id) { $img = $p['img'] ?? null; break; }
            }

            /* ── Handle photo removal ── */
            if (!empty($_POST['remove_photo']) && $img) {
                $old = UPLOAD_DIR . basename($img);
                if (file_exists($old)) @unlink($old);
                $img = null;
            }

            /* ── Handle photo upload ── */
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $mime = mime_content_type($_FILES['photo']['tmp_name']);
                $size = $_FILES['photo']['size'];

                if (!in_array($mime, $ALLOWED_MIME, true)) {
                    $error = $al['prod_photo_err_type'];
                } elseif ($size > UPLOAD_MAX_BYTES) {
                    $error = $al['prod_photo_err_size'];
                } else {
                    $ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
                    $filename = $id . '.' . $ext;

                    /* Remove old photo if extension changes */
                    if ($img && basename($img) !== $filename) {
                        $old = UPLOAD_DIR . basename($img);
                        if (file_exists($old)) @unlink($old);
                    }

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $filename)) {
                        $img = UPLOAD_WEB_BASE . $filename;
                    } else {
                        $error = $al['prod_photo_err_move'];
                    }
                }
            }

            if (!$error) {
                $found  = false;
                foreach ($products as $p) {
                    if ($p['id'] === $id) { $found = true; break; }
                }
                $record = compact('id', 'brand', 'name', 'cat', 'price', 'color', 'tag', 'img');
                save_product_db($record);
                $products = reload_products();
                $msg = $found ? $al['prod_saved_msg'] : $al['prod_added_msg'];
            }
        }
    }

    /* ── Delete ── */
    if ($action === 'delete') {
        $del_id = $_POST['del_id'] ?? '';
        foreach ($products as $p) {
            if ($p['id'] === $del_id && !empty($p['img'])) {
                $old = UPLOAD_DIR . basename($p['img']);
                if (file_exists($old)) @unlink($old);
            }
        }
        delete_product_db($del_id);
        $products = reload_products();
        $msg = $al['prod_deleted_msg'];
    }
}

/* ── Inline edit pre-fill ── */
$edit_id = $_GET['edit'] ?? null;
if ($edit_id) {
    foreach ($products as $p) {
        if ($p['id'] === $edit_id) { $edit = $p; break; }
    }
}
?>

<?php if ($msg):   ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="a-alert a-alert--error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Add / Edit form -->
<div class="a-card" id="product-form-card"
     style="<?= ($edit || $error) ? '' : 'display:none' ?>;margin-bottom:1.375rem">
  <div class="a-card-head">
    <h2 id="form-title">
      <?= $edit ? htmlspecialchars($al['prod_edit_title']) : htmlspecialchars($al['prod_new_title']) ?>
    </h2>
    <button class="a-btn a-btn--outline" onclick="hideProductForm()">
      <?= htmlspecialchars($al['prod_cancel']) ?>
    </button>
  </div>
  <div class="a-card-body">
    <form method="post" enctype="multipart/form-data" class="a-form">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id"     value="<?= htmlspecialchars($edit['id'] ?? '') ?>">

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_brand']) ?></label>
          <input type="text" name="brand"
                 value="<?= htmlspecialchars($edit['brand'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['prod_brand_ph']) ?>" required>
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_model']) ?></label>
          <input type="text" name="name"
                 value="<?= htmlspecialchars($edit['name'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['prod_model_ph']) ?>" required>
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_cat']) ?></label>
          <select name="cat" required>
            <option value=""><?= htmlspecialchars($al['prod_cat_ph']) ?></option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"
                      <?= ($edit['cat'] ?? '') === $c ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_price']) ?></label>
          <input type="text" name="price"
                 value="<?= htmlspecialchars($edit['price'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['prod_price_ph']) ?>" required>
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_color']) ?></label>
          <input type="color" name="color"
                 value="<?= htmlspecialchars($edit['color'] ?? '#2f9bd6') ?>">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['prod_tag']) ?></label>
          <input type="text" name="tag"
                 value="<?= htmlspecialchars($edit['tag'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['prod_tag_ph']) ?>">
        </div>
      </div>

      <!-- Photo upload -->
      <div class="a-form-row">
        <div class="a-field a-field--photo">
          <label><?= htmlspecialchars($al['prod_photo']) ?></label>

          <?php if (!empty($edit['img'])): ?>
            <div class="a-photo-preview">
              <img src="../<?= htmlspecialchars($edit['img']) ?>"
                   alt="" class="a-photo-thumb">
              <label class="a-photo-remove-label">
                <input type="checkbox" name="remove_photo" value="1" id="chk-remove-photo"
                       onchange="togglePhotoInput(this.checked)">
                <?= htmlspecialchars($al['prod_photo_remove']) ?>
              </label>
            </div>
          <?php endif; ?>

          <div id="photo-input-wrap"<?= !empty($edit['img']) ? ' style="display:none"' : '' ?>>
            <input type="file" name="photo" id="photo-input"
                   accept="image/jpeg,image/png,image/webp"
                   class="a-file-input"
                   onchange="previewPhoto(this)">
            <p class="a-field-hint"><?= htmlspecialchars($al['prod_photo_hint']) ?></p>
            <div id="photo-new-preview" class="a-photo-new-preview" style="display:none">
              <img id="photo-new-img" src="" alt="">
            </div>
          </div>
        </div>
      </div>

      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['prod_save']) ?>
        </button>
        <button type="button" class="a-btn a-btn--outline" onclick="hideProductForm()">
          <?= htmlspecialchars($al['prod_cancel']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Products table -->
<div class="a-card">
  <div class="a-card-head" style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
    <h2 style="margin:0"><?= htmlspecialchars($al['prod_all_title']) ?> (<?= count($products) ?>)</h2>
    <a href="?page=products&export=csv" class="a-btn a-btn--outline"
       title="<?= htmlspecialchars($al['btn_export_csv'] ?? 'Export CSV') ?>">
      <?= icon('external') ?> <?= htmlspecialchars($al['btn_export_csv'] ?? 'Export CSV') ?>
    </a>
  </div>
  <?php if (empty($products)): ?>
    <div class="a-empty">
      <?= icon('glasses') ?>
      <p><?= htmlspecialchars($al['prod_empty']) ?></p>
    </div>
  <?php else: ?>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead>
          <tr>
            <th style="width:56px"><?= htmlspecialchars($al['col_photo']) ?></th>
            <th><?= htmlspecialchars($al['col_brand']) ?></th>
            <th><?= htmlspecialchars($al['col_model']) ?></th>
            <th><?= htmlspecialchars($al['col_cat']) ?></th>
            <th><?= htmlspecialchars($al['col_price']) ?></th>
            <th><?= htmlspecialchars($al['col_tag']) ?></th>
            <th class="a-col-actions"><?= htmlspecialchars($al['col_actions']) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <?php if (!empty($p['img'])): ?>
                  <img src="../<?= htmlspecialchars($p['img']) ?>" alt=""
                       class="a-table-thumb">
                <?php else: ?>
                  <div class="a-table-thumb-empty"
                       style="background:<?= htmlspecialchars($p['color'] ?? '#ccc') ?>22">
                    <span class="a-color-dot"
                          style="background:<?= htmlspecialchars($p['color'] ?? '#ccc') ?>"></span>
                  </div>
                <?php endif; ?>
              </td>
              <td><strong><?= htmlspecialchars($p['brand']) ?></strong></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><span class="a-badge a-badge--cat"><?= htmlspecialchars($p['cat']) ?></span></td>
              <td style="font-variant-numeric:tabular-nums"><?= htmlspecialchars($p['price']) ?></td>
              <td>
                <?php if ($p['tag']): ?>
                  <span class="a-badge a-badge--tag"><?= htmlspecialchars($p['tag']) ?></span>
                <?php endif; ?>
              </td>
              <td class="a-col-actions">
                <a href="?page=products&edit=<?= urlencode($p['id']) ?>"
                   class="a-btn a-btn--outline">
                  <?= icon('edit') ?> <?= htmlspecialchars($al['btn_edit']) ?>
                </a>
                <form method="post" style="display:inline"
                      onsubmit="return confirm(<?= json_encode($al['prod_delete_confirm']) ?>)">
                  <input type="hidden" name="action"  value="delete">
                  <input type="hidden" name="del_id"  value="<?= htmlspecialchars($p['id']) ?>">
                  <button type="submit" class="a-btn a-btn--danger"><?= icon('trash') ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
const _prodNewTitle  = <?= json_encode($al['prod_new_title']) ?>;
const _prodEditTitle = <?= json_encode($al['prod_edit_title']) ?>;

function showProductForm(reset) {
  const card = document.getElementById('product-form-card');
  if (reset) {
    card.querySelector('form').reset();
    card.querySelector('#form-title').textContent = _prodNewTitle;
    card.querySelector('[name=id]').value = '';
    const wrap = card.querySelector('#photo-input-wrap');
    if (wrap) wrap.style.display = '';
    const preview = card.querySelector('#photo-new-preview');
    if (preview) preview.style.display = 'none';
  }
  card.style.display = '';
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function hideProductForm() {
  document.getElementById('product-form-card').style.display = 'none';
}

function togglePhotoInput(removing) {
  const wrap = document.getElementById('photo-input-wrap');
  if (wrap) wrap.style.display = removing ? '' : 'none';
}

function previewPhoto(input) {
  const preview = document.getElementById('photo-new-preview');
  const img     = document.getElementById('photo-new-img');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = '';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.style.display = 'none';
  }
}
</script>
