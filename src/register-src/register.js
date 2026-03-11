// Golyóálló Event Delegation a form küldéséhez
document.addEventListener('submit', async (event) => {

    // Csak a regisztrációs formra figyelünk
    if (event.target.id === 'register-form') {
        event.preventDefault(); // Megakadályozzuk az oldal újratöltését

        const form = event.target;
        const formData = new FormData(form);

        // Átalakítjuk a form adatait JSON formátumra
        const data = Object.fromEntries(formData.entries());

        try {
            // Elküldjük a POST kérést az API-nak
            // FIGYELEM: Írd át az URL-t arra, ami nálad a registrationController-re mutat!
            // A sima '/api/registration' helyett a teljes XAMPP útvonal kell:
            const data = Object.fromEntries(formData.entries());

            console.log("🚀 KÜLDÖTT ADATOK:", data);

            const response = await fetch('http://localhost/troxan/app/api.php?path=registration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Sikeres regisztráció! 
                alert("🎉 " + result.message);

                // Itt meghívhatod azt a JS függvényedet, ami átvált a Login nézetre!
                // pl: loadView('login-view'); 

            } else {
                // Hiba történt (pl. foglalt név, nem egyező jelszó)
                alert("❌ Hiba: " + result.message);
            }

        } catch (error) {
            console.error('Hiba a regisztráció során:', error);
            alert('❌ Váratlan hiba történt a szerverrel való kommunikáció során.');
        }
    }
});