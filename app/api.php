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
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Preflight (OPTIONS) kérés azonnali megválaszolása
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- 2. SESSION BEÁLLÍTÁSOK ---
// Fontos: Localhoston a SameSite 'Lax' vagy 'None' (utóbbi csak HTTPS-en)
session_set_cookie_params([
    'lifetime' => 0,
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
require CORE . 'router.php';