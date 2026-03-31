// ====== UNIVERZÁLIS CUSTOM ALERT & CONFIRM LOGIKA ======
let alertCallback = null;
let confirmCallback = null;

function showCustomAlert(title, message, type = 'info', callback = null) {
    const modal = document.getElementById('basesite-alert-modal');
    if (!modal) return;
    document.getElementById('basesite-alert-title').innerText = title;
    document.getElementById('basesite-alert-message').innerHTML = message;
    const headerEl = document.getElementById('basesite-alert-header');
    
    if (type === 'error') headerEl.className = 'basesite-modal-header bg-red-800 border-b-4 border-red-950';
    else if (type === 'success') headerEl.className = 'basesite-modal-header bg-green-800 border-b-4 border-green-950';
    else headerEl.className = 'basesite-modal-header bg-orange-900 border-b-4 border-orange-950';

    alertCallback = callback;
    modal.classList.remove('basesite-hidden');
}

function showCustomConfirm(title, message, type = 'danger', onConfirm) {
    const modal = document.getElementById('basesite-confirm-modal');
    if (!modal) return;
    document.getElementById('basesite-confirm-title').innerText = title;
    document.getElementById('basesite-confirm-message').innerHTML = message;
    const headerEl = document.getElementById('basesite-confirm-header');
    const okBtn = document.getElementById('basesite-confirm-ok-btn');

    if (type === 'danger') {
        headerEl.className = 'basesite-modal-header bg-red-800 border-b-4 border-red-950';
        okBtn.className = 'bg-red-600 hover:bg-red-500 text-white font-extrabold py-2 px-6 rounded border-2 border-red-900 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer';
    } else {
        headerEl.className = 'basesite-modal-header bg-orange-900 border-b-4 border-orange-950';
        okBtn.className = 'bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-extrabold py-2 px-6 rounded border-2 border-orange-950 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer';
    }

    confirmCallback = onConfirm;
    modal.classList.remove('basesite-hidden');
}

document.addEventListener('click', (event) => {
    if (event.target.closest('#basesite-alert-close-btn') || event.target.closest('#basesite-alert-ok-btn')) {
        const alertModal = document.getElementById('basesite-alert-modal');
        if (alertModal && !alertModal.classList.contains('basesite-hidden')) {
            alertModal.classList.add('basesite-hidden');
            if (alertCallback) { alertCallback(); alertCallback = null; }
        }
    }
    if (event.target.closest('#basesite-confirm-close-btn') || event.target.closest('#basesite-confirm-cancel-btn')) {
        const confirmModal = document.getElementById('basesite-confirm-modal');
        if (confirmModal && !confirmModal.classList.contains('basesite-hidden')) {
            confirmModal.classList.add('basesite-hidden');
            confirmCallback = null;
        }
    }
    if (event.target.closest('#basesite-confirm-ok-btn')) {
        const confirmModal = document.getElementById('basesite-confirm-modal');
        if (confirmModal && !confirmModal.classList.contains('basesite-hidden')) {
            confirmModal.classList.add('basesite-hidden');
            if (confirmCallback) { confirmCallback(); confirmCallback = null; }
        }
    }
});

