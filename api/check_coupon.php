<?php
// api/check_coupon.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$kode = clean_input($_GET['kode'] ?? '');

if (empty($kode)) {
    echo json_encode(['valid' => false, 'message' => 'Masukkan kode kupon terlebih dahulu!']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kupon WHERE UPPER(TRIM(kode)) = UPPER(TRIM(?)) LIMIT 1");
$stmt->execute([$kode]);
$kupon = $stmt->fetch();

if ($kupon) {
    echo json_encode([
        'valid' => true,
        'kode' => $kupon['kode'],
        'potongan' => (int)$kupon['potongan'],
        'potongan_formatted' => format_rupiah($kupon['potongan']),
        'message' => 'Kupon diskon berhasil diterapkan! Hemat ' . format_rupiah($kupon['potongan'])
    ]);
} else {
    echo json_encode([
        'valid' => false,
        'message' => 'Kode kupon tidak valid atau sudah kadaluarsa.'
    ]);
}
