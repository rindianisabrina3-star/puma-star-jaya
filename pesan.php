<?php
// pesan.php - Formulir Pemesanan Paket Wisata + Negosiasi In-System + Pemilihan Kursi Bus Interaktif
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Pastikan pengguna sudah login sesuai Gambar 28
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

$paketId = (int)($_GET['id'] ?? 0);
if ($paketId <= 0) {
    header("Location: " . base_url('paket.php'));
    exit;
}

// Ambil data paket wisata dari database (Tabel 18 & Tabel 40)
$stmt = $pdo->prepare("SELECT * FROM paket WHERE id = ?");
$stmt->execute([$paketId]);
$paket = $stmt->fetch();

if (!$paket) {
    header("Location: " . base_url('paket.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = clean_input($_POST['action_type'] ?? 'normal');
    $tanggalBerangkat = clean_input($_POST['tanggal_berangkat'] ?? '');
    $namaRombongan = clean_input($_POST['nama_rombongan'] ?? '');
    $jumlahPax = (int)($_POST['jumlah_pax'] ?? 0);
    $noHp = clean_input($_POST['no_hp'] ?? '');
    $kodeKupon = clean_input($_POST['kode_kupon'] ?? '');
    $kursiDipilih = clean_input($_POST['kursi_dipilih'] ?? '');

    // Validasi field umum
    if (empty($tanggalBerangkat) || empty($namaRombongan) || $jumlahPax <= 0 || empty($noHp)) {
        $error = 'Semua field formulir pemesanan wajib diisi dengan lengkap dan benar!';
    } else {
        $userId = $_SESSION['user_id'];

        if ($actionType === 'nego') {
            // PROSES PENGAJUAN NEGOSIASI MELALUI SISTEM (Sesuai Saran Skripsi Poin 1)
            $hargaNego = (int)($_POST['nego_harga'] ?? 0);
            $catatanNego = clean_input($_POST['catatan_nego'] ?? '');

            if ($hargaNego <= 0) {
                $error = 'Nominal harga tawaran negosiasi harus lebih dari Rp 0!';
            } else {
                $totalHarga = $hargaNego * $jumlahPax;
                $stmtInsert = $pdo->prepare("INSERT INTO pemesanan (user_id, paket_id, harga, harga_nego, status_nego, catatan_nego, tanggal_berangkat, nama_rombongan, jumlah_pax, kursi_dipilih, no_hp, created_at, updated_at) VALUES (?, ?, ?, ?, 'Menunggu', ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                if ($stmtInsert->execute([$userId, $paket['id'], $totalHarga, $hargaNego, $catatanNego, $tanggalBerangkat, $namaRombongan, $jumlahPax, $kursiDipilih, $noHp])) {
                    $pemesananId = $pdo->lastInsertId();
                    header("Location: " . base_url('checkout.php?id=' . $pemesananId . '&nego=pending'));
                    exit;
                } else {
                    $error = 'Gagal mengajukan negosiasi ke sistem. Coba lagi.';
                }
            }
        } else {
            // PEMESANAN NORMAL
            $totalHarga = $paket['harga'] * $jumlahPax;
            $potongan = 0;

            // Cek Kupon jika diisi
            if (!empty($kodeKupon)) {
                $stmtKupon = $pdo->prepare("SELECT * FROM kupon WHERE BINARY kode = ? LIMIT 1");
                $stmtKupon->execute([$kodeKupon]);
                $kuponData = $stmtKupon->fetch();
                if ($kuponData) {
                    $potongan = (int)$kuponData['potongan'];
                    $totalHarga = max(0, $totalHarga - $potongan);
                }
            }

            $stmtInsert = $pdo->prepare("INSERT INTO pemesanan (user_id, paket_id, harga, tanggal_berangkat, nama_rombongan, jumlah_pax, kursi_dipilih, no_hp, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            if ($stmtInsert->execute([$userId, $paket['id'], $totalHarga, $tanggalBerangkat, $namaRombongan, $jumlahPax, $kursiDipilih, $noHp])) {
                $pemesananId = $pdo->lastInsertId();
                header("Location: " . base_url('checkout.php?id=' . $pemesananId . (!empty($kodeKupon) ? '&kupon=' . urlencode($kodeKupon) : '')));
                exit;
            } else {
                $error = 'Gagal menyimpan data pemesanan. Silakan coba kembali.';
            }
        }
    }
}
?>

<div class="section" style="padding-top: 40px;">
    <div class="container">
        <!-- Judul Halaman Sesuai Gambar 28: Pemesanan Paket -->
        <div style="text-align: center; margin-bottom: 36px;">
            <span class="slide-badge"><i class="fa-solid fa-file-signature"></i> Reservasi Perjalanan</span>
            <h1 style="font-size: 2.4rem; color: var(--primary);">Pemesanan Paket Wisata</h1>
            <p style="color: var(--text-muted);">Silakan lengkapi data pemesanan paket wisata rombongan Anda</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="order-layout">
            <!-- SISI KIRI: Ringkasan Program Paket Sesuai Gambar 28 -->
            <div>
                <div class="order-package-summary">
                    <div style="border-radius: var(--radius-md); overflow: hidden; height: 230px; margin-bottom: 20px; background: #e2e8f0;">
                        <img src="<?= htmlspecialchars($paket['foto'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($paket['judul']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <!-- Program Box Sesuai Gambar 28 -->
                    <div style="background: var(--bg-body); border-radius: var(--radius-md); padding: 20px; border: 1px solid var(--border-color);">
                        <h4 style="font-size: 1.15rem; margin-bottom: 14px; border-bottom: 2px solid var(--accent); padding-bottom: 6px; display: inline-block;">
                            Program: <?= htmlspecialchars($paket['judul']) ?>
                        </h4>

                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 14px; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($paket['deskripsi'])) ?>
                        </div>

                        <div style="font-size: 0.88rem; margin-bottom: 8px;">
                            <strong><i class="fa-solid fa-location-dot" style="color: var(--sky);"></i> Destinasi:</strong><br>
                            <?= htmlspecialchars($paket['destinasi']) ?>
                        </div>

                        <div style="font-size: 0.88rem; margin-bottom: 8px;">
                            <strong><i class="fa-solid fa-bus" style="color: var(--accent);"></i> Armada:</strong><br>
                            <?= htmlspecialchars($paket['bus']) ?>
                        </div>

                        <div style="font-size: 0.88rem; margin-bottom: 14px;">
                            <strong><i class="fa-solid fa-star" style="color: #f59e0b;"></i> Fasilitas:</strong><br>
                            <?= htmlspecialchars($paket['fasilitas'] ?: 'Hotel Bintang, Makan, Tiket Masuk, Tour Guide, P3K & Dokumentasi') ?>
                        </div>

                        <!-- Harga Satuan Sesuai Gambar 28 -->
                        <div style="background: #ffffff; padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: center; margin-top: 14px;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Tarif Resmi Per Peserta:</span>
                            <span style="font-size: 1.4rem; font-weight: 800; color: var(--accent-hover);"><?= format_rupiah($paket['harga']) ?> / Pax</span>
                        </div>

                        <!-- Tombol Tawar Harga Sesuai Gambar 28 & Saran Skripsi -->
                        <div style="margin-top: 18px;">
                            <button type="button" onclick="openNegoModal()" class="btn btn-outline" style="width: 100%; background: #ffffff; border-color: var(--accent); color: var(--accent-hover);">
                                <i class="fa-solid fa-handshake"></i> Tawar Harga Rombongan (Nego)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN: Kupon & Formulir Pemesanan Sesuai Gambar 28 -->
            <div>
                <!-- Fitur Kupon Sesuai Gambar 28 -->
                <div class="coupon-box">
                    <h4 style="font-size: 1rem; margin-bottom: 10px; color: var(--primary);">
                        <i class="fa-solid fa-tags" style="color: var(--accent);"></i> Kupon Potongan Harga
                    </h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">Punya kode diskon rombongan? Masukkan kodenya di bawah ini:</p>
                    <div class="coupon-input-group">
                        <input type="text" id="inputKupon" class="form-control" placeholder="Contoh: PUMAJAYA25" style="background: #ffffff; text-transform: uppercase;">
                        <button type="button" id="btnCekKupon" class="btn btn-accent" style="white-space: nowrap;">
                            <i class="fa-solid fa-check"></i> Cek Potongan Harga
                        </button>
                    </div>
                    <div id="couponResult" style="margin-top: 10px; font-size: 0.88rem; font-weight: 600; display: none;"></div>
                </div>

                <!-- Formulir Pemesanan Sesuai Gambar 28 -->
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 32px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.35rem; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                        Formulir Pemesanan
                    </h3>

                    <form method="POST" action="" id="mainOrderForm">
                        <input type="hidden" name="action_type" id="formActionType" value="normal">
                        <input type="hidden" name="kode_kupon" id="hiddenKodeKupon" value="">
                        <input type="hidden" name="kursi_dipilih" id="hiddenKursiDipilih" value="">

                        <!-- 1. Tanggal Berangkat Sesuai Gambar 28 -->
                        <div class="form-group">
                            <label class="form-label" for="tanggal_berangkat"><i class="fa-regular fa-calendar" style="color: var(--accent);"></i> Tanggal Berangkat</label>
                            <input type="date" id="tanggal_berangkat" name="tanggal_berangkat" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- 2. Nama Rombongan Sesuai Gambar 28 -->
                        <div class="form-group">
                            <label class="form-label" for="nama_rombongan"><i class="fa-solid fa-users" style="color: var(--accent);"></i> Nama Rombongan / Instansi / Sekolah</label>
                            <input type="text" id="nama_rombongan" name="nama_rombongan" class="form-control" placeholder="Contoh: SMA Negeri 1 Pariwisata" required>
                        </div>

                        <!-- 3. Jumlah px (Pax) Sesuai Gambar 28 -->
                        <div class="form-group">
                            <label class="form-label" for="jumlah_pax"><i class="fa-solid fa-user-group" style="color: var(--accent);"></i> Jumlah Pax (Peserta)</label>
                            <input type="number" id="jumlah_pax" name="jumlah_pax" class="form-control" min="1" max="500" placeholder="Contoh: 4" required oninput="updateSeatCountGuide()">
                            <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display: block;">Kapasitas armada bus Puma Star Jaya menyesuaikan jumlah peserta.</small>
                        </div>

                        <!-- FITUR 3: DENAH PEMILIHAN KURSI BUS INTERAKTIF -->
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <label class="form-label" style="margin: 0; font-size: 0.92rem;">
                                    <i class="fa-solid fa-chair" style="color: var(--accent);"></i> Pilih Kursi Bus (Opsional)
                                </label>
                                <span id="seatCounterBadge" class="badge badge-warning" style="font-size: 0.75rem;">0 Kursi Dipilih</span>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0 0 12px 0;">
                                Klik kursi pada denah di bawah untuk memilih tempat duduk rombongan utama Anda:
                            </p>

                            <!-- Denah Visual Bus Pariwisata 2-2 -->
                            <div style="max-width: 320px; margin: 0 auto; background: #ffffff; border: 2px solid #cbd5e1; border-radius: 20px; padding: 16px;">
                                <!-- Depan Bus (Sopir & Pintu) -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 10px; margin-bottom: 12px; font-size: 0.8rem; color: #64748b;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-circle-radiation" style="color: #ea580c;"></i> <strong>PINTU</strong>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <strong>SOPIR</strong> <i class="fa-solid fa-dharmachakra" style="color: #0284c7;"></i>
                                    </div>
                                </div>

                                <!-- Grid Kursi 8 Baris 2-2 -->
                                <div id="busGrid" style="display: grid; grid-template-columns: 1fr 1fr 20px 1fr 1fr; gap: 8px; text-align: center;">
                                    <?php 
                                    $rows = [1, 2, 3, 4, 5, 6, 7, 8];
                                    foreach ($rows as $r): 
                                    ?>
                                        <button type="button" class="bus-seat" data-seat="<?= $r ?>A" onclick="toggleSeat('<?= $r ?>A')"><?= $r ?>A</button>
                                        <button type="button" class="bus-seat" data-seat="<?= $r ?>B" onclick="toggleSeat('<?= $r ?>B')"><?= $r ?>B</button>
                                        <div style="font-size: 0.6rem; color: #cbd5e1; display: flex; align-items: center; justify-content: center;">&bull;</div>
                                        <button type="button" class="bus-seat" data-seat="<?= $r ?>C" onclick="toggleSeat('<?= $r ?>C')"><?= $r ?>C</button>
                                        <button type="button" class="bus-seat" data-seat="<?= $r ?>D" onclick="toggleSeat('<?= $r ?>D')"><?= $r ?>D</button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div style="margin-top: 10px; font-size: 0.8rem; color: #64748b; text-align: center;">
                                Kursi Terpilih: <strong id="selectedSeatsText" style="color: #ea580c;">(Belum ada, akan diatur otomatis)</strong>
                            </div>
                        </div>

                        <!-- 4. No WhatsApp Sesuai Gambar 28 -->
                        <div class="form-group">
                            <label class="form-label" for="no_hp"><i class="fa-brands fa-whatsapp" style="color: var(--accent);"></i> No WhatsApp Penanggung Jawab</label>
                            <input type="tel" id="no_hp" name="no_hp" class="form-control" placeholder="Contoh: 085835688224" required>
                        </div>

                        <!-- Tombol Pesan Sekarang Sesuai Gambar 28 & Tabel 41 -->
                        <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 14px;">
                            <i class="fa-solid fa-arrow-right"></i> Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NEGOSIASI TAWAR HARGA ROMBONGAN (IN-SYSTEM & WHATSAPP) -->
<div id="negoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 540px; width: 100%; overflow: hidden; box-shadow: var(--shadow-lg);">
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-handshake" style="color: var(--accent); font-size: 1.3rem;"></i>
                <h3 style="color: #fff; margin: 0; font-size: 1.2rem;">Pengajuan Tawar Harga Rombongan</h3>
            </div>
            <button onclick="closeNegoModal()" style="background: none; border: none; color: #fff; font-size: 1.3rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 24px; max-height: 80vh; overflow-y: auto;">
            <div style="background: #fffbeb; border: 1px solid var(--accent); border-radius: var(--radius-md); padding: 14px; margin-bottom: 20px; font-size: 0.88rem; color: #92400e;">
                <i class="fa-solid fa-info-circle"></i> Fasilitas <strong>Tawar Harga Khusus</strong> diperuntukkan bagi rombongan sekolah, instansi, atau komunitas. Anda dapat mengajukan tawaran langsung ke sistem web agar ditinjau oleh Admin/Bendahara.
            </div>

            <div class="form-group">
                <label class="form-label">Paket Wisata</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($paket['judul']) ?>" readonly style="background: #f1f5f9; font-weight: 700;">
            </div>

            <div class="form-group">
                <label class="form-label">Tarif Standar Resmi</label>
                <input type="text" class="form-control" value="<?= format_rupiah($paket['harga']) ?> / Pax" readonly style="background: #f1f5f9;">
            </div>

            <div class="form-group">
                <label class="form-label">Perkiraan Jumlah Rombongan (Pax)</label>
                <input type="number" id="negoPax" class="form-control" placeholder="Contoh: 45" min="1" value="45" oninput="hitungSimulasiNego()">
            </div>

            <div class="form-group">
                <label class="form-label">Ajukan Penawaran Harga Baru (Rp / Pax)</label>
                <input type="number" id="negoHarga" class="form-control" placeholder="Contoh: <?= max(1000, $paket['harga'] - 1000) ?>" min="500" oninput="hitungSimulasiNego()">
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Permintaan Khusus / Nego</label>
                <textarea id="catatanNego" class="form-control" rows="2" placeholder="Contoh: Penawaran khusus untuk study tour 45 siswa SMAN 1"></textarea>
            </div>

            <div id="simulasiNegoBox" style="background: #f8fafc; border-radius: var(--radius-md); padding: 14px; border: 1px dashed var(--border-color); margin-bottom: 20px; font-size: 0.88rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Estimasi Total Normal:</span>
                    <strong id="totalNormalText">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; color: var(--accent-hover);">
                    <span>Estimasi Total Tawaran Anda:</span>
                    <strong id="totalTawaranText">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; color: #059669; font-weight: 700;">
                    <span>Potensi Penghematan:</span>
                    <span id="hematText">-</span>
                </div>
            </div>

            <!-- DUA PILIHAN: SUBMIT KE SISTEM WEB ATAU KE WHATSAPP -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button type="button" onclick="submitNegoKeSistem()" class="btn btn-accent btn-lg" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-paper-plane"></i> Ajukan Nego Langsung ke Sistem Web
                </button>
                <button type="button" onclick="kirimNegoKeWhatsApp()" class="btn btn-outline" style="width: 100%; justify-content: center; color: #059669; border-color: #059669;">
                    <i class="fa-brands fa-whatsapp"></i> Chat Nego via WhatsApp Resmi (085835688224)
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .bus-seat {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 0;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }
    .bus-seat:hover {
        background: #fee2e2;
        border-color: #ff5722;
    }
    .bus-seat.selected {
        background: #ff5722 !important;
        color: #ffffff !important;
        border-color: #ea580c !important;
    }
</style>

<script>
    const hargaAsli = <?= (int)$paket['harga'] ?>;
    const judulPaket = "<?= addslashes($paket['judul']) ?>";
    let selectedSeats = [];

    function toggleSeat(seatNo) {
        const idx = selectedSeats.indexOf(seatNo);
        const btn = document.querySelector(`.bus-seat[data-seat='${seatNo}']`);

        if (idx > -1) {
            selectedSeats.splice(idx, 1);
            if (btn) btn.classList.remove('selected');
        } else {
            selectedSeats.push(seatNo);
            if (btn) btn.classList.add('selected');
        }

        document.getElementById('hiddenKursiDipilih').value = selectedSeats.join(', ');
        document.getElementById('selectedSeatsText').innerText = selectedSeats.length > 0 ? selectedSeats.join(', ') : '(Belum ada, akan diatur otomatis)';
        document.getElementById('seatCounterBadge').innerText = selectedSeats.length + ' Kursi Dipilih';
    }

    function openNegoModal() {
        // Salin jumlah pax jika sudah diisi
        const pPax = document.getElementById('jumlah_pax').value;
        if (pPax && parseInt(pPax) > 0) {
            document.getElementById('negoPax').value = pPax;
        }
        document.getElementById('negoModal').style.display = 'flex';
        hitungSimulasiNego();
    }

    function closeNegoModal() {
        document.getElementById('negoModal').style.display = 'none';
    }

    function hitungSimulasiNego() {
        const pax = parseInt(document.getElementById('negoPax').value) || 0;
        const hargaTawar = parseInt(document.getElementById('negoHarga').value) || 0;

        const totalNormal = pax * hargaAsli;
        const totalTawar = pax * hargaTawar;
        const hemat = totalNormal - totalTawar;

        document.getElementById('totalNormalText').innerText = 'Rp ' + totalNormal.toLocaleString('id-ID');
        document.getElementById('totalTawaranText').innerText = hargaTawar > 0 ? ('Rp ' + totalTawar.toLocaleString('id-ID')) : '-';
        document.getElementById('hematText').innerText = (hemat > 0 && hargaTawar > 0) ? ('Hemat Rp ' + hemat.toLocaleString('id-ID')) : '-';
    }

    function submitNegoKeSistem() {
        const tgl = document.getElementById('tanggal_berangkat').value;
        const rombongan = document.getElementById('nama_rombongan').value;
        const nohp = document.getElementById('no_hp').value;
        const pax = document.getElementById('negoPax').value;
        const hargaTawar = parseInt(document.getElementById('negoHarga').value) || 0;
        const catatan = document.getElementById('catatanNego').value;

        if (!tgl || !rombongan || !nohp) {
            alert('Silakan lengkapi Tanggal Berangkat, Nama Rombongan, dan No WhatsApp pada form utama terlebih dahulu!');
            closeNegoModal();
            return;
        }
        if (!hargaTawar || hargaTawar <= 0) {
            alert('Silakan masukkan harga penawaran yang diajukan!');
            return;
        }

        // Set ke form utama
        document.getElementById('jumlah_pax').value = pax;
        document.getElementById('formActionType').value = 'nego';

        // Tambah input tersembunyi untuk harga nego dan catatan
        let inputHrg = document.getElementById('hiddenNegoHarga');
        if (!inputHrg) {
            inputHrg = document.createElement('input');
            inputHrg.type = 'hidden';
            inputHrg.name = 'nego_harga';
            inputHrg.id = 'hiddenNegoHarga';
            document.getElementById('mainOrderForm').appendChild(inputHrg);
        }
        inputHrg.value = hargaTawar;

        let inputCat = document.getElementById('hiddenCatatanNego');
        if (!inputCat) {
            inputCat = document.createElement('input');
            inputCat.type = 'hidden';
            inputCat.name = 'catatan_nego';
            inputCat.id = 'hiddenCatatanNego';
            document.getElementById('mainOrderForm').appendChild(inputCat);
        }
        inputCat.value = catatan;

        // Submit form
        document.getElementById('mainOrderForm').submit();
    }

    function kirimNegoKeWhatsApp() {
        const pax = document.getElementById('negoPax').value;
        const hargaTawar = parseInt(document.getElementById('negoHarga').value) || 0;
        if (!hargaTawar || hargaTawar <= 0) {
            alert('Silakan masukkan harga penawaran yang diajukan!');
            return;
        }

        const pesan = "Halo Admin PT. Puma Star Jaya Tour,%0A%0ASaya ingin mengajukan *Tawar Harga Rombongan*:%0A" +
                      "• Paket: *" + encodeURIComponent(judulPaket) + "*%0A" +
                      "• Jumlah Peserta: *" + pax + " Pax*%0A" +
                      "• Harga Normal: Rp " + hargaAsli.toLocaleString('id-ID') + "/pax%0A" +
                      "• Harga Yang Diajukan: *Rp " + hargaTawar.toLocaleString('id-ID') + "/pax*%0A%0A" +
                      "Mohon informasi apakah penawaran harga kami bisa disetujui. Terima kasih!";

        window.open('https://wa.me/6285835688224?text=' + pesan, '_blank');
        closeNegoModal();
    }

    // AJAX Cek Kupon Potongan Harga
    document.getElementById('btnCekKupon').addEventListener('click', function() {
        const kode = document.getElementById('inputKupon').value.trim();
        const resDiv = document.getElementById('couponResult');
        const hiddenInput = document.getElementById('hiddenKodeKupon');

        if (!kode) {
            resDiv.style.display = 'block';
            resDiv.style.color = '#ef4444';
            resDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Masukkan kode kupon terlebih dahulu!';
            return;
        }

        fetch('<?= base_url("api/check_coupon.php?kode=") ?>' + encodeURIComponent(kode))
            .then(res => res.json())
            .then(data => {
                resDiv.style.display = 'block';
                if (data.valid) {
                    resDiv.style.color = '#059669';
                    resDiv.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    hiddenInput.value = data.kode;
                } else {
                    resDiv.style.color = '#ef4444';
                    resDiv.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + data.message;
                    hiddenInput.value = '';
                }
            })
            .catch(() => {
                resDiv.style.display = 'block';
                resDiv.style.color = '#ef4444';
                resDiv.innerHTML = 'Gagal menghubungi server untuk cek kupon.';
            });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
