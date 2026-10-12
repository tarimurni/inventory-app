<?php
require_once '../../config/database.php';
require_once '../../app/Helpers/auth.php';

checkLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: index.php');
exit;