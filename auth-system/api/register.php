<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

$response = array();

if(!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    
    $user->name = $data->name;
    $user->email = $data->email;
    
    // UNTUK DEVELOPMENT: Simpan password plain text di kolom terpisah
    $plain_password = $data->password; // Simpan password asli
    $user->password = password_hash($data->password, PASSWORD_DEFAULT);

    // MODIFIED QUERY: tambah kolom password_plain
    $query = "INSERT INTO users 
              SET name=:name, email=:email, password=:password, password_plain=:password_plain, created_at=NOW()";

    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":name", $user->name);
    $stmt->bindParam(":email", $user->email);
    $stmt->bindParam(":password", $user->password);
    $stmt->bindParam(":password_plain", $plain_password);

    if($stmt->execute()) {
        http_response_code(201);
        $response["success"] = true;
        $response["message"] = "User berhasil dibuat.";
        $response["debug"] = [
            "plain_password" => $plain_password,
            "hashed_password" => $user->password
        ];
    } else {
        http_response_code(400);
        $response["success"] = false;
        $response["message"] = "Gagal membuat user.";
    }
} else {
    http_response_code(400);
    $response["success"] = false;
    $response["message"] = "Data tidak lengkap.";
}

echo json_encode($response);
?>