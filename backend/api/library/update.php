ant <?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["message" => "No data"]);
  exit;
}

$stmt = $conn->prepare("
  UPDATE library_settings SET 
  open_weekday = :open_weekday,
  close_weekday = :close_weekday,
  open_saturday = :open_saturday,
  close_saturday = :close_saturday,
  open_sunday = :open_sunday,
  close_sunday = :close_sunday,
  reminder_minutes = :reminder_minutes
  WHERE id = 1
");

$result = $stmt->execute($data);

echo json_encode(["success" => $result, "message" => "Settings updated"]);
?>

