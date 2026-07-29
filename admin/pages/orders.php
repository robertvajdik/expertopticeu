<?php
require_once __DIR__ . '/../../includes/mail.php';
$msg = '';

/* ── CSV export ──
   ?export=csv           — all orders (respects &filter=)
   ?export=csv&mode=items — flat rows of order-item lines */
if (($_GET['export'] ?? '') === 'csv') {
    while (ob_get_level() > 0) ob_end_clean();

    $exp_filter = $_GET['filter'] ?? 'all';
    $exp_mode   = $_GET['mode']   ?? 'orders';
    $exp_where  = match ($exp_filter) {
        'new'        => "WHERE o.status = 'new'",
        'processing' => "WHERE o.status = 'processing'",
        'shipped'    => "WHERE o.status = 'shipped'",
        'delivered'  => "WHERE o.status = 'delivered'",
        'cancelled'  => "WHERE o.status = 'cancelled'",
        default      => '',
    };

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="orders-' . $exp_filter . '-' . date('Ymd') . '.csv"');
    header('Cache-Control: no-store, max-age=0');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

    if ($exp_mode === 'items') {
        fputcsv($out, [
            'order_number','created_at','status','payment_status',
            'customer_name','email','phone','shipping_method','pickup_point_name',
            'tracking_number','order_total_kc',
            'item_brand','item_name','item_price_kc','item_qty','item_total_kc',
        ], ';');

        $rows = db()->query("
            SELECT o.order_number, o.created_at, o.status, o.payment_status,
                   o.customer_name, o.email, o.phone, o.shipping_method, o.pickup_point_name,
                   o.tracking_number, o.total AS order_total,
                   oi.brand, oi.name, oi.price, oi.quantity
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            {$exp_where}
            ORDER BY o.created_at DESC, oi.id
        ")->fetchAll();

        foreach ($rows as $r) {
            $qty  = (int)($r['quantity'] ?? 0);
            $line = (float)($r['price'] ?? 0) * $qty;
            fputcsv($out, [
                $r['order_number'],
                substr($r['created_at'] ?? '', 0, 19),
                $r['status'],
                $r['payment_status'],
                $r['customer_name'],
                $r['email'],
                $r['phone'],
                $r['shipping_method'],
                $r['pickup_point_name'],
                $r['tracking_number'],
                number_format((float)$r['order_total'], 2, ',', ''),
                $r['brand'],
                $r['name'],
                $r['price'] !== null ? number_format((float)$r['price'], 2, ',', '') : '',
                $qty ?: '',
                $qty > 0 ? number_format($line, 2, ',', '') : '',
            ], ';');
        }
    } else {
        fputcsv($out, [
            'order_number','created_at','status','payment_status',
            'customer_name','email','phone',
            'shipping_method','pickup_point_name','delivery_address',
            'tracking_number','item_count','shipping_kc','total_kc',
            'notes','lang',
        ], ';');

        $has_lang = orders_has_lang();
        $lang_col = $has_lang ? ', o.lang' : '';

        $rows = db()->query("
            SELECT o.order_number, o.created_at, o.status, o.payment_status,
                   o.customer_name, o.email, o.phone,
                   o.shipping_method, o.pickup_point_name, o.delivery_address,
                   o.tracking_number, o.shipping_cost, o.total, o.notes,
                   COUNT(oi.id) AS item_count
                   {$lang_col}
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            {$exp_where}
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ")->fetchAll();

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['order_number'],
                substr($r['created_at'] ?? '', 0, 19),
                $r['status'],
                $r['payment_status'],
                $r['customer_name'],
                $r['email'],
                $r['phone'],
                $r['shipping_method'],
                $r['pickup_point_name'],
                $r['delivery_address'],
                $r['tracking_number'],
                (int)$r['item_count'],
                number_format((float)$r['shipping_cost'], 2, ',', ''),
                number_format((float)$r['total'], 2, ',', ''),
                str_replace(["\r\n", "\n", "\r"], ' | ', (string)($r['notes'] ?? '')),
                $r['lang'] ?? '',
            ], ';');
        }
    }

    fclose($out);
    exit;
}

