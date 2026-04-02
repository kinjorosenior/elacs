<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    echo json_encode([]);
    exit;
}

try {

    $stmt = $conn->prepare("
        SELECT 
            d.serial_number,
            d.model,
            COALESCE(
                (
                    SELECT c.status
                    FROM checkins c
                    WHERE BINARY c.device_serial = BINARY d.serial_number
                    ORDER BY c.checkin_time DESC
                    LIMIT 1
                ),
                'OUT'
            ) as current_status
        FROM devices d
        WHERE d.student_id = :student_id
    ");

    $stmt->bindParam(":student_id", $student_id);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data ?: []);

} catch (Exception $e) {

    echo json_encode([
        "error" => "Server error",
        "message" => $e->getMessage()
    ]);
}