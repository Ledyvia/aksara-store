<?php
require_once __DIR__ . '/../config/session.php';
require_admin();
require_once __DIR__ . '/../config/database.php';

$editData = null;
$formError = '';

// ── HAPUS ──
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare('DELETE FROM novels WHERE id = ?');
    $stmt->execute([(int)$_GET['hapus']]);
    header('Location: produk.php?msg=deleted');
    exit;
}

// ── SIMPAN (TAMBAH / EDIT) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? '';
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $genre       = trim($_POST['genre'] ?? '');
    $price       = (int)($_POST['price'] ?? 0);
    $orig_price  = $_POST['orig_price'] !== '' ? (int)$_POST['orig_price'] : null;
    $rating      = (float)($_POST['rating'] ?? 4.5);
    $badge       = $_POST['badge'] ?? '';
    $icon        = trim($_POST['icon'] ?? '📖');
    $description = trim($_POST['description'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $cover_color = trim($_POST['cover_color'] ?? '#c8a84b');

    if ($title === '' || $author === '' || $genre === '' || $price <= 0) {
        $formError = 'Judul, penulis, genre, dan harga wajib diisi dengan benar.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE novels SET title=?, author=?, genre=?, price=?, orig_price=?, rating=?, badge=?, icon=?, description=?, tags=?, cover_color=? WHERE id=?');
            $stmt->execute([$title,$author,$genre,$price,$orig_price,$rating,$badge,$icon,$description,$tags,$cover_color,$id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO novels (title,author,genre,price,orig_price,rating,badge,icon,description,tags,cover_color) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$title,$author,$genre,$price,$orig_price,$rating,$badge,$icon,$description,$tags,$cover_color]);
        }
        header('Location: produk.php?msg=saved');
        exit;
    }
}

// ── DATA UNTUK FORM EDIT ──
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM novels WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

$novels = $pdo->query('SELECT * FROM novels ORDER BY created_at DESC')->fetchAll();

$page = 'produk';
include __DIR__ . '/_nav.php';
?>

    <div class="dash-top">
      <div class="dash-title">Kelola <span>Produk</span></div>
      <div class="user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
        <div><?= e($_SESSION['full_name']) ?></div>
        <a href="../auth/logout.php" class="btn-logout">Keluar</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert-box alert-ok">
        <?= $_GET['msg']==='saved' ? 'Produk berhasil disimpan.' : 'Produk berhasil dihapus.' ?>
      </div>
    <?php endif; ?>
    <?php if ($formError): ?><div class="alert-box alert-err"><?= e($formError) ?></div><?php endif; ?>

    <div class="panel">
      <div class="panel-head"><h3><?= $editData ? 'Edit Novel' : 'Tambah Novel Baru' ?></h3></div>
      <form method="post">
        <input type="hidden" name="id" value="<?= e((string)($editData['id'] ?? '')) ?>"/>
        <div class="form-grid">
          <div><label class="f-label">Judul</label><input class="c-input" name="title" required value="<?= e($editData['title'] ?? '') ?>"/></div>
          <div><label class="f-label">Penulis</label><input class="c-input" name="author" required value="<?= e($editData['author'] ?? '') ?>"/></div>

          <div>
            <label class="f-label">Genre</label>
            <select class="c-input" name="genre" required>
              <?php foreach (['drama','romance','thriller','misteri','fantasy','horror'] as $g): ?>
                <option value="<?= $g ?>" <?= (($editData['genre'] ?? '')===$g)?'selected':'' ?>><?= ucfirst($g) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="f-label">Badge</label>
            <select class="c-input" name="badge">
              <option value="" <?= (($editData['badge'] ?? '')==='')?'selected':'' ?>>Tidak ada</option>
              <option value="new" <?= (($editData['badge'] ?? '')==='new')?'selected':'' ?>>Baru</option>
              <option value="hot" <?= (($editData['badge'] ?? '')==='hot')?'selected':'' ?>>Hot</option>
              <option value="disc" <?= (($editData['badge'] ?? '')==='disc')?'selected':'' ?>>Diskon</option>
            </select>
          </div>

          <div><label class="f-label">Harga (Rp)</label><input class="c-input" type="number" name="price" required value="<?= e((string)($editData['price'] ?? '')) ?>"/></div>
          <div><label class="f-label">Harga Coret (opsional)</label><input class="c-input" type="number" name="orig_price" value="<?= e((string)($editData['orig_price'] ?? '')) ?>"/></div>

          <div><label class="f-label">Rating (1-5)</label><input class="c-input" type="number" step="0.1" min="1" max="5" name="rating" value="<?= e((string)($editData['rating'] ?? '4.5')) ?>"/></div>
          <div><label class="f-label">Ikon Emoji</label><input class="c-input" name="icon" value="<?= e($editData['icon'] ?? '📖') ?>"/></div>

          <div class="full"><label class="f-label">Tag (pisahkan dengan koma)</label><input class="c-input" name="tags" value="<?= e($editData['tags'] ?? '') ?>"/></div>
          <div class="full"><label class="f-label">Deskripsi</label><textarea class="c-input" name="description"><?= e($editData['description'] ?? '') ?></textarea></div>
        </div>
        <button class="btn-sm primary" type="submit" style="padding:.6rem 1.4rem">
          <?= $editData ? 'Simpan Perubahan' : 'Tambah Novel' ?>
        </button>
        <?php if ($editData): ?><a href="produk.php" class="btn-sm">Batal</a><?php endif; ?>
      </form>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Daftar Novel (<?= count($novels) ?>)</h3></div>
      <table>
        <thead><tr><th>Judul</th><th>Penulis</th><th>Genre</th><th>Harga</th><th>Badge</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($novels as $n): ?>
          <tr>
            <td><?= $n['icon'] ?> <?= e($n['title']) ?></td>
            <td><?= e($n['author']) ?></td>
            <td><?= e($n['genre']) ?></td>
            <td>Rp <?= number_format($n['price'],0,',','.') ?></td>
            <td><?= $n['badge'] ? '<span class="badge badge-active">'.e($n['badge']).'</span>' : '-' ?></td>
            <td>
              <a href="produk.php?edit=<?= $n['id'] ?>" class="btn-sm">Edit</a>
              <a href="produk.php?hapus=<?= $n['id'] ?>" class="btn-sm danger" onclick="return confirm('Hapus novel ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>