/* ── Handle actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $oid    = (int)($_POST['oid'] ?? 0);

    if ($action === 'update_status' && $oid) {
        $status = $_POST['status'] ?? '';
        $allowed = ['new','processing','shipped','delivered','cancelled'];
        if (in_array($status, $allowed, true)) {
            db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $oid]);
            $msg = $al['msg_status_updated'];
        }
    }

    if ($action === 'update_tracking' && $oid) {
        $tracking = trim($_POST['tracking_number'] ?? '');
        db()->prepare('UPDATE orders SET tracking_number = ?, status = IF(? != "" AND status = "processing", "shipped", status) WHERE id = ?')
            ->execute([$tracking ?: null, $tracking, $oid]);
        $msg = $al['msg_tracking_saved'];
    }

    if ($action === 'update_payment' && $oid) {
        $ps = $_POST['payment_status'] ?? '';
        if (in_array($ps, ['unpaid','paid','refunded'], true)) {
            $prev = db()->prepare('SELECT payment_status FROM orders WHERE id = ?');
            $prev->execute([$oid]);
            $prev_status = $prev->fetchColumn();

            db()->prepare('UPDATE orders SET payment_status = ? WHERE id = ?')->execute([$ps, $oid]);
            $msg = $al['msg_payment_updated'];

            if ($ps === 'paid' && $prev_status !== 'paid') {
                $cols = 'order_number, customer_name, email, total' . (orders_has_lang() ? ', lang' : '');
                $ord = db()->prepare("SELECT {$cols} FROM orders WHERE id = ?");
                $ord->execute([$oid]);
                if ($order_row = $ord->fetch()) {
                    mail_order_paid_customer($order_row, $order_row['lang'] ?? 'cz');
                }
            }
        }
    }

    if ($msg) {
        header('Location: index.php?page=orders&filter=' . urlencode($_GET['filter'] ?? 'all') . '&msg=' . urlencode($msg));
        exit;
    }
}

if (!$msg && isset($_GET['msg'])) $msg = htmlspecialchars($_GET['msg']);

/* ── Load orders ── */
$filter = $_GET['filter'] ?? 'all';
$where  = match($filter) {
    'new'        => "WHERE o.status = 'new'",
    'processing' => "WHERE o.status = 'processing'",
    'shipped'    => "WHERE o.status = 'shipped'",
    'delivered'  => "WHERE o.status = 'delivered'",
    'cancelled'  => "WHERE o.status = 'cancelled'",
    default      => '',
};

