<?php

function getContent() {
    global $pdo; 
    
    $patchNotes = [];
    try {
        if ($pdo) {
            // Behozzuk az is_locked oszlopot is!
            $stmt = $pdo->query("
                SELECT p.id, p.name, p.description, p.created_at, p.updated_at, p.created_by, p.updated_by, p.is_locked,
                       u.username AS author_name, r.role_name AS author_role,
                       u2.username AS updater_name
                FROM PatchNotes p
                LEFT JOIN User u ON p.created_by = u.user_id
                LEFT JOIN Roles r ON u.role_id = r.id
                LEFT JOIN User u2 ON p.updated_by = u2.user_id
                WHERE p.is_deleted = 0 
                ORDER BY p.updated_at DESC
            ");
            $patchNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Patch notes hiba: " . $e->getMessage());
    }
    
    $isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
    $currentUserId = $_SESSION['user_id'] ?? null;
    
    $canEditPatchNotes = false;
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if (isset($_SESSION['role_name']) && in_array($_SESSION['role_name'], ['Admin', 'Engineer'])) {
            $canEditPatchNotes = true;
        }
    }
    
    ob_start();
    require VIEWS . 'main/main.php';
    $buffer = ob_get_clean();

    json_response([
        "html"    => $buffer,
        "status"  => "success",
        "message" => ""
    ], 200);
}

function handlePatchAction() {
    global $pdo;
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], ['Admin', 'Engineer'])) {
        json_response(["status" => "error", "message" => "Nincs jogosultságod ehhez a művelethez!"], 403);
    }

    $action = $input['action'] ?? '';
    $id = $input['id'] ?? null;
    $currentUserId = $_SESSION['user_id'];
    $myRole = $_SESSION['role_name'];

    try {
        // === LAKAT ÉS ENGINEER VÉDELEM ===
        if (($action === 'edit' || $action === 'delete') && $id) {
            $checkStmt = $pdo->prepare("
                SELECT p.created_by, p.is_locked, r.role_name 
                FROM PatchNotes p
                LEFT JOIN User u ON p.created_by = u.user_id
                LEFT JOIN Roles r ON u.role_id = r.id
                WHERE p.id = ?
            ");
            $checkStmt->execute([$id]);
            $patchData = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($patchData) {
                // Engineer patch védelme
                if ($patchData['role_name'] === 'Engineer' && $patchData['created_by'] != $currentUserId) {
                    json_response(["status" => "error", "message" => "Ehhez a frissítéshez csak maga a Teremtője nyúlhat!"], 403);
                }
                // Lakat védelem (Ha zárva van, Admin nem nyúlhat hozzá!)
                if ($patchData['is_locked'] == 1 && $myRole !== 'Engineer') {
                    json_response(["status" => "error", "message" => "Ez a bejegyzés le van lakatolva egy Engineer által!"], 403);
                }
            }
        }

        // === ÚJ: LAKAT ÁTKAPCSOLÁSA ===
        if ($action === 'toggle_lock' && $id) {
            if ($myRole !== 'Engineer') {
                json_response(["status" => "error", "message" => "Csak egy Engineer használhatja a lakatot!"], 403);
            }
            $stmt = $pdo->prepare("SELECT is_locked FROM PatchNotes WHERE id = ?");
            $stmt->execute([$id]);
            $currentLock = $stmt->fetchColumn();
            
            $newLock = $currentLock == 1 ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE PatchNotes SET is_locked = ? WHERE id = ?");
            $updateStmt->execute([$newLock, $id]);
            
            $msg = $newLock == 1 ? "Patch sikeresen lelakatolva! 🔒" : "Lakat feloldva! 🔓";
            json_response(["status" => "success", "message" => $msg], 200);
            
        } elseif ($action === 'delete' && $id) {
            $stmt = $pdo->prepare("UPDATE PatchNotes SET is_deleted = 1, deleted_by = ? WHERE id = ?");
            $stmt->execute([$currentUserId, $id]);
            json_response(["status" => "success", "message" => "Patch áthelyezve a lomtárba!"], 200);
            
        } elseif ($action === 'edit' && $id) {
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            $stmt = $pdo->prepare("UPDATE PatchNotes SET name = ?, description = ?, updated_by = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $currentUserId, $id]);
            json_response(["status" => "success", "message" => "Patch frissítve!"], 200);
            
        } elseif ($action === 'create') {
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO PatchNotes (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$name, $desc, $currentUserId]);
            json_response(["status" => "success", "message" => "Patch sikeresen publikálva!"], 200);

        } elseif ($action === 'restore' && $id) {
            $checkStmt = $pdo->prepare("
                SELECT p.deleted_by, r.role_name 
                FROM PatchNotes p
                LEFT JOIN User u ON p.deleted_by = u.user_id
                LEFT JOIN Roles r ON u.role_id = r.id
                WHERE p.id = ?
            ");
            $checkStmt->execute([$id]);
            $delData = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($delData && $delData['role_name'] === 'Engineer' && $delData['deleted_by'] != $currentUserId) {
                json_response(["status" => "error", "message" => "Vigyázz! Ezt a frissítést egy Engineer törölte, csak ő állíthatja vissza!"], 403);
            }

            $stmt = $pdo->prepare("UPDATE PatchNotes SET is_deleted = 0, deleted_by = NULL WHERE id = ?");
            $stmt->execute([$id]);
            json_response(["status" => "success", "message" => "Patch visszaállítva!"], 200);

        } elseif ($action === 'get_deleted') {
            $stmt = $pdo->query("SELECT id, name, created_at FROM PatchNotes WHERE is_deleted = 1 ORDER BY created_at DESC");
            $deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(["status" => "success", "data" => $deleted], 200);

        } else {
            json_response(["status" => "error", "message" => "Érvénytelen művelet!"], 400);
        }
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500);
    }
}

switch ($data["method"]) {
    case 'GET': getContent(); break;
    case 'POST': handlePatchAction(); break;
    default: json_response(["status" => "error", "message" => "Method not allowed"], 405); break;
}
?>