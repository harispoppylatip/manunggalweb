<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'manunggal';
$DB_PASS = getenv('DB_PASS') ?: 'jaya333';
$DB_NAME = getenv('DB_NAME') ?: 'manunggaljaya';
header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
  http_response_code(405);
  echo json_encode(["ok"=>false, "error"=>"Method not allowed, use POST"]);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if(!is_array($data)){
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"Invalid JSON"]);
  exit;
}

$action = strtoupper($data['action'] ?? 'ON');
if(!in_array($action, ['ON','OFF','SET','DISABLE'])) $action = 'ON';
$reason = strtolower($data['reason'] ?? 'manual');
if(!in_array($reason, ['manual','schedule','moisture','other'])) $reason = 'other';
$duration = isset($data['duration_sec']) ? (int)$data['duration_sec'] : null;
$note = isset($data['note']) ? substr($data['note'],0,255) : null;

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if($mysqli->connect_errno){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]);
  exit;
}

$stmt = $mysqli->prepare("INSERT INTO pump_logs (duration_sec, reason, action, note) VALUES (?,?,?,?)");
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"Prepare failed"]);
  exit;
}
$stmt->bind_param("isss", $duration, $reason, $action, $note);
$ok = $stmt->execute();
if(!$ok){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"Execute failed: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true, "id"=>$stmt->insert_id]);
