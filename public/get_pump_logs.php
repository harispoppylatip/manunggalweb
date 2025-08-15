<?php
require_once 'config.php';

// Validate request method
validateMethod('GET');

try {
    // Validate and sanitize limit parameter
    $limit = isset($_GET['limit']) ? max(1, min(100, sanitizeInput($_GET['limit'], 'int'))) : 20;
    
    // Get database connection
    $mysqli = getDbConnection();
    
    // Prepare and execute query
    $stmt = $mysqli->prepare("
        SELECT started_at, duration_sec, reason, action, note 
        FROM pump_logs 
        ORDER BY started_at DESC 
        LIMIT ?
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param("i", $limit);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert duration to int if not null
        $row['duration_sec'] = $row['duration_sec'] !== null ? (int)$row['duration_sec'] : null;
        $rows[] = $row;
    }
    
    $stmt->close();
    $mysqli->close();
    
    // Send response
    sendSuccess([
        'limit' => $limit,
        'count' => count($rows),
        'data' => $rows
    ]);
    
} catch (Exception $e) {
    logMessage("Error in get_pump_logs.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}
