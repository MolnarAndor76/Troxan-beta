## 8. game_* endpointok (a játék klienshez: token + stats sync)

### 8.1 Áttekintés: hol vannak bekötve?
A `game_*` endpointok a normál routerben vannak bekötve (`app/core/router/api.php`), és a webes MVC controllerektől eltérően **nem `load_controller()`-ral** mennek, hanem közvetlen `require + handle...()` hívással.

```php
// app/core/router/api.php (részlet)
case 'game_login':
  require API_CONTROLLERS . 'gameLoginController.php';
  handleGameLogin();
  break;

case 'game_stats':
  require API_CONTROLLERS . 'gameStatsController.php';
  handleGameStats();
  break;

// Ez menti el a statokat
case 'game_update_stats':
  require API_CONTROLLERS . 'gameUpdateStatsController.php';
  handleGameUpdateStats();
  break;
```

Közös cél:
- a játék (C# kliens) ne PHP sessionnel, hanem egy **Bearer token**-nel azonosítson
- a webes rész és a játék rész ugyanazt a `User` + `Statistics` DB-t használja

---

### 8.2 `POST /app/api.php?path=game_login` — játék login + token generálás

#### 8.2.1 Method: csak POST
```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(["status" => "error", "message" => "Method not allowed"], 405);
  return;
}
```

#### 8.2.2 Input: JSON body (username + password)
```php
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
```

Üres mezők:

```php
if (empty($username) || empty($password)) {
  json_response(["status" => "error", "message" => "Hiányzó felhasználónév vagy jelszó!"], 400);
  return;
}
```

#### 8.2.3 Auth + banned check (User tábla)
User lookup username alapján:

```php
$stmt = $pdo->prepare("SELECT user_id, username, password, is_banned FROM `User` WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

Jelszó ellenőrzés:

```php
if (!$user || !password_verify($password, $user['password'])) {
  json_response(["status" => "error", "message" => "Hibás felhasználónév vagy jelszó!"], 401);
  return;
}
```

Banned:

```php
if ($user['is_banned'] == 1) {
  json_response(["status" => "error", "message" => "A fiókod ki van tiltva a szerverről!"], 403);
  return;
}
```

#### 8.2.4 Token generálás + mentés a User táblába
A token egy random 32 byte (hex-ben 64 karakter):

```php
$token = bin2hex(random_bytes(32));
```

Mentés + last online:

```php
$updateStmt = $pdo->prepare("UPDATE `User` SET user_token = ?, last_time_online = NOW() WHERE user_id = ?");
$updateStmt->execute([$token, $user['user_id']]);
```

#### 8.2.5 Response: játék által várt forma
```php
json_response([
  "status" => "success",
  "message" => "Login successful!",
  "data" => [
    "user_id" => $user['user_id'],
    "username" => $user['username'],
    "token" => $token
  ]
], 200);
```

**Fejlesztői megjegyzés:**
- a játék innentől a tokent tárolja, és a többi game endpointnál Bearer tokennel azonosít.

---

### 8.3 `GET /app/api.php?path=game_stats` — játék stat lekérés tokennel

#### 8.3.1 Method: csak GET
```php
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_response(["status" => "error", "message" => "Method not allowed"], 405);
  return;
}
```

#### 8.3.2 „Golyóálló” Bearer token kiolvasás (Authorization header)
A controller több forrásból próbálja kiolvasni az Authorization headert (Apache/PHP környezetek miatt):

```php
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
  $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
  $authHeader = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
} elseif (function_exists('apache_request_headers')) {
  $requestHeaders = apache_request_headers();
  if (isset($requestHeaders['Authorization'])) {
    $authHeader = trim($requestHeaders['Authorization']);
  }
}
```

Bearer parse:

```php
if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
  json_response(["status" => "error", "message" => "Missing or invalid token. Please log in again."], 401);
  return;
}
$token = $matches[1];
```

#### 8.3.3 User lookup token alapján + banned check
```php
$stmt = $pdo->prepare("SELECT username, coins, level, is_banned FROM `User` WHERE user_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  json_response(["status" => "error", "message" => "Invalid or expired token."], 401);
  return;
}

if ($user['is_banned'] == 1) {
  json_response(["status" => "error", "message" => "Your account is banned."], 403);
  return;
}
```

#### 8.3.4 Response: explicit JSON encode (nem json_response)
Itt a controller direkt `echo json_encode(...)`-ot csinál:

```php
header('Content-Type: application/json');
echo json_encode([
  "status" => "success",
  "username" => $user['username'],
  "coins" => (int)$user['coins'],
  "level" => (int)$user['level']
]);
exit();
```

**Miért lehet ez így?**
- a komment szerint „szigorú JSON formátum”, amit a kliens vár.
- funkcionálisan a `json_response()` is jó lenne, de itt fixre van fogva.

---

### 8.4 `POST /app/api.php?path=game_update_stats` — coins/level + Statistics mentés (delta merge)

#### 8.4.1 Method: csak POST + token
Ugyanaz a „golyóálló token” kiolvasás, mint a `game_stats`-nál:

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(["status" => "error", "message" => "Method not allowed"], 405);
  return;
}

// token parse...
if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
  json_response(["status" => "error", "message" => "Missing or invalid token."], 401);
  return;
}
$token = $matches[1];
```

