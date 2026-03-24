//URL
const registerUrl = `${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=registration`;

// ====== MODAL (Terms and Conditions) LOGIKA ======
document.addEventListener('click', (event) => {
    const termsModal = document.getElementById('terms-modal');
    if (!termsModal) return;

    // 1. Kinyitás: Ha a terms gombra kattintanak
    if (event.target.closest('.register-terms-btn')) {
        event.preventDefault(); // Megakadályozzuk, hogy a checkboxot is bepipálja véletlenül
        termsModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Letiltjuk az oldal görgetését
    }

    // 2. Bezárás: X gombra, "Understood" gombra, VAGY a sötét háttérre kattintva
    if (event.target.closest('#close-terms-btn') || 
        event.target.closest('#accept-terms-btn') || 
        event.target === termsModal) {
        
        termsModal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // Visszaadjuk a görgetést
    }
});

// ====== FORM BEKÜLDÉS LOGIKA ======
document.addEventListener('submit', async (event) => {

    if (event.target.id === 'register-form') {
        event.preventDefault(); 

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Extra Front-end Validáció (Hogy már beküldés előtt is szóljunk, ha valami nem jó)
        if (data.username.length > 16 || !/^[a-zA-Z0-9]+$/.test(data.username)) {
            alert("❌ Username must be alphanumeric and max 16 characters!");
            return;
        }
        if (data.password.length < 8) {
            alert("❌ Password must be at least 8 characters long!");
            return;
        }

        try {
            const response = await fetch(registerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // SIKERES REGISZTRÁCIÓ!
                // Itt jön az angol értesítés, amit kértél:
                alert("Please confirm your email address before logging in.");

                // Átirányítás a login oldalra:
                window.location.href = '/login'; 
            } else {
                // Hiba történt (pl. foglalt név)
                alert("❌ Hiba: " + result.message);
            }

        } catch (error) {
            console.error('Hiba a regisztráció során:', error);
            alert('❌ Váratlan hiba történt a szerverrel való kommunikáció során.');
        }
    }
});