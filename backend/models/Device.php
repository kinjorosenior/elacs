<?php

class Device {

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function create($data){

        $query = "INSERT INTO devices
        (student_id,serial_number,model,device_type,color)
        VALUES
        (:student_id,:serial_number,:model,:device_type,:color)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":student_id"=>$data['student_id'],
            ":serial_number"=>$data['serial_number'],
            ":model"=>$data['model'],
            ":device_type"=>$data['device_type'],
            ":color"=>$data['color']
        ]);
    }

    // ✅ DEVICES INSIDE
    public function getDevicesInside()
    {
        $query = "
            SELECT 
                d.serial_number,
                d.model,
                d.device_type,
                s.full_name,
                c.checkin_time
            FROM checkins c
JOIN devices d ON d.serial_number = c.device_serial 
JOIN students s ON s.student_id = c.student_id
            WHERE c.status = 'IN'
            ORDER BY c.checkin_time DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ DEVICES OUTSIDE
    public function getDevicesOutside()
    {
        $query = "
            SELECT 
                d.serial_number,
                d.model,
                d.device_type,
                s.full_name,
                c.checkout_time
            FROM checkins c
JOIN devices d ON d.serial_number = c.device_serial
JOIN students s ON s.student_id = c.student_id
            WHERE c.status = 'OUT'
            ORDER BY c.checkout_time DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalDevices()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM devices");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $query = "
            SELECT 
                d.serial_number,
                d.model,
                d.device_type,
                d.color,
                s.full_name,
                d.created_at
            FROM devices d
            JOIN students s ON s.student_id = d.student_id
            ORDER BY d.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
