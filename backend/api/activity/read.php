<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("
  SELECT 
    c.*, 
    s.full_name, 
    s.student_id,
    d.model,
    d.device_type
  FROM checkins c
  LEFT JOIN students s ON c.student_id = s.student_id
  JOIN devices d ON c.serial_number = d.serial_number
  ORDER BY c.checkin_time DESC 
  LIMIT 10
");

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as &$row) {
  $row['status'] = $row['status'] === 'IN' ? 'checkin' : 'checkout';
  $row['checkin_time'] = date('M j, Y H:i', strtotime($row['checkin_time']));
}

echo json_encode($data ?: []);


