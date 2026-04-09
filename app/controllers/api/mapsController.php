<?php
function getContent()
{
    global $pdo;
    if (!isset($_SESSION['user_id'])) {
        json_response(["status" => "error", "message" => "Login required"], 401);
        return;
    }

    try {
        $search = trim($_GET['search'] ?? '');
        $sort = $_GET['sort'] ?? 'Downloads';

        $roleName = $_SESSION['role_name'] ?? 'Player';
        $roleId = $_SESSION['role_id'] ?? 0;
        
        // Define staff and engineer roles
        $isStaff = in_array($roleName, ['Admin', 'Moderator', 'Engineer']);
        $isEngineer = ($roleName === 'Engineer');

        // --- 1. FETCH ACTIVE MAPS ---
        $currentUserId = $_SESSION['user_id'] ?? 0;

        $query = "SELECT m.*, u.username as creator_name, r.role_name as creator_role,
                 CASE WHEN uml.map_id IS NOT NULL THEN 1 ELSE 0 END as is_in_library
              FROM `Maps` m 
              LEFT JOIN `User_Map_Library` uml ON m.id = uml.map_id AND uml.user_id = ?
                  JOIN `User` u ON m.creator_user_id = u.user_id 
                  JOIN Roles r ON u.role_id = r.id
                  WHERE m.status = 1";
        $params = [$currentUserId];

        if (!empty($search)) {
            $query .= " AND (m.map_name LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        switch ($sort) {
            case 'Alphabetical':
                $query .= " ORDER BY m.map_name ASC";
                break;
            case 'Most recent':
                $query .= " ORDER BY m.created_at DESC";
                break;
            case 'Oldest':
                $query .= " ORDER BY m.created_at ASC";
                break;
            default: // Downloads
                $query .= " ORDER BY m.downloads DESC";
                break;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $active_maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. FETCH TRASH MAPS (Staff Only) ---
        $trash_maps = [];
        if ($isStaff) {
            $trashQuery = "SELECT m.*, u.username as creator_name, r.role_name as creator_role 
                           FROM `Maps` m 
                           JOIN `User` u ON m.creator_user_id = u.user_id 
                           JOIN Roles r ON u.role_id = r.id
                           WHERE m.status IN (3, 4, 5) 
                           ORDER BY m.id DESC";
            $stmtTrash = $pdo->prepare($trashQuery);
            $stmtTrash->execute();
            $trash_maps = $stmtTrash->fetchAll(PDO::FETCH_ASSOC);
        }

        ob_start();
        require VIEWS . 'maps/maps.php';
        $buffer = ob_get_clean();
        json_response(["html" => $buffer, "status" => "success"], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => $e->getMessage()], 500);
    }
}

function handlePost()
{
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $mapId = (int)($input['map_id'] ?? 0);

    $roleName = $_SESSION['role_name'] ?? 'Player';
    $isStaff = in_array($roleName, ['Admin', 'Moderator', 'Engineer']);
    $isEngineer = ($roleName === 'Engineer');
    $currentUserId = $_SESSION['user_id'] ?? null;

    if (!$currentUserId) {
        json_response(["status" => "error", "message" => "Nincs bejelentkezve!"], 401);
        return;
    }

    try {
        if ($action === 'delete_map') {
            $checkStmt = $pdo->prepare("SELECT m.creator_user_id, r.role_name 
                                        FROM `Maps` m 
                                        JOIN `User` u ON m.creator_user_id = u.user_id 
                                        JOIN Roles r ON u.role_id = r.id 
                                        WHERE m.id = ?");
            $checkStmt->execute([$mapId]);
            $mapData = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$mapData) {
                json_response(["status" => "error", "message" => "Map not found."], 404);
            }

            if ($mapData['role_name'] === 'Engineer' && !$isEngineer && $mapData['creator_user_id'] != $currentUserId) {
                json_response(["status" => "error", "message" => "Only the Creator can delete this map!"], 403);
            }

            $stmt = $pdo->prepare("SELECT status FROM `Maps` WHERE id = ?");
            $stmt->execute([$mapId]);
            $currentStatus = $stmt->fetchColumn();

            $newStatus = ($isStaff && $mapData['creator_user_id'] != $currentUserId) ? 4 : (($currentStatus == 0) ? 5 : 3);
            
            $pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);
            json_response(["status" => "success", "message" => "Map moved to trash!"], 200);
            
        } elseif ($action === 'restore_map' && $isStaff) {
            $checkStmt = $pdo->prepare("SELECT m.creator_user_id, r.role_name 
                                        FROM `Maps` m 
                                        JOIN `User` u ON m.creator_user_id = u.user_id 
                                        JOIN Roles r ON u.role_id = r.id 
                                        WHERE m.id = ?");
            $checkStmt->execute([$mapId]);
            $mapData = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($mapData['role_name'] === 'Engineer' && !$isEngineer && $mapData['creator_user_id'] != $currentUserId) {
                json_response(["status" => "error", "message" => "Only the Creator can restore this map!"], 403);
            }

            $pdo->prepare("UPDATE `Maps` SET status = 1 WHERE id = ?")->execute([$mapId]);
            json_response(["status" => "success", "message" => "Map restored successfully!"], 200);
            
        } elseif ($action === 'add_to_library') {
            // 1. Check if the map is already in their library
            $checkStmt = $pdo->prepare("SELECT 1 FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
            $checkStmt->execute([$currentUserId, $mapId]);
            if ($checkStmt->fetchColumn()) {
                json_response(["status" => "info", "message" => "This map is already in your My Maps library!"], 200);
                return;
            }

            // 2. Növeljük a letöltések/hozzáadások számát a Maps táblában
            $pdo->prepare("UPDATE `Maps` SET downloads = downloads + 1 WHERE id = ?")->execute([$mapId]);
            
            // 3. Hozzáadjuk a játékos könyvtárához
            $libStmt = $pdo->prepare("INSERT INTO `User_Map_Library` (user_id, map_id) VALUES (?, ?)");
            $libStmt->execute([$currentUserId, $mapId]);

            json_response(["status" => "success", "message" => "Map successfully added to My Maps!"], 201);
        }
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}

switch ($data["method"]) {
    case 'GET':
        getContent();
        break;
    case 'POST':
        handlePost();
        break;
    default:
        json_response(["status" => "error", "message" => "Method not allowed"], 405); 
        break;
}
?>