<?php
$DB_HOST="vps2.haris-iot.my.id"; $DB_USER="manunggal"; $DB_PASS="jaya333"; $DB_NAME="manunggaljaya";
header("Content-Type: application/json");

$hours = isset($_GET['hours']) ? max(1, min(48, (int)$_GET['hours'])) : 6;

$mysqli = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]); exit; }

$stmt = $mysqli->prepare("
  SELECT UNIX_TIMESTAMP(ts) AS t, suhu, kelembapan_tanah, ph, relay
  FROM sensor_realtime
  WHERE ts >= (NOW() - INTERVAL ? HOUR)
  ORDER BY ts ASC
");
$stmt->bind_param("i", $hours);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }

echo json_encode(["ok"=>true, "hours"=>$hours, "data"=>$rows]);
