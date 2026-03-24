<div class="mymaps-site">
  <div id="mymaps-main">
    <section id="mymaps-wrapper" class="content-box">
      <header class="mymaps-header">
        <h2 id="mymaps-title">MY MAPS</h2>

        <div class="mymaps-controls-row">
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
        </div>
      </header>

      <div class="mymaps-scroll-area max-h-[600px] overflow-y-auto pr-2">
        <div class="mymaps-grid">
         <?php if (empty($my_maps)): ?>
             <p id="live-mymaps-empty-msg" class="text-orange-900 font-bold text-xl col-span-full mt-10 text-center w-full">Könyvtárad jelenleg üres! Adj hozzá pályákat a Maps menüből! 🏝️</p>
          <?php else: ?>
            <?php foreach ($my_maps as $map): ?>
              <?php 
                $isCreatorEngineer = ($map['creator_role'] === 'Engineer');
                $isMyMap = ($myUserId == $map['creator_user_id']);
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
                    <button class="mymaps-edit-btn bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-1.5 px-3 border-2 border-blue-950 rounded-sm shadow-[2px_2px_0px_#000] text-xs uppercase" data-mapid="<?= $map['id'] ?>">Edit</button>
                    
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