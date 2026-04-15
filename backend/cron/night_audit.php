<?php
/**
 * ELACS Library Night Audit Cron Job
 * Runs daily to send reminders for overdue devices and after-hours checkins
 */

require_once '../config/database.php';
require_once '../models/Checkin.php';
require_once '../models/Student.php';
require_once '../models/Device.php';

$database = new Database();
$db = $database->connect();

echo "[" . date('Y-m-d H:i:s') . "] Starting night audit...\n";

// 1. Overdue checkouts (24+ hours OUT)
$overdueStmt = $db->prepare("
  SELECT c.*, s.full_name, s.email, s.phone
  FROM checkins c
  JOIN (
      SELECT serial_number, MAX(id) AS latest_id
      FROM checkins
      GROUP BY serial_number
  ) latest ON latest.latest_id = c.id
  JOIN students s ON c.student_id = s.student_id
  JOIN devices d ON c.serial_number = d.serial_number
  WHERE c.status = 'OUT'
  AND c.checkin_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)
");

$overdueStmt->execute();
$overdue = $overdueStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($overdue as $record) {
  $subject = "🚨 Device Overdue - ELACS Library";
  $message = "Hi " . $record['full_name'] . ",\n\n" .
             "Device " . $record['serial_number'] . " (checked out " . $record['checkin_time'] . ") " .
             "has been out for over 24 hours.\n\n" .
             "Please return it promptly.\n\n" .
             "-- ELACS Library System";
  
  if (filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
    mail($record['email'], $subject, $message);
    echo "[REMINDER] Overdue sent to {$record['email']}\n";
  }
}

// 2. After-hours devices still IN (after 8PM)
if (intval(date('H')) >= 20) {
  $checkin = new Checkin($db);
  $insideDevices = $checkin->getDevicesStillInside();
  
  foreach ($insideDevices as $device) {
    $studentStmt = $db->prepare("
      SELECT s.full_name, s.email
      FROM devices d
      LEFT JOIN students s
        ON (s.student_id = d.student_id OR CAST(s.id AS CHAR) = CAST(d.student_id AS CHAR))
      WHERE d.serial_number = ?
      LIMIT 1
    ");
    $studentStmt->execute([$device['serial_number']]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student && $student['email']) {
      $subject = "📢 Library Closing - Collect Your Device";
      $message = "Hi " . $student['full_name'] . ",\n\n" .
                 "Library closes soon. Please check out your device: " . $device['serial_number'] . "\n\n" .
                 "Thank you!";
      
      mail($student['email'], $subject, $message);
      echo "[CLOSING] Reminder sent to {$student['email']}\n";
    }
  }
}

echo "[" . date('Y-m-d H:i:s') . "] Night audit complete. Processed " . count($overdue) . " overdue + after-hours checks.\n";
?>



