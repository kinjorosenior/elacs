<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  exit(0);
}

try {
    require_once "../../config/database.php";
    $db = new Database();
    $conn = $db->connect();

    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;

    $stmt = $conn->prepare("UPDATE visitors SET status = 'OUT', leave_time = NOW() WHERE id = ? AND status = 'IN'");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["message" => "Visitor signed out"]);
    } else {
        echo json_encode(["error" => "Visitor not active or not found"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>

