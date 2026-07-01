<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '');

if (is_logged_in()) {
    if ($redirect === 'shop') {
        header('Location: ../index.php');
    } else {
        header('Location: ' . (current_role() === 'admin' ? '../admin/dashboard.php' : '../akun/dashboard.php'));
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Email atau kata sandi salah.';
        } elseif ($user['status'] === 'blocked') {
            $error = 'Akun kamu diblokir. Hubungi admin.';
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            if ($redirect === 'shop') {
                header('Location: ../index.php');
            } else {
                header('Location: ' . ($user['role'] === 'admin' ? '../admin/dashboard.php' : '../akun/dashboard.php'));
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Masuk — Aksara Store</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="../assets/css/dashboard.css" rel="stylesheet"/>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">Aksara Store</div>
    <div class="auth-sub">Masuk ke akunmu — admin maupun pembeli memakai form yang sama</div>

    <?php if ($error): ?><div class="alert-box alert-err"><?= e($error) ?></div><?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="redirect" value="<?= e($redirect) ?>"/>
      <label class="f-label">Email</label>
      <input type="email" name="email" class="c-input" placeholder="email@kamu.com" value="<?= e($_POST['email'] ?? '') ?>" required/>

      <label class="f-label">Kata Sandi</label>
      <input type="password" name="password" class="c-input" placeholder="Kata sandi" required/>

      <button type="submit" class="btn-gold">Masuk</button>
    </form>

    <div class="auth-switch">Belum punya akun? <a href="register.php">Daftar sebagai pembeli</a></div>
    <div class="auth-switch"><a href="../index.php">&larr; Kembali ke toko</a></div>
  </div>
</div>
</body>
</html>
