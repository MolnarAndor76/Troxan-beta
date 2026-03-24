
console.log("🟢 Maps JS Loaded!");

window.alertCallback = null;
window.confirmCallback = null;

const mapUrl = `${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=maps`;

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
    if (type === 'error') titleEl.className = 'text-xl font-bold text-red-600';
    else if (type === 'success') titleEl.className = 'text-xl font-bold text-green-600';
    else titleEl.className = 'text-xl font-bold text-orange-950';

    window.alertCallback = callback;
    modal.classList.remove('hidden');
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
    const okBtn = document.getElementById('basesite-confirm-ok-btn');

    if (type === 'danger') {
        headerEl.className = 'border-b-4 border-red-950 w-full pb-2 mb-4';
        document.getElementById('basesite-confirm-title').className = 'text-xl font-bold text-red-600';
        okBtn.className = 'bg-red-600 px-6 py-2 font-bold text-white border-2 border-red-900 rounded shadow-[2px_2px_0px_#000]';
    } else {
        headerEl.className = 'border-b-4 border-orange-950 w-full pb-2 mb-4';
        document.getElementById('basesite-confirm-title').className = 'text-xl font-bold text-orange-950';
        okBtn.className = 'bg-yellow-500 px-6 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000]';
    }

    window.confirmCallback = onConfirm;
    modal.classList.remove('hidden');
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
            emptyMsg.className = 'text-orange-900 font-bold text-xl col-span-full mt-10 text-center w-full';
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

    // --- ÚJ GOMB: Irány a My Maps! ---
    const goMyMapsBtn = event.target.closest('#maps-go-mymaps-btn');
    if (goMyMapsBtn) {
        window.location.href = '/my_maps';
        return;
    }

    if (event.target.closest('#basesite-alert-ok-btn') || (event.target.closest('.maps-close-modal') && event.target.closest('#basesite-alert-modal'))) {
        const modal = document.getElementById('basesite-alert-modal');
        if (modal) { modal.classList.add('hidden'); if (window.alertCallback) { window.alertCallback(); window.alertCallback = null; } }
        return;
    }
    if (event.target.closest('#basesite-confirm-cancel-btn') || (event.target.closest('.maps-close-modal') && event.target.closest('#basesite-confirm-modal'))) {
        const modal = document.getElementById('basesite-confirm-modal');
        if (modal) { modal.classList.add('hidden'); window.confirmCallback = null; }
        return;
    }
    if (event.target.closest('#basesite-confirm-ok-btn')) {
        const modal = document.getElementById('basesite-confirm-modal');
        if (modal) { modal.classList.add('hidden'); if (window.confirmCallback) { window.confirmCallback(); window.confirmCallback = null; } }
        return;
    }

    const sortTrigger = event.target.closest('#maps-sort-trigger');
    const sortDropdown = document.getElementById('maps-sort-dropdown');
    const sortItem = event.target.closest('.maps-dropdown-item');

    if (sortTrigger && sortDropdown) {
        sortDropdown.classList.toggle('hidden');
        return;
    }

    if (sortItem) {
        const newSort = sortItem.textContent.trim();
        document.getElementById('maps-selected-sort').textContent = newSort;
        sortDropdown.classList.add('hidden');
        filterAndSortMaps();
        return;
    }
    if (sortDropdown && !sortDropdown.classList.contains('hidden') && !event.target.closest('.maps-sort-box')) {
        sortDropdown.classList.add('hidden');
    }

    const helpBtn = event.target.closest('#maps-help-btn');
    const trashOpenBtn = event.target.closest('#maps-trash-open-btn');
    const closeModalBtn = event.target.closest('.maps-close-modal');
    const backdrop = event.target.closest('.maps-modal-backdrop');

    if (helpBtn) { document.getElementById('maps-help-modal').classList.remove('hidden'); return; }
    if (trashOpenBtn) { document.getElementById('maps-trash-modal').classList.remove('hidden'); return; }

    if (closeModalBtn || backdrop) {
        const activeModal = event.target.closest('.fixed.inset-0:not(.hidden)');
        if (activeModal && (activeModal.id === 'maps-help-modal' || activeModal.id === 'maps-trash-modal')) {
            activeModal.classList.add('hidden');
            return;
        }
    }

    const editBtn = event.target.closest('.maps-edit-btn');
    if (editBtn) {
        const mapId = editBtn.getAttribute('data-mapid');
        showCustomAlert("Editor", `Irány az Editor! Pálya ID: ${mapId}`, "info");
        return;
    }

    // --- AZ ADD TO LIBRARY GOMB LOGIKA ---
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
                    
                    // Gomb "Added" állapotba rakása
                    addBtn.classList.replace('bg-green-600', 'bg-gray-500');
                    addBtn.classList.replace('hover:bg-green-500', 'hover:bg-gray-400');
                    addBtn.classList.replace('border-green-950', 'border-gray-950');
                    addBtn.textContent = 'Added ✔️';
                    addBtn.style.pointerEvents = 'none';

                    showCustomAlert("Siker", data.message, "success");
                } else if (data.status === 'info') {
                    showCustomAlert("Infó", data.message, "info");
                    addBtn.textContent = 'Added ✔️';
                    addBtn.style.pointerEvents = 'none';
                } else {
                    showCustomAlert("Hiba", data.message, "error");
                }
            }).catch(err => {
                console.error("Fetch hiba történt:", err);
            });
        }
        return;
    }

    const deleteBtn = event.target.closest('.maps-delete');
    if (deleteBtn) {
        const mapId = deleteBtn.getAttribute('data-mapid');
        const card = deleteBtn.closest('.maps-card');
        const mapName = card.querySelector('.map-name').textContent.trim();

        showCustomConfirm("Törlés megerősítése", `Biztosan törlöd a(z) "${mapName}" nevű pályát?`, "danger", function () {
            fetch(mapUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_map', map_id: mapId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.4s ease'; card.style.opacity = '0'; card.style.transform = 'scale(0.5)';
                    setTimeout(() => card.remove(), 400);
                    showCustomAlert("Siker", data.message || "Pálya törölve!", "success");
                } else showCustomAlert("Hiba", data.message, "error");
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
            if (data.status === 'success') showCustomAlert("Siker", data.message || "Pálya visszaállítva!", "success", () => window.location.reload());
            else showCustomAlert("Hiba", data.message, "error");
        });
        return;
    }
});