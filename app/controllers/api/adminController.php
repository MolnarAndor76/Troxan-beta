<?php

// ==========================================
// 1. OLDAL BETÖLTÉSE (GET)
// ==========================================
function getContent() {
    global $pdo;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        json_response(["status" => "error", "message" => "Unauthorized access."], 401);
        return;
    }

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        if (!in_array($checkStmt->fetchColumn(), ['Admin', 'Engineer'])) {
            json_response(["status" => "error", "message" => "Only Admins and Engineers can access this area."], 403);
            return;
        }

        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

        $hasLastUsername = $pdo->query("SHOW COLUMNS FROM `User` LIKE 'last_username_change'")->rowCount() > 0;
        $hasLastPassword = $pdo->query("SHOW COLUMNS FROM `User` LIKE 'last_password_change'")->rowCount() > 0;

        $lastUsernameSelect = $hasLastUsername ? 'u.last_username_change' : 'NULL';
        $lastPasswordSelect = $hasLastPassword ? 'u.last_password_change' : 'NULL';

        $query = "
            SELECT u.user_id, u.username, u.email, u.created_at, u.last_time_online, u.is_banned,
                   u.role_id, r.role_name, a.avatar_picture, s.statistics_file, {$lastUsernameSelect} as last_username_change, {$lastPasswordSelect} as last_password_change
            FROM `User` u
            JOIN Roles r ON u.role_id = r.id
            LEFT JOIN Avatars a ON u.avatar_id = a.id
            LEFT JOIN `Statistics` s ON u.user_id = s.user_id 
                AND s.id = (
                    SELECT MAX(id) 
                    FROM `Statistics` s2 
                    WHERE s2.user_id = u.user_id
                )
        ";

        $params = [];
        if (!empty($searchTerm)) {
            $query .= " WHERE u.username LIKE ?";
            $params[] = "%" . $searchTerm . "%";
        }

        $query .= " ORDER BY r.id DESC, u.username ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require VIEWS . 'admin/admin.php';
        $buffer = ob_get_clean();

        json_response(["html" => $buffer, "status" => "success"], 200);

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}

// ==========================================
// 2. BAN / UNBAN LOGIKA (POST)
// ==========================================
function toggleBan() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error", "message" => "Unauthorized access."], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    
    $targetUserId = (int)$input['target_user_id'];
    $reason = trim($input['reason'] ?? '');
    $myUserId = $_SESSION['user_id'];

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$myUserId]);
        $myRole = $checkStmt->fetchColumn();

        if (!in_array($myRole, ['Admin', 'Engineer'])) { json_response(["status" => "error", "message" => "Only Admins and Engineers can use this feature."], 403); return; }
        if ($targetUserId === $myUserId) { json_response(["status" => "error", "message" => "You cannot ban yourself."], 400); return; }

        $statusStmt = $pdo->prepare("SELECT u.is_banned, r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $statusStmt->execute([$targetUserId]);
        $targetData = $statusStmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetData) { json_response(["status" => "error", "message" => "Player not found."], 404); return; }
        if ($targetData['role_name'] === 'Engineer') { json_response(["status" => "error", "message" => "Engineers cannot be banned."], 403); return; }
        if ($targetData['role_name'] === 'Admin' && $myRole !== 'Engineer') { json_response(["status" => "error", "message" => "A Engineer is required to ban Admins."], 403); return; }

        $newStatus = ($targetData['is_banned'] == 1) ? 0 : 1;
        if ($newStatus === 1 && empty($reason)) {
            json_response(["status" => "error", "message" => "Ban reason is required."], 400);
            return;
        }

        $pdo->prepare("UPDATE `User` SET is_banned = ? WHERE user_id = ?")->execute([$newStatus, $targetUserId]);

        // Email értesítés a cél felhasználónak, ha van email cím
        $targetEmailStmt = $pdo->prepare("SELECT email, username FROM `User` WHERE user_id = ?");
        $targetEmailStmt->execute([$targetUserId]);
        $targetInfo = $targetEmailStmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($targetInfo['email'])) {
            $mailerPath = __DIR__ . '/../../mailer.php';
            if (file_exists($mailerPath)) {
                require_once $mailerPath;
                $subject = $newStatus == 1 ? "Troxan - You were banned" : "Troxan - You were unbanned";
                $body = "<h2>Account status changed</h2>";
                $body .= "<p>Your account (<strong>".htmlspecialchars($targetInfo['username'])."</strong>) has been " . ($newStatus == 1 ? 'banned' : 'unbanned') . " by an administrator.</p>";
                if (!empty($reason)) {
                    $body .= "<p>Reason: " . htmlspecialchars($reason) . "</p>";
                }
                $body .= "<p>If this was not expected, contact support immediately.</p>";
                @sendTroxanMail($targetInfo['email'], $subject, $body);
            }
        }

        json_response(["status" => "success", "message" => ($newStatus == 1) ? "Player banned successfully." : "Player unbanned successfully."], 200);
    } catch (Exception $e) { json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500); }
}

