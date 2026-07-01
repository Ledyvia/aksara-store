<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$totalNovel   = $pdo->query('SELECT COUNT(*) FROM novels')->fetchColumn();
$totalPesanan = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalUser    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn();
$totalOmzet   = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='paid'")->fetchColumn();

$recentOrders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll();

$page = 'dashboard';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Ringkasan <span>Toko</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?><br><small style="color:var(--muted)">Administrator</small></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card"><div class="sv"><?= $totalNovel ?></div><div class="sl">Total Novel</div></div>
      <div class="stat-card"><div class="sv"><?= $totalPesanan ?></div><div class="sl">Total Pesanan</div></div>
      <div class="stat-card"><div class="sv"><?= $totalUser ?></div><div class="sl">Total Pembeli</div></div>
      <div class="stat-card"><div class="sv">Rp <?= number_format($totalOmzet,0,',','.') ?></div><div class="sl">Omzet (Lunas)</div></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Pesanan Terbaru</h3><a href="pesanan.php" class="btn-sm">Lihat Semua</a></div>
      <?php if (!$recentOrders): ?>
        <div class="empty-state">Belum ada pesanan masuk.</div>
      <?php else: ?>
      <table>
        <thead><tr><th>Kode</th><th>Pembeli</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><?= e($o['order_code']) ?></td>
            <td><?= e($o['customer_name']) ?></td>
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
