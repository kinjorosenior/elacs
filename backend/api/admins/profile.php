<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

// Get token from Authorization header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!$token) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Decode token (simple base64 token used in login.php)
$decoded = explode(':', base64_decode($token));
$adminId = $decoded[0] ?? null;

if (!$adminId) {
    echo json_encode(["error" => "Invalid token"]);
    exit;
}

// Fetch admin data
$stmt = $conn->prepare("SELECT id, full_name, email, role, status FROM admins WHERE id = :id");
$stmt->bindParam(":id", $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode(["error" => "Admin not found"]);
    exit;
}

echo json_encode($admin);