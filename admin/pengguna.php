<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','blocked','active') WHERE id = ? AND role='buyer'");
        $stmt->execute([$id]);
    }
    header('Location: pengguna.php');
    exit;
}

$users = $pdo->query("SELECT * FROM users WHERE role='buyer' ORDER BY created_at DESC")->fetchAll();

$page = 'pengguna';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Kelola <span>Pengguna</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Daftar Pembeli (<?= count($users) ?>)</h3></div>
      <?php if (!$users): ?>
        <div class="empty-state">Belum ada pembeli yang mendaftar.</div>
      <?php else: ?>
      <table>
        <thead><tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['phone'] ?: '-') ?></td>
            <td><span class="badge badge-<?= $u['status'] ?>"><?= $u['status']==='active'?'Aktif':'Diblokir' ?></span></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td><a href="pengguna.php?toggle=<?= $u['id'] ?>" class="btn-sm <?= $u['status']==='active'?'danger':'' ?>" onclick="return confirm('Ubah status pengguna ini?')"><?= $u['status']==='active'?'Blokir':'Aktifkan' ?></a></td>
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
