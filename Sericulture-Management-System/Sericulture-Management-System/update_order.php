<?php
session_start();
include "config.php";

// ✅ Only admin can update
if (!isset($_SESSION["username"]) || $_SESSION["role"] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orderId = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($orderId && $action) {
        // Decide status
        if ($action === "complete") {
            $status = "completed";
        } elseif ($action === "cancel") {
            $status = "cancelled";
        } else {
            echo "error";
            exit;
        }

        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([
            ":status" => $status,
            ":id" => $orderId
        ]);

        echo "success";
        exit;
    }
}

echo "error";

