document.addEventListener('DOMContentLoaded', () => {
    // ID-k visszaállítva az eredeti, basesite nélküli nevekre
    const trigger = document.getElementById('leaderboard-sort-trigger');
    const dropdown = document.getElementById('leaderboard-sort-dropdown');
    const selectedSortSpan = document.getElementById('leaderboard-selected-sort');
    
    // Biztonsági ellenőrzés
    if (!trigger || !dropdown) return;

    // --- Menü nyitása/csukása kattintásra ---
    trigger.addEventListener('click', (e) => {
        // Megakadályozzuk, hogy a kattintás azonnal eljusson a document-ig
        e.stopPropagation(); 
        
        dropdown.classList.toggle('leaderboard-hidden');
    });

    // --- Elem kiválasztása a menüből ---
    dropdown.querySelectorAll('button').forEach(item => {
        item.addEventListener('click', () => {
            // Frissíti a gomb szövegét
            if (selectedSortSpan) {
                selectedSortSpan.textContent = item.textContent;
            }
            // Bezárja a menüt
            dropdown.classList.add('leaderboard-hidden');
        });
    });

    // --- Bezárás, ha a menün kívülre kattintanak ---
    document.addEventListener('click', () => {
        if (!dropdown.classList.contains('leaderboard-hidden')) {
            dropdown.classList.add('leaderboard-hidden');
        }
    });
});