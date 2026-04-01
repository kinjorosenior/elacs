<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  exit(0);
}

try {
require_once "../../config/database.php";
  $db = new Database();
  $conn = $db->connect();

  $status = $_GET['status'] ?? '';
  $limit = $_GET['limit'] ?? 50;
  $offset = $_GET['offset'] ?? 0;

  $where = $status ? "WHERE status = ?" : '';
  $params = $status ? [$status] : [];

  $sql = "SELECT * FROM visitors $where ORDER BY visit_time DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);

  $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $statsStmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'IN' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'OUT' THEN 1 ELSE 0 END) as signed_out,
    COUNT(DISTINCT DATE(visit_time)) as unique_days
    FROM visitors");
  $statsStmt->execute();
  $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    'visitors' => $visitors,
    'stats' => $stats,
    'total' => $stats['total']
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>