// ==========================================
// 3. ROLE CHANGE (POST)
// ==========================================
function changeRole() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error", "message" => "Unauthorized access."], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    
    $targetUserId = (int)$input['target_user_id'];
    $roleAction = $input['role_action']; 
    $myUserId = $_SESSION['user_id'];

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$myUserId]);
        $myRole = $checkStmt->fetchColumn();

        if (!in_array($myRole, ['Admin', 'Engineer'])) { json_response(["status" => "error", "message" => "Only Admins and Engineers can use this feature."], 403); return; }
        if ($targetUserId === $myUserId) { json_response(["status" => "error", "message" => "You cannot modify your own role."], 400); return; }

        $targetStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $targetStmt->execute([$targetUserId]);
        $currentRole = $targetStmt->fetchColumn();

        if ($currentRole === 'Engineer') { json_response(["status" => "error", "message" => "Engineers cannot have their role changed."], 403); return; }
        if ($currentRole === 'Admin' && $myRole !== 'Engineer') { json_response(["status" => "error", "message" => "Only an Engineer can change another Admin's role."], 403); return; }

        $newRoleName = '';
        if ($roleAction === 'promote') {
            if ($currentRole === 'Player') $newRoleName = 'Moderator';
            elseif ($currentRole === 'Moderator') {
                if ($myRole !== 'Engineer') { json_response(["status" => "error", "message" => "Only an Engineer can promote a Moderator to Admin."], 403); return; }
                $newRoleName = 'Admin';
            } else { json_response(["status" => "error", "message" => "Őt már nem lehet feljebb léptetni!"], 400); return; }
        } elseif ($roleAction === 'demote') {
            if ($currentRole === 'Admin') $newRoleName = 'Moderator';
            elseif ($currentRole === 'Moderator') $newRoleName = 'Player';
            else { json_response(["status" => "error", "message" => "Őt már nem lehet lejjebb fokozni!"], 400); return; }
        }

        $roleIdStmt = $pdo->prepare("SELECT id FROM Roles WHERE role_name = ?");
        $roleIdStmt->execute([$newRoleName]);
        $newRoleId = $roleIdStmt->fetchColumn();

        $pdo->prepare("UPDATE `User` SET role_id = ? WHERE user_id = ?")->execute([$newRoleId, $targetUserId]);
        json_response(["status" => "success", "message" => "Role changed successfully. New role: " . $newRoleName], 200);
    } catch (Exception $e) { json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500); }
}

