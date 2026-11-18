<?php
// direktur/dashboard.php - Sesuai Tabel 44 & 46 (Dashboard Eksekutif Direktur + Grafik Interaktif)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'direktur') {
    header("Location: " . base_url('login.php'));
    exit;
}

$direkturName = $_SESSION['nama'];

// Ringkasan Eksekutif
$totalPemesanan = (int)$pdo->query("SELECT COUNT(*) FROM pemesanan")->fetchColumn();
$totalTransaksi = (int)$pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
$totalPengguna = (int)$pdo->query("SELECT COUNT(*) FROM user WHERE level = 'konsumen'")->fetchColumn();

// Total Pendapatan Lunas
$totalPendapatan = (int)$pdo->query("SELECT COALESCE(SUM(p.harga), 0) FROM pemesanan p JOIN transaksi t ON t.pemesanan_id = p.id WHERE t.status IN ('Lunas', 'Diterima')")->fetchColumn();

// Total Peserta Terlayani
$totalPax = (int)$pdo->query("SELECT COALESCE(SUM(p.jumlah_pax), 0) FROM pemesanan p JOIN transaksi t ON t.pemesanan_id = p.id WHERE t.status IN ('Lunas', 'Diterima')")->fetchColumn();

// Paket Wisata Terpopuler
$stmtPopular = $pdo->query("SELECT pkt.judul, pkt.destinasi, COUNT(p.id) as total_order, COALESCE(SUM(p.jumlah_pax), 0) as total_pax, COALESCE(SUM(CASE WHEN t.status IN ('Lunas', 'Diterima') THEN p.harga ELSE 0 END), 0) as omset
                            FROM paket pkt
                            LEFT JOIN pemesanan p ON p.paket_id = pkt.id
                            LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                            GROUP BY pkt.id
                            ORDER BY total_order DESC LIMIT 5");
$popularPackages = $stmtPopular->fetchAll();

// --- DATA UNTUK GRAFIK INTERAKTIF (CHART.JS) ---
// 1. Data Tren 6 Bulan Terakhir
$months = [
    '2026-06' => 'Jun 2026',
    '2026-07' => 'Jul 2026',
    '2026-08' => 'Agu 2026',
    '2026-09' => 'Sep 2026',
    '2026-10' => 'Okt 2026',
    '2026-11' => 'Nov 2026',
];

$stmtMonthly = $pdo->query("SELECT DATE_FORMAT(p.created_at, '%Y-%m') as ym,
                                   COUNT(p.id) as orders,
                                   COALESCE(SUM(CASE WHEN t.status IN ('Lunas', 'Diterima') THEN p.harga ELSE 0 END), 0) as omset
                            FROM pemesanan p
                            LEFT JOIN transaksi t ON t.pemesanan_id = p.id
                            GROUP BY ym");
$dbMonthly = [];
while ($row = $stmtMonthly->fetch()) {
    $dbMonthly[$row['ym']] = $row;
}

$chartLabels = [];
$chartRevenue = [];
$chartOrders = [];

foreach ($months as $ym => $label) {
    $chartLabels[] = $label;
    $chartRevenue[] = isset($dbMonthly[$ym]) ? (int)$dbMonthly[$ym]['omset'] : 0;
    $chartOrders[] = isset($dbMonthly[$ym]) ? (int)$dbMonthly[$ym]['orders'] : 0;
}

// 2. Data Komposisi Destinasi Wisata
$stmtDest = $pdo->query("SELECT pkt.judul, COUNT(p.id) as total_order, COALESCE(SUM(p.jumlah_pax), 0) as total_pax
                         FROM paket pkt
                         LEFT JOIN pemesanan p ON p.paket_id = pkt.id
                         GROUP BY pkt.id
                         ORDER BY total_order DESC");
$destLabels = [];
$destOrders = [];
$destPax = [];
while ($d = $stmtDest->fetch()) {
    $shortTitle = explode(' ', $d['judul'])[2] ?? $d['judul']; // e.g. Bali, Jogja, Bromo, Bandung
    $destLabels[] = $shortTitle;
    $destOrders[] = (int)$d['total_order'];
    $destPax[] = (int)$d['total_pax'];
}

// 3. Status Transaksi Breakdown
$stmtStatus = $pdo->query("SELECT 
    SUM(CASE WHEN t.status IN ('Lunas', 'Diterima') THEN 1 ELSE 0 END) as lunas,
    SUM(CASE WHEN t.status = 'Menunggu Verifikasi' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN t.status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
    COUNT(p.id) - COUNT(t.id) as belum_bayar
    FROM pemesanan p
    LEFT JOIN transaksi t ON t.pemesanan_id = p.id");
$statusBreakdown = $stmtStatus->fetch();
$lunasCount = (int)($statusBreakdown['lunas'] ?? 0);
$pendingCount = (int)($statusBreakdown['pending'] ?? 0);
$ditolakCount = (int)($statusBreakdown['ditolak'] ?? 0);
$belumBayarCount = (int)($statusBreakdown['belum_bayar'] ?? 0);

// Hitung Conversion Rate
$konversiPersen = $totalPemesanan > 0 ? round(($lunasCount / $totalPemesanan) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard & Analitik - Direktur PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .chart-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }
        .chart-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .chart-kpi-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-item {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        @media (max-width: 1024px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            .chart-kpi-bar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <div class="header-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($direkturName) ?></div>
                    <div style="font-size: 0.75rem; color: #b45309; font-weight: 800; text-transform: uppercase;">Direktur Utama</div>
                </div>
                <div class="user-avatar" style="background: #1e1b4b;"><i class="fa-solid fa-user-tie"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Salam Pembuka Eksekutif -->
            <div style="background: linear-gradient(135deg, #1e1b4b, #312e81); color: #fff; padding: 26px 30px; border-radius: var(--radius-lg); margin-bottom: 28px; box-shadow: var(--shadow-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 6px;">Executive Dashboard & Analitik</h1>
                    <p style="color: #c7d2fe; font-size: 1.05rem; margin: 0;">
                        <i class="fa-solid fa-chart-line" style="color: var(--accent);"></i>
                        <strong>Selamat datang, Direktur Utama PT. Puma Star Jaya Tour & Travel</strong>
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button onclick="window.print()" class="btn btn-outline" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-print"></i> Cetak Analitik
                    </button>
                    <a href="<?= base_url('direktur/laporan.php') ?>" class="btn btn-accent btn-lg">
                        <i class="fa-solid fa-file-invoice"></i> Rekapitulasi Laporan Lengkap
                    </a>
                </div>
            </div>

            <!-- KARTU STATISTIK KINERJA PERUSAHAAN (ERD & Rekap) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Omset Masuk</h4>
                        <div class="stat-value" style="font-size: 1.75rem; color: #059669;"><?= format_rupiah($totalPendapatan) ?></div>
                    </div>
                    <div class="stat-icon success"><i class="fa-solid fa-coins"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Peserta (Pax)</h4>
                        <div class="stat-value" style="color: var(--sky);"><?= $totalPax ?> Org</div>
                    </div>
                    <div class="stat-icon info"><i class="fa-solid fa-users"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Pemesanan</h4>
                        <div class="stat-value"><?= $totalPemesanan ?></div>
                    </div>
                    <div class="stat-icon primary"><i class="fa-solid fa-ticket"></i></div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Pelanggan Terdaftar</h4>
                        <div class="stat-value" style="color: var(--accent-hover);"><?= $totalPengguna ?></div>
                    </div>
                    <div class="stat-icon warning"><i class="fa-solid fa-id-badge"></i></div>
                </div>
            </div>

            <!-- KPI & QUICK RATIOS -->
            <div class="chart-kpi-bar">
                <div class="kpi-item">
                    <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Tingkat Konversi Lunas</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #059669;"><?= $konversiPersen ?>%</div>
                    </div>
                </div>

                <div class="kpi-item">
                    <div class="kpi-icon" style="background: #eff6ff; color: #0284c7;">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Rata-rata Nilai Order (AOV)</div>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">
                            <?= $totalPemesanan > 0 ? format_rupiah(round($totalPendapatan / $totalPemesanan)) : 'Rp 0' ?>
                        </div>
                    </div>
                </div>

                <div class="kpi-item">
                    <div class="kpi-icon" style="background: #fff7ed; color: #ea580c;">
                        <i class="fa-solid fa-bus-simple"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Armada Bus Utama</div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #ea580c;">Executive SHD 45/50</div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN GRAFIK INTERAKTIF (CHART.JS) -->
            <div class="analytics-grid">
                <!-- Grafik 1: Tren Pendapatan & Pemesanan Bulanan -->
                <div class="chart-box">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">
                                <i class="fa-solid fa-chart-column" style="color: var(--accent);"></i>
                                Tren Pendapatan & Frekuensi Pemesanan
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Periode Berjalan Tahun 2026</span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <span class="badge badge-success" style="font-size: 0.75rem;"><i class="fa-solid fa-circle"></i> Omset (Rp)</span>
                            <span class="badge badge-warning" style="font-size: 0.75rem;"><i class="fa-solid fa-circle"></i> Order</span>
                        </div>
                    </div>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>

                <!-- Grafik 2: Komposisi Destinasi Terpopuler -->
                <div class="chart-box">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">
                                <i class="fa-solid fa-chart-pie" style="color: #0284c7;"></i>
                                Porsi Destinasi Wisata
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Berdasarkan Total Order</span>
                        </div>
                    </div>
                    <div style="position: relative; height: 260px; width: 100%; display: flex; justify-content: center; align-items: center;">
                        <canvas id="destinationShareChart"></canvas>
                    </div>
                    <div style="text-align: center; margin-top: 14px; font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info"></i> Klik label legenda untuk menyembunyikan/menampilkan destinasi
                    </div>
                </div>
            </div>

            <!-- Analisis Paket Wisata Terpopuler & Kinerja Bisnis -->
            <div class="card-table">
                <div class="card-header-flex">
                    <div>
                        <h3 style="font-size: 1.15rem; margin: 0 0 4px 0; color: var(--primary);">Kinerja Paket Wisata & Kontribusi Pendapatan</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Detail rekapitulasi performa per paket wisata yang ditawarkan</p>
                    </div>
                    <a href="<?= base_url('direktur/laporan.php') ?>" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-print"></i> Cetak Laporan
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Paket Wisata</th>
                                <th>Destinasi Rute</th>
                                <th>Frekuensi Pemesanan</th>
                                <th>Total Penumpang (Pax)</th>
                                <th>Kontribusi Omset (Lunas)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($popularPackages as $pk): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($pk['judul']) ?></strong></td>
                                    <td><?= htmlspecialchars($pk['destinasi']) ?></td>
                                    <td>
                                        <span class="badge badge-warning" style="font-size: 0.85rem;">
                                            <?= $pk['total_order'] ?> Kali
                                        </span>
                                    </td>
                                    <td><?= (int)$pk['total_pax'] ?> Orang</td>
                                    <td><strong style="color: #059669;"><?= format_rupiah((int)$pk['omset']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });

        // ==========================================
        // INISIALISASI GRAFIK INTERAKTIF (CHART.JS)
        // ==========================================
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartRevenue = <?= json_encode($chartRevenue) ?>;
        const chartOrders = <?= json_encode($chartOrders) ?>;
        const destLabels = <?= json_encode($destLabels) ?>;
        const destOrders = <?= json_encode($destOrders) ?>;

        // 1. GRAFIK COMBO TREN BULANAN (Bar Omset + Line Pemesanan)
        const ctxRevenue = document.getElementById('revenueTrendChart').getContext('2d');
        const revenueChart = new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Omset Masuk (Rp)',
                        data: chartRevenue,
                        backgroundColor: 'rgba(255, 87, 34, 0.85)',
                        borderColor: '#ea580c',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        yAxisID: 'yRevenue',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Jumlah Order',
                        data: chartOrders,
                        borderColor: '#0284c7',
                        backgroundColor: '#0284c7',
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0284c7',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.35,
                        yAxisID: 'yOrders',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'yRevenue') {
                                    return ' Omset: Rp ' + context.raw.toLocaleString('id-ID');
                                } else {
                                    return ' Pemesanan: ' + context.raw + ' transaksi';
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    },
                    yRevenue: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                return 'Rp ' + value;
                            },
                            font: { family: "'Plus Jakarta Sans', sans-serif" }
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { family: "'Plus Jakarta Sans', sans-serif" }
                        },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        // 2. GRAFIK DOUGHNUT PORSI DESTINASI
        const ctxDest = document.getElementById('destinationShareChart').getContext('2d');
        const destChart = new Chart(ctxDest, {
            type: 'doughnut',
            data: {
                labels: destLabels,
                datasets: [{
                    data: destOrders,
                    backgroundColor: [
                        '#ff5722', // Bali Orange
                        '#0284c7', // Jogja Sky
                        '#10b981', // Bromo Emerald
                        '#8b5cf6', // Bandung Purple
                        '#f59e0b'  // Amber
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 12,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.raw;
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ' ' + context.label + ': ' + val + ' Order (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
