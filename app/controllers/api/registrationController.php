<?php

function getContent() {
    ob_start();
    require VIEWS . 'registration/registration.php';
    $buffer = ob_get_clean();

    $data = [
        "html"    => $buffer,
        "status"  => "success",
        "message" => ""
    ];

    json_response($data, 200);
}

function registerUser() {
    global $pdo; // A connect.php-ből jövő adatbázis kapcsolat

    // Adatok kinyerése (támogatja a JS Fetch API-t json formátumban)
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $passwordConfirm = $input['password_confirm'] ?? '';

    // Validációk
    if (empty($username) || empty($email) || empty($password)) {
        json_response(["status" => "error", "message" => "Minden mező kitöltése kötelező!"], 400);
    }
    if ($password !== $passwordConfirm) {
        json_response(["status" => "error", "message" => "A jelszavak nem egyeznek!"], 400);
    }

    try {
        // Biztonsági ellenőrzés: Létezik egyáltalán az adatbázis kapcsolat?
        if (!$pdo) {
            throw new Exception("Nincs adatbázis kapcsolat! (A \$pdo változó null)");
        }

        // Foglalt-e a név vagy az email? 
        $stmt = $pdo->prepare("SELECT user_id FROM User WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            json_response(["status" => "error", "message" => "Ez a felhasználónév vagy e-mail cím már foglalt!"], 409);
        }

        // Jelszó titkosítása
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $defaultJson = json_encode(new stdClass()); // '{}' üres JSON 
        
        // ALAPÉRTELMEZETT ID-k (Fontos: a Roles és Avatars táblában lennie kell ID=1-es sornak!)
        $defaultRoleId = 1; 
        $defaultAvatarId = 1; 

        // Adatbázis Tranzakció INDÍTÁSA
        $pdo->beginTransaction();

        // 1. Settings létrehozása
        $stmtSettings = $pdo->prepare("INSERT INTO Settings (settings_file) VALUES (?)");
        $stmtSettings->execute([$defaultJson]);
        $settingsId = $pdo->lastInsertId();

        // 2. User létrehozása
        $stmtUser = $pdo->prepare("INSERT INTO User (username, email, password, savestate_file, role_id, settings_id, avatar_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUser->execute([$username, $email, $hashedPassword, $defaultJson, $defaultRoleId, $settingsId, $defaultAvatarId]);
        $userId = $pdo->lastInsertId();

        // 3. Statisztika létrehozása
        $stmtStats = $pdo->prepare("INSERT INTO Statistics (user_id, statistics_file) VALUES (?, ?)");
        $stmtStats->execute([$userId, $defaultJson]);

        // Tranzakció mentése
        $pdo->commit();

        json_response(["status" => "success", "message" => "Sikeres regisztráció! Most már bejelentkezhetsz."], 201);

    } catch (Throwable $e) { // A Throwable MINDEN hibát megfog!
        // Csak akkor vonjuk vissza a tranzakciót, ha tényleg el lett indítva
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Itt végre szépen kiírjuk a valós hibát!
        json_response(["status" => "error", "message" => "Rendszerhiba: " . $e->getMessage()], 500);
    }
}

// Router logika a metódus alapján
switch ($data["method"]) {
    case 'GET': 
        getContent();                 
        break;
    case 'POST':
        registerUser();
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        break;
}
?>