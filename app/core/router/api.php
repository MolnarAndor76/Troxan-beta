<?php
// ======================================================
// API ROUTES (REST ENDPOINTS)
// All URLs starting with /api go here
// ======================================================

global $pdo;

// --- GLOBÁLIS BAN ELLENŐRZÉS ---
// Ha be van lépve a felhasználó, azonnal megnézzük, hogy nincs-e kitiltva.
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    try {
        $banStmt = $pdo->prepare("SELECT is_banned FROM `User` WHERE user_id = ?");
        $banStmt->execute([$_SESSION['user_id']]);
        $isBanned = $banStmt->fetchColumn();

        // Ha ki van tiltva (1), és nem épp kijelentkezni akar, akkor erőszakkal az "isBanned" oldalra irányítjuk!
        if ($isBanned == 1 && $route['segment1'] !== 'logout') {
            $route['segment1'] = 'isBanned';
        }
    } catch (Exception $e) {
        json_response(['error' => 'Database error during ban check.'], 500);
    }
}
// -------------------------------

$data = [
    "route"  => $route,
    "method" => $method,
];

// Decide which API resource is requested
switch ($route['segment1']) {

    // ----------------------------------
    // API ROUTES
    // ----------------------------------
    case "main":
        load_controller($data, API_CONTROLLERS . 'mainController.php');
        break;
    case "editor":
        load_controller($data, API_CONTROLLERS . 'editorController.php');
        break;
    case "maps":
        load_controller($data, API_CONTROLLERS . 'mapsController.php');
        break;
    case "statistics":
        load_controller($data, API_CONTROLLERS . 'statisticsController.php');
        break;
    case "leaderboard":
        load_controller($data, API_CONTROLLERS . 'leaderboardController.php');
        break;
    case "login":
        load_controller($data, API_CONTROLLERS . 'loginController.php');
        break;
    case "registration":
        load_controller($data, API_CONTROLLERS . 'registrationController.php');
        break;
    case "profile":
        load_controller($data, API_CONTROLLERS . 'profileController.php');
        break;
    case "admin":
        load_controller($data, API_CONTROLLERS . 'adminController.php');
        break;
    case "logout":
        load_controller($data, API_CONTROLLERS . 'logoutController.php');
        break;
    case "guest":
        load_controller($data, API_CONTROLLERS . 'guestController.php');
        break;
        
    // ÚJ ISBANNED VÉGPONT
    case "isBanned":
        load_controller($data, API_CONTROLLERS . 'isBannedController.php');
        break;

    // ----------------------------------
    // UNKNOWN API ROUTE
    // ----------------------------------
    default:
        json_response(['error' => 'API endpoint not found'], 404);
}
?>