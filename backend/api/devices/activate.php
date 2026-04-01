<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    require_once "../config/database.php";
    $db = new Database();
    $conn = $db->connect();

    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;

    $stmt = $conn->prepare("UPDATE devices SET status = 'active' WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["message" => "Device activated"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>

