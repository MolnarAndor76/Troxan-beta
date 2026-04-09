<?php

function getContent()
{
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        ob_start();
        require VIEWS . 'guest/guest.php';
        $buffer = ob_get_clean();
        json_response(["html" => $buffer, "status" => "success", "message" => "Redirected to guest"], 200);
        return; 
    }

    try {
        $userId = $_SESSION['user_id'];

        // 1. Lekérjük a JELENLEGI JÁTÉKOS adatait
        $stmt = $pdo->prepare("
            SELECT u.username, u.email, u.created_at, u.last_username_change, r.role_name, a.avatar_picture 
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
            SELECT s.user_id, MAX(CAST(JSON_UNQUOTE(JSON_EXTRACT(s.statistics_file, '$.score')) AS UNSIGNED)) as max_score
            FROM `Statistics` s
            JOIN `User` u ON s.user_id = u.user_id
            WHERE u.is_banned = 0
            GROUP BY s.user_id
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
        json_response(["status" => "error", "message" => "SQL error: " . $e->getMessage()], 500);
    }
}

function handlePostActionsLegacy()
{
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Unauthorized access."], 401);
        return;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? '';
    $userId = $_SESSION['user_id'];

    try {
        // ==========================================
        // AVATAR CSERE LOGIKA
        // ==========================================
        if ($action === 'change_avatar') {
            $avatar_id = isset($input['avatar_id']) ? (int)$input['avatar_id'] : 0;
            if ($avatar_id === 0) {
                json_response(["status" => "error", "message" => "Invalid avatar ID!"], 400);
                return;
            }
            $stmt = $pdo->prepare("UPDATE `User` SET avatar_id = ? WHERE user_id = ?");
            $stmt->execute([$avatar_id, $userId]);
            json_response(["status" => "success", "message" => "Avatar updated successfully!"], 200);
            
        } 
        // ==========================================
        // USERNAME CSERE LOGIKA (EMAIL KÜLDÉSSEL)
        // ==========================================
        elseif ($action === 'change_username') {
            $newUsername = trim($input['new_username'] ?? '');
            
            if (empty($newUsername)) {
                json_response(["status" => "error", "message" => "A név nem lehet üres!"], 400);
                return;
            }

            if (strtolower($newUsername) === strtolower($_SESSION['username'])) {
                json_response(["status" => "error", "message" => "Same username!"], 400);
                return;
            }

            $stmt = $pdo->prepare("SELECT u.email, u.last_username_change, r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $isEngineer = ($userData['role_name'] === 'Engineer');
            $userEmail = $userData['email'] ?? ''; 
            $oldUsername = $_SESSION['username'];  

            if (!$isEngineer) {
                if (strlen($newUsername) < 4 || strlen($newUsername) > 12) {
                    json_response(["status" => "error", "message" => "Username must be between 4 and 12 characters."], 400);
                    return;
                }
            }

            if (!$isEngineer && $userData['last_username_change'] !== null) {
                $lastTimestamp = strtotime($userData['last_username_change']);
                $cooldownDays = 30;
                $nextAvailable = $lastTimestamp + ($cooldownDays * 24 * 60 * 60);

                if (time() < $nextAvailable) {
                    $daysLeft = ceil(($nextAvailable - time()) / (24 * 60 * 60));
                    json_response(["status" => "error", "message" => "Még várnod kell {$daysLeft} napot a következő névváltásig!"], 403);
                    return;
                }
            }

            $checkName = $pdo->prepare("SELECT 1 FROM `User` WHERE username = ? AND user_id != ?");
            $checkName->execute([$newUsername, $userId]);
            if ($checkName->fetchColumn()) {
                json_response(["status" => "error", "message" => "Ez a név már foglalt!"], 400);
                return;
            }

            $update = $pdo->prepare("UPDATE `User` SET username = ?, last_username_change = NOW() WHERE user_id = ?");
            $update->execute([$newUsername, $userId]);
            $_SESSION['username'] = $newUsername;

            if (!empty($userEmail)) {
                $mailerPath = __DIR__ . '/../../mailer.php';
                if (file_exists($mailerPath)) {
                    require_once $mailerPath;
                    $subject = "Troxan - Username Changed";
                    
                    if (!$isEngineer) {
                        $nextDate = date('F j, Y', strtotime('+30 days')); 
                        $cooldownText = "<p style='color: #444; font-size: 14px;'>Your next name change will be available on: <strong>{$nextDate}</strong>.</p>";
                    } else {
                        $cooldownText = "<p style='color: #444; font-size: 14px;'><em>As an Engineer, you have no cooldown for future name changes.</em></p>";
                    }

                    $emailBody = "
                        <div style='font-family: \"Press Start 2P\", Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 4px solid #431407; border-radius: 10px; background-color: #fffaf0;'>
                            <h2 style='color: #d97706; text-align: center; border-bottom: 2px solid #431407; padding-bottom: 10px;'>Notice</h2>
                            <p style='font-size: 16px; color: #333; font-weight: bold;'>Hello {$oldUsername}!</p>
                            <p style='font-size: 14px; color: #333;'>You have successfully changed your Troxan username.</p>
                            
                            <div style='background-color: #fff; padding: 15px; border-radius: 5px; border: 2px dashed #ccc; margin: 20px 0;'>
                                <p style='margin: 5px 0; font-size: 14px;'>Old username: <span style='color: #dc2626; font-weight: bold;'>{$oldUsername}</span></p>
                                <p style='margin: 5px 0; font-size: 14px;'>New username: <span style='color: #16a34a; font-weight: bold;'>{$newUsername}</span></p>
                            </div>
                            
                            {$cooldownText}
                            
                            <p style='font-size: 11px; color: #888; margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;'>
                                If you did not request this change, please contact an Administrator immediately!
                            </p>
                        </div>
                    ";

                    if (function_exists('sendTroxanMail')) {
                        @sendTroxanMail($userEmail, $subject, $emailBody);
                    }
                }
            }

            json_response(["status" => "success", "message" => "Username changed successfully to: {$newUsername}!"], 200);
            
        } 
        // ==========================================
        // JELSZÓ CSERE LOGIKA (BELSŐ HIBAÜZENETEK + KILÉPTETÉS)
        // ==========================================
        elseif ($action === 'change_password') {
            $oldPass = $input['old_password'] ?? '';
            $newPass = $input['new_password'] ?? '';
            $confirmPass = $input['confirm_password'] ?? '';

            if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
                json_response(["status" => "error", "message" => "Please fill in all fields!"], 400);
                return;
            }

            // 0. Lekérjük a DB-ből a user adatait
            $stmt = $pdo->prepare("SELECT password, email, username FROM `User` WHERE user_id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // 1. PRIORITÁS: Rossz a régi jelszó!
            if (!$userData || !password_verify($oldPass, $userData['password'])) {
                json_response(["status" => "error", "message" => "Incorrect current password!"], 400);
                return;
            }

            // ÚJ BÓNUSZ PRIORITÁS: Az új jelszó nem lehet azonos a régivel!
            if ($oldPass === $newPass) {
                json_response(["status" => "error", "message" => "New password cannot be the same as the current one!"], 400);
                return;
            }

            // 2. PRIORITÁS: Túl rövid az új jelszó!
            if (strlen($newPass) < 8) {
                json_response(["status" => "error", "message" => "New password must be at least 8 characters long!"], 400);
                return;
            }

            // 3. PRIORITÁS: Nem egyezik a két új jelszó!
            if ($newPass !== $confirmPass) {
                json_response(["status" => "error", "message" => "New passwords do not match!"], 400);
                return;
            }

            // Ha ide eljutott, minden hibátlan! Titkosítjuk és mentjük!
            $newHashedPass = password_hash($newPass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE `User` SET password = ? WHERE user_id = ?");
            $update->execute([$newHashedPass, $userId]);

            // BIZTONSÁGI EMAIL KIKÜLDÉSE
            $userEmail = $userData['email'] ?? '';
            if (!empty($userEmail)) {
                $mailerPath = __DIR__ . '/../../mailer.php';
                if (file_exists($mailerPath)) {
                    require_once $mailerPath;
                    $subject = "Troxan - Security Alert: Password Changed";
                    $username = $userData['username'];
                    
                    $emailBody = "
                        <div style='font-family: \"Press Start 2P\", Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 4px solid #431407; border-radius: 10px; background-color: #fffaf0;'>
                            <h2 style='color: #dc2626; text-align: center; border-bottom: 2px solid #431407; padding-bottom: 10px;'>Security Alert</h2>
                            <p style='font-size: 16px; color: #333; font-weight: bold;'>Hello {$username}!</p>
                            <p style='font-size: 14px; color: #333;'>The password for your Troxan account has just been changed.</p>
                            <p style='font-size: 14px; color: #333;'>If you made this change, you can safely ignore this email.</p>
                            
                            <p style='font-size: 12px; color: #dc2626; font-weight: bold; margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;'>
                                If you did NOT request this change, your account may be compromised. Please contact an Administrator immediately!
                            </p>
                        </div>
                    ";
                    
                    if (function_exists('sendTroxanMail')) {
                        @sendTroxanMail($userEmail, $subject, $emailBody);
                    }
                }
            }

            // Visszaszólunk a JS-nek, hogy sikerült
            json_response(["status" => "success", "message" => "Password changed!"], 200);
            
        } 
        else {
            json_response(["status" => "error", "message" => "Unknown action!"], 400);
        }
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "SQL error: " . $e->getMessage()], 500);
    }
}

