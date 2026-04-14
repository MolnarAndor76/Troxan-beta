## 3. Auth flows (registration + login + logout + kliens session state)

### 3.1 Közös fogalmak: mi számít „bejelentkezett” állapotnak?

#### 3.1.1 Szerver oldali auth (PHP session)
A backend oldalon a bejelentkezés alapjai (tipikusan):
- `$_SESSION['user_id']`
- `$_SESSION['username']`
- `$_SESSION['role_name']`
- `$_SESSION['logged_in'] = true`

A `loginController.php` sikeres login után állítja be ezeket.

```php
// app/controllers/api/loginController.php (részlet)
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['role_name'] = $user['role_name'] ?? 'Player';
$_SESSION['logged_in'] = true;
```

#### 3.1.2 Kliens oldali auth (localStorage)
A kliens oldalon a UI állapot (header, route guard) localStorage-ból megy:
- `isLoggedIn` (string: `"true"|"false"`)
- `username`
- `userAvatar`

Sikeres login után ezt a `src/login-src/login.js` állítja:

```js
// src/login-src/login.js (részlet)
if (response.ok) {
  localStorage.setItem('isLoggedIn', 'true');
  if (result.user && result.user.username) {
    localStorage.setItem('username', result.user.username);
  }
  if (result.user && result.user.avatar) {
    localStorage.setItem('userAvatar', result.user.avatar);
  }
}
```

A header frissítése a `src/main.js`-ben lévő `updateHeader()`-rel történik:

```js
// src/main.js (részlet)
export function updateHeader() {
  const username = localStorage.getItem('username');
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const userAvatar = localStorage.getItem('userAvatar') || 'https://picsum.photos/id/1025/200/200';
  // ... UI csere Login ↔ Avatar/Profile
}
```

---

### 3.2 Registration flow (`/app/api.php?path=registration`)

#### 3.2.1 GET: registration view render
A registration controller GET esetben a `views/registration/registration.php`-t rendereli HTML-ként és JSON-ban visszaküldi.

```php
// app/controllers/api/registrationController.php (részlet)
function getContent()
{
  ob_start();
  require VIEWS . 'registration/registration.php';
  $buffer = ob_get_clean();

  json_response([
    "html" => $buffer,
    "status" => "success",
    "message" => ""
  ], 200);
}
```

#### 3.2.2 POST: register (user létrehozás + Settings + Statistics + email verification code)
A `registerUser()` több táblát érint (transaction-ben):

- `Settings` INSERT
- `User` INSERT (`role_id`, `avatar_id` default)
- `Statistics` INSERT
- `User` UPDATE: `verification_code`, `verification_expires`, `is_verified = 0`
- email küldés (mailer)

Fontos validációk:
- username: 4–12 karakter + alfanumerikus
- email format
- password min 8 + confirm egyezzen

```php
// app/controllers/api/registrationController.php (részlet)
if (strlen($username) > 12 || strlen($username) < 4 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
  json_response(["status" => "error", "message" => "Username must be 4-12 characters and contain only letters and numbers!"], 400);
}

if (strlen($password) < 8) {
  json_response(["status" => "error", "message" => "Password must be at least 8 characters long!"], 400);
}

if ($password !== $passwordConfirm) {
  json_response(["status" => "error", "message" => "Passwords do not match!"], 400);
}
```

Transaction és insert-ek:

```php
// app/controllers/api/registrationController.php (részlet)
$pdo->beginTransaction();

// 1. Create Settings
$stmtSettings = $pdo->prepare("INSERT INTO Settings (settings_file) VALUES (?)");
$stmtSettings->execute([$defaultJson]);
$settingsId = $pdo->lastInsertId();

// 2. Create User
$stmtUser = $pdo->prepare("INSERT INTO User (username, email, password, savestate_file, role_id, settings_id, avatar_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmtUser->execute([$username, $email, $hashedPassword, $defaultJson, $defaultRoleId, $settingsId, $defaultAvatarId]);
$userId = $pdo->lastInsertId();

// 3. Create Statistics
$stmtStats = $pdo->prepare("INSERT INTO Statistics (user_id, statistics_file) VALUES (?, ?)");
$stmtStats->execute([$userId, $defaultJson]);

// 4. Generate verification code + expiry and save
$verificationCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
$verificationExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmtUpdate = $pdo->prepare("UPDATE `User` SET is_verified = 0, verification_code = ?, verification_expires = ? WHERE user_id = ?");
$stmtUpdate->execute([$verificationCode, $verificationExpires, $userId]);

$pdo->commit();
```

Sikeres response:

```php
json_response([
  "status" => "success",
  "message" => "Registration successful! Please verify your email address with the code you received."
], 201);
```

