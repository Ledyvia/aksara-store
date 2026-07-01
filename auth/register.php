<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    header('Location: ' . (current_role() === 'admin' ? '../admin/dashboard.php' : '../akun/dashboard.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($full_name === '' || $email === '' || $password === '') {
        $error = 'Nama, email, dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan masuk.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, "buyer")');
            $stmt->execute([$full_name, $email, $hash, $phone]);
            $success = 'Akun berhasil dibuat! Silakan masuk.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Daftar Akun — Aksara Store</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="../assets/css/dashboard.css" rel="stylesheet"/>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">Aksara Store</div>
    <div class="auth-sub">Buat akun pembeli baru</div>

    <?php if ($error): ?><div class="alert-box alert-err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-box alert-ok"><?= e($success) ?> <a href="login.php">Masuk sekarang</a></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" novalidate>
      <label class="f-label">Nama Lengkap</label>
      <input type="text" name="full_name" class="c-input" placeholder="Nama kamu..." value="<?= e($_POST['full_name'] ?? '') ?>" required/>

      <label class="f-label">Email</label>
      <input type="email" name="email" class="c-input" placeholder="email@kamu.com" value="<?= e($_POST['email'] ?? '') ?>" required/>

      <label class="f-label">No. HP (opsional)</label>
      <input type="text" name="phone" class="c-input" placeholder="08xxxxxxxxxx" value="<?= e($_POST['phone'] ?? '') ?>"/>

      <label class="f-label">Kata Sandi</label>
      <input type="password" name="password" class="c-input" placeholder="Minimal 6 karakter" required/>

      <label class="f-label">Konfirmasi Kata Sandi</label>
      <input type="password" name="password2" class="c-input" placeholder="Ulangi kata sandi" required/>

      <button type="submit" class="btn-gold">Daftar</button>
    </form>
    <?php endif; ?>

    <div class="auth-switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    <div class="auth-switch"><a href="../index.php">&larr; Kembali ke toko</a></div>
  </div>
</div>
</body>
</html>
