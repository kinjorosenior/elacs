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
                (
                    SELECT c2.checkin_time
                    FROM checkins c2
                    WHERE c2.serial_number = c.serial_number
                      AND c2.status = 'OUT'
                      AND c2.checkin_time >= c.checkin_time
                    ORDER BY c2.checkin_time ASC, c2.id ASC
                    LIMIT 1
                ) as checkout_time,
                TIMESTAMPDIFF(
                    HOUR,
                    c.checkin_time,
                    COALESCE(
                        (
                            SELECT c3.checkin_time
                            FROM checkins c3
                            WHERE c3.serial_number = c.serial_number
                              AND c3.status = 'OUT'
                              AND c3.checkin_time >= c.checkin_time
                            ORDER BY c3.checkin_time ASC, c3.id ASC
                            LIMIT 1
                        ),
                        NOW()
                    )
                ) as duration_hours
            FROM checkins c
            JOIN devices d ON d.serial_number = c.serial_number
            LEFT JOIN students s ON s.student_id = c.student_id
            WHERE c.status = 'IN'
            ORDER BY c.checkin_time DESC
            LIMIT 100
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    }
}


