
console.log("🟢 My Maps JS Loaded!");
const myMapUrl = `/app/api.php?path=my_maps`;

window.alertCallback = null;
window.confirmCallback = null;
let pendingRename = null;

function openModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('mymaps-hidden');
    modal.classList.add('mymaps-flex');
}

function closeModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('mymaps-hidden');
    modal.classList.remove('mymaps-flex');
}

function showCustomAlert(title, message, type = 'info', callback = null) {
    const modal = document.getElementById('mymaps-alert-modal');
    if (!modal) {
        alert(title + ": " + message);
        if (callback) callback();
        return;
    }

    const titleEl = document.getElementById('mymaps-alert-title');
    const msgEl = document.getElementById('mymaps-alert-message');
    const headerEl = document.getElementById('mymaps-alert-header');
    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerHTML = message;

    if (type === 'error') {
        if (headerEl) headerEl.className = 'mymaps-modal-header mymaps-modal-header-danger';
        if (titleEl) titleEl.className = 'mymaps-modal-title mymaps-modal-title-error';
    } else if (type === 'success') {
        if (headerEl) headerEl.className = 'mymaps-modal-header mymaps-modal-header-success';
        if (titleEl) titleEl.className = 'mymaps-modal-title mymaps-modal-title-success';
    } else {
        if (headerEl) headerEl.className = 'mymaps-modal-header';
        if (titleEl) titleEl.className = 'mymaps-modal-title';
    }

    window.alertCallback = callback;
    openModalById('mymaps-alert-modal');
}

