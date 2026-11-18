<?php
// index.php - Halaman Utama Sesuai Gambar 24 & Tabel 23
$pageTitle = "Halaman Utama";
require_once __DIR__ . '/includes/header.php';

// Ambil paket wisata dari database
$stmtPaket = $pdo->query("SELECT * FROM paket ORDER BY id ASC LIMIT 3");
$pakets = $stmtPaket->fetchAll();

// Ambil ulasan terbaru dari database (Saran Skripsi: Kepuasan Pelanggan)
$recentReviews = $pdo->query("SELECT u.*, usr.nama as nama_user, pkt.judul as nama_paket, pkt.destinasi FROM ulasan u JOIN user usr ON u.user_id = usr.id JOIN paket pkt ON u.paket_id = pkt.id ORDER BY u.id DESC LIMIT 3")->fetchAll();
?>

<!-- 1. HERO SLIDER / CAROUSEL (Sesuai Gambar 24: Background, Text, <, >) -->
<div class="hero-slider">
    <div class="slider-container">
        <!-- Slide 1: Bali -->
        <div class="slide-item active" style="background-image: url('https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-badge"><i class="fa-solid fa-star"></i> Bus Pariwisata & Tour Premium</span>
                        <h1 class="slide-title">Jelajahi Pesona Nusantara Bersama PT. Puma Star Jaya</h1>
                        <p class="slide-desc">Layanan perjalanan wisata terbaik dan armada bus pariwisata eksekutif dengan fasilitas terlengkap untuk rombongan sekolah, instansi, dan keluarga.</p>
                        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                            <a href="<?= base_url('paket.php') ?>" class="btn btn-accent btn-lg"><i class="fa-solid fa-compass"></i> Lihat Paket Wisata</a>
                            <a href="#tentang" class="btn btn-outline btn-lg" style="color: #fff; border-color: rgba(255,255,255,0.4);"><i class="fa-solid fa-circle-info"></i> Tentang Kami</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: Jogja -->
        <div class="slide-item" style="background-image: url('https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=1920&q=80');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-badge"><i class="fa-solid fa-shield-halved"></i> Aman, Nyaman & Berpengalaman</span>
                        <h1 class="slide-title">Sensasi Liburan Budaya & Petualangan Merapi Jogja</h1>
                        <p class="slide-desc">Paket hemat istimewa dengan fasilitas penginapan berkelas, armada bus nyaman, serta pendampingan tour leader berpengalaman sepanjang perjalanan.</p>
                        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                            <a href="<?= base_url('paket.php') ?>" class="btn btn-accent btn-lg"><i class="fa-solid fa-cart-shopping"></i> Pesan Sekarang</a>
                            <a href="https://wa.me/6285835688224" target="_blank" class="btn btn-outline btn-lg" style="color: #fff; border-color: rgba(255,255,255,0.4);"><i class="fa-brands fa-whatsapp"></i> Hubungi Kami</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: Bromo -->
        <div class="slide-item" style="background-image: url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1920&q=80');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-badge"><i class="fa-solid fa-mountain-sun"></i> Sunrise Spektakuler</span>
                        <h1 class="slide-title">Keajaiban Bromo Sunrise & Kota Wisata Batu Malang</h1>
                        <p class="slide-desc">Nikmati momen magis lautan pasir Bromo hingga keseruan wisata petik apel dan theme park modern bersama rekan serta rombongan Anda.</p>
                        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                            <a href="<?= base_url('paket.php') ?>" class="btn btn-accent btn-lg"><i class="fa-solid fa-compass"></i> Eksplorasi Paket</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi Slider Panah Kiri (<) dan Kanan (>) -->
    <div class="slider-nav slider-prev" title="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></div>
    <div class="slider-nav slider-next" title="Berikutnya"><i class="fa-solid fa-chevron-right"></i></div>

    <!-- Dots -->
    <div class="slider-dots">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </div>
</div>

