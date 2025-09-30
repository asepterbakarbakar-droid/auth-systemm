<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db) {
    echo json_encode([
        "success" => true,
        "message" => "Database connected successfully",
        "database" => "auth_system"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
}
?>