#### 3.2.3 Email verification: POST action `verify_code` vagy PUT
A registration controller a verifikációt kétféleképp tudja:
- `POST /registration` + `{ action: "verify_code", email, verification_code }`
- `PUT /registration` (ugyanaz a logika)

```php
// app/controllers/api/registrationController.php (részlet)
if (isset($input['action']) && $input['action'] === 'verify_code') {
  verifyRegistrationCode();
} else {
  registerUser();
}
```

A `verifyRegistrationCode()` ellenőrzi:
- user létezik-e
- már verified-e
- code egyezik-e
- expiry nem járt-e le

```php
// app/controllers/api/registrationController.php (részlet)
if ($user['verification_code'] !== $code) {
  json_response(['status' => 'error', 'message' => 'Invalid verification code.'], 401);
}

if (!empty($user['verification_expires']) && strtotime($user['verification_expires']) < time()) {
  json_response(['status' => 'error', 'message' => 'The verification code has expired. Please register again.'], 403);
}

$update = $pdo->prepare('UPDATE `User` SET is_verified = 1, verification_code = NULL, verification_expires = NULL WHERE user_id = ?');
$update->execute([$user['user_id']]);
```

Siker:

```php
json_response(['status' => 'success', 'message' => 'Email verified successfully. You can now log in.'], 200);
```

---

### 3.3 Login flow (`/app/api.php?path=login`)

#### 3.3.1 GET: login view
```php
// app/controllers/api/loginController.php
function getContent() {
  ob_start(); require VIEWS . 'login/login.php'; $buffer = ob_get_clean();
  json_response(["html" => $buffer, "status" => "success"], 200);
}
```

#### 3.3.2 POST: normal login (email + password)
A controller lekéri a usert + role + avatar-t, majd:
- `password_verify()`
- `is_verified` ellenőrzés
- `has_temp_password` ellenőrzés
- session változók beállítása
- `Active_Web_Sessions` token létrehozása + DB upsert
- válaszban user minimal adat (username + avatar)

DB select:

```php
// app/controllers/api/loginController.php (részlet)
$stmt = $pdo->prepare("
  SELECT u.user_id, u.username, u.password, u.is_verified, u.has_temp_password, u.temp_password_expires,
         r.role_name, a.avatar_picture
  FROM `User` u
  LEFT JOIN `Avatars` a ON u.avatar_id = a.id
  LEFT JOIN `Roles` r ON u.role_id = r.id
  WHERE u.email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

Not verified eset:

```php
if (isset($user['is_verified']) && $user['is_verified'] == 0) {
  json_response([
    "status" => "error",
    "code" => "not_verified",
    "message" => "Your account is not verified yet. Please check your email and enter the verification code."
  ], 403);
}
```

Temporary password eset:

```php
if (isset($user['has_temp_password']) && $user['has_temp_password'] == 1) {
  if (!empty($user['temp_password_expires']) && strtotime($user['temp_password_expires']) < time()) {
    json_response(["status" => "error", "code" => "temp_password_expired", "message" => "Your temporary password has expired. Please request a new password reset."], 403);
  }

  json_response([
    "status" => "error",
    "code" => "force_password_change",
    "message" => "You must change your password before accessing your account.",
    "user_id" => $user['user_id']
  ], 403);
}
```

Sikeres login: session + token:

```php
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['role_name'] = $user['role_name'] ?? 'Player';
$_SESSION['logged_in'] = true;

$webSessionToken = bin2hex(random_bytes(32));
$_SESSION['web_session_token'] = $webSessionToken;

$sessionStmt = $pdo->prepare("INSERT INTO `Active_Web_Sessions` (user_id, session_token) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), updated_at = NOW()");
$sessionStmt->execute([$user['user_id'], $webSessionToken]);
```

---

### 3.4 Kliens oldali login UX: `src/login-src/login.js`

#### 3.4.1 Login form submit
A login form elküldi az adatokat JSON-ban:

```js
const response = await fetch(loginUrl, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(data)
});

