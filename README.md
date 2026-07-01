# Aksara Store — Dashboard Admin & Pembeli (XAMPP)

Paket ini menambahkan **login, dashboard admin, dan dashboard pembeli** ke toko
Aksara Store yang sudah ada, memakai PHP + MySQL (XAMPP).

## 1. Struktur Folder

```
aksara-store/
├── index.php              ← halaman toko (katalog diambil langsung dari database)
├── setup_akun_awal.php   ← jalankan SEKALI untuk buat akun admin & demo
├── config/
│   ├── database.php      ← koneksi database
│   └── session.php       ← helper login/session
├── database/
│   └── aksara_store.sql  ← import ini ke phpMyAdmin
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── admin/                ← dashboard admin (kelola produk, pesanan, pengguna)
├── akun/                 ← dashboard pembeli (riwayat pesanan, profil)
├── api/
│   └── checkout.php      ← menyimpan pesanan dari toko ke database
└── assets/css/dashboard.css
```

## 2. Instalasi di XAMPP

1. **Install XAMPP** (jika belum) dari https://www.apachefriends.org, lalu buka **XAMPP Control Panel** dan **Start** modul `Apache` dan `MySQL`.
2. **Salin folder** `aksara-store` ke dalam folder `htdocs` XAMPP:
   - Windows: `C:\xampp\htdocs\aksara-store`
   - macOS: `/Applications/XAMPP/htdocs/aksara-store`
   - Linux: `/opt/lampp/htdocs/aksara-store`
3. **Buat database**: buka `http://localhost/phpmyadmin`, klik tab **Import**, pilih file `database/aksara_store.sql`, lalu klik **Go**. Ini akan membuat database `aksara_store` beserta tabel, 13 data novel awal, **dan akun admin + pembeli demo (sudah otomatis ter-hash password-nya)** — jadi tidak perlu langkah tambahan apa pun lagi:
   - **Admin** → email `admin@aksarastore.id`, password `admin123`
   - **Pembeli demo** → email `pembeli@aksarastore.id`, password `pembeli123`
4. **Buka toko**: `http://localhost/aksara-store/index.php`
   - Toko sekarang **wajib login dulu**. Kalau belum login, kamu otomatis diarahkan ke halaman Masuk (`auth/login.php`) — katalog & tombol pesan baru muncul setelah login berhasil.
5. **Login**: `http://localhost/aksara-store/auth/login.php`
   - Login sebagai admin → diarahkan ke `admin/dashboard.php`
   - Login sebagai pembeli (lewat tombol Masuk di toko) → langsung kembali ke halaman toko (`index.php`) supaya bisa langsung belanja
   - Pembeli baru bisa daftar sendiri lewat `auth/register.php`

Jika koneksi database gagal, cek `config/database.php` — defaultnya pakai user `root` tanpa password (default XAMPP). Sesuaikan jika instalasi MySQL kamu berbeda.

## 3. Fitur Dashboard Admin (`/admin`)

| Halaman | Fungsi |
|---|---|
| `dashboard.php` | Ringkasan: total novel, pesanan, pembeli, omzet |
| `produk.php` | Tambah / edit / hapus novel (CRUD lengkap) |
| `pesanan.php` | Lihat semua pesanan, filter status, ubah status (pending/lunas/batal) |
| `pengguna.php` | Lihat daftar pembeli, blokir/aktifkan akun |

## 4. Fitur Dashboard Pembeli (`/akun`)

| Halaman | Fungsi |
|---|---|
| `dashboard.php` | Ringkasan total pesanan & total belanja |
| `pesanan.php` | Riwayat lengkap semua pesanan beserta item |
| `profil.php` | Edit nama/HP & ganti kata sandi |

## 5. Alur Data

- **Toko (`index.php`) sekarang wajib login.** Pengunjung yang belum login
  otomatis di-redirect ke `auth/login.php` dan tidak bisa melihat katalog
  atau memesan sama sekali sampai berhasil masuk.
- Saat pembeli yang sudah login menyelesaikan checkout, data dikirim ke
  `api/checkout.php` dan tersimpan ke tabel `orders` + `order_items`,
  otomatis terhubung ke akunnya, dan langsung muncul di halaman
  "Riwayat Pesanan".
- `api/checkout.php` juga menolak (HTTP 401) setiap request yang datang
  tanpa sesi login aktif, jadi endpoint-nya tidak bisa dipakai untuk
  checkout sebagai tamu meskipun diakses langsung.

## 6. Keamanan yang Sudah Diterapkan

- Toko dikunci di belakang login (`index.php` redirect ke `auth/login.php` jika belum masuk) — tidak ada lagi pesan tanpa akun.
- Password disimpan ter-**hash** (`password_hash` / `password_verify`), bukan teks biasa.
- Query database pakai **prepared statements** (PDO), aman dari SQL Injection.
- Setiap halaman admin/`akun` dicek sesi & role lewat `require_admin()` / `require_buyer()` — tidak bisa diakses tanpa login yang sesuai.
- Semua output ke HTML di-escape lewat fungsi `e()` untuk mencegah XSS.

## 7. Catatan Lanjutan (opsional, jika ingin dikembangkan)

- Katalog novel di `index.php` **sudah dinamis** — diambil langsung dari tabel `novels` setiap halaman dimuat. Jadi begitu admin tambah/edit/hapus novel di `admin/produk.php`, perubahan langsung tampil di toko tanpa perlu ubah kode apa pun.
- Navbar toko otomatis berubah: menampilkan "Masuk" jika belum login, atau nama pembeli/tombol Keluar jika sudah login. Saat checkout, nama & email otomatis terisi untuk pembeli yang sedang login.
- Kalau perlu tambah admin kedua lewat phpMyAdmin, isi kolom `role` = `admin` dan `password` dengan hasil `password_hash()` dari PHP (bukan teks biasa).
