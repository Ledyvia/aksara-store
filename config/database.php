<?php
/**
 * Konfigurasi koneksi database.
 * Sesuaikan DB_USER / DB_PASS jika instalasi XAMPP kamu memakai
 * user/password MySQL yang berbeda dari default.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'aksara_store');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan MySQL di XAMPP sudah dijalankan dan database "aksara_store" sudah diimport. Detail: ' . $e->getMessage());
}
