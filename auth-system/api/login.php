<?php
// Enable error reporting
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

session_start();

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

$response = array();

if(!empty($data->email) && !empty($data->password)) {
    $user->email = $data->email;
    
    if($user->emailExists() && password_verify($data->password, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        
        http_response_code(200);
        $response["success"] = true;
        $response["message"] = "Login berhasil.";
        $response["user"] = array(
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email
        );
    } else {
        http_response_code(401);
        $response["success"] = false;
        $response["message"] = "Email atau password salah.";
    }
} else {
    http_response_code(400);
    $response["success"] = false;
    $response["message"] = "Email dan password harus diisi.";
}

echo json_encode($response);
?>