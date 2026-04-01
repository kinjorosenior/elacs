<?php

class ActivityController {

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

public function getRecent(){

    $stmt = $this->conn->query("
        SELECT 
            s.full_name,
            d.device_type,
            d.model,
            c.status,
            c.checkin_time,
            c.checkout_time
        FROM checkins c
        JOIN students s ON c.student_id = s.id
        JOIN devices d ON c.device_serial = d.serial_number
        ORDER BY c.checkin_time DESC
        LIMIT 10
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}
}