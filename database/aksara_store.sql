-- =========================================================
-- AKSARA STORE — Skema Database
-- Import file ini lewat phpMyAdmin (XAMPP) sebelum apa pun lain.
-- =========================================================

CREATE DATABASE IF NOT EXISTS aksara_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aksara_store;

-- ─── TABEL USERS (admin & pembeli jadi satu tabel, dibedakan lewat role) ───
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password      VARCHAR(255) NOT NULL,
  phone         VARCHAR(30)  DEFAULT NULL,
  role          ENUM('admin','buyer') NOT NULL DEFAULT 'buyer',
  status        ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TABEL NOVELS (produk) ───
CREATE TABLE IF NOT EXISTS novels (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(150) NOT NULL,
  author        VARCHAR(120) NOT NULL,
  genre         VARCHAR(40)  NOT NULL,
  price         INT NOT NULL,
  orig_price    INT DEFAULT NULL,
  rating        DECIMAL(2,1) DEFAULT 4.5,
  badge         ENUM('new','hot','disc','') DEFAULT '',
  icon          VARCHAR(10)  DEFAULT '📖',
  description   TEXT,
  tags          VARCHAR(255) DEFAULT NULL,
  cover_bg      VARCHAR(255) DEFAULT NULL,
  cover_color   VARCHAR(20)  DEFAULT '#c8a84b',
  status        ENUM('active','hidden') NOT NULL DEFAULT 'active',
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── TABEL ORDERS (pesanan) ───
CREATE TABLE IF NOT EXISTS orders (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  order_code      VARCHAR(30) NOT NULL UNIQUE,
  user_id         INT DEFAULT NULL,
  customer_name   VARCHAR(100) NOT NULL,
  customer_email  VARCHAR(150) NOT NULL,
  customer_phone  VARCHAR(30) DEFAULT NULL,
  payment_method  VARCHAR(40) NOT NULL,
  total           INT NOT NULL,
  status          ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─── TABEL ORDER_ITEMS (detail item per pesanan) ───
CREATE TABLE IF NOT EXISTS order_items (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  novel_id      INT DEFAULT NULL,
  novel_title   VARCHAR(150) NOT NULL,
  price         INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- SEED DATA — 13 novel dari katalog Aksara Store
-- =========================================================
INSERT INTO novels (title, author, genre, price, orig_price, rating, badge, icon, description, tags, cover_bg, cover_color) VALUES
('Cantik Itu Luka','Eka Kurniawan','drama',45000,60000,4.9,'hot','🌹','Novel epik yang menggabungkan realisme magis, sejarah, dan tragedi keluarga. Kisah tentang kecantikan, pengorbanan, dan luka yang tak pernah sembuh dari sebuah keluarga di Indonesia pascapenjajahan.','Realisme Magis,Sastra,Sejarah,Indonesia','linear-gradient(150deg,#2d1b2e,#8b2252,#5c1040)','#f8b4c8'),
('Seporsi Mi Ayam Sebelum Mati','J.S. Khairen','drama',38000,NULL,4.7,'new','🍜','Kisah haru tentang seseorang yang memutuskan untuk menghabiskan hari terakhirnya dengan menikmati semangkuk mi ayam. Sebuah refleksi tentang hidup, kehilangan, dan hal-hal sederhana yang berarti.','Kontemporer,Emosional,Reflektif','linear-gradient(150deg,#1a1200,#2d2000,#3d2c00)','#f8d76a'),
('Sisi Tergelap Surga','Fiersa Besari','romance',35000,50000,4.8,'hot','🌟','Sebuah kisah cinta yang menyentuh hati, menceritakan perjalanan dua jiwa yang saling mencintai namun terpisah oleh takdir. Penuh dengan puisi dan kata-kata indah yang menyentuh jiwa.','Romance,Puitis,Menyentuh','linear-gradient(150deg,#0f0f1a,#1a1a2e,#2a1060)','#b39ddb'),
('Laut Bercerita','Leila S. Chudori','thriller',42000,NULL,4.9,'hot','🌊','Novel tentang penghilangan paksa aktivis mahasiswa di era Orde Baru. Kisah yang memadukan misteri, sejarah, dan pergulatan keluarga yang ditinggalkan mencari kebenaran.','Sejarah,Thriller,Politik,Indonesia','linear-gradient(150deg,#0a1628,#1a3a5c,#0d2137)','#7ec8e3'),
('Di Tanah Lada','Ziggy Zezsyazeoviennazabrizkie','misteri',32000,45000,4.6,'disc','🌿','Novel pemenang Sayembara Menulis Novel DKJ. Kisah anak kecil bernama Ava yang pindah ke Kompleks P dan mengungkap misteri gelap di balik kehidupan sehari-hari yang tampak biasa.','Misteri,Dark,Pemenang DKJ','linear-gradient(150deg,#0a1a0a,#0d2d0d,#102010)','#a5d6a7'),
('Bayangan Tak Bernama','Rian Maulana','thriller',42000,NULL,4.7,'hot','🔪','Thriller psikologis yang menegangkan. Seorang detektif diburu oleh bayangannya sendiri ketika menyelidiki serangkaian kasus pembunuhan misterius yang memiliki pola tak lazim.','Thriller,Psikologis,Detektif','linear-gradient(150deg,#0d0d0d,#1a1a1a,#2d1b00)','#ff8c00'),
('Satu Musim Bersamamu','Dira Kusuma','romance',35000,50000,4.8,'new','🌹','Kisah cinta yang manis dan hangat antara dua insan yang dipertemukan di musim yang tidak biasa. Penuh dengan momen-momen indah yang akan membuat hatimu bergetar.','Sweet Romance,Heartwarming','linear-gradient(150deg,#2d1b2e,#8b2252,#5c1040)','#f8b4c8'),
('Negeri di Atas Awan','Sekar Ayu','fantasy',38000,NULL,4.7,'new','⚔️','Petualangan epik di negeri yang berada di atas awan, di mana para pejuang berjuang mempertahankan dunia mereka dari invasi kekuatan jahat. Penuh dengan aksi dan magia.','Epic Fantasy,Petualangan,Magia','linear-gradient(150deg,#0a1628,#1a3a5c,#0d2137)','#7ec8e3'),
('Rumah Tua Itu','Hendra Wijaya','misteri',29000,45000,4.5,'disc','🔮','Sebuah rumah tua menyimpan rahasia yang telah terkubur selama puluhan tahun. Ketika keluarga baru menempatinya, teror demi teror mulai datang mengancam.','Misteri,Horor Ringan,Suspense','linear-gradient(150deg,#0f0f1a,#1a1a2e,#16213e)','#b39ddb'),
('Di Balik Kegelapan','Ardi Nugroho','horror',31000,NULL,4.6,'hot','👁️','Teror yang datang dari kegelapan — sesuatu yang tak kasat mata namun selalu hadir. Novel horor psikologis yang akan membuatmu tidur dengan lampu menyala.','Horror,Psikologis,Mencekam','linear-gradient(150deg,#0a0a0a,#1a0000,#2d0505)','#ef9a9a'),
('Surat Terakhir Untukmu','Nisa Rahmawati','romance',37000,NULL,4.8,'new','💌','Surat-surat yang tidak pernah terkirim, menceritakan cinta yang tidak pernah terucap. Sebuah kisah tentang keberanian mencintai dan keputusan yang mengubah segalanya.','Romance,Epistolary,Emosional','linear-gradient(150deg,#1a0a0a,#3d1515,#2d1010)','#ff8a80'),
('Antara Dua Pilihan','Laila Fitri','drama',33000,NULL,4.6,'','🎭','Ketika dua jalan terbuka di hadapanmu, mana yang kau pilih? Novel drama tentang dilema hidup, keluarga, dan impian yang saling bertabrakan.','Drama,Keluarga,Dilema','linear-gradient(150deg,#1a1200,#2d2000,#3d2c00)','#c8a84b'),
('Hutan Abadi','Putri Andini','fantasy',28000,40000,4.5,'disc','🌿','Di sebuah hutan yang tak pernah berubah, waktu berhenti dan rahasia alam semesta tersimpan rapat. Sebuah perjalanan fantasi yang memukau tentang keabadian dan pilihan.','Fantasy,Alam,Petualangan','linear-gradient(150deg,#0a1a0a,#0d2d0d,#102010)','#a5d6a7');

-- =========================================================
-- SEED AKUN — admin & pembeli demo (password sudah di-hash bcrypt,
-- kompatibel langsung dengan password_verify() di PHP).
-- Jadi begitu SQL ini di-import, akun sudah siap dipakai TANPA perlu
-- menjalankan setup_akun_awal.php lagi.
--
--   Admin   -> email: admin@aksarastore.id   | password: admin123
--   Pembeli -> email: pembeli@aksarastore.id | password: pembeli123
-- =========================================================
INSERT INTO users (full_name, email, password, role) VALUES
('Admin Aksara', 'admin@aksarastore.id', '$2y$10$2uC6HmoiaMzJEqGtD8DfRO2jggldx6OsU1Lb2MMerYL8UznKGZxeS', 'admin'),
('Budi Pembeli', 'pembeli@aksarastore.id', '$2y$10$iyhLNmNJLqKSIHkPU9/Hu.KNjoIGqtGQO74KezLnD6wrAzt/afgCq', 'buyer');
