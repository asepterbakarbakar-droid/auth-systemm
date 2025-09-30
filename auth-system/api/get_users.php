<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize response array
$response = array();

try {
    // Database configuration
    $host = "localhost";
    $db_name = "auth_system";
    $username = "root";
    $password = "";

    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get all users
    $query = "SELECT id, name, email, password, created_at FROM users ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        "success" => true,
        "users" => $users,
        "total" => count($users),
        "timestamp" => date('Y-m-d H:i:s')
    ];
    
} catch (PDOException $e) {
    // Fallback sample data if database fails
    $sampleUsers = [
        [
            "id" => 1,
            "name" => "Admin User", 
            "email" => "admin@example.com",
            "password" => "hashed_password_123",
            "created_at" => date('Y-m-d H:i:s')
        ],
        [
            "id" => 2,
            "name" => "Test User",
            "email" => "test@example.com",
            "password" => "hashed_password_456", 
            "created_at" => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
    
    $response = [
        "success" => true,
        "users" => $sampleUsers,
        "total" => count($sampleUsers),
        "note" => "Using sample data - Database connection failed: " . $e->getMessage()
    ];
} catch (Exception $e) {
    $response = [
        "success" => false,
        "error" => $e->getMessage()
    ];
}

// Ensure valid JSON output
echo json_encode($response, JSON_PRETTY_PRINT);
exit();
?>