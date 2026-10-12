<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$error = '';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId   = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $sku          = trim($_POST['sku'] ?? '');
    $name         = trim($_POST['name'] ?? '');
    $price        = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock        = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $minimumStock = filter_input(INPUT_POST, 'minimum_stock', FILTER_VALIDATE_INT);

    if ($categoryId && $sku && $name && $price !== false && $stock !== false) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) VALUES (:category_id, :sku, :name, :price, :stock, :minimum_stock)");
            $stmt->execute([
                'category_id'   => $categoryId,
                'sku'           => $sku,
                'name'          => $name,
                'price'         => $price,
                'stock'         => $stock,
                'minimum_stock' => $minimumStock ?? 5
            ]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: SKU sudah digunakan atau data tidak valid!';
        }
    } else {
        $error = 'Harap isi semua kolom inputan dengan benar!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk Baru - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">

<div class="container" style="max-width: 650px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0">Tambah Produk Baru</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kode SKU</label>
                    <input type="text" name="sku" class="form-control" placeholder="Contoh: KBD-001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Produk</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Keyboard Mechanical RGB" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Harga (Rp)</label>
                        <input type="number" name="price" class="form-control" min="0" placeholder="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Stok Awal</label>
                        <input type="number" name="stock" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Stok Min.</label>
                        <input type="number" name="minimum_stock" class="form-control" min="0" value="5" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>