<?php

require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController
{
    private $analytics;
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->analytics = new Analytics($db);
    }

    public function dashboard()
    {
        echo json_encode([
            "total_students" => $this->analytics->totalStudents(),
            "total_devices" => $this->analytics->totalDevices(),
            "devices_inside" => $this->analytics->devicesInside(),
            "today_checkins" => $this->analytics->todayCheckins(),
            "weekly_checkins" => $this->analytics->weeklyCheckins(),
            "monthly_checkins" => $this->analytics->monthlyCheckins(),
            "trust_distribution" => $this->analytics->trustDistribution()
        ]);
    }

    public function checkinCheckoutStats()
    {
        echo json_encode($this->analytics->checkinCheckoutStats());
    }

    public function studentReport()
    {
        $query = "
            SELECT 
                s.full_name,
                d.serial_number,
                d.device_type,
                c.checkin_time,
                c.checkout_time,
                TIMESTAMPDIFF(HOUR, c.checkin_time, COALESCE(c.checkout_time, NOW())) as duration_hours
            FROM checkins c
            JOIN devices d ON d.serial_number COLLATE utf8mb4_unicode_ci = c.device_serial COLLATE utf8mb4_unicode_ci
            JOIN students s ON s.student_id COLLATE utf8mb4_unicode_ci = c.student_id COLLATE utf8mb4_unicode_ci
            ORDER BY c.checkin_time DESC
            LIMIT 100
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    }
}

