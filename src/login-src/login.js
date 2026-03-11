import { updateHeader } from '../main.js';

document.addEventListener('submit', async (event) => {
    if (event.target.id === 'login-form') {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('http://localhost/troxan/app/api.php?path=login', {
                method: 'POST',
                credentials: 'include', // KÖTELEZŐ, HOGY MENTSE A SÜTIT!
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                localStorage.setItem('isLoggedIn', 'true');
                if (result.user && result.user.username) {
                    localStorage.setItem('username', result.user.username);
                }
                
                // --- ÚJDONSÁG: Elmentjük a backendtől kapott képet is! ---
                if (result.user && result.user.avatar) {
                    localStorage.setItem('userAvatar', result.user.avatar);
                }
                // ---------------------------------------------------------

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
});