
console.log("🟢 My Maps JS Loaded!");
const myMapUrl = `${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=my_maps`;

// ==========================================
// 1. LIVE SEARCH & SORT 
// ==========================================
function filterAndSortMyMaps() {
    const searchInput = document.getElementById('mymaps-search');
    const sortEl = document.getElementById('mymaps-selected-sort');

    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const sortVal = sortEl ? sortEl.textContent.trim() : 'Newest Added';

    const grid = document.querySelector('.mymaps-grid');
    if (!grid) return;

    let cards = Array.from(grid.querySelectorAll('.mymaps-card'));
    let visibleCount = 0;

    // RENDEZÉS
    cards.sort((a, b) => {
        if (sortVal === 'Alphabetical') {
            const nameA = a.getAttribute('data-name') || '';
            const nameB = b.getAttribute('data-name') || '';
            return nameA.localeCompare(nameB);
        } else if (sortVal === 'Oldest Added') {
            const dateA = parseInt(a.getAttribute('data-date')) || 0;
            const dateB = parseInt(b.getAttribute('data-date')) || 0;
            return dateA - dateB;
        } else {
            // Newest Added
            const dateA = parseInt(a.getAttribute('data-date')) || 0;
            const dateB = parseInt(b.getAttribute('data-date')) || 0;
            return dateB - dateA;
        }
    });

    // SZŰRÉS
    cards.forEach(card => {
        const mapName = card.getAttribute('data-name') || '';
        const creator = card.getAttribute('data-creator') || '';

        if (mapName.includes(searchVal) || creator.includes(searchVal)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
        grid.appendChild(card);
    });

    let emptyMsg = document.getElementById('live-mymaps-empty-msg');
    if (visibleCount === 0 && cards.length > 0) {
        if (!emptyMsg) {
            emptyMsg = document.createElement('p');
            emptyMsg.id = 'live-mymaps-empty-msg';
            emptyMsg.className = 'text-orange-900 font-bold text-xl col-span-full mt-10 text-center w-full';
            emptyMsg.innerText = 'Nincs a keresésnek megfelelő pálya! 🏝️';
            grid.appendChild(emptyMsg);
        }
        emptyMsg.style.display = 'block';
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
}

function setMyMapsProfileAvatar() {
    const avatarEl = document.getElementById('mymaps-nav-profile-avatar');
    if (!avatarEl) return;
    const avatarUrl = localStorage.getItem('userAvatar') || 'https://picsum.photos/id/1025/200/200';
    avatarEl.src = avatarUrl;
}

function initMyMapsNavButtons() {
    const mapsBtn = document.getElementById('mymaps-nav-maps');
    const profileBtn = document.getElementById('mymaps-nav-profile');

    if (mapsBtn) {
        mapsBtn.addEventListener('click', () => {
            window.location.href = '/maps';
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', () => {
            window.location.href = '/profile';
        });
    }

    setMyMapsProfileAvatar();
}

// Whenever content updates dynamically (AJAX routing), call initCharacter.
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(initMyMapsNavButtons, 0);
} else {
    document.addEventListener('DOMContentLoaded', initMyMapsNavButtons);
}

document.addEventListener('input', (event) => {
    if (event.target.id === 'mymaps-search') filterAndSortMyMaps();
});

// ==========================================
// 2. GOMBOK ESEMÉNYKEZELŐJE
// ==========================================
document.addEventListener('click', (event) => {

    // --- SORTING GOMBOK ---
    const sortTrigger = event.target.closest('#mymaps-sort-trigger');
    const sortDropdown = document.getElementById('mymaps-sort-dropdown');
    const sortItem = event.target.closest('.mymaps-dropdown-item');

    if (sortTrigger && sortDropdown) {
        sortDropdown.classList.toggle('hidden');
        return;
    }
    if (sortItem) {
        document.getElementById('mymaps-selected-sort').textContent = sortItem.textContent.trim();
        sortDropdown.classList.add('hidden');
        filterAndSortMyMaps();
        return;
    }
    if (sortDropdown && !sortDropdown.classList.contains('hidden') && !event.target.closest('.mymaps-sort-box')) {
        sortDropdown.classList.add('hidden');
    }

    // --- NAV BUTTONS ---
    const mapsNav = event.target.closest('#mymaps-nav-maps');
    if (mapsNav) {
        window.location.href = '/maps';
        return;
    }

    const profileNav = event.target.closest('#mymaps-nav-profile');
    if (profileNav) {
        window.location.href = '/profile';
        return;
    }

    // --- MAP ACTIONS ---
    const editBtn = event.target.closest('.mymaps-edit-btn');
    if (editBtn) {
        const mapId = editBtn.getAttribute('data-mapid');
        showCustomAlert("Editor", `Irány az Editor! Pálya ID: ${mapId}`, "info");
        return;
    }

    const publishBtn = event.target.closest('.mymaps-publish-btn');
    if (publishBtn) {
        const mapId = publishBtn.getAttribute('data-mapid');
        fetch(myMapUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_publish', map_id: mapId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                showCustomAlert("Siker", data.message, "success", () => window.location.reload());
            } else {
                showCustomAlert("Hiba", data.message, "error");
            }
        });
        return;
    }

    const removeBtn = event.target.closest('.mymaps-remove-btn');
    if (removeBtn) {
        const mapId = removeBtn.getAttribute('data-mapid');
        const card = removeBtn.closest('.mymaps-card');
        const mapName = card.querySelector('.map-name').textContent.trim();

        showCustomConfirm("Eltávolítás", `Biztosan eltávolítod a(z) "${mapName}" pályát a könyvtáradból?`, "danger", function () {
            fetch(myMapUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove_map', map_id: mapId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.4s ease'; card.style.opacity = '0'; card.style.transform = 'scale(0.5)';
                    setTimeout(() => card.remove(), 400);
                    showCustomAlert("Siker", data.message, "success");
                } else showCustomAlert("Hiba", data.message, "error");
            });
        });
        return;
    }
});