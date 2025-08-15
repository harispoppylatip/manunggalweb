<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'manunggal';
$DB_PASS = getenv('DB_PASS') ?: 'jaya333';
$DB_NAME = getenv('DB_NAME') ?: 'manunggaljaya';
$API_KEY = "GROWY_SECRET_123";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["ok"=>false,"error"=>"Method not allowed, use POST"]); exit; }
if (($_SERVER['HTTP_X_API_KEY'] ?? '') !== $API_KEY) { http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Unauthorized"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) { http_response_code(400); echo json_encode(["ok"=>false,"error"=>"Invalid JSON"]); exit; }

$suhu  = array_key_exists('suhu',$data) ? (is_null($data['suhu'])?null:(float)$data['suhu']) : null;
$soil  = array_key_exists('kelembapan_tanah',$data) ? (is_null($data['kelembapan_tanah'])?null:(int)$data['kelembapan_tanah']) : null;
$ph    = array_key_exists('ph',$data) ? (is_null($data['ph'])?null:(float)$data['ph']) : null;
$relay = array_key_exists('relay',$data) ? (int)$data['relay'] : 0;

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]); exit; }

$stmt = $mysqli->prepare("INSERT INTO sensor_realtime (suhu, kelembapan_tanah, ph, relay) VALUES (?,?,?,?)");
if (!$stmt) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"Prepare failed"]); exit; }
$stmt->bind_param("didi", $suhu, $soil, $ph, $relay);
$ok = $stmt->execute();
if (!$ok) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"Execute failed: ".$stmt->error]); exit; }

echo json_encode(["ok"=>true,"id"=>$stmt->insert_id]);