function showCustomConfirm(title, message, type = 'danger', onConfirm = null) {
    const modal = document.getElementById('mymaps-confirm-modal');
    if (!modal) {
        if (confirm(title + ' - ' + message) && onConfirm) onConfirm();
        return;
    }

    const titleEl = document.getElementById('mymaps-confirm-title');
    const msgEl = document.getElementById('mymaps-confirm-message');
    const headerEl = document.getElementById('mymaps-confirm-header');
    const okBtn = document.getElementById('mymaps-confirm-ok-btn');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerHTML = message;

    if (type === 'danger') {
        if (headerEl) headerEl.className = 'mymaps-modal-header mymaps-modal-header-danger';
        if (titleEl) titleEl.className = 'mymaps-modal-title mymaps-modal-title-danger';
        if (okBtn) okBtn.className = 'mymaps-modal-btn-confirm';
    } else {
        if (headerEl) headerEl.className = 'mymaps-modal-header';
        if (titleEl) titleEl.className = 'mymaps-modal-title';
        if (okBtn) okBtn.className = 'mymaps-modal-btn-ok';
    }

    window.confirmCallback = onConfirm;
    openModalById('mymaps-confirm-modal');
}

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
            emptyMsg.className = 'mymaps-empty-msg';
            emptyMsg.innerText = 'No maps found for the current search! 🏝️';
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

    const mobileMenuBtn = event.target.closest('#mymaps-mobile-menu-btn');
    const controlsRow = document.getElementById('mymaps-controls-row');
    if (mobileMenuBtn && controlsRow) {
        controlsRow.classList.toggle('mymaps-controls-row-collapsed');
        controlsRow.classList.toggle('mymaps-mobile-open');
        return;
    }

    if (controlsRow && controlsRow.classList.contains('mymaps-mobile-open')) {
        const clickedInsideMenu = !!event.target.closest('#mymaps-controls-row');
        const clickedMenuBtn = !!event.target.closest('#mymaps-mobile-menu-btn');
        if (!clickedInsideMenu && !clickedMenuBtn) {
            controlsRow.classList.add('mymaps-controls-row-collapsed');
            controlsRow.classList.remove('mymaps-mobile-open');
        }
    }

    if (event.target.closest('#mymaps-alert-close-btn') || event.target.closest('#mymaps-alert-ok-btn')) {
        closeModalById('mymaps-alert-modal');
        if (window.alertCallback) {
            window.alertCallback();
            window.alertCallback = null;
        }
        return;
    }

    if (event.target.closest('#mymaps-confirm-close-btn') || event.target.closest('#mymaps-confirm-cancel-btn')) {
        closeModalById('mymaps-confirm-modal');
        window.confirmCallback = null;
        return;
    }

    if (event.target.closest('#mymaps-confirm-ok-btn')) {
        closeModalById('mymaps-confirm-modal');
        if (window.confirmCallback) {
            window.confirmCallback();
            window.confirmCallback = null;
        }
        return;
    }

    if (event.target.id === 'mymaps-alert-modal') {
        closeModalById('mymaps-alert-modal');
        window.alertCallback = null;
        return;
    }

    if (event.target.id === 'mymaps-confirm-modal') {
        closeModalById('mymaps-confirm-modal');
        window.confirmCallback = null;
        return;
    }

    if (event.target.closest('#mymaps-rename-close-btn') || event.target.closest('#mymaps-rename-cancel-btn') || event.target.id === 'mymaps-rename-modal') {
        closeModalById('mymaps-rename-modal');
        pendingRename = null;
        return;
    }

    if (event.target.closest('#mymaps-rename-save-btn')) {
        if (!pendingRename) {
            closeModalById('mymaps-rename-modal');
            return;
        }

        const inputEl = document.getElementById('mymaps-rename-input');
        const newName = inputEl ? inputEl.value.trim() : '';

        if (!newName) {
            showCustomAlert('Error', 'Map name cannot be empty.', 'error');
            if (inputEl) inputEl.focus();
            return;
        }
        if (newName === pendingRename.oldName) {
            showCustomAlert('Info', 'Map name is unchanged.', 'info');
            return;
        }

        fetch(myMapUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'edit_map_name', map_id: pendingRename.mapId, new_name: newName })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                if (pendingRename.mapNameEl) pendingRename.mapNameEl.textContent = newName;
                if (pendingRename.card) pendingRename.card.setAttribute('data-name', newName.toLowerCase());
                closeModalById('mymaps-rename-modal');
                pendingRename = null;
                showCustomAlert('Success', data.message, 'success');
            } else {
                showCustomAlert('Error', data.message, 'error');
            }
        }).catch(() => {
            showCustomAlert('Error', 'Network error occurred.', 'error');
        });
        return;
    }

    // --- SORTING GOMBOK ---
    const sortTrigger = event.target.closest('#mymaps-sort-trigger');
    const sortDropdown = document.getElementById('mymaps-sort-dropdown');
    const sortItem = event.target.closest('.mymaps-dropdown-item');

    if (sortTrigger && sortDropdown) {
        sortDropdown.classList.toggle('mymaps-hidden');
        return;
    }
    if (sortItem) {
        document.getElementById('mymaps-selected-sort').textContent = sortItem.textContent.trim();
        sortDropdown.classList.add('mymaps-hidden');
        filterAndSortMyMaps();
        return;
    }
    if (sortDropdown && !sortDropdown.classList.contains('mymaps-hidden') && !event.target.closest('.mymaps-sort-box')) {
        sortDropdown.classList.add('mymaps-hidden');
    }

    // --- NAV BUTTONS ---
    const mapsNav = event.target.closest('#mymaps-nav-maps');
    if (mapsNav) {
        if (controlsRow && controlsRow.classList.contains('mymaps-mobile-open')) {
            controlsRow.classList.add('mymaps-controls-row-collapsed');
            controlsRow.classList.remove('mymaps-mobile-open');
        }
        window.location.href = '/maps';
        return;
    }

    const profileNav = event.target.closest('#mymaps-nav-profile');
    if (profileNav) {
        if (controlsRow && controlsRow.classList.contains('mymaps-mobile-open')) {
            controlsRow.classList.add('mymaps-controls-row-collapsed');
            controlsRow.classList.remove('mymaps-mobile-open');
        }
        window.location.href = '/profile';
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
                showCustomAlert("Success", data.message, "success", () => window.location.reload());
            } else {
                showCustomAlert("Error", data.message, "error");
            }
        });
        return;
    }

    const editBtn = event.target.closest('.mymaps-edit-btn');
    if (editBtn) {
        const mapId = editBtn.getAttribute('data-mapid');
        const card = editBtn.closest('.mymaps-card');
        const mapNameEl = card ? card.querySelector('.map-name') : null;
        const oldName = mapNameEl ? mapNameEl.textContent.trim() : '';

        if (!mapId || !oldName) {
            showCustomAlert('Error', 'Invalid map data.', 'error');
            return;
        }

        const oldNameEl = document.getElementById('mymaps-rename-old-name');
        const inputEl = document.getElementById('mymaps-rename-input');
        if (oldNameEl) oldNameEl.textContent = oldName;
        if (inputEl) {
            inputEl.value = oldName;
            inputEl.focus();
            inputEl.select();
        }

        pendingRename = { mapId, oldName, mapNameEl, card };
        openModalById('mymaps-rename-modal');
        return;
    }

    const lockedBtn = event.target.closest('.mymaps-edit-locked-btn');
    if (lockedBtn) {
        showCustomAlert('Locked', 'Only the original creator or staff (Admin / Moderator / Engineer) can rename this map.', 'info');
        return;
    }

    const removeBtn = event.target.closest('.mymaps-remove-btn');
    if (removeBtn) {
        const mapId = removeBtn.getAttribute('data-mapid');
        const card = removeBtn.closest('.mymaps-card');
        const mapName = card.querySelector('.map-name').textContent.trim();

        showCustomConfirm("Remove", `Are you sure you want to remove the "${mapName}" map from your library?`, "danger", function () {
            fetch(myMapUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove_map', map_id: mapId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.4s ease'; card.style.opacity = '0'; card.style.transform = 'scale(0.5)';
                    setTimeout(() => card.remove(), 400);
                    showCustomAlert("Success", data.message, "success");
                } else showCustomAlert("Error", data.message, "error");
            });
        });
        return;
    }
});