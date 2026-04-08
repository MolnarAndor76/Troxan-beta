//url
const logoutUrl = `/app/api.php?path=logout`;

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
                    window.location.href = '/login';
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => {
                console.error('Logout error:', err);
                // On error, still clear and redirect user
                localStorage.clear();
                window.location.href = '/login'; 
            });

        return;
    }
});