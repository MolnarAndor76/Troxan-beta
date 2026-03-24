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
    const result = await fetchData(`${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=${path}`);
    
    if (result.status === "success") {
      appDiv.innerHTML = result.html;
      if (result.user && result.user.avatar_picture) {
        localStorage.setItem('userAvatar', result.user.avatar_picture);
        updateHeader();
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

  const route = getRoute();
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

  // 2. Útvonalválasztó védelemmel
switch (route) {
    case "main": 
      // A főoldalt bárki láthatja, nem kell isLoggedIn csekk!
      getMainPageContent(); 
      break;
    
    case "maps": 
      if (isLoggedIn) getMapsContent(); 
      else getGuestContent(); // Ha ide akarnak jönni belépés nélkül, akkor jön a vár!
      break;
        case "my_maps": 
      if (isLoggedIn) getMyMapsContent(); 
      else getGuestContent(); // Ha ide akarnak jönni belépés nélkül, akkor jön a vár!
      break;
    case "editor": 
      if (isLoggedIn) getEditorContent(); 
      else getGuestContent();
      break;

    case "login": 
      getLoginContent(); 
      break;
    
    case "registration": 
      getRegistrationContent(); 
      break;

    case "admin":
      if (isLoggedIn) getAdminContent();
      else getGuestContent();
      break;
    
    case "profile": 
      if (isLoggedIn) getProfileContent(); 
      else getLoginContent();
      break;

    case "leaderboard": 
      getLeaderboardContent(); 
      break;
    
    case "statistics": 
      getStatisticsContent(); 
      break;

    case "guest":
      getGuestContent();
      break;

    default: 
      // Ha eltévedt a júzer, alapból a főoldalt kapja
      getMainPageContent(); 
      break;
  }
});