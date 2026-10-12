<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!empty($name)) {
        // Query disesuaikan tanpa kolom email
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, address) VALUES (:name, :phone, :address)");
        $stmt->execute([
            'name'    => $name,
            'phone'   => $phone,
            'address' => $address
        ]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nama Supplier wajib diisi!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Supplier - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">

<div class="container" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0">Tambah Supplier Baru</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Supplier</label>
                    <input type="text" name="name" class="form-control" placeholder="PT. Jaya Abadi" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">No. Telepon</label>
                    <input type="text" name="phone" class="form-control" placeholder="081234567890">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap..."></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>