function changeUserName() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error", "message" => "Unauthorized access."], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);

    $targetUserId = (int)$input['target_user_id'];
    $newUsername = trim($input['new_username'] ?? '');
    $reason = trim($input['reason'] ?? '');
    $adminUserId = $_SESSION['user_id'];

    if (empty($newUsername)) { json_response(["status" => "error", "message" => "New username cannot be empty."], 400); return; }
    if (strlen($newUsername) < 4 || strlen($newUsername) > 12) { json_response(["status" => "error", "message" => "Username must be between 4 and 12 characters."], 400); return; }
    if (empty($reason)) { json_response(["status" => "error", "message" => "Reason is required."], 400); return; }

    $myRoleStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
    $myRoleStmt->execute([$adminUserId]);
    $myRole = $myRoleStmt->fetchColumn();
    if ($myRole !== 'Engineer') { json_response(["status" => "error", "message" => "Only Engineers can change usernames for others."], 403); return; }

    $targetStmt = $pdo->prepare("SELECT u.username, u.email, r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
    $targetStmt->execute([$targetUserId]);
    $targetData = $targetStmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetData) { json_response(["status" => "error", "message" => "Target user not found."], 404); return; }
    if ($targetData['role_name'] === 'Engineer') { json_response(["status" => "error", "message" => "Engineers' names cannot be changed."], 403); return; }

    $checkName = $pdo->prepare("SELECT 1 FROM `User` WHERE username = ? AND user_id != ?");
    $checkName->execute([$newUsername, $targetUserId]);
    if ($checkName->fetchColumn()) { json_response(["status" => "error", "message" => "That username is already taken."], 400); return; }

    $updateSql = "UPDATE `User` SET username = ?, last_username_change = NOW() WHERE user_id = ?";
    if (!$pdo->query("SHOW COLUMNS FROM `User` LIKE 'last_username_change'")->rowCount()) {
        $updateSql = "UPDATE `User` SET username = ? WHERE user_id = ?";
    }

    try {
        $pdo->prepare($updateSql)->execute([$newUsername, $targetUserId]);

        // Email értesítés a cél felhasználónak
        $targetEmail = $targetData['email'] ?? '';
        if (!empty($targetEmail)) {
            $mailerPath = __DIR__ . '/../../mailer.php';
            if (file_exists($mailerPath)) {
                require_once $mailerPath;
                $subject = "Troxan - Username Changed by Engineer";
                $body = "<h2>Username change</h2>";
                $body .= "<p>Your username has been changed from <strong>". htmlspecialchars($targetData['username']) ."</strong> to <strong>". htmlspecialchars($newUsername) ."</strong> by an Engineer.</p>";
                if (!empty($reason)) {
                    $body .= "<p>Reason: " . htmlspecialchars($reason) . "</p>";
                }
                $body .= "<p>If you did not request this change, contact support immediately.</p>";
                @sendTroxanMail($targetEmail, $subject, $body);
            }
        }

        json_response(["status" => "success", "message" => "Name successfully changed."], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}

// ==========================================
// 4. LOGOK LEKÉRÉSE (POST)
// ==========================================
function getLogs() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error"], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    $targetUserId = (int)$input['target_user_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, statistics_file, last_updated FROM `Statistics` WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$targetUserId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $parsedLogs = [];
        foreach ($logs as $log) {
            $stats = !empty($log['statistics_file']) ? json_decode($log['statistics_file'], true) : [];
            $parsedLogs[] = [
                'id' => $log['id'],
                'date' => troxan_format_db_datetime($log['last_updated'], 'Y.m.d H:i', 'Unknown'),
                'score' => troxan_get_stat_score($stats),
                'details' => [
                    'Enemies killed' => troxan_get_stat_int($stats, ['num_of_enemies_killed', 'Mobs killed'], 0),
                    'Deaths' => troxan_get_stat_int($stats, ['num_of_deaths', 'Deaths'], 0),
                    'Story finished' => troxan_get_stat_int($stats, ['num_of_story_finished', 'Story finished'], 0)
                ]
            ];
        }
        json_response(["status" => "success", "logs" => $parsedLogs], 200);
    } catch (Exception $e) { json_response(["status" => "error"], 500); }
}

// ==========================================
// 5. ÚJ: ADMIN MAPS FUNKCIÓK (GET MAPS, REMOVE, EDIT)
// ==========================================
function getUserMaps() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error"], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    $targetUserId = (int)$input['target_user_id'];

    try {
        $query = "SELECT m.*, u.username as creator_name, r.role_name as creator_role, 
                         COALESCE(uml.added_at, m.created_at) as added_at 
                  FROM `Maps` m 
                  LEFT JOIN `User_Map_Library` uml ON m.id = uml.map_id AND uml.user_id = ?
                  JOIN `User` u ON m.creator_user_id = u.user_id 
                  JOIN Roles r ON u.role_id = r.id
                  WHERE (
                      (m.creator_user_id = ? AND m.status IN (0, 1, 3)) 
                      OR 
                      (uml.user_id = ? AND m.status IN (1, 3, 5))
                  ) ORDER BY added_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$targetUserId, $targetUserId, $targetUserId]);
        $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response(["status" => "success", "maps" => $maps], 200);
    } catch (Exception $e) { json_response(["status" => "error", "message" => $e->getMessage()], 500); }
}

function adminRemoveMap() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error"], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    
    $targetUserId = (int)$input['target_user_id'];
    $mapId = (int)$input['map_id'];

    try {
        // Remove ONLY from player's library (no map ban from this action)
        $delStmt = $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
        $delStmt->execute([$targetUserId, $mapId]);

        if ($delStmt->rowCount() <= 0) {
            json_response(["status" => "error", "message" => "This map is not in the player's library."], 404);
            return;
        }

        $pdo->prepare("UPDATE `Maps` SET downloads = GREATEST(downloads - 1, 0) WHERE id = ?")->execute([$mapId]);

        json_response(["status" => "success", "message" => "Map removed from player's library successfully!"], 200);

    } catch (Exception $e) { json_response(["status" => "error", "message" => $e->getMessage()], 500); }
}

