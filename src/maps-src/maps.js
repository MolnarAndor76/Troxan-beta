// --- FŐ KATTINTÁS FIGYELŐ (Minden gomb és modal) ---
document.addEventListener('click', (event) => {
    
    // 1. MODALOK KEZELÉSE (Nyitás / Zárás)
    const helpBtn = event.target.closest('#maps-help-btn');
    const trashOpenBtn = event.target.closest('#maps-trash-open-btn');
    const closeModalBtn = event.target.closest('.maps-close-modal');
    const backdrop = event.target.closest('.maps-modal-backdrop');

    if (helpBtn) {
        document.getElementById('maps-help-modal').classList.remove('hidden');
        return;
    }
    if (trashOpenBtn) {
        document.getElementById('maps-trash-modal').classList.remove('hidden');
        return;
    }
    if (closeModalBtn || backdrop) {
        document.querySelectorAll('#maps-help-modal, #maps-trash-modal').forEach(modal => {
            if (modal) modal.classList.add('hidden');
        });
        return;
    }

    // 2. RENDEZÉS (Sort Dropdown nyitása és kiválasztás)
    const sortTrigger = event.target.closest('#maps-sort-trigger');
    const sortDropdown = document.getElementById('maps-sort-dropdown');
    const sortItem = event.target.closest('.maps-dropdown-item');

    if (sortTrigger) {
        sortDropdown.classList.toggle('hidden');
        return;
    }
    if (sortItem) {
        const newSort = sortItem.textContent;
        const searchVal = document.getElementById('maps-search').value;
        // Az oldal újratöltése a paraméterekkel a PHP szűréshez
        window.location.href = `/maps?search=${encodeURIComponent(searchVal)}&sort=${encodeURIComponent(newSort)}`;
        return;
    }
    if (sortDropdown && !sortDropdown.classList.contains('hidden') && !event.target.closest('.maps-sort-box')) {
        sortDropdown.classList.add('hidden');
    }

    // 3. EDIT GOMB (Galéria kártya és Kuka)
    const editBtn = event.target.closest('.maps-edit-btn');
    if (editBtn) {
        const mapId = editBtn.getAttribute('data-mapid');
        // Ide jön majd az Editorodhoz vezető link!
        alert(`Irány az Editor! Pálya ID: ${mapId}`);
        // window.location.href = `/editor?map_id=${mapId}`;
        return;
    }

    // 4. LETÖLTÉS GOMB
    const downloadBtn = event.target.closest('.maps-download-overlay');
    if (downloadBtn) {
        const card = downloadBtn.closest('.maps-card');
        const mapData = downloadBtn.getAttribute('data-mapfile');
        const mapName = downloadBtn.getAttribute('data-mapname') || 'map';
        const mapId = downloadBtn.getAttribute('data-mapid');

        if (mapId) {
            fetch('http://localhost/troxan/app/api.php?path=maps', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'increment_download', map_id: mapId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && card) {
                    const countSpan = card.querySelector('.maps-download-count');
                    let current = parseInt(countSpan.textContent.replace(/[^\d]/g, '')) || 0;
                    countSpan.textContent = `⬇ ${(current + 1).toLocaleString()}`;
                }
            });
        }

        const blob = new Blob([mapData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${mapName.replace(/\s+/g, '_')}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        return;
    }

    // 5. TÖRLÉS GOMB (Galéria)
    const deleteBtn = event.target.closest('.maps-delete');
    if (deleteBtn) {
        const mapId = deleteBtn.getAttribute('data-mapid');
        const card = deleteBtn.closest('.maps-card');
        const mapName = card.querySelector('.map-name').textContent.trim();

        if (confirm(`Biztosan törlöd a(z) "${mapName}" nevű pályát?`)) {
            fetch('http://localhost/troxan/app/api.php?path=maps', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_map', map_id: mapId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.5)';
                    setTimeout(() => card.remove(), 400);
                } else {
                    alert("Hiba: " + data.message);
                }
            });
        }
        return;
    }

    // 6. RESTORE GOMB (Kuka)
    const restoreBtn = event.target.closest('.maps-restore-btn');
    if (restoreBtn) {
        const mapId = restoreBtn.getAttribute('data-mapid');
        fetch('http://localhost/troxan/app/api.php?path=maps', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'restore_map', map_id: mapId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                restoreBtn.closest('tr').remove();
                // A legegyszerűbb, ha egy visszaállítás után frissítjük az oldalt, 
                // hogy azonnal megjelenjen a galériában az elem.
                window.location.reload(); 
            } else {
                alert("Hiba: " + data.message);
            }
        });
        return;
    }
});

// --- KUKA KOMBINÁLT SZŰRÉS (Keresőmező + Pipák) ---
const updateTrashFilter = () => {
    const searchInput = document.getElementById('trash-search-input');
    if (!searchInput) return; 
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    const checkedStatuses = Array.from(document.querySelectorAll('.trash-filter-cb:checked')).map(cb => cb.value);
    const rows = document.querySelectorAll('.trash-row');
    
    rows.forEach(row => {
        const creatorName = row.querySelector('.trash-creator').textContent.toLowerCase();
        const rowStatus = row.getAttribute('data-status');
        
        const matchesSearch = creatorName.includes(searchTerm);
        const matchesStatus = checkedStatuses.includes(rowStatus);
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
};

document.addEventListener('input', (event) => {
    if (event.target.id === 'trash-search-input') updateTrashFilter();
});
document.addEventListener('change', (event) => {
    if (event.target.classList.contains('trash-filter-cb')) updateTrashFilter();
});

// --- GALÉRIA KERESŐ (Enter gombra reagál) ---
document.addEventListener('keypress', (e) => {
    if (e.target.id === 'maps-search' && e.key === 'Enter') {
        const searchVal = e.target.value;
        const sortVal = document.getElementById('maps-selected-sort').textContent;
        window.location.href = `/maps?search=${encodeURIComponent(searchVal)}&sort=${encodeURIComponent(sortVal)}`;
    }
});