<?php
require_once __DIR__ . '/../config/session.php';
require_buyer();
require_once __DIR__ . '/../config/database.php';

$uid = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        if ($full_name === '') {
            $error = 'Nama tidak boleh kosong.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
            $stmt->execute([$full_name, $phone, $uid]);
            $_SESSION['full_name'] = $full_name;
            $success = 'Profil berhasil diperbarui.';
            $user['full_name'] = $full_name;
            $user['phone'] = $phone;
        }
    } elseif (isset($_POST['change_password'])) {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';
        if (!password_verify($old, $user['password'])) {
            $error = 'Kata sandi lama salah.';
        } elseif (strlen($new) < 6) {
            $error = 'Kata sandi baru minimal 6 karakter.';
        } elseif ($new !== $new2) {
            $error = 'Konfirmasi kata sandi baru tidak cocok.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $uid]);
            $success = 'Kata sandi berhasil diubah.';
        }
    }
}

$page = 'profil';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Profil <span>Saya</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <?php if ($success): ?><div class="alert-box alert-ok"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-box alert-err"><?= e($error) ?></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h3>Data Diri</h3></div>
      <form method="post">
        <div class="form-grid">
          <div><label class="f-label">Nama Lengkap</label><input class="c-input" name="full_name" value="<?= e($user['full_name']) ?>" required/></div>
          <div><label class="f-label">Email</label><input class="c-input" value="<?= e($user['email']) ?>" disabled/></div>
          <div><label class="f-label">No. HP</label><input class="c-input" name="phone" value="<?= e($user['phone'] ?? '') ?>"/></div>
        </div>
        <button class="btn-sm primary" type="submit" name="update_profile" style="padding:.6rem 1.4rem">Simpan Profil</button>
      </form>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Ganti Kata Sandi</h3></div>
      <form method="post">
        <div class="form-grid">
          <div class="full"><label class="f-label">Kata Sandi Lama</label><input class="c-input" type="password" name="old_password" required/></div>
          <div><label class="f-label">Kata Sandi Baru</label><input class="c-input" type="password" name="new_password" required/></div>
          <div><label class="f-label">Konfirmasi Kata Sandi Baru</label><input class="c-input" type="password" name="new_password2" required/></div>
        </div>
        <button class="btn-sm primary" type="submit" name="change_password" style="padding:.6rem 1.4rem">Ubah Kata Sandi</button>
      </form>
    </div>

  </div>
</div>
</body>
</html>
