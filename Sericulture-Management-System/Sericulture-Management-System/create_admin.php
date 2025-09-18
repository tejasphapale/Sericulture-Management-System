<?php
include "config.php"; // your DB connection

$username = "tejas";
$email = "tejas1@gmail.com";
$password = password_hash("tejas12345", PASSWORD_DEFAULT); // hashed password
$role = "admin";

try {
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role, created_at)
        VALUES (?, ?, ?, ?, now())
    ");
    $stmt->execute([$username, $email, $password, $role]);
    echo "✅ Admin 'tejas' created successfully!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}

