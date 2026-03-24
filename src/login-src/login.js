//url
const loginUrl = `${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=login`;

import { updateHeader } from '../main.js';

// ====== FORGOT PASSWORD MODAL NYITÁS/ZÁRÁS ======
document.addEventListener('click', (event) => {
    const forgotModal = document.getElementById('forgot-pw-modal');
    if (!forgotModal) return;

    // 1. Kinyitás: a "Forgot password?" gombra kattintva
    if (event.target.closest('[data-target="forgot-pw-view"]')) {
        event.preventDefault();
        forgotModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Ne görögjön a háttér
    }

    // 2. Bezárás: X gombra VAGY a sötét háttérre kattintva
    if (event.target.closest('#close-forgot-btn') || event.target === forgotModal) {
        forgotModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('forgot-pw-error').innerHTML = ''; // Kitöröljük a hibaüzenetet bezáráskor
        document.getElementById('forgot-pw-form').reset(); // Kiürítjük az inputot
    }
});

// ====== ŰRLAPOK BEKÜLDÉSE (Esemény delegáció) ======
document.addEventListener('submit', async (event) => {
    
    // --- 1. SIMA BEJELENTKEZÉS LOGIKA ---
    if (event.target.id === 'login-form') {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            
            const response = await fetch(loginUrl, {
                method: 'POST',
                credentials: 'include', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                localStorage.setItem('isLoggedIn', 'true');
                if (result.user && result.user.username) {
                    localStorage.setItem('username', result.user.username);
                }
                if (result.user && result.user.avatar) {
                    localStorage.setItem('userAvatar', result.user.avatar);
                }

                updateHeader();
                alert("🎉 SIKER! Üdv a fedélzeten!");
                window.location.href = '/profile';

            } else {
                alert("❌ HOPPÁ: " + result.message);
                const errorDiv = document.getElementById('login-error');
                if(errorDiv) errorDiv.innerText = result.message;
            }
        } catch (error) {
            console.error('Hiba:', error);
            alert('❌ Váratlan hiba történt.');
        }
    }

    // --- 2. FORGOT PASSWORD LOGIKA ---
    if (event.target.id === 'forgot-pw-form') {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Hozzáadjuk a flag-et, hogy a PHP tudja mit akarunk
        data.action = 'forgot_password'; 
        
        const errorDiv = document.getElementById('forgot-pw-error');
        errorDiv.innerHTML = 'Loading...'; // Látványos várakozás
        errorDiv.classList.replace('text-red-600', 'text-gray-600');

        try {
            const response = await fetch(loginUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Siker esetén ZÖLD színnel írjuk ki az innerHTML-be
                errorDiv.classList.replace('text-gray-600', 'text-green-600');
                errorDiv.innerHTML = '✔ ' + result.message;
            } else {
                // Hiba esetén (pl. Invalid email) PIROS színnel írjuk ki, alert NÉLKÜL!
                errorDiv.classList.replace('text-gray-600', 'text-red-600');
                errorDiv.innerHTML = result.message; 
            }
        } catch (error) {
            console.error('Hiba:', error);
            errorDiv.classList.replace('text-gray-600', 'text-red-600');
            errorDiv.innerHTML = "Server connection error!";
        }
    }
});