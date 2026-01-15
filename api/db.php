<?php
/**
 * api/db.php
 * Robust database connection and helper functions.
 * No closing tag to avoid whitespace issues.
 */

// Security & CORS Headers
if (!function_exists('setSecurityHeaders')) {
    function setSecurityHeaders() {
        // Allow from localhost:5173 (Vite) and localhost/vital-connect (Propd)
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed_origins = [
            'http://localhost:5173', 
            'http://localhost:3000', 
            'http://localhost'
        ];
        
        if (in_array($origin, $allowed_origins) || empty($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }

        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // Security Headers
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        header("Content-Security-Policy: default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'none';");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
setSecurityHeaders();

$host = 'localhost';
$db   = 'blood_don';
$user = 'root';
$pass = ''; // Default WAMP password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     error_log("Database connection failed: " . $e->getMessage());
     if (ob_get_length()) ob_clean();
     header('Content-Type: application/json', true, 500);
     echo json_encode(["status" => "error", "message" => "Database connection failed"]);
     exit;
}

if (!function_exists('sendResponse')) {
    function sendResponse($data, $statusCode = 200) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('getJsonInput')) {
    function getJsonInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}

if (!function_exists('generateUUID')) {
    function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
