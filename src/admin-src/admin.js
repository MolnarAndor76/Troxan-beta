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
let currentHardDeleteTarget = { id: null, username: '' };
let currentLogsData = [];

function openModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('admin-hidden');
}

function closeModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('admin-hidden');
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
    titleEl.className = 'admin-modal-alert-title';
    if (type === 'error') titleEl.classList.add('admin-modal-alert-title-danger');
    else if (type === 'success') titleEl.classList.add('admin-modal-alert-title-success');

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
    const titleEl = document.getElementById('basesite-confirm-title');
    const okBtn = document.getElementById('basesite-confirm-ok-btn');

    headerEl.className = 'admin-modal-alert-header';
    titleEl.className = 'admin-modal-alert-title';
    okBtn.className = 'admin-action-btn admin-btn-red admin-confirm-btn';

    if (type === 'danger') {
        headerEl.classList.add('admin-modal-alert-header-danger');
        titleEl.classList.add('admin-modal-alert-title-danger');
    } else {
        okBtn.className = 'admin-action-btn admin-btn-yellow admin-confirm-btn';
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
            const nameEl = card.querySelector('.admin-username-btn') || card.querySelector('span[title]');
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
                emptyMsg.className = 'admin-empty-msg';
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
        grid.innerHTML = '<p class="admin-grid-message">Library is empty or no maps match the filter. 🏝️</p>';
        return;
    }

    filteredMaps.forEach(map => {
        let statusBadge = '';
        if (map.status == 1) statusBadge = '<span class="admin-map-status-badge admin-map-status-pub">Pub</span>';
        else if (map.status == 0) statusBadge = '<span class="admin-map-status-badge admin-map-status-draft">Draft</span>';
        else if (map.status == 3) statusBadge = '<span class="admin-map-status-badge admin-map-status-unpub">Unpub</span>';
        else if (map.status == 4) statusBadge = '<span class="admin-map-status-badge admin-map-status-banned">Banned</span>';
        else if (map.status == 5) statusBadge = '<span class="admin-map-status-badge admin-map-status-scrap">Scrap</span>';

        const isCreatorEngineer = (map.creator_role === 'Engineer');
        const cardBorder = isCreatorEngineer ? 'admin-map-image-engineer' : 'admin-map-image-default';

        const isCreatedByTargetPlayer = Number(map.creator_user_id) === Number(currentAdminTargetUser.id);
        const removeBtnHtml = isCreatedByTargetPlayer
            ? ''
            : `<button class="admin-remove-map-btn" data-mapid="${map.id}" title="Remove from player's library">🗑️</button>`;

        const cardHtml = `
            <article class="admin-map-card" data-mapid="${map.id}">
                <div class="admin-map-image ${cardBorder}">
                    ${statusBadge}
                    <img src="${map.map_picture}" class="admin-map-image-img">
                </div>
                <div class="admin-map-info">
                    <p class="admin-map-name-text">${map.map_name}</p>
                    <p class="admin-map-creator">${isCreatorEngineer ? '🛠️ ' : ''}By: ${map.creator_name}</p>
                    <div class="admin-map-actions">
                        <button class="admin-edit-map-name-btn admin-map-edit-btn" data-mapid="${map.id}">✏️ Edit</button>
                        ${removeBtnHtml}
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
        logsContainer.innerHTML = '<p class="admin-log-message">No logs match this date filter.</p>';
        return;
    }

    let html = '';
    logs.forEach(log => {
        html += `
            <div class="admin-log-card">
                <div class="admin-log-header" data-logid="${log.id}">
                    <div class="admin-log-meta">🗓️ ${log.date}</div>
                    <div class="admin-log-meta">⭐ ${log.score}</div>
                    <button class="admin-log-view-btn">View</button>
                </div>
                <div id="log-details-${log.id}" class="admin-log-details admin-hidden">
                    <p>⚔️ Enemies killed: <span class="admin-log-details-value">${log.details['Enemies killed']}</span></p>
                    <p>💀 Deaths: <span class="admin-log-details-value">${log.details['Deaths']}</span></p>
                    <p>📖 Story finishes: <span class="admin-log-details-value">${log.details['Story finished']}</span></p>
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
grid.innerHTML = '<p class="admin-grid-message admin-grid-message-loading">Loading maps...</p>';
    
    fetch(adminUrl, {
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_user_maps', target_user_id: userId })
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') {
            currentAdminMaps = data.maps;
            renderAdminMaps();
        } else {
            grid.innerHTML = `<p class="admin-grid-message admin-grid-message-error">Error: ${data.message}</p>`;
        }
    }).catch(err => {
            grid.innerHTML = '<p class="admin-grid-message admin-grid-message-error">Network error occurred.</p>';
    });
}

