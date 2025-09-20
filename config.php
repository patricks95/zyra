<?php
// Zyra Video Conferencing Configuration

// VideoSDK Configuration
define('VIDEOSDK_API_KEY', '264a55c3-2c83-4f43-b36a-002d37fbcf1e');
define('VIDEOSDK_SECRET_KEY', '67d52398243cbf43d0c5a8c15fe515dad43d40cabcf6951ec7f34a8d2aef2ed9');
define('VIDEOSDK_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiIyNjRhNTVjMy0yYzgzLTRmNDMtYjM2YS0wMDJkMzdmYmNmMWUiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM2MTkwMCwiZXhwIjoxNzU4OTY2NzAwfQ.tQLdxahiITv6Cnb82y61oQDNF9SjcfGRe4wqNIn1P_o');

// Application Configuration
define('APP_NAME', 'Zyra');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Professional Video Conferencing Made Simple');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u765872199_zyradb');
define('DB_USER', 'u765872199_patricks');
define('DB_PASS', '7^b?vmFk/+Qz');

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
