<?php
require_once 'config.php';

// Validate request method
validateMethod('GET');

try {
    // Validate and sanitize days parameter
    $days = isset($_GET['days']) ? max(1, min(365, sanitizeInput($_GET['days'], 'int'))) : 30;
    
    // Get database connection
    $mysqli = getDbConnection();
    
    // Prepare and execute query
    $stmt = $mysqli->prepare("
        SELECT UNIX_TIMESTAMP(hour_start) AS hour_start,
               ph_avg, kelembapan_avg, suhu_avg
        FROM sensor_hourly
        WHERE hour_start >= (UTC_TIMESTAMP() - INTERVAL ? DAY)
        ORDER BY hour_start ASC
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param("i", $days);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert numeric strings to proper types
        $row['hour_start'] = (int)$row['hour_start'];
        $row['ph_avg'] = $row['ph_avg'] !== null ? (float)$row['ph_avg'] : null;
        $row['kelembapan_avg'] = $row['kelembapan_avg'] !== null ? (float)$row['kelembapan_avg'] : null;
        $row['suhu_avg'] = $row['suhu_avg'] !== null ? (float)$row['suhu_avg'] : null;
        
        $rows[] = $row;
    }
    
    $stmt->close();
    $mysqli->close();
    
    // Send response
    sendSuccess([
        'days' => $days,
        'count' => count($rows),
        'data' => $rows
    ]);
    
} catch (Exception $e) {
    logMessage("Error in get_hourly.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}
