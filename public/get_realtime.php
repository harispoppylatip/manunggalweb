<?php
require_once 'config.php';

// Validate request method
validateMethod('GET');

try {
    // Validate and sanitize hours parameter
    $hours = isset($_GET['hours']) ? max(1, min(48, sanitizeInput($_GET['hours'], 'int'))) : 6;
    
    // Get database connection
    $mysqli = getDbConnection();
    
    // Prepare and execute query
    $stmt = $mysqli->prepare("
        SELECT UNIX_TIMESTAMP(ts) AS t, suhu, kelembapan_tanah, ph, relay
        FROM sensor_realtime
        WHERE ts >= (NOW() - INTERVAL ? HOUR)
        ORDER BY ts ASC
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param("i", $hours);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert numeric strings to proper types
        $row['t'] = (int)$row['t'];
        $row['suhu'] = $row['suhu'] !== null ? (float)$row['suhu'] : null;
        $row['kelembapan_tanah'] = $row['kelembapan_tanah'] !== null ? (int)$row['kelembapan_tanah'] : null;
        $row['ph'] = $row['ph'] !== null ? (float)$row['ph'] : null;
        $row['relay'] = (int)$row['relay'];
        
        $rows[] = $row;
    }
    
    $stmt->close();
    $mysqli->close();
    
    // Send response
    sendSuccess([
        'hours' => $hours,
        'count' => count($rows),
        'data' => $rows
    ]);
    
} catch (Exception $e) {
    logMessage("Error in get_realtime.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}
