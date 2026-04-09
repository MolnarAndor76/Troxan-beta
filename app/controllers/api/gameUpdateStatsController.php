<?php
function handleGameUpdateStats()
{
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        return;
    }

    // GOLYÓÁLLÓ TOKEN KIOLVASÁS
    $authHeader = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $authHeader = trim($requestHeaders['Authorization']);
        }
    }

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        json_response(["status" => "error", "message" => "Missing or invalid token."], 401);
        return;
    }

    $token = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        json_response(["status" => "error", "message" => "Invalid JSON format."], 400);
        return;
    }

    try {
        // 1. Játékos lekérése TOKEN alapján
        $stmt = $pdo->prepare("SELECT user_id, username, is_banned FROM `User` WHERE user_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_response(["status" => "error", "message" => "Invalid or expired token."], 401);
            return;
        }

        if ($user['is_banned'] == 1) {
            json_response(["status" => "error", "message" => "Your account is banned."], 403);
            return;
        }

        if (isset($input['username']) && $input['username'] !== $user['username']) {
            json_response(["status" => "error", "message" => "Cheat detected: You cannot modify another player's stats!"], 403);
            return;
        }

        // 2. USER TÁBLA FRISSÍTÉSE (coins, level)
        $coins = isset($input['coins']) ? (int)$input['coins'] : 0;
        $level = isset($input['level']) ? (int)$input['level'] : 1;
        
        $updateUser = $pdo->prepare("UPDATE `User` SET coins = ?, level = ? WHERE user_id = ?");
        $updateUser->execute([$coins, $level, $user['user_id']]);

// 3. STATISTICS TÁBLA FRISSÍTÉSE (ÖSSZEGZETT statokkal, minden mentés új sor)
        if (isset($input['statistics'])) {
            $incomingStats = is_array($input['statistics']) ? $input['statistics'] : [];

            $prevStmt = $pdo->prepare("SELECT statistics_file FROM `Statistics` WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $prevStmt->execute([$user['user_id']]);
            $prevRow = $prevStmt->fetch(PDO::FETCH_ASSOC);
            $previousStats = [];

            if ($prevRow && !empty($prevRow['statistics_file'])) {
                $decodedPrev = json_decode($prevRow['statistics_file'], true);
                if (is_array($decodedPrev)) {
                    $previousStats = $decodedPrev;
                }
            }

            $sumKeys = [
                'num_of_story_finished',
                'num_of_enemies_killed',
                'num_of_deaths',
                'score',
                'Story finished',
                'Mobs killed',
                'Deaths',
                'Experience points'
            ];

            $mergedStats = array_merge($previousStats, $incomingStats);
            foreach ($sumKeys as $key) {
                $prevVal = isset($previousStats[$key]) ? (int)$previousStats[$key] : 0;
                $newVal = isset($incomingStats[$key]) ? (int)$incomingStats[$key] : 0;
                if (isset($previousStats[$key]) || isset($incomingStats[$key])) {
                    $mergedStats[$key] = $prevVal + $newVal;
                }
            }

            $statsJson = json_encode($mergedStats);
            
            $insertStat = $pdo->prepare("INSERT INTO `Statistics` (user_id, statistics_file, last_updated) VALUES (?, ?, NOW())");
            $insertStat->execute([$user['user_id'], $statsJson]);
        }

        // Siker válasz a játéknak!
        json_response([
            "status" => "success",
            "message" => "Stats updated successfully!"
        ], 200);

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}
?>