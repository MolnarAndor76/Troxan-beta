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
  const mobileLoginLink = document.querySelector('#mobile-menu a[href="/login"]');
  const existingProfile = document.getElementById('go-to-profile');
  const existingMobileProfile = document.getElementById('go-to-profile-mobile');

  const username = localStorage.getItem('username');
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  const userAvatar = localStorage.getItem('userAvatar') || 'https://picsum.photos/id/1025/200/200';

  // 1. ÁG: BE VAN LÉPVE -> Desktop: Profilkép, Mobile: Profile gomb
  if (isLoggedIn && username) {
    // DESKTOP MENÜ
    if (!existingProfile && loginLink) {
      const profileNav = document.createElement('div');
      profileNav.className = 'user-profile-nav troxan-nav-link';
      profileNav.id = 'go-to-profile';

      const avatarImg = document.createElement('img');
      avatarImg.src = userAvatar;
      avatarImg.className = 'nav-avatar';
      avatarImg.title = `${username} profilja`;
      profileNav.appendChild(avatarImg);

      loginLink.replaceWith(profileNav);

      profileNav.addEventListener('click', () => {
        window.location.href = '/profile';
      });
    }

    // MOBILE MENÜ
    if (!existingMobileProfile && mobileLoginLink) {
      const mobileProfileBtn = document.createElement('a');
      mobileProfileBtn.href = '/profile';
      mobileProfileBtn.className = 'troxan-nav-link';
      mobileProfileBtn.id = 'go-to-profile-mobile';
      mobileProfileBtn.innerText = 'Profile';

      mobileLoginLink.replaceWith(mobileProfileBtn);
    }
  }
  // 2. ÁG: NINCS BELÉPVE -> Visszaállítjuk a Login gombot
  else {
    if (existingProfile) {
      const originalLogin = document.createElement('a');
      originalLogin.href = '/login';
      originalLogin.className = 'troxan-nav-link';
      originalLogin.innerText = 'Login';
      existingProfile.replaceWith(originalLogin);
    }

    if (existingMobileProfile) {
      const originalMobileLogin = document.createElement('a');
      originalMobileLogin.href = '/login';
      originalMobileLogin.className = 'troxan-nav-link';
      originalMobileLogin.innerText = 'Login';
      existingMobileProfile.replaceWith(originalMobileLogin);
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

// --- ÚTVONAL-SPECIFIKUS TARTALOM LEKÉRŐK ---
const appDiv = document.querySelector("#main-content");
// 1. Dinamikusan összerakjuk a szerver URL-jét (IP/Domain + mappa)

function clearClientAuthState() {
  localStorage.removeItem('isLoggedIn');
  localStorage.removeItem('username');
  localStorage.removeItem('userAvatar');
}

async function syncAuthStateWithServer() {
  try {
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
  } catch (error) {
    clearClientAuthState();
  }
}

function fillClientLastUpdatedFields() {
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  const h = String(now.getHours()).padStart(2, '0');
  const min = String(now.getMinutes()).padStart(2, '0');
  const formatted = `${y}.${m}.${d} ${h}:${min}`;

  const leaderboardEl = document.getElementById('leaderboard-last-updated-time');
  if (leaderboardEl) leaderboardEl.textContent = formatted;

  const profileEl = document.getElementById('profile-last-updated-time');
  if (profileEl) profileEl.textContent = formatted;
}

async function loadContent(path) {
  try {
    // ITT A JAVÍTÁS: Beletettem az api.php?path= részt!
    const result = await fetchData(`/app/api.php?path=${path}`);
    if (result.status === "success") {
      appDiv.innerHTML = result.html;
      fillClientLastUpdatedFields();

      if (path === 'main' && typeof window.initBasesiteView === 'function') {
        window.initBasesiteView();
      }

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
    appDiv.innerHTML = `<p class="troxan-error-message">Error: ${error.message}</p>`;
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
async function getGuestContent() { await loadContent('guest'); }

// --- CENTRAL NAVIGÁCIÓ ÉS ACTION EVENT DELEGATION ---
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
  const logoutBtn = event.target.closest('[data-action="logout"]');

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
      // Close mobile menu if open
      const menuBtn = document.getElementById('menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      if (mobileMenu && !mobileMenu.classList.contains('troxan-hidden')) {
        mobileMenu.classList.add('troxan-hidden');
        if (menuBtn) menuBtn.classList.remove('is-open');
      }
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

// --- PERIODIKUS SESSION ELLENŐRZÉS (5 percenként) ---
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
    // Hálózati hiba – nem biztonságos kijelentkeztetni, várunk
  }
}

// --- INDÍTÁS ---
document.addEventListener('DOMContentLoaded', async () => {
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');

  const setMobileMenuOpen = (open) => {
    if (!menuBtn || !mobileMenu) return;
    mobileMenu.classList.toggle('troxan-hidden', !open);
    menuBtn.classList.toggle('is-open', open);
  };

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      const isOpen = !mobileMenu.classList.contains('troxan-hidden');
      setMobileMenuOpen(!isOpen);
    });

    // Close the mobile menu like a popup when user clicks outside of it.
    document.addEventListener('click', (event) => {
      const isOpen = !mobileMenu.classList.contains('troxan-hidden');
      if (!isOpen) return;

      const clickedMenuBtn = !!event.target.closest('#menu-btn');
      const clickedInsideMenu = !!event.target.closest('#mobile-menu');
      if (!clickedMenuBtn && !clickedInsideMenu) {
        setMobileMenuOpen(false);
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 768) {
        setMobileMenuOpen(false);
      }
    });
  }

  await syncAuthStateWithServer();
  updateHeader();
  loadRoute(getRoute());

  // Indítjuk a periodikus session ellenőrzést (5 percenként)
  setInterval(periodicSessionCheck, 5 * 60 * 1000);
});