<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once "../../config/database.php";
    $db = new Database();
    $conn = $db->connect();

    $data = json_decode(file_get_contents("php://input"), true) ?: [];
    $serial = trim((string)($data['serial_number'] ?? ''));
    $admin_id = (int)($data['admin_id'] ?? $data['librarian_id'] ?? 1);

    if (!$serial) {
        echo json_encode(["message" => "Serial required"]);
        exit;
    }

    $conn->beginTransaction();

    // Latest event for this serial must be IN before we can check out
    $check = $conn->prepare("
        SELECT id, library_id, student_id, status
        FROM checkins
        WHERE serial_number = ?
        ORDER BY checkin_time DESC
        , id DESC
        LIMIT 1
    ");
    $check->execute([$serial]);
    $current = $check->fetch(PDO::FETCH_ASSOC);

    if (!$current || $current['status'] !== 'IN') {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(["message" => "Device is not checked in"]);
        return;
    }

    // Insert OUT event (new schema keeps one timestamp per event)
    $insert = $conn->prepare("
        INSERT INTO checkins (library_id, student_id, serial_number, admin_id, status, checkin_time)
        VALUES (?, ?, ?, ?, 'OUT', NOW())
    ");
    $insert->execute([
        $current['library_id'],
        $current['student_id'],
        $serial,
        $admin_id
    ]);

    // Keep legacy device status views in sync
    $device = $conn->prepare("UPDATE devices SET status = 'registered' WHERE serial_number = ?");
    $device->execute([$serial]);

    $conn->commit();

    echo json_encode([
        "message" => "Device checked out successfully",
        "status" => "OUT"
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}



