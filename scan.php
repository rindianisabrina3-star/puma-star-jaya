<?php
// scan.php - Pemindai Kamera QR E-Tiket & Boarding Pass Kru Bus PT. Puma Star Jaya Tour
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR E-Tiket Boarding - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <!-- Library Pemindai Kamera HTML5 QR-Code -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            background-color: #0b1329;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            padding: 30px 16px;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .scanner-container {
            max-width: 600px;
            width: 100%;
            background: #ffffff;
            color: #1e293b;
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .reader-wrapper {
            width: 100%;
            max-width: 440px;
            margin: 0 auto 20px;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid #ff5722;
            background: #000;
            position: relative;
        }

        #reader {
            width: 100%;
        }

        .scan-overlay-guide {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .manual-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 18px;
            margin-top: 24px;
        }
    </style>
</head>
<body>

    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.08); padding: 8px 18px; border-radius: 999px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.15);">
            <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; animation: pulse 1.5s infinite;"></div>
            <span style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">SCANNER BOARDING PASS KRU BUS</span>
        </div>
        <h1 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 2rem; color: #ffffff;">
            Pindai QR E-Tiket Wisata
        </h1>
        <p style="color: #94a3b8; font-size: 0.95rem; margin: 6px 0 0 0;">
            Arahkan kamera ke QR Code E-Tiket penumpang untuk validasi boarding
        </p>
    </div>

    <div class="scanner-container">
        <!-- Frame Kamera Live -->
        <div class="reader-wrapper">
            <div id="reader"></div>
        </div>

        <div class="scan-overlay-guide">
            <i class="fa-solid fa-camera" style="color: #ff5722;"></i> 
            Pastikan QR Code berada di dalam kotak pemindai kamera
        </div>

        <!-- Feedback Box -->
        <div id="scanResult" style="display: none; margin-top: 16px; padding: 14px; border-radius: 10px; font-weight: 700;"></div>

        <!-- Alternatif: Cek Manual ID Tiket -->
        <div class="manual-box">
            <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; color: #0f172a;">
                <i class="fa-solid fa-keyboard" style="color: #0284c7;"></i> Atau Masukkan ID Tiket / Kode Transaksi Manual:
            </h4>
            <form action="<?= base_url('verifikasi.php') ?>" method="GET" style="display: flex; gap: 10px;">
                <input type="number" name="id" class="form-control" placeholder="Contoh: 2" required style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; flex: 1;">
                <button type="submit" class="btn btn-accent" style="white-space: nowrap;">
                    <i class="fa-solid fa-magnifying-glass"></i> Cek Tiket
                </button>
            </form>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
            <a href="<?= base_url() ?>" class="btn btn-outline" style="font-size: 0.85rem;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
            <a href="<?= base_url('etiket.php?id=2') ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.82rem; color: #0284c7;">
                <i class="fa-solid fa-ticket"></i> Lihat Contoh E-Tiket #2
            </a>
        </div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`, decodedResult);
            
            const resultBox = document.getElementById('scanResult');
            resultBox.style.display = 'block';
            resultBox.style.background = '#dcfce7';
            resultBox.style.color = '#15803d';
            resultBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> QR Code Terdeteksi! Membuka data tiket...';

            // Jika decodedText adalah URL yang valid
            if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                window.location.href = decodedText;
            } else {
                // Jika berupa teks ID atau string
                window.location.href = 'verifikasi.php?id=' + encodeURIComponent(decodedText);
            }
        }

        function onScanFailure(error) {
            // Biarkan scan berlanjut tanpa alert error berulang
        }

        // Inisialisasi HTML5 QR Scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: { width: 250, height: 250 } },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>