function updateProfile()
{
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Unauthorized access."], 401);
        return;
    }

    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    $userId = $_SESSION['user_id'];
    $updates = [];
    $params = [];

    try {
        // Avatar change
        if (isset($input['avatar_id'])) {
            $avatar_id = (int)$input['avatar_id'];
            if ($avatar_id <= 0) {
                json_response(["status" => "error", "message" => "Invalid avatar ID!"], 400);
            }
            $updates[] = "avatar_id = ?";
            $params[] = $avatar_id;
        }

        // Username change
        if (isset($input['username'])) {
            $newUsername = trim($input['username']);
            if (empty($newUsername)) {
                json_response(["status" => "error", "message" => "A név nem lehet üres!"], 400);
            }

            if (strtolower($newUsername) === strtolower($_SESSION['username'])) {
                json_response(["status" => "error", "message" => "Same username!"], 400);
            }

            if (strlen($newUsername) < 4 || strlen($newUsername) > 12) {
                json_response(["status" => "error", "message" => "Username must be between 4 and 12 characters."], 400);
            }

            $checkName = $pdo->prepare("SELECT 1 FROM `User` WHERE username = ? AND user_id != ?");
            $checkName->execute([$newUsername, $userId]);
            if ($checkName->fetchColumn()) {
                json_response(["status" => "error", "message" => "Ez a név már foglalt!"], 409);
            }

            $updates[] = "username = ?";
            $params[] = $newUsername;
            $_SESSION['username'] = $newUsername;
        }

        // Password change
        if (isset($input['old_password']) || isset($input['new_password']) || isset($input['confirm_password'])) {
            $oldPass = $input['old_password'] ?? '';
            $newPass = $input['new_password'] ?? '';
            $confirmPass = $input['confirm_password'] ?? '';

            if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
                json_response(["status" => "error", "message" => "Please fill in all fields!"], 400);
            }

            if ($newPass !== $confirmPass) {
                json_response(["status" => "error", "message" => "New passwords do not match!"], 400);
            }

            if (strlen($newPass) < 8) {
                json_response(["status" => "error", "message" => "New password must be at least 8 characters long!"], 400);
            }

            $stmt = $pdo->prepare("SELECT password FROM `User` WHERE user_id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData || !password_verify($oldPass, $userData['password'])) {
                json_response(["status" => "error", "message" => "Incorrect current password!"], 400);
            }

            $newHashedPass = password_hash($newPass, PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $newHashedPass;
        }

        if (empty($updates)) {
            json_response(["status" => "error", "message" => "Nothing to update."], 400);
        }

        $params[] = $userId;
        $sql = "UPDATE `User` SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        json_response(["status" => "success", "message" => "Profile updated successfully."], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "SQL error: " . $e->getMessage()], 500);
    }
}