function adminEditMapName() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) { json_response(["status" => "error"], 401); return; }
    $input = json_decode(file_get_contents('php://input'), true);
    
    $mapId = (int)$input['map_id'];
    $newName = trim($input['new_name']);

    if (empty($newName)) { json_response(["status" => "error", "message" => "The name cannot be empty."], 400); return; }

    try {
        $pdo->prepare("UPDATE `Maps` SET map_name = ? WHERE id = ?")->execute([$newName, $mapId]);
        json_response(["status" => "success", "message" => "Map name updated successfully!"], 200);
    } catch (Exception $e) { json_response(["status" => "error", "message" => $e->getMessage()], 500); }
}

function hardDeleteUser() {
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Unauthorized access."], 401);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $targetUserId = (int)($input['target_user_id'] ?? 0);
    $confirmText = strtoupper(trim($input['confirm_text'] ?? ''));
    $myUserId = (int)$_SESSION['user_id'];

    if ($targetUserId <= 0) {
        json_response(["status" => "error", "message" => "Invalid target user."], 400);
        return;
    }

    if ($confirmText !== 'CONFIRM') {
        json_response(["status" => "error", "message" => "Type CONFIRM to permanently delete this account."], 400);
        return;
    }

    if ($targetUserId === $myUserId) {
        json_response(["status" => "error", "message" => "You cannot delete your own account from here."], 400);
        return;
    }

    $transactionStarted = false;

    try {
        $myRoleStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $myRoleStmt->execute([$myUserId]);
        $myRole = $myRoleStmt->fetchColumn();

        if (!in_array($myRole, ['Admin', 'Engineer'])) {
            json_response(["status" => "error", "message" => "Only Admins and Engineers can use this feature."], 403);
            return;
        }

        $targetRoleStmt = $pdo->prepare("SELECT u.user_id, r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $targetRoleStmt->execute([$targetUserId]);
        $targetData = $targetRoleStmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetData) {
            json_response(["status" => "error", "message" => "Target user not found."], 404);
            return;
        }

        if ($targetData['role_name'] === 'Engineer') {
            json_response(["status" => "error", "message" => "Engineers cannot be deleted."], 403);
            return;
        }

        if ($targetData['role_name'] === 'Admin' && $myRole !== 'Engineer') {
            json_response(["status" => "error", "message" => "Only an Engineer can delete an Admin."], 403);
            return;
        }

        if (!$pdo->inTransaction()) {
            $transactionStarted = $pdo->beginTransaction();
        }

        $mapIdsStmt = $pdo->prepare("SELECT id FROM `Maps` WHERE creator_user_id = ?");
        $mapIdsStmt->execute([$targetUserId]);
        $createdMapIds = $mapIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($createdMapIds)) {
            $placeholders = implode(',', array_fill(0, count($createdMapIds), '?'));
            $pdo->prepare("DELETE FROM `User_Map_Library` WHERE map_id IN ($placeholders)")->execute($createdMapIds);
            $pdo->prepare("DELETE FROM `Maps` WHERE id IN ($placeholders)")->execute($createdMapIds);
        }

        $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ?")->execute([$targetUserId]);
        $pdo->prepare("DELETE FROM `Statistics` WHERE user_id = ?")->execute([$targetUserId]);

        try {
            $pdo->prepare("DELETE FROM `Active_Web_Sessions` WHERE user_id = ?")->execute([$targetUserId]);
        } catch (Exception $e) {
            // Optional cleanup table may not exist in all environments.
        }

        $pdo->prepare("DELETE FROM `User` WHERE user_id = ?")->execute([$targetUserId]);

        if ($transactionStarted && $pdo->inTransaction()) {
            $pdo->commit();
        }
        json_response(["status" => "success", "message" => "User account permanently deleted."], 200);
    } catch (Exception $e) {
        if ($transactionStarted && $pdo->inTransaction()) $pdo->rollBack();
        json_response(["status" => "error", "message" => $e->getMessage()], 500);
    }
}


// ==========================================
// 6. ROUTER
// ==========================================
switch ($data["method"]) {
    case 'GET': getContent(); break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action'])) {
            if ($input['action'] === 'toggle_ban') toggleBan();
            elseif ($input['action'] === 'change_role') changeRole();
            elseif ($input['action'] === 'change_username') changeUserName();
            elseif ($input['action'] === 'get_logs') getLogs();
            elseif ($input['action'] === 'get_user_maps') getUserMaps();
            elseif ($input['action'] === 'admin_remove_map') adminRemoveMap();
            elseif ($input['action'] === 'admin_edit_map_name') adminEditMapName();
            elseif ($input['action'] === 'hard_delete_user') hardDeleteUser();
            else json_response(["status" => "error", "message" => "Ismeretlen POST akció"], 400);
        } else json_response(["status" => "error", "message" => "Hiányzó action paraméter"], 400);
        break;
    default: json_response(["status" => "error", "message" => "Method not allowed"], 405); break;
}
?>