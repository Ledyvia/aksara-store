<?php
/**
 * Helper session & proteksi akses halaman.
 * Panggil session_start() di paling atas SEBELUM output apa pun.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/** Panggil di awal halaman admin. Redirect ke login jika bukan admin. */
function require_admin(): void {
    if (!is_logged_in() || current_role() !== 'admin') {
        header('Location: ../auth/login.php?redirect=admin');
        exit;
    }
}

/** Panggil di awal halaman akun pembeli. Redirect ke login jika belum login. */
function require_buyer(): void {
    if (!is_logged_in() || current_role() !== 'buyer') {
        header('Location: ../auth/login.php?redirect=akun');
        exit;
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