const result = await response.json();
```

Siker esetén:
- `localStorage.isLoggedIn = true`
- `localStorage.username`
- `localStorage.userAvatar`
- header frissül
- redirect `/profile`

```js
if (response.ok) {
  localStorage.setItem('isLoggedIn', 'true');
  if (result.user && result.user.username) localStorage.setItem('username', result.user.username);
  if (result.user && result.user.avatar) localStorage.setItem('userAvatar', result.user.avatar);

  updateHeader();
  showNotification('Success', 'Welcome to Troxan!', () => {
    window.location.href = '/profile';
  });
}
```

#### 3.4.2 not_verified flow (verification code modal + registration verify_code)
Ha a backend `code: not_verified`-et ad, akkor a kliens megnyit egy kódbeviteli modált, majd `POST /registration action=verify_code`.

```js
} else if (result.code === 'not_verified') {
  const verificationCode = await showCodeInputModal();

  if (verificationCode) {
    const verifyResponse = await fetch('/app/api.php?path=registration', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'verify_code',
        email: data.email,
        verification_code: verificationCode
      })
    });

    const verifyResult = await verifyResponse.json();

    if (verifyResponse.ok) {
      showNotification('Verified', 'Email verified successfully! You can now log in.', () => {
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      });
    } else {
      showNotification('Error', verifyResult.message || 'Invalid verification code.');
    }
  }
}
```

#### 3.4.3 force_password_change flow (modal + login action force_password_change)
Ha a backend `code: force_password_change`, akkor a kliens modált nyit, és `POST /login action=force_password_change`-t hív.

```js
} else if (result.code === 'force_password_change') {
  const tempPassword = data.password;

  const changeResult = await showForcePasswordChangeModal(result.user_id, result.username, tempPassword);

  if (changeResult && changeResult.success) {
    showNotification('Success', 'Password changed successfully! You can now log in.', () => {
      // automatikus új login a friss jelszóval
      form.dispatchEvent(new Event('submit', { cancelable: true }));
    });
  }
}
```

A modal belsejében a request:

```js
const response = await fetch(loginUrl, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'force_password_change',
    user_id: userId,
    old_password: tempPassword,
    new_password: newPassword,
    confirm_password: confirmPassword
  })
});
```

---

### 3.5 Logout flow (`/app/api.php?path=logout`)

#### 3.5.1 Backend: `logoutController.php` csak POST
A backend törli az `Active_Web_Sessions` sort (best effort), majd session-t destroyol.

```php
// app/controllers/api/logoutController.php (részlet)
$userId = $_SESSION['user_id'] ?? null;
$sessionToken = $_SESSION['web_session_token'] ?? null;

if (!empty($userId) && !empty($sessionToken)) {
  $stmt = $pdo->prepare("DELETE FROM `Active_Web_Sessions` WHERE user_id = ? AND session_token = ?");
  $stmt->execute([$userId, $sessionToken]);
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();

json_response(["status" => "success", "message" => "Logged out successfully"], 200);
```

Method guard:

```php
if ($data["method"] === 'POST') {
  logout();
} else {
  json_response(["status" => "error", "message" => "Method not allowed"], 405);
}
```

#### 3.5.2 Frontend: `performLogout()` (src/main.js)
A kliens meghívja a logout API-t, majd törli a localStorage-t és login oldalra navigál.

```js
// src/main.js (részlet)
const logoutUrl = '/app/api.php?path=logout';

async function performLogout() {
  try {
    await fetch(logoutUrl, { method: 'POST', credentials: 'include' });
  } catch (err) {
    console.warn('Logout API call failed, continuing anyway.', err);
  }
  localStorage.clear();
  window.location.href = '/login';
}
```

---

### 3.6 Profil endpoint „auth ellenőrzésre” is használva (`/profile`)
A `profileController.php` GET esetben:
- ha nincs session user: guest view-t ad vissza `message: Redirected to guest`
- ha van: profile view-t renderel

```php
// app/controllers/api/profileController.php (részlet)
if (!isset($_SESSION['user_id'])) {
  ob_start();
  require VIEWS . 'guest/guest.php';
  $buffer = ob_get_clean();
  json_response(["html" => $buffer, "status" => "success", "message" => "Redirected to guest"], 200);
  return;
}
```

Ezért használja a kliens a profile GET-et session checkre is:

```js
// src/main.js (részlet)
const response = await fetch('/app/api.php?path=profile', {
  method: 'GET',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' }
});

const data = await response.json().catch(() => null);

if (!response.ok || !data) {
  clearClientAuthState();
  return;
}

if (data.status === 'success' && data.message === 'Redirected to guest') {
  clearClientAuthState();
}
```

---

### 3.7 Auth „mini-CRUD” összefoglaló (a kódban látható műveletekkel)
- Create (account):
  - `POST /app/api.php?path=registration` → `registerUser()` (User + Settings + Statistics + verification code)
- Update (verify email):
  - `POST /app/api.php?path=registration` + `{ action: "verify_code", email, verification_code }`
  - vagy `PUT /app/api.php?path=registration`
- Read (login view / registration view):
  - `GET /app/api.php?path=login`
  - `GET /app/api.php?path=registration`
- Update (force password change temp password esetén):
  - `POST /app/api.php?path=login` + `{ action: "force_password_change", ... }`
- Delete (session):
  - `POST /app/api.php?path=logout` → session destroy + Active_Web_Sessions cleanup
