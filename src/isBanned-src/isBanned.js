//url
const logoutUrl = `${window.location.protocol}//${window.location.hostname}/troxan/app/api.php?path=logout`;

document.addEventListener('click', (event) => {

    const bannedLogoutBtn = event.target.closest('#isBanned-logout-btn');

    if (bannedLogoutBtn) {
        fetch(logoutUrl, {
            method: 'POST',
            credentials: 'include'
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    localStorage.clear(); // Mindent takarítsunk ki!
                    // FIX: Ne fix URL, csak a gyökérkönyvtár!
                    window.location.href = '/';
                } else {
                    alert("Hiba: " + data.message);
                }
            })
            .catch(err => {
                console.error('Logout error:', err);
                // Hiba esetén is takarítsunk ki és dobjuk ki a júzert
                localStorage.clear();
                
                // ITT A CSERE: A localhost helyett dinamikusan a gyökérre dobjuk!
                window.location.href = '/'; 
            });

        return;
    }
});