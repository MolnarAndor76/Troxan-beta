// --- ADMIN AREA: Menük, Modálok és Akciók logikája ---
document.addEventListener('click', (event) => {
    
    // ==========================================
    // 1. HAMBURGER MENÜ LOGIKÁJA
    // ==========================================
    const hamburgerBtn = event.target.closest('.admin-hamburger-btn');
    const closeBtn = event.target.closest('.admin-close-menu-btn');
    const clickedInsideMenu = event.target.closest('.admin-card-actions');
    
    if (hamburgerBtn) {
        document.querySelectorAll('.admin-card-actions.menu-open').forEach(menu => {
            menu.classList.remove('menu-open');
            menu.closest('.admin-user-card').style.zIndex = '1';
        });

        const userCard = hamburgerBtn.closest('.admin-user-card');
        const actionsMenu = userCard.querySelector('.admin-card-actions');
        
        if (actionsMenu) {
            const isOpen = actionsMenu.classList.toggle('menu-open');
            userCard.style.zIndex = isOpen ? '50' : '1';
        }
        return;
    }

    if (closeBtn) {
        const actionsMenu = closeBtn.closest('.admin-card-actions');
        if (actionsMenu) {
            actionsMenu.classList.remove('menu-open');
            actionsMenu.closest('.admin-user-card').style.zIndex = '1';
        }
        return;
    }

    if (!clickedInsideMenu && !event.target.closest('.admin-action-btn')) {
        document.querySelectorAll('.admin-card-actions.menu-open').forEach(menu => {
            menu.classList.remove('menu-open');
            menu.closest('.admin-user-card').style.zIndex = '1';
        });
    }

    // ==========================================
    // 2. FELHASZNÁLÓI RÉSZLETEK MODÁL LOGIKÁJA
    // ==========================================
    const usernameBtn = event.target.closest('.admin-username-btn');
    if (usernameBtn) {
        const userId = usernameBtn.getAttribute('data-userid');
        const modal = document.getElementById(`details-modal-${userId}`);
        if (modal) modal.classList.remove('hidden');
        return;
    }

    const closeDetailsBtn = event.target.closest('.admin-close-details-btn');
    if (closeDetailsBtn) {
        const modal = closeDetailsBtn.closest('.admin-details-modal');
        if (modal) modal.classList.add('hidden');
        return;
    }

    // Zárás, ha a sötét háttérre kattint (a sima és a logs modálnál is)
    if (event.target.classList.contains('admin-details-modal')) {
        event.target.classList.add('hidden');
    }

    // ==========================================
    // 3. BAN ÉS ROLE LOGIKA
    // ==========================================
    const banToggleBtn = event.target.closest('.admin-ban-toggle-btn');
    if (banToggleBtn) {
        const userId = banToggleBtn.getAttribute('data-userid');
        const action = banToggleBtn.getAttribute('data-action'); 
        
        const confirmMsg = action === 'ban' 
            ? 'Are you sure you want to BAN this player?' 
            : 'Are you sure you want to UNBAN this player?';

        if (confirm(confirmMsg)) {
            fetch('http://localhost/troxan/app/api.php?path=admin', {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_ban', target_user_id: userId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') { alert(data.message); location.reload(); } 
                else { alert('Error: ' + data.message); }
            }).catch(err => { console.error(err); alert('Network error occurred.'); });
        }
        return;
    }

    const roleBtn = event.target.closest('.admin-role-btn');
    if (roleBtn) {
        const userId = roleBtn.getAttribute('data-userid');
        const roleAction = roleBtn.getAttribute('data-action'); 
        
        const confirmMsg = roleAction === 'promote' 
            ? 'Are you sure you want to PROMOTE this player?' 
            : 'Are you sure you want to DEMOTE this player?';

        if (confirm(confirmMsg)) {
            fetch('http://localhost/troxan/app/api.php?path=admin', {
                method: 'POST', credentials: 'include', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_role', role_action: roleAction, target_user_id: userId })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') { alert(data.message); location.reload(); } 
                else { alert('Error: ' + data.message); }
            }).catch(err => { console.error(err); alert('Network error occurred.'); });
        }
        return;
    }

    // ==========================================
    // 4. VIEW LOGS LOGIKA (ÚJ!)
    // ==========================================
    const viewLogsBtn = event.target.closest('.admin-view-logs-btn');
    if (viewLogsBtn) {
        const userId = viewLogsBtn.getAttribute('data-userid');
        const username = viewLogsBtn.getAttribute('data-username');
        
        // Bezárjuk a személyes Részletek modált
        const detailsModal = viewLogsBtn.closest('.admin-details-modal');
        if (detailsModal) detailsModal.classList.add('hidden');
        
        // Megnyitjuk a globális Logs modált egy betöltő szöveggel
        const logsModal = document.getElementById('global-logs-modal');
        const logsContainer = document.getElementById('logs-container');
        document.getElementById('logs-modal-title').innerText = username + " - Logs";
        logsContainer.innerHTML = '<p class="text-center font-bold animate-pulse text-orange-900">Fetching logs from server...</p>';
        logsModal.classList.remove('hidden');

        // Lekérjük az adatokat a szervertől
        fetch('http://localhost/troxan/app/api.php?path=admin', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_logs', target_user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.logs.length === 0) {
                    logsContainer.innerHTML = '<p class="text-center font-bold text-orange-900">No logs found for this player.</p>';
                    return;
                }

                // Generáljuk a listát
                let html = '';
                data.logs.forEach(log => {
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
                        </div>
                    `;
                });
                logsContainer.innerHTML = html;
            } else {
                logsContainer.innerHTML = `<p class="text-center font-bold text-red-600">Error: ${data.message}</p>`;
            }
        })
        .catch(err => {
            logsContainer.innerHTML = '<p class="text-center font-bold text-red-600">Network error occurred.</p>';
        });
        return;
    }

    // Globális Logs modál bezárása
    const closeLogsBtn = event.target.closest('.admin-close-logs-btn');
    if (closeLogsBtn) {
        document.getElementById('global-logs-modal').classList.add('hidden');
        return;
    }

    // Egyedi log kinyitása / becsukása
    const logHeader = event.target.closest('.admin-log-header');
    if (logHeader) {
        const logId = logHeader.getAttribute('data-logid');
        const detailsDiv = document.getElementById(`log-details-${logId}`);
        if (detailsDiv) {
            detailsDiv.classList.toggle('hidden');
        }
        return;
    }
});