function deleteProfile()
{
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Unauthorized access."], 401);
        return;
    }

    $userId = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $confirmText = strtoupper(trim($input['confirm_text'] ?? ''));

    if ($confirmText !== 'CONFIRM') {
        json_response(["status" => "error", "message" => "Type CONFIRM to delete your profile."], 400);
        return;
    }

    $transactionStarted = false;

    try {
        if (!$pdo->inTransaction()) {
            try {
                $transactionStarted = $pdo->beginTransaction();
            } catch (Exception $txe) {
                $transactionStarted = false;
            }
        }

        $userStmt = $pdo->prepare("SELECT user_id FROM `User` WHERE user_id = ? LIMIT 1");
        $userStmt->execute([$userId]);
        $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_response(["status" => "error", "message" => "User not found."], 404);
            return;
        }

        $mapIdsStmt = $pdo->prepare("SELECT id FROM `Maps` WHERE creator_user_id = ?");
        $mapIdsStmt->execute([$userId]);
        $createdMapIds = $mapIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($createdMapIds)) {
            $placeholders = implode(',', array_fill(0, count($createdMapIds), '?'));
            $pdo->prepare("DELETE FROM `User_Map_Library` WHERE map_id IN ($placeholders)")->execute($createdMapIds);
            $pdo->prepare("DELETE FROM `Maps` WHERE id IN ($placeholders)")->execute($createdMapIds);
        }

        $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM `Statistics` WHERE user_id = ?")->execute([$userId]);

        try {
            $pdo->prepare("DELETE FROM `Active_Web_Sessions` WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Optional cleanup table may not exist in some environments.
        }

        $stmt = $pdo->prepare("DELETE FROM `User` WHERE user_id = ?");
        $stmt->execute([$userId]);

        if ($transactionStarted && $pdo->inTransaction()) {
            $pdo->commit();
        }

        session_unset();
        session_destroy();

        json_response(["status" => "success", "message" => "User account deleted."], 200);
    } catch (Exception $e) {
        if ($transactionStarted && $pdo->inTransaction()) $pdo->rollBack();
        json_response(["status" => "error", "message" => "SQL error: " . $e->getMessage()], 500);
    }
}

function handlePostActions()
{
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    if (isset($input['action']) && !empty($input['action'])) {
        // legacy action-based POST
        handlePostActionsLegacy();
        return;
    }

    // RESTful fallback: treat POST as update for compatibility
    updateProfile();
}

// ==========================================
// ROUTING
// ==========================================
switch ($data["method"]) {
    case 'GET':
        getContent();
        break;
    case 'POST':
        handlePostActions();
        break;
    case 'PUT':
        updateProfile();
        break;
    case 'PATCH':
        handlePostActionsLegacy();
        break;
    case 'DELETE':
        deleteProfile();
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405);
        break;
}
?>