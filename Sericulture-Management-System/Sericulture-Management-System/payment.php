<?php

header("Content-Type: application/json");
include "config.php"; // PDO connection

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No data received"]);
    exit;
}

// For debugging, show received data
// file_put_contents("debug.log", print_r($data, true), FILE_APPEND);

try {
    $stmt = $conn->prepare("
        INSERT INTO payments
        (order_id, amount, status, paid_at, username, mobile, product_name, city, payment_method)
        VALUES
        (:order_id, :amount, :status, NOW(), :username, :mobile, :product_name, :city, :payment_method)
    ");

    $stmt->execute([
        ":order_id"      => $data["order_id"],
        ":amount"        => $data["amount"],
        ":status"        => $data["status"] ?? "pending",
        ":username"      => $data["username"] ?? "Guest",
        ":mobile"        => $data["mobile"] ?? "0000000000",
        ":product_name"  => $data["product_name"] ?? "Unknown",
        ":city"          => $data["city"] ?? "Unknown",
        ":payment_method"=> $data["payment_method"] ?? "COD"
    ]);

    echo json_encode(["success" => true, "message" => "Payment saved"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>

