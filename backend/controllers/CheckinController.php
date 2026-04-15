<?php

require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Checkin.php';

class CheckinController
{
    private $device;
    private $student;
    private $checkin;

    public function __construct($db)
    {
        $this->device = new Device($db);
        $this->student = new Student($db);
        $this->checkin = new Checkin($db);
    }

    // ==============================
    // CHECK-IN
    // ==============================
    public function checkIn()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        $serial = trim((string)($data['serial_number'] ?? ''));
        $adminId = (int)($data['admin_id'] ?? $data['librarian_id'] ?? 1);

        if ($serial === '') {
            echo json_encode(["error" => "Serial number required"]);
            return;
        }

        // 🔒 Enforce Library Hours
        $currentTime = date("H:i");
        if ($currentTime < "08:00" || $currentTime > "22:00") {
            echo json_encode([
                "error" => "Library is closed (08:00 - 22:00)"
            ]);
            return;
        }

        $deviceData = $this->device->findBySerial($serial);

        if (!$deviceData) {
            echo json_encode(["error" => "Device not found"]);
            return;
        }

        if ($deviceData['status'] === 'inside') {
            echo json_encode(["error" => "Device already checked in"]);
            return;
        }

        $studentData = $this->student->findByStudentId((string)$deviceData['student_id']);
        if (!$studentData && is_numeric((string)$deviceData['student_id'])) {
            $studentData = $this->student->findById((int)$deviceData['student_id']);
        }

        if (!$studentData) {
            echo json_encode(["error" => "Student not found"]);
            return;
        }

        if ($studentData['trust_score'] < 30) {
            echo json_encode(["error" => "Student flagged as high risk"]);
            return;
        }

        $activeCheckin = $this->checkin->getActiveCheckin($serial);
        if ($activeCheckin) {
            echo json_encode(["error" => "Device already checked in"]);
            return;
        }

        $this->checkin->create([
            'library_id' => $deviceData['library_id'] ?? 1,
            'student_id' => $studentData['student_id'],
            'serial_number' => $serial,
            'admin_id' => $adminId
        ]);

        $this->device->updateStatus($deviceData['id'], 'inside');

        echo json_encode(["message" => "Check-in successful"]);
    }

    // ==============================
    // CHECK-OUT
    // ==============================
    public function checkOut()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        $serial = trim((string)($data['serial_number'] ?? ''));
        $adminId = (int)($data['admin_id'] ?? $data['librarian_id'] ?? 1);

        if ($serial === '') {
            echo json_encode(["error" => "Serial number required"]);
            return;
        }

        $deviceData = $this->device->findBySerial($serial);

        if (!$deviceData) {
            echo json_encode(["error" => "Device not found"]);
            return;
        }

        $activeCheckin = $this->checkin->getActiveCheckin($serial);

        if (!$activeCheckin) {
            echo json_encode(["error" => "No active check-in record"]);
            return;
        }

        $this->checkin->closeCheckin($activeCheckin['id'], $adminId);
        $this->device->updateStatus($deviceData['id'], 'registered');

        // ⏱ Calculate duration
        $duration = $this->checkin->calculateDuration($activeCheckin['id']);
        $minutesSpent = $duration['minutes_spent'];

        // 🚨 Penalize if stayed more than 10 hours
        $studentData = $this->student->findByStudentId((string)$activeCheckin['student_id']);
        if (!$studentData && is_numeric((string)$activeCheckin['student_id'])) {
            $studentData = $this->student->findById((int)$activeCheckin['student_id']);
        }

        if ($minutesSpent > 600 && $studentData) {
            $newScore = max(0, ((int)$studentData['trust_score']) - 10);
            $this->student->updateTrustScore($studentData['id'], $newScore);
        }

        echo json_encode([
            "message" => "Check-out successful",
            "minutes_spent" => $minutesSpent
        ]);
    }
}