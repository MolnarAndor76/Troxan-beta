<div class="mymaps-site">
  <div id="mymaps-main" class="flex-1 min-h-0 flex flex-col">
    <section id="mymaps-wrapper" class="content-box flex-1 min-h-0 flex flex-col">
      <header class="mymaps-header">
        <div class="mymaps-header-top">
          <h2 id="mymaps-title">MY MAPS</h2>
          <button id="mymaps-mobile-menu-btn" class="mymaps-ham-btn md:hidden" type="button" aria-label="Open my maps menu">☰</button>
        </div>

        <div id="mymaps-controls-row" class="mymaps-controls-row hidden md:flex">
          <input type="text" id="mymaps-search" placeholder="Search my library...">
          
          <div class="mymaps-sort-box relative">
            <button id="mymaps-sort-trigger" class="mymaps-sort-btn">
              <span id="mymaps-selected-sort">Newest Added</span>
              <svg class="w-4 h-4 ml-2 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
            </button>
            <ul id="mymaps-sort-dropdown" class="mymaps-sort-dropdown hidden absolute top-full left-0 mt-1 bg-white border-2 border-orange-950 rounded shadow-lg z-50 w-full">
              <li><button class="mymaps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Newest Added</button></li>
              <li><button class="mymaps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Oldest Added</button></li>
              <li><button class="mymaps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Alphabetical</button></li>
            </ul>
          </div>

          <div class="mymaps-nav-buttons flex items-center gap-2">
            <button id="mymaps-nav-maps" class="mymaps-nav-btn" type="button">Maps</button>
            <button id="mymaps-nav-profile" class="mymaps-nav-btn" type="button" aria-label="Profile">
              <img id="mymaps-nav-profile-avatar" class="mymaps-profile-avatar" src="https://picsum.photos/id/1025/200/200" alt="Profile">
              <span>Profile</span>
            </button>
          </div>
        </div>
      </header>

      <div class="mymaps-scroll-area">
        <div class="mymaps-grid">
         <?php if (empty($my_maps)): ?>
             <p id="live-mymaps-empty-msg" class="text-orange-900 font-bold text-xl col-span-full mt-10 text-center w-full">Könyvtárad jelenleg üres! Adj hozzá pályákat a Maps menüből! 🏝️</p>
          <?php else: ?>
            <?php foreach ($my_maps as $map): ?>
              <?php 
                $isCreatorEngineer = ($map['creator_role'] === 'Engineer');
                $isMyMap = ($myUserId == $map['creator_user_id']);
                $canRenameMap = $isMyMap || $isStaff;
                $cardBorderClass = $isCreatorEngineer ? 'border-cyan-900 shadow-cyan-900/50' : 'border-orange-950 shadow-[2px_2px_0px_#000]';
                $nameClass = $isCreatorEngineer ? 'text-cyan-950' : 'text-orange-950';

                // Státusz jelvény (Badge) generálása
                $statusBadge = '';
                if ($isMyMap) {
                    if ($map['status'] == 1) $statusBadge = '<span class="bg-green-600 text-white text-xs px-2 py-0.5 rounded-sm border border-green-900 absolute top-2 right-2 font-bold shadow-md z-10 uppercase">Published</span>';
                    elseif ($map['status'] == 0) $statusBadge = '<span class="bg-gray-500 text-white text-xs px-2 py-0.5 rounded-sm border border-gray-900 absolute top-2 right-2 font-bold shadow-md z-10 uppercase">Draft</span>';
                    elseif ($map['status'] == 3) $statusBadge = '<span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-sm border border-orange-900 absolute top-2 right-2 font-bold shadow-md z-10 uppercase">Unpublished</span>';
                }
              ?>
              
              <article class="mymaps-card" 
                       data-id="<?= $map['id'] ?>"
                       data-name="<?= htmlspecialchars(strtolower($map['map_name'])) ?>"
                       data-creator="<?= htmlspecialchars(strtolower($map['creator_name'])) ?>"
                       data-date="<?= !empty($map['added_at']) ? strtotime($map['added_at']) : 0 ?>">
                       
                <div class="mymaps-image relative group overflow-hidden border-4 <?= $cardBorderClass ?> rounded-sm mb-2">
                  <?= $statusBadge ?>
                  <img src="<?= htmlspecialchars($map['map_picture']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                </div>

                <div class="mymaps-info">
                  <p class="map-name font-bold <?= $nameClass ?>"><?= htmlspecialchars($map['map_name']) ?></p>
                  <p class="mymaps-creator-name text-sm">
                    <?php if ($isCreatorEngineer): ?>🛠️<?php endif; ?> By: <?= $isMyMap ? 'You' : htmlspecialchars($map['creator_name']) ?>
                  </p>
                  
                  <div class="mymaps-btns-stats mt-2 flex justify-between items-center w-full gap-2 p-2 bg-orange-950/30 rounded-sm border border-orange-950/50">
                    <?php if ($canRenameMap): ?>
                        <button class="mymaps-edit-btn bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-1.5 px-3 border-2 border-blue-950 rounded-sm shadow-[2px_2px_0px_#000] text-xs uppercase" data-mapid="<?= $map['id'] ?>">Edit</button>
                    <?php else: ?>
                        <button class="mymaps-edit-locked-btn bg-gray-500 text-white font-extrabold py-1.5 px-3 border-2 border-gray-900 rounded-sm shadow-[2px_2px_0px_#000] text-xs uppercase cursor-not-allowed" data-mapid="<?= $map['id'] ?>" title="Only the creator or staff can rename this map">🔒</button>
                    <?php endif; ?>
                    
                    <?php if ($isMyMap): ?>
                        <?php if ($map['status'] == 1): ?>
                            <button class="mymaps-publish-btn bg-orange-600 hover:bg-orange-500 text-white font-extrabold py-1.5 px-2 border-2 border-orange-950 rounded-sm shadow-[2px_2px_0px_#000] text-[10px] uppercase tracking-wide" data-mapid="<?= $map['id'] ?>">Unpublish</button>
                        <?php else: ?>
                            <button class="mymaps-publish-btn bg-green-600 hover:bg-green-500 text-white font-extrabold py-1.5 px-3 border-2 border-green-950 rounded-sm shadow-[2px_2px_0px_#000] text-[10px] uppercase tracking-wide" data-mapid="<?= $map['id'] ?>">Publish</button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <button class="mymaps-remove-btn text-xl hover:scale-110 transition-transform cursor-pointer drop-shadow-md ml-auto" data-mapid="<?= $map['id'] ?>" title="Eltávolítás a könyvtárból">🗑️</button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>

