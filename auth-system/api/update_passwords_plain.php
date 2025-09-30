<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Koneksi database
$host = "localhost";
$db_name = "auth_system";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tambah kolom jika belum ada
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_plain VARCHAR(255) AFTER password");
    
    // Set semua password_plain ke 'default123' untuk testing
    $updateQuery = "UPDATE users SET password_plain = 'default123' WHERE password_plain IS NULL";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute();
    
    echo json_encode([
        "success" => true,
        "message" => "Kolom password_plain sudah diupdate",
        "updated_rows" => $stmt->rowCount()
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>