<?php

class SuspiciousLog {

    private $conn;
    private $table = "suspicious_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($library_id, $student_id, $device_id, $event_type, $risk_level, $description) {

        $query = "INSERT INTO " . $this->table . "
        (library_id, student_id, device_id, event_type, risk_level, description)
        VALUES
        (:library_id, :student_id, :device_id, :event_type, :risk_level, :description)";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ":library_id" => $library_id,
            ":student_id" => $student_id,
            ":device_id" => $device_id,
            ":event_type" => $event_type,
            ":risk_level" => $risk_level,
            ":description" => $description
        ]);
    }
}