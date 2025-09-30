<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

$response = array();

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $response["authenticated"] = true;
    $response["user"] = array(
        "id" => $_SESSION['user_id'],
        "name" => $_SESSION['user_name'],
        "email" => $_SESSION['user_email']
    );
} else {
    $response["authenticated"] = false;
}

echo json_encode($response);
?>