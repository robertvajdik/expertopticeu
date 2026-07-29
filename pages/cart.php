<?php
require_once __DIR__ . '/../includes/db.php';

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);

    if ($action === 'update' && $item_id) {
        cart_update_qty($item_id, (int)($_POST['qty'] ?? 1));
    } elseif ($action === 'remove' && $item_id) {
        cart_remove($item_id);
    }
    $_SESSION['cart_count'] = cart_count();
    header('Location: ?p=cart&lang=' . $lang);
    exit;
}

$items = cart_items();
$total = cart_total($items);
$fmt_money = fn(float $n): string => fmt_display($n, $lang);
?>

<div class="page-wrap">
  <a href="?p=collection&amp;lang=<?= $lang ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= htmlspecialchars($t['prod_back']) ?>
  </a>

  <h1 class="cart-page-title"><?= htmlspecialchars($t['cart_title']) ?></h1>

  <?php if (empty($items)): ?>
    <div class="alert alert--navy cart-empty">
      <i data-lucide="shopping-bag"></i>
      <div>
        <strong><?= htmlspecialchars($t['cart_empty']) ?></strong><br>
        <a href="?p=collection&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['cart_browse']) ?></a>
      </div>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <!-- Items list -->
      <div class="cart-items">
        <?php foreach ($items as $item): ?>
          <div class="cart-item">

            <!-- Thumbnail -->
            <div class="cart-item__thumb" style="background:<?= htmlspecialchars($item['color']) ?>1a">
              <?php if (!empty($item['img'])): ?>
                <img src="<?= htmlspecialchars($item['img']) ?>" alt="">
              <?php else: ?>
                <i data-lucide="glasses" style="color:<?= htmlspecialchars($item['color']) ?>"></i>
              <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="cart-item__info">
              <p class="cart-item__brand"><?= htmlspecialchars($item['brand']) ?></p>
              <p class="cart-item__name"><?= htmlspecialchars($item['name']) ?></p>
              <p class="cart-item__cat"><?= htmlspecialchars($item['cat']) ?></p>
              <p class="cart-item__price"><?= $fmt_money($item['price'] * $item['quantity']) ?></p>
            </div>

            <!-- Qty + remove -->
            <div class="cart-item__controls">
              <form method="post" class="cart-qty">
                <input type="hidden" name="action"  value="update">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" name="qty" value="<?= $item['quantity'] - 1 ?>"
                        class="btn btn--secondary cart-qty__btn" aria-label="−">−</button>
                <span class="cart-qty__count"><?= $item['quantity'] ?></span>
                <button type="submit" name="qty" value="<?= $item['quantity'] + 1 ?>"
                        class="btn btn--secondary cart-qty__btn" aria-label="+">+</button>
              </form>
              <form method="post">
                <input type="hidden" name="action"  value="remove">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" class="btn btn--secondary cart-remove">
                  <i data-lucide="trash-2"></i>
                  <?= htmlspecialchars($t['cart_remove']) ?>
                </button>
              </form>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="card cart-summary">
        <h2 class="cart-summary__heading"><?= htmlspecialchars($t['cart_summary']) ?></h2>

        <?php foreach ($items as $item): ?>
          <div class="cart-summary__line">
            <span><?= htmlspecialchars($item['brand'] . ' ' . $item['name']) ?> ×<?= $item['quantity'] ?></span>
            <span><?= $fmt_money($item['price'] * $item['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <hr class="cart-summary__divider">

        <div class="cart-summary__total">
          <span><?= htmlspecialchars($t['cart_total']) ?></span>
          <span><?= $fmt_money($total) ?></span>
        </div>
        <p class="cart-summary__vat"><?= htmlspecialchars($t['cart_vat']) ?></p>

        <div class="cart-summary__actions">
          <a href="?p=checkout&amp;lang=<?= $lang ?>" class="btn btn--primary btn--lg btn--block">
            <i data-lucide="arrow-right"></i>
            <?= htmlspecialchars($t['cart_checkout']) ?>
          </a>
          <a href="?p=collection&amp;lang=<?= $lang ?>" class="btn btn--secondary btn--block">
            <?= htmlspecialchars($t['cart_continue']) ?>
          </a>
        </div>
      </div>

    </div>
  <?php endif; ?>
</div>
