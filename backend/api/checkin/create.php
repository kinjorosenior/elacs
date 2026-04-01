<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");


require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"), true);

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$student_id = $data['student_id'] ?? null;
$device_serial = $data['device_serial'] ?? null;

if (!$student_id || !$device_serial) {
    echo json_encode(["message" => "student_id and device_serial required"]);
    exit;
}

// ❌ Prevent duplicate check-in
$check = $conn->prepare("
    SELECT * FROM checkins 
    WHERE device_serial = :serial 
    AND status = 'IN'
");

$check->bindParam(":serial", $device_serial);
$check->execute();

if ($check->rowCount() > 0) {
    echo json_encode(["message" => "Device already checked in"]);
    exit;
}

// ✅ Insert new check-in
$stmt = $conn->prepare("
    INSERT INTO checkins (student_id, device_serial, status, checkin_time)
    VALUES (:student_id, :serial, 'IN', NOW())
");

$stmt->bindParam(":student_id", $student_id);
$stmt->bindParam(":serial", $device_serial);
$stmt->execute();

echo json_encode(["message" => "Device checked in successfully"]);