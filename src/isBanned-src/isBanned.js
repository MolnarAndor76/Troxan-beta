document.addEventListener('click', (event) => {

    const bannedLogoutBtn = event.target.closest('#isBanned-logout-btn');

    if (bannedLogoutBtn) {
        fetch('http://localhost/troxan/app/api.php?path=logout', {
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
                window.location.href = 'http://localhost/troxan/';
            });

        return;
    }
});