## 7. Admin — jogosultságok + action-alapú admin API (ban/role/logs/maps/hard delete)

### 7.1 Endpoint és szerepe
Az admin felület egyetlen controller alá van szervezve:

- View: `GET /app/api.php?path=admin`
- Műveletek: `POST /app/api.php?path=admin` + `{ action: ... }`

Backend: `app/controllers/api/adminController.php`  
Frontend: `src/admin-src/admin.js` (UI: modálok, gombok, fetch hívások)

---

### 7.2 GET: admin oldal betöltése (role check + user lista)
#### 7.2.1 Bejelentkezés check
```php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
  json_response(["status" => "error", "message" => "Unauthorized access."], 401);
  return;
}
```

#### 7.2.2 Admin area jogosultság: csak Admin és Engineer
```php
$checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
$checkStmt->execute([$_SESSION['user_id']]);
if (!in_array($checkStmt->fetchColumn(), ['Admin', 'Engineer'])) {
  json_response(["status" => "error", "message" => "Only Admins and Engineers can access this area."], 403);
  return;
}
```

#### 7.2.3 User lista lekérdezés (search + latest statistics join)
Az admin oldal a user listát úgy hozza, hogy a `Statistics` táblából a legutolsó sort köti a userhez:

```php
$query = "
  SELECT u.user_id, u.username, u.email, u.created_at, u.last_time_online, u.is_banned,
         u.role_id, r.role_name, a.avatar_picture, s.statistics_file,
         {$lastUsernameSelect} as last_username_change,
         {$lastPasswordSelect} as last_password_change
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
```

Search:

```php
if (!empty($searchTerm)) {
  $query .= " WHERE u.username LIKE ?";
  $params[] = "%" . $searchTerm . "%";
}
```

Sort:

```php
$query .= " ORDER BY r.id DESC, u.username ASC";
```

View render:

```php
ob_start();
require VIEWS . 'admin/admin.php';
$buffer = ob_get_clean();
json_response(["html" => $buffer, "status" => "success"], 200);
```

---

### 7.3 POST: admin műveletek — `action` dispatcher
Az admin controller POST esetén action alapján dispatch-el:

```php
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
} else {
  json_response(["status" => "error", "message" => "Hiányzó action paraméter"], 400);
}
```

---

### 7.4 Ban / Unban (`action: toggle_ban`)
#### 7.4.1 Jogosultságok és tiltások
- csak Admin/Engineer használhatja
- nem bannolhatod magad
- Engineer user nem bannolható
- Admin bannolásához Engineer szükséges
- bannolásnál a `reason` kötelező

```php
if (!in_array($myRole, ['Admin', 'Engineer'])) {
  json_response(["status" => "error", "message" => "Only Admins and Engineers can use this feature."], 403);
  return;
}

if ($targetUserId === $myUserId) {
  json_response(["status" => "error", "message" => "You cannot ban yourself."], 400);
  return;
}

if ($targetData['role_name'] === 'Engineer') {
  json_response(["status" => "error", "message" => "Engineers cannot be banned."], 403);
  return;
}

if ($targetData['role_name'] === 'Admin' && $myRole !== 'Engineer') {
  json_response(["status" => "error", "message" => "A Engineer is required to ban Admins."], 403);
  return;
}

if ($newStatus === 1 && empty($reason)) {
  json_response(["status" => "error", "message" => "Ban reason is required."], 400);
  return;
}
```

#### 7.4.2 DB update
```php
$pdo->prepare("UPDATE `User` SET is_banned = ? WHERE user_id = ?")->execute([$newStatus, $targetUserId]);
```

#### 7.4.3 Email értesítés (best effort)
Ha van email, a rendszer mailt küld (mailer.php).

```php
$subject = $newStatus == 1 ? "Troxan - You were banned" : "Troxan - You were unbanned";
@sendTroxanMail($targetInfo['email'], $subject, $body);
```

---

### 7.5 Role change (`action: change_role`)
#### 7.5.1 Szabályok
- csak Admin/Engineer
- nem módosíthatod a saját role-odat
- Engineer role nem módosítható
- más Admin role-ját csak Engineer módosíthatja
- promote: Player → Moderator → Admin (utóbbi csak Engineer)
- demote: Admin → Moderator → Player

```php
if ($targetUserId === $myUserId) {
  json_response(["status" => "error", "message" => "You cannot modify your own role."], 400);
  return;
}

if ($currentRole === 'Engineer') {
  json_response(["status" => "error", "message" => "Engineers cannot have their role changed."], 403);
  return;
}

if ($currentRole === 'Admin' && $myRole !== 'Engineer') {
  json_response(["status" => "error", "message" => "Only an Engineer can change another Admin's role."], 403);
  return;
}
```

Role ID lookup + update:

