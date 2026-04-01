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

// Deactivate account (status = inactive)
$stmt = $conn->prepare("UPDATE admins SET status = 'inactive' WHERE id = :id");
$stmt->bindParam(":id", $adminId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to deactivate account"]);
}