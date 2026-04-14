## 1. Alap architektúra és request flow (rewrite → api.php → router → controller → JSON → SPA render)

### 1.1 Projekt „mentális modell” (mit kell fejlesztőként fejben tartani)

#### 1.1.1 Egyetlen backend belépési pont
A Troxan-beta webes alkalmazásban a kérések nagy része az `app/api.php` fájlon fut át. A szerveroldal a kéréstől függően:
- vagy HTML-t generál (PHP view render), majd JSON-ban visszaküldi (`{ status, html }`),
- vagy egy API műveletet futtat (jellemzően `POST` + `action`), és JSON választ ad.

Ez egy „front controller” jellegű minta: egy belépési pont + router + controllerek.

---

### 1.2 URL átírás (Apache rewrite) → `api.php?path=...`

#### 1.2.1 `.htaccess` szabály
Az `app/.htaccess` gondoskodik arról, hogy a nem létező fájlokra/könyvtárakra érkező kérések átíródjanak az `api.php`-ra.

Példa:
- Eredeti URL: `/maps`
- Rewrite után: `/app/api.php?path=maps`

```apacheconf
<IfModule mod_rewrite.c>
    # URL átírás engedélyezése
    RewriteEngine On

    # Feltétel: Ha a fájl nem létezik
    RewriteCond %{REQUEST_FILENAME} !-f

    # Feltétel: Ha a könyvtár nem létezik
    RewriteCond %{REQUEST_FILENAME} !-d

    # Átírási szabály: a (.*) MINDENT elfogad, perjeleket is!
    RewriteRule ^(.*)$ api.php?path=$1 [QSA,L]
</IfModule>
```

Miért fontos?
- A frontend használhat „szép URL-eket” (`/profile`, `/maps`), miközben a backend egyetlen routeren keresztül szolgál ki mindent.

---

### 1.3 `app/api.php` — front controller: CORS + session + DB + router

#### 1.3.1 Dinamikus CORS kezelés
A `api.php` elején a kód dinamikusan beállítja az `Access-Control-Allow-Origin` headert az `HTTP_ORIGIN` alapján, majd engedélyezi a credential-öket és a HTTP methodokat.

```php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (!empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

Megjegyzés:
- Ez fejlesztési környezetben kényelmes, mert „bárhonnan” enged requestet, ahol van Origin fejléc.
- Éles környezetben általában szűkíteni érdemes (whitelist).

---

#### 1.3.2 Session cookie paraméterezés
A session és cookie paraméterek 2 órás életciklusra vannak hangolva:

```php
ini_set('session.gc_maxlifetime', 7200); // 2 óra

session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => '',
    'secure' => false,  // fejlesztés alatt false, élesben HTTPS esetén true
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

---

#### 1.3.3 DB config + connection boot
A config és a DB kapcsolat betöltése:

```php
require 'core/config.php';
require 'core/connect.php';
```

`app/core/config.php` DB konstansok:

```php
const DB_HOST = "localhost";
const DB_USER = "troxan_user";
const DB_PASS = "TroxanServer123";
const DB_NAME = "troxan_db";
const DB_CHARSET = "utf8mb4";
```

`app/core/connect.php` PDO connection:

```php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
```

---

#### 1.3.4 `Active_Web_Sessions` token logika (multi-browser védelem)
Ha a felhasználó be van lépve (`$_SESSION['user_id']` és `$_SESSION['logged_in']`), a `api.php` karbantartja a `Active_Web_Sessions` táblát és ellenőrzi a session tokent.

A cél: ha a user másik böngészőben belép, a token eltér, és az API 401-et ad.

Tipikus ág:

```php
if (!hash_equals((string)$dbToken, (string)$currentToken)) {
    $_SESSION = [];
    session_destroy();

    json_response([
        "status" => "error",
        "message" => "Your account was used on another browser. Please log in again."
    ], 401);
    exit();
}
```

---

#### 1.3.5 Router indítása
A legvégén a vezérlés átmegy a routerre:

```php
require CORE . 'router.php';
```

---

### 1.4 `app/core/router.php` — path parsing + helper függvények

#### 1.4.1 `path` feldarabolása
A router a `path` paramétert feldarabolja, és a `segment1/2/3` mezőkbe teszi.

```php
$path = $_GET['path'] ?? '';
$path = trim($path, '/');
$segments = ($path === '') ? [] : explode('/', $path);

$method = $_SERVER['REQUEST_METHOD'];

$route = [
    'segment1' => $segments[0] ?? null,
    'segment2' => $segments[1] ?? null,
    'segment3' => $segments[2] ?? null,
];
```

---

#### 1.4.2 `json_response()` helper
Ez a projekt standard JSON válasz-küldője. Minden controller ezt használja.

```php
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_QUOT);
    exit;
}
```

Miért fontos, hogy `exit` van a végén?
- Megakadályozza, hogy a script tovább fusson és „ráírjon” a válaszra.

---

#### 1.4.3 `load_controller()` helper
A router ezzel tölti be a controller fájlokat.

```php
function load_controller(array $data, string $file, int $statusCode = 200): void
{
    http_response_code($statusCode);

    if (file_exists($file)) {
        require $file;
    } else {
        http_response_code(500);
        echo "Controller not found.";
    }
}
```

---

### 1.5 `app/core/router/api.php` — endpoint dispatch + global ban check

#### 1.5.1 Globális ban check
Bejelentkezett usernél a router ellenőrzi a `User.is_banned` mezőt. Ha banned és nem `logout`, akkor a route átíródik `isBanned`-re.

