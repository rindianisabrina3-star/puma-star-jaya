<?php
// riwayat.php - Sesuai Gambar 31 & Tabel 30 (Halaman Pemesanan Paket Wisata / Riwayat Pemesanan)
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['nama'];

// Ambil seluruh data pemesanan milik user ini (Tabel 30)
$stmt = $pdo->prepare("SELECT p.*, u.nama as nama_akun, pkt.judul as nama_paket, t.id as trx_id, t.status as status_bayar, t.no_invoice
                       FROM pemesanan p
                       JOIN user u ON p.user_id = u.id
                       JOIN paket pkt ON p.paket_id = pkt.id
                       LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                       WHERE p.user_id = ?
                       ORDER BY p.id DESC");
$stmt->execute([$userId]);
$riwayatPemesanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Paket Wisata - Riwayat Pemesanan - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <!-- 1. SIDEBAR Sesuai Gambar 31: Menu Pemesanan Paket Wisata Aktif -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- 2. MAIN CONTENT Sesuai Gambar 31 & Tabel 30 -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle" title="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="header-profile">
                <div style="text-align: right; display: none; display: md-block;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary);"><?= htmlspecialchars($userName) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Konsumen</div>
                </div>
                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Judul Halaman Sesuai Gambar 31: Pemesanan Paket Wisata -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Pemesanan Paket Wisata</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Daftar seluruh pemesanan yang sedang berjalan dan riwayat perjalanan Anda</p>
                </div>
                <a href="<?= base_url('paket.php') ?>" class="btn btn-accent">
                    <i class="fa-solid fa-plus"></i> Pesan Paket Baru
                </a>
            </div>

            <!-- Tabel Riwayat Sesuai Gambar 31 & Tabel 30: No, Nama, Paket, Tanggal Berangkat, Nama Rombongan, Jumlah Pax, No Hp, Status, Aksi -->
            <div class="card-table">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Paket</th>
                                <th>Tanggal Berangkat</th>
                                <th>Nama Rombongan</th>
                                <th>Jumlah Pax</th>
                                <th>No Hp</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riwayatPemesanan)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        Belum ada data pemesanan. Silakan pilih paket wisata dan lakukan pemesanan terlebih dahulu.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($riwayatPemesanan as $row): ?>
                                    <tr>
                                        <!-- 1. No Sesuai Tabel 30 -->
                                        <td><?= $no++ ?></td>

                                        <!-- 2. Nama Sesuai Tabel 30 -->
                                        <td><strong><?= htmlspecialchars($row['nama_akun']) ?></strong></td>

                                        <!-- 3. Paket Sesuai Tabel 30 -->
                                        <td><?= htmlspecialchars($row['nama_paket']) ?></td>

                                        <!-- 4. Tanggal Berangkat Sesuai Tabel 30 -->
                                        <td><?= format_tanggal_indo($row['tanggal_berangkat']) ?></td>

                                        <!-- 5. Nama Rombongan Sesuai Tabel 30 -->
                                        <td><?= htmlspecialchars($row['nama_rombongan']) ?></td>

                                        <!-- 6. Jumlah Pax Sesuai Tabel 30 -->
                                        <td><?= htmlspecialchars($row['jumlah_pax']) ?> Org</td>

                                        <!-- 7. No Hp Sesuai Tabel 30 -->
                                        <td><?= htmlspecialchars($row['no_hp']) ?></td>

                                        <!-- 8. Status Sesuai Tabel 30 -->
                                        <td>
                                            <?php 
                                            $st = $row['status_bayar'] ?? 'Menunggu Bukti Transfer';
                                            $badgeClass = 'badge-warning';
                                            if ($st === 'Lunas' || $st === 'Diterima') $badgeClass = 'badge-success';
                                            else if ($st === 'Ditolak') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($st) ?></span>
                                        </td>

                                        <!-- 9. Aksi Sesuai Tabel 30: Untuk melihat detail pemesanan -->
                                        <td style="text-align: center; white-space: nowrap;">
                                            <a href="<?= base_url('checkout.php?id=' . $row['id']) ?>" class="btn btn-outline btn-sm" title="Lihat Detail & Upload Bukti">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                            <?php if ($row['trx_id']): ?>
                                                <a href="<?= base_url('invoice.php?id=' . $row['trx_id']) ?>" class="btn btn-accent btn-sm" title="Cetak Invoice">
                                                    <i class="fa-solid fa-print"></i> Invoice
                                                </a>
                                                <?php if ($st === 'Lunas' || $st === 'Diterima'): ?>
                                                    <a href="<?= base_url('etiket.php?id=' . $row['trx_id']) ?>" class="btn btn-sm" style="background: #0284c7; color: #fff;" title="Cetak E-Tiket Wisata">
                                                        <i class="fa-solid fa-ticket"></i> E-Tiket
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

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
