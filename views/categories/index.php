<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

// Ambil semua data kategori
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) AS total_products FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar a { color: #cfd4da; text-decoration: none; padding: 12px 20px; display: block; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 p-3 sidebar">
            <h4 class="text-white text-center mb-4"><i class="fa-solid fa-boxes-stacked me-2"></i>Inventory</h4>
            <div class="text-secondary small px-3 mb-2">NAVIGASI</div>
            <a href="../dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
            <a href="index.php" class="active"><i class="fa-solid fa-tags me-2"></i>Kategori</a>
            <a href="../products/index.php"><i class="fa-solid fa-box me-2"></i>Produk</a>
            <a href="../suppliers/index.php"><i class="fa-solid fa-truck-field me-2"></i>Supplier</a>
            <a href="../stock/index.php"><i class="fa-solid fa-right-left me-2"></i>Transaksi Stok</a>
            
            <hr class="text-secondary">
            <a href="../auth/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <div>
                    <h3 class="mb-0"><i class="fa-solid fa-tags me-2"></i>Kelola Kategori</h3>
                    <small class="text-muted">Manajemen kategori produk dalam sistem inventory</small>
                </div>
                <a href="create.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah Kategori</a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah Produk</th>
                                    <th style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $index => $cat): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= e($cat['name']) ?></strong></td>
                                            <td><?= e($cat['description'] ?? '-') ?></td>
                                            <td><span class="badge bg-info text-dark"><?= $cat['total_products'] ?> Produk</span></td>
                                            <td>
                                                <a href="edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                                <a href="delete.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data kategori. Klik "Tambah Kategori" untuk membuat baru.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>