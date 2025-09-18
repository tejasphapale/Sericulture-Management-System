<?php
// save_order.php
session_start();

// Connect to PostgreSQL
$dsn = "pgsql:host=localhost;port=5432;dbname=sericulture;";
$user = "tejas";   // your postgres role
$pass = "your_password";  // replace with your password

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("❌ DB Connection failed: " . $e->getMessage());
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

try {
    // Save into orders table
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, mobile, address, product, quantity, price, total, payment_method) 
                           VALUES (:name, :mobile, :address, :product, :quantity, :price, :total, :payment)");

    $stmt->execute([
        ":name"     => $data["name"],
        ":mobile"   => $data["mobile"],
        ":address"  => $data["address"],
        ":product"  => $data["product"],
        ":quantity" => $data["quantity"],
        ":price"    => $data["price"],
        ":total"    => $data["total"],
        ":payment"  => $data["payment_method"] ?? "Pending"
    ]);

    echo json_encode(["status" => "success", "message" => "Order saved successfully!"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

