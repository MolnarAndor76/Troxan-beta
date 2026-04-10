
console.log("🟢 Maps JS Loaded!");

window.alertCallback = null;
window.confirmCallback = null;

const mapUrl = `/app/api.php?path=maps`;

function showCustomAlert(title, message, type = 'info', callback = null) {
    const modal = document.getElementById('basesite-alert-modal');
    if (!modal) {
        alert(title + ": " + message);
        if (callback) callback();
        return;
    }
    document.getElementById('basesite-alert-title').innerText = title;
    document.getElementById('basesite-alert-message').innerHTML = message;

    const titleEl = document.getElementById('basesite-alert-title');
    titleEl.className = 'maps-modal-alert-title';
    if (type === 'error') titleEl.classList.add('maps-modal-alert-title-danger');
    else if (type === 'success') titleEl.classList.add('maps-modal-alert-title-success');

    window.alertCallback = callback;
    modal.classList.remove('maps-hidden');
    modal.classList.add('maps-modal-visible');
}

function showCustomConfirm(title, message, type = 'danger', onConfirm = null) {
    const modal = document.getElementById('basesite-confirm-modal');
    if (!modal) {
        if (confirm(title + " - " + message)) onConfirm();
        return;
    }
    document.getElementById('basesite-confirm-title').innerText = title;
    document.getElementById('basesite-confirm-message').innerHTML = message;

    const headerEl = document.getElementById('basesite-confirm-header');
    const titleEl = document.getElementById('basesite-confirm-title');
    const okBtn = document.getElementById('basesite-confirm-ok-btn');

    headerEl.className = 'maps-modal-alert-head';
    titleEl.className = 'maps-modal-alert-title';
    okBtn.className = 'maps-confirm-btn-ok';

    if (type === 'danger') {
        headerEl.classList.add('maps-modal-alert-head-danger');
        titleEl.classList.add('maps-modal-alert-title-danger');
    } else {
        okBtn.className = 'maps-modal-alert-btn';
    }

    window.confirmCallback = onConfirm;
    modal.classList.remove('maps-hidden');
    modal.classList.add('maps-modal-visible');
}

