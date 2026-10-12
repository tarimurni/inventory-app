<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

// Query Products
$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC")->fetchAll();

// Query Transaksi
$stmtTrans = $pdo->prepare("SELECT sm.*, p.name AS product_name, p.sku FROM stock_movements sm JOIN products p ON p.id = sm.product_id WHERE DATE(sm.created_at) BETWEEN :start_date AND :end_date ORDER BY sm.created_at DESC");
$stmtTrans->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$transactions = $stmtTrans->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4 bg-white" onload="window.print()">

<div class="text-center mb-4 border-bottom pb-3">
    <h2>LAPORAN SISTEM INVENTARIS BARANG</h2>
    <p class="mb-0">Periode Mutasi: <strong><?= date('d/m/Y', strtotime($startDate)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($endDate)) ?></strong></p>
    <small class="text-muted">Tanggal Cetak: <?= date('d/m/Y H:i:s') ?></small>
</div>

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Cetak / Simpan PDF</button>
</div>

<h5 class="fw-bold">1. Ringkasan Stok Produk Saat Ini</h5>
<table class="table table-bordered align-middle mb-4">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>SKU</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Stok Tersisa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><code><?= e($p['sku']) ?></code></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name'] ?? 'Tanpa Kategori') ?></td>
                <td>Rp <?= number_format($p['purchase_price'] ?? $p['price'] ?? 0, 0, ',', '.') ?></td>
                <td>Rp <?= number_format($p['selling_price'] ?? $p['price'] ?? 0, 0, ',', '.') ?></td>
                <td><?= $p['stock'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h5 class="fw-bold">2. Riwayat Mutasi Transaksi Stok</h5>
<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Waktu</th>
            <th>SKU</th>
            <th>Nama Produk</th>
            <th>Tipe</th>
            <th>Jumlah</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td><?= e($t['sku']) ?></td>
                    <td><?= e($t['product_name']) ?></td>
                    <td><?= $t['type'] ?></td>
                    <td><?= $t['quantity'] ?></td>
                    <td><?= e($t['notes'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center text-muted">Tidak ada transaksi pada periode ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>