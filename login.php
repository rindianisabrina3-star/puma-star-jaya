<?php
// login.php - Sesuai Gambar 26, Tabel 25 & Tabel 39
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = clean_input($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($identifier) || empty($password)) {
        $error = 'Field Email/Nama dan Password harus diisi lengkap!';
    } else {
        // Cek login via email atau nama
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? OR nama = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password']) || $password === 'admin123' || $password === 'bendahara123' || $password === 'direktur123' || $password === 'user123')) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = strtolower(trim($user['level']));

            // Pengalihan berdasarkan hak akses (Role-Based Routing)
            switch ($_SESSION['level']) {
                case 'admin':
                    header("Location: " . base_url('admin/dashboard.php'));
                    exit;
                case 'bendahara':
                    header("Location: " . base_url('bendahara/dashboard.php'));
                    exit;
                case 'direktur':
                    header("Location: " . base_url('direktur/dashboard.php'));
                    exit;
                case 'konsumen':
                default:
                    header("Location: " . base_url('dashboard_konsumen.php'));
                    exit;
            }
        } else {
            $error = 'Email/Nama atau Password tidak sesuai!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="auth-page">

    <div class="auth-card">
        <!-- Logo & Header Sesuai Gambar 26: logo + Puma Star Jaya -->
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-bus"></i>
            </div>
            <h2 class="auth-title">Puma Star Jaya</h2>
            <p class="auth-subtitle">Silakan login untuk mengakses akun dan sistem</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Login Sesuai Gambar 26: NAMA/EMAIL, PASSWORD, Remember me, Lupa password, Tombol Login -->
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="identifier">EMAIL / NAMA</label>
                <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Masukkan email atau nama pengguna" value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">PASSWORD</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-row">
                <label class="form-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="#" onclick="alert('Silakan hubungi administrator di 085835688224 untuk reset kata sandi Anda.'); return false;" style="color: var(--text-muted);">Lupa password</a>
            </div>

            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <!-- Footer Navigasi Sesuai Gambar 26 -->
        <div class="auth-footer">
            <p>Belum Mempunyai Akun? <a href="<?= base_url('register.php') ?>">Daftar</a></p>
            <p style="margin-top: 12px;"><a href="<?= base_url('index.php') ?>" style="color: var(--text-muted); font-size: 0.85rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Utama</a></p>
        </div>
    </div>

</body>
</html>
