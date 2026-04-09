<div class="basesite-main-wrapper">

  <nav class="basesite-nav">
    <button id="basesite-btn-download" class="basesite-tab-btn basesite-tab-active">
      <span class="basesite-tab-text">Download</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" /></svg>
    </button>
    <button id="basesite-btn-patchnotes" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">What's new?</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" /></svg>
    </button>
    <button id="basesite-btn-lore" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">Lore</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" /></svg>
    </button>
    <button id="basesite-btn-about" class="basesite-tab-btn basesite-tab-inactive">
      <span class="basesite-tab-text">About us</span>
      <svg class="basesite-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
    </button>
  </nav>

  <div class="basesite-content-wrapper">

    <section id="basesite-tab-download" class="basesite-tab-content basesite-block">
      <h1 class="basesite-title-main">Welcome to Troxan!</h1>
      <div class="basesite-video-container">
        <iframe src="https://www.youtube-nocookie.com/embed/_pMgNJjNodo?autoplay=1&mute=1&loop=1&playlist=_pMgNJjNodo&controls=0&modestbranding=1&rel=0" class="basesite-video-iframe" title="YouTube video player" frameborder="0" allow="autoplay; encrypted-media" loading="lazy"></iframe>
      </div>
      <div class="basesite-dl-layout">
        <button class="basesite-dl-btn" id="basesite-download-game-btn" data-loggedin="<?= $isLoggedIn ?>">Download Game</button>
        <button id="basesite-open-req-btn" class="basesite-link">View System requirements</button>
      </div>
    </section>

    <section id="basesite-tab-patchnotes" class="basesite-tab-content basesite-hidden">
      <div class="flex justify-between items-center mb-6 border-b-2 border-orange-900 pb-2">
        <h2 class="text-3xl font-bold text-orange-950 m-0 border-0 pb-0">What's new?</h2>
        <?php if ($canEditPatchNotes): ?>
          <div class="flex gap-2">
            <button id="patch-recycle-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded border border-gray-500 shadow-sm transition-colors text-sm flex items-center gap-1 cursor-pointer">🗑️ Recycle Bin</button>
            <button id="patch-new-btn" class="bg-green-600 hover:bg-green-500 text-white font-bold py-1 px-3 rounded border border-green-800 shadow-sm transition-colors text-sm flex items-center gap-1 cursor-pointer">➕ New Patch</button>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($patchNotes)): ?>
        <div class="flex flex-col gap-6">
          <?php foreach ($patchNotes as $index => $patch): 
              
              $isEngineerPatch = ($patch['author_role'] === 'Engineer');
              $isMyPatch = ($currentUserId == $patch['created_by']);
              $isLocked = ($patch['is_locked'] == 1);
              $iAmEngineer = (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Engineer');
              
              // === JOGOSULTSÁG ===
              $canEditThisPatch = false;
              if ($canEditPatchNotes) {
                  if ($isEngineerPatch) {
                      if ($isMyPatch) $canEditThisPatch = true; // Csak a saját ciánkék posztját editálhatja
                  } else {
                      // Ha Admin patch, és lakatolva van, csak Engineer editálhatja
                      if ($isLocked) {
                          if ($iAmEngineer) $canEditThisPatch = true;
                      } else {
                          $canEditThisPatch = true; 
                      }
                  }
              }

              $bgClass = $isEngineerPatch ? 'bg-cyan-100 border-cyan-900 shadow-cyan-900/50' : 'bg-orange-100 border-orange-900 shadow-orange-900/50';
              $titleClass = $isEngineerPatch ? 'text-cyan-950' : 'text-orange-950';
              $timeClass = $isEngineerPatch ? 'text-cyan-800' : 'text-orange-800';
              $borderClass = $isEngineerPatch ? 'border-cyan-950/30' : 'border-orange-950/30';
          ?>

            <div class="<?= $bgClass ?> border-2 rounded p-4 md:p-6 shadow-md relative group" data-id="<?= $patch['id'] ?>">

              <div class="flex justify-between items-end border-b-2 <?= $borderClass ?> pb-2 mb-4">
                <div>
                    <h3 class="text-2xl font-bold <?= $titleClass ?> patch-title"><?= htmlspecialchars($patch['name']) ?></h3>
                    <?php if ($patch['author_name']): ?>
                        <div class="text-sm font-bold mt-1 <?= $timeClass ?>">
                            <?= $isEngineerPatch ? '🛠️ Engineer:' : '👤 Admin:' ?> <?= htmlspecialchars($patch['author_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <time class="text-sm md:text-base font-bold <?= $timeClass ?>" datetime="<?= $patch['created_at'] ?>">
                  <?= date('Y.m.d H:i', strtotime($patch['created_at'])) ?>
                </time>
              </div>

              <div class="text-lg text-gray-800 leading-relaxed patch-desc">
                <?= nl2br(htmlspecialchars($patch['description'])) ?>
              </div>

              <?php if (!empty($patch['updated_by']) && !empty($patch['updater_name'])): ?>
                  <div class="text-sm font-medium italic mt-6 text-right opacity-70 <?= $timeClass ?>">
                      Utoljára frissítve: <?= htmlspecialchars($patch['updater_name']) ?> által (<?= date('Y.m.d H:i', strtotime($patch['updated_at'])) ?>)
                  </div>
              <?php endif; ?>

              <div class="absolute bottom-2 right-2 flex gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                
                <?php if ($iAmEngineer): ?>
                    <button class="patch-lock-btn text-xl hover:scale-110 transition-transform cursor-pointer" title="<?= $isLocked ? 'Unlock patch' : 'Lock patch' ?>">
                        <?= $isLocked ? '🔒' : '🔓' ?>
                    </button>
                <?php elseif ($isLocked && $canEditPatchNotes): ?>
                    <span class="text-xl opacity-50 cursor-not-allowed" title="Engineer által lezárva!">🔒</span>
                <?php endif; ?>

                <?php if ($canEditThisPatch): ?>
                  <button class="patch-edit-btn text-xl hover:scale-110 transition-transform cursor-pointer" title="Edit Patch">✏️</button>
                  <button class="patch-delete-btn text-xl hover:scale-110 transition-transform cursor-pointer" title="Move to Recycle Bin">🗑️</button>
                <?php endif; ?>

              </div>

            </div>

          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="basesite-placeholder-box text-orange-950 font-bold text-xl bg-orange-200 border-orange-900">
          There are no patch notes available yet. Check back later!
        </div>
      <?php endif; ?>
    </section>

    <section id="basesite-tab-lore" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">The fate of troxan</h2>
      <p class="basesite-text">For centuries, the majestic realm of Troxan was a beacon of absolute peace and prosperity. <br>
       Joyous laughter echoed through its emerald valleys, and citizens lived in perfect harmony under the wise and benevolent guidance of the High Sovereign. The skies were forever bright, the rivers flowed with crystal-clear waters, and a golden age of tranquility blessed every corner of the kingdom. It was a true paradise, untouched by darkness. <br>

<br> But then, everything changed. <br>

<br> Without warning, a mysterious and devastating plague—a rapidly mutating, corrupted virus—swept across the land like a silent storm. It withered the once-vibrant forests, silenced the joyful streets, and began twisting the realm's peaceful inhabitants into hollow, aggressive husks. No ancient magic could cure it, and no fortress walls could keep the infection at bay. The virus is spreading at an unstoppable rate, consuming the very life force of Troxan. <br>

<br> Now, as the kingdom teeters on the brink of total annihilation, the desperate Sovereign has summoned you. Out of all the warriors and scholars, you are the only one who possesses the resilience to withstand the infection. You have been tasked with the ultimate, perilous mission: venture deep into the heart of the corrupted zones, eradicate the source of the virus, and cleanse the land. <br>

<br> The time for fear is over. You are Troxan's last, shining hope. Will you answer the call and save the realm, or will the darkness consume us all?</p>
    </section>

    <section id="basesite-tab-about" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">About Troxan and us</h2>
      <p class="basesite-text">Troxan started as a school project…</p>
      <p class="basesite-text">Feel free to email us at <a href="mailto:troxangame@email.com" class="basesite-link">troxangame@email.com</a></p>
      <div class="basesite-about-box">
        <h3 class="basesite-about-title">Special thanks to our artists:</h3>
        <ul class="basesite-about-list">
          <li>Trailer made by: <a href="#" class="basesite-link">Név (kattintható link)</a></li>
          <li>Artworks: <em>Hamarosan...</em></li>
        </ul>
      </div>
    </section>

    <div id="basesite-req-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window">
        <div class="basesite-modal-header">
          <h2 class="basesite-modal-title">System requirements</h2>
          <button id="basesite-close-req-btn" class="basesite-modal-close">&times;</button>
        </div>
        <div class="basesite-modal-body">
          <table class="basesite-table">
            <thead class="basesite-thead">
              <tr class="basesite-tr-head"><th class="basesite-th">Komponens</th><th class="basesite-th">Minimum</th><th class="basesite-th-yellow">Ajánlott</th></tr>
            </thead>
            <tbody class="basesite-tbody">
              <tr class="basesite-tr-split"><td class="basesite-td-hide-mobile">CPU</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">CPU</span><span class="basesite-mobile-label-inline">Min: </span>Nemtom p4</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>Ryzen 9 5950X</td></tr>
              <tr class="basesite-tr-split"><td class="basesite-td-hide-mobile">GPU</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">GPU</span><span class="basesite-mobile-label-inline">Min: </span>Gt 1030</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>RTX 3080TI</td></tr>
              <tr class="basesite-tr-split"><td class="basesite-td-hide-mobile">RAM</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">RAM</span><span class="basesite-mobile-label-inline">Min: </span>256mb</td><td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>64gb</td></tr>
              <tr class="basesite-tr-hidden-mobile"><td class="basesite-td-label">OS</td><td class="basesite-td">&lt;WIN XP 64bit</td><td class="basesite-td-gray">-------------------</td></tr>
              <tr class="basesite-tr-hidden-desktop"><td class="basesite-td-label">STORAGE</td><td class="basesite-td">300mb</td><td class="basesite-td-gray">-------------------</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="patch-new-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window max-w-2xl">
        <div class="basesite-modal-header bg-green-800 border-b-4 border-green-950">
          <h2 class="basesite-modal-title text-green-300">➕ Create New Patch</h2>
          <button class="basesite-modal-close patch-close-btn">&times;</button>
        </div>
        <div class="basesite-modal-body bg-orange-50">
          <input type="text" id="new-patch-title" class="w-full bg-white border-4 border-orange-950 p-3 rounded-md text-lg text-gray-800 font-bold mb-4 focus:outline-none focus:border-green-600" placeholder="Patch Name (e.g. Patch 1.2 - The Awakening)">
          <textarea id="new-patch-desc" class="w-full h-48 bg-white border-4 border-orange-950 p-3 rounded-md text-lg text-gray-800 font-medium mb-4 focus:outline-none focus:border-green-600" placeholder="Write the patch notes here..."></textarea>
          <div class="flex justify-end gap-4 mt-4">
            <button id="patch-discard-btn" class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-6 rounded border-2 border-red-900 shadow-[2px_2px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Discard</button>
            <button id="patch-publish-btn" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded border-2 border-green-900 shadow-[2px_2px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Publish</button>
          </div>
        </div>
      </div>
    </div>

    <div id="patch-recycle-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window max-w-xl">
        <div class="basesite-modal-header bg-gray-800 border-b-4 border-gray-950">
          <h2 class="basesite-modal-title text-gray-300">🗑️ Recycle Bin</h2>
          <button class="basesite-modal-close patch-close-btn">&times;</button>
        </div>
        <div class="basesite-modal-body bg-gray-200" id="recycle-bin-content">
          <div class="text-center font-bold text-gray-500 py-10">Loading deleted patches...</div>
        </div>
      </div>
    </div>

    <div id="basesite-alert-modal" class="basesite-modal-overlay basesite-hidden" style="z-index: 9998;">
      <div class="basesite-modal-window max-w-sm">
        <div id="basesite-alert-header" class="basesite-modal-header bg-orange-900 border-b-4 border-orange-950">
          <h2 id="basesite-alert-title" class="basesite-modal-title text-white">Notice</h2>
          <button id="basesite-alert-close-btn" class="basesite-modal-close">&times;</button>
        </div>
        <div class="basesite-modal-body bg-orange-50 text-center p-6">
          <p id="basesite-alert-message" class="text-xl font-bold text-orange-950 my-4 leading-relaxed">Message goes here</p>
          <button id="basesite-alert-ok-btn" class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-extrabold py-2 px-8 rounded border-2 border-orange-950 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer mt-4">OK</button>
        </div>
      </div>
    </div>

    <div id="basesite-confirm-modal" class="basesite-modal-overlay basesite-hidden" style="z-index: 9999;">
      <div class="basesite-modal-window max-w-sm">
        <div id="basesite-confirm-header" class="basesite-modal-header bg-red-800 border-b-4 border-red-950">
          <h2 id="basesite-confirm-title" class="basesite-modal-title text-white">Megerősítés</h2>
          <button id="basesite-confirm-close-btn" class="basesite-modal-close">&times;</button>
        </div>
        <div class="basesite-modal-body bg-orange-50 text-center p-6">
          <p id="basesite-confirm-message" class="text-xl font-bold text-orange-950 my-4 leading-relaxed">Biztos vagy benne?</p>
          <div class="flex justify-center gap-4 mt-6">
            <button id="basesite-confirm-cancel-btn" class="bg-gray-500 hover:bg-gray-400 text-white font-extrabold py-2 px-6 rounded border-2 border-gray-800 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Mégse</button>
            <button id="basesite-confirm-ok-btn" class="bg-red-600 hover:bg-red-500 text-white font-extrabold py-2 px-6 rounded border-2 border-red-900 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Igen</button>
          </div>
        </div>
      </div>
    </div>

  </div> 
</div>