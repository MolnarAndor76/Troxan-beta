<?php
function handleGameLogin()
{
    global $pdo;

    // 1. Csak POST kérést fogadunk el a játéktól
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        return;
    }

    // 2. Olvassuk a C# által küldött JSON-t (amit a Copilot írt)
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Kiszedjük a username-t és a password-ot
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        json_response(["status" => "error", "message" => "Hiányzó felhasználónév vagy jelszó!"], 400);
        return;
    }

    try {
        // 3. Megkeressük a játékost az adatbázisban
        $stmt = $pdo->prepare("SELECT user_id, username, password, is_banned FROM `User` WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Ellenőrizzük a jelszót
        if (!$user || !password_verify($password, $user['password'])) {
            json_response(["status" => "error", "message" => "Hibás felhasználónév vagy jelszó!"], 401);
            return;
        }

        // 5. Ellenőrizzük, hogy nincs-e kitiltva
        if ($user['is_banned'] == 1) {
            json_response(["status" => "error", "message" => "A fiókod ki van tiltva a szerverről!"], 403);
            return;
        }

        // 6. GENERÁLJUK A VIP BELÉPŐKÁRTYÁT (Token) a C#-nak
        $token = bin2hex(random_bytes(32));

        // 7. Elmentjük az adatbázisba a tokent, és frissítjük az "utoljára online" időt
        $updateStmt = $pdo->prepare("UPDATE `User` SET user_token = ?, last_time_online = NOW() WHERE user_id = ?");
        $updateStmt->execute([$token, $user['user_id']]);

        // 8. Visszaküldjük a C#-nak a sikeres válasz csomagot!
        json_response([
            "status" => "success",
            "message" => "Login successful!",
            "data" => [
                "user_id" => $user['user_id'],
                "username" => $user['username'],
                "token" => $token // EZT FOGJA A COPILOT ELMENTENI A JÁTÉKBAN!
            ]
        ], 200);

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error."], 500);
    }
}
?>