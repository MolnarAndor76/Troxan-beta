// ========== VITE BUNDLER: IMPORT AZ ÖSSZES JS MODULNAK ==========
// Ezek az importok biztosítják, hogy a Vite egyszerű egy nagy index.js fájlra csomagol össze
import './admin-src/admin.js';
import './basesite-src/basesite.js';
import './leaderboard-src/leaderboard.js';
import './maps-src/maps.js';
import './myMaps-src/myMaps.js';
import './login-src/login.js';
import './register-src/register.js';
import './profile-src/profile.js';
import './isBanned-src/isBanned.js';

// --- SESSION ÉS HEADER FRISSÍTÉS ---
export function updateHeader() {
  const loginLink = document.querySelector('a[href="/login"]');
  const existingProfile = document.getElementById('go-to-profile');

  const username = localStorage.getItem('username');
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const userAvatar = localStorage.getItem('userAvatar') || 'https://picsum.photos/id/1025/200/200';

  // 1. ÁG: BE VAN LÉPVE -> Profilkép megjelenítése
  if (isLoggedIn && username) {
    if (!existingProfile && loginLink) {
      const profileNav = document.createElement('div');
      profileNav.className = 'user-profile-nav troxan-nav-link';
      profileNav.style.cssText = 'cursor:pointer; display:flex; align-items:center;';
      profileNav.id = 'go-to-profile';

      profileNav.innerHTML = `
              <img src="${userAvatar}" class="nav-avatar" style="width:35px; height:35px; border-radius:50%; border:2px solid white; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="${username} profilja">
          `;

      loginLink.replaceWith(profileNav);

      profileNav.addEventListener('click', () => {
        // Mivel SPA, itt is használhatnád a routert, de a reload biztosabb
        window.location.href = '/profile';
      });
    }
  }
  // 2. ÁG: NINCS BELÉPVE -> Visszaállítjuk a Login gombot (ha kint maradt volna a kép)
  else {
    if (existingProfile) {
      const originalLogin = document.createElement('a');
      originalLogin.href = '/login';
      originalLogin.className = 'troxan-nav-link';
      originalLogin.innerText = 'Login';
      existingProfile.replaceWith(originalLogin);
    }
  }
}

// --- KÖZPONTI ADATLEKÉRŐ (Süti-kezeléssel) ---
async function fetchData(url, options = {}) {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

  const fetchOptions = {
    ...options,
    // Ha be van lépve, küldjük a session sütit, ha nincs, nem zavarjuk a szervert vele
    credentials: isLoggedIn ? 'include' : 'omit',
    headers: {
      ...options.headers,
      "Content-Type": "application/json",
    }
  };

  const response = await fetch(url, fetchOptions);

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
  }

  return await response.json();
}

// --- ÚTVONAL-SPECIFIKUS TARTALOM LEKÉRŐK ---
const appDiv = document.querySelector("#main-content");
// 1. Dinamikusan összerakjuk a szerver URL-jét (IP/Domain + mappa)

async function loadContent(path) {
  try {
    // ITT A JAVÍTÁS: Beletettem az api.php?path= részt!
    const result = await fetchData(`/app/api.php?path=${path}`);
    if (result.status === "success") {
      appDiv.innerHTML = result.html;

      // Update avatar in myMaps navbar if loaded
      if (typeof setMyMapsProfileAvatar === 'function') {
        setMyMapsProfileAvatar();
      }

      if (result.user && result.user.avatar_picture) {
        localStorage.setItem('userAvatar', result.user.avatar_picture);
        updateHeader();
        if (typeof setMyMapsProfileAvatar === 'function') {
          setMyMapsProfileAvatar();
        }
      }
    }
  } catch (error) {
    appDiv.innerHTML = `<p style="color:red; font-weight:bold; text-align:center;">Error: ${error.message}</p>`;
  }
}

