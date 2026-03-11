<?php

// ==========================================
// 1. OLDAL BETÖLTÉSE (GET)
// ==========================================
function getContent() {
    global $pdo;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        json_response(["status" => "error", "message" => "Nincs jogosultságod!"], 401);
        return;
    }

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        if ($checkStmt->fetchColumn() !== 'Admin') {
            json_response(["status" => "error", "message" => "Ide csak Adminok léphetnek be!"], 403);
            return;
        }

        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $query = "
            SELECT u.user_id, u.username, u.email, u.created_at, u.last_time_online, u.is_banned,
                   u.role_id, r.role_name, a.avatar_picture, s.statistics_file
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
        json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500);
    }
}

// ==========================================
// 2. BAN / UNBAN LOGIKA (POST)
// ==========================================
function toggleBan() {
    global $pdo;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) { json_response(["status" => "error", "message" => "Nincs jogosultságod!"], 401); return; }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['action']) || $input['action'] !== 'toggle_ban' || empty($input['target_user_id'])) { json_response(["status" => "error", "message" => "Érvénytelen kérés!"], 400); return; }

    $targetUserId = (int)$input['target_user_id'];

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        if ($checkStmt->fetchColumn() !== 'Admin') { json_response(["status" => "error", "message" => "Csak Adminok használhatják ezt a funkciót!"], 403); return; }

        if ($targetUserId === $_SESSION['user_id']) { json_response(["status" => "error", "message" => "Magadat nem tilthatod ki, te zseni!"], 400); return; }

        $statusStmt = $pdo->prepare("SELECT is_banned FROM `User` WHERE user_id = ?");
        $statusStmt->execute([$targetUserId]);
        $currentStatus = $statusStmt->fetchColumn();

        if ($currentStatus === false) { json_response(["status" => "error", "message" => "Nem található ilyen játékos!"], 404); return; }

        $newStatus = ($currentStatus == 1) ? 0 : 1;
        $updateStmt = $pdo->prepare("UPDATE `User` SET is_banned = ? WHERE user_id = ?");
        $updateStmt->execute([$newStatus, $targetUserId]);

        $message = ($newStatus == 1) ? "Játékos sikeresen kitiltva!" : "A kitiltás sikeresen feloldva!";
        json_response(["status" => "success", "message" => $message], 200);

    } catch (Exception $e) { json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500); }
}

// ==========================================
// 3. JOGOSULTSÁG MEGVÁLTOZTATÁSA (POST)
// ==========================================
function changeRole() {
    global $pdo;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) { json_response(["status" => "error", "message" => "Nincs jogosultságod!"], 401); return; }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['action']) || $input['action'] !== 'change_role' || empty($input['target_user_id']) || empty($input['role_action'])) { json_response(["status" => "error", "message" => "Érvénytelen kérés!"], 400); return; }

    $targetUserId = (int)$input['target_user_id'];
    $roleAction = $input['role_action']; 

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        if ($checkStmt->fetchColumn() !== 'Admin') { json_response(["status" => "error", "message" => "Csak Adminok használhatják ezt a funkciót!"], 403); return; }

        if ($targetUserId === $_SESSION['user_id']) { json_response(["status" => "error", "message" => "A saját rangodat nem módosíthatod!"], 400); return; }

        $targetStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $targetStmt->execute([$targetUserId]);
        $currentRole = $targetStmt->fetchColumn();

        if (!$currentRole) { json_response(["status" => "error", "message" => "Felhasználó nem található!"], 404); return; }

        $newRoleName = '';
        if ($roleAction === 'promote') {
            if ($currentRole === 'Player') $newRoleName = 'Moderator';
            elseif ($currentRole === 'Moderator') $newRoleName = 'Admin';
            else { json_response(["status" => "error", "message" => "Őt már nem lehet feljebb léptetni!"], 400); return; }
        } elseif ($roleAction === 'demote') {
            if ($currentRole === 'Admin') $newRoleName = 'Moderator';
            elseif ($currentRole === 'Moderator') $newRoleName = 'Player';
            else { json_response(["status" => "error", "message" => "Őt már nem lehet lejjebb fokozni!"], 400); return; }
        } else { json_response(["status" => "error", "message" => "Ismeretlen művelet!"], 400); return; }

        $roleIdStmt = $pdo->prepare("SELECT id FROM Roles WHERE role_name = ?");
        $roleIdStmt->execute([$newRoleName]);
        $newRoleId = $roleIdStmt->fetchColumn();

        $updateStmt = $pdo->prepare("UPDATE `User` SET role_id = ? WHERE user_id = ?");
        $updateStmt->execute([$newRoleId, $targetUserId]);

        json_response(["status" => "success", "message" => "Sikeres művelet! Új rang: " . $newRoleName], 200);

    } catch (Exception $e) { json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500); }
}

// ==========================================
// 4. LOGOK LEKÉRÉSE (POST) - ÚJ!
// ==========================================
function getLogs() {
    global $pdo;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) { json_response(["status" => "error", "message" => "Nincs jogosultságod!"], 401); return; }

    $input = json_decode(file_get_contents('php://input'), true);
    $targetUserId = (int)($input['target_user_id'] ?? 0);

    if (!$targetUserId) { json_response(["status" => "error", "message" => "Érvénytelen felhasználó ID!"], 400); return; }

    try {
        $checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        if ($checkStmt->fetchColumn() !== 'Admin') { json_response(["status" => "error", "message" => "Csak Adminok használhatják ezt a funkciót!"], 403); return; }

        // Lekérjük az összes logot időrendben visszafelé (legújabb legelöl)
        $stmt = $pdo->prepare("SELECT id, statistics_file, last_updated FROM `Statistics` WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$targetUserId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $parsedLogs = [];
        foreach ($logs as $log) {
            $stats = !empty($log['statistics_file']) ? json_decode($log['statistics_file'], true) : [];
            
            // Kinyerjük a specifikus adatokat (kezelve a régi és az új JSON formátumokat is)
            $parsedLogs[] = [
                'id' => $log['id'],
                'date' => $log['last_updated'] ? date('Y.m.d H:i', strtotime($log['last_updated'])) : 'Unknown',
                'score' => $stats['score'] ?? ($stats['Experience points'] ?? 0),
                'details' => [
                    'Enemies killed' => $stats['num_of_enemies_killed'] ?? ($stats['Mobs killed'] ?? 0),
                    'Deaths' => $stats['num_of_deaths'] ?? ($stats['Deaths'] ?? 0),
                    'Story finished' => $stats['num_of_story_finished'] ?? 0,
                    'Time played' => $stats['time_played'] ?? ($stats['Total playtime'] ?? '0h 0m')
                ]
            ];
        }

        json_response(["status" => "success", "logs" => $parsedLogs], 200);

    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500);
    }
}

// ==========================================
// 5. ROUTER
// ==========================================
switch ($data["method"]) {
    case 'GET': 
        getContent(); 
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action'])) {
            if ($input['action'] === 'toggle_ban') {
                toggleBan();
            } elseif ($input['action'] === 'change_role') {
                changeRole();
            } elseif ($input['action'] === 'get_logs') { // ÚJ RÉSZ A ROUTERBEN!
                getLogs();
            } else {
                json_response(["status" => "error", "message" => "Ismeretlen POST akció"], 400);
            }
        } else {
            json_response(["status" => "error", "message" => "Hiányzó action paraméter"], 400);
        }
        break;
    default: 
        json_response(["status" => "error", "message" => "Method not allowed"], 405); 
        break;
}
?>