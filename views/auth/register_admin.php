<?php
require_once '../../config/database.php';

$username = 'admin';
$password = 'admin123';
$role     = 'admin';

// Hash password dengan algoritma bawaan PHP
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Hapus user admin lama jika ada
    $stmt = $pdo->prepare('DELETE FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);

    // Insert user admin baru dengan hash yang valid
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
    $stmt->execute([
        'username' => $username,
        'password' => $hashedPassword,
        'role'     => $role
    ]);

    echo "<h2 style='color: green;'>Akun Admin Berhasil Dibuat/Diperbarui!</h2>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>admin123</strong></p>";
    echo "<a href='login.php'>Klik di sini untuk Login</a>";
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Gagal: " . $e->getMessage() . "</h2>";
}