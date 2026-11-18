<?php
// paket.php - Halaman Paket Wisata Sesuai Gambar 27, Tabel 26 & Tabel 40
$pageTitle = "Daftar Paket Wisata";
require_once __DIR__ . '/includes/header.php';

$search = clean_input($_GET['q'] ?? '');

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM paket WHERE judul LIKE ? OR destinasi LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM paket ORDER BY id ASC");
}
$pakets = $stmt->fetchAll();
?>

<div style="background: radial-gradient(circle at top, #1e293b 0%, #0f172a 100%); color: #fff; padding: 60px 0 50px;">
    <div class="container" style="text-align: center;">
        <span class="slide-badge"><i class="fa-solid fa-map-location-dot"></i> Destinasi Wisata Terbaik</span>
        <h1 style="color: #fff; font-size: 2.75rem; margin-bottom: 14px;">Paket Wisata</h1>
        <p style="color: #cbd5e1; max-width: 600px; margin: 0 auto 30px; font-size: 1.05rem;">
            Temukan pilihan paket wisata terbaik dengan fasilitas armada bus pariwisata eksekutif dan harga istimewa untuk rombongan Anda.
        </p>

        <!-- Search Form -->
        <form method="GET" action="" style="max-width: 520px; margin: 0 auto; display: flex; gap: 8px;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari destinasi atau nama paket wisata..." style="border-radius: var(--radius-full); background: #ffffff;">
            <button type="submit" class="btn btn-accent" style="border-radius: var(--radius-full); padding: 0 24px;">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= base_url('paket.php') ?>" class="btn btn-outline" style="border-radius: var(--radius-full); color: #fff; border-color: rgba(255,255,255,0.4);">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="section" style="padding-top: 50px;">
    <div class="container">
        <?php if (!empty($search)): ?>
            <p style="margin-bottom: 24px; color: var(--text-muted);">Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong> (<?= count($pakets) ?> paket ditemukan)</p>
        <?php endif; ?>

        <?php if (empty($pakets)): ?>
            <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #94a3b8; margin-bottom: 16px;"></i>
                <h3 style="color: var(--text-main);">Paket Wisata Tidak Ditemukan</h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Silakan coba kata kunci lain atau lihat seluruh katalog paket wisata kami.</p>
                <a href="<?= base_url('paket.php') ?>" class="btn btn-primary">Lihat Semua Paket</a>
            </div>
        <?php else: ?>
            <!-- Grid Card Paket Wisata Sesuai Gambar 27 -->
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
                                    <i class="fa-solid fa-bus"></i>
                                    <span><?= htmlspecialchars($p['bus']) ?></span>
                                </div>
                                <div class="paket-meta-item">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span><?= htmlspecialchars($p['fasilitas']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="paket-footer">
                            <!-- Tombol Pesan Sekarang Sesuai Gambar 27 & Tabel 26 -->
                            <a href="<?= base_url('pesan.php?id=' . $p['id']) ?>" class="btn btn-accent" style="width: 100%;">
                                <i class="fa-solid fa-cart-plus"></i> Pesan Sekarang
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
