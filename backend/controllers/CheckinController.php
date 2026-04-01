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
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['serial_number']) || !isset($data['librarian_id'])) {
            echo json_encode(["error" => "Serial number and librarian ID required"]);
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

        $deviceData = $this->device->findBySerial($data['serial_number']);

        if (!$deviceData) {
            echo json_encode(["error" => "Device not found"]);
            return;
        }

        if ($deviceData['status'] === 'inside') {
            echo json_encode(["error" => "Device already checked in"]);
            return;
        }

        $studentData = $this->student->findById($deviceData['student_id']);

        if (!$studentData) {
            echo json_encode(["error" => "Student not found"]);
            return;
        }

        if ($studentData['trust_score'] < 30) {
            echo json_encode(["error" => "Student flagged as high risk"]);
            return;
        }

        $this->checkin->create([
            'device_id' => $deviceData['id'],
            'student_id' => $deviceData['student_id'],
            'library_id' => $deviceData['library_id'],
            'librarian_id' => $data['librarian_id'],
            'status' => 'inside'
        ]);

        $this->device->updateStatus($deviceData['id'], 'inside');

        echo json_encode(["message" => "Check-in successful"]);
    }

    // ==============================
    // CHECK-OUT
    // ==============================
    public function checkOut()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['serial_number'])) {
            echo json_encode(["error" => "Serial number required"]);
            return;
        }

        $deviceData = $this->device->findBySerial($data['serial_number']);

        if (!$deviceData) {
            echo json_encode(["error" => "Device not found"]);
            return;
        }

        if ($deviceData['status'] !== 'inside') {
            echo json_encode(["error" => "Device is not currently inside"]);
            return;
        }

        $activeCheckin = $this->checkin->getActiveCheckin($deviceData['id']);

        if (!$activeCheckin) {
            echo json_encode(["error" => "No active check-in record"]);
            return;
        }

        $this->checkin->closeCheckin($activeCheckin['id']);
        $this->device->updateStatus($deviceData['id'], 'registered');

        // ⏱ Calculate duration
        $duration = $this->checkin->calculateDuration($activeCheckin['id']);
        $minutesSpent = $duration['minutes_spent'];

        // 🚨 Penalize if stayed more than 10 hours
        $userData = $this->user->findById($deviceData['student_id']);

        if ($minutesSpent > 600) {
            $newScore = $userData['trust_score'] - 10;
            $this->user->updateTrustScore($userData['id'], $newScore);
        }

        echo json_encode([
            "message" => "Check-out successful",
            "minutes_spent" => $minutesSpent
        ]);
    }
}