<?php
// verifikasi.php - Halaman Publik Validasi QR E-Tiket (Bisa discan langsung via Kamera HP / Google Lens / Kru Bus)
require_once __DIR__ . '/config/database.php';

$trxId = (int)($_GET['id'] ?? 0);
$code = clean_input($_GET['code'] ?? '');

$ticket = null;

if ($trxId > 0) {
    $stmt = $pdo->prepare("SELECT t.*, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih, p.no_hp, p.harga as total_bayar,
                                  pkt.judul as rute_paket, pkt.destinasi, pkt.bus, pkt.fasilitas,
                                  u.nama as nama_pemesan, u.email as email_pemesan
                           FROM transaksi t
                           JOIN pemesanan p ON t.pemesanan_id = p.id
                           JOIN paket pkt ON p.paket_id = pkt.id
                           JOIN user u ON t.user_id = u.id
                           WHERE t.id = ?");
    $stmt->execute([$trxId]);
    $ticket = $stmt->fetch();
}

$isValid = false;
$bookingCode = '';
if ($ticket) {
    $expectedCode = 'PUMA-' . strtoupper(substr(md5($ticket['id'] . $ticket['no_invoice']), 0, 7));
    $bookingCode = $expectedCode;
    $isValid = in_array($ticket['status'], ['Lunas', 'Diterima']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi E-Tiket Wisata - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body {
            background-color: #0b1329;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            margin: 0;
        }

        .verify-card {
            background: #ffffff;
            border-radius: 24px;
            max-width: 540px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .verify-badge-header {
            padding: 32px 24px;
            color: #ffffff;
        }
        .header-valid {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }
        .header-invalid {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 16px;
            border: 3px solid rgba(255, 255, 255, 0.4);
        }

        .verify-body {
            padding: 28px 24px;
            text-align: left;
        }

        .verify-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 0.92rem;
        }
        .verify-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .verify-table th {
            padding: 10px 0;
            color: #64748b;
            font-weight: 600;
            width: 42%;
        }
        .verify-table td {
            padding: 10px 0;
            color: #0f172a;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="verify-card">
        <?php if ($ticket && $isValid): ?>
            <!-- STATUS VALID -->
            <div class="verify-badge-header header-valid">
                <div class="verify-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 style="margin: 0 0 6px 0; font-family: 'Outfit', sans-serif; font-size: 1.6rem; color: #fff;">
                    E-TIKET SAH & VALID
                </h2>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.95;">
                    Terverifikasi Resmi oleh PT. Puma Star Jaya Tour
                </p>
                <div style="margin-top: 12px; display: inline-block; background: rgba(0,0,0,0.2); padding: 4px 16px; border-radius: 999px; font-family: monospace; font-size: 1.05rem; font-weight: 800; letter-spacing: 1px;">
                    <?= htmlspecialchars($bookingCode) ?>
                </div>
            </div>

            <div class="verify-body">
                <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 12px 14px; border-radius: 6px; font-size: 0.85rem; color: #065f46; margin-bottom: 20px;">
                    <i class="fa-solid fa-bus"></i> <strong>Izin Boarding Diterima:</strong> Penumpang berhak menaiki armada bus pariwisata sesuai jadwal keberangkatan.
                </div>

                <table class="verify-table">
                    <tr>
                        <th>Nama Pemesan</th>
                        <td><?= htmlspecialchars($ticket['nama_pemesan']) ?></td>
                    </tr>
                    <tr>
                        <th>Rombongan</th>
                        <td><?= htmlspecialchars($ticket['nama_rombongan']) ?></td>
                    </tr>
                    <tr>
                        <th>Destinasi Rute</th>
                        <td><?= htmlspecialchars($ticket['destinasi']) ?></td>
                    </tr>
                    <tr>
                        <th>Paket Wisata</th>
                        <td><?= htmlspecialchars($ticket['rute_paket']) ?></td>
                    </tr>
                    <tr>
                        <th>Armada Bus</th>
                        <td><?= htmlspecialchars($ticket['bus']) ?></td>
                    </tr>
                    <tr>
                        <th>Nomor Kursi</th>
                        <td><strong style="color: #ff5722;"><?= htmlspecialchars($ticket['kursi_dipilih'] ?: 'Bebas / Diatur Kru Bus') ?></strong></td>
                    </tr>
                    <tr>
                        <th>Jumlah Peserta</th>
                        <td><?= htmlspecialchars($ticket['jumlah_pax']) ?> Orang (Pax)</td>
                    </tr>
                    <tr>
                        <th>Tgl Keberangkatan</th>
                        <td><?= format_tanggal_indo($ticket['tanggal_berangkat']) ?></td>
                    </tr>
                    <tr>
                        <th>Titik Kumpul</th>
                        <td>Pool Pusat Bus Puma Star Jaya (06:30 WIB)</td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td><span style="color: #059669; font-weight: 800;">LUNAS (<?= format_rupiah($ticket['total_bayar']) ?>)</span></td>
                    </tr>
                </table>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= base_url('etiket.php?id=' . $ticket['id']) ?>" class="btn btn-outline" style="flex: 1; justify-content: center;">
                        <i class="fa-solid fa-ticket"></i> Buka E-Tiket
                    </a>
                    <a href="<?= base_url() ?>" class="btn btn-accent" style="flex: 1; justify-content: center;">
                        <i class="fa-solid fa-house"></i> Beranda Web
                    </a>
                </div>
            </div>

        <?php elseif ($ticket && !$isValid): ?>
            <!-- STATUS BELUM LUNAS / MENUNGGU -->
            <div class="verify-badge-header header-invalid" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                <div class="verify-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h2 style="margin: 0 0 6px 0; font-family: 'Outfit', sans-serif; font-size: 1.6rem; color: #fff;">
                    MENUNGGU PEMBAYARAN
                </h2>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.95;">
                    Status Tiket: <?= htmlspecialchars($ticket['status']) ?>
                </p>
            </div>
            <div class="verify-body">
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">
                    Tiket untuk rombongan <strong><?= htmlspecialchars($ticket['nama_rombongan']) ?></strong> belum terkonfirmasi lunas oleh manajemen keuangan. Silakan selesaikan pembayaran terlebih dahulu.
                </p>
                <a href="<?= base_url('checkout.php?id=' . $ticket['pemesanan_id']) ?>" class="btn btn-accent" style="width: 100%; justify-content: center;">
                    Buka Halaman Checkout
                </a>
            </div>

        <?php else: ?>
            <!-- STATUS TIDAK DITEMUKAN -->
            <div class="verify-badge-header header-invalid">
                <div class="verify-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2 style="margin: 0 0 6px 0; font-family: 'Outfit', sans-serif; font-size: 1.6rem; color: #fff;">
                    TIKET TIDAK DITEMUKAN
                </h2>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.95;">
                    QR Code tidak terdaftar dalam database resmi PT. Puma Star Jaya Tour.
                </p>
            </div>
            <div class="verify-body" style="text-align: center;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                    Pastikan Anda memindai QR Code resmi yang tertera pada E-Tiket PT. Puma Star Jaya Tour.
                </p>
                <a href="<?= base_url() ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">
                    Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>

        <div style="background: #f8fafc; border-top: 1px solid #f1f5f9; padding: 14px; font-size: 0.78rem; color: #94a3b8; text-align: center;">
            Sistem Verifikasi Tiket Digital &bull; PT. Puma Star Jaya Tour
        </div>
    </div>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>

