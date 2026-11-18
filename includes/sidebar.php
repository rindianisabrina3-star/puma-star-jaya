<?php
// includes/sidebar.php - Sidebar Multi-Role Sesuai Gambar 30, 31, 33, 34
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userRole = $_SESSION['level'] ?? 'konsumen';
$userName = $_SESSION['nama'] ?? 'Pengguna';
$currentFile = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR -->
<aside class="sidebar no-print" id="mainSidebar">
    <!-- Header Sidebar Sesuai Wireframe: Logo -->
    <div class="sidebar-header">
        <div class="brand-badge" style="font-size: 1rem; padding: 6px 10px;">PUMA</div>
        <div>
            <div style="font-weight: 800; font-size: 1.05rem; color: #fff;">STAR JAYA</div>
            <div style="font-size: 0.72rem; color: var(--accent); font-weight: 700; letter-spacing: 0.5px;">TOUR & TRAVEL</div>
        </div>
    </div>

    <!-- Role Badge Sesuai Gambar 30, 33, 34 -->
    <div class="sidebar-role-badge">
        <i class="fa-solid fa-shield-halved"></i> Role: <?= strtoupper($userRole) ?>
    </div>

    <ul class="sidebar-menu">
        <?php if ($userRole === 'konsumen'): ?>
            <!-- MENU KONSUMEN (Sesuai Gambar 30 & 31) -->
            <li class="menu-category">MENU KONSUMEN</li>
            <li class="menu-item">
                <a href="<?= base_url('dashboard_konsumen.php') ?>" class="menu-link <?= ($currentFile == 'dashboard_konsumen.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('riwayat.php') ?>" class="menu-link <?= ($currentFile == 'riwayat.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt"></i> Pemesanan Paket Wisata
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('paket.php') ?>" class="menu-link">
                    <i class="fa-solid fa-compass"></i> Katalog Paket Wisata
                </a>
            </li>

        <?php elseif ($userRole === 'admin'): ?>
            <!-- MENU ADMIN (Sesuai Gambar 33 & Tabel 32) -->
            <li class="menu-category">MAIN MENU</li>
            <li class="menu-item">
                <a href="<?= base_url('admin/dashboard.php') ?>" class="menu-link <?= ($currentFile == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
            <li class="menu-category">MENU PENGELOLAAN</li>
            <li class="menu-item">
                <a href="<?= base_url('admin/paket.php') ?>" class="menu-link <?= ($currentFile == 'paket.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-route"></i> Paket Wisata
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('admin/laporan.php') ?>" class="menu-link <?= ($currentFile == 'laporan.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> Laporan Pemesanan
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('admin/user.php') ?>" class="menu-link <?= ($currentFile == 'user.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users-gear"></i> Manajemen Pengguna
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('admin/kupon.php') ?>" class="menu-link <?= ($currentFile == 'kupon.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-ticket-simple"></i> Cek Kupon
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('admin/nego.php') ?>" class="menu-link <?= ($currentFile == 'nego.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-handshake" style="color: var(--accent);"></i> Kelola Negosiasi
                </a>
            </li>

        <?php elseif ($userRole === 'bendahara'): ?>
            <!-- MENU BENDAHARA (Sesuai Gambar 34, Tabel 45 & Tabel 46) -->
            <li class="menu-category">MAIN MENU</li>
            <li class="menu-item">
                <a href="<?= base_url('bendahara/dashboard.php') ?>" class="menu-link <?= ($currentFile == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
            <li class="menu-category">KEUANGAN & VALIDASI</li>
            <li class="menu-item">
                <a href="<?= base_url('bendahara/validasi.php') ?>" class="menu-link <?= ($currentFile == 'validasi.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-money-check-dollar"></i> Validasi Pembayaran
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('bendahara/laporan.php') ?>" class="menu-link <?= ($currentFile == 'laporan.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Laporan Transaksi
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('admin/nego.php') ?>" class="menu-link <?= ($currentFile == 'nego.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-handshake" style="color: var(--accent);"></i> Persetujuan Nego
                </a>
            </li>

        <?php elseif ($userRole === 'direktur'): ?>
            <!-- MENU DIREKTUR (Sesuai Tabel 44 & Tabel 46) -->
            <li class="menu-category">EKSEKUTIF</li>
            <li class="menu-item">
                <a href="<?= base_url('direktur/dashboard.php') ?>" class="menu-link <?= ($currentFile == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i> Executive Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= base_url('direktur/laporan.php') ?>" class="menu-link <?= ($currentFile == 'laporan.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-signature"></i> Rekapitulasi Laporan
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-category" style="margin-top: 14px;">OPERASIONAL TIKET</li>
        <li class="menu-item">
            <a href="<?= base_url('scan.php') ?>" class="menu-link <?= ($currentFile == 'scan.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-qrcode" style="color: var(--accent);"></i> Scanner Boarding Pass
            </a>
        </li>
        <li class="menu-item">
            <a href="<?= base_url('index.php') ?>" class="menu-link">
                <i class="fa-solid fa-globe"></i> Kunjungi Website Utama
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="<?= base_url('logout.php') ?>" class="btn btn-outline btn-sm" style="width: 100%; color: #ef4444; border-color: rgba(239,68,68,0.4);">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar (Logout)
        </a>
    </div>
</aside>
