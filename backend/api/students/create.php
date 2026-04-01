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
INSERT INTO students
(student_id,full_name,department,year_of_study,contact,email)
VALUES
(:student_id,:full_name,:department,:year_of_study,:contact,:email)
";

$stmt = $conn->prepare($query);

$stmt->bindParam(":student_id",$data['student_id']);
$stmt->bindParam(":full_name",$data['full_name']);
$stmt->bindParam(":department",$data['department']);
$stmt->bindParam(":year_of_study",$data['year_of_study']);
$stmt->bindParam(":contact",$data['contact']);
$stmt->bindParam(":email",$data['email']);

if($stmt->execute()){

echo json_encode(["message"=>"Student added"]);

}else{

echo json_encode(["error"=>"Insert failed"]);

}