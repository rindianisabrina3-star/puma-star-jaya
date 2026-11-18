<?php
// checkout.php - Halaman Rincian Pemesanan Paket Wisata Sesuai Gambar 29, Tabel 28 & Tabel 42
$pageTitle = "Rincian Pemesanan Paket Wisata";
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$pemesananId = (int)($_GET['id'] ?? 0);
if ($pemesananId <= 0) {
    header("Location: " . base_url('paket.php'));
    exit;
}

// Ambil data pemesanan dan paket terkait
$stmt = $pdo->prepare("SELECT p.*, pkt.judul, pkt.destinasi, pkt.fasilitas, pkt.bus, pkt.harga as harga_satuan, pkt.foto, u.nama as nama_pemesan, u.email as email_pemesan
                       FROM pemesanan p
                       JOIN paket pkt ON p.paket_id = pkt.id
                       JOIN user u ON p.user_id = u.id
                       WHERE p.id = ? AND (p.user_id = ? OR ? IN ('admin', 'bendahara', 'direktur'))");
$stmt->execute([$pemesananId, $_SESSION['user_id'], $_SESSION['level']]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    header("Location: " . base_url('dashboard_konsumen.php'));
    exit;
}

// Cek apakah sudah ada transaksi untuk pemesanan ini
$stmtTrx = $pdo->prepare("SELECT * FROM transaksi WHERE pemesanan_id = ? LIMIT 1");
$stmtTrx->execute([$pemesananId]);
$transaksi = $stmtTrx->fetch();

$error = '';
$success = '';

// Handle upload bukti pembayaran (Sesuai Gambar 29, Tabel 28 & Tabel 42)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Silakan pilih file bukti transfer pembayaran (format JPG, PNG, atau PDF)!';
    } else {
        $file = $_FILES['bukti_bayar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($ext, $allowed)) {
            $error = 'Format file tidak diizinkan! Gunakan JPG, PNG, atau PDF.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran file bukti pembayaran maksimal 5MB!';
        } else {
            // Buat direktori upload jika belum ada
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'BUKTI_' . $pemesananId . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $noInvoice = $transaksi ? $transaksi['no_invoice'] : ('INV-' . date('Ymd') . '-' . str_pad($pemesananId, 4, '0', STR_PAD_LEFT));

                if ($transaksi) {
                    $stmtUpdateTrx = $pdo->prepare("UPDATE transaksi SET bukti_pembayaran = ?, status = 'Menunggu Verifikasi', updated_at = NOW() WHERE id = ?");
                    $stmtUpdateTrx->execute([$newFileName, $transaksi['id']]);
                } else {
                    $stmtInsertTrx = $pdo->prepare("INSERT INTO transaksi (user_id, pemesanan_id, no_invoice, status, bukti_pembayaran, created_at, updated_at) VALUES (?, ?, ?, 'Menunggu Verifikasi', ?, NOW(), NOW())");
                    $stmtInsertTrx->execute([$pesanan['user_id'], $pemesananId, $noInvoice, $newFileName]);
                }

                $success = 'Bukti pembayaran berhasil diupload! Menunggu proses verifikasi oleh Bendahara.';
                
                // Refresh data transaksi
                $stmtTrx->execute([$pemesananId]);
                $transaksi = $stmtTrx->fetch();
            } else {
                $error = 'Gagal menyimpan file upload ke server. Coba lagi.';
            }
        }
    }
}

