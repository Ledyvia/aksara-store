<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

if (isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    header('Location: pesanan.php?msg=updated');
    exit;
}

$filter = $_GET['status'] ?? 'semua';
$sql = 'SELECT * FROM orders';
$params = [];
if ($filter !== 'semua') {
    $sql .= ' WHERE status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

function getItems(PDO $pdo, int $orderId): array {
    $s = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $s->execute([$orderId]);
    return $s->fetchAll();
}

$page = 'pesanan';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Kelola <span>Pesanan</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?><div class="alert-box alert-ok">Status pesanan diperbarui.</div><?php endif; ?>

    <div class="panel">
      <div class="panel-head">
        <h3>Semua Pesanan</h3>
        <div style="display:flex;gap:.5rem">
          <?php foreach (['semua'=>'Semua','pending'=>'Pending','paid'=>'Lunas','cancelled'=>'Batal'] as $k=>$v): ?>
            <a href="pesanan.php?status=<?= $k ?>" class="btn-sm <?= $filter===$k?'primary':'' ?>"><?= $v ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (!$orders): ?>
        <div class="empty-state">Belum ada pesanan untuk filter ini. Catatan: pesanan akan muncul di sini jika halaman checkout toko dihubungkan ke <code>api/checkout.php</code> (lihat README).</div>
      <?php else: ?>
      <table>
        <thead><tr><th>Kode</th><th>Pembeli</th><th>Item</th><th>Total</th><th>Metode</th><th>Status</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): $items = getItems($pdo, $o['id']); ?>
          <tr>
            <td><?= e($o['order_code']) ?></td>
            <td><?= e($o['customer_name']) ?><br><small style="color:var(--muted)"><?= e($o['customer_email']) ?></small></td>
            <td><?= count($items) ?> item</td>
            <td>Rp <?= number_format($o['total'],0,',','.') ?></td>
            <td><?= e($o['payment_method']) ?></td>
            <td>
              <form method="post" style="display:flex;gap:.4rem;align-items:center">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
                <select name="status" class="c-input" style="margin:0;padding:.3rem .5rem;font-size:.75rem" onchange="this.form.submit()">
                  <option value="pending" <?= $o['status']==='pending'?'selected':'' ?>>Pending</option>
                  <option value="paid" <?= $o['status']==='paid'?'selected':'' ?>>Lunas</option>
                  <option value="cancelled" <?= $o['status']==='cancelled'?'selected':'' ?>>Batal</option>
                </select>
              </form>
            </td>
            <td><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>