// Segédfüggvények a régi hívásokhoz (hogy ne kelljen mindent átírni)
async function getMainPageContent() { await loadContent('main'); }
async function getMapsContent() { await loadContent('maps'); }
async function getMyMapsContent() { await loadContent('my_maps'); }
async function getLoginContent() { await loadContent('login'); }
async function getRegistrationContent() { await loadContent('registration'); }
async function getProfileContent() { await loadContent('profile'); }
async function getAdminContent() { await loadContent('admin'); }
async function getLeaderboardContent() { await loadContent('leaderboard'); }
async function getStatisticsContent() { await loadContent('statistics'); }
async function getEditorContent() { await loadContent('editor'); }
async function getGuestContent() { await loadContent('guest'); }

// --- CENTRAL NAVIGÁCIÓ ÉS ACTION EVENT DELEGATION ---
const logoutUrl = '/app/api.php?path=logout';

async function performLogout() {
  try {
    await fetch(logoutUrl, { method: 'POST', credentials: 'include' });
  } catch (err) {
    console.warn('Logout API hívás sikertelen, továbblépünk így is.', err);
  }
  localStorage.clear();
  window.location.href = '/login';
}

function routePathToRouteName(path) {
  const clean = (path || '/').replace(/\/+/g, '/').replace(/^\//, '').replace(/\/$/, '');
  return clean || 'main';
}

function loadRoute(routeName) {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  switch (routeName) {
    case 'main': getMainPageContent(); break;
    case 'maps': isLoggedIn ? getMapsContent() : getGuestContent(); break;
    case 'my_maps': isLoggedIn ? getMyMapsContent() : getGuestContent(); break;
    case 'editor': isLoggedIn ? getEditorContent() : getGuestContent(); break;
    case 'login': getLoginContent(); break;
    case 'registration': getRegistrationContent(); break;
    case 'admin': isLoggedIn ? getAdminContent() : getGuestContent(); break;
    case 'profile': isLoggedIn ? getProfileContent() : getLoginContent(); break;
    case 'leaderboard': getLeaderboardContent(); break;
    case 'statistics': getStatisticsContent(); break;
    case 'guest': getGuestContent(); break;
    default: getMainPageContent(); break;
  }
}

function navigateTo(routeName, pushState = true) {
  if (pushState) {
    const newPath = routeName === 'main' ? '/' : `/${routeName}`;
    history.pushState({ route: routeName }, '', newPath);
  }
  loadRoute(routeName);
}

document.addEventListener('popstate', (event) => {
  const routeName = (event.state && event.state.route) ? event.state.route : routePathToRouteName(window.location.pathname);
  loadRoute(routeName);
});

document.addEventListener('click', function (event) {
  const downloadBtn = event.target.closest('#basesite-download-game-btn');
  const logoutBtn = event.target.closest('#profile-log-out') || event.target.closest('#isBanned-logout-btn') || event.target.closest('[data-action="logout"]');

  if (downloadBtn) {
    const isLoggedIn = downloadBtn.getAttribute('data-loggedin') === 'true';
    event.preventDefault();
    if (isLoggedIn) {
      window.location.href = 'https://github.com/Jogasz/Troxan/releases/download/v0.5.0-alpha/Troxan.rar';
    } else {
      navigateTo('login');
    }
    return;
  }

  if (logoutBtn) {
    event.preventDefault();
    performLogout();
    return;
  }

  const link = event.target.closest('a[href^="/"]');
  if (link && !link.hasAttribute('download') && link.target !== '_blank' && !link.closest('[data-no-spa]')) {
    const href = link.getAttribute('href');
    if (!href.startsWith('/app/') && !href.startsWith('/<?')) {
      event.preventDefault();
      const routeName = routePathToRouteName(href);
      navigateTo(routeName);
      return;
    }
  }
});

// --- ROUTER LOGIKA ---
function getRoute() {
  const { pathname } = window.location;
  const cleanPath = pathname.replace(/\/+$/, "") || "/";
  const segments = cleanPath.split("/").filter(Boolean);
  return segments[0] || "main";
}

// --- INDÍTÁS ---
document.addEventListener('DOMContentLoaded', () => {
  // 1. Frissítjük a fejlécet a mentett adatok alapján
  updateHeader();

  loadRoute(getRoute());
});