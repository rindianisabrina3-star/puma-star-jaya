<?php
// api/index.php - Vercel Serverless Entrypoint & Router

// Parse requested URL path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Decode URI
$path = urldecode($path);

// Root path mapping
if ($path === '/' || $path === '' || $path === '/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// Cek file langsung
$baseDir = dirname(__DIR__);
$targetFile = $baseDir . $path;

// 1. Jika file PHP langsung ada
if (file_exists($targetFile) && is_file($targetFile)) {
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $targetFile;
        exit;
    }
}

// 2. Jika diakses tanpa .php (clean URL)
if (file_exists($targetFile . '.php') && is_file($targetFile . '.php')) {
    require $targetFile . '.php';
    exit;
}

// 3. Jika mengakses direktori (misal /admin atau /admin/)
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/');
    if (file_exists($targetFile . '/dashboard.php')) {
        require $targetFile . '/dashboard.php';
        exit;
    } elseif (file_exists($targetFile . '/index.php')) {
        require $targetFile . '/index.php';
        exit;
    }
}

// 4. Jika file statis di uploads
if (strpos($path, '/uploads/') === 0 && file_exists($targetFile)) {
    $mime = mime_content_type($targetFile) ?: 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($targetFile);
    exit;
}

// 404 Handler
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h2>Halaman Tidak Ditemukan (404)</h2>";
echo "<p>Path: " . htmlspecialchars($path) . "</p>";
echo "<a href='/'>Kembali ke Halaman Utama</a>";
echo "</body></html>";
exit;
