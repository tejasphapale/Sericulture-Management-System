<?php
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$dbname = "sericulture";
$user = "postgres";
$pass = "yourpassword";  // change this

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    // insert into orders
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_name, quantity, total_amount) 
                           VALUES (:user_id, :product_name, :quantity, :total_amount)
                           RETURNING id");

    $stmt->execute([
        ":user_id" => 1,  // ✅ Replace later with logged-in user ID
        ":product_name" => $data["product"],
        ":quantity" => $data["quantity"],
        ":total_amount" => $data["total"]
    ]);

    $order_id = $stmt->fetchColumn();

    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully",
        "order_id" => $order_id
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed",
        "error" => $e->getMessage()
    ]);
}

