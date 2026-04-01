<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");

try {
    require_once "../../config/database.php";
    $db = new Database();
    $conn = $db->connect();

    $q = $_GET['q'] ?? '';

    // Simple search without subquery to avoid column issues
    $stmt = $conn->prepare("
      SELECT d.*, COALESCE(s.full_name, 'Unassigned') as student_name
      FROM devices d
      LEFT JOIN students s ON d.student_id = s.student_id
      WHERE d.serial_number LIKE ? OR d.model LIKE ? OR s.full_name LIKE ?
    ");

    $search = "%{$q}%";
    $stmt->execute([$search, $search, $search]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data ?: []);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>

