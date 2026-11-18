<?php
// admin/laporan.php - Sesuai Tabel 46: Laporan Pemesanan Paket Wisata untuk Staf Admin
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['level'], ['admin', 'bendahara', 'direktur'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$currentUserRole = $_SESSION['level'];
$currentUserName = $_SESSION['nama'];

$statusFilter = clean_input($_GET['status'] ?? '');
$tglMulai = clean_input($_GET['tgl_mulai'] ?? '');
$tglSelesai = clean_input($_GET['tgl_selesai'] ?? '');

$sql = "SELECT p.*, pkt.judul as nama_paket, pkt.destinasi, u.nama as nama_pemesan, u.email as email_pemesan,
               t.id as trx_id, t.no_invoice, t.status as status_bayar, t.bukti_pembayaran
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
$laporan = $stmt->fetchAll();

// Hitung total penerimaan dan pax
$totalPendapatan = 0;
$totalPax = 0;
foreach ($laporan as $row) {
    if ($row['status_bayar'] === 'Lunas' || $row['status_bayar'] === 'Diterima') {
        $totalPendapatan += $row['harga'];
    }
    $totalPax += $row['jumlah_pax'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemesanan Paket Wisata - PT. Puma Star Jaya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        .print-only { display: none; }
        @media print {
            body { background: #ffffff !important; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .dashboard-main { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .card-table { box-shadow: none !important; border: 1px solid #000 !important; }
            .sidebar { display: none !important; }
        }
    </style>
</head>
<body class="dashboard-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-header no-print">
            <button class="header-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <div class="header-profile">
                <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($currentUserName) ?></div>
                <div class="user-avatar"><i class="fa-solid fa-user-tie"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Kop Laporan untuk Cetak (Hanya Muncul saat Print) -->
            <div style="display: none;" class="print-only">
                <div style="text-align: center; border-bottom: 3px double #000; padding-bottom: 14px; margin-bottom: 20px;">
                    <h2 style="font-size: 1.6rem; margin: 0;">PT. PUMA STAR JAYA TOUR & TRAVEL</h2>
                    <p style="margin: 4px 0 0; font-size: 0.9rem;">LAPORAN PEMESANAN & TRANSAKSI PAKET WISATA</p>
                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: #555;">Dicetak pada: <?= date('d/m/Y H:i') ?> | Oleh: <?= htmlspecialchars($currentUserName) ?> (<?= strtoupper($currentUserRole) ?>)</p>
                </div>
            </div>

            <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Laporan Pemesanan Paket Wisata</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Rekapitulasi dan analisis seluruh pemesanan paket wisata (Tabel 46)</p>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= base_url('admin/export_excel.php?' . http_build_query($_GET)) ?>" class="btn btn-primary" style="background: #059669; border-color: #059669;">
                        <i class="fa-solid fa-file-excel"></i> Ekspor ke Excel (.csv)
                    </a>
                    <button onclick="window.print()" class="btn btn-accent">
                        <i class="fa-solid fa-print"></i> Cetak / Ekspor PDF
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="no-print" style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 20px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                <!-- Preset Cepat -->
                <div style="display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);"><i class="fa-solid fa-bolt" style="color: var(--accent);"></i> Periode Cepat:</span>
                    <a href="?tgl_mulai=<?= date('Y-m-01') ?>&tgl_selesai=<?= date('Y-m-t') ?>" class="btn btn-sm btn-outline" style="background: #f8fafc;">Bulan Ini (<?= date('M Y') ?>)</a>
                    <a href="?tgl_mulai=<?= date('Y-m-01', strtotime('-1 month')) ?>&tgl_selesai=<?= date('Y-m-t', strtotime('-1 month')) ?>" class="btn btn-sm btn-outline" style="background: #f8fafc;">Bulan Lalu</a>
                    <a href="?tgl_mulai=2026-01-01&tgl_selesai=2026-12-31" class="btn btn-sm btn-outline" style="background: #f8fafc;">Tahun 2026 Penuh</a>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-sm btn-outline" style="background: #f8fafc;">Reset Semua</a>
                </div>

                <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap: 16px; align-items: flex-end;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Status Pembayaran</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="Menunggu Verifikasi" <?= $statusFilter === 'Menunggu Verifikasi' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                            <option value="Lunas" <?= $statusFilter === 'Lunas' ? 'selected' : '' ?>>Lunas / Diterima</option>
                            <option value="Ditolak" <?= $statusFilter === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tglMulai) ?>">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tglSelesai) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="height: 48px;">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </form>
            </div>

            <!-- Ringkasan Laporan Widget -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pemesanan</h4>
                        <div class="stat-value"><?= count($laporan) ?></div>
                    </div>
                    <div class="stat-icon primary"><i class="fa-solid fa-receipt"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Peserta (Pax)</h4>
                        <div class="stat-value" style="color: var(--sky);"><?= $totalPax ?></div>
                    </div>
                    <div class="stat-icon info"><i class="fa-solid fa-user-group"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pendapatan Lunas</h4>
                        <div class="stat-value" style="font-size: 1.6rem; color: #059669;"><?= format_rupiah($totalPendapatan) ?></div>
                    </div>
                    <div class="stat-icon success"><i class="fa-solid fa-sack-dollar"></i></div>
                </div>
            </div>

            <!-- TABEL LAPORAN -->
            <div class="card-table">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Invoice</th>
                                <th>Pemesan</th>
                                <th>Paket Wisata</th>
                                <th>Tgl Berangkat</th>
                                <th>Rombongan</th>
                                <th>Pax</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th class="no-print" style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 36px; color: var(--text-muted);">Tidak ada data pemesanan yang sesuai dengan filter.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($laporan as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['no_invoice'] ?: ('#ORD-' . $row['id'])) ?></strong></td>
                                        <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                        <td><?= format_tanggal_indo($row['tanggal_berangkat']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_rombongan']) ?></td>
                                        <td><?= htmlspecialchars($row['jumlah_pax']) ?> Org</td>
                                        <td><strong style="color: var(--accent-hover);"><?= format_rupiah($row['harga']) ?></strong></td>
                                        <td>
                                            <?php 
                                            $st = $row['status_bayar'] ?? 'Menunggu Bukti';
                                            $badgeClass = 'badge-warning';
                                            if ($st === 'Lunas' || $st === 'Diterima') $badgeClass = 'badge-success';
                                            else if ($st === 'Ditolak') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($st) ?></span>
                                        </td>
                                        <td class="no-print" style="text-align: center; white-space: nowrap;">
                                            <?php if ($row['trx_id']): ?>
                                                <a href="<?= base_url('invoice.php?id=' . $row['trx_id']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Invoice">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lembar Pengesahan Cetak Laporan Sesuai Format Skripsi -->
            <div class="print-only" style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center; page-break-inside: avoid;">
                <div style="width: 250px;">
                    <p style="margin-bottom: 60px;">Mengetahui,<br><strong>Bendahara Keuangan</strong></p>
                    <p style="text-decoration: underline; font-weight: bold; margin: 0;">Bendahara Puma Tour</p>
                    <p style="font-size: 0.8rem; color: #555; margin: 0;">PT. Puma Star Jaya Tour</p>
                </div>
                <div style="width: 250px;">
                    <p style="margin-bottom: 60px;">Disetujui Oleh,<br><strong>Direktur Utama</strong></p>
                    <p style="text-decoration: underline; font-weight: bold; margin: 0;">Direktur Utama Puma Tour</p>
                    <p style="font-size: 0.8rem; color: #555; margin: 0;">PT. Puma Star Jaya Tour</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
