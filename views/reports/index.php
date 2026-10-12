<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

// Tangkap Filter Tanggal (jika ada)
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

// Query Laporan Ringkasan Stok Produk
$stmtProducts = $pdo->query("SELECT p.*, c.name AS category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             ORDER BY p.stock ASC");
$products = $stmtProducts->fetchAll();

// Query Laporan Transaksi Berdasarkan Filter Tanggal
$sqlTrans = "SELECT sm.*, p.name AS product_name, p.sku 
             FROM stock_movements sm 
             JOIN products p ON p.id = sm.product_id 
             WHERE DATE(sm.created_at) BETWEEN :start_date AND :end_date 
             ORDER BY sm.created_at DESC";
$stmtTrans = $pdo->prepare($sqlTrans);
$stmtTrans->execute([
    'start_date' => $startDate,
    'end_date'   => $endDate
]);
$transactions = $stmtTrans->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Inventory System</title>
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
            <a href="../categories/index.php"><i class="fa-solid fa-tags me-2"></i>Kategori</a>
            <a href="../products/index.php"><i class="fa-solid fa-box me-2"></i>Produk</a>
            <a href="../suppliers/index.php"><i class="fa-solid fa-truck-field me-2"></i>Supplier</a>
            <a href="../stock/index.php"><i class="fa-solid fa-right-left me-2"></i>Transaksi Stok</a>
            <a href="index.php" class="active"><i class="fa-solid fa-file-lines me-2"></i>Laporan</a>
            
            <hr class="text-secondary">
            <a href="../auth/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <div>
                    <h3 class="mb-0"><i class="fa-solid fa-file-lines me-2"></i>Laporan Stok & Transaksi</h3>
                    <small class="text-muted">Pantau posisi stok dan riwayat mutasi barang</small>
                </div>
                <!-- Tombol Export Excel & Cetak PDF -->
                <div class="d-flex gap-2">
                    <a href="export_excel.php?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-success">
                        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                    </a>
                    <a href="print.php?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" target="_blank" class="btn btn-danger">
                        <i class="fa-solid fa-file-pdf me-1"></i> Cetak / Simpan PDF
                    </a>
                </div>
            </div>

            <!-- Filter Tanggal Transaksi -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="<?= e($endDate) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Status Stok Saat Ini -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white font-weight-bold py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-layer-group me-2 text-primary"></i>Ringkasan Stok Produk Saat Ini</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Stok Tersisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><code><?= e($p['sku']) ?></code></td>
                                        <td><strong><?= e($p['name']) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= e($p['category_name'] ?? 'Tanpa Kategori') ?></span></td>
                                        <td>Rp <?= number_format($p['purchase_price'] ?? $p['price'] ?? 0, 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($p['selling_price'] ?? $p['price'] ?? 0, 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($p['stock'] <= ($p['min_stock'] ?? $p['minimum_stock'] ?? 5)): ?>
                                                <span class="badge bg-danger"><?= $p['stock'] ?> (Stok Menipis!)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?= $p['stock'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabel Transaksi Hasil Filter -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Mutasi Barang (<?= date('d/m/Y', strtotime($startDate)) ?> s/d <?= date('d/m/Y', strtotime($endDate)) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                                            <td><strong><?= e($t['product_name']) ?></strong> (<?= e($t['sku']) ?>)</td>
                                            <td>
                                                <span class="badge <?= $t['type'] === 'IN' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= $t['type'] ?>
                                                </span>
                                            </td>
                                            <td><strong><?= number_format($t['quantity']) ?></strong></td>
                                            <td><?= e($t['notes'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Tidak ada transaksi pada periode ini.</td>
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