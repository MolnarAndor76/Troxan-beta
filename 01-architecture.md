## 5. Profile — GET view + profile update/delete (action + RESTful methodok)

### 5.1 Endpoint
- View: `GET /app/api.php?path=profile`
- Update (vegyes):
  - `POST /app/api.php?path=profile` + `action` (legacy mód)
  - `PUT /app/api.php?path=profile` (REST-szerű update)
  - `PATCH /app/api.php?path=profile` + `action` (legacy)
- Delete account:
  - `DELETE /app/api.php?path=profile` + `{ confirm_text: "CONFIRM" }`

Backend: `app/controllers/api/profileController.php`  
Frontend: `src/profile-src/profile.js`

---

### 5.2 GET: profile view render + stat + rank + avatar lista
Ha nincs bejelentkezés, a controller guest view-t ad vissza:

```php
// app/controllers/api/profileController.php
if (!isset($_SESSION['user_id'])) {
  ob_start();
  require VIEWS . 'guest/guest.php';
  $buffer = ob_get_clean();
  json_response(["html" => $buffer, "status" => "success", "message" => "Redirected to guest"], 200);
  return;
}
```

Bejelentkezve:
1) user adatok (username/email/created_at/last_time_online/last_username_change + role + avatar)
2) legutolsó Statistics sor a userhez (ORDER BY id DESC LIMIT 1)
3) leaderboard rank számolás (összes user score → sort → pozíció)
4) összes avatar lekérés a modálhoz
5) view render: `views/profile/profile.php`

User select:

```php
$stmt = $pdo->prepare("
  SELECT u.username, u.email, u.created_at, u.last_time_online, u.last_username_change,
         r.role_name, a.avatar_picture
  FROM `User` u
  JOIN Roles r ON u.role_id = r.id
  LEFT JOIN Avatars a ON u.avatar_id = a.id
  WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

Legutolsó stat:

```php
$stmtStats = $pdo->prepare("
  SELECT statistics_file, last_updated
  FROM `Statistics`
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT 1
");
$stmtStats->execute([$userId]);
```

---

### 5.3 Profile update — két stílus (legacy action és REST)
A controllerben több út is van a frissítésre.

#### 5.3.1 Legacy action alapú POST (példák: avatar, username, password)
A `handlePostActionsLegacy()` a JSON body-ból `action`-t olvas:

```php
$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
```

##### a) Avatar csere (`action: change_avatar`)
```php
if ($action === 'change_avatar') {
  $avatar_id = isset($input['avatar_id']) ? (int)$input['avatar_id'] : 0;
  $stmt = $pdo->prepare("UPDATE `User` SET avatar_id = ? WHERE user_id = ?");
  $stmt->execute([$avatar_id, $userId]);
  json_response(["status" => "success", "message" => "Avatar updated successfully!"], 200);
}
```

Frontend oldalon kattintásra elküldi:

```js
// src/profile-src/profile.js (részlet)
fetch(profileUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'change_avatar', avatar_id: selectedAvatarId })
});
```

##### b) Username csere (`action: change_username`) + cooldown logika
A backend:
- ellenőrzi az ürességet
- non-engineer esetén 4–12
- non-engineer esetén 30 napos cooldown (`last_username_change`)
- ellenőrzi foglaltságot
- update + session username frissítés
- email küldés

Cooldown rész:

```php
if (!$isEngineer && $userData['last_username_change'] !== null) {
  $lastTimestamp = strtotime($userData['last_username_change']);
  $nextAvailable = $lastTimestamp + (30 * 24 * 60 * 60);

  if (time() < $nextAvailable) {
    $daysLeft = ceil(($nextAvailable - time()) / (24 * 60 * 60));
    json_response(["status" => "error", "message" => "You must wait {$daysLeft} more day(s) before changing your username again."], 403);
  }
}
```

Frontend oldalon (PATCH + action):

```js
fetch(profileUrl, {
  method: 'PATCH',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'change_username', new_username: newName.trim() })
})
```

##### c) Password csere (`action: change_password`)
A frontend POST-ban küldi:

```js
fetch(profileUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'change_password',
    old_password: oldPass,
    new_password: newPass,
    confirm_password: confirmPass
  })
})
```

A backend erősen validál (prioritásos üzenetek), majd hash + update:

```php
$newHashedPass = password_hash($newPass, PASSWORD_DEFAULT);
$updateSql = $hasLastPasswordChange
  ? "UPDATE `User` SET password = ?, last_password_change = NOW() WHERE user_id = ?"
  : "UPDATE `User` SET password = ? WHERE user_id = ?";
$update = $pdo->prepare($updateSql);
$update->execute([$newHashedPass, $userId]);
```

---

#### 5.3.2 REST-szerű update: `updateProfile()` (PUT vagy POST action nélkül)
Ha a POST body-ban nincs `action`, akkor a controller `updateProfile()`-t hívja:

```php
function handlePostActions()
{
  $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

  if (isset($input['action']) && !empty($input['action'])) {
    handlePostActionsLegacy();
    return;
  }

  updateProfile();
}
```

A `updateProfile()` dinamikusan épít SQL-t az alapján, mi van a body-ban:
- avatar_id
- username
- old/new/confirm password

SQL összeállítás:

```php
$sql = "UPDATE `User` SET " . implode(', ', $updates) . " WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
```

---

### 5.4 Delete profile: `DELETE /profile` + confirm
A törléshez `confirm_text` kötelező:

```php
$confirmText = strtoupper(trim($input['confirm_text'] ?? ''));
if ($confirmText !== 'CONFIRM') {
  json_response(["status" => "error", "message" => "Type CONFIRM to delete your profile."], 400);
  return;
}
```

A törlés érinti:
- Maps, amiket a user created (törli a library linkeket is)
- User_Map_Library sorok a userhez
- Statistics sorok
- Active_Web_Sessions best effort
- User sor

```php
$pdo->prepare("DELETE FROM `User_Map_Library` WHERE user_id = ?")->execute([$userId]);
$pdo->prepare("DELETE FROM `Statistics` WHERE user_id = ?")->execute([$userId]);
$pdo->prepare("DELETE FROM `User` WHERE user_id = ?")->execute([$userId]);
```

A végén session destroy:

```php
session_unset();
session_destroy();
json_response(["status" => "success", "message" => "User account deleted."], 200);
```

---

### 5.5 Profile „CRUD” összefoglaló (kódbeli műveletekkel)
- Read:
  - `GET /app/api.php?path=profile` (profile view + stat + rank)
- Update:
  - Avatar: `POST /profile` + `{ action:"change_avatar", avatar_id }`
  - Username: `PATCH /profile` + `{ action:"change_username", new_username }`
  - Password: `POST /profile` + `{ action:"change_password", old_password, new_password, confirm_password }`
  - REST update: `PUT /profile` (body mezők alapján)
- Delete:
  - `DELETE /profile` + `{ confirm_text:"CONFIRM" }`
