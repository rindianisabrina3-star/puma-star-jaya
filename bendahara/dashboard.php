<?php
// bendahara/dashboard.php - Sesuai Gambar 34 & Tabel 44 (Rancangan Dashboard Bendahara)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'bendahara') {
    header("Location: " . base_url('login.php'));
    exit;
}

$bendaharaName = $_SESSION['nama'];

// 1. Total Pemesanan Sesuai Gambar 34
$totalPemesanan = $pdo->query("SELECT COUNT(*) FROM pemesanan")->fetchColumn();

// 2. Total Transaksi Sesuai Gambar 34
$totalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();

// 3. Jumlah Pengguna Sesuai Gambar 34
$jumlahPengguna = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();

// Transaksi menunggu validasi bendahara
$stmtPending = $pdo->query("SELECT t.*, p.nama_rombongan, p.jumlah_pax, p.harga, pkt.judul as nama_paket, u.nama as nama_pemesan
                            FROM transaksi t
                            JOIN pemesanan p ON t.pemesanan_id = p.id
                            JOIN paket pkt ON p.paket_id = pkt.id
                            JOIN user u ON t.user_id = u.id
                            WHERE t.status = 'Menunggu Verifikasi'
                            ORDER BY t.id DESC");
$pendingTransactions = $stmtPending->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <!-- SIDEBAR BENDAHARA Sesuai Gambar 34 -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <!-- Topbar Sesuai Gambar 34 -->
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <div class="header-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($bendaharaName) ?></div>
                    <div style="font-size: 0.75rem; color: var(--accent-hover); font-weight: 700; text-transform: uppercase;">Bendahara Keuangan</div>
                </div>
                <div class="user-avatar" style="background: #059669;"><i class="fa-solid fa-wallet"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Salam Pembuka Sesuai Gambar 34: Halo, Bendahara anda login sebagai bendahara -->
            <div style="background: linear-gradient(135deg, #064e3b, #047857); color: #fff; padding: 26px 30px; border-radius: var(--radius-lg); margin-bottom: 28px; box-shadow: var(--shadow-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 6px;">Dashboard</h1>
                    <p style="color: #d1fae5; font-size: 1.05rem; margin: 0;">
                        <i class="fa-solid fa-circle-check" style="color: var(--accent);"></i>
                        <strong>Halo, Bendahara anda login sebagai bendahara</strong>
                    </p>
                </div>
                <div>
                    <a href="<?= base_url('bendahara/validasi.php') ?>" class="btn btn-accent btn-lg">
                        <i class="fa-solid fa-stamp"></i> Validasi Pembayaran (<?= count($pendingTransactions) ?>)
                    </a>
                </div>
            </div>

            <!-- TIGA KARTU STATISTIK Sesuai Gambar 34: Total Pemesanan (00), Total Transaksi (00), Jumlah Pengguna (00) -->
            <div class="stats-grid">
                <!-- 1. Total Pemesanan Sesuai Gambar 34 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pemesanan</h4>
                        <div class="stat-value"><?= str_pad($totalPemesanan, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon primary"><i class="fa-solid fa-ticket"></i></div>
                </div>

                <!-- 2. Total Transaksi Sesuai Gambar 34 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Transaksi</h4>
                        <div class="stat-value" style="color: var(--accent-hover);"><?= str_pad($totalTransaksi, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon warning"><i class="fa-solid fa-money-bill-transfer"></i></div>
                </div>

                <!-- 3. Jumlah Pengguna Sesuai Gambar 34 -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Jumlah Pengguna</h4>
                        <div class="stat-value" style="color: #059669;"><?= str_pad($jumlahPengguna, 2, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="stat-icon success"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>

            <!-- Antrean Pembayaran Menunggu Validasi Bendahara (Tabel 45) -->
            <div class="card-table">
                <div class="card-header-flex">
                    <div>
                        <h3 style="font-size: 1.15rem; color: var(--primary); margin: 0;">Pembayaran Menunggu Validasi Anda</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Periksa bukti transfer dan klik tombol Terima atau Tolak (Tabel 45)</p>
                    </div>
                    <a href="<?= base_url('bendahara/validasi.php') ?>" class="btn btn-outline btn-sm">Lihat Semua</a>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pemesan</th>
                                <th>Paket Wisata</th>
                                <th>Total Tagihan</th>
                                <th>Bukti Transfer</th>
                                <th>Status</th>
                                <th style="text-align: center;">Tindakan Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingTransactions)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        <i class="fa-solid fa-circle-check" style="color: #059669; font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
                                        Tidak ada pembayaran yang menunggu validasi saat ini. Semua telah diproses!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingTransactions as $pt): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($pt['no_invoice']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($pt['nama_pemesan']) ?><br>
                                            <small style="color: var(--text-muted);"><?= htmlspecialchars($pt['nama_rombongan']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($pt['nama_paket']) ?></td>
                                        <td><strong style="color: var(--accent-hover);"><?= format_rupiah($pt['harga']) ?></strong></td>
                                        <td>
                                            <?php if ($pt['bukti_pembayaran']): ?>
                                                <a href="<?= base_url('uploads/' . $pt['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                                    <i class="fa-solid fa-image"></i> Cek Bukti
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">Belum ada berkas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-warning"><?= htmlspecialchars($pt['status']) ?></span></td>
                                        <td style="text-align: center;">
                                            <a href="<?= base_url('bendahara/validasi.php?detail=' . $pt['id']) ?>" class="btn btn-accent btn-sm">
                                                <i class="fa-solid fa-magnifying-glass"></i> Proses Validasi
                                            </a>
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
