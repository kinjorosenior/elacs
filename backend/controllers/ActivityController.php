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
            CASE WHEN c.status = 'IN' THEN 'checkin' ELSE 'checkout' END AS status,
            c.checkin_time,
            c.checkin_time AS checkout_time
        FROM checkins c
        LEFT JOIN students s ON c.student_id = s.student_id
        JOIN devices d ON c.serial_number = d.serial_number
        ORDER BY c.checkin_time DESC
        LIMIT 10
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}
}