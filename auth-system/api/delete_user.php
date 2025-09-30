<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize response array
$response = array();

try {
    // Get the raw POST data
    $input = file_get_contents("php://input");
    
    if (empty($input)) {
        throw new Exception("No data received");
    }

    // Decode JSON data
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    // Validate input
    if (!$data || !isset($data['id'])) {
        throw new Exception("User ID is required");
    }

    $userId = intval($data['id']);

    if ($userId <= 0) {
        throw new Exception("Invalid User ID: " . $data['id']);
    }

    // Database configuration
    $host = "localhost";
    $db_name = "auth_system";
    $username = "root";
    $password = "";

    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    // Check if user exists first
    $checkQuery = "SELECT id FROM users WHERE id = :id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("User with ID $userId not found");
    }

    // Prepare delete query
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

    // Execute query
    if ($stmt->execute()) {
        $affectedRows = $stmt->rowCount();
        
        if ($affectedRows > 0) {
            $response = [
                "success" => true,
                "message" => "User berhasil dihapus",
                "deleted_id" => $userId,
                "affected_rows" => $affectedRows
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "Tidak ada user yang terhapus"
            ];
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Database error: " . $errorInfo[2]);
    }

} catch (PDOException $e) {
    $response = [
        "success" => false,
        "message" => "Database connection error: " . $e->getMessage(),
        "error_code" => $e->getCode()
    ];
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage()
    ];
}

// Ensure we output valid JSON
echo json_encode($response, JSON_PRETTY_PRINT);
exit();
?>