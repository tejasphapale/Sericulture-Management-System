<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

    if (!$username || !$password) {
        echo "<script>alert('❌ Please enter username and password'); window.location='admin_login.html';</script>";
        exit;
    }

    try {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['role'] === 'admin' && password_verify($password, $user["password"])) {
            // Store session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            header("Location: admin_orders.php"); // admin orders page
            exit;
        } else {
            echo "<script>alert('❌ Invalid admin username or password'); window.location='admin_login.html';</script>";
        }

    } catch (PDOException $e) {
        echo "<script>alert('❌ Database error: " . $e->getMessage() . "'); window.location='admin_login.html';</script>";
    }
}
?>

