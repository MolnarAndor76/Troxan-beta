// ====== TABS LOGIKA ======
document.addEventListener('click', (event) => {
  const btn = event.target.closest('.basesite-tab-btn');
  if (!btn) return; 

  const tabId = btn.id.replace('basesite-btn-', '');

  // 1. Minden tartalom elrejtése
  const allContents = document.querySelectorAll('.basesite-tab-content');
  allContents.forEach(content => {
    content.classList.remove('basesite-block');
    content.classList.add('basesite-hidden');
  });

  // 2. Minden gomb "inaktív" állapotba rakása
  const allButtons = document.querySelectorAll('.basesite-tab-btn');
  allButtons.forEach(b => {
    b.classList.remove('basesite-tab-active');
    b.classList.add('basesite-tab-inactive');
  });

  // 3. A cél tartalom megjelenítése ÉS animálása
  const targetContent = document.getElementById('basesite-tab-' + tabId);
  if (targetContent) {
    targetContent.classList.remove('basesite-hidden');
    targetContent.classList.add('basesite-block');
    
    // JS Animáció: Lágy beúszás alulról
    targetContent.animate([
      { opacity: 0, transform: 'translateY(10px)' },
      { opacity: 1, transform: 'translateY(0)' }
    ], {
      duration: 300,
      easing: 'ease-in-out'
    });
  }

  // 4. A rákattintott gomb "aktív" állapotba rakása
  btn.classList.remove('basesite-tab-inactive');
  btn.classList.add('basesite-tab-active');
});

// ====== POP-UP (MODAL) LOGIKA ======
document.addEventListener('click', (event) => {
  const modal = document.getElementById('basesite-req-modal');
  if (!modal) return; 
  
  // 1. Kinyitás: Gombra kattintva
  if (event.target.closest('#basesite-open-req-btn')) {
    modal.classList.remove('basesite-hidden');
    document.body.style.overflow = 'hidden'; 

    // Megkeressük a belső ablakot és animáljuk a belépését
    const modalWindow = modal.querySelector('.basesite-modal-window');
    if (modalWindow) {
      modalWindow.animate([
        { opacity: 0, transform: 'translateY(15px) scale(0.95)' },
        { opacity: 1, transform: 'translateY(0) scale(1)' }
      ], { duration: 300, easing: 'ease-out' });
    }
  }

  // 2. Bezárás: X gombra, VAGY a sötét háttérre kattintva
  if (event.target.closest('#basesite-close-req-btn') || event.target === modal) {
    const modalWindow = modal.querySelector('.basesite-modal-window');
    
    // Ha megvan az ablak, csinálunk egy kilépő animációt
    if (modalWindow) {
      const fadeOut = modalWindow.animate([
        { opacity: 1, transform: 'translateY(0) scale(1)' },
        { opacity: 0, transform: 'translateY(15px) scale(0.95)' }
      ], { duration: 200, easing: 'ease-in' });

      // Amikor az animáció véget ért (onfinish), CSAK AKKOR rejtjük el a modalt
      fadeOut.onfinish = () => {
        modal.classList.add('basesite-hidden');
        document.body.style.overflow = 'auto';
      };
    } else {
      // Biztonsági fallback, ha valamiért nem lenne meg a belső ablak
      modal.classList.add('basesite-hidden');
      document.body.style.overflow = 'auto';
    }
  }
});