<?php

function getContent()
{
    global $pdo;

    // SZIGORÚ ELLENŐRZÉS: Csak akkor van current_user, ha létezik a user_id ÉS a logged_in flag is true!
    $current_user_id = null;
    if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $current_user_id = $_SESSION['user_id'];
    }

    try {
        // A VARÁZSLAT: Csak a legmagasabb ID-jú statisztikát kérjük le a NEM BANNOLT felhasználókhoz!
        $stmt = $pdo->query("
            SELECT u.user_id, u.username, s.statistics_file 
            FROM `User` u 
            LEFT JOIN `Statistics` s ON u.user_id = s.user_id 
                AND s.id = (
                    SELECT MAX(id) 
                    FROM `Statistics` s2 
                    WHERE s2.user_id = u.user_id
                )
            WHERE u.is_banned = 0   /* <--- EZ AZ ÚJ SOR, AMI KISZŰRI A BANNOLTAKAT! */
        ");
        $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $leaderboard_data = [];

        foreach ($all_users as $user) {
            $stats = [];
            if (!empty($user['statistics_file'])) {
                $stats = json_decode($user['statistics_file'], true) ?: [];
            }

            // Az új JSON formátum alapján a "score" kulcsot keressük!
            $score = isset($stats['score']) ? (int)$stats['score'] : 0;

            $leaderboard_data[] = [
                'user_id'  => $user['user_id'],
                'username' => $user['username'],
                'score'    => $score
            ];
        }

        // Sorbarendezés csökkenő sorrendben a pontszám alapján
        usort($leaderboard_data, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $current_user_data = null;

        // Helyezések (Rank) kiosztása
        foreach ($leaderboard_data as $index => &$data) {
            $data['rank'] = $index + 1;

            // Csak akkor mentjük el a jelenlegi usert a legalsó sorhoz, ha TÉNYLEG be van lépve
            if ($current_user_id !== null && $data['user_id'] == $current_user_id) {
                $current_user_data = $data;
            }
        }

        // Levágjuk a top 10-et a fő táblázathoz
        $top_10 = array_slice($leaderboard_data, 0, 10);

        // --- ÚJ: A legutolsó frissítés idejének lekérése az egész rendszerből ---
        $stmtDate = $pdo->query("SELECT MAX(last_updated) FROM `Statistics`");
        $latestUpdateRaw = $stmtDate->fetchColumn();
        $lastUpdatedText = $latestUpdateRaw ? date('Y.m.d H:i', strtotime($latestUpdateRaw)) : 'Never';
        // ------------------------------------------------------------------------
        ob_start();

        // A leaderboard.php látni fogja a $top_10 és a $current_user_data változókat!
        require VIEWS . 'leaderboard/leaderboard.php';

        $buffer = ob_get_clean();

        json_response([
            "html"    => $buffer,
            "status"  => "success",
            "message" => "Leaderboard loaded"
        ], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Rendszerhiba: " . $e->getMessage()], 500);
    }
}

switch ($data["method"]) {
    case 'GET':
        getContent();
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        break;
}
