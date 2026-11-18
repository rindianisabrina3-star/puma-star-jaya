<?php
// admin/export_excel.php - Ekspor Rekapitulasi Laporan Pemesanan & Transaksi ke Excel (.csv)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['level'], ['admin', 'bendahara', 'direktur'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$statusFilter = clean_input($_GET['status'] ?? '');
$tglMulai = clean_input($_GET['tgl_mulai'] ?? '');
$tglSelesai = clean_input($_GET['tgl_selesai'] ?? '');

$sql = "SELECT p.*, pkt.judul as nama_paket, pkt.destinasi, u.nama as nama_pemesan, u.email as email_pemesan,
               t.no_invoice, t.status as status_bayar, t.created_at as tgl_transaksi
        FROM pemesanan p
        JOIN paket pkt ON p.paket_id = pkt.id
        JOIN user u ON p.user_id = u.id
        LEFT JOIN transaksi t ON t.pemesanan_id = p.id
        WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
}
if (!empty($tglMulai)) {
    $sql .= " AND DATE(p.created_at) >= ?";
    $params[] = $tglMulai;
}
if (!empty($tglSelesai)) {
    $sql .= " AND DATE(p.created_at) <= ?";
    $params[] = $tglSelesai;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Header untuk Download File Excel (.csv)
$filename = "Laporan_PumaStarJaya_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// BOM untuk UTF-8 agar karakter Excel tidak rusak di Windows
fputs($output, "\xEF\xBB\xBF");

// Baris Judul
fputcsv($output, ['LAPORAN PEMESANAN & TRANSAKSI PT. PUMA STAR JAYA TOUR & TRAVEL']);
fputcsv($output, ['Tanggal Unduh:', date('d-m-Y H:i:s')]);
fputcsv($output, ['Diekspor Oleh:', $_SESSION['nama'] . ' (' . strtoupper($_SESSION['level']) . ')']);
fputcsv($output, []); // Baris Kosong

// Header Kolom
fputcsv($output, [
    'No',
    'No. Invoice',
    'Nama Pemesan',
    'Email',
    'No. WhatsApp',
    'Nama Rombongan',
    'Paket Wisata',
    'Destinasi',
    'Tanggal Berangkat',
    'Jumlah Pax',
    'Total Biaya (Rp)',
    'Status Pembayaran',
    'Tanggal Booking'
]);

$no = 1;
$totalPendapatan = 0;
$totalPax = 0;

foreach ($data as $row) {
    $isLunas = in_array($row['status_bayar'], ['Lunas', 'Diterima']);
    if ($isLunas) {
        $totalPendapatan += $row['harga'];
    }
    $totalPax += $row['jumlah_pax'];

    fputcsv($output, [
        $no++,
        $row['no_invoice'] ?: ('#ORD-' . $row['id']),
        $row['nama_pemesan'],
        $row['email_pemesan'],
        $row['no_hp'],
        $row['nama_rombongan'],
        $row['nama_paket'],
        $row['destinasi'],
        $row['tanggal_berangkat'],
        $row['jumlah_pax'],
        $row['harga'],
        $row['status_bayar'] ?: 'Menunggu Bukti',
        $row['created_at']
    ]);
}

fputcsv($output, []); // Baris Kosong
fputcsv($output, ['', '', '', '', '', '', '', '', 'TOTAL PESERTA:', $totalPax, 'TOTAL OMSET LUNAS:', $totalPendapatan, '']);

fclose($output);
exit;
