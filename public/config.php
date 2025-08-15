<?php
/**
 * Database Configuration
 * Centralized config file for all PHP scripts
 */

// Database Configuration
$DB_CONFIG = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'manunggal', 
    'pass' => getenv('DB_PASS') ?: 'jaya333',
    'name' => getenv('DB_NAME') ?: 'manunggaljaya'
];

// API Configuration
$API_CONFIG = [
    'secret_key' => 'GROWY_SECRET_123',
    'cors_origins' => ['*'], // Allow all origins, change for production
    'timezone' => 'Asia/Makassar'
];

// Set timezone
date_default_timezone_set($API_CONFIG['timezone']);

/**
 * Get database connection
 * @return mysqli
 * @throws Exception
 */
function getDbConnection() {
    global $DB_CONFIG;
    
    $mysqli = @new mysqli(
        $DB_CONFIG['host'], 
        $DB_CONFIG['user'], 
        $DB_CONFIG['pass'], 
        $DB_CONFIG['name']
    );
    
    if ($mysqli->connect_errno) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }
    
    // Set charset to UTF-8
    $mysqli->set_charset("utf8");
    
    return $mysqli;
}

/**
 * Send JSON response
 * @param mixed $data
 * @param int $httpCode
 */
function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 * @param string $message
 * @param int $httpCode
 */
function sendError($message, $httpCode = 400) {
    sendJsonResponse(['ok' => false, 'error' => $message], $httpCode);
}

/**
 * Send success response
 * @param mixed $data
 */
function sendSuccess($data = null) {
    $response = ['ok' => true];
    if ($data !== null) {
        $response['data'] = $data;
    }
    sendJsonResponse($response);
}

/**
 * Validate API key
 * @param string $providedKey
 * @return bool
 */
function validateApiKey($providedKey) {
    global $API_CONFIG;
    return $providedKey === $API_CONFIG['secret_key'];
}

/**
 * Require API key authentication
 */
function requireApiKey() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (!validateApiKey($apiKey)) {
        sendError('Unauthorized', 401);
    }
}

/**
 * Enable CORS headers
 */
function enableCors() {
    global $API_CONFIG;
    
    header('Access-Control-Allow-Origin: ' . implode(', ', $API_CONFIG['cors_origins']));
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    header('Access-Control-Max-Age: 86400'); // 24 hours
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Validate HTTP method
 * @param string|array $allowedMethods
 */
function validateMethod($allowedMethods) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $allowed = is_array($allowedMethods) ? $allowedMethods : [$allowedMethods];
    
    if (!in_array($method, $allowed)) {
        sendError('Method not allowed. Allowed: ' . implode(', ', $allowed), 405);
    }
}

/**
 * Get JSON input data
 * @return array
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON input');
    }
    
    return $data ?: [];
}

/**
 * Sanitize input value
 * @param mixed $value
 * @param string $type (int, float, string, bool)
 * @return mixed
 */
function sanitizeInput($value, $type = 'string') {
    if ($value === null) return null;
    
    switch ($type) {
        case 'int':
            return (int) $value;
        case 'float':
            return (float) $value;
        case 'bool':
            return (bool) $value;
        case 'string':
        default:
            return trim((string) $value);
    }
}

/**
 * Log message to file (for debugging)
 * @param string $message
 * @param string $level
 */
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/logs/app.log';
    
    // Create logs directory if not exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Auto-enable CORS for all requests
enableCors();
?>