<!-- 2. SECTION TENTANG KAMI (Sesuai Tabel 23: Melihat informasi tentang PT. Puma Star Jaya) -->
<section id="tentang" class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
            <div>
                <span class="section-subtitle">Profil Perusahaan</span>
                <h2 class="section-title">Solusi Terpercaya Perjalanan Wisata & Transportasi Rombongan</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.8;">
                    <strong>PT. Puma Star Jaya Tour & Travel</strong> adalah perusahaan biro perjalanan wisata resmi dan penyedia armada bus pariwisata eksekutif terdepan. Kami berkomitmen menyajikan pengalaman liburan berkualitas tinggi dengan standar kenyamanan prima, keamanan maksimal, dan tarif yang transparan.
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 24px;">
                    <div style="background: #ffffff; padding: 16px; border-radius: var(--radius-md); border-left: 4px solid var(--accent); box-shadow: var(--shadow-sm);">
                        <h4 style="font-size: 1.05rem; margin-bottom: 6px;"><i class="fa-solid fa-bus" style="color: var(--accent);"></i> Armada Modern</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Unit bus SHD & HDD berfasilitas full AC, audio, toilet, dan reclining seats.</p>
                    </div>
                    <div style="background: #ffffff; padding: 16px; border-radius: var(--radius-md); border-left: 4px solid var(--sky); box-shadow: var(--shadow-sm);">
                        <h4 style="font-size: 1.05rem; margin-bottom: 6px;"><i class="fa-solid fa-certificate" style="color: var(--sky);"></i> Berizin Resmi</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Legalitas lengkap dan asuransi keselamatan resmi Jasa Raharja.</p>
                    </div>
                </div>
                <a href="#kontak" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Konsultasi Wisata Gratis</a>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=800&q=80" alt="Bus Pariwisata Puma Star Jaya" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); border: 4px solid #ffffff;">
            </div>
        </div>
    </div>
</section>

