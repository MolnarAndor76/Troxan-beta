//URL
const profileUrl = `/app/api.php?path=profile`;
const logoutUrl = `/app/api.php?path=logout`;

// Globális változók a mi kulturált pop-upjainkhoz
window.alertCallback = null;
window.promptCallback = null;

// ==========================================
// KULTURÁLT POP-UP FÜGGVÉNYEK (Globálisak)
// ==========================================
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
    if (type === 'error') titleEl.className = 'profile-popup-title profile-popup-title-error';
    else if (type === 'success') titleEl.className = 'profile-popup-title profile-popup-title-success';
    else titleEl.className = 'profile-popup-title';

    window.alertCallback = callback;
    
    // Kőkemény biztosíték, hogy garantáltan megjelenjen!
    modal.classList.remove('profile-hidden');
    modal.classList.add('profile-flex'); 
}

function showCustomPrompt(title, placeholder, onConfirm) {
    const modal = document.getElementById('basesite-prompt-modal');
    if (!modal) return;
    
    document.getElementById('basesite-prompt-title').innerText = title;
    const inputEl = document.getElementById('basesite-prompt-input');
    inputEl.placeholder = placeholder;
    inputEl.value = ''; 
    
    window.promptCallback = onConfirm;
    
    modal.classList.remove('profile-hidden');
    modal.classList.add('profile-flex');
    inputEl.focus();
}

