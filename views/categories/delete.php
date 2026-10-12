<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';

checkLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        // Mencegah error jika kategori masih terikat dengan produk (Foreign Key Constraint)
        echo "<script>alert('Gagal menghapus: Kategori ini masih memiliki produk terkait!'); window.location.href='index.php';</script>";
        exit;
    }
}

header('Location: index.php');
exit;