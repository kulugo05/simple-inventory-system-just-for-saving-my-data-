<?php
// ============================================================
// CONFIGURATION FILE - database.php
// Dito nakalagay ang lahat ng database settings at helper
// functions na ginagamit ng buong system.
// ============================================================

// --- Database Credentials ---
// Palitan ang mga values dito kung mag-iiba ang database mo
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'foodhub_db');
define('DB_PORT',    3306);
define('DB_CHARSET', 'utf8mb4');

// --- Kumokonekta sa Database ---
// Ginagamit ang "static" para hindi na mag-reconnect ulit
// sa bawat request - isa lang ang connection sa buong session
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// --- Nagse-set ng API Headers ---
// Tinitiyak na ang lahat ng API responses ay JSON format
function apiHeaders(): void {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// --- Nagre-return ng JSON Response ---
function jsonResponse(mixed $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// --- Nagbabasa ng Request Body ---
function getBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
