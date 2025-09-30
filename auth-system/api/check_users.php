<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT * FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "total_users" => count($users),
        "users" => $users
    ]);
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>