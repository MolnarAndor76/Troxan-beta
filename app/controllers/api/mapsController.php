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

        $roleName = strtolower($_SESSION['role_name'] ?? 'player');
        $roleId = $_SESSION['role_id'] ?? 0;
        $isStaff = (in_array($roleName, ['admin', 'moderator', 'owner']) || in_array($roleId, [1, 2]));

        // --- 1. AKTÍV PÁLYÁK LEKÉRÉSE ---
        $query = "SELECT m.*, u.username as creator_name FROM `Maps` m JOIN `User` u ON m.creator_user_id = u.user_id WHERE m.status = 1";
        $params = [];

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
            default:
                $query .= " ORDER BY m.downloads DESC";
                break;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $active_maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. KUKA TARTALMÁNYAK LEKÉRÉSE (Csak Staffnak!) ---
        $trash_maps = [];
        if ($isStaff) {
            $trashQuery = "SELECT m.*, u.username as creator_name FROM `Maps` m JOIN `User` u ON m.creator_user_id = u.user_id WHERE m.status IN (3, 4, 5) ORDER BY m.id DESC";
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

    $roleName = strtolower($_SESSION['role_name'] ?? 'player');
    $roleId = $_SESSION['role_id'] ?? 0;
    $isStaff = (in_array($roleName, ['admin', 'moderator', 'owner']) || in_array($roleId, [1, 2]));

    try {
        if ($action === 'delete_map') {
            $stmt = $pdo->prepare("SELECT status FROM `Maps` WHERE id = ?");
            $stmt->execute([$mapId]);
            $currentStatus = $stmt->fetchColumn();

            $newStatus = $isStaff ? 4 : (($currentStatus == 0) ? 5 : 3);
            $pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);
            json_response(["status" => "success"], 200);
        } elseif ($action === 'restore_map' && $isStaff) {
            $pdo->prepare("UPDATE `Maps` SET status = 1 WHERE id = ?")->execute([$mapId]);
            json_response(["status" => "success"], 200);
        } elseif ($action === 'increment_download') {
            $pdo->prepare("UPDATE `Maps` SET downloads = downloads + 1 WHERE id = ?")->execute([$mapId]);
            json_response(["status" => "success"], 200);
        }
    } catch (Exception $e) {
        json_response(["status" => "error"], 500);
    }
}

switch ($data["method"]) {
    case 'GET':
        getContent();
        break;
    case 'POST':
        handlePost();
        break;
}
