<?php
/**
 * Endpoint checkout. Dipanggil lewat fetch() dari index.php saat pembeli
 * menekan tombol "Bayar Sekarang". Menyimpan pesanan ke tabel orders +
 * order_items. Jika pembeli sedang login, pesanan otomatis terhubung ke
 * akunnya (muncul di dashboard "Riwayat Pesanan").
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Kamu harus masuk (login) dulu sebelum bisa memesan.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items']) || empty($input['email']) || empty($input['name'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Data pesanan tidak lengkap.']);
    exit;
}

$name    = trim($input['name']);
$email   = trim($input['email']);
$phone   = trim($input['phone'] ?? '');
$payment = trim($input['payment'] ?? '-');
$items   = $input['items'];

$total = 0;
foreach ($items as $it) {
    $total += (int)($it['price'] ?? 0);
}

$orderCode = 'AKS' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
$userId = $_SESSION['user_id'] ?? null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO orders (order_code, user_id, customer_name, customer_email, customer_phone, payment_method, total, status) VALUES (?,?,?,?,?,?,?,"pending")');
    $stmt->execute([$orderCode, $userId, $name, $email, $phone, $payment, $total]);
    $orderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, novel_id, novel_title, price) VALUES (?,?,?,?)');
    foreach ($items as $it) {
        $stmtItem->execute([$orderId, $it['id'] ?? null, $it['title'] ?? '-', (int)($it['price'] ?? 0)]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'order_code' => $orderCode]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan pesanan.']);
}
