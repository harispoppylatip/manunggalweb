<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'manunggal';
$DB_PASS = getenv('DB_PASS') ?: 'jaya333';
$DB_NAME = getenv('DB_NAME') ?: 'manunggaljaya';
header("Content-Type: application/json");

$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20;

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if($mysqli->connect_errno){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]);
  exit;
}

$stmt = $mysqli->prepare("SELECT started_at, duration_sec, reason, action, note FROM pump_logs ORDER BY started_at DESC LIMIT ?");
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"Prepare failed"]);
  exit;
}
$stmt->bind_param("i", $limit);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while($r = $res->fetch_assoc()) { $rows[] = $r; }

echo json_encode(["ok"=>true, "limit"=>$limit, "data"=>$rows]);
