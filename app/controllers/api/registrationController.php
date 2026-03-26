<?php

function getContent()
{
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

function verifyRegistrationCode()
{
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = trim($input['email'] ?? '');
    $code = trim($input['verification_code'] ?? '');

    if (empty($email) || empty($code)) {
        json_response(['status' => 'error', 'message' => 'Email and verification code are required.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['status' => 'error', 'message' => 'Invalid email address.'], 400);
    }

    try {
        $stmt = $pdo->prepare('SELECT user_id, is_verified, verification_code, verification_expires FROM `User` WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_response(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        if ($user['is_verified']) {
            json_response(['status' => 'success', 'message' => 'This account is already verified.'], 200);
        }

        if ($user['verification_code'] !== $code) {
            json_response(['status' => 'error', 'message' => 'Invalid verification code.'], 401);
        }

        if (!empty($user['verification_expires']) && strtotime($user['verification_expires']) < time()) {
            json_response(['status' => 'error', 'message' => 'The verification code has expired. Please register again.'], 403);
        }

        $update = $pdo->prepare('UPDATE `User` SET is_verified = 1, verification_code = NULL, verification_expires = NULL WHERE user_id = ?');
        $update->execute([$user['user_id']]);

        json_response(['status' => 'success', 'message' => 'Email verified successfully. You can now log in.'], 200);
    } catch (Throwable $e) {
        json_response(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()], 500);
    }
}

function registerUser()
{
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

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        json_response(["status" => "error", "message" => "All fields are required!"], 400);
    }

    // Username validation: 7-16 characters, letters and numbers only
    if (strlen($username) > 16 || strlen($username) < 7 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        json_response(["status" => "error", "message" => "Username must be 7-16 characters and contain only letters and numbers!"], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(["status" => "error", "message" => "Invalid email address format!"], 400);
    }

    // Password validation: Minimum 8 characters
    if (strlen($password) < 8) {
        json_response(["status" => "error", "message" => "Password must be at least 8 characters long!"], 400);
    }

    if ($password !== $passwordConfirm) {
        json_response(["status" => "error", "message" => "Passwords do not match!"], 400);
    }

    try {
        // Security check: Does the database connection exist?
        if (!$pdo) {
            throw new Exception("No database connection! (The \$pdo variable is null)");
        }

        // Is the username or email already taken?
        $stmt = $pdo->prepare("SELECT user_id FROM User WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            json_response(["status" => "error", "message" => "Username or email already taken!"], 409);
        }

        // Encrypt password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $defaultJson = json_encode(new stdClass()); // '{}' empty JSON

        // Default IDs (Important: Roles and Avatars tables must have ID=1 row!)
        $defaultRoleId = 1;
        $defaultAvatarId = 1;

        // Start database transaction
        $pdo->beginTransaction();

        // 1. Create Settings
        $stmtSettings = $pdo->prepare("INSERT INTO Settings (settings_file) VALUES (?)");
        $stmtSettings->execute([$defaultJson]);
        $settingsId = $pdo->lastInsertId();

        // 2. Create User
        $stmtUser = $pdo->prepare("INSERT INTO User (username, email, password, savestate_file, role_id, settings_id, avatar_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUser->execute([$username, $email, $hashedPassword, $defaultJson, $defaultRoleId, $settingsId, $defaultAvatarId]);
        $userId = $pdo->lastInsertId();

        // 3. Create Statistics
        $stmtStats = $pdo->prepare("INSERT INTO Statistics (user_id, statistics_file) VALUES (?, ?)");
        $stmtStats->execute([$userId, $defaultJson]);

        // 4. Generate email verification code
        $verificationCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $verificationExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmtUpdate = $pdo->prepare("UPDATE `User` SET is_verified = 0, verification_code = ?, verification_expires = ? WHERE user_id = ?");
        $stmtUpdate->execute([$verificationCode, $verificationExpires, $userId]);

        // Save transaction
        $pdo->commit();

        // 5. Send verification email
        $mailerPath = __DIR__ . '/../../mailer.php';
        if (file_exists($mailerPath)) {
            require_once $mailerPath;
            if (function_exists('sendTroxanMail')) {
                $subject = 'Troxan - Email Verification';
                $emailBody = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>"
                           . "<h2>Welcome to the Troxan universe!</h2>"
                           . "<p>Thank you for registering, <strong>{$username}</strong>!</p>"
                           . "<p>Enter the following code to verify your email:</p>"
                           . "<p style='font-size: 22px; font-weight: bold; color: #d97706;'>{$verificationCode}</p>"
                           . "<p>The code expires in 1 hour.</p>"
                           . "</div>";
                @sendTroxanMail($email, $subject, $emailBody);
            }
        }

        json_response(["status" => "success", "message" => "Registration successful! Please verify your email address with the code you received."], 201);
    } catch (Throwable $e) { // Throwable catches ALL errors!
        // Only rollback if transaction was actually started
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Output the actual error
        json_response(["status" => "error", "message" => "System error: " . $e->getMessage()], 500);
    }
}

// Router logic based on HTTP method
switch ($data["method"]) {
    case 'GET':
        getContent();
        break;
    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        if (isset($input['action']) && $input['action'] === 'verify_code') {
            verifyRegistrationCode();
        } else {
            registerUser();
        }
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        break;
}
