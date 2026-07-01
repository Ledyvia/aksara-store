<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Akun Saya — Aksara Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="../assets/css/dashboard.css" rel="stylesheet"/>
</head>
<body>
<div class="dash">
  <div class="dash-side">
    <div class="dash-brand">Aksara Store<small>Akun Saya</small></div>
    <nav class="dash-nav">
      <a href="dashboard.php" class="<?= $page==='dashboard'?'active':'' ?>"><i class="bi bi-house"></i> Ringkasan</a>
      <a href="pesanan.php" class="<?= $page==='pesanan'?'active':'' ?>"><i class="bi bi-bag"></i> Riwayat Pesanan</a>
      <a href="profil.php" class="<?= $page==='profil'?'active':'' ?>"><i class="bi bi-person"></i> Profil Saya</a>
      <a href="../index.php"><i class="bi bi-shop"></i> Belanja Lagi</a>
    </nav>
  </div>
  <div class="dash-main">