function filterAndSortMaps() {
    const searchInput = document.getElementById('maps-search');
    const sortEl = document.getElementById('maps-selected-sort');

    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const sortVal = sortEl ? sortEl.textContent.trim() : 'Downloads';

    const grid = document.querySelector('.maps-grid');
    if (!grid) return;

    let cards = Array.from(grid.querySelectorAll('.maps-card'));
    let visibleCount = 0;

    cards.sort((a, b) => {
        if (sortVal === 'Alphabetical') {
            const nameA = a.getAttribute('data-name') || '';
            const nameB = b.getAttribute('data-name') || '';
            return nameA.localeCompare(nameB);
        } else if (sortVal === 'Most recent') {
            const dateA = parseInt(a.getAttribute('data-date')) || 0;
            const dateB = parseInt(b.getAttribute('data-date')) || 0;
            if (dateA === dateB) {
                const idA = parseInt(a.getAttribute('data-id')) || 0;
                const idB = parseInt(b.getAttribute('data-id')) || 0;
                return idB - idA;
            }
            return dateB - dateA;
        } else {
            const dlA = parseInt(a.getAttribute('data-downloads')) || 0;
            const dlB = parseInt(b.getAttribute('data-downloads')) || 0;
            return dlB - dlA;
        }
    });

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

    let emptyMsg = document.getElementById('live-maps-empty-msg');
    if (visibleCount === 0 && cards.length > 0) {
        if (!emptyMsg) {
            emptyMsg = document.createElement('p');
            emptyMsg.id = 'live-maps-empty-msg';
            emptyMsg.className = 'maps-empty-msg';
            emptyMsg.innerText = 'No maps found matching your search. 🏝️';
            grid.appendChild(emptyMsg);
        }
        emptyMsg.style.display = 'block';
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
}

document.addEventListener('input', (event) => {
    if (event.target.id === 'maps-search') filterAndSortMaps();
});
document.addEventListener('keypress', (e) => {
    if (e.target.id === 'maps-search' && e.key === 'Enter') { e.preventDefault(); }
});

document.addEventListener('click', (event) => {

    const mobileMenuBtn = event.target.closest('#maps-mobile-menu-btn');
    const controlsRow = document.getElementById('maps-controls-row');
    if (mobileMenuBtn && controlsRow) {
        controlsRow.classList.toggle('maps-controls-row-collapsed');
        controlsRow.classList.toggle('maps-mobile-open');
        return;
    }

    if (controlsRow && controlsRow.classList.contains('maps-mobile-open')) {
        const clickedInsideMenu = !!event.target.closest('#maps-controls-row');
        const clickedMenuBtn = !!event.target.closest('#maps-mobile-menu-btn');
        if (!clickedInsideMenu && !clickedMenuBtn) {
            controlsRow.classList.add('maps-controls-row-collapsed');
            controlsRow.classList.remove('maps-mobile-open');
        }
    }

    // --- ÚJ GOMB: Irány a My Maps! ---
    const goMyMapsBtn = event.target.closest('#maps-go-mymaps-btn');
    if (goMyMapsBtn) {
        if (controlsRow && controlsRow.classList.contains('maps-mobile-open')) {
            controlsRow.classList.add('maps-controls-row-collapsed');
            controlsRow.classList.remove('maps-mobile-open');
        }
        window.location.href = '/my_maps';
        return;
    }

    if (event.target.closest('#basesite-alert-ok-btn') || (event.target.closest('.maps-close-modal') && event.target.closest('#basesite-alert-modal'))) {
        const modal = document.getElementById('basesite-alert-modal');
        if (modal) { modal.classList.add('maps-hidden'); modal.classList.remove('maps-modal-visible'); if (window.alertCallback) { window.alertCallback(); window.alertCallback = null; } }
        return;
    }
    if (event.target.closest('#basesite-confirm-cancel-btn') || (event.target.closest('.maps-close-modal') && event.target.closest('#basesite-confirm-modal'))) {
        const modal = document.getElementById('basesite-confirm-modal');
        if (modal) { modal.classList.add('maps-hidden'); modal.classList.remove('maps-modal-visible'); window.confirmCallback = null; }
        return;
    }
    if (event.target.closest('#basesite-confirm-ok-btn')) {
        const modal = document.getElementById('basesite-confirm-modal');
        if (modal) { modal.classList.add('maps-hidden'); modal.classList.remove('maps-modal-visible'); if (window.confirmCallback) { window.confirmCallback(); window.confirmCallback = null; } }
        return;
    }

    const sortTrigger = event.target.closest('#maps-sort-trigger');
    const sortDropdown = document.getElementById('maps-sort-dropdown');
    const sortItem = event.target.closest('.maps-dropdown-item');

    if (sortTrigger && sortDropdown) {
        sortDropdown.classList.toggle('maps-hidden');
        return;
    }

    if (sortItem) {
        const newSort = sortItem.textContent.trim();
        document.getElementById('maps-selected-sort').textContent = newSort;
        sortDropdown.classList.add('maps-hidden');
        filterAndSortMaps();
        return;
    }
    if (sortDropdown && !sortDropdown.classList.contains('maps-hidden') && !event.target.closest('.maps-sort-box')) {
        sortDropdown.classList.add('maps-hidden');
    }

    const helpBtn = event.target.closest('#maps-help-btn');
    const trashOpenBtn = event.target.closest('#maps-trash-open-btn');
    const closeModalBtn = event.target.closest('.maps-close-modal');
    const backdrop = event.target.closest('.maps-modal-backdrop');

    if (helpBtn) { const modal = document.getElementById('maps-help-modal'); modal.classList.remove('maps-hidden'); modal.classList.add('maps-modal-visible'); return; }
    if (trashOpenBtn) { const modal = document.getElementById('maps-trash-modal'); modal.classList.remove('maps-hidden'); modal.classList.add('maps-modal-visible'); return; }

    if (closeModalBtn || backdrop) {
        const activeModal = event.target.closest('.maps-modal-overlay:not(.maps-hidden)');
        if (activeModal && (activeModal.id === 'maps-help-modal' || activeModal.id === 'maps-trash-modal')) {
            activeModal.classList.add('maps-hidden');
            activeModal.classList.remove('maps-modal-visible');
            return;
        }
    }

    const addBtn = event.target.closest('.maps-add-btn');
    if (addBtn) {
        event.preventDefault(); // Biztos ami tuti, ne ugorjon el az oldal
        const card = addBtn.closest('.maps-card');
        const mapId = addBtn.getAttribute('data-mapid');
        
        console.log("🔥 ADD gomb megnyomva! Map ID:", mapId); // DEBUG: Ezt látnod kell F12-ben!

        if (mapId) {
            fetch(mapUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add_to_library', map_id: mapId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    // Növeljük a számlálót vizuálisan a kártyán
                    const countSpan = card.querySelector('.dl-number');
                    if (countSpan) {
                        let current = parseInt(countSpan.textContent.replace(/[^\d]/g, '')) || 0;
                        countSpan.textContent = (current + 1).toLocaleString();
                        card.setAttribute('data-downloads', current + 1);
                    }
                    
                    // Set button to "Added" state
                    addBtn.classList.remove('maps-add-btn-available');
                    addBtn.classList.add('maps-add-btn-added');
                    addBtn.dataset.added = 'true';
                    addBtn.textContent = 'Added ✔️';

                    showCustomAlert("Success", data.message, "success");
                } else if (data.status === 'info') {
                    showCustomAlert("Info", data.message, "info");
                    addBtn.textContent = 'Added ✔️';
                } else {
                    showCustomAlert("Error", data.message, "error");
                }
            }).catch(err => {
                console.error("Fetch error occurred:", err);
            });
        }
        return;
    }

    const deleteBtn = event.target.closest('.maps-delete');
    if (deleteBtn) {
        const mapId = deleteBtn.getAttribute('data-mapid');
        const card = deleteBtn.closest('.maps-card');
        const mapName = card.querySelector('.map-name').textContent.trim();

        showCustomConfirm("Confirm deletion", `Are you sure you want to delete the map "${mapName}"?`, "danger", function () {
            fetch(mapUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_map', map_id: mapId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.4s ease'; card.style.opacity = '0'; card.style.transform = 'scale(0.5)';
                    setTimeout(() => card.remove(), 400);
                    showCustomAlert("Success", data.message || "Map deleted!", "success");
                } else showCustomAlert("Error", data.message, "error");
            });
        });
        return;
    }

    const restoreBtn = event.target.closest('.maps-restore-btn');
    if (restoreBtn) {
        const mapId = restoreBtn.getAttribute('data-mapid');
        fetch(mapUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'restore_map', map_id: mapId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') showCustomAlert("Success", data.message || "Map restored!", "success", () => window.location.reload());
            else showCustomAlert("Error", data.message, "error");
        });
        return;
    }
});