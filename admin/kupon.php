<?php
// admin/kupon.php - Sesuai Tabel 32: Melihat, menghapus dan menambahkan kupon
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$adminName = $_SESSION['nama'];
$message = '';
$error = '';

// Handle Delete Kupon Sesuai Tabel 32
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmtDel = $pdo->prepare("DELETE FROM kupon WHERE id = ?");
    if ($stmtDel->execute([$delId])) {
        $message = "Kupon diskon berhasil dihapus.";
    } else {
        $error = "Gagal menghapus kupon.";
    }
}

// Handle Tambah Kupon Sesuai Tabel 32
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_kupon'])) {
    $kode = strtoupper(clean_input($_POST['kode'] ?? ''));
    $potongan = (int)($_POST['potongan'] ?? 0);

    if (empty($kode) || $potongan <= 0) {
        $error = "Kode Kupon dan Besaran Potongan (Rp) wajib diisi valid!";
    } else {
        // Cek duplikat
        $stmtCheck = $pdo->prepare("SELECT id FROM kupon WHERE kode = ?");
        $stmtCheck->execute([$kode]);
        if ($stmtCheck->fetch()) {
            $error = "Kode kupon ini sudah ada! Gunakan kode kupon yang unik.";
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO kupon (user_id, kode, potongan, created_at, updated_at) VALUES (NULL, ?, ?, NOW(), NOW())");
            if ($stmtIns->execute([$kode, $potongan])) {
                $message = "Kupon diskon baru berhasil ditambahkan.";
            } else {
                $error = "Terjadi kegagalan saat menambahkan kupon.";
            }
        }
    }
}

$kupons = $pdo->query("SELECT * FROM kupon ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek & Kelola Kupon - Admin PT. Puma Star Jaya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($adminName) ?></div>
                <div class="user-avatar"><i class="fa-solid fa-user-shield"></i></div>
            </div>
        </header>

        <div class="dashboard-body">
            <div style="margin-bottom: 24px;">
                <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Pengelolaan Kupon Diskon</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Melihat, menghapus dan menambahkan kupon potongan harga (Tabel 32)</p>
            </div>

            <?php if (!empty($message)): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 30px;">
                <!-- FORM TAMBAH KUPON (Tabel 32) -->
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 26px; box-shadow: var(--shadow-sm); height: fit-content;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        Tambah Kupon Baru
                    </h3>

                    <form method="POST" action="">
                        <input type="hidden" name="save_kupon" value="1">

                        <div class="form-group">
                            <label class="form-label">Kode Kupon Diskon</label>
                            <input type="text" name="kode" class="form-control" placeholder="Contoh: PROMOLEBARAN" style="text-transform: uppercase;" required>
                            <small style="color: var(--text-muted); font-size: 0.8rem;">Gunakan huruf kapital tanpa spasi.</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nominal Potongan Harga (Rp)</label>
                            <input type="number" name="potongan" class="form-control" placeholder="Contoh: 500000" min="1000" step="500" required>
                        </div>

                        <button type="submit" class="btn btn-accent" style="width: 100%; margin-top: 10px;">
                            <i class="fa-solid fa-plus-circle"></i> Simpan Kupon Baru
                        </button>
                    </form>
                </div>

                <!-- TABEL DAFTAR KUPON (Tabel 32) -->
                <div class="card-table">
                    <div class="card-header-flex">
                        <h3 style="font-size: 1.15rem; margin: 0; color: var(--primary);">Daftar Kupon Aktif (<?= count($kupons) ?>)</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Kupon</th>
                                    <th>Nilai Potongan</th>
                                    <th>Tanggal Dibuat</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kupons)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 28px; color: var(--text-muted);">Belum ada kupon yang dibuat.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($kupons as $k): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <code style="background: #f1f5f9; padding: 4px 10px; border-radius: 4px; font-weight: 800; color: var(--primary); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($k['kode']) ?>
                                                </code>
                                            </td>
                                            <td><strong style="color: #059669;"><?= format_rupiah($k['potongan']) ?></strong></td>
                                            <td><?= format_tanggal_indo($k['created_at']) ?></td>
                                            <td style="text-align: center;">
                                                <a href="<?= base_url('admin/kupon.php?delete=' . $k['id']) ?>" onclick="return confirm('Hapus kupon ini?');" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3);" title="Hapus Kupon">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('mainSidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
