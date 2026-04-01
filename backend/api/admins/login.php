<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight (VERY IMPORTANT)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(["error" => "Email and password required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email AND status='active'");
$stmt->bindParam(":email", $email);
$stmt->execute();

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode(["error" => "Invalid email or inactive account"]);
    exit;
}

if ($admin['password'] !== $password) {
    echo json_encode(["error" => "Invalid password"]);
    exit;
}

$token = base64_encode($admin['id'] . ":" . time());

echo json_encode([
    "token" => $token,
    "full_name" => $admin['full_name'],
    "role" => $admin['role'],
    "id" => $admin['id']
]);