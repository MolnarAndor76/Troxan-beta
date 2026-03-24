<?php
function handleGameStats()
{
    global $pdo;

    // 1. Csak GET kérés
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        return;
    }

    // 2. GOLYÓÁLLÓ TOKEN KIOLVASÁS (Megkerüli az Apache/PHP korlátozásait)
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

    // Ha még így sincs token:
    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        json_response(["status" => "error", "message" => "Hiányzó vagy érvénytelen token! Jelentkezz be újra!"], 401);
        return;
    }

    $token = $matches[1];

    try {
        // 3. Megkeressük a játékost
        $stmt = $pdo->prepare("SELECT username, coins, level, is_banned FROM `User` WHERE user_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_response(["status" => "error", "message" => "Érvénytelen vagy lejárt token!"], 401);
            return;
        }

        if ($user['is_banned'] == 1) {
            json_response(["status" => "error", "message" => "A fiókod ki van tiltva!"], 403);
            return;
        }

        // 4. PONTOSAN EZT VÁRJA A COPILOT (szigorú JSON formátum)
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "username" => $user['username'],
            "coins" => (int)$user['coins'],
            "level" => (int)$user['level']
        ]);
        exit();

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500);
    }
}
?>