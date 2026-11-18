<?php
// admin/nego.php - Manajemen Persetujuan Negosiasi Tawar Harga Rombongan
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['level'], ['admin', 'bendahara', 'direktur'])) {
    header("Location: " . base_url('login.php'));
    exit;
}

$success = '';
$error = '';

// Handle Persetujuan / Penolakan Nego
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_nego'])) {
    $pemesananId = (int)$_POST['pemesanan_id'];
    $action = $_POST['action_nego']; // 'setujui' atau 'tolak'

    $stmtP = $pdo->prepare("SELECT p.*, pkt.harga as harga_normal FROM pemesanan p JOIN paket pkt ON p.paket_id = pkt.id WHERE p.id = ?");
    $stmtP->execute([$pemesananId]);
    $order = $stmtP->fetch();

    if ($order && $order['status_nego'] === 'Menunggu') {
        if ($action === 'setujui') {
            $totalBaru = (int)$order['harga_nego'] * (int)$order['jumlah_pax'];
            $stmtUp = $pdo->prepare("UPDATE pemesanan SET status_nego = 'Disetujui', harga = ?, updated_at = NOW() WHERE id = ?");
            $stmtUp->execute([$totalBaru, $pemesananId]);

            // Jika transaksi sudah dibuat, update juga harga pemesanan di transaksi
            $pdo->prepare("UPDATE transaksi SET updated_at = NOW() WHERE pemesanan_id = ?")->execute([$pemesananId]);

            $success = "Pengajuan negosiasi rombongan #ORD-{$pemesananId} berhasil DISETUJUI! Total harga disesuaikan menjadi " . format_rupiah($totalBaru);
        } elseif ($action === 'tolak') {
            $stmtUp = $pdo->prepare("UPDATE pemesanan SET status_nego = 'Ditolak', updated_at = NOW() WHERE id = ?");
            $stmtUp->execute([$pemesananId]);

            $success = "Pengajuan negosiasi rombongan #ORD-{$pemesananId} telah DITOLAK. Harga kembali ke tarif standar.";
        }
    } else {
        $error = "Data negosiasi tidak valid atau telah diproses sebelumnya.";
    }
}

// Ambil daftar pengajuan negosiasi
$filterStatus = clean_input($_GET['status'] ?? 'all');
$sql = "SELECT p.*, u.nama as nama_pemesan, u.email as email_pemesan, pkt.judul as nama_paket, pkt.harga as harga_satuan_normal
        FROM pemesanan p
        JOIN user u ON p.user_id = u.id
        JOIN paket pkt ON p.paket_id = pkt.id
        WHERE p.status_nego != 'None'";

if ($filterStatus === 'menunggu') {
    $sql .= " AND p.status_nego = 'Menunggu'";
} elseif ($filterStatus === 'disetujui') {
    $sql .= " AND p.status_nego = 'Disetujui'";
} elseif ($filterStatus === 'ditolak') {
    $sql .= " AND p.status_nego = 'Ditolak'";
}

$sql .= " ORDER BY p.id DESC";
$negoList = $pdo->query($sql)->fetchAll();

