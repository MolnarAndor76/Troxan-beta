<?php

function getContent() {
    global $pdo;

    // ======================================================
    // BIZTONSÁGI ELLENŐRZÉS: Ha nincs belépve, a Guest oldalt kapja! uwu
    // ======================================================
    if (!isset($_SESSION['user_id'])) {
        ob_start();
        
        // Betöltjük a guest nézetet
        require VIEWS . 'guest/guest.php';
        
        $buffer = ob_get_clean();

        // Sikeres választ küldünk, hogy a JS szépen renderelje le a Guest oldalt
        json_response([
            "html"    => $buffer,
            "status"  => "success",
            "message" => "Redirected to guest"
        ], 200);
        
        return; // Megállítjuk a futást
    }

    try {
        $userId = $_SESSION['user_id'];

        // 1. Lekérjük a JELENLEGI JÁTÉKOS adatait
        $stmt = $pdo->prepare("
            SELECT u.username, u.created_at, r.role_name, a.avatar_picture 
            FROM `User` u 
            JOIN Roles r ON u.role_id = r.id 
            LEFT JOIN Avatars a ON u.avatar_id = a.id
            WHERE u.user_id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_response(["status" => "error", "message" => "User not found"], 404);
            return;
        }

        // 2. Lekérjük a LEGUTOLSÓ statisztikát a játékoshoz!
        $stmtStats = $pdo->prepare("
            SELECT statistics_file, last_updated 
            FROM `Statistics` 
            WHERE user_id = ? 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmtStats->execute([$userId]);
        $statsRow = $stmtStats->fetch(PDO::FETCH_ASSOC);

        $playerStats = [];
        $lastUpdated = null;

        if ($statsRow && !empty($statsRow['statistics_file'])) {
            $playerStats = json_decode($statsRow['statistics_file'], true) ?: [];
            $lastUpdated = $statsRow['last_updated'];
        }

        // 3. LEADERBOARD RANK KISZÁMÍTÁSA
        $stmtRank = $pdo->query("
            SELECT user_id, MAX(CAST(JSON_UNQUOTE(JSON_EXTRACT(statistics_file, '$.score')) AS UNSIGNED)) as max_score
            FROM `Statistics`
            GROUP BY user_id
            ORDER BY max_score DESC
        ");
        $allScores = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

        $leaderboardRank = '-';
        $currentRank = 1;
        
        foreach ($allScores as $row) {
            if ($row['user_id'] == $userId) {
                $leaderboardRank = $currentRank . '.';
                break;
            }
            $currentRank++;
        }

        // 4. Lekérjük az ÖSSZES választható avatart a modálhoz
        $stmt_avatars = $pdo->query("SELECT id, avatar_name, avatar_picture FROM Avatars");
        $all_avatars = $stmt_avatars->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require VIEWS . 'profile/profile.php';
        $buffer = ob_get_clean();

        json_response(["html" => $buffer, "status" => "success"], 200);

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "SQL hiba: " . $e->getMessage()], 500);
    }
}

function updateAvatar() {
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Nincs jogosultságod!"], 401);
        return;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $avatar_id = isset($input['avatar_id']) ? (int)$input['avatar_id'] : 0;

    if ($avatar_id === 0) {
        json_response(["status" => "error", "message" => "Érvénytelen avatar azonosító!"], 400);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `User` SET avatar_id = ? WHERE user_id = ?");
        $stmt->execute([$avatar_id, $_SESSION['user_id']]);
        json_response(["status" => "success", "message" => "Avatar sikeresen frissítve!"], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "SQL hiba: " . $e->getMessage()], 500);
    }
}

switch ($data["method"]) {
    case 'GET': 
        getContent();                 
        break;
    case 'POST': 
        updateAvatar();
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        break;
}
?>