// ====== TABS LOGIKA ======
document.addEventListener('click', (event) => {
  const btn = event.target.closest('.basesite-tab-btn');
  if (!btn) return; 

  const tabId = btn.id.replace('basesite-btn-', '');
  document.querySelectorAll('.basesite-tab-content').forEach(c => { c.classList.remove('basesite-block'); c.classList.add('basesite-hidden'); });
  document.querySelectorAll('.basesite-tab-btn').forEach(b => { b.classList.remove('basesite-tab-active'); b.classList.add('basesite-tab-inactive'); });
  const targetContent = document.getElementById('basesite-tab-' + tabId);
  if (targetContent) {
    targetContent.classList.remove('basesite-hidden'); targetContent.classList.add('basesite-block');
    targetContent.animate([{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 300, easing: 'ease-in-out' });
  }
  btn.classList.remove('basesite-tab-inactive'); btn.classList.add('basesite-tab-active');
});

// ====== POP-UP LOGIKA ======
document.addEventListener('click', (event) => {
  const modal = document.getElementById('basesite-req-modal');
  if (!modal) return; 
  if (event.target.closest('#basesite-open-req-btn')) {
    modal.classList.remove('basesite-hidden'); document.body.style.overflow = 'hidden'; 
    const mw = modal.querySelector('.basesite-modal-window');
    if (mw) mw.animate([{ opacity: 0, transform: 'translateY(15px) scale(0.95)' }, { opacity: 1, transform: 'translateY(0) scale(1)' }], { duration: 300, easing: 'ease-out' });
  }
  if ((event.target.closest('#basesite-close-req-btn') || event.target === modal) && !event.target.closest('#basesite-alert-modal') && !event.target.closest('#basesite-confirm-modal')) {
    const mw = modal.querySelector('.basesite-modal-window');
    if (mw) {
      const fadeOut = mw.animate([{ opacity: 1, transform: 'translateY(0) scale(1)' }, { opacity: 0, transform: 'translateY(15px) scale(0.95)' }], { duration: 200, easing: 'ease-in' });
      fadeOut.onfinish = () => { modal.classList.add('basesite-hidden'); document.body.style.overflow = 'auto'; };
    } else { modal.classList.add('basesite-hidden'); document.body.style.overflow = 'auto'; }
  }
});

// ====== DOWNLOAD GOMB ======
document.addEventListener('click', (event) => {
  const downloadBtn = event.target.closest('#basesite-download-game-btn');
  if (!downloadBtn) return; 
  if (downloadBtn.getAttribute('data-loggedin') !== 'true') {
    event.preventDefault();
    showCustomAlert("Hold up, adventurer!", "You need to log in or create an account before downloading the game.", "error", () => { window.location.href = '/login'; });
  } else showCustomAlert("Downloading", "Download is starting... (Placeholder)", "success");
});

// ====== PATCH NOTES ADMIN LOGIKA ======
document.addEventListener('click', async (event) => {
    const fetchConfig = (bodyData) => ({
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(bodyData)
    });
    const apiUrl = '/app/api.php?path=main';

    // --- 0. LAKAT (Toggle Lock - Csak Engineer) ---
    const lockBtn = event.target.closest('.patch-lock-btn');
    if (lockBtn) {
        const card = lockBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');

        try {
            lockBtn.innerHTML = '⏳';
            const response = await fetch(apiUrl, fetchConfig({ action: 'toggle_lock', id: patchId }));
            const result = await response.json();
            
            if (response.ok) {
                showCustomAlert("Siker", result.message, "success", () => { window.location.reload(); });
            } else { 
                showCustomAlert("Hiba", result.message, "error"); 
                lockBtn.innerHTML = '🔒'; 
            }
        } catch (e) { 
            showCustomAlert("Hiba", "Server error!", "error"); 
            lockBtn.innerHTML = '🔒'; 
        }
        return;
    }

    // 1. KUKA (Delete)
    const deleteBtn = event.target.closest('.patch-delete-btn');
    if (deleteBtn) {
        const card = deleteBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');
        showCustomConfirm("Törlés megerősítése", "Biztosan a Lomtárba küldöd ezt a frissítést?", "danger", async () => {
            try {
                const response = await fetch(apiUrl, fetchConfig({ action: 'delete', id: patchId }));
                const result = await response.json();
                if (response.ok) {
                    card.style.transition = "opacity 0.3s, transform 0.3s"; card.style.opacity = "0"; card.style.transform = "scale(0.9)";
                    setTimeout(() => card.remove(), 300);
                } else showCustomAlert("Hiba", result.message, "error");
            } catch (e) { showCustomAlert("Hiba", "Server connection error!", "error"); }
        });
        return; 
    }

    // 2. CERUZA (Edit mód)
    const editBtn = event.target.closest('.patch-edit-btn');
    if (editBtn) {
        const card = editBtn.closest('[data-id]');
        const titleEl = card.querySelector('.patch-title');
        const descEl = card.querySelector('.patch-desc');
        const currentTitle = titleEl.innerText;
        const currentDesc = descEl.innerHTML.replace(/<br\s*[\/]?>/gi, '\n').trim();

        titleEl.innerHTML = `<input type="text" class="edit-title-input w-full bg-white border-2 border-orange-950 p-1 text-orange-950 rounded" value="${currentTitle}">`;
        descEl.innerHTML = `<textarea class="edit-desc-input w-full h-32 bg-white border-2 border-orange-950 p-2 text-gray-800 rounded mt-2">${currentDesc}</textarea>`;

        editBtn.innerHTML = '💾'; editBtn.title = "Save Changes"; editBtn.classList.replace('patch-edit-btn', 'patch-save-btn');
        return; 
    }

    // 3. MENTÉS (Save)
    const saveBtn = event.target.closest('.patch-save-btn');
    if (saveBtn) {
        const card = saveBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');
        const newTitle = card.querySelector('.edit-title-input').value;
        const newDesc = card.querySelector('.edit-desc-input').value;

        try {
            saveBtn.innerHTML = '⏳'; 
            const response = await fetch(apiUrl, fetchConfig({ action: 'edit', id: patchId, name: newTitle, description: newDesc }));
            const result = await response.json();
            
            if (response.ok) { window.location.reload(); } 
            else { showCustomAlert("Hiba", result.message, "error"); saveBtn.innerHTML = '💾'; }
        } catch (e) { showCustomAlert("Hiba", "Server error!", "error"); saveBtn.innerHTML = '💾'; }
        return; 
    }

    // MODALOK BEZÁRÁSA
    const newModal = document.getElementById('patch-new-modal');
    const recycleModal = document.getElementById('patch-recycle-modal');
    if (event.target.closest('.patch-close-btn') && !event.target.closest('#basesite-alert-close-btn') && !event.target.closest('#basesite-confirm-close-btn')) {
        if(newModal) newModal.classList.add('basesite-hidden'); if(recycleModal) recycleModal.classList.add('basesite-hidden');
        document.body.style.overflow = 'auto';
    }
    if ((event.target === newModal || event.target === recycleModal) && !event.target.closest('#basesite-alert-modal') && !event.target.closest('#basesite-confirm-modal')) {
        event.target.classList.add('basesite-hidden'); document.body.style.overflow = 'auto';
    }

    // ÚJ PATCH NYITÁS
    if (event.target.closest('#patch-new-btn')) {
        newModal.classList.remove('basesite-hidden'); document.body.style.overflow = 'hidden';
        document.getElementById('new-patch-title').value = ''; document.getElementById('new-patch-desc').value = '';
    }

    // LOMTÁR NYITÁS
    if (event.target.closest('#patch-recycle-btn')) {
        recycleModal.classList.remove('basesite-hidden'); document.body.style.overflow = 'hidden';
        const binContent = document.getElementById('recycle-bin-content');
        binContent.innerHTML = '<div class="text-center font-bold text-gray-500 py-10">⏳ Loading...</div>';

        try {
            const response = await fetch(apiUrl, fetchConfig({ action: 'get_deleted' }));
            const result = await response.json();
            if (response.ok && result.data && result.data.length > 0) {
                let html = '<div class="flex flex-col gap-3">';
                result.data.forEach(patch => {
                    html += `<div class="bg-white p-3 border-2 border-gray-400 rounded flex justify-between items-center shadow-sm" data-id="${patch.id}">
                        <div><div class="font-bold text-gray-800">${patch.name}</div><div class="text-xs text-gray-500">${patch.created_at}</div></div>
                        <button class="patch-restore-btn bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded font-bold cursor-pointer">Restore</button>
                    </div>`;
                });
                binContent.innerHTML = html + '</div>';
            } else binContent.innerHTML = '<div class="text-center font-bold text-gray-500 py-10">Lomtár üres! 🍃</div>';
        } catch (e) { binContent.innerHTML = '<div class="text-center text-red-600 py-10">Hiba a betöltéskor!</div>'; }
    }

    // DISCARD
    if (event.target.closest('#patch-discard-btn')) {
        showCustomConfirm("Figyelem", "Biztosan eldobod ezt a piszkozatot?", "danger", () => {
            newModal.classList.add('basesite-hidden'); document.body.style.overflow = 'auto';
        }); return;
    }

    // PUBLISH
    if (event.target.closest('#patch-publish-btn')) {
        const title = document.getElementById('new-patch-title').value; const desc = document.getElementById('new-patch-desc').value;
        if (title.trim() === '' || desc.trim() === '') { showCustomAlert("Figyelem", "Minden mezőt ki kell tölteni!", "error"); return; }
        try {
            const btn = event.target.closest('#patch-publish-btn'); btn.innerHTML = 'Publishing...';
            const response = await fetch(apiUrl, fetchConfig({ action: 'create', name: title, description: desc }));
            if (response.ok) showCustomAlert("Siker", "Patch publikálva!", "success", () => window.location.reload());
            else { showCustomAlert("Hiba", "Hiba publikáláskor!", "error"); btn.innerHTML = 'Publish'; }
        } catch (e) { showCustomAlert("Hiba", "Server error!", "error"); }
    }

    // RESTORE
    if (event.target.closest('.patch-restore-btn')) {
        const btn = event.target.closest('.patch-restore-btn');
        const patchId = btn.closest('[data-id]').getAttribute('data-id');
        try {
            btn.innerHTML = '⏳';
            const response = await fetch(apiUrl, fetchConfig({ action: 'restore', id: patchId }));
            const result = await response.json();
            if (response.ok) showCustomAlert("Siker", "Patch visszaállítva!", "success", () => window.location.reload());
            else { showCustomAlert("Hiba", result.message, "error"); btn.innerHTML = 'Restore'; }
        } catch (e) { showCustomAlert("Hiba", "Server error!", "error"); }
    }
});