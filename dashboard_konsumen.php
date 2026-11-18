<?php
// dashboard_konsumen.php - Sesuai Gambar 30 & Tabel 29 (Rancangan Dashboard Konsumen)
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['nama'];

// Hitung statistik konsumen
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE user_id = ?");
$stmtTotal->execute([$userId]);
$totalPesanan = $stmtTotal->fetchColumn();

$stmtLunas = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status IN ('Lunas', 'Diterima')");
$stmtLunas->execute([$userId]);
$totalLunas = $stmtLunas->fetchColumn();

$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ? AND status = 'Menunggu Verifikasi'");
$stmtPending->execute([$userId]);
$totalPending = $stmtPending->fetchColumn();

// Ambil 5 pesanan terbaru
$stmtLatest = $pdo->prepare("SELECT p.*, pkt.judul as nama_paket, t.id as trx_id, t.status, t.no_invoice
                             FROM pemesanan p
                             JOIN paket pkt ON p.paket_id = pkt.id
                             LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                             WHERE p.user_id = ?
                             ORDER BY p.id DESC LIMIT 5");
$stmtLatest->execute([$userId]);
$recentOrders = $stmtLatest->fetchAll();

$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $paketIdRev = (int)$_POST['paket_id'];
    $pemesananIdRev = (int)$_POST['pemesanan_id'];
    $bintangRev = max(1, min(5, (int)$_POST['bintang']));
    $komentarRev = clean_input($_POST['komentar'] ?? '');

    if (!empty($komentarRev) && $paketIdRev > 0) {
        $stmtRev = $pdo->prepare("INSERT INTO ulasan (user_id, paket_id, pemesanan_id, bintang, komentar, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmtRev->execute([$userId, $paketIdRev, $pemesananIdRev, $bintangRev, $komentarRev]);
        $reviewSuccess = 'Terima kasih! Ulasan bintang & testimoni wisata Anda berhasil dikirim.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Konsumen - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <!-- 1. SIDEBAR KONSUMEN Sesuai Gambar 30 -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- 2. MAIN CONTENT AREA Sesuai Gambar 30 -->
    <main class="dashboard-main">
        <!-- Dashboard Topbar Header Sesuai Gambar 30: Toggle Menu (≡) & Avatar Profile -->
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle" title="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="header-profile">
                <div style="text-align: right; display: none; display: md-block;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary);"><?= htmlspecialchars($userName) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Konsumen</div>
                </div>
                <div class="user-avatar" title="<?= htmlspecialchars($userName) ?>">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
        </header>

        <!-- Dashboard Body Content Sesuai Gambar 30 -->
        <div class="dashboard-body">
            <!-- Salam Pembuka Sesuai Gambar 30: Halo, anda login sebagai konsumen -->
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 28px 32px; border-radius: var(--radius-lg); margin-bottom: 28px; box-shadow: var(--shadow-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 6px;">Dashboard</h1>
                    <p style="color: #cbd5e1; font-size: 1.05rem; margin: 0;">
                        <i class="fa-solid fa-circle-check" style="color: var(--accent);"></i> 
                        <strong>Halo, anda login sebagai konsumen</strong>
                    </p>
                </div>
                <div>
                    <a href="<?= base_url('paket.php') ?>" class="btn btn-accent btn-lg">
                        <i class="fa-solid fa-cart-plus"></i> Pesan Paket Wisata Baru
                    </a>
                </div>
            </div>

            <!-- Kartu Statistik Aktivitas Konsumen -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pemesanan</h4>
                        <div class="stat-value"><?= $totalPesanan ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Menunggu Verifikasi</h4>
                        <div class="stat-value" style="color: var(--warning);"><?= $totalPending ?></div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Transaksi Lunas</h4>
                        <div class="stat-value" style="color: var(--success);"><?= $totalLunas ?></div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <!-- Tabel Pemesanan Terbaru Sesuai Gambar 30 & Tabel 29 -->
            <div class="card-table">
                <div class="card-header-flex">
                    <div>
                        <h3 style="font-size: 1.15rem; margin-bottom: 4px; color: var(--primary);">Pemesanan Paket Wisata Terbaru</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Pantau status proses pemesanan dan bukti pembayaran Anda</p>
                    </div>
                    <a href="<?= base_url('riwayat.php') ?>" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-list"></i> Lihat Seluruh Riwayat
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Paket Wisata</th>
                                <th>Rombongan</th>
                                <th>Tanggal Berangkat</th>
                                <th>Pax</th>
                                <th>Total Harga</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        Belum ada riwayat pemesanan paket wisata. <a href="<?= base_url('paket.php') ?>" style="color: var(--accent-hover); font-weight: 700;">Pesan sekarang!</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($recentOrders as $ro): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($ro['nama_paket']) ?></strong></td>
                                        <td><?= htmlspecialchars($ro['nama_rombongan']) ?></td>
                                        <td><?= format_tanggal_indo($ro['tanggal_berangkat']) ?></td>
                                        <td><?= htmlspecialchars($ro['jumlah_pax']) ?> Org</td>
                                        <td><strong style="color: var(--accent-hover);"><?= format_rupiah($ro['harga']) ?></strong></td>
                                        <td>
                                            <?php 
                                            $st = $ro['status'] ?? 'Belum Upload Bukti';
                                            $bClass = 'badge-warning';
                                            if ($st === 'Lunas' || $st === 'Diterima') $bClass = 'badge-success';
                                            else if ($st === 'Ditolak') $bClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $bClass ?>"><?= htmlspecialchars($st) ?></span>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a href="<?= base_url('checkout.php?id=' . $ro['id']) ?>" class="btn btn-outline btn-sm" title="Rincian & Pembayaran">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                            <?php if ($ro['trx_id']): ?>
                                                <a href="<?= base_url('invoice.php?id=' . $ro['trx_id']) ?>" class="btn btn-accent btn-sm" title="Cetak Invoice">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                                <?php if ($st === 'Lunas' || $st === 'Diterima'): ?>
                                                    <a href="<?= base_url('etiket.php?id=' . $ro['trx_id']) ?>" class="btn btn-sm" style="background: #0284c7; color: #fff;" title="Cetak E-Tiket">
                                                        <i class="fa-solid fa-ticket"></i> E-Tiket
                                                    </a>
                                                    <button type="button" onclick="openReviewModal(<?= $ro['paket_id'] ?>, <?= $ro['id'] ?>, '<?= htmlspecialchars(addslashes($ro['nama_paket'])) ?>')" class="btn btn-sm btn-outline" style="color: #d97706; border-color: #fcd34d;" title="Beri Rating & Ulasan">
                                                        <i class="fa-solid fa-star"></i> Ulasan
                                                    </button>
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

    <!-- MODAL BERI ULASAN & RATING WISATA -->
    <div id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 480px; width: 100%; overflow: hidden; box-shadow: var(--shadow-lg);">
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; color: #fff;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #fff;"><i class="fa-solid fa-star" style="color: #f59e0b;"></i> Beri Ulasan Wisata</h3>
                <button type="button" onclick="closeReviewModal()" style="background: none; border: none; color: #fff; font-size: 1.3rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST" action="" style="padding: 24px;">
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" name="paket_id" id="revPaketId" value="">
                <input type="hidden" name="pemesanan_id" id="revPemesananId" value="">

                <div style="margin-bottom: 16px;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Paket Wisata:</span>
                    <strong id="revPaketTitle" style="font-size: 1.05rem; color: var(--primary);">-</strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Tingkat Kepuasan (Rating Bintang)</label>
                    <div style="display: flex; gap: 8px; font-size: 1.8rem; color: #cbd5e1; cursor: pointer;" id="starContainer">
                        <i class="fa-solid fa-star star-item" data-val="1" onclick="setRating(1)"></i>
                        <i class="fa-solid fa-star star-item" data-val="2" onclick="setRating(2)"></i>
                        <i class="fa-solid fa-star star-item" data-val="3" onclick="setRating(3)"></i>
                        <i class="fa-solid fa-star star-item" data-val="4" onclick="setRating(4)"></i>
                        <i class="fa-solid fa-star star-item" data-val="5" onclick="setRating(5)"></i>
                    </div>
                    <input type="hidden" name="bintang" id="starInput" value="5">
                </div>

                <div class="form-group">
                    <label class="form-label">Komentar & Testimoni Anda</label>
                    <textarea name="komentar" class="form-control" rows="3" placeholder="Ceritakan kenyamanan bus, pelayanan kru, dan pengalaman berwisata bersama Puma Star Jaya..." required></textarea>
                </div>

                <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Ulasan
                </button>
            </form>
        </div>
    </div>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script>
        // Toggle Sidebar on mobile/desktop
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });

        function openReviewModal(paketId, pemesananId, title) {
            document.getElementById('revPaketId').value = paketId;
            document.getElementById('revPemesananId').value = pemesananId;
            document.getElementById('revPaketTitle').innerText = title;
            setRating(5);
            document.getElementById('reviewModal').style.display = 'flex';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        function setRating(val) {
            document.getElementById('starInput').value = val;
            const stars = document.querySelectorAll('.star-item');
            stars.forEach((s, idx) => {
                if (idx < val) {
                    s.style.color = '#f59e0b';
                } else {
                    s.style.color = '#cbd5e1';
                }
            });
        }
    </script>
</body>
</html>
