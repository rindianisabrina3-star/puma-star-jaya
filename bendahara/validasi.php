<?php
// bendahara/validasi.php - Sesuai Tabel 45: Validasi Pembayaran oleh Bendahara (Terima / Tolak)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'bendahara') {
    header("Location: " . base_url('login.php'));
    exit;
}

$bendaharaName = $_SESSION['nama'];
$message = '';
$error = '';

// Handle Proses Terima atau Tolak Sesuai Tabel 45: "Klik tombol terima atau tolak -> Berhasil mengubah status pembayaran"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_status'])) {
    $trxId = (int)$_POST['trx_id'];
    $act = $_POST['action_status']; // 'terima' atau 'tolak'

    if ($act === 'terima') {
        $newStatus = 'Lunas';
        $stmtUp = $pdo->prepare("UPDATE transaksi SET status = ?, updated_at = NOW() WHERE id = ?");
        if ($stmtUp->execute([$newStatus, $trxId])) {
            $message = "Pembayaran BERHASIL DITERIMA / DIVERIFIKASI LUNAS!";
        }
    } elseif ($act === 'tolak') {
        $newStatus = 'Ditolak';
        $stmtUp = $pdo->prepare("UPDATE transaksi SET status = ?, updated_at = NOW() WHERE id = ?");
        if ($stmtUp->execute([$newStatus, $trxId])) {
            $message = "Pembayaran BERHASIL DITOLAK (Status diubah menjadi Ditolak).";
        }
    }
}

