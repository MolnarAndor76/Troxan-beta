## 6. Maps (public list + staff trash) — GET view + POST action CRUD

### 6.1 Endpoint és cél
A Maps oldal a „public maps” listát adja, illetve staff szerepkörben egy „trash” listát is.

- View: `GET /app/api.php?path=maps`
- Műveletek: `POST /app/api.php?path=maps` + `{ action: ... }`

Backend: `app/controllers/api/mapsController.php`  
Frontend: `src/maps-src/maps.js` (a view-hoz tartozó UI logika)

---

### 6.2 Belépés követelmény (auth guard)
A controller elején látszik, hogy a Maps csak belépett usernek elérhető:

```php
// app/controllers/api/mapsController.php
if (!isset($_SESSION['user_id'])) {
  json_response(["status" => "error", "message" => "Login required"], 401);
  return;
}
```

Frontend oldalon a `src/main.js` is „guardol”:
- ha nincs `localStorage.isLoggedIn === 'true'`, akkor a `/maps` route helyett `guest` view töltődik.

```js
// src/main.js (részlet)
case 'maps': isLoggedIn ? getMapsContent() : getGuestContent(); break;
```

Fontos: ez **kettős védelem**:
- a kliens nem engedi UI-ból könnyen,
- de a szerver is véd 401-gyel.

---

### 6.3 GET: public maps list + staff trash list + view render

#### 6.3.1 Query paraméterek: `search` és `sort`
A controller GET-ben olvassa:

```php
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'Downloads';
```

A `sort` értékei:
- `Alphabetical`
- `Most recent`
- `Oldest`
- default: `Downloads`

#### 6.3.2 Staff / Engineer flag
A Maps oldalnál staff logikát a `role_name` session értékéből számolja:

```php
$roleName = $_SESSION['role_name'] ?? 'Player';
$isStaff = in_array($roleName, ['Admin', 'Moderator', 'Engineer']);
$isEngineer = ($roleName === 'Engineer');
```

Megjegyzés:
- `isEngineer` itt a POST műveleteknél fontos, mert bizonyos „Engineer creator” mapekhez külön szabály van.

#### 6.3.3 Active maps lekérdezés (status = 1)
A public listában csak a `Maps.status = 1` jön.

Plusz egy extra UX mező: `is_in_library` (bal join `User_Map_Library` alapján).

```php
$currentUserId = $_SESSION['user_id'] ?? 0;

$query = "SELECT m.*, u.username as creator_name, r.role_name as creator_role,
         CASE WHEN uml.map_id IS NOT NULL THEN 1 ELSE 0 END as is_in_library
      FROM `Maps` m
      LEFT JOIN `User_Map_Library` uml ON m.id = uml.map_id AND uml.user_id = ?
          JOIN `User` u ON m.creator_user_id = u.user_id
          JOIN Roles r ON u.role_id = r.id
      WHERE m.status = 1";
$params = [$currentUserId];
```

Search feltétel:

```php
if (!empty($search)) {
  $query .= " AND (m.map_name LIKE ? OR u.username LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}
```

Sort:

```php
switch ($sort) {
  case 'Alphabetical': $query .= " ORDER BY m.map_name ASC"; break;
  case 'Most recent':  $query .= " ORDER BY m.created_at DESC"; break;
  case 'Oldest':       $query .= " ORDER BY m.created_at ASC"; break;
  default:             $query .= " ORDER BY m.downloads DESC"; break;
}
```

Lekérés:

