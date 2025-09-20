<?php
// Zyra Video Conferencing Configuration

// VideoSDK Configuration
define('VIDEOSDK_API_KEY', '73648993-befd-40c3-ba71-56a3d1e6e304');
define('VIDEOSDK_SECRET_KEY', 'f604fad113af89b72ae9df09d9ca9a4bfa83a36b0e28543668ab947f0e40b02e');
define('VIDEOSDK_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiI3MzY0ODk5My1iZWZkLTQwYzMtYmE3MS01NmEzZDFlNmUzMDQiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0OTQ1NSwiZXhwIjoxNzg5ODg1NDU1fQ.6iIQeg2rABa0Mp3gfUxqsSxd6J8GBuyQ6tP7msoPuJU');

// Application Configuration
define('APP_NAME', 'Zyra');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Professional Video Conferencing Made Simple');

// Database Configuration (if needed for future features)
define('DB_HOST', 'localhost');
define('DB_NAME', 'zyra_conferences');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security Configuration
define('ENCRYPTION_KEY', 'zyra_secure_key_2024');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Meeting Configuration
define('MAX_PARTICIPANTS', 100);
define('MEETING_DURATION', 1440); // 24 hours in minutes
define('RECORDING_ENABLED', true);

// API Endpoints
define('VIDEOSDK_API_URL', 'https://api.videosdk.live/v2');
define('VIDEOSDK_WS_URL', 'wss://api.videosdk.live/v2');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Utility Functions
function generateMeetingId() {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateMeetingId($meetingId) {
    return preg_match('/^[a-zA-Z0-9]{8,20}$/', $meetingId);
}

function logActivity($message) {
    $logFile = 'logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
