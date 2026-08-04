<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$student_id = $_GET['student_id'] ?? $_GET['id'] ?? null;

if (!$student_id) {
    echo json_encode([]);
    exit;
}

try {

    // dev.student_id may store either the numeric students.id OR the
    // human-readable student_id code (e.g. KAB001). Resolve both.
    $stmt = $conn->prepare("
        SELECT 
            d.serial_number,
            d.model,
            COALESCE(
                (
                    SELECT c.status
                    FROM checkins c
                    WHERE BINARY c.serial_number = BINARY d.serial_number
                    ORDER BY c.checkin_time DESC
                    LIMIT 1
                ),
                'OUT'
            ) as current_status
        FROM devices d
        WHERE d.student_id = :v1
           OR d.student_id = (
                SELECT CAST(id AS CHAR)
                FROM students
                WHERE student_id = :v2
                LIMIT 1
           )
           OR d.student_id = (
                SELECT id
                FROM students
                WHERE student_id = :v3
                LIMIT 1
           )
    ");

    $student_id = trim($student_id);
    $stmt->bindParam(":v1", $student_id);
    $stmt->bindParam(":v2", $student_id);
    $stmt->bindParam(":v3", $student_id);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data ?: []);

} catch (Exception $e) {

    echo json_encode([
        "error" => "Server error",
        "message" => $e->getMessage()
    ]);
}
