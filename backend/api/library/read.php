<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM library_settings ORDER BY id LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($settings ?: []);
?>

