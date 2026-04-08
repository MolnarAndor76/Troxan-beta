console.log("🟢 Admin JS Loaded!");

// ==========================================
// 0. GLOBÁLIS VÁLTOZÓK ÉS UTILITIES
// ==========================================
const adminUrl = `/app/api.php?path=admin`;

window.alertCallback = null;
window.confirmCallback = null;

let currentAdminMaps = []; // Itt tároljuk a letöltött kártyákat a gyors szűréshez
let currentAdminTargetUser = { id: null, username: '' };
let currentBanTarget = { id: null, username: '', action: 'ban', type: 'user', mapId: null, targetUserId: null };
let currentNameTarget = { id: null, username: '' };
let currentAdminRenameMap = { mapId: null, oldName: '' };
let currentLogsData = [];

function openModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden', 'basesite-hidden');
}

function closeModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden', 'basesite-hidden');
}

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
    openModalById('basesite-alert-modal');
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
        headerEl.className = 'border-b-4 border-red-950 pb-2 mb-4';
        document.getElementById('basesite-confirm-title').className = 'text-xl font-bold text-red-600';
        okBtn.className = 'admin-action-btn admin-btn-red py-2 px-6';
    } else {
        headerEl.className = 'border-b-4 border-orange-950 pb-2 mb-4';
        document.getElementById('basesite-confirm-title').className = 'text-xl font-bold text-orange-950';
        okBtn.className = 'admin-action-btn admin-btn-yellow py-2 px-6';
    }

    window.confirmCallback = onConfirm;
    openModalById('basesite-confirm-modal');
}

