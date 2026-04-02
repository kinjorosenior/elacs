<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../../config/database.php";

try {
    $db = new Database();
    $conn = $db->connect();

    $data = json_decode(file_get_contents("php://input"), true) ?: [];
    $student_id = $data['id'] ?? null;
    $device_serial = $data['device_serial'] ?? null;

    if (!$student_id || !$device_serial) {
        throw new Exception("student_id and device_serial required");
    }

    // Validate student exists
    $studentStmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = :student_id");
    $studentStmt->bindParam(":student_id", $student_id);
    $studentStmt->execute();
    if (!$studentStmt->fetch()) {
        throw new Exception("Student not found: " . $student_id);
    }

    // Validate device exists
    $deviceStmt = $conn->prepare("SELECT serial_number FROM devices WHERE serial_number = :serial");
    $deviceStmt->bindParam(":serial", $device_serial);
    $deviceStmt->execute();
    if (!$deviceStmt->fetch()) {
        throw new Exception("Device not found: " . $device_serial);
    }

    // Prevent duplicate check-in
    $checkStmt = $conn->prepare("
        SELECT id FROM checkins 
        WHERE device_serial = :serial AND status = 'IN'
        ORDER BY checkin_time DESC LIMIT 1
    ");
    $checkStmt->bindParam(":serial", $device_serial);
    $checkStmt->execute();
    if ($checkStmt->fetch()) {
        throw new Exception("Device already checked in");
    }

    // Insert new check-in
    $insertStmt = $conn->prepare("
        INSERT INTO checkins (student_id, device_serial, status, checkin_time)
        VALUES (:student_id, :serial, 'IN', NOW())
    ");
    $insertStmt->bindParam(":student_id", $student_id);
    $insertStmt->bindParam(":serial", $device_serial);
    
    if ($insertStmt->execute()) {
        echo json_encode(["message" => "Device checked in successfully"]);
    } else {
        throw new Exception("Failed to insert check-in record");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>

