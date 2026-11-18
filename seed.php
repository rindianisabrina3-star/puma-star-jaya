<?php
// seed.php - Seeder data awal untuk PUMA_STAR_JAYA_TOUR
require_once __DIR__ . '/config/database.php';

try {
    // 1. Seed Users (Admin, Bendahara, Direktur, Konsumen)
    $users = [
        [
            'nama' => 'Staff Administrator',
            'email' => 'admin@pumatour.com',
            'level' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
        ],
        [
            'nama' => 'Bendahara Keuangan',
            'email' => 'bendahara@pumatour.com',
            'level' => 'bendahara',
            'password' => password_hash('bendahara123', PASSWORD_DEFAULT),
        ],
        [
            'nama' => 'Direktur Utama',
            'email' => 'direktur@pumatour.com',
            'level' => 'direktur',
            'password' => password_hash('direktur123', PASSWORD_DEFAULT),
        ],
        [
            'nama' => 'Budi Santoso',
            'email' => 'konsumen@gmail.com',
            'level' => 'konsumen',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
        ],
    ];

    $stmtCheck = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmtInsertUser = $pdo->prepare("INSERT INTO user (nama, email, level, password, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())");

    foreach ($users as $u) {
        $stmtCheck->execute([$u['email']]);
        if (!$stmtCheck->fetch()) {
            $stmtInsertUser->execute([$u['nama'], $u['email'], $u['level'], $u['password']]);
        }
    }
    echo "Users seeded successfully.\n";

    // 2. Seed Paket Wisata
    $pakets = [
        [
            'judul' => 'Paket Wisata Bali Eksotis 3D2N',
            'deskripsi' => 'Nikmati keindahan Pulau Dewata Bali mengunjungi Pantai Pandawa, Pura Uluwatu, Tanah Lot, Kintamani, serta pertunjukan Tari Kecak spektakuler. Termasuk penginapan hotel bintang 3 dan makan khas Bali.',
            'destinasi' => 'Denpasar, Kuta, Ubud, Uluwatu, Tanah Lot',
            'fasilitas' => 'Hotel Bintang 3, Makan 7x, Tiket Masuk Wisata, Guide Berpengalaman, Air Mineral & Snack',
            'bus' => 'Bus Pariwisata Executive SHD 45 Seat (AC, Reclining Seat, Audio/Video, USB Port)',
            'harga' => 5000, // Harga sementara untuk uji coba (Normal: 1850000)
            'foto' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'judul' => 'Pesona Budaya Jogja & Candi Borobudur 2D1N',
            'deskripsi' => 'Eksplorasi warisan sejarah dan budaya Yogyakarta, Candi Borobudur, Tebing Breksi, Malioboro, serta sensasi seru Jeep Lava Tour Gunung Merapi yang memacu adrenalin.',
            'destinasi' => 'Yogyakarta, Candi Borobudur, Merapi Lava Tour, Malioboro',
            'fasilitas' => 'Hotel Bintang 3, Makan 4x, Sewa Jeep Merapi, Tiket Masuk Objek Wisata, P3K',
            'bus' => 'Jetbus 3+ HDD Pariwisata 50 Seat (AC Dingin, Karaoke, Toilet, Charger)',
            'harga' => 2000, // Harga sementara untuk uji coba (Normal: 850000)
            'foto' => 'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'judul' => 'Bromo Golden Sunrise & Malang Fantasy Tour 3D2N',
            'deskripsi' => 'Saksikan matahari terbit legendaris di Penanjakan Bromo, Kawah Bromo, Pasir Berbisik, dilanjutkan petik apel Kota Batu Malang, Museum Angkut, dan Jatim Park.',
            'destinasi' => 'Taman Nasional Bromo Tengger Semeru, Batu Malang',
            'fasilitas' => 'Jeep Bromo 4WD, Villa/Hotel Bintang 3 Batu, Tiket Bromo & Jatim Park, Makan 6x',
            'bus' => 'Big Bus Pariwisata Mercedes-Benz 48 Seat (Suspensi Udara, Full Entertainment)',
            'harga' => 3000, // Harga sementara untuk uji coba (Normal: 1450000)
            'foto' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=800&q=80',
        ],
        [
            'judul' => 'Bandung Heritage & Nuansa Alam Ciwidey 2D1N',
            'deskripsi' => 'Liburan sejuk menyegarkan di Kawah Putih Ciwidey, Perkebunan Teh Rancabali, Glamping Lakeside, serta wisata belanja dan kuliner legendaris Kota Kembang.',
            'destinasi' => 'Bandung, Ciwidey, Kawah Putih, Glamping Lakeside, Paris Van Java',
            'fasilitas' => 'Penginapan Resort Alam, Makan 4x, Tiket Kawah Putih & Perahu Angsa, Tour Leader',
            'bus' => 'Medium Bus Pariwisata 31 Seat Luxury Class (Captain Seat, Karaoke, Coolbox)',
            'harga' => 1000, // Harga sementara untuk uji coba (Normal: 750000)
            'foto' => 'https://images.unsplash.com/photo-1584810359583-96fc3448beaa?auto=format&fit=crop&w=800&q=80',
        ],
    ];

    $stmtCheckPaket = $pdo->prepare("SELECT id FROM paket WHERE judul = ?");
    $stmtInsertPaket = $pdo->prepare("INSERT INTO paket (judul, deskripsi, destinasi, fasilitas, bus, harga, foto, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    foreach ($pakets as $p) {
        $stmtCheckPaket->execute([$p['judul']]);
        if (!$stmtCheckPaket->fetch()) {
            $stmtInsertPaket->execute([$p['judul'], $p['deskripsi'], $p['destinasi'], $p['fasilitas'], $p['bus'], $p['harga'], $p['foto']]);
        }
    }
    echo "Paket Wisata seeded successfully.\n";

    // 3. Seed Kupon Diskon
    $kupons = [
        ['kode' => 'PUMAJAYA25', 'potongan' => 500],
        ['kode' => 'LIBURANSERU', 'potongan' => 1000],
        ['kode' => 'PROMOHEMAT', 'potongan' => 500],
        ['kode' => 'ROMBONGAN2025', 'potongan' => 1500],
    ];

    $stmtCheckKupon = $pdo->prepare("SELECT id FROM kupon WHERE kode = ?");
    $stmtInsertKupon = $pdo->prepare("INSERT INTO kupon (user_id, kode, potongan, created_at, updated_at) VALUES (NULL, ?, ?, NOW(), NOW())");

    foreach ($kupons as $k) {
        $stmtCheckKupon->execute([$k['kode']]);
        if (!$stmtCheckKupon->fetch()) {
            $stmtInsertKupon->execute([$k['kode'], $k['potongan']]);
        }
    }
    echo "Kupon seeded successfully.\n";

    // 4. Seed 1 Sample Pemesanan & Transaksi for demo
    $uKonsumen = $pdo->query("SELECT id FROM user WHERE email = 'konsumen@gmail.com'")->fetch();
    $pPaket = $pdo->query("SELECT id, harga FROM paket LIMIT 1")->fetch();

    if ($uKonsumen && $pPaket) {
        $stmtCheckPemesanan = $pdo->prepare("SELECT id FROM pemesanan WHERE user_id = ? AND paket_id = ?");
        $stmtCheckPemesanan->execute([$uKonsumen['id'], $pPaket['id']]);
        $existingPemesanan = $stmtCheckPemesanan->fetch();

        if (!$existingPemesanan) {
            $stmtInsertPesanan = $pdo->prepare("INSERT INTO pemesanan (user_id, paket_id, harga, tanggal_berangkat, nama_rombongan, jumlah_pax, no_hp, created_at, updated_at) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'SMA Negeri 1 Jaya Tour', 45, '081234567890', NOW(), NOW())");
            $totalHarga = $pPaket['harga'] * 45;
            $stmtInsertPesanan->execute([$uKonsumen['id'], $pPaket['id'], $totalHarga]);
            $pemesananId = $pdo->lastInsertId();

            $noInvoice = 'INV-' . date('Ymd') . '-001';
            $stmtInsertTransaksi = $pdo->prepare("INSERT INTO transaksi (user_id, pemesanan_id, no_invoice, status, bukti_pembayaran, created_at, updated_at) VALUES (?, ?, ?, 'Menunggu Verifikasi', 'sample_bukti.jpg', NOW(), NOW())");
            $stmtInsertTransaksi->execute([$uKonsumen['id'], $pemesananId, $noInvoice]);
            echo "Sample Pemesanan & Transaksi seeded.\n";
        }
    }

    echo "Seeding completed successfully!\n";
} catch (Exception $e) {
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
