<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

$currentUser = isset($_SESSION['user_id']) ? [
    'id' => $_SESSION['user_id'],
    'nama' => $_SESSION['nama'],
    'email' => $_SESSION['email'],
    'level' => $_SESSION['level']
] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>PT. Puma Star Jaya Tour & Travel</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- 1. TOP BAR (Sesuai Gambar 24) -->
    <div class="top-bar no-print">
        <div class="container">
            <div class="top-bar-left">
                <div class="top-bar-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>pumatour@gmail.com</span>
                </div>
                <div class="top-bar-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>085835688224</span>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="social-links">
                    <a href="#" class="social-circle" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-circle" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-circle" title="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="social-circle" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://wa.me/6285835688224" target="_blank" class="social-circle" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. NAVBAR (Sesuai Gambar 24 & Tabel 23) -->
    <nav class="main-navbar no-print">
        <div class="navbar-container">
            <a href="<?= base_url('index.php') ?>" class="navbar-brand">
                <div class="brand-badge">PUMA</div>
                <div class="brand-text">STAR JAYA <span>TOUR</span></div>
            </a>

            <ul class="nav-links">
                <li><a href="<?= base_url('index.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= base_url('index.php#tentang') ?>" class="nav-link">Tentang Kami</a></li>
                <li><a href="<?= base_url('paket.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'paket.php' || basename($_SERVER['PHP_SELF']) == 'pesan.php') ? 'active' : '' ?>">Paket Wisata</a></li>
                <li><a href="<?= base_url('index.php#galeri') ?>" class="nav-link">Galeri</a></li>
                <li><a href="<?= base_url('index.php#kontak') ?>" class="nav-link">Contact</a></li>
            </ul>

            <div class="nav-actions">
                <?php if ($currentUser): ?>
                    <!-- State Login: Menampilkan Hallo, Pengguna sesuai Gambar 28 -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php 
                        $dashUrl = base_url('dashboard_konsumen.php');
                        if ($currentUser['level'] === 'admin') $dashUrl = base_url('admin/dashboard.php');
                        else if ($currentUser['level'] === 'bendahara') $dashUrl = base_url('bendahara/dashboard.php');
                        else if ($currentUser['level'] === 'direktur') $dashUrl = base_url('direktur/dashboard.php');
                        ?>
                        <a href="<?= $dashUrl ?>" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-user-circle"></i>
                            Hallo, <?= htmlspecialchars($currentUser['nama']) ?>
                        </a>
                        <a href="<?= base_url('logout.php') ?>" class="btn btn-outline btn-sm" title="Keluar">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- State Belum Login: Sesuai Gambar 24 & Tabel 23 -->
                    <a href="<?= base_url('register.php') ?>" class="btn btn-outline">Registrasi</a>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-accent">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
