<?php
// admin/paket.php - Sesuai Tabel 32: Melihat, menghapus dan menambahkan paket wisata
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$adminName = $_SESSION['nama'];
$message = '';
$error = '';

// Handle Delete Paket Sesuai Tabel 32
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmtDel = $pdo->prepare("DELETE FROM paket WHERE id = ?");
    if ($stmtDel->execute([$delId])) {
        $message = "Paket wisata berhasil dihapus dari sistem.";
    } else {
        $error = "Gagal menghapus paket wisata.";
    }
}

// Handle Add / Edit Paket Sesuai Tabel 32
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_paket'])) {
    $editId = (int)($_POST['paket_id'] ?? 0);
    $judul = clean_input($_POST['judul'] ?? '');
    $destinasi = clean_input($_POST['destinasi'] ?? '');
    $deskripsi = clean_input($_POST['deskripsi'] ?? '');
    $fasilitas = clean_input($_POST['fasilitas'] ?? '');
    $bus = clean_input($_POST['bus'] ?? '');
    $harga = (int)($_POST['harga'] ?? 0);
    $foto = clean_input($_POST['foto'] ?? '');

    if (empty($judul) || empty($destinasi) || empty($bus) || $harga <= 0) {
        $error = "Judul, Destinasi, Bus, dan Harga wajib diisi!";
    } else {
        if ($editId > 0) {
            $stmtUp = $pdo->prepare("UPDATE paket SET judul = ?, destinasi = ?, deskripsi = ?, fasilitas = ?, bus = ?, harga = ?, foto = ?, updated_at = NOW() WHERE id = ?");
            if ($stmtUp->execute([$judul, $destinasi, $deskripsi, $fasilitas, $bus, $harga, $foto, $editId])) {
                $message = "Paket wisata berhasil diperbarui.";
            }
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO paket (judul, destinasi, deskripsi, fasilitas, bus, harga, foto, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmtIns->execute([$judul, $destinasi, $deskripsi, $fasilitas, $bus, $harga, $foto])) {
                $message = "Paket wisata baru berhasil ditambahkan.";
            }
        }
    }
}

// Data paket untuk edit jika ada
$editData = null;
if (isset($_GET['edit'])) {
    $stmtFind = $pdo->prepare("SELECT * FROM paket WHERE id = ?");
    $stmtFind->execute([(int)$_GET['edit']]);
    $editData = $stmtFind->fetch();
}

$pakets = $pdo->query("SELECT * FROM paket ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket Wisata - Admin PT. Puma Star Jaya</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Pengelolaan Paket Wisata</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Fitur untuk melihat, menambahkan, dan menghapus paket wisata (Tabel 32)</p>
                </div>
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

            <div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 30px;">
                <!-- FORM TAMBAH / EDIT PAKET (Tabel 32) -->
                <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 26px; box-shadow: var(--shadow-sm); height: fit-content;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <?= $editData ? 'Edit Paket Wisata' : 'Tambah Paket Wisata Baru' ?>
                    </h3>

                    <form method="POST" action="">
                        <input type="hidden" name="save_paket" value="1">
                        <input type="hidden" name="paket_id" value="<?= $editData['id'] ?? 0 ?>">

                        <div class="form-group">
                            <label class="form-label">Judul Paket</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Wisata Bali Eksotis 3D2N" value="<?= htmlspecialchars($editData['judul'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Destinasi</label>
                            <input type="text" name="destinasi" class="form-control" placeholder="Denpasar, Ubud, Kuta" value="<?= htmlspecialchars($editData['destinasi'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Rincian agenda dan kegiatan..."><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fasilitas</label>
                            <input type="text" name="fasilitas" class="form-control" placeholder="Hotel Bintang 3, Makan 7x, Guide" value="<?= htmlspecialchars($editData['fasilitas'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Armada Bus Pariwisata</label>
                            <input type="text" name="bus" class="form-control" placeholder="Big Bus SHD 45 Seat AC Karaoke" value="<?= htmlspecialchars($editData['bus'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Harga Satuan (Rp / Pax)</label>
                            <input type="number" name="harga" class="form-control" placeholder="1500000" value="<?= htmlspecialchars($editData['harga'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">URL Foto Paket</label>
                            <input type="url" name="foto" class="form-control" placeholder="https://images.unsplash.com/..." value="<?= htmlspecialchars($editData['foto'] ?? '') ?>">
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <button type="submit" class="btn btn-accent" style="flex-grow: 1;">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Paket
                            </button>
                            <?php if ($editData): ?>
                                <a href="<?= base_url('admin/paket.php') ?>" class="btn btn-outline">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- TABEL DAFTAR PAKET (Tabel 32) -->
                <div class="card-table">
                    <div class="card-header-flex">
                        <h3 style="font-size: 1.15rem; margin: 0; color: var(--primary);">Katalog Paket Terdaftar (<?= count($pakets) ?>)</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Judul & Destinasi</th>
                                    <th>Bus</th>
                                    <th>Harga / Pax</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pakets as $p): ?>
                                    <tr>
                                        <td style="width: 70px;">
                                            <img src="<?= htmlspecialchars($p['foto'] ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=200&q=80') ?>" alt="" style="width: 60px; height: 45px; object-fit: cover; border-radius: var(--radius-sm);">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['judul']) ?></strong><br>
                                            <small style="color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p['destinasi']) ?></small>
                                        </td>
                                        <td><small><?= htmlspecialchars($p['bus']) ?></small></td>
                                        <td><strong style="color: var(--accent-hover);"><?= format_rupiah($p['harga']) ?></strong></td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            <a href="<?= base_url('admin/paket.php?edit=' . $p['id']) ?>" class="btn btn-outline btn-sm" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="<?= base_url('admin/paket.php?delete=' . $p['id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?');" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3);" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
