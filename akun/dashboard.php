<?php
require_once __DIR__ . '/../config/session.php';
require_buyer();
require_once __DIR__ . '/../config/database.php';

$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$uid]);
$totalPesanan = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = ? AND status='paid'");
$stmt->execute([$uid]);
$totalBelanja = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$uid]);
$recent = $stmt->fetchAll();

$page = 'dashboard';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Halo, <span><?= e($_SESSION['full_name']) ?></span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div>Pembeli</div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card"><div class="sv"><?= $totalPesanan ?></div><div class="sl">Total Pesanan</div></div>
      <div class="stat-card"><div class="sv">Rp <?= number_format($totalBelanja,0,',','.') ?></div><div class="sl">Total Belanja (Lunas)</div></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Pesanan Terakhir</h3><a href="pesanan.php" class="btn-sm">Lihat Semua</a></div>
      <?php if (!$recent): ?>
        <div class="empty-state">Kamu belum punya pesanan. <a href="../index.php">Yuk mulai belanja!</a></div>
      <?php else: ?>
      <table>
        <thead><tr><th>Kode</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $o): ?>
          <tr>
            <td><?= e($o['order_code']) ?></td>
            <td>Rp <?= number_format($o['total'],0,',','.') ?></td>
            <td><span class="badge badge-<?= $o['status'] ?>"><?= e($o['status']) ?></span></td>
            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
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
