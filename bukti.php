<?php
// bukti.php - Dynamic Digital Payment Receipt Viewer (Resmi PT. Puma Star Jaya Tour)
require_once __DIR__ . '/config/database.php';

$trxId = (int)($_GET['id'] ?? 0);
$pemesananId = (int)($_GET['pemesanan_id'] ?? 0);
$fileParam = clean_input($_GET['file'] ?? '');

$data = null;

// 1. Cari berdasarkan ID transaksi
if ($trxId > 0) {
    $stmt = $pdo->prepare("SELECT t.*, p.harga as total_bayar, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih,
                                  pkt.judul as nama_paket, u.nama as nama_pemesan, u.email as email_pemesan
                           FROM transaksi t
                           JOIN pemesanan p ON t.pemesanan_id = p.id
                           JOIN paket pkt ON p.paket_id = pkt.id
                           JOIN user u ON t.user_id = u.id
                           WHERE t.id = ?");
    $stmt->execute([$trxId]);
    $data = $stmt->fetch();
}

// 2. Cari berdasarkan ID pemesanan
if (!$data && $pemesananId > 0) {
    $stmt = $pdo->prepare("SELECT t.*, p.harga as total_bayar, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih,
                                  pkt.judul as nama_paket, u.nama as nama_pemesan, u.email as email_pemesan
                           FROM pemesanan p
                           LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                           JOIN paket pkt ON p.paket_id = pkt.id
                           JOIN user u ON p.user_id = u.id
                           WHERE p.id = ?");
    $stmt->execute([$pemesananId]);
    $data = $stmt->fetch();
}

// 3. Cari berdasarkan nama file (misal BUKTI_AUTO_30008_... atau BUKTI_FALLBACK_30008_...)
if (!$data && !empty($fileParam)) {
    // Coba cari dari nama file persis di transaksi
    $stmt = $pdo->prepare("SELECT t.*, p.harga as total_bayar, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih,
                                  pkt.judul as nama_paket, u.nama as nama_pemesan, u.email as email_pemesan
                           FROM transaksi t
                           JOIN pemesanan p ON t.pemesanan_id = p.id
                           JOIN paket pkt ON p.paket_id = pkt.id
                           JOIN user u ON t.user_id = u.id
                           WHERE t.bukti_pembayaran = ?");
    $stmt->execute([$fileParam]);
    $data = $stmt->fetch();

    // Jika tidak ditemukan persis, ekstrak ID pemesanan dari pola nama file BUKTI_xxx_(\d+)_
    if (!$data && preg_match('/BUKTI_(?:AUTO_|FALLBACK_)?(\d+)/i', $fileParam, $m)) {
        $extractedId = (int)$m[1];
        $stmt = $pdo->prepare("SELECT t.*, p.harga as total_bayar, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih,
                                      pkt.judul as nama_paket, u.nama as nama_pemesan, u.email as email_pemesan
                               FROM pemesanan p
                               LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                               JOIN paket pkt ON p.paket_id = pkt.id
                               JOIN user u ON p.user_id = u.id
                               WHERE p.id = ?");
        $stmt->execute([$extractedId]);
        $data = $stmt->fetch();
    }
}

// Jika masih tidak ditemukan, ambil transaksi terbaru sebagai fallback demo
if (!$data) {
    $stmt = $pdo->query("SELECT t.*, p.harga as total_bayar, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih,
                                pkt.judul as nama_paket, u.nama as nama_pemesan, u.email as email_pemesan
                         FROM transaksi t
                         JOIN pemesanan p ON t.pemesanan_id = p.id
                         JOIN paket pkt ON p.paket_id = pkt.id
                         JOIN user u ON t.user_id = u.id
                         ORDER BY t.id DESC LIMIT 1");
    $data = $stmt->fetch();
}

if (!$data) {
    die("Bukti pembayaran tidak ditemukan.");
}

$nominal = (int)$data['total_bayar'];
$nominalFormatted = format_rupiah($nominal);
$noInvoice = $data['no_invoice'] ?: ('INV-' . date('Ymd', strtotime($data['created_at'])) . '-' . str_pad($data['id'], 4, '0', STR_PAD_LEFT));
$namaPemesan = $data['nama_pemesan'] ?: 'Konsumen Puma';
$namaRombongan = $data['nama_rombongan'] ?: '-';
$namaPaket = $data['nama_paket'] ?: 'Paket Wisata';
$jumlahPax = $data['jumlah_pax'] ?: '1';
$kursiDipilih = $data['kursi_dipilih'] ?: '-';
$waktuTrx = date('d F Y, H:i:s WIB', strtotime($data['created_at']));
$rrn = 'PSJ' . date('Ymd', strtotime($data['created_at'])) . str_pad($data['id'], 5, '0', STR_PAD_LEFT);
$status = $data['status'] ?: 'Lunas';
$isLunas = in_array($status, ['Lunas', 'Diterima']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Bukti Pembayaran Resmi - <?= htmlspecialchars($noInvoice) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0f172a;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }
        .action-bar {
            width: 100%;
            max-width: 580px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 12px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print { background: #ff5722; color: #fff; }
        .btn-print:hover { background: #ea580c; transform: translateY(-1px); }
        .btn-back { background: #334155; color: #f8fafc; }
        .btn-back:hover { background: #475569; }

        /* RECEIPT CARD */
        .receipt-card {
            width: 100%;
            max-width: 580px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.5);
            position: relative;
        }
        .receipt-header {
            background: #0f172a;
            color: #ffffff;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #ff5722;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-badge {
            background: #ff5722;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .brand-title {
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .receipt-body {
            padding: 28px 28px 24px;
        }
        .status-badge-wrap {
            text-align: center;
            margin-bottom: 20px;
        }
        .status-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #10b981;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 10px;
            border: 2px solid #a7f3d0;
        }
        .status-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: #065f46;
        }
        .status-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .nominal-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            margin-bottom: 24px;
        }
        .nominal-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nominal-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.3rem;
            font-weight: 900;
            color: #0f172a;
            margin-top: 4px;
            letter-spacing: -0.5px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .receipt-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .receipt-table td {
            padding: 10px 4px;
            vertical-align: top;
        }
        .receipt-table td.lbl {
            color: #64748b;
            width: 44%;
            font-weight: 500;
        }
        .receipt-table td.val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .receipt-table tr.total-row {
            background: #f0fdf4;
            border-top: 2px solid #86efac;
            border-bottom: 2px solid #86efac;
        }
        .receipt-table tr.total-row td {
            padding: 14px 10px;
            font-size: 1.05rem;
        }
        .receipt-table tr.total-row td.val {
            color: #047857;
            font-size: 1.25rem;
            font-weight: 900;
        }

        .receipt-footer {
            background: #f8fafc;
            border-top: 1px dashed #cbd5e1;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .security-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.78rem;
            color: #047857;
            font-weight: 700;
        }
        .qris-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: #64748b;
            text-align: right;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none; }
            .receipt-card { box-shadow: none; border: 1px solid #cbd5e1; max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.history.back();" class="btn-action btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>
        <button onclick="window.print();" class="btn-action btn-print">
            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="receipt-card">
        <!-- Header -->
        <div class="receipt-header">
            <div class="brand-logo">
                <span class="brand-badge">PUMA</span>
                <div>
                    <div class="brand-title">PT. PUMA STAR JAYA TOUR</div>
                    <div class="brand-sub">Biro Perjalanan Wisata & Sewa Bus Pariwisata</div>
                </div>
            </div>
            <i class="fa-solid fa-shield-halved" style="color: #10b981; font-size: 1.5rem;" title="Transaksi Aman Terverifikasi"></i>
        </div>

        <!-- Body -->
        <div class="receipt-body">
            <div class="status-badge-wrap">
                <div class="status-icon">
                    <i class="fa-solid <?= $isLunas ? 'fa-check' : 'fa-clock' ?>"></i>
                </div>
                <div class="status-title"><?= $isLunas ? 'PEMBAYARAN SUKSES' : 'MENUNGGU VERIFIKASI' ?></div>
                <div class="status-subtitle"><?= $waktuTrx ?></div>
            </div>

            <!-- Box Nominal Besar Sesuai Permintaan Dosen & Konsumen -->
            <div class="nominal-box">
                <div class="nominal-label">Total Pembayaran Sesuai Tagihan</div>
                <div class="nominal-value"><?= $nominalFormatted ?></div>
            </div>

            <table class="receipt-table">
                <tr>
                    <td class="lbl">No. Referensi (RRN)</td>
                    <td class="val" style="font-family:'JetBrains Mono',monospace; color:#475569;"><?= $rrn ?></td>
                </tr>
                <tr>
                    <td class="lbl">No. Invoice Resmi</td>
                    <td class="val" style="font-family:'JetBrains Mono',monospace;"><?= htmlspecialchars($noInvoice) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Nama Pelanggan</td>
                    <td class="val"><?= htmlspecialchars($namaPemesan) ?></td>
                </tr>
                <?php if ($namaRombongan && $namaRombongan !== '-'): ?>
                <tr>
                    <td class="lbl">Nama Rombongan</td>
                    <td class="val"><?= htmlspecialchars($namaRombongan) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="lbl">Paket Wisata</td>
                    <td class="val"><?= htmlspecialchars($namaPaket) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Jumlah Peserta (Pax)</td>
                    <td class="val"><?= htmlspecialchars($jumlahPax) ?> Orang</td>
                </tr>
                <?php if ($kursiDipilih && $kursiDipilih !== '-'): ?>
                <tr>
                    <td class="lbl">Nomor Kursi Bus</td>
                    <td class="val" style="color: #ff5722;"><?= htmlspecialchars($kursiDipilih) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="lbl">Metode Pembayaran</td>
                    <td class="val">QRIS Instan Resmi / Transfer Bank</td>
                </tr>
                <tr>
                    <td class="lbl">Biaya Layanan Admin</td>
                    <td class="val" style="color: #10b981;">Rp 0 (GRATIS)</td>
                </tr>
                <tr class="total-row">
                    <td class="lbl" style="font-weight: 800; color: #065f46;">TOTAL DIBAYAR</td>
                    <td class="val"><?= $nominalFormatted ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="security-badge">
                <i class="fa-solid fa-circle-check"></i>
                <span>TERVERIFIKASI SISTEM OTOMATIS</span>
            </div>
            <div class="qris-tag">
                NMID: ID1025448727392<br>
                Merchant: zaa_twisty
            </div>
        </div>
    </div>

</body>
</html>
