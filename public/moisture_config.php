<?php
require_once 'config.php';

// Validate request method
validateMethod(['GET', 'POST']);

try {
    $mysqli = getDbConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get moisture configuration
        $res = $mysqli->query('SELECT enabled, threshold, target FROM moisture_config WHERE id=1');
        $row = $res ? $res->fetch_assoc() : null;
        
        if (!$row) {
            // Default values if no config exists
            $row = ['enabled' => 0, 'threshold' => 30, 'target' => 70];
        }
        
        $row['enabled'] = (int)$row['enabled'];
        $row['threshold'] = (int)$row['threshold'];
        $row['target'] = (int)$row['target'];
        
        sendSuccess($row);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update moisture configuration
        $data = getJsonInput();
        
        // Validate required fields
        if (!isset($data['enabled'], $data['threshold'], $data['target'])) {
            sendError('Missing required fields: enabled, threshold, target');
        }
        
        $enabled = sanitizeInput($data['enabled'], 'bool') ? 1 : 0;
        $threshold = sanitizeInput($data['threshold'], 'int');
        $target = sanitizeInput($data['target'], 'int');
        
        // Validate business logic
        if ($threshold >= $target) {
            sendError('Threshold must be less than target');
        }
        
        if ($threshold < 0 || $threshold > 100 || $target < 0 || $target > 100) {
            sendError('Threshold and target must be between 0-100');
        }
        
        // Update or insert configuration
        $stmt = $mysqli->prepare('INSERT INTO moisture_config (id, enabled, threshold, target)
            VALUES (1, ?, ?, ?)
            ON DUPLICATE KEY UPDATE enabled=?, threshold=?, target=?');
            
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        
        $stmt->bind_param('iiiiii', $enabled, $threshold, $target, $enabled, $threshold, $target);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $stmt->close();
        $mysqli->close();
        
        // Log configuration change
        logMessage("Moisture config updated: enabled=$enabled, threshold=$threshold, target=$target");
        
        sendSuccess([
            'enabled' => $enabled,
            'threshold' => $threshold,
            'target' => $target
        ]);
    }
    
} catch (Exception $e) {
    logMessage("Error in moisture_config.php: " . $e->getMessage(), 'ERROR');
    sendError($e->getMessage(), 500);
}

