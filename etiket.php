<?php
// etiket.php - E-Tiket Wisata & Digital Boarding Pass Resmi PT. Puma Star Jaya Tour
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
$stmt = $pdo->prepare("SELECT t.*, p.tanggal_berangkat, p.nama_rombongan, p.jumlah_pax, p.kursi_dipilih, p.no_hp, p.harga as total_bayar, p.created_at as tgl_booking,
                              pkt.judul as rute_paket, pkt.destinasi, pkt.bus, pkt.fasilitas, pkt.harga as harga_satuan, pkt.foto as foto_paket,
                              u.nama as nama_pemesan, u.email as email_pemesan, u.id as user_owner_id
                       FROM transaksi t
                       JOIN pemesanan p ON t.pemesanan_id = p.id
                       JOIN paket pkt ON p.paket_id = pkt.id
                       JOIN user u ON t.user_id = u.id
                       WHERE t.id = ?");
$stmt->execute([$trxId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo "Data E-Tiket tidak ditemukan!";
    exit;
}

// Cek hak akses jika konsumen hanya boleh lihat tiket miliknya
if ($_SESSION['level'] === 'konsumen' && $ticket['user_owner_id'] != $_SESSION['user_id']) {
    echo "Anda tidak memiliki akses ke tiket ini.";
    exit;
}

// Kode Booking / PNR Unik
$bookingCode = 'PUMA-' . strtoupper(substr(md5($ticket['id'] . $ticket['no_invoice']), 0, 7));
$ticketNo = 'ETKT-' . date('Ymd', strtotime($ticket['created_at'])) . '-' . str_pad($ticket['id'], 4, '0', STR_PAD_LEFT);
$qrData = base_url('verifikasi.php?id=' . $ticket['id'] . '&code=' . $bookingCode);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($qrData);

$isLunas = in_array($ticket['status'], ['Lunas', 'Diterima']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket Wisata #<?= htmlspecialchars($bookingCode) ?> - PT. Puma Star Jaya Tour</title>
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
            padding: 24px 0 60px;
            margin: 0;
        }

        .ticket-page-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Top Bar Actions */
        .ticket-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* The Boarding Pass Card */
        .ticket-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 300px;
            position: relative;
        }

        /* Left Main Section */
        .ticket-main {
            padding: 36px 40px;
            position: relative;
        }

        /* Right Stub Section */
        .ticket-stub {
            background: #f8fafc;
            border-left: 2px dashed #cbd5e1;
            padding: 36px 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            position: relative;
        }

        /* Perforated Notches */
        .ticket-card::before, .ticket-card::after {
            content: '';
            position: absolute;
            width: 32px;
            height: 32px;
            background-color: #0b1329;
            border-radius: 50%;
            z-index: 10;
        }
        .ticket-card::before {
            top: -16px;
            right: 284px;
        }
        .ticket-card::after {
            bottom: -16px;
            right: 284px;
        }

        /* Header within ticket */
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 24px;
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 24px;
        }

        .ticket-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ticket-logo {
            background: #ff5722;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 1.4rem;
            padding: 8px 16px;
            border-radius: 10px;
            letter-spacing: 1px;
        }

        .ticket-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .status-valid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .status-pending {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        /* Destination Banner */
        .ticket-route {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .route-point h3 {
            margin: 0 0 4px 0;
            font-size: 1.4rem;
            font-family: 'Outfit', sans-serif;
            color: #ffffff;
        }
        .route-point p {
            margin: 0;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .route-icon {
            color: #ff5722;
            font-size: 1.6rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        /* Info Grid */
        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px 16px;
            margin-bottom: 24px;
        }

        .ticket-item .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        .ticket-item .value {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Facilities & Inclusions */
        .ticket-notes {
            background: #f8fafc;
            border-left: 4px solid #ff5722;
            padding: 14px 18px;
            border-radius: 0 10px 10px 0;
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
        }

        /* Right QR Section */
        .qr-frame {
            background: #ffffff;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-bottom: 12px;
        }
        .qr-frame img {
            width: 150px;
            height: 150px;
            display: block;
        }

        .ticket-code-label {
            font-family: monospace;
            font-size: 1.05rem;
            font-weight: 800;
            color: #ff5722;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .ticket-page-container {
                max-width: 100% !important;
                padding: 0 !important;
            }
            .ticket-card {
                box-shadow: none !important;
                border: 2px solid #0f172a !important;
                page-break-inside: avoid;
            }
            .ticket-card::before, .ticket-card::after {
                display: none !important;
            }
            .ticket-route {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @media (max-width: 768px) {
            .ticket-card {
                grid-template-columns: 1fr;
            }
            .ticket-stub {
                border-left: none;
                border-top: 2px dashed #cbd5e1;
            }
            .ticket-card::before, .ticket-card::after {
                display: none;
            }
            .ticket-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="ticket-page-container">
        <!-- Action Buttons Bar -->
        <div class="ticket-actions-bar no-print">
            <a href="<?= base_url('dashboard_konsumen.php') ?>" class="btn btn-outline" style="background: rgba(255,255,255,0.1); color: #ffffff; border-color: rgba(255,255,255,0.2);">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
            </a>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php
                $cleanPhone = preg_replace('/[^0-9]/', '', $ticket['no_hp']);
                if (substr($cleanPhone, 0, 1) === '0') $cleanPhone = '62' . substr($cleanPhone, 1);
                $waText = "Halo, berikut adalah E-Tiket Wisata Resmi Anda dari PT. Puma Star Jaya Tour:\n\n" .
                          "• Kode Booking: " . $bookingCode . "\n" .
                          "• Nama Pemesan: " . $ticket['nama_pemesan'] . " (" . $ticket['nama_rombongan'] . ")\n" .
                          "• Paket Wisata: " . $ticket['rute_paket'] . "\n" .
                          "• Destinasi: " . $ticket['destinasi'] . "\n" .
                          "• Keberangkatan: " . format_tanggal_indo($ticket['tanggal_berangkat']) . "\n" .
                          "• Armada Bus: " . $ticket['bus'] . "\n" .
                          "• Kursi Bus: " . ($ticket['kursi_dipilih'] ?: 'Diatur Otomatis oleh Kru') . "\n" .
                          "• Status: LUNAS & VALID\n\n" .
                          "Link Cek Tiket / Boarding Pass:\n" . base_url('verifikasi.php?id=' . $ticket['id']);
                ?>
                <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= urlencode($waText) ?>" target="_blank" class="btn btn-outline" style="background: #059669; color: #ffffff; border-color: #059669;">
                    <i class="fa-brands fa-whatsapp"></i> Kirim ke WhatsApp
                </a>
                <a href="<?= base_url('invoice.php?id=' . $ticket['id']) ?>" class="btn btn-outline" style="background: rgba(255,255,255,0.1); color: #ffffff; border-color: rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-file-invoice"></i> Lihat Invoice
                </a>
                <button onclick="window.print()" class="btn btn-accent btn-lg" style="box-shadow: 0 4px 14px rgba(255, 87, 34, 0.4);">
                    <i class="fa-solid fa-print"></i> Cetak / Unduh E-Tiket (PDF)
                </button>
            </div>
        </div>

        <!-- The Official E-Tiket & Boarding Pass -->
        <div class="ticket-card">
            <!-- Sisi Utama Tiket -->
            <div class="ticket-main">
                <div class="ticket-header">
                    <div class="ticket-brand">
                        <div class="ticket-logo">PUMA</div>
                        <div>
                            <div style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.15rem; color: #0f172a; line-height: 1.2;">
                                PT. PUMA STAR JAYA TOUR
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b;">
                                Biro Perjalanan Wisata & Transportasi Pariwisata Resmi
                            </div>
                        </div>
                    </div>

                    <div>
                        <?php if ($isLunas): ?>
                            <div class="ticket-status-badge status-valid">
                                <i class="fa-solid fa-circle-check"></i> E-Tiket Sah / Lunas
                            </div>
                        <?php else: ?>
                            <div class="ticket-status-badge status-pending">
                                <i class="fa-solid fa-clock"></i> <?= htmlspecialchars($ticket['status']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rute dan Destinasi Banner -->
                <div class="ticket-route">
                    <div class="route-point">
                        <p><i class="fa-solid fa-location-dot" style="color: #ff5722;"></i> Titik Keberangkatan</p>
                        <h3>KANTOR PUSAT PUMA</h3>
                        <p>Pool Bus Pariwisata Puma Star Jaya</p>
                    </div>

                    <div class="route-icon">
                        <i class="fa-solid fa-bus"></i>
                        <span style="font-size: 0.68rem; letter-spacing: 1px; color: #94a3b8;">DIRECT TOUR</span>
                    </div>

                    <div class="route-point" style="text-align: right;">
                        <p><i class="fa-solid fa-map-pin" style="color: #38bdf8;"></i> Destinasi Tujuan</p>
                        <h3><?= strtoupper(htmlspecialchars($ticket['destinasi'])) ?></h3>
                        <p><?= htmlspecialchars($ticket['rute_paket']) ?></p>
                    </div>
                </div>

                <!-- Grid Data Penumpang & Perjalanan -->
                <div class="ticket-grid">
                    <div class="ticket-item">
                        <span class="label">Nama Pemesan</span>
                        <div class="value"><?= htmlspecialchars($ticket['nama_pemesan']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Nama Rombongan</span>
                        <div class="value"><?= htmlspecialchars($ticket['nama_rombongan']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Jumlah Peserta</span>
                        <div class="value" style="color: #ff5722;"><?= htmlspecialchars($ticket['jumlah_pax']) ?> Orang (Pax)</div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Tanggal Berangkat</span>
                        <div class="value"><?= format_tanggal_indo($ticket['tanggal_berangkat']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Waktu Kumpul</span>
                        <div class="value">06:30 WIB (Pagi)</div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Armada Bus Pariwisata</span>
                        <div class="value"><?= htmlspecialchars($ticket['bus']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Nomor Kursi Bus</span>
                        <div class="value" style="color: #ff5722; font-weight: 800;"><?= htmlspecialchars($ticket['kursi_dipilih'] ?: 'Diatur Otomatis Kru') ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">No. Telepon / WhatsApp</span>
                        <div class="value"><?= htmlspecialchars($ticket['no_hp']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">No. Invoice Resmi</span>
                        <div class="value" style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($ticket['no_invoice']) ?></div>
                    </div>

                    <div class="ticket-item">
                        <span class="label">Total Pembayaran</span>
                        <div class="value" style="color: #059669; font-weight: 800;"><?= format_rupiah($ticket['total_bayar']) ?></div>
                    </div>
                </div>

                <!-- Catatan Ketentuan -->
                <div class="ticket-notes">
                    <strong><i class="fa-solid fa-circle-info" style="color: #ff5722;"></i> Ketentuan Boarding & Keberangkatan:</strong>
                    <ul style="margin: 4px 0 0 16px; padding: 0;">
                        <li>Peserta diharapkan tiba di titik kumpul selambat-lambatnya 30 menit sebelum jadwal keberangkatan.</li>
                        <li>Tunjukkan E-Tiket ini (versi cetak atau layar smartphone) kepada petugas kru bus saat proses boarding.</li>
                        <li>Fasilitas paket: Hotel Bintang, Tiket Masuk Seluruh Wisata, Konsumsi/Makan Terjadwal, Tour Guide & Dokumentasi.</li>
                    </ul>
                </div>
            </div>

            <!-- Sisi Kanan Stub & Barcode Boarding -->
            <div class="ticket-stub">
                <div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 1px; margin-bottom: 4px;">
                        BOARDING PASS STUB
                    </div>
                    <div class="ticket-code-label"><?= htmlspecialchars($bookingCode) ?></div>
                    <div style="font-size: 0.78rem; color: #94a3b8; font-family: monospace;"><?= htmlspecialchars($ticketNo) ?></div>
                </div>

                <!-- QR CODE CHECK-IN -->
                <div style="margin: 20px 0;">
                    <div class="qr-frame">
                        <img src="<?= $qrUrl ?>" alt="QR Code Boarding Pass Puma Tour">
                    </div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #0f172a; margin-top: 6px;">
                        <i class="fa-solid fa-qrcode"></i> SCAN UNTUK VERIFIKASI
                    </div>
                    <div style="font-size: 0.7rem; color: #64748b;">Validasi Keberangkatan Kru Bus</div>
                </div>

                <!-- Info Bantuan CS -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; width: 100%;">
                    <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 4px;">Bantuan Perjalanan / CS:</div>
                    <a href="https://wa.me/6285835688224?text=Halo%20Admin%20Puma%20Tour,%20saya%20mau%20konfirmasi%20E-Tiket%20<?= urlencode($bookingCode) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; color: #059669; font-weight: 700; font-size: 0.82rem; text-decoration: none;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i> 085835688224
                    </a>
                </div>
            </div>
        </div>

        <!-- Watermark / Footer -->
        <div style="text-align: center; margin-top: 24px; color: #94a3b8; font-size: 0.82rem;" class="no-print">
            &copy; <?= date('Y') ?> PT. Puma Star Jaya Tour & Travel &bull; Dokumen E-Tiket Resmi Terverifikasi Otomatis
        </div>
    </div>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>

