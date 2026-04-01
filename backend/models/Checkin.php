<?php

class Checkin
{
    private $conn;
    private $table = "checkins";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $query = "INSERT INTO {$this->table}
                  (student_id, device_serial, status, checkin_time)
                  VALUES
                  (:student_id, :device_serial, 'IN', NOW())";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':device_serial' => $data['device_serial']
        ]);
    }

    public function getActiveCheckin($serial)
    {
        $query = "SELECT * FROM {$this->table}
                  WHERE device_serial = :serial
                  AND status = 'IN'
                  ORDER BY id DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':serial' => $serial]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function closeCheckin($id)
    {
        $query = "UPDATE {$this->table}
                  SET status = 'OUT',
                      checkout_time = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([':id' => $id]);
    }

    public function getDevicesStillInside()
    {
        $query = "
            SELECT 
                c.device_serial,
                c.checkin_time,
                s.full_name
            FROM {$this->table} c
            JOIN students s ON c.student_id = s.student_id
            WHERE c.status = 'IN'
            ORDER BY c.checkin_time DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}