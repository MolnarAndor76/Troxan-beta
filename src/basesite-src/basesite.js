// ====== UNIVERZÁLIS CUSTOM ALERT & CONFIRM LOGIKA ======
let alertCallback = null;
let confirmCallback = null;
let patchActionInProgress = false;
let activeEditPatchId = null;
let siteSettingsActionInProgress = false;
let siteSettingsEditMode = false;

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
    modal.classList.remove('basesite-hidden', 'hidden');
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
    modal.classList.remove('basesite-hidden', 'hidden');
}

document.addEventListener('click', (event) => {
    if (event.target.closest('#basesite-alert-close-btn') || event.target.closest('#basesite-alert-ok-btn') || event.target.id === 'basesite-alert-modal') {
        const alertModal = document.getElementById('basesite-alert-modal');
        if (alertModal && !alertModal.classList.contains('basesite-hidden')) {
            alertModal.classList.add('basesite-hidden', 'hidden');
            if (alertCallback) { alertCallback(); alertCallback = null; }
        }
    }
    if (event.target.closest('#basesite-confirm-close-btn') || event.target.closest('#basesite-confirm-cancel-btn') || event.target.id === 'basesite-confirm-modal') {
        const confirmModal = document.getElementById('basesite-confirm-modal');
        if (confirmModal && !confirmModal.classList.contains('basesite-hidden')) {
            confirmModal.classList.add('basesite-hidden', 'hidden');
            confirmCallback = null;
        }
    }
    if (event.target.closest('#basesite-confirm-ok-btn')) {
        const confirmModal = document.getElementById('basesite-confirm-modal');
        if (confirmModal && !confirmModal.classList.contains('basesite-hidden')) {
            confirmModal.classList.add('basesite-hidden', 'hidden');
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
    return;
  }

  const downloadUrl = (downloadBtn.getAttribute('data-download-url') || '').trim();
  if (!downloadUrl) {
    showCustomAlert('Error', 'Download URL is missing. Ask an Engineer to update it.', 'error');
    return;
  }

  window.location.href = downloadUrl;
});

function isLikelyValidSiteUrl(value) {
    const url = (value || '').trim();
    if (!url) return false;
    if (url.startsWith('/') && !url.startsWith('//')) return true;

    try {
        const parsed = new URL(url);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

function toEditableAboutText(rawHtml) {
    return rawHtml
        .replace(/<br\s*[\/]?>/gi, '\n')
        .replace(/&nbsp;/g, ' ')
        .trim();
}

// ====== MAIN PAGE SETTINGS (ENGINEER) ======
document.addEventListener('click', async (event) => {
    const editBtn = event.target.closest('#site-settings-edit-btn');
    const saveBtn = event.target.closest('#site-settings-save-btn');

    if (!editBtn && !saveBtn) {
        return;
    }

    if (siteSettingsActionInProgress) {
        showCustomAlert('Please wait', 'Settings update is already in progress.', 'info');
        return;
    }

    const aboutTextEl = document.getElementById('basesite-about-us-text');
    const specialThanksList = document.getElementById('basesite-special-thanks-list');
    const systemReqSource = document.getElementById('site-settings-system-req-source');
    const loreSource = document.getElementById('site-settings-lore-source');
    const downloadBtn = document.getElementById('basesite-download-game-btn');
    const trailerIframe = document.getElementById('basesite-trailer-iframe');

    if (!aboutTextEl || !downloadBtn || !trailerIframe) {
        showCustomAlert('Error', 'Required elements are missing from page.', 'error');
        return;
    }

    if (editBtn) {
        if (siteSettingsEditMode) {
            return;
        }

        const currentDownloadUrl = (downloadBtn.getAttribute('data-download-url') || '').trim();
        const currentTrailerUrl = (trailerIframe.getAttribute('src') || '').trim();
        const currentAboutText = toEditableAboutText(aboutTextEl.innerHTML);
        const currentSpecialThanks = specialThanksList
            ? Array.from(specialThanksList.querySelectorAll('li')).map(li => li.innerText.trim()).join('\n')
            : '';
        const currentSystemRequirements = systemReqSource ? systemReqSource.value : '';
        const currentLoreText = loreSource ? loreSource.value : '';

        const editorHtml = `
            <div id="site-settings-editor" class="bg-orange-100 border-2 border-orange-900 rounded p-4 mb-4">
                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-download-url">Download URL</label>
                <input id="site-settings-download-url" type="text" class="w-full bg-white border-2 border-orange-950 p-2 rounded text-gray-900 mb-3" value="${currentDownloadUrl.replace(/"/g, '&quot;')}">

                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-trailer-url">Trailer URL</label>
                <input id="site-settings-trailer-url" type="text" class="w-full bg-white border-2 border-orange-950 p-2 rounded text-gray-900 mb-3" value="${currentTrailerUrl.replace(/"/g, '&quot;')}">

                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-about-us">About us text</label>
                <textarea id="site-settings-about-us" class="w-full h-24 bg-white border-2 border-orange-950 p-2 rounded text-gray-900 mb-3">${currentAboutText}</textarea>

                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-special-thanks">Special thanks (one line = one entry)</label>
                <textarea id="site-settings-special-thanks" class="w-full h-24 bg-white border-2 border-orange-950 p-2 rounded text-gray-900 mb-3">${currentSpecialThanks}</textarea>

                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-system-req">System requirements (one line: Component|Minimum|Recommended)</label>
                <textarea id="site-settings-system-req" class="w-full h-28 bg-white border-2 border-orange-950 p-2 rounded text-gray-900 mb-3">${currentSystemRequirements}</textarea>

                <label class="block text-sm font-bold text-orange-950 mb-1" for="site-settings-lore">Lore text</label>
                <textarea id="site-settings-lore" class="w-full h-40 bg-white border-2 border-orange-950 p-2 rounded text-gray-900">${currentLoreText}</textarea>
            </div>
        `;

        aboutTextEl.insertAdjacentHTML('beforebegin', editorHtml);
        aboutTextEl.classList.add('basesite-hidden');
        if (specialThanksList) specialThanksList.closest('.basesite-about-box').classList.add('basesite-hidden');

        editBtn.id = 'site-settings-save-btn';
        editBtn.textContent = '💾';
        editBtn.title = 'Save Main Page Settings';
        siteSettingsEditMode = true;
        return;
    }

    if (!siteSettingsEditMode || !saveBtn) {
        return;
    }

    const downloadInput = document.getElementById('site-settings-download-url');
    const trailerInput = document.getElementById('site-settings-trailer-url');
    const aboutInput = document.getElementById('site-settings-about-us');
    const specialThanksInput = document.getElementById('site-settings-special-thanks');
    const systemReqInput = document.getElementById('site-settings-system-req');
    const loreInput = document.getElementById('site-settings-lore');

    if (!downloadInput || !trailerInput || !aboutInput) {
        showCustomAlert('Error', 'Editor fields are missing.', 'error');
        return;
    }

    const downloadUrl = downloadInput.value.trim();
    const trailerUrl = trailerInput.value.trim();
    const aboutUsText = aboutInput.value.trim();
    const specialThanksText = specialThanksInput ? specialThanksInput.value : '';
    const systemRequirementsText = systemReqInput ? systemReqInput.value.trim() : '';
    const loreText = loreInput ? loreInput.value.trim() : '';

    if (!isLikelyValidSiteUrl(downloadUrl)) {
        showCustomAlert('Validation error', 'Download URL is invalid.', 'error');
        return;
    }

    if (!isLikelyValidSiteUrl(trailerUrl)) {
        showCustomAlert('Validation error', 'Trailer URL is invalid.', 'error');
        return;
    }

    if (!aboutUsText) {
        showCustomAlert('Validation error', 'About us text cannot be empty.', 'error');
        return;
    }

    if (!systemRequirementsText) {
        showCustomAlert('Validation error', 'System requirements cannot be empty.', 'error');
        return;
    }

    if (!loreText) {
        showCustomAlert('Validation error', 'Lore text cannot be empty.', 'error');
        return;
    }

    try {
        siteSettingsActionInProgress = true;
        saveBtn.textContent = '⏳';

        const response = await fetch('/app/api.php?path=main', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_site_settings',
                download_url: downloadUrl,
                trailer_url: trailerUrl,
                about_us_text: aboutUsText,
                special_thanks_text: specialThanksText,
                system_requirements_text: systemRequirementsText,
                lore_text: loreText
            })
        });

        const result = await response.json();
        if (response.ok) {
            window.location.reload();
        } else {
            showCustomAlert('Error', result.message || 'Could not save main page settings.', 'error');
            saveBtn.textContent = '💾';
            siteSettingsActionInProgress = false;
        }
    } catch (e) {
        showCustomAlert('Error', 'Server error while saving settings.', 'error');
        saveBtn.textContent = '💾';
        siteSettingsActionInProgress = false;
    }
});

