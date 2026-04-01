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

    $data = json_decode(file_get_contents("php://input"), true);
    $serial = $data['device_serial'] ?? null;

    if (!$serial) {
        echo json_encode(["message" => "Serial required"]);
        exit;
    }

    $check = $conn->prepare("
        SELECT * FROM checkins
        WHERE device_serial = ?
        AND status = 'IN'
        ORDER BY checkin_time DESC
        LIMIT 1
    ");
    $check->execute([$serial]);
    $current = $check->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        echo json_encode(["message" => "Device is not checked in"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE checkins
        SET status = 'OUT',
            checkout_time = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$current['id']]);

    echo json_encode(["message" => "Device checked out successfully"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}