<div id="mymaps-alert-modal" class="hidden fixed inset-0 z-[9998] items-center justify-center">
  <div class="absolute inset-0 bg-black/80"></div>
  <div class="relative z-10 w-[90%] max-w-[400px] bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] flex flex-col">
    <button id="mymaps-alert-close-btn" class="absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
    <div id="mymaps-alert-header" class="border-b-4 border-orange-950 pb-2 mb-4">
      <h2 id="mymaps-alert-title" class="text-xl font-bold text-orange-950">Notice</h2>
    </div>
    <p id="mymaps-alert-message" class="text-lg font-bold text-gray-800 my-4">Message</p>
    <button id="mymaps-alert-ok-btn" class="bg-yellow-500 hover:bg-yellow-400 text-orange-950 font-extrabold py-2 px-8 rounded border-2 border-orange-950 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer self-center mt-4">OK</button>
  </div>
</div>

<div id="mymaps-confirm-modal" class="hidden fixed inset-0 z-[9999] items-center justify-center">
  <div class="absolute inset-0 bg-black/80"></div>
  <div class="relative z-10 w-[90%] max-w-[420px] bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] flex flex-col">
    <button id="mymaps-confirm-close-btn" class="absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
    <div id="mymaps-confirm-header" class="border-b-4 border-red-950 pb-2 mb-4">
      <h2 id="mymaps-confirm-title" class="text-xl font-bold text-red-600">Confirmation</h2>
    </div>
    <p id="mymaps-confirm-message" class="text-lg font-bold text-gray-800 my-4">Are you sure?</p>
    <div class="flex justify-center gap-4 mt-6">
      <button id="mymaps-confirm-cancel-btn" class="bg-gray-500 hover:bg-gray-400 text-white font-extrabold py-2 px-6 rounded border-2 border-gray-900 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Cancel</button>
      <button id="mymaps-confirm-ok-btn" class="bg-red-600 hover:bg-red-500 text-white font-extrabold py-2 px-6 rounded border-2 border-red-900 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Confirm</button>
    </div>
  </div>
</div>

<div id="mymaps-rename-modal" class="hidden fixed inset-0 z-[10000] items-center justify-center">
  <div class="absolute inset-0 bg-black/80"></div>
  <div class="relative z-10 w-[90%] max-w-[460px] bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)] flex flex-col">
    <button id="mymaps-rename-close-btn" class="absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
    <h2 class="text-lg font-bold text-orange-950 mb-3">Rename Map</h2>
    <p class="text-sm text-orange-900 mb-2">Current name: <span id="mymaps-rename-old-name" class="font-bold"></span></p>
    <input id="mymaps-rename-input" type="text" class="w-full border border-orange-950 rounded p-2 mb-3" placeholder="New map name">
    <div class="flex justify-end gap-3 mt-1">
      <button id="mymaps-rename-cancel-btn" class="bg-gray-500 hover:bg-gray-400 text-white font-extrabold py-2 px-6 rounded border-2 border-gray-900 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Cancel</button>
      <button id="mymaps-rename-save-btn" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-2 px-6 rounded border-2 border-blue-950 shadow-[3px_3px_0px_rgba(0,0,0,1)] transition-transform hover:translate-y-1 cursor-pointer">Save</button>
    </div>
  </div>
</div>