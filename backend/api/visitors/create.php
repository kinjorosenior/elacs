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

$stmt = $conn->prepare("INSERT INTO visitors (name, phone, purpose, visit_time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        $data['name'],
        $data['phone'],
        $data['purpose']
    ]);

    echo json_encode(["message" => "Visitor signed in", "id" => $conn->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>