// profile.js - A végleges, egyesített változat Avatar mentéssel és Navbar frissítéssel
document.addEventListener('click', async (event) => {
    
    // Ha a My Maps gombra kattintanak a profilban
    const myMapsBtn = event.target.closest('#profile-my-maps');
    if (myMapsBtn) {
        event.preventDefault(); // Védelem a fantom frissítés ellen!
        window.location.href = '/my_maps';
        return;
    }

    // --- 1. ELEMEK ÉS GOMBOK BEAZONOSÍTÁSA ---
    const btnSettings   = event.target.closest('#profile-settings-button');
    const btnLogout     = event.target.closest('#profile-log-out');
    const btnAvatar     = event.target.closest('#profile-avatar-button');
    const btnAvatarAlt  = event.target.closest('#profile-avatar-button-alt');
    const btnAdminArea  = event.target.closest('#profile-admin-button');
    
    // Modálok lekérése
    const settingsModal = document.getElementById('profile-settings-modal-id');
    const logoutModal   = document.getElementById('profile-logout-modal-id');
    const avatarModal   = document.getElementById('profile-avatar-modal');
    
    // Egyéb interakciók
    const isCloseBtn    = event.target.closest('.profile-close-btn');
    const clickedAvatarOption = event.target.closest('.profile-avatar-option');

    // ==========================================
    // 2. SEGÉDFÜGGVÉNYEK (Animációk és állapítások)
    // ==========================================
    
    const profileOpenModal = (modalElement) => {
        if (!modalElement) return;
        const backdrop = modalElement.querySelector('div[id$="-backdrop"], div[id$="-backdrop-id"]');
        const box = modalElement.querySelector('.profile-modal-box');
        
        modalElement.classList.remove('profile-hidden');
        modalElement.classList.add('profile-flex');
        
        setTimeout(() => {
            if(backdrop) { backdrop.classList.remove('profile-backdrop-hidden'); backdrop.classList.add('profile-backdrop-visible'); }
            if(box) { 
                box.classList.remove('profile-modal-box-hidden'); 
                box.classList.add('profile-modal-box-visible'); 
            }
        }, 10);
    };

    const profileCloseModal = (modalElement) => {
        if (!modalElement) return;
        const backdrop = modalElement.querySelector('div[id$="-backdrop"], div[id$="-backdrop-id"]');
        const box = modalElement.querySelector('.profile-modal-box');
        
        if(backdrop) { backdrop.classList.remove('profile-backdrop-visible'); backdrop.classList.add('profile-backdrop-hidden'); }
        if(box) { 
            box.classList.remove('profile-modal-box-visible'); 
            box.classList.add('profile-modal-box-hidden'); 
        }
        
        setTimeout(() => {
            modalElement.classList.remove('profile-flex');
            modalElement.classList.add('profile-hidden');
        }, 300);
    };

    // ==========================================
    // KULTURÁLT POP-UP GOMBOK
    // ==========================================
    
    if (event.target.closest('#basesite-alert-ok-btn') || event.target.id === 'basesite-alert-backdrop') {
        event.preventDefault(); 
        const alertModal = document.getElementById('basesite-alert-modal');
        if (!alertModal.classList.contains('profile-hidden')) {
            alertModal.classList.add('profile-hidden');
            alertModal.classList.remove('profile-flex');
            if (window.alertCallback) { 
                window.alertCallback(); 
                window.alertCallback = null; 
            }
        }
        return;
    }

    if (event.target.closest('#basesite-prompt-cancel-btn')) {
        event.preventDefault(); 
        const promptModal = document.getElementById('basesite-prompt-modal');
        promptModal.classList.add('profile-hidden');
        promptModal.classList.remove('profile-flex');
        window.promptCallback = null;
        return;
    }

    if (event.target.closest('#basesite-prompt-ok-btn')) {
        event.preventDefault(); 
        const inputVal = document.getElementById('basesite-prompt-input').value;
        const promptModal = document.getElementById('basesite-prompt-modal');
        promptModal.classList.add('profile-hidden');
        promptModal.classList.remove('profile-flex');
        if (window.promptCallback) {
            window.promptCallback(inputVal);
            window.promptCallback = null;
        }
        return;
    }

    // ==========================================
    // PASSWORD MODAL GOMBOK (BELSŐ HIBAÜZENETTEL!)
    // ==========================================

    if (event.target.closest('#basesite-password-cancel-btn') || event.target.id === 'basesite-password-backdrop') {
        event.preventDefault();
        const passModal = document.getElementById('basesite-password-modal');
        passModal.classList.add('profile-hidden');
        passModal.classList.remove('profile-flex');
        return;
    }

    if (event.target.closest('#basesite-password-save-btn')) {
        event.preventDefault();
        
        const oldPass = document.getElementById('pass-old').value;
        const newPass = document.getElementById('pass-new').value;
        const confirmPass = document.getElementById('pass-confirm').value;
        const errorMsg = document.getElementById('password-error-msg');

        const showPassError = (msg) => {
            errorMsg.innerText = msg;
            errorMsg.classList.remove('profile-hidden');
        };

        // Csak azt ellenőrizzük, hogy üres-e, a prioritásos vizsgálatot rábízzuk a PHP-ra!
        if (!oldPass || !newPass || !confirmPass) {
            showPassError("Please fill in all fields!");
            return;
        }

        // Küldjük a szervernek!
        fetch(profileUrl, {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'change_password', old_password: oldPass, new_password: newPass, confirm_password: confirmPass })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // SIKER: Zárjuk az ablakot, és szó nélkül kilépünk a 3..2..1-es modal segítségével!
                const passModal = document.getElementById('basesite-password-modal');
                passModal.classList.add('profile-hidden');
                passModal.classList.remove('profile-flex');
                
                document.getElementById('profile-log-out').click(); 
            } else {
                // HIBA: Kiírjuk pirossal a PHP üzenetét a prioritási sorrendnek megfelelően!
                showPassError(data.message);
            }
        })
        .catch(err => {
            showPassError("Failed to connect to the server.");
        });
        
        return;
    }

    // ==========================================
    // SETTINGS: NÉVVÁLTÁS ÉS JELSZÓ (Aktiválás)
    // ==========================================
    
    if (event.target.closest('#btn-change-username')) {
        event.preventDefault(); 
        const currentSettingsModal = event.target.closest('.profile-modal-overlay');
        profileCloseModal(currentSettingsModal);
        
        showCustomPrompt("Change Username", "4-12 characters", (newName) => {
            if (newName && newName.trim() !== '') {
                fetch(profileUrl, {
                    method: 'PATCH', credentials: 'include', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'change_username', new_username: newName.trim() })
                })
                .then(res => res.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            localStorage.setItem('username', newName.trim());
                            showCustomAlert("Success", data.message, "success", () => location.reload());
                        } else {
                            showCustomAlert("Error", data.message, "error");
                        }
                    } catch (e) {
                        showCustomAlert("System Error", "The server returned an invalid format.", "error");
                    }
                })
                .catch(err => {
                    showCustomAlert("Network Error", "Failed to connect to the server.", "error");
                });
            }
        });
        return;
    }

    if (event.target.closest('#btn-change-password')) {
        event.preventDefault(); 
        const currentSettingsModal = event.target.closest('.profile-modal-overlay');
        profileCloseModal(currentSettingsModal);
        
        const passModal = document.getElementById('basesite-password-modal');
        if(passModal) {
            document.getElementById('pass-old').value = '';
            document.getElementById('pass-new').value = '';
            document.getElementById('pass-confirm').value = '';
            
            // ELTÜNTETJÜK A RÉGI PIROS SZÖVEGET KINYITÁSKOR!
            document.getElementById('password-error-msg').classList.add('profile-hidden');
            document.getElementById('password-error-msg').innerText = '';
            
            passModal.classList.remove('profile-hidden');
            passModal.classList.add('profile-flex');
            document.getElementById('pass-old').focus();
        }
        return;
    }

    // ==========================================
    // 3. FŐ LOGIKA (Kattintások lekezelése)
    // ==========================================

    if (btnSettings) { event.preventDefault(); profileOpenModal(settingsModal); return; }
    if (btnAdminArea) {
        event.preventDefault();
        window.location.href = '/admin';
        return;
    }
    if (btnAvatar || btnAvatarAlt) { 
        event.preventDefault();
        profileOpenModal(avatarModal); 
        return; 
    }

    // --- KIJELENTKEZÉS LOGIKA (VISSZASZÁMLÁLÓVAL) ---
    if (btnLogout) {
        event.preventDefault();
        try {
            const response = await fetch(logoutUrl, { method: 'POST' });

            if (response.ok) {
                profileOpenModal(logoutModal);
                
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('username');
                localStorage.removeItem('userAvatar'); 

                const logoutMessageEl = logoutModal.querySelector('h3');
                if (logoutMessageEl) {
                    let timeLeft = 3;
                    logoutMessageEl.innerHTML = `You have been logged out!<br>The site will refresh in <span class="profile-countdown-text">${timeLeft}</span>...`;
                    
                    const countdown = setInterval(() => {
                        timeLeft--;
                        if (timeLeft > 0) {
                            logoutMessageEl.innerHTML = `You have been logged out!<br>The site will refresh in <span class="profile-countdown-text">${timeLeft}</span>...`;
                        } else {
                            clearInterval(countdown);
                            window.location.href = '/login';
                        }
                    }, 1000);
                } else {
                    setTimeout(() => { window.location.href = '/login'; }, 3000);
                }
            }
        } catch {
            // Logout error handled silently
        }
        return;
    }

    if (isCloseBtn || (event.target.id && event.target.id.includes('backdrop'))) {
        event.preventDefault();
        const currentModal = event.target.closest('.profile-modal-overlay');
        profileCloseModal(currentModal);
        return;
    }

    if (clickedAvatarOption) {
        event.preventDefault();
        const mainAvatarImg = document.getElementById('profile-avatar');
        if (mainAvatarImg) {
            mainAvatarImg.src = clickedAvatarOption.src;
            localStorage.setItem('userAvatar', clickedAvatarOption.src);
            const navAvatar = document.querySelector('.nav-avatar');
            if (navAvatar) navAvatar.src = clickedAvatarOption.src;
            
            const selectedAvatarId = clickedAvatarOption.getAttribute('data-avatar-id');
            profileCloseModal(avatarModal);
            
            try {
                fetch(profileUrl, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'change_avatar', avatar_id: selectedAvatarId })
                });
            } catch {
            }
        }
        return;
    }
});