<?php
// admin/dashboard.php - Sesuai Gambar 33 & Tabel 32 (Rancangan Dashboard Admin)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$adminName = $_SESSION['nama'];

// 1. Total Pemesanan Sesuai Gambar 33 & Tabel 32
$totalPemesanan = $pdo->query("SELECT COUNT(*) FROM pemesanan")->fetchColumn();

// 2. Total Transaksi Sesuai Gambar 33 & Tabel 32
$totalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();

// 3. Jumlah Pengguna Sesuai Gambar 33 & Tabel 32
$jumlahPengguna = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();

// Data pemesanan terbaru untuk monitoring admin
$stmtRecent = $pdo->query("SELECT p.*, u.nama as nama_pemesan, pkt.judul as nama_paket, t.id as trx_id, t.status, t.no_invoice
                           FROM pemesanan p
                           JOIN user u ON p.user_id = u.id
                           JOIN paket pkt ON p.paket_id = pkt.id
                           LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                           ORDER BY p.id DESC LIMIT 6");
$recentOrders = $stmtRecent->fetchAll();

// Jadwal Keberangkatan Bus Terdekat
$stmtUpcoming = $pdo->query("SELECT p.*, pkt.judul as nama_paket, pkt.bus, pkt.destinasi
                             FROM pemesanan p
                             JOIN paket pkt ON p.paket_id = pkt.id
                             WHERE p.tanggal_berangkat >= CURDATE()
                             ORDER BY p.tanggal_berangkat ASC LIMIT 3");
$upcomingTrips = $stmtUpcoming->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <!-- SIDEBAR ADMIN Sesuai Gambar 33 -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <!-- Topbar Sesuai Gambar 33 -->
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle" title="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="header-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary);"><?= htmlspecialchars($adminName) ?></div>
                    <div style="font-size: 0.75rem; color: var(--accent-hover); font-weight: 700; text-transform: uppercase;">Staff Administrator</div>
                </div>
                <div class="user-avatar" style="background: var(--primary);">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Salam Pembuka Sesuai Gambar 33: Hallo, Admin anda login sebagai admin -->
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 26px 30px; border-radius: var(--radius-lg); margin-bottom: 28px; box-shadow: var(--shadow-md);">
                <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 6px;">Dashboard</h1>
                <p style="color: #cbd5e1; font-size: 1.05rem; margin: 0;">
                    <i class="fa-solid fa-circle-check" style="color: var(--accent);"></i>
                    <strong>Hallo, Admin anda login sebagai admin</strong>
                </p>
            </div>

            <!-- TIGA KARTU STATISTIK Sesuai Gambar 33 & Tabel 32: Total Pemesanan, Total Transaksi, Jumlah Pengguna -->
            <div class="stats-grid">
                <!-- 1. Total Pemesanan Sesuai Gambar 33 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pemesanan</h4>
                        <div class="stat-value"><?= str_pad($totalPemesanan, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>

                <!-- 2. Total Transaksi Sesuai Gambar 33 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Transaksi</h4>
                        <div class="stat-value" style="color: var(--accent-hover);"><?= str_pad($totalTransaksi, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>

                <!-- 3. Jumlah Pengguna Sesuai Gambar 33 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Jumlah Pengguna</h4>
                        <div class="stat-value" style="color: var(--sky);"><?= str_pad($jumlahPengguna, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
                <a href="<?= base_url('admin/paket.php?action=add') ?>" class="btn btn-primary" style="justify-content: flex-start; padding: 14px 20px;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--accent);"></i> Tambah Paket Wisata
                </a>
                <a href="<?= base_url('admin/kupon.php') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 14px 20px; background: #fff;">
                    <i class="fa-solid fa-ticket" style="color: var(--sky);"></i> Kelola Kupon Diskon
                </a>
                <a href="<?= base_url('admin/laporan.php') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 14px 20px; background: #fff;">
                    <i class="fa-solid fa-file-invoice" style="color: var(--success);"></i> Buka Laporan Pemesanan
                </a>
            </div>

            <!-- JADWAL KEBERANGKATAN BUS TERDEKAT -->
            <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h3 style="font-size: 1.15rem; color: var(--primary); margin: 0;">
                            <i class="fa-solid fa-calendar-days" style="color: var(--accent);"></i> Jadwal Keberangkatan Bus Terdekat
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Pantau alokasi armada bus dan tanggal jalan rombongan</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                    <?php if (empty($upcomingTrips)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.9rem;">
                            Belum ada jadwal keberangkatan rombongan mendatang.
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingTrips as $ut): ?>
                            <div style="background: #f8fafc; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; border-left: 4px solid var(--accent);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <div>
                                        <div style="font-weight: 800; font-size: 1rem; color: var(--primary);"><?= htmlspecialchars($ut['nama_rombongan']) ?></div>
                                        <div style="font-size: 0.82rem; color: var(--sky); font-weight: 600;"><?= htmlspecialchars($ut['nama_paket']) ?></div>
                                    </div>
                                    <span class="badge badge-warning" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($ut['jumlah_pax']) ?> Pax
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                                    <div><i class="fa-regular fa-calendar" style="color: var(--accent); width: 18px;"></i> <strong><?= format_tanggal_indo($ut['tanggal_berangkat']) ?></strong></div>
                                    <div><i class="fa-solid fa-bus" style="color: var(--sky); width: 18px;"></i> <?= htmlspecialchars($ut['bus']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Pemesanan Terkini -->
            <div class="card-table">
                <div class="card-header-flex">
                    <div>
                        <h3 style="font-size: 1.15rem; color: var(--primary); margin: 0;">Monitoring Pemesanan Masuk</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Daftar pemesanan tiket paket wisata dari seluruh konsumen</p>
                    </div>
                    <a href="<?= base_url('admin/laporan.php') ?>" class="btn btn-outline btn-sm">Lihat Semua Data</a>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Pemesan</th>
                                <th>Paket Wisata</th>
                                <th>Tanggal Berangkat</th>
                                <th>Rombongan</th>
                                <th>Pax</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 32px; color: var(--text-muted);">Belum ada data pemesanan yang masuk.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($recentOrders as $r): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($r['no_invoice'] ?: ('#ORD-' . $r['id'])) ?></strong></td>
                                        <td><?= htmlspecialchars($r['nama_pemesan']) ?></td>
                                        <td><?= htmlspecialchars($r['nama_paket']) ?></td>
                                        <td><?= format_tanggal_indo($r['tanggal_berangkat']) ?></td>
                                        <td><?= htmlspecialchars($r['nama_rombongan']) ?></td>
                                        <td><?= htmlspecialchars($r['jumlah_pax']) ?> Org</td>
                                        <td><strong style="color: var(--accent-hover);"><?= format_rupiah($r['harga']) ?></strong></td>
                                        <td>
                                            <?php 
                                            $st = $r['status'] ?? 'Menunggu Bukti';
                                            $bClass = 'badge-warning';
                                            if ($st === 'Lunas' || $st === 'Diterima') $bClass = 'badge-success';
                                            else if ($st === 'Ditolak') $bClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $bClass ?>"><?= htmlspecialchars($st) ?></span>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <?php if ($r['trx_id']): ?>
                                                <a href="<?= base_url('invoice.php?id=' . $r['trx_id']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Lihat Invoice">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                                <?php if ($st === 'Lunas' || $st === 'Diterima'): ?>
                                                    <a href="<?= base_url('etiket.php?id=' . $r['trx_id']) ?>" target="_blank" class="btn btn-sm" style="background: #0284c7; color: white;" title="Lihat E-Tiket">
                                                        <i class="fa-solid fa-ticket"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
