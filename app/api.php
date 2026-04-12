<?php
/**
 * TROXAN API - FŐKAPU (api.php)
 */

// --- 1. DINAMIKUS CORS LEKEZELÉS ---
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// KIVETTÜK A LOCALHOST KORLÁTOZÁST! 
// Most már dinamikusan beenged mindenkit (a haverod IP-jét is), aki a hálózatról kopogtat!
if (!empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Preflight (OPTIONS) kérés azonnali megválaszolása
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- 2. SESSION BEÁLLÍTÁSOK ---
// Fontos: Localhoston a SameSite 'Lax' vagy 'None' (utóbbi csak HTTPS-en)
ini_set('session.gc_maxlifetime', 3600); // 1 óra - server oldali session élettartam
session_set_cookie_params([
    'lifetime' => 3600, // 1 óra - cookie élettartam
    'path' => '/',
    'domain' => '', 
    'secure' => false, // Fejlesztés alatt (HTTP) false, élesben (HTTPS) true
    'httponly' => true,
    'samesite' => 'Lax' 
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 3. HIBAKERESÉS ÉS TÍPUS ---
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- 4. MVC ÉS ADATBÁZIS BETÖLTÉSE ---
require 'core/config.php';
require 'core/connect.php'; 

if (isset($_SESSION['user_id']) && !empty($_SESSION['logged_in'])) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `Active_Web_Sessions` (
            `user_id` INT NOT NULL PRIMARY KEY,
            `session_token` VARCHAR(128) NOT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $sessionUserId = (int)$_SESSION['user_id'];
        $currentToken = $_SESSION['web_session_token'] ?? '';

        if (empty($currentToken)) {
            $currentToken = bin2hex(random_bytes(32));
            $_SESSION['web_session_token'] = $currentToken;

            $upsertStmt = $pdo->prepare("INSERT INTO `Active_Web_Sessions` (user_id, session_token) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), updated_at = NOW()");
            $upsertStmt->execute([$sessionUserId, $currentToken]);
        } else {
            $getStmt = $pdo->prepare("SELECT session_token FROM `Active_Web_Sessions` WHERE user_id = ? LIMIT 1");
            $getStmt->execute([$sessionUserId]);
            $dbToken = $getStmt->fetchColumn();

            if (!$dbToken) {
                $upsertStmt = $pdo->prepare("INSERT INTO `Active_Web_Sessions` (user_id, session_token) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), updated_at = NOW()");
                $upsertStmt->execute([$sessionUserId, $currentToken]);
            } elseif (!hash_equals((string)$dbToken, (string)$currentToken)) {
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();

                json_response([
                    "status" => "error",
                    "message" => "Your account was used on another browser. Please log in again."
                ], 401);
                exit();
            } else {
                $touchStmt = $pdo->prepare("UPDATE `Active_Web_Sessions` SET updated_at = NOW() WHERE user_id = ?");
                $touchStmt->execute([$sessionUserId]);
            }
        }
    } catch (Throwable $e) {
        // fail-open: do not block whole API if session-table maintenance fails
    }
}

require CORE . 'router.php';