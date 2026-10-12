<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';

checkLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus: Produk ini sudah memiliki histori transaksi!'); window.location.href='index.php';</script>";
        exit;
    }
}

header('Location: index.php');
exit;