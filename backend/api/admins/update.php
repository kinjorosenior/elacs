<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!$token) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$decoded = explode(':', base64_decode($token));
$adminId = $decoded[0] ?? null;

if (!$adminId) {
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data['full_name'] ?? '');
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? ''); // optional

if (!$full_name || !$email) {
    echo json_encode(["error" => "Name and email are required"]);
    exit;
}

// Update query
if ($password) {
    $stmt = $conn->prepare("
        UPDATE admins 
        SET full_name = :full_name, email = :email, password = :password 
        WHERE id = :id
    ");
    $stmt->bindParam(":password", $password); // plain text for now
} else {
    $stmt = $conn->prepare("
        UPDATE admins 
        SET full_name = :full_name, email = :email
        WHERE id = :id
    ");
}

$stmt->bindParam(":full_name", $full_name);
$stmt->bindParam(":email", $email);
$stmt->bindParam(":id", $adminId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update profile"]);
}