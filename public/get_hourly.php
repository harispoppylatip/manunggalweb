<?php
$DB_HOST="vps2.haris-iot.my.id"; $DB_USER="manunggal"; $DB_PASS="jaya333"; $DB_NAME="manunggaljaya";
header("Content-Type: application/json");

$days = isset($_GET['days']) ? max(1, min(365, (int)$_GET['days'])) : 30;
$device_id = isset($_GET['device_id']) ? substr(trim($_GET['device_id']),0,64) : 'esp32-1';

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]); exit; }

$stmt = $mysqli->prepare("
  SELECT UNIX_TIMESTAMP(hour_start) AS hour_start,
         ph_avg, soil_avg, temp_avg
  FROM sensor_hourly
  WHERE device_id=? AND hour_start >= (UTC_TIMESTAMP() - INTERVAL ? DAY)
  ORDER BY hour_start ASC
");
$stmt->bind_param("si", $device_id, $days);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }

echo json_encode(["ok"=>true, "device_id"=>$device_id, "days"=>$days, "data"=>$rows]);
