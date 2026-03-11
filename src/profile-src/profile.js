// profile.js - A végleges, egyesített változat Avatar mentéssel és Navbar frissítéssel
document.addEventListener('click', async (event) => {
    
    // --- 1. ELEMEK ÉS GOMBOK BEAZONOSÍTÁSA ---
    const btnSettings   = event.target.closest('#profile-settings-button');
    const btnLogout     = event.target.closest('#profile-log-out');
    const btnAvatar     = event.target.closest('#profile-avatar-button');
    const btnAvatarAlt  = event.target.closest('#profile-avatar-button-alt');
    const btnAdminArea = event.target.closest('#profile-admin-button');
    // Modálok lekérése
    const settingsModal = document.getElementById('profile-settings-modal-id');
    const logoutModal   = document.getElementById('profile-logout-modal-id');
    const avatarModal   = document.getElementById('profile-avatar-modal');
    
    // Egyéb interakciók (X gomb, sötét háttér, avatar opciók)
    const isCloseBtn    = event.target.closest('.profile-close-btn');
    const clickedAvatarOption = event.target.closest('.profile-avatar-option');

    // ==========================================
    // 2. SEGÉDFÜGGVÉNYEK (Animációk és állapítások)
    // ==========================================
    
    const profileOpenModal = (modalElement) => {
        if (!modalElement) return;
        const backdrop = modalElement.querySelector('div[id$="-backdrop"], div[id$="-backdrop-id"]');
        const box = modalElement.querySelector('.profile-modal-box');
        
        modalElement.classList.remove('hidden');
        modalElement.classList.add('flex');
        
        // Rövid késleltetés az animációnak
        setTimeout(() => {
            if(backdrop) { backdrop.classList.remove('opacity-0'); backdrop.classList.add('opacity-100'); }
            if(box) { 
                box.classList.remove('opacity-0', 'scale-95', 'translate-y-4'); 
                box.classList.add('opacity-100', 'scale-100', 'translate-y-0'); 
            }
        }, 10);
    };

    const profileCloseModal = (modalElement) => {
        if (!modalElement) return;
        const backdrop = modalElement.querySelector('div[id$="-backdrop"], div[id$="-backdrop-id"]');
        const box = modalElement.querySelector('.profile-modal-box');
        
        if(backdrop) { backdrop.classList.remove('opacity-100'); backdrop.classList.add('opacity-0'); }
        if(box) { 
            box.classList.remove('opacity-100', 'scale-100', 'translate-y-0'); 
            box.classList.add('opacity-0', 'scale-95', 'translate-y-4'); 
        }
        
        setTimeout(() => {
            modalElement.classList.remove('flex');
            modalElement.classList.add('hidden');
        }, 300);
    };

    // ==========================================
    // 3. FŐ LOGIKA (Kattintások lekezelése)
    // ==========================================

    // --- MODÁLOK NYITÁSA ---
    if (btnSettings) { profileOpenModal(settingsModal); return; }
    if (btnAdminArea) {
        window.location.href = '/admin';
        return;
    }
    // Bármelyik avatar gombot (Admin vagy Sima Player) nyomja meg, ugyanaz a modál jön be
    if (btnAvatar || btnAvatarAlt) { 
        profileOpenModal(avatarModal); 
        return; 
    }

    // --- KIJELENTKEZÉS LOGIKA ---
    if (btnLogout) {
        try {
            // Szólunk a backendnek, hogy semmisítse meg a session-t
            const response = await fetch('http://localhost/troxan/app/api.php?path=logout', {
                method: 'POST'
            });

            if (response.ok) {
                // Először megmutatjuk a búcsúzó modált
                profileOpenModal(logoutModal);
                
                // Takarítás a böngésző memóriájában (LocalStorage)
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('username');
                localStorage.removeItem('userAvatar'); // Kijelentkezéskor a képet is töröljük a memóriából!

                // 3 másodperces hatásszünet, utána vissza a főoldalra
                setTimeout(() => {
                    window.location.href = '/'; 
                }, 3000);
            }
        } catch (error) {
            console.error("Hiba a kijelentkezés során:", error);
        }
        return;
    }

    // --- MODÁLOK ZÁRÁSA ---
    // Ha az X-re vagy a modál mögötti sötét háttérre kattintasz
    if (isCloseBtn || (event.target.id && event.target.id.includes('backdrop'))) {
        const currentModal = event.target.closest('.fixed.inset-0');
        profileCloseModal(currentModal);
        return;
    }

    // --- AVATAR KIVÁLASZTÁSA ÉS MENTÉSE ---
    if (clickedAvatarOption) {
        const mainAvatarImg = document.getElementById('profile-avatar');
        if (mainAvatarImg) {
            // 1. Kicseréljük a profilképet a választottra a képernyőn (azonnali visszajelzés a profilban)
            mainAvatarImg.src = clickedAvatarOption.src;
            
            // ------ ÚJ RÉSZ A MENÜHÖZ ------
            // Elmentjük a böngészőbe a választott képet
            localStorage.setItem('userAvatar', clickedAvatarOption.src);
            // Azonnal kicseréljük a fenti menüben is a képet, anélkül hogy frissíteni kéne az oldalt!
            const navAvatar = document.querySelector('.nav-avatar');
            if (navAvatar) navAvatar.src = clickedAvatarOption.src;
            // -------------------------------
            
            // 2. Kinyerjük a választott kép ID-ját
            const selectedAvatarId = clickedAvatarOption.getAttribute('data-avatar-id');
            
            // Bezárjuk az ablakot
            profileCloseModal(avatarModal);
            
            // 3. Szólunk a Backendnek, hogy mentse el az adatbázisba POST kéréssel!
            try {
                fetch('http://localhost/troxan/app/api.php?path=profile', {
                    method: 'POST',
                    credentials: 'include', // KÖTELEZŐ, hogy tudja a PHP, ki vagy!
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    // Elküldjük a kiválasztott kép ID-ját!
                    body: JSON.stringify({ avatar_id: selectedAvatarId })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        console.log("✅ Avatar ID sikeresen elmentve az adatbázisba:", selectedAvatarId);
                    } else {
                        console.error("❌ Hiba az avatar mentésekor:", result.message);
                    }
                });
            } catch (error) {
                console.error("Hálózati hiba az avatar mentésekor:", error);
            }
        }
        return;
    }
});