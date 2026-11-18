<?php
// register.php - Sesuai Gambar 25, Tabel 24 & Tabel 38
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua field (Nama, Email, Password) wajib diisi lengkap!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah email sudah terdaftar
        $stmtCheck = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $error = 'Email ini sudah terdaftar! Silakan gunakan email lain atau langsung login.';
        } else {
            // Simpan akun konsumen ke database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $pdo->prepare("INSERT INTO user (nama, email, level, password, email_verified_at, created_at, updated_at) VALUES (?, ?, 'konsumen', ?, NOW(), NOW(), NOW())");
            if ($stmtInsert->execute([$nama, $email, $hashedPassword])) {
                $newUserId = $pdo->lastInsertId();
                // Set sesi login langsung sesuai skenario pengujian Tabel 38 (Menyimpan Akun yang terdaftar -> Masuk ke website PumaTour)
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $_SESSION['level'] = 'konsumen';
                
                header("Location: " . base_url('dashboard_konsumen.php'));
                exit;
            } else {
                $error = 'Terjadi kesalahan sistem saat mendaftar. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendaftar Akun Puma Star Jaya - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="auth-page">

    <div class="auth-card">
        <!-- Logo & Header Sesuai Gambar 25 -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-bus"></i>
            </div>
            <h2 class="auth-title">Mendaftar Akun Puma Star Jaya</h2>
            <p class="auth-subtitle">Lengkapi formulir pendaftaran untuk memesan paket wisata</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Registrasi Sesuai Gambar 25: NAMA, EMAIL, PASSWORD, Tombol Daftar -->
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="nama">NAMA</label>
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">EMAIL</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">PASSWORD</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 10px;">
                <i class="fa-solid fa-user-plus"></i> Daftar
            </button>
        </form>

        <!-- Footer Link Sesuai Gambar 25 -->
        <div class="auth-footer">
            <p>Sudah Memiliki Akun? <a href="<?= base_url('login.php') ?>">Klik disini untuk login</a></p>
            <p style="margin-top: 12px;"><a href="<?= base_url('index.php') ?>" style="color: var(--text-muted); font-size: 0.85rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Utama</a></p>
        </div>
    </div>

</body>
</html>