// Hitung total pending nego
$totalPendingNego = (int)$pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_nego = 'Menunggu'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Negosiasi Harga - PT. Puma Star Jaya Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="dashboard-layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <button class="header-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <div class="header-profile">
                <div style="text-align: right;">
                    <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--accent-hover); font-weight: 800; text-transform: uppercase;">
                        <?= htmlspecialchars($_SESSION['level']) ?>
                    </div>
                </div>
                <div class="user-avatar"><i class="fa-solid fa-handshake"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Header Halaman -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="font-size: 1.8rem; color: var(--primary); margin: 0 0 4px 0;">
                        <i class="fa-solid fa-handshake" style="color: var(--accent);"></i> Manajemen Negosiasi Harga Rombongan
                    </h1>
                    <p style="color: var(--text-muted); margin: 0;">Persetujuan tawar harga khusus rombongan wisata skala besar melalui sistem</p>
                </div>
                <div style="background: #ffffff; padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.9rem;">
                    Menunggu Persetujuan: <strong style="color: var(--warning);"><?= $totalPendingNego ?> Permintaan</strong>
                </div>
            </div>

            <?php if (!empty($success)): ?>
                <div style="background: #dcfce7; color: #15803d; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; border: 1px solid #86efac; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Tab Filter Status -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <a href="<?= base_url('admin/nego.php?status=all') ?>" class="btn btn-sm <?= ($filterStatus === 'all') ? 'btn-primary' : 'btn-outline' ?>" style="<?= ($filterStatus !== 'all') ? 'background:#fff;' : '' ?>">Semua Data</a>
                <a href="<?= base_url('admin/nego.php?status=menunggu') ?>" class="btn btn-sm <?= ($filterStatus === 'menunggu') ? 'btn-primary' : 'btn-outline' ?>" style="<?= ($filterStatus !== 'menunggu') ? 'background:#fff;' : '' ?>">
                    Menunggu Persetujuan (<?= $totalPendingNego ?>)
                </a>
                <a href="<?= base_url('admin/nego.php?status=disetujui') ?>" class="btn btn-sm <?= ($filterStatus === 'disetujui') ? 'btn-primary' : 'btn-outline' ?>" style="<?= ($filterStatus !== 'disetujui') ? 'background:#fff;' : '' ?>">Disetujui</a>
                <a href="<?= base_url('admin/nego.php?status=ditolak') ?>" class="btn btn-sm <?= ($filterStatus === 'ditolak') ? 'btn-primary' : 'btn-outline' ?>" style="<?= ($filterStatus !== 'ditolak') ? 'background:#fff;' : '' ?>">Ditolak</a>
            </div>

            <!-- Tabel Daftar Nego -->
            <div class="card-table">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pemesan & Rombongan</th>
                                <th>Paket Wisata</th>
                                <th>Pax</th>
                                <th>Harga Normal</th>
                                <th>Tawaran Konsumen</th>
                                <th>Total Nego</th>
                                <th>Status</th>
                                <th style="text-align: center;">Keputusan Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($negoList)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        Tidak ada data pengajuan negosiasi harga pada filter ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($negoList as $n): 
                                    $totalNormal = (int)$n['harga_satuan_normal'] * (int)$n['jumlah_pax'];
                                    $totalTawar = (int)$n['harga_nego'] * (int)$n['jumlah_pax'];
                                    $hemat = $totalNormal - $totalTawar;
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($n['nama_pemesan']) ?></strong><br>
                                            <small style="color: var(--text-muted);">Rombongan: <?= htmlspecialchars($n['nama_rombongan']) ?></small><br>
                                            <small><i class="fa-brands fa-whatsapp" style="color: #059669;"></i> <?= htmlspecialchars($n['no_hp']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($n['nama_paket']) ?></td>
                                        <td><strong><?= htmlspecialchars($n['jumlah_pax']) ?> Org</strong></td>
                                        <td>
                                            <?= format_rupiah($n['harga_satuan_normal']) ?> /pax<br>
                                            <small style="color: var(--text-muted);">Total: <?= format_rupiah($totalNormal) ?></small>
                                        </td>
                                        <td>
                                            <strong style="color: var(--accent-hover);"><?= format_rupiah($n['harga_nego']) ?> /pax</strong>
                                            <?php if (!empty($n['catatan_nego'])): ?>
                                                <br><small style="color: #64748b; font-style: italic;">"<?= htmlspecialchars($n['catatan_nego']) ?>"</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color: #059669; font-size: 1.05rem;"><?= format_rupiah($totalTawar) ?></strong><br>
                                            <small style="color: #059669;">Hemat: <?= format_rupiah(max(0, $hemat)) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $stNego = $n['status_nego'];
                                            $bClass = 'badge-warning';
                                            if ($stNego === 'Disetujui') $bClass = 'badge-success';
                                            elseif ($stNego === 'Ditolak') $bClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $bClass ?>"><?= htmlspecialchars($stNego) ?></span>
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            <?php if ($stNego === 'Menunggu'): ?>
                                                <form method="POST" action="" style="display: inline-flex; gap: 6px;">
                                                    <input type="hidden" name="pemesanan_id" value="<?= $n['id'] ?>">
                                                    <button type="submit" name="action_nego" value="setujui" class="btn btn-sm btn-success" onclick="return confirm('Setujui tawaran harga <?= format_rupiah($n['harga_nego']) ?>/pax untuk rombongan ini?');">
                                                        <i class="fa-solid fa-check"></i> Setujui
                                                    </button>
                                                    <button type="submit" name="action_nego" value="tolak" class="btn btn-sm btn-danger" onclick="return confirm('Tolak tawaran negosiasi harga ini?');">
                                                        <i class="fa-solid fa-xmark"></i> Tolak
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Selesai Diproses</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
    </script>
</body>
</html>

