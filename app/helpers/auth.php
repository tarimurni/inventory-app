<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: /inventory-app/views/auth/login.php');
        exit;
    }
}

function checkAdmin() {
    checkLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        exit('Akses Ditolak: Halaman ini hanya untuk Admin.');
    }
}