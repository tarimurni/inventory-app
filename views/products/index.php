<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

// Paramater Filter, Search, dan Pagination
$search = trim($_GET['search'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Base Query
$where = [];
$params = [];

if ($search !== '') {
    // Memakai 2 parameter terpisah agar PDO tidak error HY093
    $where[] = "(p.name LIKE :search_name OR p.sku LIKE :search_sku)";
    $params['search_name'] = "%{$search}%";
    $params['search_sku']  = "%{$search}%";
}

if ($categoryId) {
    $where[] = "p.category_id = :category_id";
    $params['category_id'] = $categoryId;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung Total Data untuk Pagination
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Query Ambil Data Produk + Nama Kategori
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON c.id = p.category_id 
        $whereSql 
        ORDER BY p.id DESC 
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Ambil Semua Kategori untuk Dropdown Filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Inventory System</title>
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
            <a href="index.php" class="active"><i class="fa-solid fa-box me-2"></i>Produk</a>
            <a href="../suppliers/index.php"><i class="fa-solid fa-truck-field me-2"></i>Supplier</a>
            <a href="../stock/index.php"><i class="fa-solid fa-right-left me-2"></i>Transaksi Stok</a>
            
            <hr class="text-secondary">
            <a href="../auth/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <div>
                    <h3 class="mb-0"><i class="fa-solid fa-box me-2"></i>Kelola Produk</h3>
                    <small class="text-muted">Daftar stok produk dan informasi barang</small>
                </div>
                <a href="create.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah Produk</a>
            </div>

            <!-- Form Search & Filter -->
            <div class="card shadow-sm border-0 mb-4 p-3">
                <form method="GET" class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari SKU atau Nama Produk..." value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="category_id" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Cari</button>
                        <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate me-1"></i> Reset</a>
                    </div>
                </form>
            </div>

            <!-- Tabel Produk -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Batas Min.</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $p): ?>
                                        <tr>
                                            <td><code><?= e($p['sku']) ?></code></td>
                                            <td><strong><?= e($p['name']) ?></strong></td>
                                            <td><span class="badge bg-secondary"><?= e($p['category_name']) ?></span></td>
                                            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($p['stock'] <= $p['minimum_stock']): ?>
                                                    <span class="badge bg-danger"><?= $p['stock'] ?> (Low)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?= $p['stock'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $p['minimum_stock'] ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i> Edit</a>
                                                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Data produk tidak ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-end py-3">
                        <nav>
                            <ul class="pagination mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= $categoryId ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>