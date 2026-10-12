<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$error = '';
$products = $pdo->query("SELECT id, name, sku, stock FROM products ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $type      = $_POST['type'] ?? '';
    $quantity  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $notes     = trim($_POST['notes'] ?? '');
    $userId    = $_SESSION['user']['id'] ?? null; // Ambil ID user yang sedang login

    if ($productId && in_array($type, ['IN', 'OUT']) && $quantity > 0 && $userId) {
        // Ambil stok produk saat ini
        $stmtStock = $pdo->prepare("SELECT stock FROM products WHERE id = :id");
        $stmtStock->execute(['id' => $productId]);
        $currentStock = $stmtStock->fetchColumn();

        // Validasi stok jika barang keluar
        if ($type === 'OUT' && $currentStock < $quantity) {
            $error = "Stok tidak mencukupi! Stok saat ini hanya: {$currentStock}";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Simpan Transaksi Stok (Termasuk user_id)
                $stmtMove = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES (:product_id, :user_id, :type, :quantity, :notes)");
                $stmtMove->execute([
                    'product_id' => $productId,
                    'user_id'    => $userId,
                    'type'       => $type,
                    'quantity'   => $quantity,
                    'notes'      => $notes
                ]);

                // 2. Update Stok di Tabel Products
                if ($type === 'IN') {
                    $stmtUpdate = $pdo->prepare("UPDATE products SET stock = stock + :qty WHERE id = :id");
                } else {
                    $stmtUpdate = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
                }
                $stmtUpdate->execute(['qty' => $quantity, 'id' => $productId]);

                $pdo->commit();
                header('Location: index.php');
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Gagal memproses transaksi: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Harap isi semua data dengan benar!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Transaksi Stok - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">

<div class="container" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0">Input Transaksi Barang Masuk / Keluar</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Produk</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?> | Stok: <?= $p['stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Transaksi</label>
                    <select name="type" class="form-select" required>
                        <option value="IN">Barang Masuk (IN) - Stok Bertambah</option>
                        <option value="OUT">Barang Keluar (OUT) - Stok Berkurang</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jumlah (Quantity)</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan / Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Pembelian supplier PT A / Penjualan toko"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>