// ====== PATCH NOTES ADMIN LOGIKA ======
document.addEventListener('click', async (event) => {
    const fetchConfig = (bodyData) => ({
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(bodyData)
    });
    const apiUrl = '/app/api.php?path=main';

    const patchActionTarget = event.target.closest('.patch-lock-btn, .patch-delete-btn, .patch-edit-btn, .patch-save-btn, .patch-restore-btn, #patch-publish-btn, #patch-discard-btn');
    if (patchActionInProgress && patchActionTarget) {
        showCustomAlert('Please wait', 'Another patch action is in progress. Wait for completion or page refresh.', 'info');
        return;
    }

    if (activeEditPatchId && patchActionTarget && !event.target.closest('.patch-save-btn')) {
        showCustomAlert('Edit in progress', 'Save the currently edited patch first. After saving, the page will refresh.', 'info');
        return;
    }

    // --- 0. LAKAT (Toggle Lock - Csak Engineer) ---
    const lockBtn = event.target.closest('.patch-lock-btn');
    if (lockBtn) {
        const card = lockBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');

        try {
            patchActionInProgress = true;
            lockBtn.innerHTML = '⏳';
            const response = await fetch(apiUrl, fetchConfig({ action: 'toggle_lock', id: patchId }));
            const result = await response.json();
            
            if (response.ok) {
                showCustomAlert("Success", result.message, "success", () => { window.location.reload(); });
            } else { 
                showCustomAlert("Error", result.message, "error"); 
                lockBtn.innerHTML = '🔒'; 
                patchActionInProgress = false;
            }
        } catch (e) { 
            showCustomAlert("Error", "Server error!", "error"); 
            lockBtn.innerHTML = '🔒'; 
            patchActionInProgress = false;
        }
        return;
    }

    // 1. KUKA (Delete)
    const deleteBtn = event.target.closest('.patch-delete-btn');
    if (deleteBtn) {
        const card = deleteBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');
        showCustomConfirm("Confirm deletion", "Are you sure you want to move this patch to recycle bin?", "danger", async () => {
            try {
                patchActionInProgress = true;
                const response = await fetch(apiUrl, fetchConfig({ action: 'delete', id: patchId }));
                const result = await response.json();
                if (response.ok) {
                    showCustomAlert("Success", result.message || "Patch moved to recycle bin.", "success", () => window.location.reload());
                } else {
                    showCustomAlert("Error", result.message, "error");
                    patchActionInProgress = false;
                }
            } catch (e) {
                showCustomAlert("Error", "Server connection error!", "error");
                patchActionInProgress = false;
            }
        });
        return; 
    }

    // 2. CERUZA (Edit mód)
    const editBtn = event.target.closest('.patch-edit-btn');
    if (editBtn) {
        const card = editBtn.closest('[data-id]');
        const patchId = card.getAttribute('data-id');
        const titleEl = card.querySelector('.patch-title');
        const descEl = card.querySelector('.patch-desc');
        const currentTitle = titleEl.innerText;
        const currentDesc = descEl.innerHTML.replace(/<br\s*[\/]?>/gi, '\n').trim();

        titleEl.innerHTML = `<input type="text" class="edit-title-input w-full bg-white border-2 border-orange-950 p-1 text-orange-950 rounded" value="${currentTitle}">`;
        descEl.innerHTML = `<textarea class="edit-desc-input w-full h-32 bg-white border-2 border-orange-950 p-2 text-gray-800 rounded mt-2">${currentDesc}</textarea>`;

        editBtn.innerHTML = '💾'; editBtn.title = "Save Changes"; editBtn.classList.replace('patch-edit-btn', 'patch-save-btn');
        activeEditPatchId = patchId;
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
            patchActionInProgress = true;
            saveBtn.innerHTML = '⏳'; 
            const response = await fetch(apiUrl, fetchConfig({ action: 'edit', id: patchId, name: newTitle, description: newDesc }));
            const result = await response.json();
            
            if (response.ok) { window.location.reload(); } 
            else {
                showCustomAlert("Error", result.message, "error");
                saveBtn.innerHTML = '💾';
                patchActionInProgress = false;
            }
        } catch (e) {
            showCustomAlert("Error", "Server error!", "error");
            saveBtn.innerHTML = '💾';
            patchActionInProgress = false;
        }
        return; 
    }

    // MODALOK BEZÁRÁSA
    const newModal = document.getElementById('patch-new-modal');
    const recycleModal = document.getElementById('patch-recycle-modal');
    if (event.target.closest('.patch-close-btn') && !event.target.closest('#basesite-alert-close-btn') && !event.target.closest('#basesite-confirm-close-btn')) {
        if(newModal) newModal.classList.add('basesite-hidden', 'hidden'); if(recycleModal) recycleModal.classList.add('basesite-hidden', 'hidden');
        document.body.style.overflow = 'auto';
    }
    if ((event.target === newModal || event.target === recycleModal) && !event.target.closest('#basesite-alert-modal') && !event.target.closest('#basesite-confirm-modal')) {
        event.target.classList.add('basesite-hidden', 'hidden'); document.body.style.overflow = 'auto';
    }

    // ÚJ PATCH NYITÁS
    if (event.target.closest('#patch-new-btn')) {
        newModal.classList.remove('basesite-hidden', 'hidden'); document.body.style.overflow = 'hidden';
        document.getElementById('new-patch-title').value = ''; document.getElementById('new-patch-desc').value = '';
    }

    // LOMTÁR NYITÁS
    if (event.target.closest('#patch-recycle-btn')) {
        recycleModal.classList.remove('basesite-hidden', 'hidden'); document.body.style.overflow = 'hidden';
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
            } else binContent.innerHTML = '<div class="text-center font-bold text-gray-500 py-10">Recycle bin is empty! 🍃</div>';
        } catch (e) { binContent.innerHTML = '<div class="text-center text-red-600 py-10">Error loading content!</div>'; }
    }

    // DISCARD
    if (event.target.closest('#patch-discard-btn')) {
        showCustomConfirm("Attention", "Are you sure you want to discard this draft?", "danger", () => {
            newModal.classList.add('basesite-hidden'); document.body.style.overflow = 'auto';
        }); return;
    }

    // PUBLISH
    if (event.target.closest('#patch-publish-btn')) {
        const title = document.getElementById('new-patch-title').value; const desc = document.getElementById('new-patch-desc').value;
        if (title.trim() === '' || desc.trim() === '') { showCustomAlert("Attention", "All fields are required!", "error"); return; }
        try {
            patchActionInProgress = true;
            const btn = event.target.closest('#patch-publish-btn'); btn.innerHTML = 'Publishing...';
            const response = await fetch(apiUrl, fetchConfig({ action: 'create', name: title, description: desc }));
            if (response.ok) showCustomAlert("Success", "Patch published!", "success", () => window.location.reload());
            else { showCustomAlert("Error", "Error publishing patch!", "error"); btn.innerHTML = 'Publish'; patchActionInProgress = false; }
        } catch (e) { showCustomAlert("Error", "Server error!", "error"); patchActionInProgress = false; }
    }

    // RESTORE
    if (event.target.closest('.patch-restore-btn')) {
        const btn = event.target.closest('.patch-restore-btn');
        const patchId = btn.closest('[data-id]').getAttribute('data-id');
        try {
            patchActionInProgress = true;
            btn.innerHTML = '⏳';
            const response = await fetch(apiUrl, fetchConfig({ action: 'restore', id: patchId }));
            const result = await response.json();
            if (response.ok) showCustomAlert("Success", "Patch restored!", "success", () => window.location.reload());
            else { showCustomAlert("Error", result.message, "error"); btn.innerHTML = 'Restore'; patchActionInProgress = false; }
        } catch (e) { showCustomAlert("Error", "Server error!", "error"); patchActionInProgress = false; }
    }
});