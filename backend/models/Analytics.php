<?php

class Analytics
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function safeFetch($stmt)
    {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : ["total" => 0];
    }

    public function totalStudents()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM students");
        return $this->safeFetch($stmt);
    }

    public function totalDevices()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM devices");
        return $this->safeFetch($stmt);
    }

    // ✅ FIXED (USES CHECKINS)
    public function devicesInside()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as total
            FROM (
                SELECT c1.serial_number
                FROM checkins c1
                INNER JOIN (
                    SELECT serial_number, MAX(id) AS latest_id
                    FROM checkins
                    GROUP BY serial_number
                ) c2 ON c1.id = c2.latest_id
                WHERE c1.status = 'IN'
            ) t
        ");
        return $this->safeFetch($stmt);
    }

    public function todayCheckins()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as total 
            FROM checkins 
            WHERE DATE(checkin_time) = CURDATE()
              AND status = 'IN'
        ");
        return $this->safeFetch($stmt);
    }

    public function weeklyCheckins()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as total 
            FROM checkins 
            WHERE YEARWEEK(checkin_time, 1) = YEARWEEK(CURDATE(), 1)
              AND status = 'IN'
        ");
        return $this->safeFetch($stmt);
    }

    public function monthlyCheckins()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as total 
            FROM checkins 
            WHERE MONTH(checkin_time) = MONTH(CURDATE())
            AND YEAR(checkin_time) = YEAR(CURDATE())
            AND status = 'IN'
        ");
        return $this->safeFetch($stmt);
    }

    // ✅ NEW (for charts)
    public function checkinCheckoutStats()
    {
        $stmt = $this->conn->query("
            SELECT 
                SUM(CASE WHEN status = 'IN' THEN 1 ELSE 0 END) as total_checkins,
                SUM(CASE WHEN status = 'OUT' THEN 1 ELSE 0 END) as total_checkouts
            FROM checkins
        ");

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : [
            "total_checkins" => 0,
            "total_checkouts" => 0
        ];
    }

    public function trustDistribution()
    {
        $stmt = $this->conn->query("
            SELECT 
                CASE
                    WHEN trust_score >= 80 THEN 'High'
                    WHEN trust_score >= 50 THEN 'Medium'
                    ELSE 'Low'
                END as category,
                COUNT(*) as total
            FROM students
            GROUP BY category
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}