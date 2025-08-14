<?php
// ====== CONFIG ======
$DB_HOST = "vps2.haris-iot.my.id";
$DB_USER = "haris";
$DB_PASS = "moler333";
$DB_NAME = "manunggaljaya";

$API_KEY = "GROWY_SECRET_123"; // samakan dengan yang di ESP32

header("Content-Type: application/json");

// Cek metode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["ok"=>false, "error"=>"Method not allowed"]);
  exit;
}

// Cek API key sederhana
$hdrKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($hdrKey !== $API_KEY) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "error"=>"Unauthorized"]);
  exit;
}

// Ambil payload JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"Invalid JSON"]);
  exit;
}

// Ambil field (boleh null)
$suhu = isset($data['suhu']) ? $data['suhu'] : null;
$soil = isset($data['kelembapan_tanah']) ? $data['kelembapan_tanah'] : null;
$ph   = isset($data['ph']) ? $data['ph'] : null;

// Validasi ringan (opsional)
if ($soil !== null) $soil = (int)$soil;
if ($suhu !== null) $suhu = (float)$suhu;
if ($ph   !== null) $ph   = (float)$ph;

// Koneksi DB (mysqli + prepared stmt)
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"DB connect: ".$mysqli->connect_error]);
  exit;
}

$sql = "INSERT INTO `growy_logs` (`suhu`, `kelembapan_tanah`, `pH`) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"Prepare failed"]);
  exit;
}

// Bind (isi null aman dengan i/f + null check)
if ($suhu === null) { $suhu_param = null; } else { $suhu_param = $suhu; }
if ($soil === null) { $soil_param = null; } else { $soil_param = $soil; }
if ($ph   === null) { $ph_param   = null; } else { $ph_param   = $ph; }

// Gunakan "sdi" bukan masalah urutan, tapi kita set: d (double), i (int), d (double)
$stmt->bind_param("did", $suhu_param, $soil_param, $ph_param);

$ok = $stmt->execute();
if (!$ok) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"Execute failed: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true, "id"=>$stmt->insert_id]);