// ==========================================
// 1. VALÓS IDEJŰ KERESŐ (Event Delegation)
// ==========================================
document.addEventListener('input', function(event) {
    if (event.target && event.target.id === 'admin-search-input') {
        const filterText = event.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.admin-user-card');
        let hasVisibleCard = false;

        cards.forEach(card => {
            const nameEl = card.querySelector('.admin-username-btn') || card.querySelector('span[title="Protected profile!"]');
            if (nameEl) {
                const username = nameEl.innerText.replace('🔒', '').toLowerCase().trim();
                if (username.includes(filterText)) {
                    card.style.display = ''; 
                    hasVisibleCard = true;
                } else {
                    card.style.display = 'none';
                }
            }
        });

        const listContainer = document.querySelector('.admin-list');
        let emptyMsg = document.getElementById('live-search-empty-msg');

        if (!hasVisibleCard && cards.length > 0) {
            if (!emptyMsg && listContainer) {
                emptyMsg = document.createElement('p');
                emptyMsg.id = 'live-search-empty-msg';
                emptyMsg.className = 'admin-empty-msg text-center text-gray-500 font-bold text-lg mt-10';
                emptyMsg.innerText = 'No players found matching your search.';
                listContainer.appendChild(emptyMsg);
            }
            if (emptyMsg) emptyMsg.style.display = 'block';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
    }
});

document.addEventListener('keypress', function(event) {
    if (event.key === 'Enter' && event.target && event.target.id === 'admin-search-input') {
        event.preventDefault();
    }
});

// ==========================================
// 2. ADMIN MAPS RENDERELŐ FUNKCIÓ
// ==========================================
function renderAdminMaps() {
    const grid = document.getElementById('admin-maps-grid');
    const isFilterOn = document.getElementById('admin-maps-own-filter').checked;
    grid.innerHTML = '';

    let filteredMaps = currentAdminMaps;
    if (isFilterOn) {
        filteredMaps = currentAdminMaps.filter(m => m.creator_name.toLowerCase() === currentAdminTargetUser.username.toLowerCase());
    }

    if (filteredMaps.length === 0) {
        grid.innerHTML = '<p class="col-span-full text-center font-bold text-orange-900 text-xl mt-10">Library is empty or no maps match the filter. 🏝️</p>';
        return;
    }

    filteredMaps.forEach(map => {
        let statusBadge = '';
        if (map.status == 1) statusBadge = '<span class="bg-green-600 text-white text-[10px] px-1.5 py-0.5 rounded-sm border border-green-900 absolute top-1 right-1 font-bold shadow-md z-10 uppercase">Pub</span>';
        else if (map.status == 0) statusBadge = '<span class="bg-gray-500 text-white text-[10px] px-1.5 py-0.5 rounded-sm border border-gray-900 absolute top-1 right-1 font-bold shadow-md z-10 uppercase">Draft</span>';
        else if (map.status == 3) statusBadge = '<span class="bg-orange-500 text-white text-[10px] px-1.5 py-0.5 rounded-sm border border-orange-900 absolute top-1 right-1 font-bold shadow-md z-10 uppercase">Unpub</span>';
        else if (map.status == 4) statusBadge = '<span class="bg-red-600 text-white text-[10px] px-1.5 py-0.5 rounded-sm border border-red-900 absolute top-1 right-1 font-bold shadow-md z-10 uppercase">Banned</span>';
        else if (map.status == 5) statusBadge = '<span class="bg-gray-700 text-white text-[10px] px-1.5 py-0.5 rounded-sm border border-black absolute top-1 right-1 font-bold shadow-md z-10 uppercase">Scrap</span>';

        const isCreatorEngineer = (map.creator_role === 'Engineer');
        const cardBorder = isCreatorEngineer ? 'border-cyan-900 shadow-cyan-900/50' : 'border-orange-950 shadow-[2px_2px_0px_#000]';

        const cardHtml = `
            <article class="admin-map-card relative w-[240px] h-[340px] p-4 flex flex-col items-center justify-between transition-transform duration-300 hover:-translate-y-1" data-mapid="${map.id}">
                <div class="w-full h-28 border-4 ${cardBorder} rounded-sm overflow-hidden mb-2 relative shadow-[2px_2px_0px_#000]">
                    ${statusBadge}
                    <img src="${map.map_picture}" class="w-full h-full object-cover">
                </div>
                <div class="w-full flex flex-col items-center flex-1 justify-center">
                    <p class="font-extrabold text-lg text-yellow-400 drop-shadow-[2px_2px_0px_#000] text-center w-full truncate mb-1 admin-map-name-text">${map.map_name}</p>
                    <p class="text-xs font-bold text-white drop-shadow-[1px_1px_0px_#000] text-center w-full mb-auto truncate">${isCreatorEngineer ? '🛠️ ' : ''}By: ${map.creator_name}</p>
                    <div class="mt-2 flex justify-between items-center w-full gap-2 p-2 bg-orange-950/30 rounded-sm border border-orange-950/50">
                        <button class="admin-edit-map-name-btn bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-1.5 px-3 border-2 border-blue-950 rounded-sm shadow-[2px_2px_0px_#000] text-[10px] uppercase" data-mapid="${map.id}">✏️ Edit</button>
                        <button class="admin-remove-map-btn text-xl hover:scale-110 transition-transform cursor-pointer drop-shadow-md ml-auto" data-mapid="${map.id}" title="Remove / Ban">🗑️</button>
                    </div>
                </div>
            </article>
        `;
        grid.insertAdjacentHTML('beforeend', cardHtml);
    });
}

function renderLogs(logs) {
    const logsContainer = document.getElementById('logs-container');
    if (!logsContainer) return;

    if (logs.length === 0) {
        logsContainer.innerHTML = '<p class="text-center font-bold text-orange-900">No logs match this date filter.</p>';
        return;
    }

    let html = '';
    logs.forEach(log => {
        html += `
            <div class="border-2 border-orange-950 rounded-md bg-orange-100 p-3 mb-2 shadow-sm transition-all">
                <div class="flex justify-between items-center cursor-pointer admin-log-header hover:bg-orange-200 p-1 rounded" data-logid="${log.id}">
                    <div class="font-bold text-orange-950 text-xs md:text-sm">🗓️ ${log.date}</div>
                    <div class="font-bold text-orange-950 text-xs md:text-sm">⭐ ${log.score}</div>
                    <button class="bg-blue-400 text-white px-3 py-1 rounded border-2 border-orange-950 font-bold text-xs hover:bg-blue-500 shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_rgba(0,0,0,1)]">View</button>
                </div>
                <div id="log-details-${log.id}" class="hidden mt-3 pt-3 border-t-2 border-orange-950/30 text-sm font-bold text-orange-900 flex flex-col gap-1">
                    <p>⚔️ Enemies killed: <span class="font-normal">${log.details['Enemies killed']}</span></p>
                    <p>💀 Deaths: <span class="font-normal">${log.details['Deaths']}</span></p>
                    <p>📖 Story finishes: <span class="font-normal">${log.details['Story finished']}</span></p>
                    <p>⏱️ Time played: <span class="font-normal">${log.details['Time played']}</span></p>
                </div>
            </div>`;
    });
    logsContainer.innerHTML = html;
}

function applyLogDateFilter() {
    const from = document.getElementById('logs-date-from').value;
    const to = document.getElementById('logs-date-to').value;

    if (!from && !to) { renderLogs(currentLogsData); return; }

    const fromDate = from ? new Date(from) : null;
    const toDate = to ? new Date(to) : null;

    const filtered = currentLogsData.filter(log => {
        const [d, t] = log.date.split(' ');
        const parsedDate = new Date(d.replace(/\./g, '-') + 'T' + (t || '00:00') + ':00');
        if (Number.isNaN(parsedDate.getTime())) return false;
        if (fromDate && parsedDate < fromDate) return false;
        if (toDate) {
            const toDateEnd = new Date(toDate);
            toDateEnd.setHours(23, 59, 59, 999);
            if (parsedDate > toDateEnd) return false;
        }
        return true;
    });

    renderLogs(filtered);
}

// Ha a checkbox változik, azonnal újrarenderelünk
document.addEventListener('change', (event) => {
    if (event.target.id === 'admin-maps-own-filter') {
        renderAdminMaps();
    }
});

function fetchAdminMaps(userId) {
    const grid = document.getElementById('admin-maps-grid');
grid.innerHTML = '<p class="col-span-full text-center font-bold text-orange-900 text-xl animate-pulse mt-10">Loading maps...</p>';
    
    fetch(adminUrl, {
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_user_maps', target_user_id: userId })
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') {
            currentAdminMaps = data.maps;
            renderAdminMaps();
        } else {
            grid.innerHTML = `<p class="col-span-full text-center font-bold text-red-600 text-xl mt-10">Error: ${data.message}</p>`;
        }
    }).catch(err => {
            grid.innerHTML = '<p class="col-span-full text-center font-bold text-red-600 text-xl mt-10">Network error occurred.</p>';
    });
}

