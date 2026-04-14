## 2. API konvenciók és endpoint minták (GET view render vs POST action)

### 2.1 Alapelv: minden az `/app/api.php?path=...` kapun megy be
A Troxan-beta webes appban a backend belépési pontja az `app/api.php`. A kliens jellemzően így hív:

- Oldal (view) betöltés: `GET /app/api.php?path=<route>`
- Műveletek: `POST /app/api.php?path=<route>` + JSON body (gyakran `action` mezővel)
- Egyes helyeken: `PUT` / `PATCH` is előfordul (főleg auth jellegű folyamatoknál)

A route-ot a router a `path` paraméterből állítja elő, a controllert a `segment1` dönti el:

```php
// app/core/router.php
$path = trim($_GET['path'] ?? '', '/');
$segments = ($path === '') ? [] : explode('/', $path);
$method = $_SERVER['REQUEST_METHOD'];

$route = [
  'segment1' => $segments[0] ?? null,
  'segment2' => $segments[1] ?? null,
  'segment3' => $segments[2] ?? null,
];
```

```php
// app/core/router/api.php (részlet)
switch ($route['segment1']) {
  case "maps": load_controller($data, API_CONTROLLERS . 'mapsController.php'); break;
  case "my_maps": load_controller($data, API_CONTROLLERS . 'myMapsController.php'); break;
  case "login": load_controller($data, API_CONTROLLERS . 'loginController.php'); break;
  case "registration": load_controller($data, API_CONTROLLERS . 'registrationController.php'); break;
  case "profile": load_controller($data, API_CONTROLLERS . 'profileController.php'); break;
  case "admin": load_controller($data, API_CONTROLLERS . 'adminController.php'); break;
  // ...
}
```

---

### 2.2 Standard válaszküldés: `json_response()`
A projektben a controllerek a `json_response()` helperrel válaszolnak.

```php
// app/core/router.php
function json_response($data, int $statusCode = 200): void
{
  http_response_code($statusCode);
  header('Content-Type: application/json');
  echo json_encode($data, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_QUOT);
  exit;
}
```

A tipikus response mezők:
- `status`: `"success" | "error" | "info"`
- `message`: üzenet
- `html`: HTML view string (amikor a GET oldalbetöltés HTML-t ad vissza)
- domain specifikus: pl. `user`, `maps`, `logs`

---

### 2.3 Kliens oldali API hívások alapmintája (`credentials: include`)
A beléptetett funkciók nagy részénél fontos, hogy a session cookie menjen a requesttel:

```js
fetch('/app/api.php?path=admin', {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'get_logs', target_user_id: userId })
}).then(res => res.json()).then(data => { /* ... */ });
```

A `src/main.js` saját fetch wrapperrel kezeli az auth állapotot:

```js
// src/main.js (részlet)
const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
credentials: isLoggedIn ? 'include' : 'omit'
```

401 esetén loginra navigál:

```js
if (response.status === 401) {
  clearClientAuthState();
  navigateTo('login');
  throw new Error('Session expired. Please log in again.');
}
```

---

### 2.4 „GET = view render” minta (HTML JSON-ban)
A legtöbb webes oldal GET-re HTML-t generál a view-ból, és JSON-ban visszaküldi.

Példa: login (GET)

```php
// app/controllers/api/loginController.php
function getContent() {
  ob_start();
  require VIEWS . 'login/login.php';
  $buffer = ob_get_clean();
  json_response(["html" => $buffer, "status" => "success"], 200);
}
```

Példa: admin (GET) — jogosultság ellenőrzéssel, majd view render:

```php
// app/controllers/api/adminController.php (részlet)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
  json_response(["status" => "error", "message" => "Unauthorized access."], 401);
  return;
}

$checkStmt = $pdo->prepare("SELECT r.role_name FROM `User` u JOIN Roles r ON u.role_id = r.id WHERE u.user_id = ?");
$checkStmt->execute([$_SESSION['user_id']]);

if (!in_array($checkStmt->fetchColumn(), ['Admin', 'Engineer'])) {
  json_response(["status" => "error", "message" => "Only Admins and Engineers can access this area."], 403);
  return;
}

ob_start();
require VIEWS . 'admin/admin.php';
$buffer = ob_get_clean();
json_response(["html" => $buffer, "status" => "success"], 200);
```

---

### 2.5 „POST = action” minta (RPC-szerű műveletek)
A projekt több controllerében POST-ban az `action` kulcs dönti el, milyen művelet történjen.

#### 2.5.1 Maps: `POST /...maps` (delete/restore/add_to_library)
A `mapsController.php` `handlePost()` beolvassa a JSON body-t:

```php
// app/controllers/api/mapsController.php (részlet)
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$mapId = (int)($input['map_id'] ?? 0);
```

„Add to library” (CRUD: Create a User_Map_Library sor + Map downloads increment):

