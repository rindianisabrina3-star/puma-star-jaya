<?php
// admin/user.php - Sesuai Tabel 32: Manajemen Pengguna (Melihat pengguna user)
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'admin') {
    header("Location: " . base_url('login.php'));
    exit;
}

$adminName = $_SESSION['nama'];
$message = '';
$error = '';

// Handle Ubah Role User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $uId = (int)$_POST['user_id'];
    $newRole = clean_input($_POST['level']);

    if (in_array($newRole, ['konsumen', 'admin', 'bendahara', 'direktur'])) {
        $stmtRole = $pdo->prepare("UPDATE user SET level = ?, updated_at = NOW() WHERE id = ?");
        if ($stmtRole->execute([$newRole, $uId])) {
            $message = "Hak akses pengguna berhasil diperbarui.";
        }
    }
}

// Handle Hapus User
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === $_SESSION['user_id']) {
        $error = "Anda tidak dapat menghapus akun Anda sendiri!";
    } else {
        $stmtDel = $pdo->prepare("DELETE FROM user WHERE id = ?");
        if ($stmtDel->execute([$delId])) {
            $message = "Pengguna berhasil dihapus.";
        }
    }
}

$filterLevel = clean_input($_GET['level'] ?? '');
if (!empty($filterLevel)) {
    $stmtUsers = $pdo->prepare("SELECT * FROM user WHERE level = ? ORDER BY id DESC");
    $stmtUsers->execute([$filterLevel]);
} else {
    $stmtUsers = $pdo->query("SELECT * FROM user ORDER BY id DESC");
}
$users = $stmtUsers->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Admin PT. Puma Star Jaya</title>
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
                    <h1 style="font-size: 1.85rem; color: var(--primary); margin: 0;">Manajemen Pengguna</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Melihat dan mengelola pengguna sistem (Tabel 32)</p>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="<?= base_url('admin/user.php') ?>" class="btn <?= empty($filterLevel) ? 'btn-primary' : 'btn-outline' ?> btn-sm">Semua</a>
                    <a href="<?= base_url('admin/user.php?level=konsumen') ?>" class="btn <?= $filterLevel === 'konsumen' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Konsumen</a>
                    <a href="<?= base_url('admin/user.php?level=admin') ?>" class="btn <?= $filterLevel === 'admin' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Admin</a>
                    <a href="<?= base_url('admin/user.php?level=bendahara') ?>" class="btn <?= $filterLevel === 'bendahara' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Bendahara</a>
                    <a href="<?= base_url('admin/user.php?level=direktur') ?>" class="btn <?= $filterLevel === 'direktur' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Direktur</a>
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

            <div class="card-table">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Hak Akses (Level)</th>
                                <th>Tanggal Registrasi</th>
                                <th style="text-align: center;">Ubah Level / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="user-avatar" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                                <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                            </div>
                                            <strong><?= htmlspecialchars($u['nama']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php 
                                        $lvl = $u['level'];
                                        $badge = 'badge-info';
                                        if ($lvl === 'admin') $badge = 'badge-danger';
                                        else if ($lvl === 'bendahara') $badge = 'badge-warning';
                                        else if ($lvl === 'direktur') $badge = 'badge-success';
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= strtoupper($lvl) ?></span>
                                    </td>
                                    <td><?= format_tanggal_indo($u['created_at']) ?></td>
                                    <td style="text-align: center;">
                                        <form method="POST" action="" style="display: inline-flex; align-items: center; gap: 6px;">
                                            <input type="hidden" name="update_role" value="1">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="level" class="form-control" style="padding: 4px 8px; font-size: 0.8rem; width: auto;" onchange="this.form.submit()">
                                                <option value="konsumen" <?= $u['level'] === 'konsumen' ? 'selected' : '' ?>>Konsumen</option>
                                                <option value="admin" <?= $u['level'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="bendahara" <?= $u['level'] === 'bendahara' ? 'selected' : '' ?>>Bendahara</option>
                                                <option value="direktur" <?= $u['level'] === 'direktur' ? 'selected' : '' ?>>Direktur</option>
                                            </select>
                                        </form>

                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <a href="<?= base_url('admin/user.php?delete=' . $u['id']) ?>" onclick="return confirm('Hapus user ini?');" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 4px 8px;" title="Hapus User">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
