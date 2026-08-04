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
    $student_lookup = trim((string)($data['student_id'] ?? $data['id'] ?? ''));
    $device_serial_number = trim((string)($data['serial_number'] ?? ''));
    $admin_id = (int)($data['admin_id'] ?? $data['librarian_id'] ?? 1);

    // Resolve a valid admin_id (the provided one may not exist)
    if (!$admin_id) {
        throw new Exception("admin_id required");
    }

    $adminCheck = $conn->prepare("SELECT id FROM admins WHERE id = :id LIMIT 1");
    $adminCheck->bindParam(":id", $admin_id);
    $adminCheck->execute();
    if (!$adminCheck->fetch()) {
        // Fall back to any active admin
        $anyAdmin = $conn->prepare("SELECT id FROM admins WHERE status = 'active' ORDER BY id LIMIT 1");
        $anyAdmin->execute();
        $fallback = $anyAdmin->fetch(PDO::FETCH_ASSOC);
        if ($fallback) {
            $admin_id = (int)$fallback['id'];
        } else {
            // No admins exist at all - insert a system admin
            $conn->prepare("INSERT IGNORE INTO admins (id, library_id, full_name, email, password, role, status) VALUES (1, 1, 'System Admin', 'admin@kabarak.ac.ke', 'admin123', 'admin', 'active')")->execute();
            $admin_id = 1;
        }
    }

    if (!$device_serial_number) {
        throw new Exception("serial_number required");
    }

    $conn->beginTransaction();

    // Resolve device first (source of truth for ownership)
    $deviceStmt = $conn->prepare("
        SELECT
            d.id,
            d.library_id,
            d.student_id,
            d.serial_number,
            s.student_id AS mapped_student_id
        FROM devices d
        LEFT JOIN students s
            ON (s.student_id = d.student_id OR CAST(s.id AS CHAR) = CAST(d.student_id AS CHAR))
        WHERE d.serial_number = :serial
        LIMIT 1
    ");
    $deviceStmt->bindParam(":serial", $device_serial_number);
    $deviceStmt->execute();
    $device = $deviceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        throw new Exception("Device not found: " . $device_serial_number);
    }

    $resolved_student_id = $device['mapped_student_id'] ?? null;

    // Fall back to device.student_id when it already stores a human-readable student_id
    if (empty($resolved_student_id) && !empty($device['student_id']) && !is_numeric((string)$device['student_id'])) {
        $resolved_student_id = (string)$device['student_id'];
    }

    // Validate incoming student identifier (if provided) using BOTH id and student_id
    if ($student_lookup !== '') {
        $studentStmt = $conn->prepare("SELECT id, student_id FROM students WHERE student_id = :lookup OR CAST(id AS CHAR) = :lookup LIMIT 1");
        $studentStmt->bindParam(":lookup", $student_lookup);
        $studentStmt->execute();
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception("Student not found: " . $student_lookup);
        }

        if (!empty($resolved_student_id) && (string)$student['student_id'] !== (string)$resolved_student_id) {
            throw new Exception("Selected student does not match device owner");
        }

        // Keep checkins.student_id aligned with students.student_id (VARCHAR FK)
        if (empty($resolved_student_id)) {
            $resolved_student_id = (string)$student['student_id'];
        }
    }

if (empty($resolved_student_id)) {
        throw new Exception("Unable to resolve student for this device");
    }

    // Resolve a valid library_id (device.library_id may be null)
    $library_id = $device['library_id'] ?? null;
    if (empty($library_id)) {
        $libCheck = $conn->prepare("SELECT id FROM libraries ORDER BY id LIMIT 1");
        $libCheck->execute();
        $lib = $libCheck->fetch(PDO::FETCH_ASSOC);
        $library_id = $lib ? (int)$lib['id'] : 1;
    }

    // Prevent duplicate check-in (latest event for serial cannot already be IN)
    $checkStmt = $conn->prepare("
        SELECT status
        FROM checkins
        WHERE serial_number = :serial
        ORDER BY checkin_time DESC, id DESC
        LIMIT 1
    ");
    $checkStmt->bindParam(":serial", $device_serial_number);
    $checkStmt->execute();
    $latest = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if ($latest && $latest['status'] === 'IN') {
        throw new Exception("Device already checked in");
    }

    // Insert new check-in
    $insertStmt = $conn->prepare("
        INSERT INTO checkins (library_id, student_id, serial_number, admin_id, status, checkin_time)
        VALUES (:library_id, :student_id, :serial_number, :admin_id, 'IN', NOW())
    ");
$insertStmt->bindParam(":library_id", $library_id);
    $insertStmt->bindParam(":student_id", $resolved_student_id);
    $insertStmt->bindParam(":serial_number", $device_serial_number);
    $insertStmt->bindParam(":admin_id", $admin_id);
    
    if ($insertStmt->execute()) {
        // Keep devices table in sync for any views still using devices.status
        $updateDevice = $conn->prepare("UPDATE devices SET status = 'inside' WHERE serial_number = :serial");
        $updateDevice->bindParam(":serial", $device_serial_number);
        $updateDevice->execute();

        $conn->commit();
        echo json_encode([
            "message" => "Device checked in successfully",
            "status" => "IN"
        ]);
    } else {
        throw new Exception("Failed to insert check-in record");
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>



