<?php
function handleGameUpdateStats()
{
    global $pdo;

    if (!function_exists('troxan_stats_pick_int')) {
        function troxan_stats_pick_int($stats, array $keys, $default = 0)
        {
            if (!is_array($stats)) {
                return (int)$default;
            }

            foreach ($keys as $key) {
                if (!array_key_exists($key, $stats)) {
                    continue;
                }

                $value = $stats[$key];
                if ($value === null) {
                    continue;
                }

                if (is_string($value) && trim($value) === '') {
                    continue;
                }

                return (int)$value;
            }

            return (int)$default;
        }
    }

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

            $prevStmt = $pdo->prepare("SELECT statistics_file, last_updated FROM `Statistics` WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $prevStmt->execute([$user['user_id']]);
            $prevRow = $prevStmt->fetch(PDO::FETCH_ASSOC);
            $previousStats = [];

            if ($prevRow && !empty($prevRow['statistics_file'])) {
                $decodedPrev = json_decode($prevRow['statistics_file'], true);
                if (is_array($decodedPrev)) {
                    $previousStats = $decodedPrev;
                }
            }

            $mergedStats = array_merge($previousStats, $incomingStats);

            $counterMap = [
                'num_of_story_finished' => ['num_of_story_finished', 'Story finished'],
                'num_of_enemies_killed' => ['num_of_enemies_killed', 'Mobs killed'],
                'num_of_deaths' => ['num_of_deaths', 'Deaths'],
                'score' => ['score', 'Experience points']
            ];

            $previousSnapshot = [];
            if (isset($previousStats['_meta_last_snapshot']) && is_array($previousStats['_meta_last_snapshot'])) {
                $previousSnapshot = $previousStats['_meta_last_snapshot'];
            }

            $nextSnapshot = [];
            foreach ($counterMap as $canonicalKey => $aliases) {
                $incomingValue = troxan_stats_pick_int($incomingStats, $aliases, 0);
                $previousTotal = troxan_stats_pick_int($previousStats, $aliases, 0);
                $previousSeen = isset($previousSnapshot[$canonicalKey]) ? (int)$previousSnapshot[$canonicalKey] : null;

                if ($previousSeen === null) {
                    $delta = $incomingValue;
                } elseif ($incomingValue >= $previousSeen) {
                    $delta = $incomingValue - $previousSeen;
                } else {
                    // Counter reset between sessions/game launches.
                    $delta = $incomingValue;
                }

                if ($delta < 0) {
                    $delta = 0;
                }

                $newTotal = $previousTotal + $delta;
                $nextSnapshot[$canonicalKey] = $incomingValue;

                $mergedStats[$canonicalKey] = $newTotal;
            }

            // Keep legacy aliases synchronized so all views read consistent values.
            $mergedStats['Story finished'] = $mergedStats['num_of_story_finished'];
            $mergedStats['Mobs killed'] = $mergedStats['num_of_enemies_killed'];
            $mergedStats['Deaths'] = $mergedStats['num_of_deaths'];
            $mergedStats['Experience points'] = $mergedStats['score'];

            $mergedStats['_meta_last_snapshot'] = $nextSnapshot;

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