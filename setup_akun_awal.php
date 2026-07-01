<?php
/**
 * CATATAN: file ini SUDAH TIDAK WAJIB dijalankan lagi.
 * Akun admin & pembeli demo sekarang sudah otomatis dibuat langsung
 * lewat database/aksara_store.sql (password sudah di-hash bcrypt di
 * dalam file SQL-nya). Jadi begitu SQL selesai di-import, akun sudah
 * bisa langsung dipakai untuk login.
 *
 * Script ini hanya disisakan sebagai cadangan (misalnya kalau kamu
 * import ulang database tanpa data seed akun, atau ingin bikin akun
 * demo tambahan). Kalau akun sudah ada, script ini otomatis
 * melewati/skip tanpa membuat duplikat.
 *
 * Jika dipakai, jalankan lewat browser:
 *   http://localhost/aksara-store/setup_akun_awal.php
 * lalu HAPUS FILE INI dari server setelah selesai untuk keamanan.
 */
require_once __DIR__ . '/config/database.php';

$accounts = [
    ['Admin Aksara', 'admin@aksarastore.id', 'admin123', 'admin'],
    ['Budi Pembeli', 'pembeli@aksarastore.id', 'pembeli123', 'buyer'],
];

$created = [];
$skipped = [];

foreach ($accounts as [$name, $email, $plainPassword, $role]) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $skipped[] = $email;
        continue;
    }
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $role]);
    $created[] = "$email ($role) — password: $plainPassword";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<title>Setup Akun Awal — Aksara Store</title>
<style>
body{background:#08080f;color:#e8e2d4;font-family:sans-serif;padding:3rem;line-height:1.8}
h1{color:#c8a84b}
code{background:#12121f;padding:.2rem .5rem;border-radius:3px;color:#e5c97a}
.box{background:#0f0f1c;border:1px solid rgba(200,168,75,.2);padding:1.5rem;border-radius:4px;margin:1rem 0}
a{color:#c8a84b}
</style>
</head>
<body>
<h1>Setup Akun Awal Aksara Store</h1>

<?php if ($created): ?>
<div class="box">
  <strong>✔ Akun baru berhasil dibuat:</strong>
  <ul><?php foreach ($created as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<?php if ($skipped): ?>
<div class="box">
  <strong>⚠ Sudah ada sebelumnya (dilewati):</strong>
  <ul><?php foreach ($skipped as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<p><strong style="color:#e05555">Penting:</strong> hapus file <code>setup_akun_awal.php</code> ini dari server sekarang setelah selesai, supaya tidak ada orang lain yang bisa mengaksesnya.</p>
<p><a href="auth/login.php">&rarr; Lanjut ke halaman login</a></p>
</body>
</html>