Input JSON:

```php
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  json_response(["status" => "error", "message" => "Invalid JSON format."], 400);
  return;
}
```

#### 8.4.2 User lookup token alapján + cheat check
```php
$stmt = $pdo->prepare("SELECT user_id, username, is_banned FROM `User` WHERE user_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  json_response(["status" => "error", "message" => "Invalid or expired token."], 401);
  return;
}

if ($user['is_banned'] == 1) {
  json_response(["status" => "error", "message" => "Your account is banned."], 403);
  return;
}

if (isset($input['username']) && $input['username'] !== $user['username']) {
  json_response(["status" => "error", "message" => "Cheat detected: You cannot modify another player's stats!"], 403);
  return;
}
```

#### 8.4.3 User tábla frissítés (coins, level)
```php
$coins = isset($input['coins']) ? (int)$input['coins'] : 0;
$level = isset($input['level']) ? (int)$input['level'] : 1;

$updateUser = $pdo->prepare("UPDATE `User` SET coins = ?, level = ? WHERE user_id = ?");
$updateUser->execute([$coins, $level, $user['user_id']]);
```

#### 8.4.4 Statistics mentés: „összegzett statok” + snapshot delta logika
Ha érkezik `statistics` objektum, akkor:
1) előző (legutolsó) Statistics sor lekérése
2) `array_merge(previousStats, incomingStats)`
3) kulcsok összehangolása aliasokkal (`score` vs `Experience points`, stb.)
4) delta számítás `_meta_last_snapshot` alapján
5) totals frissítés + alias kulcsok szinkronban tartása
6) INSERT új sor a `Statistics` táblába (`minden mentés új sor`)

Előző stat:

```php
$prevStmt = $pdo->prepare("SELECT statistics_file, last_updated FROM `Statistics` WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$prevStmt->execute([$user['user_id']]);
$prevRow = $prevStmt->fetch(PDO::FETCH_ASSOC);

$previousStats = [];
if ($prevRow && !empty($prevRow['statistics_file'])) {
  $decodedPrev = json_decode($prevRow['statistics_file'], true);
  if (is_array($decodedPrev)) $previousStats = $decodedPrev;
}
```

Counter map (canonical key + aliasok):

```php
$counterMap = [
  'num_of_story_finished' => ['num_of_story_finished', 'Story finished'],
  'num_of_enemies_killed' => ['num_of_enemies_killed', 'Mobs killed'],
  'num_of_deaths' => ['num_of_deaths', 'Deaths'],
  'score' => ['score', 'Experience points']
];
```

Snapshot és delta:

```php
$previousSnapshot = [];
if (isset($previousStats['_meta_last_snapshot']) && is_array($previousStats['_meta_last_snapshot'])) {
  $previousSnapshot = $previousStats['_meta_last_snapshot'];
}

$nextSnapshot = [];
foreach ($counterMap as $canonicalKey => $aliases) {
  $incomingValue = troxan_stats_pick_int($incomingStats, $aliases, 0);
  $previousTotal = troxan_stats_pick_int($previousStats, $aliases, 0);
  $previousSeen = isset($previousSnapshot[$canonicalKey]) ? (int)$previousSnapshot[$canonicalKey] : null;

  if ($previousSeen === null) {
    $delta = $incomingValue;
  } elseif ($incomingValue >= $previousSeen) {
    $delta = $incomingValue - $previousSeen;
  } else {
    // counter reset
    $delta = $incomingValue;
  }

  if ($delta < 0) $delta = 0;

  $newTotal = $previousTotal + $delta;
  $nextSnapshot[$canonicalKey] = $incomingValue;

  $mergedStats[$canonicalKey] = $newTotal;
}
```

Alias kulcsok szinkron:

```php
$mergedStats['Story finished'] = $mergedStats['num_of_story_finished'];
$mergedStats['Mobs killed'] = $mergedStats['num_of_enemies_killed'];
$mergedStats['Deaths'] = $mergedStats['num_of_deaths'];
$mergedStats['Experience points'] = $mergedStats['score'];
$mergedStats['_meta_last_snapshot'] = $nextSnapshot;
```

INSERT új Statistics sor:

```php
$statsJson = json_encode($mergedStats);
$insertStat = $pdo->prepare("INSERT INTO `Statistics` (user_id, statistics_file, last_updated) VALUES (?, ?, NOW())");
$insertStat->execute([$user['user_id'], $statsJson]);
```

#### 8.4.5 Response
```php
json_response([
  "status" => "success",
  "message" => "Stats updated successfully!"
], 200);
```

---

### 8.5 game_* „CRUD” összefoglaló
- Create (token létrehozás és mentés):
  - `POST /game_login` → `User.user_token` update
- Read (stats lekérés):
  - `GET /game_stats` + `Authorization: Bearer <token>` → `User.username/coins/level`
- Update (coins/level + stat history):
  - `POST /game_update_stats` + `Authorization: Bearer <token>` → `User` update + `Statistics` insert (új sor)