$orders = db()->query("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    {$where}
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();

$counts = db()->query("
    SELECT status, COUNT(*) AS n FROM orders GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$status_labels = [
    'new'        => $al['ord_status_new'],
    'processing' => $al['ord_status_processing'],
    'shipped'    => $al['ord_status_shipped'],
    'delivered'  => $al['ord_status_delivered'],
    'cancelled'  => $al['ord_status_cancelled'],
];
$payment_labels = [
    'unpaid'   => $al['ord_pay_unpaid'],
    'paid'     => $al['ord_pay_paid'],
    'refunded' => $al['ord_pay_refunded'],
];
$shipping_labels = [
    'personal'         => $al['ord_ship_personal'],
    'balikovna'        => $al['ord_ship_balikovna'],
    'balikovna_home'   => $al['ord_ship_balikovna_home'],
];

$status_badge = [
    'new'        => 'a-badge--new',
    'processing' => 'a-badge--processing',
    'shipped'    => 'a-badge--processing',
    'delivered'  => 'a-badge--done',
    'cancelled'  => 'a-badge--danger',
];
$payment_badge = [
    'unpaid'   => 'a-badge--new',
    'paid'     => 'a-badge--done',
    'refunded' => 'a-badge--danger',
];
?>

<?php if ($msg): ?>
  <div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Filter bar -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.125rem">
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <?php
    $filters = [$al['ord_filter_all']] + $status_labels;
    $filter_keys = array_merge(['all'], array_keys($status_labels));
    foreach ($filter_keys as $i => $f):
      $label = $i === 0 ? $al['ord_filter_all'] : $status_labels[$f];
      $cnt   = $f === 'all' ? array_sum($counts) : ($counts[$f] ?? 0);
    ?>
      <a href="?page=orders&filter=<?= $f ?>"
         class="a-btn <?= $filter === $f ? 'a-btn--primary' : 'a-btn--outline' ?>">
        <?= htmlspecialchars($label) ?>
        <?php if ($cnt > 0): ?>
          <span style="background:rgba(255,255,255,.3);padding:0 .35rem;border-radius:999px;font-size:.65rem;margin-left:.15rem"><?= $cnt ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a href="?page=orders&export=csv&filter=<?= htmlspecialchars($filter) ?>"
       class="a-btn a-btn--outline" title="<?= htmlspecialchars($al['btn_export_csv'] ?? 'Export CSV') ?>">
      <?= icon('external') ?> <?= htmlspecialchars($al['btn_export_csv'] ?? 'Export CSV') ?>
    </a>
    <a href="?page=orders&export=csv&mode=items&filter=<?= htmlspecialchars($filter) ?>"
       class="a-btn a-btn--outline" title="<?= htmlspecialchars($al['btn_export_csv_items'] ?? 'Export CSV — položky') ?>">
      <?= icon('external') ?> <?= htmlspecialchars($al['btn_export_csv_items'] ?? 'CSV položky') ?>
    </a>
  </div>
</div>

<div class="a-card">
  <?php if (empty($orders)): ?>
    <div class="a-empty">
      <?= icon('tag') ?>
      <p><?= htmlspecialchars($al['ord_empty']) ?><?= $filter !== 'all' ? ' ' . htmlspecialchars($al['ord_empty_filter']) : '.' ?></p>
    </div>
  <?php else: ?>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead>
          <tr>
            <th><?= htmlspecialchars($al['col_order_no']) ?></th>
            <th><?= htmlspecialchars($al['col_date']) ?></th>
            <th><?= htmlspecialchars($al['col_customer']) ?></th>
            <th><?= htmlspecialchars($al['col_delivery']) ?></th>
            <th><?= htmlspecialchars($al['col_total']) ?></th>
            <th><?= htmlspecialchars($al['col_payment']) ?></th>
            <th><?= htmlspecialchars($al['col_status']) ?></th>
            <th class="a-col-actions"><?= htmlspecialchars($al['col_actions']) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($o['order_number']) ?></strong><br>
                <span style="font-size:.75rem;color:var(--a-muted)"><?= (int)$o['item_count'] ?> <?= htmlspecialchars($al['ord_pieces']) ?></span>
              </td>
              <td style="white-space:nowrap;color:var(--a-muted)">
                <?= htmlspecialchars(substr($o['created_at'], 0, 16)) ?>
              </td>
              <td>
                <strong><?= htmlspecialchars($o['customer_name']) ?></strong><br>
                <a href="mailto:<?= htmlspecialchars($o['email']) ?>" style="font-size:.82rem"><?= htmlspecialchars($o['email']) ?></a>
                <?php if ($o['phone']): ?>
                  <br><a href="tel:<?= htmlspecialchars($o['phone']) ?>" style="font-size:.8rem;color:var(--a-muted)"><?= htmlspecialchars($o['phone']) ?></a>
                <?php endif; ?>
              </td>
              <td style="font-size:.85rem">
                <?= htmlspecialchars($shipping_labels[$o['shipping_method']] ?? $o['shipping_method']) ?>
                <?php if ($o['pickup_point_name']): ?>
                  <br><span style="color:var(--a-muted)"><?= htmlspecialchars($o['pickup_point_name']) ?></span>
                <?php endif; ?>
                <?php if ($o['tracking_number']): ?>
                  <br><span style="color:var(--clr-primary);font-size:.78rem">📦 <?= htmlspecialchars($o['tracking_number']) ?></span>
                <?php endif; ?>
              </td>
              <td style="font-weight:600;white-space:nowrap">
                <?= number_format((float)$o['total'], 2, ',', '.') ?> Kč
              </td>
              <td>
                <form method="post" style="display:flex;gap:.25rem;align-items:center">
                  <input type="hidden" name="action" value="update_payment">
                  <input type="hidden" name="oid"    value="<?= $o['id'] ?>">
                  <select name="payment_status" onchange="this.form.submit()" class="a-btn a-btn--outline"
                          style="padding:.2rem .4rem;font-size:.78rem">
                    <?php foreach ($payment_labels as $ps => $pl): ?>
                      <option value="<?= $ps ?>" <?= $o['payment_status'] === $ps ? 'selected' : '' ?>><?= htmlspecialchars($pl) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <form method="post" style="display:flex;gap:.25rem;align-items:center">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="oid"    value="<?= $o['id'] ?>">
                  <select name="status" onchange="this.form.submit()" class="a-btn a-btn--outline"
                          style="padding:.2rem .4rem;font-size:.78rem">
                    <?php foreach ($status_labels as $ss => $sl): ?>
                      <option value="<?= $ss ?>" <?= $o['status'] === $ss ? 'selected' : '' ?>><?= htmlspecialchars($sl) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td class="a-col-actions">
                <button class="a-btn a-btn--outline" onclick="toggleItems(<?= $o['id'] ?>)"
                        style="font-size:.78rem">
                  <?= icon('tag') ?> <?= htmlspecialchars($al['ord_items_btn']) ?>
                </button>
                <?php if ($o['shipping_method'] === 'balikovna'): ?>
                  <button class="a-btn a-btn--outline" onclick="showTracking(<?= $o['id'] ?>, '<?= htmlspecialchars($o['tracking_number'] ?? '', ENT_QUOTES) ?>')"
                          style="font-size:.78rem">
                    <?= icon('tag') ?> Tracking
                  </button>
                <?php endif; ?>
              </td>
            </tr>

            <!-- Expandable items row -->
            <tr id="items-<?= $o['id'] ?>" style="display:none;background:var(--a-bg)">
              <td colspan="8" style="padding:.75rem 1rem">
                <?php
                $items = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
                $items->execute([$o['id']]);
                $order_items = $items->fetchAll();
                ?>
                <table style="width:100%;font-size:.85rem;border-collapse:collapse">
                  <tr style="color:var(--a-muted);font-size:.75rem">
                    <th style="text-align:left;padding:.25rem .5rem"><?= htmlspecialchars($al['ord_tbl_product']) ?></th>
                    <th style="text-align:right;padding:.25rem .5rem"><?= htmlspecialchars($al['ord_tbl_unit']) ?></th>
                    <th style="text-align:right;padding:.25rem .5rem"><?= htmlspecialchars($al['ord_tbl_qty']) ?></th>
                    <th style="text-align:right;padding:.25rem .5rem"><?= htmlspecialchars($al['ord_tbl_total']) ?></th>
                  </tr>
                  <?php foreach ($order_items as $it): ?>
                    <tr>
                      <td style="padding:.3rem .5rem">
                        <strong><?= htmlspecialchars($it['brand'] . ' ' . $it['name']) ?></strong>
                      </td>
                      <td style="text-align:right;padding:.3rem .5rem"><?= number_format((float)$it['price'], 2, ',', '.') ?> Kč</td>
                      <td style="text-align:right;padding:.3rem .5rem"><?= (int)$it['quantity'] ?></td>
                      <td style="text-align:right;font-weight:600;padding:.3rem .5rem"><?= number_format($it['price'] * $it['quantity'], 2, ',', '.') ?> Kč</td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if ($o['notes']): ?>
                    <tr><td colspan="4" style="padding:.5rem .5rem 0;color:var(--a-muted);font-size:.8rem">
                      📝 <?= htmlspecialchars($o['notes']) ?>
                    </td></tr>
                  <?php endif; ?>
                </table>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Tracking modal -->
<div id="tracking-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:1.5rem;width:360px;max-width:90vw">
    <h3 style="margin:0 0 1rem"><?= htmlspecialchars($al['tracking_title']) ?></h3>
    <form method="post" id="tracking-form">
      <input type="hidden" name="action" value="update_tracking">
      <input type="hidden" name="oid"    id="tracking-oid">
      <input type="text"   name="tracking_number" id="tracking-input"
             class="booking-input" placeholder="<?= htmlspecialchars($al['tracking_ph']) ?>"
             style="margin-bottom:.75rem">
      <div style="display:flex;gap:.5rem;justify-content:flex-end">
        <button type="button" onclick="closeTracking()" class="a-btn a-btn--outline"><?= htmlspecialchars($al['tracking_cancel']) ?></button>
        <button type="submit" class="a-btn a-btn--primary"><?= htmlspecialchars($al['tracking_save']) ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleItems(id) {
  var row = document.getElementById('items-' + id);
  row.style.display = row.style.display === 'none' ? '' : 'none';
}
function showTracking(id, current) {
  document.getElementById('tracking-oid').value   = id;
  document.getElementById('tracking-input').value = current;
  var m = document.getElementById('tracking-modal');
  m.style.display = 'flex';
}
function closeTracking() {
  document.getElementById('tracking-modal').style.display = 'none';
}
document.getElementById('tracking-modal').addEventListener('click', function(e) {
  if (e.target === this) closeTracking();
});
</script>
