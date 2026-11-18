<?php
// config/database.php - Koneksi Database PUMA_STAR_JAYA_TOUR (Support Local XAMPP & Vercel Cloud)

if (session_status() === PHP_SESSION_NONE) {
    // Pada lingkungan serverless Vercel, direktori session disimpan di /tmp
    if (getenv('VERCEL') || isset($_ENV['VERCEL'])) {
        @ini_set('session.save_path', '/tmp');
    }
    session_start();
}

// Konfigurasi Database (Otomatis baca Environment Variable Vercel atau Fallback ke XAMPP Lokal)
$db_host = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
$db_port = getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306');
$db_name = getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'PUMA_STAR_JAYA_TOUR');
$db_user = getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : 'Rindiani#BD2026');

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Opsi SSL untuk database cloud (seperti TiDB Cloud / Aiven)
if (getenv('DB_SSL') === 'true' || $db_port == '4000' || strpos($db_host, 'tidbcloud') !== false) {
    $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = true;
    $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, $pdoOptions);
} catch (PDOException $e) {
    // Coba fallback tanpa password jika konfigurasi lokal XAMPP root default
    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, '', $pdoOptions);
    } catch (PDOException $e2) {
        die("Koneksi Database Gagal: " . $e2->getMessage());
    }
}

// Helper format Rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper format Tanggal Indo
function format_tanggal_indo($tanggal) {
    if (!$tanggal || $tanggal === '0000-00-00') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Helper sanitize input
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Base URL helper (Otomatis mendeteksi apakah berjalan di subfolder localhost atau root domain Vercel)
function base_url($path = '') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Deteksi otomatis apakah running di localhost dengan subfolder /PUMA_STAR_JAYA_TOUR
    $subDir = '';
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($reqUri, '/PUMA_STAR_JAYA_TOUR') !== false) {
        $subDir = '/PUMA_STAR_JAYA_TOUR';
    }
    
    return rtrim($protocol . $host . $subDir, '/') . '/' . ltrim($path, '/');
}