```php
$roleIdStmt = $pdo->prepare("SELECT id FROM Roles WHERE role_name = ?");
$roleIdStmt->execute([$newRoleName]);
$newRoleId = $roleIdStmt->fetchColumn();

$pdo->prepare("UPDATE `User` SET role_id = ? WHERE user_id = ?")->execute([$newRoleId, $targetUserId]);
```

---

### 7.6 Engineer-only rename user (`action: change_username`)
Ez nem ugyanaz, mint a „Profile oldalon a user saját rename-je”. Ez kifejezetten admin tool jelleg.

Szabályok:
- csak Engineer
- reason kötelező
- Engineer target nem rename-elhető
- foglaltság ellenőrzés

```php
if ($myRole !== 'Engineer') {
  json_response(["status" => "error", "message" => "Only Engineers can change usernames for others."], 403);
  return;
}

if (empty($reason)) {
  json_response(["status" => "error", "message" => "Reason is required."], 400);
  return;
}

if ($targetData['role_name'] === 'Engineer') {
  json_response(["status" => "error", "message" => "Engineers' names cannot be changed."], 403);
  return;
}
```

Update:

```php
$pdo->prepare($updateSql)->execute([$newUsername, $targetUserId]);
```

Email értesítés szintén best effort.

---

### 7.7 Logs (`action: get_logs`) — Statistics history olvasása
A Statistics táblában userenként több sor van. Az admin log lekérés az összes sort hozza `ORDER BY id DESC`.

```php
$stmt = $pdo->prepare("SELECT id, statistics_file, last_updated FROM `Statistics` WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$targetUserId]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

A response-ben a logok „parszolt” formában vannak visszaadva:

```php
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
```

---

### 7.8 Admin maps list (`action: get_user_maps`)
Ez a művelet nagyon hasonló a My Maps „mágikus lekérdezéséhez”, csak target userre.

```php
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
          )
          ORDER BY added_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$targetUserId, $targetUserId, $targetUserId]);
$maps = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_response(["status" => "success", "maps" => $maps], 200);
```

---

### 7.9 Admin remove map from library (`action: admin_remove_map`)
Ez csak a library linket törli, és csökkenti a downloads számlálót.

```php
$delStmt = $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
$delStmt->execute([$targetUserId, $mapId]);

if ($delStmt->rowCount() <= 0) {
  json_response(["status" => "error", "message" => "This map is not in the player's library."], 404);
  return;
}

$pdo->prepare("UPDATE `Maps` SET downloads = GREATEST(downloads - 1, 0) WHERE id = ?")->execute([$mapId]);

json_response(["status" => "success", "message" => "Map removed from player's library successfully!"], 200);
```

---

### 7.10 Admin edit map name (`action: admin_edit_map_name`)
```php
$pdo->prepare("UPDATE `Maps` SET map_name = ? WHERE id = ?")->execute([$newName, $mapId]);
json_response(["status" => "success", "message" => "Map name updated successfully!"], 200);
```

---

### 7.11 Hard delete user (`action: hard_delete_user`)
#### 7.11.1 Biztonsági megerősítés
```php
if ($confirmText !== 'CONFIRM') {
  json_response(["status" => "error", "message" => "Type CONFIRM to permanently delete this account."], 400);
  return;
}
```

#### 7.11.2 Jogosultság szabályok
- csak Admin/Engineer
- Engineer target nem törölhető
- Admin target törléséhez Engineer kell
- nem törölheted saját magad innen

```php
if (!in_array($myRole, ['Admin', 'Engineer'])) { ...403... }
if ($targetData['role_name'] === 'Engineer') { ...403... }
if ($targetData['role_name'] === 'Admin' && $myRole !== 'Engineer') { ...403... }
if ($targetUserId === $myUserId) { ...400... }
```

#### 7.11.3 Törlés transaction-ben (több tábla érintett)
A törlés fő lépései:
- a target user által created mapek ID-i
- törli a created mapekhez tartozó library linkeket + magukat a mapeket
- törli a target user library linkjeit és statjait
- best effort `Active_Web_Sessions` cleanup
- végül `User` törlés

```php
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
} catch (Exception $e) {}

$pdo->prepare("DELETE FROM `User` WHERE user_id = ?")->execute([$targetUserId]);
```

---

### 7.12 Admin „CRUD” összefoglaló
- Read:
  - `GET /app/api.php?path=admin` (user lista + view)
  - `POST /admin action=get_logs` (Statistics history)
  - `POST /admin action=get_user_maps` (user maps/library list)
- Update:
  - `POST /admin action=toggle_ban`
  - `POST /admin action=change_role`
  - `POST /admin action=change_username`
  - `POST /admin action=admin_edit_map_name`
- Delete:
  - `POST /admin action=admin_remove_map` (library link delete)
  - `POST /admin action=hard_delete_user` (hard delete, több tábla)
