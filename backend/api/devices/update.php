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

  $stmt = $conn->prepare("UPDATE devices SET student_id = ?, model = ?, device_type = ?, color = ?, marks = ? WHERE id = ?");
  $success = $stmt->execute([
    $data['student_id'],
    $data['model'] ?? '',
    $data['device_type'] ?? '',
    $data['color'] ?? '',
    $data['marks'] ?? '',
    $id
  ]);

  echo json_encode($success ? ["message" => "Device updated"] : ["error" => "Failed to update"]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>

