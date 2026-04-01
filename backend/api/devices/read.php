<?php
// read.php

header("Access-Control-Allow-Origin: *"); // allow requests from any origin
header("Content-Type: application/json; charset=UTF-8");

// Database configuration
$host = "localhost";       // Database host
$db_name = "elacs";        // Database name
$username = "root";        // Database username
$password = "";            // Database password

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all devices
    $stmt = $pdo->prepare("SELECT * FROM devices ORDER BY id DESC");
    $stmt->execute();

    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    echo json_encode($devices);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}