<!-- 3. SECTION PAKET WISATA (Sesuai Gambar 27 & Tabel 23: Melihat paket wisata) -->
<section id="paket" class="section" style="background: #ffffff;">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-subtitle">Katalog Pilihan</span>
            <h2 class="section-title">Paket Wisata Favorit</h2>
            <p style="color: var(--text-muted);">Pilih destinasi impian Anda dengan fasilitas terlengkap dan harga bersahabat untuk rombongan.</p>
        </div>

        <div class="paket-grid">
            <?php foreach ($pakets as $p): ?>
                <div class="paket-card">
                    <div class="paket-img-wrap">
                        <img src="<?= htmlspecialchars($p['foto'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($p['judul']) ?>" class="paket-img">
                        <div class="paket-badge-price">
                            <span><?= format_rupiah($p['harga']) ?></span> / pax
                        </div>
                    </div>
                    <div class="paket-body">
                        <div class="paket-destination">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= htmlspecialchars($p['destinasi']) ?></span>
                        </div>
                        <h3 class="paket-title"><?= htmlspecialchars($p['judul']) ?></h3>
                        <p class="paket-desc"><?= htmlspecialchars($p['deskripsi']) ?></p>
                        
                        <div class="paket-meta">
                            <div class="paket-meta-item">
                                <i class="fa-solid fa-van-shuttle"></i>
                                <span><?= htmlspecialchars($p['bus']) ?></span>
                            </div>
                            <div class="paket-meta-item">
                                <i class="fa-solid fa-list-check"></i>
                                <span><?= htmlspecialchars($p['fasilitas']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="paket-footer">
                        <a href="<?= base_url('pesan.php?id=' . $p['id']) ?>" class="btn btn-accent" style="width: 100%;">
                            <i class="fa-solid fa-ticket"></i> Pesan Sekarang
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?= base_url('paket.php') ?>" class="btn btn-outline btn-lg">
                <i class="fa-solid fa-grid-2"></i> Lihat Semua Paket Wisata
            </a>
        </div>
    </div>
</section>

<!-- 4. SECTION GALERI (Sesuai Tabel 23: Melihat foto foto tentang perjalanan PT Puma Star Jaya) -->
<section id="galeri" class="section">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-subtitle">Dokumentasi</span>
            <h2 class="section-title">Galeri Perjalanan Bersama Kami</h2>
            <p style="color: var(--text-muted);">Momen seru dan keceriaan rombongan konsumen bersama armada PT. Puma Star Jaya Tour.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            <div class="gallery-hover-card" style="height: 220px; overflow: hidden; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=600&q=80" alt="Galeri 1" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="gallery-hover-card" style="height: 220px; overflow: hidden; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80" alt="Galeri 2" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="gallery-hover-card" style="height: 220px; overflow: hidden; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=600&q=80" alt="Galeri 3" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="gallery-hover-card" style="height: 220px; overflow: hidden; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80" alt="Galeri 4" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- SECTION TESTIMONI & ULASAN WISATAWAN -->
<?php if (!empty($recentReviews)): ?>
<section id="testimoni" class="section" style="background: #f8fafc;">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-subtitle">Kepuasan Pelanggan</span>
            <h2 class="section-title">Testimoni & Pengalaman Wisatawan</h2>
            <p style="color: var(--text-muted);">Ulasan nyata dari rombongan yang telah mempercayakan perjalanannya kepada PT. Puma Star Jaya Tour.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            <?php foreach ($recentReviews as $rev): ?>
                <div style="background: #ffffff; border-radius: var(--radius-lg); padding: 26px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; gap: 4px; margin-bottom: 12px; font-size: 1.1rem;">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fa-solid fa-star" style="color: <?= ($s <= $rev['bintang']) ? '#f59e0b' : '#cbd5e1' ?>;"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="color: #334155; font-style: italic; font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                            "<?= htmlspecialchars($rev['komentar']) ?>"
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #ff5722; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                            <?= strtoupper(substr($rev['nama_user'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($rev['nama_user']) ?></div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);"><i class="fa-solid fa-map-pin" style="color: var(--accent);"></i> <?= htmlspecialchars($rev['nama_paket']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. SECTION CONTACT (Sesuai Tabel 23: Melihat Contact yang dapat dihubungi) -->
<section id="kontak" class="section" style="background: #ffffff;">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-subtitle">Layanan Pelanggan</span>
            <h2 class="section-title">Hubungi Kami</h2>
            <p style="color: var(--text-muted);">Siap melayani pertanyaan, konsultasi itinerary wisata, dan sewa bus rombongan 24 jam.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: var(--bg-body); border-radius: var(--radius-lg); padding: 40px; border: 1px solid var(--border-color);">
            <div>
                <h3 style="margin-bottom: 16px; font-size: 1.4rem;">Kantor Pusat PT. Puma Star Jaya</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">Silakan hubungi kontak resmi kami atau kirimkan pesan langsung melalui nomor WhatsApp di bawah ini:</p>
                
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TELEPON / WHATSAPP</div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--primary);">085835688224</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">EMAIL RESMI</div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--primary);">pumatour@gmail.com</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">JAM OPERASIONAL</div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--primary);">Setiap Hari: 08.00 - 21.00 WIB</div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <form onsubmit="kirimPesanWhatsApp(event)">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" id="wa_nama" class="form-control" placeholder="Masukkan nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp Anda</label>
                        <input type="tel" id="wa_pengirim" class="form-control" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pesan / Rencana Wisata</label>
                        <textarea id="wa_pesan" class="form-control" rows="3" placeholder="Tuliskan tujuan dan jumlah peserta rombongan..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #25D366; border-color: #25D366; color: #ffffff; font-weight: 700;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.15rem;"></i> Kirim Pesan ke WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
function kirimPesanWhatsApp(e) {
    e.preventDefault();
    const nama = document.getElementById('wa_nama').value.trim();
    const noHp = document.getElementById('wa_pengirim').value.trim();
    const pesan = document.getElementById('wa_pesan').value.trim();

    const text = "Halo Admin PT. Puma Star Jaya Tour,%0A%0A" +
                 "Saya ingin berkonsultasi mengenai paket wisata:%0A" +
                 "• *Nama:* " + encodeURIComponent(nama) + "%0A" +
                 "• *No. WhatsApp Pengirim:* " + encodeURIComponent(noHp) + "%0A" +
                 "• *Pesan/Tujuan Wisata:*%0A" + encodeURIComponent(pesan) + "%0A%0A" +
                 "Mohon informasi selengkapnya. Terima kasih!";

    window.open("https://wa.me/6285835688224?text=" + text, "_blank");
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
