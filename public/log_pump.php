<?php
require_once 'config.php';

// Validate request method
validateMethod('POST');

try {
    // Get and validate input data
    $data = getJsonInput();
    
    // Validate and sanitize action
    $action = strtoupper($data['action'] ?? 'ON');
    if (!in_array($action, ['ON', 'OFF', 'SET', 'DISABLE'])) {
        $action = 'ON';
    }
    
    // Validate and sanitize reason
    $reason = strtolower($data['reason'] ?? 'manual');
    if (!in_array($reason, ['manual', 'schedule', 'moisture', 'other'])) {
        $reason = 'other';
    }
    
    // Sanitize other fields
    $duration = isset($data['duration_sec']) ? sanitizeInput($data['duration_sec'], 'int') : null;
    $note = isset($data['note']) ? substr(sanitizeInput($data['note']), 0, 255) : null;
    
    // Get database connection
    $mysqli = getDbConnection();
    
    // Prepare and execute insert statement
    $stmt = $mysqli->prepare("INSERT INTO pump_logs (duration_sec, reason, action, note) VALUES (?,?,?,?)");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param("isss", $duration, $reason, $action, $note);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $insertId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();
    
    // Log the pump action
    logMessage("Pump log created: action=$action, reason=$reason, duration=$duration, id=$insertId");
    
    // Send success response
    sendSuccess(['id' => $insertId]);
    
} catch (Exception $e) {
    logMessage("Error in log_pump.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}
$stmt->bind_param("isss", $duration, $reason, $action, $note);
$ok = $stmt->execute();
if(!$ok){
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"Execute failed: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true, "id"=>$stmt->insert_id]);