// Handle Pembayaran Instan QRIS / Midtrans VA Simulator (Generate Otomatis Sesuai Nominal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instant_pay'])) {
    require_once __DIR__ . '/includes/receipt_generator.php';
    $noInvoice = $transaksi ? $transaksi['no_invoice'] : ('INV-' . date('Ymd') . '-' . str_pad($pemesananId, 4, '0', STR_PAD_LEFT));
    $buktiOtomatis = generate_automatic_receipt($pesanan, $noInvoice, 'QRIS Instan (zaa_twisty)');

    if ($transaksi) {
        $stmtUp = $pdo->prepare("UPDATE transaksi SET status = 'Lunas', bukti_pembayaran = ?, updated_at = NOW() WHERE id = ?");
        $stmtUp->execute([$buktiOtomatis, $transaksi['id']]);
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO transaksi (user_id, pemesanan_id, no_invoice, status, bukti_pembayaran, created_at, updated_at) VALUES (?, ?, ?, 'Lunas', ?, NOW(), NOW())");
        $stmtIns->execute([$pesanan['user_id'], $pemesananId, $noInvoice, $buktiOtomatis]);
    }

    $success = 'Pembayaran instan QRIS BERHASIL! Struk bukti pembayaran otomatis sebesar ' . format_rupiah($pesanan['harga']) . ' telah dibuat dan transaksi berstatus LUNAS.';
    $stmtTrx->execute([$pemesananId]);
    $transaksi = $stmtTrx->fetch();
}

// Handle Pembuatan Bukti Pembayaran Otomatis Sesuai Nominal Tagihan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_auto_proof'])) {
    require_once __DIR__ . '/includes/receipt_generator.php';
    $noInvoice = $transaksi ? $transaksi['no_invoice'] : ('INV-' . date('Ymd') . '-' . str_pad($pemesananId, 4, '0', STR_PAD_LEFT));
    $newFileName = generate_automatic_receipt($pesanan, $noInvoice, 'Transfer Bank Mandiri/BCA');

    if ($transaksi) {
        $stmtUpdateTrx = $pdo->prepare("UPDATE transaksi SET bukti_pembayaran = ?, status = 'Menunggu Verifikasi', updated_at = NOW() WHERE id = ?");
        $stmtUpdateTrx->execute([$newFileName, $transaksi['id']]);
    } else {
        $stmtInsertTrx = $pdo->prepare("INSERT INTO transaksi (user_id, pemesanan_id, no_invoice, status, bukti_pembayaran, created_at, updated_at) VALUES (?, ?, ?, 'Menunggu Verifikasi', ?, NOW(), NOW())");
        $stmtInsertTrx->execute([$pesanan['user_id'], $pemesananId, $noInvoice, $newFileName]);
    }

    $success = 'Struk bukti pembayaran sebesar ' . format_rupiah($pesanan['harga']) . ' berhasil dibuat otomatis sesuai nominal! Menunggu proses verifikasi Bendahara.';
    $stmtTrx->execute([$pemesananId]);
    $transaksi = $stmtTrx->fetch();
}
?>

