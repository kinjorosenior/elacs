<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once "../../config/database.php";

$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"),true);

$query = "
INSERT INTO devices
(student_id,serial_number,model,device_type,color,marks)
VALUES
(:student_id,:serial_number,:model,:device_type,:color,:marks)
";

$stmt = $conn->prepare($query);

$stmt->bindParam(":student_id",$data['student_id']);
$stmt->bindParam(":serial_number",$data['serial_number']);
$stmt->bindParam(":model",$data['model']);
$stmt->bindParam(":device_type",$data['device_type']);
$stmt->bindParam(":color",$data['color']);
$stmt->bindParam(":marks",$data['marks']);


if($stmt->execute()){
  echo json_encode(["message"=>"Device registered successfully"]);
} else {
  echo json_encode([
    "error" => "Insert failed",
    "details" => $stmt->errorInfo()
  ]);
}

