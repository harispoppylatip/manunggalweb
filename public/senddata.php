<?php
require_once 'config.php';

// Validate request method and API key
validateMethod('POST');
requireApiKey();

try {
    // Get and validate input data
    $data = getJsonInput();
    
    $suhu = array_key_exists('suhu', $data) ? 
        (is_null($data['suhu']) ? null : sanitizeInput($data['suhu'], 'float')) : null;
    
    $soil = array_key_exists('kelembapan_tanah', $data) ? 
        (is_null($data['kelembapan_tanah']) ? null : sanitizeInput($data['kelembapan_tanah'], 'int')) : null;
    
    $ph = array_key_exists('ph', $data) ? 
        (is_null($data['ph']) ? null : sanitizeInput($data['ph'], 'float')) : null;
    
    $relay = sanitizeInput($data['relay'] ?? 0, 'int');
    
    // Log received data for debugging
    logMessage("Sensor data received: " . json_encode($data));
    
    // Get database connection
    $mysqli = getDbConnection();
    
    // Prepare and execute insert statement
    $stmt = $mysqli->prepare("INSERT INTO sensor_realtime (suhu, kelembapan_tanah, ph, relay) VALUES (?,?,?,?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("didi", $suhu, $soil, $ph, $relay);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $insertId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();
    
    // Log success
    logMessage("Sensor data saved with ID: $insertId");
    
    // Send success response
    sendSuccess(['id' => $insertId]);
    
} catch (Exception $e) {
    logMessage("Error in senddata.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}