```php
// app/controllers/api/mapsController.php (részlet)
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

„Delete map” (CRUD: Update Maps.status, logika staff/engineer alapján):

```php
// app/controllers/api/mapsController.php (részlet)
if ($action === 'delete_map') {
  $stmt = $pdo->prepare("SELECT status FROM `Maps` WHERE id = ?");
  $stmt->execute([$mapId]);
  $currentStatus = $stmt->fetchColumn();

  $newStatus = ($isStaff && $mapData['creator_user_id'] != $currentUserId) ? 4 : (($currentStatus == 0) ? 5 : 3);
  $pdo->prepare("UPDATE `Maps` SET status = ? WHERE id = ?")->execute([$newStatus, $mapId]);

  json_response(["status" => "success", "message" => "Map moved to trash!"], 200);
}
```

„Restore map” (CRUD: Update Maps.status vissza 1-re, staff-only):

```php
// app/controllers/api/mapsController.php (részlet)
} elseif ($action === 'restore_map' && $isStaff) {
  $pdo->prepare("UPDATE `Maps` SET status = 1 WHERE id = ?")->execute([$mapId]);
  json_response(["status" => "success", "message" => "Map restored successfully!"], 200);
}
```

---

#### 2.5.2 Admin: `POST /...admin` (több action ugyanazon endpointon)
Az admin controller egy POST routert használ:

```php
// app/controllers/api/adminController.php (részlet)
switch ($data["method"]) {
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
    } else {
      json_response(["status" => "error", "message" => "Hiányzó action paraméter"], 400);
    }
    break;
}
```

**Következmény fejlesztői szemmel:**
- Az „admin API” valójában több al-funkció (ban, role, logs, hard delete, maps library), és ezeket az `action` különíti el.

---

### 2.6 PUT/PATCH használat (auth jellegű folyamatoknál)
A registration controller több methodot is támogat.

```php
// app/controllers/api/registrationController.php
switch ($data["method"]) {
  case 'GET':
    getContent();
    break;

  case 'POST':
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    if (isset($input['action']) && $input['action'] === 'verify_code') {
      verifyRegistrationCode();
    } else {
      registerUser();
    }
    break;

  case 'PUT':
    verifyRegistrationCode();
    break;

  default:
    json_response(["status" => "error", "message" => "Method not allowed"], 405);
    break;
}
```

Ezzel a flow-val:
- `POST /registration` = user létrehozása
- `POST /registration` + `{ action:"verify_code" }` = email verifikáció (action-os mód)
- `PUT /registration` = email verifikáció (REST-szerű update)

---

### 2.7 Rövid API „CRUD térkép” (a kódban ténylegesen látható műveletek)
A projekt nem mindenhol tiszta REST (nem mindig `POST/PUT/DELETE` egy entity-re), ezért itt a „CRUD” mappinget a tényleges hívásokkal érdemes rögzíteni.

#### 2.7.1 Maps / User_Map_Library (kliens: Maps + My Maps)
- Read (view):
  - `GET /app/api.php?path=maps`
- Create (library-be felvétel):
  - `POST /app/api.php?path=maps` + `{ action:"add_to_library", map_id }`
- Update (trash/restore):
  - `POST /app/api.php?path=maps` + `{ action:"delete_map", map_id }`
  - `POST /app/api.php?path=maps` + `{ action:"restore_map", map_id }` (staff)

#### 2.7.2 Admin user management (User tábla)
- Read (view):
  - `GET /app/api.php?path=admin` (user list + view render)
- Update:
  - `POST /app/api.php?path=admin` + `{ action:"toggle_ban", target_user_id, reason }`
  - `POST /app/api.php?path=admin` + `{ action:"change_role", role_action, target_user_id }`
  - `POST /app/api.php?path=admin` + `{ action:"change_username", target_user_id, new_username, reason }`
- Read (logs):
  - `POST /app/api.php?path=admin` + `{ action:"get_logs", target_user_id }`
- Delete (hard delete user):
  - `POST /app/api.php?path=admin` + `{ action:"hard_delete_user", target_user_id, confirm_text:"CONFIRM" }`

#### 2.7.3 Admin maps library (User_Map_Library + Maps)
- Read:
  - `POST /app/api.php?path=admin` + `{ action:"get_user_maps", target_user_id }`
- Delete (remove from library):
  - `POST /app/api.php?path=admin` + `{ action:"admin_remove_map", target_user_id, map_id }`
- Update (rename map):
  - `POST /app/api.php?path=admin` + `{ action:"admin_edit_map_name", map_id, new_name }`

---

### 2.8 Praktikus debug tippek (API fejlesztői szemmel)
- Minden requestnél figyeld a Network tabon:
  - URL: `/app/api.php?path=...`
  - Status code: 200/201/400/401/403/404/409/500
  - Response JSON: `status`, `message`, `html`, stb.
- Session problémáknál a `src/main.js` 401 esetén törli a localStorage auth state-et és loginra navigál.
- Banned user esetén a router oldalán route átírás történik `isBanned`-re, ami úgy néz ki a kliensnek, mintha „mást kapna vissza” ugyanarra a route-ra.
