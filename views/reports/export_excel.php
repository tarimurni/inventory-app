<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';
require_once '../../app/Helpers/escape.php';

checkLogin();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

// Header HTTP untuk mengunduh file sebagai Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Inventory_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Data Ringkasan Stok Produk
$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC")->fetchAll();

// Data Mutasi Transaksi Stok
$stmtTrans = $pdo->prepare("SELECT sm.*, p.name AS product_name, p.sku FROM stock_movements sm JOIN products p ON p.id = sm.product_id WHERE DATE(sm.created_at) BETWEEN :start_date AND :end_date ORDER BY sm.created_at DESC");
$stmtTrans->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$transactions = $stmtTrans->fetchAll();
?>

<h3>LAPORAN SISTEM INVENTARIS BARANG</h3>
<p>Periode Mutasi: <b><?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></b></p>
<p>Tanggal Unduh: <?= date('d/m/Y H:i:s') ?></p>

<br>
<h4>1. Ringkasan Stok Produk</h4>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #212529; color: #ffffff;">
            <th>No</th>
            <th>SKU</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga Beli (Rp)</th>
            <th>Harga Jual (Rp)</th>
            <th>Stok Tersisa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($p['sku']) ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name'] ?? 'Tanpa Kategori') ?></td>
                <td><?= $p['purchase_price'] ?? $p['price'] ?? 0 ?></td>
                <td><?= $p['selling_price'] ?? $p['price'] ?? 0 ?></td>
                <td><?= $p['stock'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br><br>
<h4>2. Mutasi Transaksi Stok</h4>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #212529; color: #ffffff;">
            <th>No</th>
            <th>Waktu Transaksi</th>
            <th>SKU</th>
            <th>Nama Produk</th>
            <th>Tipe Transaksi</th>
            <th>Jumlah (Qty)</th>
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
                <td colspan="7">Tidak ada transaksi pada periode ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>