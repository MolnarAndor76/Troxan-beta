<?php
function getContent() {
    ob_start(); require VIEWS . 'login/login.php'; $buffer = ob_get_clean();
    json_response(["html" => $buffer, "status" => "success"], 200);
}

function loginUser() {
    global $pdo;
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        json_response(["status" => "error", "message" => "Minden mező kötelező!"], 400);
    }

    try {
        // TISZTA LAP: Lekérjük a role_name-et is a Roles táblából!
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
            $_SESSION['role_name'] = $user['role_name'] ?? 'Player'; // Így már BIZTOSAN megvan!
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

switch ($data["method"]) {
    case 'GET': getContent(); break;
    case 'POST': loginUser(); break;
}
?>