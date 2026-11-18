<?php
// invoice.php - Halaman Cetak Invoice Sesuai Gambar 32, Tabel 31 & Tabel 43
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$trxId = (int)($_GET['id'] ?? 0);
if ($trxId <= 0) {
    header("Location: " . base_url('dashboard_konsumen.php'));
    exit;
}

// Ambil data transaksi, pemesanan, paket, dan user
$stmt = $pdo->prepare("SELECT t.*, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.no_hp, p.harga as total_bayar, p.created_at as tgl_booking,
                              pkt.judul as rute_paket, pkt.destinasi, pkt.bus, pkt.harga as harga_satuan,
                              u.nama as nama_pemesan, u.email as email_pemesan
                       FROM transaksi t
                       JOIN pemesanan p ON t.pemesanan_id = p.id
                       JOIN paket pkt ON p.paket_id = pkt.id
                       JOIN user u ON t.user_id = u.id
                       WHERE t.id = ?");
$stmt->execute([$trxId]);
$inv = $stmt->fetch();

if (!$inv) {
    echo "Data invoice tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVOICE #<?= htmlspecialchars($inv['no_invoice']) ?> - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body style="background: #e2e8f0; padding: 20px 0;">

    <!-- Tombol Aksi Sesuai Gambar 32: Tombol Cetak / Print & Kembali -->
    <div class="container no-print" style="max-width: 850px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="<?= base_url('dashboard_konsumen.php') ?>" class="btn btn-outline" style="background: #ffffff;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <div style="display: flex; gap: 10px;">
            <?php if (in_array($inv['status'], ['Lunas', 'Diterima'])): ?>
                <a href="<?= base_url('etiket.php?id=' . $inv['id']) ?>" class="btn btn-outline" style="background: #0284c7; color: #ffffff; border-color: #0284c7;">
                    <i class="fa-solid fa-ticket"></i> Lihat E-Tiket Wisata
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-accent btn-lg">
                <i class="fa-solid fa-print"></i> Cetak Invoice (Print)
            </button>
        </div>
    </div>

    <!-- Wrapper Dokumen Invoice Sesuai Gambar 32 & Tabel 31 -->
    <div class="invoice-wrapper">
        <!-- Kop Surat & Logo Sesuai Gambar 32 -->
        <div class="invoice-header-kop">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="brand-badge" style="font-size: 1.5rem; padding: 12px 18px;">PUMA</div>
                <div class="invoice-company">
                    <h2>PT. PUMA STAR JAYA TOUR & TRAVEL</h2>
                    <p><i class="fa-solid fa-location-dot"></i> Jl. Raya Pariwisata No. 88, Pusat Biro Perjalanan & Bus Pariwisata</p>
                    <p><i class="fa-solid fa-phone"></i> 085835688224 | <i class="fa-solid fa-envelope"></i> pumatour@gmail.com</p>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary);">NO. INVOICE:</div>
                <div style="font-family: monospace; font-size: 1.1rem; color: var(--accent-hover); font-weight: 700;"><?= htmlspecialchars($inv['no_invoice']) ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Tanggal: <?= date('d/m/Y', strtotime($inv['created_at'])) ?></div>
            </div>
        </div>

        <!-- Judul Dokumen Sesuai Gambar 32: INVOICE -->
        <h1 class="invoice-title">INVOICE PEMESANAN</h1>

        <!-- Tabel Rincian Invoice Sesuai Gambar 32 & Tabel 31 -->
        <table class="invoice-table">
            <tbody>
                <!-- 1. Nama: Melihat nama pemesanan Sesuai Tabel 31 -->
                <tr>
                    <th style="width: 32%;">Nama Pemesan / Rombongan</th>
                    <td><strong><?= htmlspecialchars($inv['nama_pemesan']) ?></strong> (<?= htmlspecialchars($inv['nama_rombongan']) ?>)</td>
                </tr>

                <!-- 2. Jumlah orang: Melihat jumlah orang yang dipesan Sesuai Tabel 31 -->
                <tr>
                    <th>Jumlah Orang (Pax)</th>
                    <td><strong><?= htmlspecialchars($inv['jumlah_pax']) ?> Orang / Peserta</strong></td>
                </tr>

                <!-- 3. Rute: Melihat rute perjalanan yang akan dilakukan Sesuai Tabel 31 -->
                <tr>
                    <th>Rute & Destinasi Perjalanan</th>
                    <td>
                        <strong><?= htmlspecialchars($inv['rute_paket']) ?></strong><br>
                        <small style="color: var(--text-muted);">Tujuan: <?= htmlspecialchars($inv['destinasi']) ?> | Bus: <?= htmlspecialchars($inv['bus']) ?></small>
                    </td>
                </tr>

                <!-- 4. Waktu keberangkatan: Melihat waktu pemberangkatan perjalanan Sesuai Tabel 31 -->
                <tr>
                    <th>Waktu Keberangkatan</th>
                    <td><strong><?= format_tanggal_indo($inv['tanggal_berangkat']) ?></strong></td>
                </tr>

                <!-- 5. Keterangan: Melihat status pembayaran Sesuai Tabel 31 -->
                <tr>
                    <th>Keterangan (Status Pembayaran)</th>
                    <td>
                        <?php 
                        $status = $inv['status'];
                        $color = ($status === 'Lunas' || $status === 'Diterima') ? '#059669' : (($status === 'Ditolak') ? '#dc2626' : '#d97706');
                        ?>
                        <span style="font-weight: 800; color: <?= $color ?>; text-transform: uppercase;">
                            <i class="fa-solid fa-stamp"></i> <?= htmlspecialchars($status) ?>
                        </span>
                    </td>
                </tr>

                <!-- 6. Tanggal booking: Melihat tanggal kapan melakukan pemesanan Sesuai Tabel 31 -->
                <tr>
                    <th>Tanggal Booking</th>
                    <td><?= format_tanggal_indo($inv['tgl_booking']) ?></td>
                </tr>

                <!-- 7. Total: Melihat total pemesanan Sesuai Tabel 31 -->
                <tr style="background-color: #f8fafc;">
                    <th style="font-size: 1.1rem; color: var(--primary);">TOTAL PEMESANAN</th>
                    <td style="font-size: 1.4rem; font-weight: 800; color: var(--accent-hover);">
                        <?= format_rupiah($inv['total_bayar']) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Info Bukti Pembayaran -->
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
            <strong>Catatan:</strong> Dokumen ini merupakan bukti sah transaksi pemesanan tiket paket wisata pada PT. Puma Star Jaya Tour & Travel.
        </div>

        <!-- Footer Tanda Tangan Sesuai Gambar 32: Puma Jaya Star -->
        <div class="invoice-signature">
            <div class="signature-box">
                <p style="font-size: 0.9rem; margin-bottom: 6px;">Puma Star Jaya,</p>
                <div class="signature-space" style="display: flex; align-items: center; justify-content: center;">
                    <!-- Stempel / Signature Badge -->
                    <div style="border: 2px solid #059669; color: #059669; padding: 6px 14px; border-radius: 8px; font-weight: 800; transform: rotate(-6deg); font-size: 0.85rem; letter-spacing: 1px;">
                        VERIFIED BY PUMA TOUR
                    </div>
                </div>
                <p style="font-weight: 700; border-top: 1px solid #1e293b; padding-top: 6px; margin: 0;">Manajemen Keuangan</p>
            </div>
        </div>
    </div>

</body>
</html>
