<?php
// logoutController.php

function logout() {
    // 1. Töröljük a session adatokat
    $_SESSION = [];

    // 2. Megsemmisítjük a session-t
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // 3. Válaszolunk a JS-nek
    json_response(["status" => "success", "message" => "Logged out successfully"], 200);
}

// Csak POST-ot engedünk a biztonság kedvéért
if ($data["method"] === 'POST') {
    logout();
} else {
    json_response(["status" => "error", "message" => "Method not allowed"], 405);
}