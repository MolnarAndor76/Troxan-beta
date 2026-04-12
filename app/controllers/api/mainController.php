<?php

function ensureSiteSettingsStorage() {
    global $pdo;

    if (!$pdo) {
        return;
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `SiteSettings` (
        `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        `download_url` VARCHAR(1024) NOT NULL,
        `trailer_url` VARCHAR(1024) NOT NULL,
        `about_us_text` TEXT NOT NULL,
        `special_thanks_text` TEXT NOT NULL,
        `system_requirements_text` TEXT NOT NULL,
        `lore_text` LONGTEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ha a tábla már létezik de a special_thanks_text oszlop még nincs, hozzáadjuk
    try {
        $colCheck = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'SiteSettings' AND COLUMN_NAME = 'special_thanks_text'");
        $colCheck->execute();
        if ((int)$colCheck->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `SiteSettings` ADD COLUMN `special_thanks_text` TEXT NOT NULL");
            $pdo->exec("UPDATE `SiteSettings` SET special_thanks_text = '' WHERE special_thanks_text IS NULL");
        }
    } catch (Exception $e) { error_log("SiteSettings migrate error: " . $e->getMessage()); }

    try {
        $colCheck = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'SiteSettings' AND COLUMN_NAME = 'system_requirements_text'");
        $colCheck->execute();
        if ((int)$colCheck->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `SiteSettings` ADD COLUMN `system_requirements_text` TEXT NOT NULL");
            $pdo->exec("UPDATE `SiteSettings` SET system_requirements_text = '' WHERE system_requirements_text IS NULL");
        }
    } catch (Exception $e) { error_log("SiteSettings migrate error: " . $e->getMessage()); }

    try {
        $colCheck = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'SiteSettings' AND COLUMN_NAME = 'lore_text'");
        $colCheck->execute();
        if ((int)$colCheck->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `SiteSettings` ADD COLUMN `lore_text` LONGTEXT NOT NULL");
            $pdo->exec("UPDATE `SiteSettings` SET lore_text = '' WHERE lore_text IS NULL");
        }
    } catch (Exception $e) { error_log("SiteSettings migrate error: " . $e->getMessage()); }

    $insertDefault = $pdo->prepare("INSERT INTO `SiteSettings` (id, download_url, trailer_url, about_us_text, special_thanks_text, system_requirements_text, lore_text)
        VALUES (1, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE id = id");

    $insertDefault->execute([
        '/troxan.zip',
        'https://www.youtube-nocookie.com/embed/_pMgNJjNodo?autoplay=1&mute=1&loop=1&playlist=_pMgNJjNodo&controls=0&modestbranding=1&rel=0',
        'Troxan started as a school project…',
        "Trailer made by: Név\nArtworks: Hamarosan...",
        "CPU|Nemtom p4|Ryzen 9 5950X\nGPU|Gt 1030|RTX 3080TI\nRAM|256mb|64gb\nOS|<WIN XP 64bit|-------------------\nSTORAGE|300mb|-------------------",
        "For centuries, the majestic realm of Troxan was a beacon of absolute peace and prosperity.\n\nJoyous laughter echoed through its emerald valleys, and citizens lived in perfect harmony under the wise and benevolent guidance of the High Sovereign. The skies were forever bright, the rivers flowed with crystal-clear waters, and a golden age of tranquility blessed every corner of the kingdom. It was a true paradise, untouched by darkness.\n\nBut then, everything changed.\n\nWithout warning, a mysterious and devastating plague—a rapidly mutating, corrupted virus—swept across the land like a silent storm. It withered the once-vibrant forests, silenced the joyful streets, and began twisting the realm's peaceful inhabitants into hollow, aggressive husks. No ancient magic could cure it, and no fortress walls could keep the infection at bay. The virus is spreading at an unstoppable rate, consuming the very life force of Troxan.\n\nNow, as the kingdom teeters on the brink of total annihilation, the desperate Sovereign has summoned you. Out of all the warriors and scholars, you are the only one who possesses the resilience to withstand the infection. You have been tasked with the ultimate, perilous mission: venture deep into the heart of the corrupted zones, eradicate the source of the virus, and cleanse the land.\n\nThe time for fear is over. You are Troxan's last, shining hope. Will you answer the call and save the realm, or will the darkness consume us all?"
    ]);
}

function getSiteSettings() {
    global $pdo;

    $defaults = [
        'download_url' => '/troxan.zip',
        'trailer_url' => 'https://www.youtube-nocookie.com/embed/_pMgNJjNodo?autoplay=1&mute=1&loop=1&playlist=_pMgNJjNodo&controls=0&modestbranding=1&rel=0',
        'about_us_text' => 'Troxan started as a school project…',
        'special_thanks_text' => "Trailer made by: Név\nArtworks: Hamarosan...",
        'system_requirements_text' => "CPU|Nemtom p4|Ryzen 9 5950X\nGPU|Gt 1030|RTX 3080TI\nRAM|256mb|64gb\nOS|<WIN XP 64bit|-------------------\nSTORAGE|300mb|-------------------",
        'lore_text' => "For centuries, the majestic realm of Troxan was a beacon of absolute peace and prosperity.\n\nJoyous laughter echoed through its emerald valleys, and citizens lived in perfect harmony under the wise and benevolent guidance of the High Sovereign. The skies were forever bright, the rivers flowed with crystal-clear waters, and a golden age of tranquility blessed every corner of the kingdom. It was a true paradise, untouched by darkness.\n\nBut then, everything changed.\n\nWithout warning, a mysterious and devastating plague—a rapidly mutating, corrupted virus—swept across the land like a silent storm. It withered the once-vibrant forests, silenced the joyful streets, and began twisting the realm's peaceful inhabitants into hollow, aggressive husks. No ancient magic could cure it, and no fortress walls could keep the infection at bay. The virus is spreading at an unstoppable rate, consuming the very life force of Troxan.\n\nNow, as the kingdom teeters on the brink of total annihilation, the desperate Sovereign has summoned you. Out of all the warriors and scholars, you are the only one who possesses the resilience to withstand the infection. You have been tasked with the ultimate, perilous mission: venture deep into the heart of the corrupted zones, eradicate the source of the virus, and cleanse the land.\n\nThe time for fear is over. You are Troxan's last, shining hope. Will you answer the call and save the realm, or will the darkness consume us all?"
    ];

    try {
        if (!$pdo) {
            return $defaults;
        }

        ensureSiteSettingsStorage();

        $stmt = $pdo->query("SELECT download_url, trailer_url, about_us_text, special_thanks_text, system_requirements_text, lore_text FROM `SiteSettings` WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            return $defaults;
        }

        return [
            'download_url' => trim($settings['download_url'] ?? '') !== '' ? $settings['download_url'] : $defaults['download_url'],
            'trailer_url' => trim($settings['trailer_url'] ?? '') !== '' ? $settings['trailer_url'] : $defaults['trailer_url'],
            'about_us_text' => trim($settings['about_us_text'] ?? '') !== '' ? $settings['about_us_text'] : $defaults['about_us_text'],
            'special_thanks_text' => trim($settings['special_thanks_text'] ?? '') !== '' ? $settings['special_thanks_text'] : $defaults['special_thanks_text'],
            'system_requirements_text' => trim($settings['system_requirements_text'] ?? '') !== '' ? $settings['system_requirements_text'] : $defaults['system_requirements_text'],
            'lore_text' => trim($settings['lore_text'] ?? '') !== '' ? $settings['lore_text'] : $defaults['lore_text']
        ];
    } catch (Exception $e) {
        error_log("Site settings hiba: " . $e->getMessage());
        return $defaults;
    }
}

function isValidHttpUrl($url) {
    if (!is_string($url) || trim($url) === '') {
        return false;
    }

    $trimmed = trim($url);
    if (strpos($trimmed, '/') === 0 && strpos($trimmed, '//') !== 0) {
        return true;
    }

    $sanitized = filter_var($trimmed, FILTER_SANITIZE_URL);
    if (!filter_var($sanitized, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = strtolower(parse_url($sanitized, PHP_URL_SCHEME) ?? '');
    return in_array($scheme, ['http', 'https'], true);
}

function parseSystemRequirements($rawText) {
    $rows = [];
    $lines = preg_split('/\r\n|\r|\n/', (string)$rawText);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = explode('|', $line, 3);
        if (count($parts) < 3) {
            continue;
        }

        $rows[] = [
            'component' => trim($parts[0]),
            'minimum' => trim($parts[1]),
            'recommended' => trim($parts[2])
        ];
    }

    return $rows;
}

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

    $canEditSiteSettings = isset($_SESSION['logged_in'])
        && $_SESSION['logged_in'] === true
        && isset($_SESSION['role_name'])
        && $_SESSION['role_name'] === 'Engineer';

    $siteSettings = getSiteSettings();
    $systemRequirementsRows = parseSystemRequirements($siteSettings['system_requirements_text']);
    
    ob_start();
    require VIEWS . 'main/main.php';
    $buffer = ob_get_clean();

    json_response([
        "html"    => $buffer,
        "status"  => "success",
        "message" => ""
    ], 200);
}

function handlePatchAction($input = null) {
    global $pdo;
    if ($input === null) {
        $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], ['Admin', 'Engineer'])) {
        json_response(["status" => "error", "message" => "You do not have permission for this action."], 403);
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
                    json_response(["status" => "error", "message" => "Only the original Engineer author can modify this patch."], 403);
                }
                // Lakat védelem (Ha zárva van, Admin nem nyúlhat hozzá!)
                if ($patchData['is_locked'] == 1 && $myRole !== 'Engineer') {
                    json_response(["status" => "error", "message" => "This entry is locked by an Engineer!"], 403);
                }
            }
        }

        // === ÚJ: LAKAT ÁTKAPCSOLÁSA ===
        if ($action === 'toggle_lock' && $id) {
            if ($myRole !== 'Engineer') {
                json_response(["status" => "error", "message" => "Only an Engineer can use the lock!"], 403);
            }
            $stmt = $pdo->prepare("SELECT is_locked FROM PatchNotes WHERE id = ?");
            $stmt->execute([$id]);
            $currentLock = $stmt->fetchColumn();
            
            $newLock = $currentLock == 1 ? 0 : 1;
            $updateStmt = $pdo->prepare("UPDATE PatchNotes SET is_locked = ? WHERE id = ?");
            $updateStmt->execute([$newLock, $id]);
            
            $msg = $newLock == 1 ? "Patch successfully locked! 🔒" : "Lock released! 🔓";
            json_response(["status" => "success", "message" => $msg], 200);
            
        } elseif ($action === 'delete' && $id) {
            $stmt = $pdo->prepare("UPDATE PatchNotes SET is_deleted = 1, deleted_by = ? WHERE id = ?");
            $stmt->execute([$currentUserId, $id]);
            json_response(["status" => "success", "message" => "Patch moved to recycle bin."], 200);
            
        } elseif ($action === 'edit' && $id) {
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            $stmt = $pdo->prepare("UPDATE PatchNotes SET name = ?, description = ?, updated_by = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $currentUserId, $id]);
            json_response(["status" => "success", "message" => "Patch updated successfully."], 200);
            
        } elseif ($action === 'create') {
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO PatchNotes (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$name, $desc, $currentUserId]);
            json_response(["status" => "success", "message" => "Patch published successfully!"], 201);

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
                json_response(["status" => "error", "message" => "This patch was deleted by an Engineer. Only that Engineer can restore it."], 403);
            }

            $stmt = $pdo->prepare("UPDATE PatchNotes SET is_deleted = 0, deleted_by = NULL WHERE id = ?");
            $stmt->execute([$id]);
            json_response(["status" => "success", "message" => "Patch restored successfully."], 200);

        } elseif ($action === 'get_deleted') {
            $stmt = $pdo->query("SELECT id, name, created_at FROM PatchNotes WHERE is_deleted = 1 ORDER BY created_at DESC");
            $deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(["status" => "success", "data" => $deleted], 200);

        } else {
            json_response(["status" => "error", "message" => "Invalid operation!"], 400);
        }
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}

function handleSiteSettingsAction($input) {
    global $pdo;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Engineer') {
        json_response(["status" => "error", "message" => "You do not have permission for this action."], 403);
    }

    $downloadUrl = trim($input['download_url'] ?? '');
    $trailerUrl = trim($input['trailer_url'] ?? '');
    $aboutUsText = trim($input['about_us_text'] ?? '');
    $specialThanksText = trim($input['special_thanks_text'] ?? '');
    $systemRequirementsText = trim($input['system_requirements_text'] ?? '');
    $loreText = trim($input['lore_text'] ?? '');

    if (!isValidHttpUrl($downloadUrl)) {
        json_response(["status" => "error", "message" => "Invalid download URL."], 400);
    }

    if (!isValidHttpUrl($trailerUrl)) {
        json_response(["status" => "error", "message" => "Invalid trailer URL."], 400);
    }

    if ($aboutUsText === '') {
        json_response(["status" => "error", "message" => "About us text cannot be empty."], 400);
    }

    if (empty(parseSystemRequirements($systemRequirementsText))) {
        json_response(["status" => "error", "message" => "System requirements must contain at least one valid line (format: Component|Minimum|Recommended)."], 400);
    }

    if ($loreText === '') {
        json_response(["status" => "error", "message" => "Lore text cannot be empty."], 400);
    }

    try {
        ensureSiteSettingsStorage();

        $stmt = $pdo->prepare("UPDATE `SiteSettings` SET download_url = ?, trailer_url = ?, about_us_text = ?, special_thanks_text = ?, system_requirements_text = ?, lore_text = ? WHERE id = 1");
        $stmt->execute([$downloadUrl, $trailerUrl, $aboutUsText, $specialThanksText, $systemRequirementsText, $loreText]);

        json_response(["status" => "success", "message" => "Main page settings updated successfully."], 200);
    } catch (Exception $e) {
        json_response(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
    }
}

switch ($data["method"]) {
    case 'GET': getContent(); break;
    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $action = $input['action'] ?? '';

        if ($action === 'update_site_settings') {
            handleSiteSettingsAction($input);
        }

        handlePatchAction($input);
        break;
    default: json_response(["status" => "error", "message" => "Method not allowed"], 405); break;
}
?>