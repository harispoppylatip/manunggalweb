<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'manunggal';
$DB_PASS = getenv('DB_PASS') ?: 'jaya333';
$DB_NAME = getenv('DB_NAME') ?: 'manunggaljaya';
header('Content-Type: application/json');

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"DB connect: ".$mysqli->connect_error]);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
  $res = $mysqli->query('SELECT enabled, threshold, target FROM moisture_config WHERE id=1');
  $row = $res ? $res->fetch_assoc() : null;
  if (!$row) { $row = ['enabled'=>0,'threshold'=>30,'target'=>70]; }
  $row['enabled'] = (int)$row['enabled'];
  echo json_encode(['ok'=>true,'data'=>$row]);
  exit;
} elseif ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!is_array($data) || !isset($data['enabled'],$data['threshold'],$data['target'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid data']);
    exit;
  }
  $enabled = $data['enabled'] ? 1 : 0;
  $threshold = (int)$data['threshold'];
  $target = (int)$data['target'];
  if ($threshold >= $target) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'threshold must be < target']);
    exit;
  }
  $stmt = $mysqli->prepare('INSERT INTO moisture_config (id, enabled, threshold, target) VALUES (1,?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled), threshold=VALUES(threshold), target=VALUES(target)');
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Prepare failed']);
    exit;
  }
  $stmt->bind_param('iii', $enabled, $threshold, $target);
  if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Execute failed: '.$stmt->error]);
    exit;
  }
  echo json_encode(['ok'=>true]);
  exit;
} else {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}
?>
