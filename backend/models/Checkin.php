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
                  (library_id, student_id, serial_number, admin_id, status, checkin_time)
                  VALUES
                  (:library_id, :student_id, :serial_number, :admin_id, 'IN', NOW())";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':library_id' => $data['library_id'] ?? null,
            ':student_id' => $data['student_id'],
            ':serial_number' => $data['serial_number'],
            ':admin_id' => $data['admin_id'] ?? $data['librarian_id'] ?? 1
        ]);
    }

    public function getActiveCheckin($serialOrDevice)
    {
        $serial = $serialOrDevice;

        // Backward compatibility: some callers pass device id instead of serial number
        if (is_numeric($serialOrDevice)) {
            $deviceStmt = $this->conn->prepare("SELECT serial_number FROM devices WHERE id = :id LIMIT 1");
            $deviceStmt->execute([':id' => $serialOrDevice]);
            $device = $deviceStmt->fetch(PDO::FETCH_ASSOC);
            $serial = $device['serial_number'] ?? null;
        }

        if (!$serial) {
            return null;
        }

        $query = "SELECT * FROM {$this->table}
                  WHERE serial_number = :serial_number
                  ORDER BY checkin_time DESC, id DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':serial_number' => $serial]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['status'] !== 'IN') {
            return null;
        }

        return $row;
    }

    public function closeCheckin($id, $admin_id = 1)
    {
        $activeStmt = $this->conn->prepare("SELECT library_id, student_id, serial_number, status FROM {$this->table} WHERE id = :id LIMIT 1");
        $activeStmt->execute([':id' => $id]);
        $active = $activeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$active || $active['status'] !== 'IN') {
            return false;
        }

        $query = "INSERT INTO {$this->table}
                  (library_id, student_id, serial_number, admin_id, status, checkin_time)
                  VALUES
                  (:library_id, :student_id, :serial_number, :admin_id, 'OUT', NOW())";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':library_id' => $active['library_id'],
            ':student_id' => $active['student_id'],
            ':serial_number' => $active['serial_number'],
            ':admin_id' => $admin_id
        ]);
    }

    public function calculateDuration($id)
    {
        $inStmt = $this->conn->prepare("SELECT serial_number, checkin_time FROM {$this->table} WHERE id = :id AND status = 'IN' LIMIT 1");
        $inStmt->execute([':id' => $id]);
        $in = $inStmt->fetch(PDO::FETCH_ASSOC);

        if (!$in) {
            return ['minutes_spent' => 0];
        }

        $outStmt = $this->conn->prepare("
            SELECT checkin_time
            FROM {$this->table}
            WHERE serial_number = :serial
              AND status = 'OUT'
              AND checkin_time >= :in_time
            ORDER BY checkin_time ASC, id ASC
            LIMIT 1
        ");
        $outStmt->execute([
            ':serial' => $in['serial_number'],
            ':in_time' => $in['checkin_time']
        ]);
        $out = $outStmt->fetch(PDO::FETCH_ASSOC);

        $endTime = $out['checkin_time'] ?? date('Y-m-d H:i:s');
        $minutes = max(
            0,
            (int)floor((strtotime($endTime) - strtotime($in['checkin_time'])) / 60)
        );

        return ['minutes_spent' => $minutes];
    }

    public function getDevicesStillInside()
    {
        $query = "
            SELECT 
                c.serial_number,
                c.checkin_time,
                s.full_name
            FROM {$this->table} c
            JOIN (
                SELECT serial_number, MAX(id) AS latest_id
                FROM {$this->table}
                GROUP BY serial_number
            ) latest ON latest.latest_id = c.id
            LEFT JOIN students s ON c.student_id = s.student_id
            WHERE c.status = 'IN'
            ORDER BY c.checkin_time DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}