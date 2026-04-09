<?php
// logoutController.php

function logout() {
    global $pdo;

    $userId = $_SESSION['user_id'] ?? null;
    $sessionToken = $_SESSION['web_session_token'] ?? null;

    if (!empty($userId) && !empty($sessionToken)) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `Active_Web_Sessions` (
                `user_id` INT NOT NULL PRIMARY KEY,
                `session_token` VARCHAR(128) NOT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stmt = $pdo->prepare("DELETE FROM `Active_Web_Sessions` WHERE user_id = ? AND session_token = ?");
            $stmt->execute([$userId, $sessionToken]);
        } catch (Throwable $e) {
            // best effort cleanup
        }
    }

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