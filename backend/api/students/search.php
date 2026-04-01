<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$search = $_GET['q'];

$query = "
SELECT * FROM students
WHERE student_id LIKE :search
OR full_name LIKE :search
";

$stmt = $conn->prepare($query);
$search = "%$search%";
$stmt->bindParam(":search",$search);

$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);