// Detail View jika ada parameter ?detail=id (Sesuai Tabel 45: Masuk ke menu detail -> Klik tombol detail)
$detailTrx = null;
if (isset($_GET['detail'])) {
    $detailId = (int)$_GET['detail'];
    $stmtDetail = $pdo->prepare("SELECT t.*, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.no_hp, p.harga as total_bayar, p.created_at as tgl_booking,
                                        pkt.judul as nama_paket, pkt.destinasi, pkt.bus, pkt.harga as harga_satuan, pkt.foto,
                                        u.nama as nama_pemesan, u.email as email_pemesan
                                 FROM transaksi t
                                 JOIN pemesanan p ON t.pemesanan_id = p.id
                                 JOIN paket pkt ON p.paket_id = pkt.id
                                 JOIN user u ON t.user_id = u.id
                                 WHERE t.id = ?");
    $stmtDetail->execute([$detailId]);
    $detailTrx = $stmtDetail->fetch();
}

// Daftar Semua Transaksi untuk Validasi
$stmtAll = $pdo->query("SELECT t.*, p.nama_rombongan, p.jumlah_pax, p.harga, pkt.judul as nama_paket, u.nama as nama_pemesan
                        FROM transaksi t
                        JOIN pemesanan p ON t.pemesanan_id = p.id
                        JOIN paket pkt ON p.paket_id = pkt.id
                        JOIN user u ON t.user_id = u.id
                        ORDER BY t.id DESC");
$allTrx = $stmtAll->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Pembayaran - Bendahara PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <div class="header-profile">
                <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($bendaharaName) ?></div>
                <div class="user-avatar" style="background: #059669;"><i class="fa-solid fa-wallet"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <div style="margin-bottom: 24px;">
                <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Validasi Pembayaran Konsumen</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Pemeriksaan bukti transfer dan validasi status pembayaran (Tabel 45)</p>
            </div>

            <?php if (!empty($message)): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- DETAIL VIEW MODAL / BOX SESUAI TABEL 45: Masuk ke menu detail -> Klik tombol detail -->
            <?php if ($detailTrx): ?>
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 2px solid var(--accent); padding: 30px; box-shadow: var(--shadow-lg); margin-bottom: 32px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 20px;">
                        <h3 style="font-size: 1.3rem; margin: 0; color: var(--primary);">
                            <i class="fa-solid fa-file-invoice-dollar" style="color: var(--accent);"></i> Rincian Pemesanan & Pembayaran (#<?= htmlspecialchars($detailTrx['no_invoice']) ?>)
                        </h3>
                        <a href="<?= base_url('bendahara/validasi.php') ?>" class="btn btn-outline btn-sm">Tutup Rincian</a>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 28px;">
                        <!-- Data Detail -->
                        <div>
                            <table class="custom-table" style="margin-bottom: 20px;">
                                <tr>
                                    <th style="width: 35%;">Nama Pemesan</th>
                                    <td><strong><?= htmlspecialchars($detailTrx['nama_pemesan']) ?></strong> (<?= htmlspecialchars($detailTrx['email_pemesan']) ?>)</td>
                                </tr>
                                <tr>
                                    <th>Nama Rombongan</th>
                                    <td><?= htmlspecialchars($detailTrx['nama_rombongan']) ?></td>
                                </tr>
                                <tr>
                                    <th>Paket Wisata</th>
                                    <td><?= htmlspecialchars($detailTrx['nama_paket']) ?> (<?= htmlspecialchars($detailTrx['destinasi']) ?>)</td>
                                </tr>
                                <tr>
                                    <th>Waktu Keberangkatan</th>
                                    <td><strong><?= format_tanggal_indo($detailTrx['tanggal_berangkat']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Jumlah Peserta</th>
                                    <td><?= htmlspecialchars($detailTrx['jumlah_pax']) ?> Orang</td>
                                </tr>
                                <tr>
                                    <th>No. WhatsApp</th>
                                    <td><?= htmlspecialchars($detailTrx['no_hp']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Tagihan</th>
                                    <td><strong style="font-size: 1.25rem; color: var(--accent-hover);"><?= format_rupiah($detailTrx['total_bayar']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Status Saat Ini</th>
                                    <td>
                                        <span class="badge <?= ($detailTrx['status'] === 'Lunas' || $detailTrx['status'] === 'Diterima') ? 'badge-success' : (($detailTrx['status'] === 'Ditolak') ? 'badge-danger' : 'badge-warning') ?>">
                                            <?= htmlspecialchars($detailTrx['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- TOMBOL VALIDASI SESUAI TABEL 45: Klik tombol TERIMA atau TOLAK -->
                            <div style="background: #f8fafc; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <h4 style="font-size: 1rem; margin-bottom: 12px; color: var(--primary);">Keputusan Validasi Bendahara:</h4>
                                <form method="POST" action="" style="display: flex; gap: 12px;">
                                    <input type="hidden" name="trx_id" value="<?= $detailTrx['id'] ?>">

                                    <!-- Tombol TERIMA Sesuai Tabel 45 -->
                                    <button type="submit" name="action_status" value="terima" class="btn btn-success btn-lg" style="flex: 1;" onclick="return confirm('Konfirmasi TERIMA pembayaran ini dan ubah status menjadi LUNAS?');">
                                        <i class="fa-solid fa-circle-check"></i> TERIMA PEMBAYARAN
                                    </button>

                                    <!-- Tombol TOLAK Sesuai Tabel 45 -->
                                    <button type="submit" name="action_status" value="tolak" class="btn btn-danger btn-lg" style="flex: 1;" onclick="return confirm('Konfirmasi TOLAK pembayaran ini?');">
                                        <i class="fa-solid fa-circle-xmark"></i> TOLAK PEMBAYARAN
                                    </button>
                                </form>

                                <?php if ($detailTrx['status'] === 'Lunas' || $detailTrx['status'] === 'Diterima'): 
                                    $phoneClean = preg_replace('/[^0-9]/', '', $detailTrx['no_hp']);
                                    if (substr($phoneClean, 0, 1) === '0') $phoneClean = '62' . substr($phoneClean, 1);
                                    $waMsg = "Halo Bpk/Ibu *" . $detailTrx['nama_pemesan'] . "* (Rombongan: *" . $detailTrx['nama_rombongan'] . "*),%0A%0A" .
                                             "Pembayaran paket wisata *" . $detailTrx['nama_paket'] . "* telah *DIVERIFIKASI LUNAS* oleh Bendahara PT. Puma Star Jaya Tour & Travel.%0A%0A" .
                                             "• No. Invoice: *" . $detailTrx['no_invoice'] . "*%0A" .
                                             "• Total: *" . format_rupiah($detailTrx['total_bayar']) . "*%0A" .
                                             "• Keberangkatan: *" . format_tanggal_indo($detailTrx['tanggal_berangkat']) . "*%0A%0A" .
                                             "E-Tiket & Boarding Pass resmi Anda dapat diunduh pada tautan berikut:%0A" .
                                             base_url('etiket.php?id=' . $detailTrx['id']) . "%0A%0ATerima kasih telah mempercayakan perjalanan wisata Anda bersama Puma Star Jaya Tour!";
                                ?>
                                    <div style="margin-top: 14px;">
                                        <a href="https://wa.me/<?= $phoneClean ?>?text=<?= $waMsg ?>" target="_blank" class="btn btn-accent btn-lg" style="width: 100%; justify-content: center; background: #059669; border-color: #059669;">
                                            <i class="fa-brands fa-whatsapp"></i> Kirim Notifikasi E-Tiket ke WhatsApp Konsumen
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Gambar Bukti Transfer -->
                        <div>
                            <h4 style="font-size: 1rem; margin-bottom: 10px; color: var(--primary);">Bukti Transfer yang Diunggah Konsumen:</h4>
                            <div style="background: #f1f5f9; border-radius: var(--radius-md); padding: 12px; border: 1px solid var(--border-color); text-align: center;">
                                <?php if (!empty($detailTrx['bukti_pembayaran'])): ?>
                                    <a href="<?= base_url('uploads/' . $detailTrx['bukti_pembayaran']) ?>" target="_blank">
                                        <img src="<?= base_url('uploads/' . $detailTrx['bukti_pembayaran']) ?>" alt="Bukti Transfer" style="max-width: 100%; max-height: 380px; object-fit: contain; border-radius: var(--radius-sm); box-shadow: var(--shadow-sm);">
                                    </a>
                                    <div style="margin-top: 8px;">
                                        <a href="<?= base_url('uploads/' . $detailTrx['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-up-right-from-square"></i> Buka Gambar Ukuran Penuh
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 40px; color: var(--text-muted);">
                                        <i class="fa-solid fa-file-excel" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                                        Konsumen belum mengunggah file bukti pembayaran.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABEL SELURUH TRANSAKSI PEMESANAN (Tabel 45) -->
            <div class="card-table">
                <div class="card-header-flex">
                    <h3 style="font-size: 1.15rem; margin: 0; color: var(--primary);">Daftar Seluruh Transaksi Masuk (<?= count($allTrx) ?>)</h3>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Invoice</th>
                                <th>Pemesan</th>
                                <th>Paket Wisata</th>
                                <th>Total Tagihan</th>
                                <th>Bukti Bayar</th>
                                <th>Status Pembayaran</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($allTrx as $t): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($t['no_invoice']) ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['nama_pemesan']) ?></strong><br>
                                        <small style="color: var(--text-muted);"><?= htmlspecialchars($t['nama_rombongan']) ?> (<?= $t['jumlah_pax'] ?> Pax)</small>
                                    </td>
                                    <td><?= htmlspecialchars($t['nama_paket']) ?></td>
                                    <td><strong style="color: var(--accent-hover);"><?= format_rupiah($t['harga']) ?></strong></td>
                                    <td>
                                        <?php if (!empty($t['bukti_pembayaran'])): ?>
                                            <span class="badge badge-success"><i class="fa-solid fa-check"></i> Sudah Upload</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Belum Ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($t['status'] === 'Lunas' || $t['status'] === 'Diterima') ? 'badge-success' : (($t['status'] === 'Ditolak') ? 'badge-danger' : 'badge-warning') ?>">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                    </td>
                                    <!-- Tombol Detail Sesuai Tabel 45: Masuk ke menu detail -> Klik tombol detail -->
                                    <td style="text-align: center; white-space: nowrap;">
                                        <a href="<?= base_url('bendahara/validasi.php?detail=' . $t['id']) ?>" class="btn btn-accent btn-sm">
                                            <i class="fa-solid fa-magnifying-glass"></i> Detail & Validasi
                                        </a>
                                        <a href="<?= base_url('invoice.php?id=' . $t['id']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Invoice">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <?php if ($t['status'] === 'Lunas' || $t['status'] === 'Diterima'): ?>
                                            <a href="<?= base_url('etiket.php?id=' . $t['id']) ?>" target="_blank" class="btn btn-sm" style="background: #0284c7; color: white;" title="Lihat E-Tiket">
                                                <i class="fa-solid fa-ticket"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
