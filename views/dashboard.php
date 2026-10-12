<?php
require_once '../config/database.php';
require_once '../app/Helpers/auth.php';
require_once '../app/Helpers/escape.php';

checkLogin();

// --- QUERY STATISTIK DASHBOARD ---

// 1. Total Produk
$stmtTotalProd = $pdo->query("SELECT COUNT(id) AS total_products FROM products");
$totalProducts = $stmtTotalProd->fetch()['total_products'] ?? 0;

// 2. Nilai Inventory (SUM(stock * price))
$stmtInventoryValue = $pdo->query("SELECT SUM(stock * price) AS total_value FROM products");
$inventoryValue = $stmtInventoryValue->fetch()['total_value'] ?? 0;

// 3. Produk Stok Rendah (stock <= minimum_stock)
$stmtLowStock = $pdo->query("SELECT COUNT(id) AS low_stock FROM products WHERE stock <= minimum_stock");
$lowStockCount = $stmtLowStock->fetch()['low_stock'] ?? 0;

// 4. Total Barang Masuk (IN)
$stmtTotalIn = $pdo->query("SELECT SUM(quantity) AS total_in FROM stock_movements WHERE type = 'IN'");
$totalIn = $stmtTotalIn->fetch()['total_in'] ?? 0;

// 5. Total Barang Keluar (OUT)
$stmtTotalOut = $pdo->query("SELECT SUM(quantity) AS total_out FROM stock_movements WHERE type = 'OUT'");
$totalOut = $stmtTotalOut->fetch()['total_out'] ?? 0;

// 6. Ambil daftar produk dengan stok rendah untuk peringatan
$stmtLowStockItems = $pdo->query("SELECT sku, name, stock, minimum_stock FROM products WHERE stock <= minimum_stock ORDER BY stock ASC LIMIT 5");
$lowStockItems = $stmtLowStockItems->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management System</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar a { color: #cfd4da; text-decoration: none; padding: 12px 20px; display: block; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
        .stat-card { border: none; border-radius: 10px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 p-3 sidebar">
            <h4 class="text-white text-center mb-4"><i class="fa-solid fa-boxes-stacked me-2"></i>Inventory</h4>
            <div class="text-secondary small px-3 mb-2">NAVIGASI</div>
            <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
            <a href="categories/index.php"><i class="fa-solid fa-tags me-2"></i>Kategori</a>
            <a href="products/index.php"><i class="fa-solid fa-box me-2"></i>Produk</a>
            <a href="suppliers/index.php"><i class="fa-solid fa-truck-field me-2"></i>Supplier</a>
            <a href="stock/index.php"><i class="fa-solid fa-right-left me-2"></i>Transaksi Stok</a>
            <a href="reports/index.php"><i class="fa-solid fa-file-lines me-2"></i>Laporan</a>
            
            <hr class="text-secondary">
            <a href="auth/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <!-- Header User Info -->
            <div class="bg-white p-3 rounded shadow-sm mb-4">
                <h3 class="mb-0">Dashboard</h3>
                <small class="text-muted">Selamat datang kembali, <strong><?= e($_SESSION['user']['username']) ?></strong> (Role: <?= e(strtoupper($_SESSION['user']['role'])) ?>)</small>
            </div>

            <!-- Widget Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-lg">
                    <div class="card stat-card bg-primary text-white p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase fw-bold opacity-75">Total Produk</small>
                                <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalProducts) ?></h3>
                            </div>
                            <i class="fa-solid fa-box fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-lg">
                    <div class="card stat-card bg-success text-white p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase fw-bold opacity-75">Nilai Inventory</small>
                                <h3 class="fw-bold mb-0 mt-1">Rp <?= number_format($inventoryValue, 0, ',', '.') ?></h3>
                            </div>
                            <i class="fa-solid fa-coins fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-lg">
                    <div class="card stat-card bg-danger text-white p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase fw-bold opacity-75">Stok Rendah</small>
                                <h3 class="fw-bold mb-0 mt-1"><?= number_format($lowStockCount) ?></h3>
                            </div>
                            <i class="fa-solid fa-triangle-exclamation fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg">
                    <div class="card stat-card bg-info text-white p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase fw-bold opacity-75">Barang Masuk</small>
                                <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalIn) ?></h3>
                            </div>
                            <i class="fa-solid fa-arrow-down-long fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg">
                    <div class="card stat-card bg-warning text-dark p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase fw-bold opacity-75">Barang Keluar</small>
                                <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalOut) ?></h3>
                            </div>
                            <i class="fa-solid fa-arrow-up-long fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Peringatan Stok Rendah -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-danger"><i class="fa-solid fa-bell me-2"></i>Peringatan Stok Rendah</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Stok Sekarang</th>
                                    <th>Batas Minimum</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lowStockItems)): ?>
                                    <?php foreach ($lowStockItems as $item): ?>
                                        <tr>
                                            <td><code><?= e($item['sku']) ?></code></td>
                                            <td><?= e($item['name']) ?></td>
                                            <td><span class="badge bg-danger"><?= e($item['stock']) ?></span></td>
                                            <td><?= e($item['minimum_stock']) ?></td>
                                            <td><small class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i>Perlu Restok</small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Semua stok produk masih aman.</td>
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