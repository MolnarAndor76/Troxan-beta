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
        $sort = $_GET['sort'] ?? 'Newest Added';
        $myUserId = $_SESSION['user_id'];

        // --- A MÁGIKUS LEKÉRDEZÉS ---
        // 1. Hozza azokat, amiket TE csináltál (és a státusz 0=Draft, 1=Publikus, vagy 3=Unpublished)
        // 2. VAGY hozza azokat, amiket LEMENTETTÉL (és a státusz 1, 3, vagy 5=Készítő által törölt, de neked megmarad!)
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
                  )";
        
        $params = [$myUserId, $myUserId, $myUserId];

        if (!empty($search)) {
            $query .= " AND (m.map_name LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        switch ($sort) {
            case 'Alphabetical':
                $query .= " ORDER BY m.map_name ASC";
                break;
            case 'Oldest Added':
                $query .= " ORDER BY added_at ASC";
                break;
            default: // Newest Added
                $query .= " ORDER BY added_at DESC";
                break;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $my_maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require VIEWS . 'myMaps/myMaps.php'; 
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
    $currentUserId = $_SESSION['user_id'] ?? null;

    if (!$currentUserId) {
        json_response(["status" => "error", "message" => "Nincs bejelentkezve!"], 401);
        return;
    }

    try {
        if ($action === 'remove_map') {
            // Ellenőrizzük a pálya készítőjét
            $stmt = $pdo->prepare("SELECT creator_user_id, status FROM `Maps` WHERE id = ?");
            $stmt->execute([$mapId]);
            $mapData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mapData) {
                json_response(["status" => "error", "message" => "Pálya nem található."], 404);
            }

            // 1. Töröljük a kapcsolatot a saját könyvtárból
            $delStmt = $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
            $delStmt->execute([$currentUserId, $mapId]);

            // EXPLOIT FIX: Ha tényleg töröltünk egy sort (azaz benne volt a könyvtárában), 
            // akkor levonunk egyet a letöltések számából! A GREATEST megvéd attól, hogy 0 alá menjen.
            if ($delStmt->rowCount() > 0) {
                $pdo->prepare("UPDATE `Maps` SET downloads = GREATEST(downloads - 1, 0) WHERE id = ?")->execute([$mapId]);
            }

            // 2. Ha én vagyok a készítő, akkor az adatbázisban a státuszt 5-re rakjuk (Scrapped).
            if ($mapData['creator_user_id'] == $currentUserId) {
                $pdo->prepare("UPDATE `Maps` SET status = 5 WHERE id = ?")->execute([$mapId]);
            }

            json_response(["status" => "success", "message" => "Pálya eltávolítva a könyvtáradból!"], 200);
            
        } elseif ($action === 'toggle_publish') {
            // Publikálás vagy visszavonás
            $stmt = $pdo->prepare("SELECT creator_user_id, status FROM `Maps` WHERE id = ?");
            $stmt->execute([$mapId]);
            $mapData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mapData['creator_user_id'] != $currentUserId) {
                json_response(["status" => "error", "message" => "Csak a saját pályádat publikálhatod!"], 403);
            }

            // Ha 1 (Publikus) -> Akkor Unpublish (3). Ha 0 vagy 3 -> Akkor Publish (1)
            $newStatus = ($mapData['status'] == 1) ? 3 : 1;
            
            $pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);
            $msg = ($newStatus == 1) ? "Pálya sikeresen publikálva a közös listába!" : "Pálya visszavonva (Unpublished)!";
            
            json_response(["status" => "success", "message" => $msg, "new_status" => $newStatus], 200);
        }
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Adatbázis hiba: " . $e->getMessage()], 500);
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