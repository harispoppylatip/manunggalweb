<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'manunggal';
$DB_PASS = getenv('DB_PASS') ?: 'jaya333';
$DB_NAME = getenv('DB_NAME') ?: 'manunggaljaya';
header("Content-Type: application/json");

$days = isset($_GET['days']) ? max(1, min(365, (int)$_GET['days'])) : 30;

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]); exit; }

$stmt = $mysqli->prepare("
  SELECT UNIX_TIMESTAMP(hour_start) AS hour_start,
         ph_avg, kelembapan_avg, suhu_avg
  FROM sensor_hourly
  WHERE hour_start >= (UTC_TIMESTAMP() - INTERVAL ? DAY)
  ORDER BY hour_start ASC
");
$stmt->bind_param("i", $days);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }

echo json_encode(["ok"=>true, "days"=>$days, "data"=>$rows]);
