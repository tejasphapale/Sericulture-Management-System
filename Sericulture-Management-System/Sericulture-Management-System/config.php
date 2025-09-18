<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'sericulture';
$user = 'postgres';       // PostgreSQL username
$pass = 'yourpassword';   // PostgreSQL password
$port = '5432';

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

