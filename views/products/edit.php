<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Ambil daftar kategori
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $categoryId    = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $purchasePrice = filter_input(INPUT_POST, 'purchase_price', FILTER_VALIDATE_FLOAT);
    $sellingPrice  = filter_input(INPUT_POST, 'selling_price', FILTER_VALIDATE_FLOAT);
    $minStock      = filter_input(INPUT_POST, 'min_stock', FILTER_VALIDATE_INT);

    if (!empty($name) && $purchasePrice !== false && $sellingPrice !== false) {
        $stmt = $pdo->prepare("UPDATE products SET 
            name = :name, 
            category_id = :category_id, 
            purchase_price = :purchase_price, 
            selling_price = :selling_price, 
            min_stock = :min_stock 
            WHERE id = :id");

        $stmt->execute([
            'name'           => $name,
            'category_id'    => $categoryId ?: null,
            'purchase_price' => $purchasePrice,
            'selling_price'  => $sellingPrice,
            'min_stock'      => $minStock ?? 5,
            'id'             => $id
        ]);

        header('Location: index.php');
        exit;
    } else {
        $error = 'Harap isi semua bidang wajib dengan benar!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">

<div class="container" style="max-width: 650px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h4 class="card-title mb-0">Edit Produk</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">SKU Produk (Read-Only)</label>
                    <input type="text" class="form-control" value="<?= e($product['sku']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Tanpa Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Harga Beli (Rp)</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= $product['purchase_price'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Harga Jual (Rp)</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Batas Minimum Stok (Peringatan)</label>
                    <input type="number" name="min_stock" class="form-control" value="<?= $product['min_stock'] ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-warning">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>