// ==========================================
// 3. ESEMÉNYEK KEZELÉSE (MINDEN KATTINTÁS)
// ==========================================
document.addEventListener('click', function(event) {
    if (!event.target) return;
    if (!document.querySelector('.admin-page-shell')) return;

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
        if (modal) modal.classList.remove('admin-hidden');
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
        if (modal) modal.classList.add('admin-hidden');
        return;
    }

    if (event.target.classList.contains('admin-details-modal')) {
        if (event.target.id === 'basesite-alert-modal') {
            event.target.classList.add('admin-hidden');
            window.alertCallback = null;
            return;
        }
        if (event.target.id === 'basesite-confirm-modal') {
            event.target.classList.add('admin-hidden');
            window.confirmCallback = null;
            return;
        }
        event.target.classList.add('admin-hidden');
        return;
    }

    // --- VIEW LOGS ---
    const viewLogsBtn = event.target.closest('.admin-view-logs-btn');
    if (viewLogsBtn) {
        const userId = viewLogsBtn.getAttribute('data-userid');
        const username = viewLogsBtn.getAttribute('data-username');
        const detailsModal = viewLogsBtn.closest('.admin-details-modal');
        if (detailsModal) detailsModal.classList.add('admin-hidden');
        
        const logsModal = document.getElementById('global-logs-modal');
        const logsContainer = document.getElementById('logs-container');
        document.getElementById('logs-modal-title').innerText = username + " - Logs";
        logsContainer.innerHTML = '<p class="admin-log-message admin-log-message-loading">Fetching logs from server...</p>';
        openModalById('global-logs-modal');

        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_logs', target_user_id: userId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                currentLogsData = data.logs || [];
                if (currentLogsData.length === 0) { logsContainer.innerHTML = '<p class="admin-log-message">No logs found for this player.</p>'; return; }
                renderLogs(currentLogsData);
            } else logsContainer.innerHTML = `<p class="admin-log-message admin-log-message-error">Error: ${data.message}</p>`;
        });
        return;
    }

    const hardDeleteOpenBtn = event.target.closest('.admin-hard-delete-open-btn');
    if (hardDeleteOpenBtn) {
        const userId = hardDeleteOpenBtn.getAttribute('data-userid');
        const username = hardDeleteOpenBtn.getAttribute('data-username') || '';
        currentHardDeleteTarget = { id: userId, username: username };

        document.getElementById('admin-hard-delete-target').innerText = username;
        document.getElementById('admin-hard-delete-input').value = '';
        openModalById('admin-hard-delete-modal');
        return;
    }

    if (event.target.closest('#admin-hard-delete-confirm-btn')) {
        const confirmInput = document.getElementById('admin-hard-delete-input');
        const confirmText = (confirmInput ? confirmInput.value : '').trim();

        if (confirmText !== 'CONFIRM') {
            showCustomAlert('Error', 'Type CONFIRM in uppercase to permanently delete this account.', 'error');
            if (confirmInput) confirmInput.focus();
            openModalById('admin-hard-delete-modal');
            return;
        }

        fetch(adminUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'hard_delete_user', target_user_id: currentHardDeleteTarget.id, confirm_text: confirmText })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                closeModalById('admin-hard-delete-modal');
                showCustomAlert('Success', data.message, 'success', () => location.reload());
            } else {
                openModalById('admin-hard-delete-modal');
                showCustomAlert('Error', data.message, 'error');
            }
        }).catch(() => {
            openModalById('admin-hard-delete-modal');
            showCustomAlert('Error', 'Network error occurred.', 'error');
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
        if (!reason) { showCustomAlert('Error', 'Reason is required.', 'error'); return; }

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
    const logViewBtn = event.target.closest('.admin-log-view-btn');
    if (logViewBtn) {
        const logHeaderEl = logViewBtn.closest('.admin-log-header');
        const logId = logHeaderEl ? logHeaderEl.getAttribute('data-logid') : null;
        const detailsDiv = logId ? document.getElementById(`log-details-${logId}`) : null;
        if (detailsDiv) detailsDiv.classList.toggle('admin-hidden');
        return;
    }

    const logHeader = event.target.closest('.admin-log-header');
    if (logHeader) {
        const logId = logHeader.getAttribute('data-logid');
        const detailsDiv = document.getElementById(`log-details-${logId}`);
        if (detailsDiv) detailsDiv.classList.toggle('admin-hidden');
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

    // PÁLYA ELTÁVOLÍTÁSA A JÁTÉKOS KÖNYVTÁRÁBÓL
    const removeMapBtn = event.target.closest('.admin-remove-map-btn');
    if (removeMapBtn) {
        const mapId = removeMapBtn.getAttribute('data-mapid');

        showCustomConfirm('Remove map', 'Are you sure you want to remove this map from the player\'s library?', 'danger', () => {
            fetch(adminUrl, {
                method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'admin_remove_map', map_id: mapId, target_user_id: currentAdminTargetUser.id })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    showCustomAlert('Success', data.message, 'success', () => fetchAdminMaps(currentAdminTargetUser.id));
                } else {
                    showCustomAlert('Error', data.message, 'error');
                }
            }).catch(() => {
                showCustomAlert('Error', 'Network error occurred.', 'error');
            });
        });

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