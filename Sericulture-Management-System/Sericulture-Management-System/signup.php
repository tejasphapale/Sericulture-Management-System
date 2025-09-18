<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email, $password]);

        echo "<script>alert('✅ Signup successful! Please login.'); window.location='login.html';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('❌ Error: " . $e->getMessage() . "'); window.location='sign.html';</script>";
    }
}
?>

