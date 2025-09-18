<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get username and password from form
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

    if (!$username || !$password) {
        echo "<script>alert('❌ Please enter username and password'); window.location='login.html';</script>";
        exit;
    }

    try {
        // Check username in database
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Login successful → store session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            // Redirect based on role
            if ($user["role"] === "admin") {
                header("Location: drser.php"); // admin page
                exit;
            } else {
                header("Location: demo.html"); // user page
                exit;
            }
        } else {
            echo "<script>alert('❌ Invalid username or password'); window.location='login.html';</script>";
        }

    } catch (PDOException $e) {
        echo "<script>alert('❌ Database error: " . $e->getMessage() . "'); window.location='login.html';</script>";
    }
}
?>

