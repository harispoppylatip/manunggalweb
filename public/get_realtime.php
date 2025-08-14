<?php
$DB_HOST="vps2.haris-iot.my.id"; $DB_USER="manunggal"; $DB_PASS="jaya333"; $DB_NAME="manunggaljaya";
header("Content-Type: application/json");

$hours = isset($_GET['hours']) ? max(1, min(48, (int)$_GET['hours'])) : 6;
$device_id = isset($_GET['device_id']) ? substr(trim($_GET['device_id']),0,64) : 'esp32-1';

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]); exit; }

$stmt = $mysqli->prepare("
  SELECT UNIX_TIMESTAMP(ts) AS t, suhu, kelembapan_tanah, pH
  FROM sensor_realtime
  WHERE device_id=? AND ts >= (NOW() - INTERVAL ? HOUR)
  ORDER BY ts ASC
");
$stmt->bind_param("si", $device_id, $hours);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }

echo json_encode(["ok"=>true, "device_id"=>$device_id, "hours"=>$hours, "data"=>$rows]);
