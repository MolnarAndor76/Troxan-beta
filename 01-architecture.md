## 4. My Maps (könyvtár) — GET view + POST action CRUD + kliens oldali működés

### 4.1 Endpoint
- View: `GET /app/api.php?path=my_maps`
- Műveletek: `POST /app/api.php?path=my_maps` + `{ action: ... }`

Backend controller: `app/controllers/api/myMapsController.php`  
Frontend modul: `src/myMaps-src/myMaps.js`

---

### 4.2 GET: My Maps lista lekérdezése + view render
A My Maps oldal csak belépve érhető el:

```php
// app/controllers/api/myMapsController.php
if (!isset($_SESSION['user_id'])) {
  json_response(["status" => "error", "message" => "Login required"], 401);
  return;
}
```

A „mágikus” lekérdezés egyszerre hozza:
1) a saját készítésű mapeket (status: 0 Draft, 1 Public, 3 Unpublished)
2) a könyvtárba mentett mapeket (status: 1, 3, vagy 5 = creator által törölt, de a user library-ben megmaradhat)

```php
// app/controllers/api/myMapsController.php (részlet)
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
```

A view render:

```php
ob_start();
require VIEWS . 'myMaps/myMaps.php';
$buffer = ob_get_clean();
json_response(["html" => $buffer, "status" => "success"], 200);
```

---

### 4.3 POST actions (CRUD műveletek)

#### 4.3.1 Remove map a könyvtárból (`action: remove_map`)
Ez a művelet:
- törli a kapcsolatot a `User_Map_Library` táblából
- ha tényleg törölt sort (`rowCount() > 0`), akkor csökkenti a `Maps.downloads` értéket `GREATEST(...,0)`-val
- ha a user a creator is, akkor a map statusát 5-re állítja (Scrapped)

```php
// app/controllers/api/myMapsController.php (részlet)
if ($action === 'remove_map') {
  $stmt = $pdo->prepare("SELECT creator_user_id, status FROM `Maps` WHERE id = ?");
  $stmt->execute([$mapId]);
  $mapData = $stmt->fetch(PDO::FETCH_ASSOC);

  $delStmt = $pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ? AND map_id = ?");
  $delStmt->execute([$currentUserId, $mapId]);

  if ($delStmt->rowCount() > 0) {
    $pdo->prepare("UPDATE `Maps` SET downloads = GREATEST(downloads - 1, 0) WHERE id = ?")->execute([$mapId]);
  }

  if ($mapData['creator_user_id'] == $currentUserId) {
    $pdo->prepare("UPDATE `Maps` SET status = 5 WHERE id = ?")->execute([$mapId]);
  }

  json_response(["status" => "success", "message" => "Map removed from your library successfully!"], 200);
}
```

#### 4.3.2 Publish / Unpublish (`action: toggle_publish`)
Csak a creator publikálhatja a saját mapjét:
- ha status=1 → 3 (Unpublished)
- ha status=0 vagy 3 → 1 (Public)

```php
// app/controllers/api/myMapsController.php (részlet)
} elseif ($action === 'toggle_publish') {
  $stmt = $pdo->prepare("SELECT creator_user_id, status FROM `Maps` WHERE id = ?");
  $stmt->execute([$mapId]);
  $mapData = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($mapData['creator_user_id'] != $currentUserId) {
    json_response(["status" => "error", "message" => "You can only publish your own maps."], 403);
  }

  $newStatus = ($mapData['status'] == 1) ? 3 : 1;
  $pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);

  json_response(["status" => "success", "message" => $msg, "new_status" => $newStatus], 200);
}
```

#### 4.3.3 Rename map (`action: edit_map_name`)
Szabályok:
- `new_name` nem üres
- max 64 karakter
- csak a creator VAGY staff (Admin/Moderator/Engineer) nevezheti át
- status=5 (Scrapped) map nem nevezhető át

```php
// app/controllers/api/myMapsController.php (részlet)
} elseif ($action === 'edit_map_name') {
  if (mb_strlen($newName) > 64) {
    json_response(["status" => "error", "message" => "Map name is too long (max 64 characters)."], 400);
  }

  $stmt = $pdo->prepare("SELECT creator_user_id, status FROM `Maps` WHERE id = ?");
  $stmt->execute([$mapId]);
  $mapData = $stmt->fetch(PDO::FETCH_ASSOC);

  if ((int)$mapData['creator_user_id'] !== (int)$currentUserId && !$isStaff) {
    json_response(["status" => "error", "message" => "Only the creator or staff can rename this map."], 403);
  }

  if ((int)$mapData['status'] === 5) {
    json_response(["status" => "error", "message" => "Scrapped maps cannot be edited."], 400);
  }

  $pdo->prepare("UPDATE `Maps` SET map_name = ? WHERE id = ?")->execute([$newName, $mapId]);
  json_response(["status" => "success", "message" => "Map name updated successfully!"], 200);
}
```

---

### 4.4 Frontend (My Maps JS) — hogyan hívja az API-t

#### 4.4.1 Endpoint konstans
```js
// src/myMaps-src/myMaps.js
const myMapUrl = `/app/api.php?path=my_maps`;
```

#### 4.4.2 Rename mentés (POST edit_map_name)
```js
fetch(myMapUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'edit_map_name', map_id: pendingRename.mapId, new_name: newName })
})
```

#### 4.4.3 Publish/unpublish (POST toggle_publish)
```js
fetch(myMapUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'toggle_publish', map_id: mapId })
})
```

#### 4.4.4 Remove (POST remove_map + confirm modal)
```js
fetch(myMapUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'remove_map', map_id: mapId })
})
```