<div class="section" style="padding-top: 40px;">
    <div class="container">
        <!-- Header Sesuai Gambar 29: Logo + Rincian Pemesanan Paket Wisata -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div class="brand-badge" style="font-size: 1.1rem; padding: 8px 12px;">PUMA</div>
                <div>
                    <h2 style="font-size: 1.8rem; margin: 0; color: var(--primary);">Rincian Pemesanan Paket Wisata</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">No. Pemesanan: #<?= str_pad($pesanan['id'], 5, '0', STR_PAD_LEFT) ?> | Tanggal Order: <?= format_tanggal_indo($pesanan['created_at']) ?></p>
                </div>
            </div>

            <div>
                <?php if ($transaksi): ?>
                    <a href="<?= base_url('invoice.php?id=' . $transaksi['id']) ?>" class="btn btn-outline">
                        <i class="fa-solid fa-file-invoice"></i> Lihat / Cetak Invoice
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('dashboard_konsumen.php') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-gauge"></i> Ke Dashboard
                </a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 32px;">
            <!-- SISI KIRI: Metode Pembayaran Transfer Bank Sesuai Gambar 29 -->
            <div>
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <h4 style="font-size: 1.1rem; margin-bottom: 16px; color: var(--primary);">
                        <i class="fa-solid fa-money-check-dollar" style="color: var(--accent);"></i> Rekening Pembayaran Resmi
                    </h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">Silakan transfer tagihan pemesanan ke salah satu rekening PT. Puma Star Jaya Tour di bawah ini:</p>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 12px; padding: 14px; border: 1.5px solid var(--accent); border-radius: var(--radius-md); background: #fffbeb; cursor: pointer;">
                            <input type="radio" name="bank_method" checked style="accent-color: var(--accent);">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: var(--primary);">Bank Mandiri</div>
                                <div style="font-family: monospace; font-size: 1.05rem; font-weight: 700; color: #0f172a;">132-00-888999-7</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">a.n. PT PUMA STAR JAYA TOUR</div>
                            </div>
                            <span class="badge badge-warning">Utama</span>
                        </label>

                        <label style="display: flex; align-items: center; gap: 12px; padding: 14px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #ffffff; cursor: pointer;">
                            <input type="radio" name="bank_method" style="accent-color: var(--accent);">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: var(--primary);">Bank Central Asia (BCA)</div>
                                <div style="font-family: monospace; font-size: 1.05rem; font-weight: 700; color: #0f172a;">035-7766-888</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">a.n. PT PUMA STAR JAYA</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 12px; padding: 14px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #ffffff; cursor: pointer;">
                            <input type="radio" name="bank_method" style="accent-color: var(--accent);">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: var(--primary);">Bank Rakyat Indonesia (BRI)</div>
                                <div style="font-family: monospace; font-size: 1.05rem; font-weight: 700; color: #0f172a;">0021-01-000333-50-8</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">a.n. PUMA STAR JAYA TOUR</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- OPSI INSTANT PAYMENT / QRIS & VIRTUAL ACCOUNT (MIDTRANS STYLE) -->
                <?php if (!$transaksi || ($transaksi['status'] !== 'Lunas' && $transaksi['status'] !== 'Diterima')): ?>
                    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-md); margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <span class="badge" style="background: #0ea5e9; color: #fff; font-size: 0.8rem;"><i class="fa-solid fa-bolt"></i> Opsi Instan</span>
                            <span style="font-size: 0.8rem; color: #94a3b8;">Verifikasi Otomatis</span>
                        </div>
                        <h4 style="font-size: 1.1rem; color: #fff; margin-bottom: 8px;">Bayar Instan QRIS / Midtrans</h4>
                        <p style="font-size: 0.84rem; color: #cbd5e1; margin-bottom: 16px;">
                            Pindai QRIS melalui GoPay, OVO, Dana, ShopeePay, atau m-Banking tanpa perlu upload bukti transfer.
                        </p>

                        <!-- Tampilan QRIS Resmi Asli -->
                        <div style="background: #ffffff; padding: 14px; border-radius: var(--radius-md); text-align: center; margin-bottom: 16px;">
                            <div style="max-width: 260px; margin: 0 auto; overflow: hidden; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                <a href="<?= base_url('assets/img/qris_real.jpg') ?>" target="_blank" title="Klik untuk perbesar QRIS">
                                    <img src="<?= base_url('assets/img/qris_real.jpg') ?>" alt="QRIS zaa_twisty" style="width: 100%; height: auto; display: block; object-fit: contain;">
                                </a>
                            </div>
                            <div style="font-size: 0.95rem; color: #0f172a; font-weight: 800; margin-top: 10px;">zaa_twisty</div>
                            <div style="font-size: 0.78rem; color: #475569; font-weight: 600;">NMID: ID1025448727392</div>
                            <div style="font-size: 1rem; font-weight: 800; color: #d97706; margin-top: 6px;">Total Tagihan: <?= format_rupiah($pesanan['harga']) ?></div>
                            <small style="color: #64748b; font-size: 0.72rem; display: block; margin-top: 2px;">*Klik gambar jika ingin memperbesar</small>
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="instant_pay" value="1">
                            <button type="submit" class="btn btn-accent" style="width: 100%; font-weight: 700;" onclick="return confirm('Simulasikan pembayaran QRIS instan sebesar <?= format_rupiah($pesanan['harga']) ?>? Status transaksi akan otomatis Lunas.');">
                                <i class="fa-solid fa-qrcode"></i> Konfirmasi Pembayaran QRIS Instan
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Status Pembayaran Saat Ini -->
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 24px; box-shadow: var(--shadow-sm);">
                    <h4 style="font-size: 1rem; margin-bottom: 12px; color: var(--primary);">Status Transaksi</h4>
                    <?php 
                    $status = $transaksi['status'] ?? 'Belum Dibayar';
                    $badgeClass = 'badge-warning';
                    if ($status === 'Lunas' || $status === 'Diterima') $badgeClass = 'badge-success';
                    else if ($status === 'Ditolak') $badgeClass = 'badge-danger';
                    ?>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span class="badge <?= $badgeClass ?>" style="font-size: 0.95rem; padding: 6px 14px;">
                            <i class="fa-solid fa-circle-dot"></i> <?= htmlspecialchars($status) ?>
                        </span>
                    </div>
                    <?php if ($transaksi && !empty($transaksi['no_invoice'])): ?>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            No. Invoice: <strong><?= htmlspecialchars($transaksi['no_invoice']) ?></strong>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="<?= base_url('invoice.php?id=' . $transaksi['id']) ?>" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-file-invoice"></i> Cetak Invoice
                            </a>
                            <?php if ($status === 'Lunas' || $status === 'Diterima'): ?>
                                <a href="<?= base_url('etiket.php?id=' . $transaksi['id']) ?>" class="btn btn-accent btn-sm" style="width: 100%; justify-content: center; background: #0284c7;">
                                    <i class="fa-solid fa-ticket"></i> Unduh / Cetak E-Tiket
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SISI KANAN: Detail Pemesanan, Gambar Paket, Program, Rincian Harga & Upload Bukti (Gambar 29) -->
            <div>
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 28px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <!-- Gambar & Program Destinasi Sesuai Gambar 29 -->
                    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 20px; margin-bottom: 24px;">
                        <div style="height: 180px; border-radius: var(--radius-md); overflow: hidden; background: #e2e8f0;">
                            <img src="<?= htmlspecialchars($pesanan['foto'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($pesanan['judul']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <!-- PROGRAM DESTINASI Sesuai Gambar 29 -->
                        <div style="background: var(--bg-body); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); font-size: 0.88rem;">
                            <div style="font-weight: 800; color: var(--primary); font-size: 0.95rem; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">PROGRAM DESTINASI</div>
                            <div style="font-weight: 700; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($pesanan['judul']) ?></div>
                            <div style="color: var(--text-muted); margin-bottom: 6px;"><i class="fa-solid fa-location-dot" style="color: var(--sky);"></i> <?= htmlspecialchars($pesanan['destinasi']) ?></div>
                            <div style="color: var(--text-muted);"><i class="fa-solid fa-bus" style="color: var(--accent);"></i> <?= htmlspecialchars($pesanan['bus']) ?></div>
                        </div>
                    </div>

                    <!-- Detail Pemesanan Paket Sesuai Gambar 29 -->
                    <h4 style="font-size: 1.1rem; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        Detail Pemesanan Paket
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.9rem; margin-bottom: 24px;">
                        <div>
                            <span style="color: var(--text-muted); display: block;">Nama Pemesan:</span>
                            <strong><?= htmlspecialchars($pesanan['nama_pemesan']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">Nama Rombongan:</span>
                            <strong><?= htmlspecialchars($pesanan['nama_rombongan']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">Tanggal Berangkat:</span>
                            <strong><?= format_tanggal_indo($pesanan['tanggal_berangkat']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">Jumlah Pax:</span>
                            <strong><?= htmlspecialchars($pesanan['jumlah_pax']) ?> Orang / Peserta</strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">Nomor Kursi Bus:</span>
                            <strong style="color: var(--accent-hover);"><?= htmlspecialchars($pesanan['kursi_dipilih'] ?: 'Diatur Otomatis oleh Kru') ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">No. WhatsApp:</span>
                            <strong><?= htmlspecialchars($pesanan['no_hp']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block;">Tarif Satuan:</span>
                            <strong><?= format_rupiah($pesanan['harga_satuan']) ?> / pax</strong>
                        </div>
                        <?php if ($pesanan['status_nego'] && $pesanan['status_nego'] !== 'None'): ?>
                            <div>
                                <span style="color: var(--text-muted); display: block;">Status Negosiasi:</span>
                                <?php
                                $sNego = $pesanan['status_nego'];
                                $badgeN = 'badge-warning';
                                if ($sNego === 'Disetujui') $badgeN = 'badge-success';
                                elseif ($sNego === 'Ditolak') $badgeN = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeN ?>"><i class="fa-solid fa-handshake"></i> Nego <?= htmlspecialchars($sNego) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Rincian Harga Sesuai Gambar 29: Rp. ... -->
                    <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: var(--radius-md); padding: 20px; border: 1.5px solid var(--border-color); margin-bottom: 28px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">RINCIAN HARGA TOTAL:</span>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">(<?= $pesanan['jumlah_pax'] ?> Pax &times; <?= format_rupiah($pesanan['harga_satuan']) ?>) - Diskon</div>
                            </div>
                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--accent-hover);">
                                <?= format_rupiah($pesanan['harga']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form Upload Bukti Pembayaran Sesuai Gambar 29, Tabel 28 & Tabel 42: Choose File & Kirim -->
                    <div style="border-top: 2px dashed var(--border-color); padding-top: 24px;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 8px; color: var(--primary);">
                            <i class="fa-solid fa-cloud-arrow-up" style="color: var(--accent);"></i> Upload Bukti Pembayaran
                        </h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                            Pilih file foto/struk transfer bukti bayar Anda melalui tombol <strong>Choose File</strong> di bawah ini:
                        </p>

                        <?php if ($transaksi && !empty($transaksi['bukti_pembayaran'])): ?>
                            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fa-solid fa-image" style="color: var(--sky); font-size: 1.4rem;"></i>
                                    <div>
                                        <div style="font-size: 0.88rem; font-weight: 700;">Bukti Terupload: <?= htmlspecialchars($transaksi['bukti_pembayaran']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">Diunggah pada <?= format_tanggal_indo($transaksi['created_at']) ?></div>
                                    </div>
                                </div>
                                <a href="<?= base_url('uploads/' . $transaksi['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-eye"></i> Lihat Bukti
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- OPSI 1: BUAT STRUK BUKTI OTOMATIS SESUAI NOMINAL (Fitur Rekomendasi) -->
                        <div style="background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #15803d; font-size: 0.95rem;">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Buat Bukti Bayar Otomatis
                                </strong>
                                <span class="badge badge-success" style="font-size: 0.72rem;">Sesuai Nominal: <?= format_rupiah($pesanan['harga']) ?></span>
                            </div>
                            <p style="font-size: 0.82rem; color: #166534; margin: 0 0 12px 0; line-height: 1.5;">
                                Sistem akan membuat struk bukti pembayaran transfer resmi berformat HD secara otomatis sesuai nominal tagihan Anda <strong>(<?= format_rupiah($pesanan['harga']) ?>)</strong>.
                            </p>
                            <form method="POST" action="">
                                <input type="hidden" name="generate_auto_proof" value="1">
                                <button type="submit" class="btn btn-sm btn-success" style="width: 100%; justify-content: center; font-weight: 700;" onclick="return confirm('Generate otomatis struk bukti pembayaran sebesar <?= format_rupiah($pesanan['harga']) ?>?');">
                                    <i class="fa-solid fa-file-circle-check"></i> Generate Bukti Bayar Otomatis (<?= format_rupiah($pesanan['harga']) ?>)
                                </button>
                            </form>
                        </div>

                        <div style="text-align: center; position: relative; margin-bottom: 20px;">
                            <span style="background: #fff; padding: 0 14px; color: #94a3b8; font-size: 0.8rem; font-weight: 700; position: relative; z-index: 1;">ATAU UPLOAD FILE MANUAL</span>
                            <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e2e8f0; z-index: 0;"></div>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" name="bukti_bayar" class="form-control" accept="image/*,.pdf" required>
                                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display: block;">Format didukung: JPG, PNG, PDF (Maks. 5MB)</small>
                            </div>

                            <button type="submit" class="btn btn-outline btn-lg" style="width: 100%; border-color: var(--border-color); color: var(--primary);">
                                <i class="fa-solid fa-upload"></i> Upload File Bukti Manual
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
