<?php
function getContent() {
    ob_start(); require VIEWS . 'login/login.php'; $buffer = ob_get_clean();
    json_response(["html" => $buffer, "status" => "success"], 200);
}

// 1. A sima bejelentkezés funkció
function loginUser($input) {
    global $pdo;
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        json_response(["status" => "error", "message" => "Minden mező kötelező!"], 400);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.username, u.password, r.role_name, a.avatar_picture 
            FROM `User` u
            LEFT JOIN `Avatars` a ON u.avatar_id = a.id
            LEFT JOIN `Roles` r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role_name'] = $user['role_name'] ?? 'Player';
            $_SESSION['logged_in'] = true;
            session_write_close();

            $avatar_base64 = 'https://picsum.photos/id/1025/200/200';
            if (!empty($user['avatar_picture'])) {
                $avatar_base64 = 'data:image/jpeg;base64,' . base64_encode($user['avatar_picture']);
            }
            json_response(["status" => "success", "user" => ["username" => $user['username'], "avatar" => $avatar_base64]], 200);
        } else {
            json_response(["status" => "error", "message" => "Hibás e-mail vagy jelszó!"], 401);
        }
    } catch (Throwable $e) { json_response(["status" => "error", "message" => $e->getMessage()], 500); }
}

// 2. Az ÚJ Elfelejtett Jelszó funkció
function forgotPassword($input) {
    global $pdo;
    $email = trim($input['forgot_email'] ?? '');

    // PHP E-mail validáció (Formátum ellenőrzés)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(["status" => "error", "message" => "Invalid email"], 400);
    }

    try {
        // Ellenőrizzük, hogy létezik-e az adatbázisban
        $stmt = $pdo->prepare("SELECT user_id FROM `User` WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Ha nem található az adatbázisban
            json_response(["status" => "error", "message" => "Invalid email"], 404);
        }

        // Ide jön majd később a tényleges E-mail küldés (Token generálással)!
        // Egyelőre csak visszaadjuk a sikert.
        json_response(["status" => "success", "message" => "Reset link sent to your email!"], 200);

    } catch (Throwable $e) { 
        json_response(["status" => "error", "message" => "Server error"], 500); 
    }
}

// Router logika
$input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

switch ($data["method"]) {
    case 'GET': 
        getContent(); 
        break;
    case 'POST': 
        // Megnézzük, van-e 'action' flag. Ha igen, és 'forgot_password', akkor oda küldjük.
        if (isset($input['action']) && $input['action'] === 'forgot_password') {
            forgotPassword($input);
        } else {
            loginUser($input); 
        }
        break;
}
?>