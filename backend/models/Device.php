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

    public function findBySerial($serial)
    {
        $stmt = $this->conn->prepare("SELECT * FROM devices WHERE serial_number = :serial LIMIT 1");
        $stmt->execute([':serial' => $serial]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE devices SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
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
            JOIN (
                SELECT serial_number, MAX(id) AS latest_id
                FROM checkins
                GROUP BY serial_number
            ) latest ON latest.latest_id = c.id
            JOIN devices d ON d.serial_number = c.serial_number
            LEFT JOIN students s ON s.student_id = c.student_id
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
                c.checkin_time AS checkout_time
            FROM checkins c
            JOIN (
                SELECT serial_number, MAX(id) AS latest_id
                FROM checkins
                GROUP BY serial_number
            ) latest ON latest.latest_id = c.id
            JOIN devices d ON d.serial_number = c.serial_number
            LEFT JOIN students s ON s.student_id = c.student_id
            WHERE c.status = 'OUT'
            ORDER BY c.checkin_time DESC
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
                d.id,
                d.student_id,
                d.serial_number,
                d.model,
                d.device_type,
                d.color,
                s.full_name,
                d.created_at
            FROM devices d
            LEFT JOIN students s
                ON (s.student_id = d.student_id OR CAST(s.id AS CHAR) = CAST(d.student_id AS CHAR))
            ORDER BY d.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