// ==========================================
// 3. ESEMÉNYEK KEZELÉSE (MINDEN KATTINTÁS)
// ==========================================
document.addEventListener('click', function(event) {
    if (!event.target) return;

    const backBtn = event.target.closest('#admin-back-btn');
    if (backBtn) {
        if (typeof window.loadContent === 'function') {
            window.loadContent('profile');
        } else {
            window.location.href = '/profile';
        }
        return;
    }

    if (event.target.closest('#admin-search-btn')) { event.preventDefault(); return; }

    // --- ALERT / CONFIRM BEZÁRÁSOK ---
    if (event.target.closest('#basesite-alert-close-btn') || event.target.closest('#basesite-alert-ok-btn')) {
        closeModalById('basesite-alert-modal');
        if (window.alertCallback) { window.alertCallback(); window.alertCallback = null; }
        return;
    }
    if (event.target.closest('#basesite-confirm-close-btn') || event.target.closest('#basesite-confirm-cancel-btn')) {
        closeModalById('basesite-confirm-modal');
        window.confirmCallback = null;
        return;
    }
    if (event.target.closest('#basesite-confirm-ok-btn')) {
        closeModalById('basesite-confirm-modal');
        if (window.confirmCallback) { window.confirmCallback(); window.confirmCallback = null; }
        return;
    }

    // --- BAN / PROMOTE LOGIKA ---
    const banToggleBtn = event.target.closest('.admin-ban-toggle-btn');
    if (banToggleBtn) {
        const userId = banToggleBtn.getAttribute('data-userid');
        const action = banToggleBtn.getAttribute('data-action');
        const username = banToggleBtn.getAttribute('data-username') || '';

        currentBanTarget = { id: userId, username: username, action: action, type: 'user', mapId: null, targetUserId: null };
        document.getElementById('ban-reason-title').innerText = action === 'ban' ? 'Ban Player' : 'Unban Player';
        document.getElementById('ban-reason-target').innerText = username;
        document.getElementById('ban-reason-input').value = '';
        document.getElementById('ban-reason-input').placeholder = action === 'ban' ? 'Ban reason (required)' : 'Unban reason (required)';
        document.getElementById('ban-reason-confirm-btn').innerText = action === 'ban' ? 'Confirm Ban' : 'Confirm Unban';

        // show the reason modal
        openModalById('admin-ban-reason-modal');
        return;
    }

    const roleBtn = event.target.closest('.admin-role-btn');
    if (roleBtn) {
        const userId = roleBtn.getAttribute('data-userid');
        const roleAction = roleBtn.getAttribute('data-action'); 
        const msg = roleAction === 'promote' ? 'Are you sure you want to PROMOTE this player?' : 'Are you sure you want to DEMOTE this player?';
        showCustomConfirm("Role Change", msg, "danger", function() {
            fetch(adminUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_role', role_action: roleAction, target_user_id: userId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') { showCustomAlert("Success", data.message, "success", () => location.reload()); } 
                else { showCustomAlert("Error", data.message, "error"); }
            });
        });
        return;
    }

    // --- HAMBURGER MENÜ ÉS MODÁLOK BEZÁRÁSA ---
    const hamburgerBtn = event.target.closest('.admin-hamburger-btn');
    if (hamburgerBtn) {
        document.querySelectorAll('.admin-card-actions.menu-open').forEach(menu => {
            menu.classList.remove('menu-open'); menu.closest('.admin-user-card').style.zIndex = '1';
        });
        const userCard = hamburgerBtn.closest('.admin-user-card');
        const actionsMenu = userCard.querySelector('.admin-card-actions');
        if (actionsMenu) {
            const isOpen = actionsMenu.classList.toggle('menu-open');
            userCard.style.zIndex = isOpen ? '50' : '1';
        }
        return;
    }
    
    if (event.target.closest('.admin-close-menu-btn')) {
        const actionsMenu = event.target.closest('.admin-card-actions');
        if (actionsMenu) { actionsMenu.classList.remove('menu-open'); actionsMenu.closest('.admin-user-card').style.zIndex = '1'; }
        return;
    }
    
    if (!event.target.closest('.admin-card-actions') && !event.target.closest('.admin-action-btn') && !event.target.closest('.admin-details-modal')) {
        document.querySelectorAll('.admin-card-actions.menu-open').forEach(menu => {
            menu.classList.remove('menu-open'); menu.closest('.admin-user-card').style.zIndex = '1';
        });
    }

    const usernameBtn = event.target.closest('.admin-username-btn');
    if (usernameBtn) {
        const userId = usernameBtn.getAttribute('data-userid');
        const modal = document.getElementById(`details-modal-${userId}`);
        if (modal) modal.classList.remove('hidden');
        return;
    }

    const changeNameBtn = event.target.closest('.admin-change-name-open-btn');
    if (changeNameBtn) {
        const userId = changeNameBtn.getAttribute('data-userid');
        const username = changeNameBtn.getAttribute('data-username');
        currentNameTarget = { id: userId, username: username };

        document.getElementById('change-username-target').innerText = username;
        document.getElementById('change-username-input').value = '';
        document.getElementById('change-username-reason-input').value = '';
        openModalById('admin-change-username-modal');
        return;
    }
    
    const closeDetailsBtn = event.target.closest('.admin-close-details-btn');
    if (closeDetailsBtn && closeDetailsBtn.id !== 'basesite-alert-close-btn' && closeDetailsBtn.id !== 'basesite-confirm-close-btn') {
        const modal = closeDetailsBtn.closest('.admin-details-modal');
        if (modal) modal.classList.add('hidden');
        return;
    }

    if (event.target.classList.contains('admin-details-modal')) {
        if (event.target.id === 'basesite-alert-modal') {
            event.target.classList.add('hidden');
            window.alertCallback = null;
            return;
        }
        if (event.target.id === 'basesite-confirm-modal') {
            event.target.classList.add('hidden');
            window.confirmCallback = null;
            return;
        }
        event.target.classList.add('hidden');
        return;
    }

    // --- VIEW LOGS ---
    const viewLogsBtn = event.target.closest('.admin-view-logs-btn');
    if (viewLogsBtn) {
        const userId = viewLogsBtn.getAttribute('data-userid');
        const username = viewLogsBtn.getAttribute('data-username');
        const detailsModal = viewLogsBtn.closest('.admin-details-modal');
        if (detailsModal) detailsModal.classList.add('hidden');
        
        const logsModal = document.getElementById('global-logs-modal');
        const logsContainer = document.getElementById('logs-container');
        document.getElementById('logs-modal-title').innerText = username + " - Logs";
        logsContainer.innerHTML = '<p class="text-center font-bold animate-pulse text-orange-900">Fetching logs from server...</p>';
        openModalById('global-logs-modal');

        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_logs', target_user_id: userId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                currentLogsData = data.logs || [];
                if (currentLogsData.length === 0) { logsContainer.innerHTML = '<p class="text-center font-bold text-orange-900">No logs found for this player.</p>'; return; }
                renderLogs(currentLogsData);
            } else logsContainer.innerHTML = `<p class="text-center font-bold text-red-600">Error: ${data.message}</p>`;
        });
        return;
    }

    if (event.target.closest('#ban-reason-confirm-btn')) {
        const reasonInput = document.getElementById('ban-reason-input');
        const reason = reasonInput ? reasonInput.value.trim() : '';

        if (!reason) {
            showCustomAlert('Error', currentBanTarget.type === 'map' ? 'Map ban reason is required.' : 'Ban reason is required.', 'error');
            if (reasonInput) reasonInput.focus();
            openModalById('admin-ban-reason-modal');
            return;
        }

        let body = {};
        if (currentBanTarget.type === 'user') {
            body = { action: 'toggle_ban', target_user_id: currentBanTarget.id, reason: reason };
        } else {
            body = { action: 'admin_remove_map', map_id: currentBanTarget.mapId, target_user_id: currentBanTarget.targetUserId, reason: reason };
        }

        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                closeModalById('admin-ban-reason-modal');
                showCustomAlert('Success', data.message, 'success', () => {
                    if (currentBanTarget.type === 'map') fetchAdminMaps(currentAdminTargetUser.id);
                    else location.reload();
                });
            } else {
                openModalById('admin-ban-reason-modal');
                showCustomAlert('Error', data.message, 'error');
            }
        }).catch(() => {
            openModalById('admin-ban-reason-modal');
            showCustomAlert('Error', 'Network error occurred.', 'error');
        });
        return;
    }

    if (event.target.closest('#change-username-confirm-btn')) {
        const newName = document.getElementById('change-username-input').value.trim();
        const reason = document.getElementById('change-username-reason-input').value.trim();
        if (!newName) { showCustomAlert('Error', 'New name is required.', 'error'); return; }
        if (newName.length < 4 || newName.length > 12) { showCustomAlert('Error', 'Username must be between 4 and 12 characters.', 'error'); return; }

        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'change_username', target_user_id: currentNameTarget.id, new_username: newName, reason: reason })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                closeModalById('admin-change-username-modal');
                showCustomAlert('Success', data.message, 'success', () => location.reload());
            } else {
                openModalById('admin-change-username-modal');
                showCustomAlert('Error', data.message, 'error');
            }
        }).catch(() => {
            openModalById('admin-change-username-modal');
            showCustomAlert('Error', 'Network error occurred.', 'error');
        });
        return;
    }

    if (event.target.closest('#logs-date-filter-btn')) {
        applyLogDateFilter();
        return;
    }

    if (event.target.closest('#logs-date-clear-btn')) {
        document.getElementById('logs-date-from').value = '';
        document.getElementById('logs-date-to').value = '';
        applyLogDateFilter();
        return;
    }

    if (event.target.closest('.admin-close-logs-btn')) { closeModalById('global-logs-modal'); return; }
    const logHeader = event.target.closest('.admin-log-header');
    if (logHeader) {
        const logId = logHeader.getAttribute('data-logid');
        const detailsDiv = document.getElementById(`log-details-${logId}`);
        if (detailsDiv) detailsDiv.classList.toggle('hidden');
        return;
    }

    // ==========================================
    // 4. ÚJ: ADMIN MAPS GOMBOK (NYITÁS, TÖRLÉS, EDIT)
    // ==========================================
    
    // MAPS GOMB MEGNYOMÁSA (Megnyitja a modalt)
    const openMapsBtn = event.target.closest('.admin-maps-open-btn');
    if (openMapsBtn) {
        const userId = openMapsBtn.getAttribute('data-userid');
        const username = openMapsBtn.getAttribute('data-username');
        
        currentAdminTargetUser.id = userId;
        currentAdminTargetUser.username = username;

        document.getElementById('admin-maps-title').innerText = `${username}'s Library`;
        document.getElementById('admin-maps-own-filter').checked = false; // Reset szűrő
        openModalById('admin-maps-modal');
        
        // Zárjuk be a hamburger menüt ha mobil nézet
        const actionsMenu = openMapsBtn.closest('.admin-card-actions');
        if (actionsMenu) { actionsMenu.classList.remove('menu-open'); actionsMenu.closest('.admin-user-card').style.zIndex = '1'; }

        fetchAdminMaps(userId);
        return;
    }

    // MAPS MODAL BEZÁRÁSA
    if (event.target.closest('.admin-close-maps-btn')) {
        closeModalById('admin-maps-modal');
        return;
    }

    // PÁLYA TÖRLÉSE / BANNOLÁSA
    const removeMapBtn = event.target.closest('.admin-remove-map-btn');
    if (removeMapBtn) {
        const mapId = removeMapBtn.getAttribute('data-mapid');

        currentBanTarget = { id: null, username: '', action: 'map_ban', type: 'map', mapId: mapId, targetUserId: currentAdminTargetUser.id };
        document.getElementById('ban-reason-title').innerText = 'Ban Map';
        document.getElementById('ban-reason-target').innerText = `Map ID: ${mapId}`;
        document.getElementById('ban-reason-input').value = '';
        openModalById('admin-ban-reason-modal');

        return;
    }

    // PÁLYA NEVÉNEK SZERKESZTÉSE
    const editMapBtn = event.target.closest('.admin-edit-map-name-btn');
    if (editMapBtn) {
        const mapId = editMapBtn.getAttribute('data-mapid');
        const card = editMapBtn.closest('.admin-map-card');
        const oldName = card.querySelector('.admin-map-name-text').innerText;
        
        currentAdminRenameMap = { mapId: mapId, oldName: oldName };
        document.getElementById('admin-rename-map-old-name').innerText = oldName;
        document.getElementById('admin-rename-map-input').value = oldName;
        openModalById('admin-rename-map-modal');
        
        return;
    }

    // PÁLYA NEVÉNEK MEGERŐSÍTÉSE
    if (event.target.closest('#admin-rename-map-confirm-btn')) {
        if (!currentAdminRenameMap) return;
        
        const newName = document.getElementById('admin-rename-map-input').value.trim();
        if (!newName || newName.length < 1 || newName.length > 64) {
            showCustomAlert("Error", "Map name must be 1-64 characters.", "error");
            return;
        }
        
        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'admin_edit_map_name', map_id: currentAdminRenameMap.mapId, new_name: newName })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                closeModalById('admin-rename-map-modal');
                showCustomAlert("Success", "Map renamed successfully!", "success", () => {
                    fetchAdminMaps(currentAdminTargetUser.id);
                });
            } else {
                showCustomAlert("Error", data.message, "error");
                openModalById('admin-rename-map-modal');
            }
        }).catch(() => {
            showCustomAlert("Error", "Network error occurred.", "error");
            openModalById('admin-rename-map-modal');
        });
        return;
    }
});