```php
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $banStmt = $pdo->prepare("SELECT is_banned FROM `User` WHERE user_id = ?");
    $banStmt->execute([$_SESSION['user_id']]);
    $isBanned = $banStmt->fetchColumn();

    if ($isBanned == 1 && $route['segment1'] !== 'logout') {
        $route['segment1'] = 'isBanned';
    }
}
```

Miért jó ez?
- Nem kell minden controller elejére külön ban-checket rakni.
- A tiltott user automatikusan a kitiltás nézetet kapja.

---

#### 1.5.2 Controller dispatch (`segment1` alapján)
A router a `segment1` alapján dönt:

```php
switch ($route['segment1']) {
    case "maps":
        load_controller($data, API_CONTROLLERS . 'mapsController.php');
        break;
    case "my_maps":
        load_controller($data, API_CONTROLLERS . 'myMapsController.php');
        break;
    case "login":
        load_controller($data, API_CONTROLLERS . 'loginController.php');
        break;
    case "registration":
        load_controller($data, API_CONTROLLERS . 'registrationController.php');
        break;
    case "profile":
        load_controller($data, API_CONTROLLERS . 'profileController.php');
        break;
    case "admin":
        load_controller($data, API_CONTROLLERS . 'adminController.php');
        break;
    // ...
    default:
        json_response(['error' => 'API endpoint not found'], 404);
}
```

---

### 1.6 Controller output minta: „GET = HTML view render JSON-ban”
A webes nézetek általában GET-re HTML-t renderelnek, majd JSON-ban visszaadják.

Példa: login GET:

```php
function getContent() {
    ob_start();
    require VIEWS . 'login/login.php';
    $buffer = ob_get_clean();
    json_response(["html" => $buffer, "status" => "success"], 200);
}
```

A frontend ezt `result.html` mezőként kapja vissza, és beszúrja a DOM-ba.

---

### 1.7 SPA jellegű működés: `src/main.js`

#### 1.7.1 Vite bundler importok
A `src/main.js` importálja az összes modul JS-t, hogy a bundler egy csomagba tegye.

```js
import './admin-src/admin.js';
import './basesite-src/basesite.js';
import './leaderboard-src/leaderboard.js';
import './maps-src/maps.js';
import './myMaps-src/myMaps.js';
import './login-src/login.js';
import './register-src/register.js';
import './profile-src/profile.js';
import './isBanned-src/isBanned.js';
```

---

#### 1.7.2 Központi fetch wrapper: `fetchData()`
A `fetchData()` kezeli:
- `credentials` beállítást (include/omit)
- 401 esetén session state törlés + loginra navigálás
- response JSON parse + hibakezelés

```js
async function fetchData(url, options = {}) {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

  const fetchOptions = {
    ...options,
    credentials: isLoggedIn ? 'include' : 'omit',
    headers: {
      ...options.headers,
      "Content-Type": "application/json",
    }
  };

  const response = await fetch(url, fetchOptions);

  if (response.status === 401) {
    clearClientAuthState();
    navigateTo('login');
    throw new Error('Session expired. Please log in again.');
  }

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
  }

  return await response.json();
}
```

---

#### 1.7.3 View betöltés: `loadContent(path)`
A route betöltés lényege: `GET /app/api.php?path=<path>` és `result.html` DOM-ba.

```js
async function loadContent(path) {
  try {
    const result = await fetchData(`/app/api.php?path=${path}`);
    if (result.status === "success") {
      appDiv.innerHTML = result.html;
      fillClientLastUpdatedFields();
    }
  } catch (error) {
    appDiv.innerHTML = `<p class="troxan-error-message">Error: ${error.message}</p>`;
  }
}
```

---

#### 1.7.4 Route guard: bizonyos oldalak csak belépve
A kliens oldalon a route-ok egy része csak belépett állapotban töltődik (maps/my_maps/admin), különben a `guest` nézet jön.

```js
function loadRoute(routeName) {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  switch (routeName) {
    case 'maps': isLoggedIn ? getMapsContent() : getGuestContent(); break;
    case 'my_maps': isLoggedIn ? getMyMapsContent() : getGuestContent(); break;
    case 'admin': isLoggedIn ? getAdminContent() : getGuestContent(); break;
    case 'profile': isLoggedIn ? getProfileContent() : getLoginContent(); break;
    default: getMainPageContent(); break;
  }
}
```

---

#### 1.7.5 Periodikus session check (5 percenként)
Ha a localStorage szerint belépett, a kliens időnként `GET /profile`-lal validálja a sessiont.

```js
async function periodicSessionCheck() {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  if (!isLoggedIn) return;

  try {
    const response = await fetch('/app/api.php?path=profile', {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json().catch(() => null);

    if (response.status === 401 || !response.ok || !data || (data.status === 'success' && data.message === 'Redirected to guest')) {
      clearClientAuthState();
      navigateTo('login');
    }
  } catch (error) {
    // hálózati hiba esetén nem léptetünk ki automatikusan
  }
}
```

---

### 1.8 Rövid összefoglaló
- `.htaccess` átírja az útvonalakat `api.php?path=...` formára.
- `app/api.php` inicializál (CORS, session, DB), majd meghívja a routert.
- A router `segment1` alapján controller fájlt tölt be.
- A controller GET-re view-t renderel JSON-ban, POST-ra action alapú műveleteket futtat.
- A frontend SPA-szerűen a szervertől kapott HTML-t injektálja a `#main-content` konténerbe.
