<?php
require_once __DIR__ . '/../config/session.php';
require_buyer();
require_once __DIR__ . '/../config/database.php';

$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$uid]);
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
      <div class="dash-title">Riwayat <span>Pesanan</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <?php if (!$orders): ?>
      <div class="panel"><div class="empty-state">Belum ada riwayat pesanan. <a href="../index.php">Mulai belanja sekarang</a>.</div></div>
    <?php else: ?>
      <?php foreach ($orders as $o): $items = getItems($pdo, $o['id']); ?>
      <div class="panel">
        <div class="panel-head">
          <h3><?= e($o['order_code']) ?> <span class="badge badge-<?= $o['status'] ?>" style="margin-left:.6rem"><?= e($o['status']) ?></span></h3>
          <div style="color:var(--muted);font-size:.8rem"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></div>
        </div>
        <table>
          <thead><tr><th>Judul Novel</th><th>Harga</th></tr></thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr><td><?= e($it['novel_title']) ?></td><td>Rp <?= number_format($it['price'],0,',','.') ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="text-align:right;margin-top:.8rem;font-size:.9rem">
          Total: <strong style="color:var(--gold)">Rp <?= number_format($o['total'],0,',','.') ?></strong>
          &nbsp;·&nbsp; Metode: <?= e($o['payment_method']) ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
