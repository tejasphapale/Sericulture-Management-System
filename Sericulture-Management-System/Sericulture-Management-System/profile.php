<?php
session_start();
include "config.php";

// If not logged in → redirect to login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

// Fetch user details
$sql = "SELECT username, email, role, created_at FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([":id" => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
  <div class="container">
    <h2>👤 Welcome, <?= htmlspecialchars($user['username']) ?></h2>
    <table class="table table-bordered mt-3">
      <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
      <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
      <tr><th>Role</th><td><?= htmlspecialchars($user['role']) ?></td></tr>
      <tr><th>Joined</th><td><?= htmlspecialchars($user['created_at']) ?></td></tr>
    </table>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
</body>
</html>