```php
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$active_maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

#### 6.3.4 Trash maps lekérdezés (staff only)
A staff külön listát kap `status IN (3,4,5)` alapján:

```php
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
```

#### 6.3.5 View render (maps.php)
```php
ob_start();
require VIEWS . 'maps/maps.php';
$buffer = ob_get_clean();
json_response(["html" => $buffer, "status" => "success"], 200);
```

---

### 6.4 POST actions (CRUD műveletek)

A `handlePost()` JSON body-ból olvas:

```php
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$mapId = (int)($input['map_id'] ?? 0);
```

Majd role + user:

```php
$roleName = $_SESSION['role_name'] ?? 'Player';
$isStaff = in_array($roleName, ['Admin', 'Moderator', 'Engineer']);
$isEngineer = ($roleName === 'Engineer');
$currentUserId = $_SESSION['user_id'] ?? null;
```

---

#### 6.4.1 `action: add_to_library` (CRUD: Create link + Update downloads)
A logika:
1) ellenőrzi, hogy már benne van-e a library-ben
2) `Maps.downloads = downloads + 1`
3) INSERT `User_Map_Library(user_id, map_id)`

```php
} elseif ($action === 'add_to_library') {
  $checkStmt = $pdo->prepare("SELECT 1 FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
  $checkStmt->execute([$currentUserId, $mapId]);
  if ($checkStmt->fetchColumn()) {
    json_response(["status" => "info", "message" => "This map is already in your My Maps library!"], 200);
    return;
  }

  $pdo->prepare("UPDATE `Maps` SET downloads = downloads + 1 WHERE id = ?")->execute([$mapId]);

  $libStmt = $pdo->prepare("INSERT INTO `User_Map_Library` (user_id, map_id) VALUES (?, ?)");
  $libStmt->execute([$currentUserId, $mapId]);

  json_response(["status" => "success", "message" => "Map successfully added to My Maps!"], 201);
}
```

Fejlesztői megjegyzés:
- Itt a „downloads” valójában inkább „library adds” jellegű számlálóként viselkedik.

---

#### 6.4.2 `action: delete_map` (CRUD: Update Maps.status)
A törlés itt nem feltétlen „hard delete”, hanem státusz változtatás.

A logika előtt van egy jogosultság ellenőrzés:
- Ha a map készítője Engineer, akkor csak Engineer vagy a creator törölheti.

```php
$checkStmt = $pdo->prepare("SELECT m.creator_user_id, r.role_name
                            FROM `Maps` m
                            JOIN `User` u ON m.creator_user_id = u.user_id
                            JOIN Roles r ON u.role_id = r.id
                            WHERE m.id = ?");
$checkStmt->execute([$mapId]);
$mapData = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($mapData['role_name'] === 'Engineer' && !$isEngineer && $mapData['creator_user_id'] != $currentUserId) {
  json_response(["status" => "error", "message" => "Only the Creator can delete this map!"], 403);
}
```

Aktuális státusz lekérése:

```php
$stmt = $pdo->prepare("SELECT status FROM `Maps` WHERE id = ?");
$stmt->execute([$mapId]);
$currentStatus = $stmt->fetchColumn();
```

Új státusz számítása:

```php
$newStatus = ($isStaff && $mapData['creator_user_id'] != $currentUserId)
  ? 4
  : (($currentStatus == 0) ? 5 : 3);
```

Mit jelent ez?
- **Staff és nem ő a creator** → `status = 4` (egy „staff trash” típus)
- különben:
  - ha `status == 0` (Draft?) → `status = 5` (Scrapped)
  - egyébként → `status = 3` (Unpublished / Trash jelleg)

Mentés:

```php
$pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);
json_response(["status" => "success", "message" => "Map moved to trash!"], 200);
```

---

#### 6.4.3 `action: restore_map` (CRUD: Update Maps.status = 1, staff only)
Csak staff képes restore-ra, és ugyanaz az Engineer creator védelem itt is megvan:

```php
} elseif ($action === 'restore_map' && $isStaff) {
  // ... ugyanaz a creator/Engineer check ...
  $pdo->prepare("UPDATE `Maps` SET status = 1 WHERE id = ?")->execute([$mapId]);
  json_response(["status" => "success", "message" => "Map restored successfully!"], 200);
}
```

---

### 6.5 Controller method routing (GET/POST)
```php
switch ($data["method"]) {
  case 'GET':  getContent(); break;
  case 'POST': handlePost(); break;
  default: json_response(["status" => "error", "message" => "Method not allowed"], 405); break;
}
```

---

### 6.6 Maps „CRUD” összefoglaló
- Read:
  - `GET /app/api.php?path=maps` (public maps list; staff esetén trash is)
- Create:
  - `POST /app/api.php?path=maps` + `{ action:"add_to_library", map_id }` → `User_Map_Library` insert
- Update:
  - `POST /app/api.php?path=maps` + `{ action:"delete_map", map_id }` → `Maps.status` update
  - `POST /app/api.php?path=maps` + `{ action:"restore_map", map_id }` → `Maps.status=1`
- Delete:
  - itt nincs klasszikus HTTP DELETE; a törlés státusz-alapú (soft delete jelleg)
