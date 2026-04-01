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
$db = $db->connect();

echo "[$(date)] Starting night audit...\n";

// 1. Overdue checkouts (24+ hours OUT)
$overdueStmt = $db->prepare("
  SELECT c.*, s.full_name, s.email, s.phone 
  FROM checkins c 
  JOIN students s ON c.student_id = s.student_id 
  JOIN devices d ON c.device_serial = d.serial_number
  WHERE c.status = 'OUT' 
  AND c.checkout_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)
");

$overdueStmt->execute();
$overdue = $overdueStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($overdue as $record) {
  $subject = "🚨 Device Overdue - ELACS Library";
  $message = "Hi " . $record['full_name'] . ",\n\n" .
             "Device " . $record['device_serial'] . " (checked out " . $record['checkout_time'] . ") " .
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
    $studentStmt = $db->prepare("SELECT full_name, email FROM students WHERE student_id = (SELECT student_id FROM devices WHERE serial_number = ? LIMIT 1)");
    $studentStmt->execute([$device['device_serial']]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student && $student['email']) {
      $subject = "📢 Library Closing - Collect Your Device";
      $message = "Hi " . $student['full_name'] . ",\n\n" .
                 "Library closes soon. Please check out your device: " . $device['device_serial'] . "\n\n" .
                 "Thank you!";
      
      mail($student['email'], $subject, $message);
      echo "[CLOSING] Reminder sent to {$student['email']}\n";
    }
  }
}

echo "[$(date)] Night audit complete. Processed " . count($overdue) . " overdue + after-hours checks.\n";
?>

