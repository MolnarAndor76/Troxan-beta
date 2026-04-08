<div class="maps-site">
  <div id="maps-main">
    <section id="maps-wrapper" class="content-box">
      <header class="maps-header">
        <h2 id="maps-title">MAPS</h2>

        <div class="maps-controls-row">
          <input type="text" id="maps-search" placeholder="Search...">
          
          <div class="maps-sort-box relative">
            <button id="maps-sort-trigger" class="maps-sort-btn">
              <span id="maps-selected-sort">Downloads</span>
              <svg class="w-4 h-4 ml-2 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
            </button>
            <ul id="maps-sort-dropdown" class="maps-sort-dropdown hidden absolute top-full left-0 mt-1 bg-white border-2 border-orange-950 rounded shadow-lg z-50 w-full">
              <li><button class="maps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Downloads</button></li>
              <li><button class="maps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Alphabetical</button></li>
              <li><button class="maps-dropdown-item w-full text-left px-4 py-2 hover:bg-orange-200" type="button">Most recent</button></li>
            </ul>
          </div>

          <?php 
          $roleName = $_SESSION['role_name'] ?? 'Player';
          $isStaff = in_array($roleName, ['Admin', 'Moderator', 'Engineer']);
          $iAmEngineer = ($roleName === 'Engineer');
          $myUserId = $_SESSION['user_id'] ?? 0;
          
          if ($isStaff): ?>
            <button id="maps-trash-open-btn" class="maps-help-btn" title="Admin Trash">🗑️</button>
          <?php endif; ?>
          
          <button id="maps-go-mymaps-btn" class="maps-help-btn" title="Irány a könyvtáram!">🗺️ My Maps</button>
          
          <button id="maps-help-btn" class="maps-help-btn">?</button>
        </div>
      </header>

      <div class="maps-scroll-area max-h-[600px] overflow-y-auto pr-2">
        <div class="maps-grid">
         <?php if (empty($active_maps)): ?>
             <p id="live-maps-empty-msg" class="text-orange-900 font-bold text-xl col-span-full mt-10 text-center w-full">No maps found. 🏝️</p>
          <?php else: ?>
            <?php foreach ($active_maps as $map): ?>
              <?php 
                $isCreatorEngineer = ($map['creator_role'] === 'Engineer');
                $isMyMap = ($myUserId == $map['creator_user_id']);
                
                $canEditOrDelete = false;
                if ($isMyMap) {
                    $canEditOrDelete = true;
                } elseif ($isStaff) {
                    if ($isCreatorEngineer && !$iAmEngineer) {
                        $canEditOrDelete = false; 
                    } else {
                        $canEditOrDelete = true;
                    }
                }

                $cardBorderClass = $isCreatorEngineer ? 'border-cyan-900 shadow-cyan-900/50' : 'border-orange-950 shadow-[2px_2px_0px_#000]';
                $nameClass = $isCreatorEngineer ? 'text-cyan-950' : 'text-orange-950';
                $isInLibrary = !empty($map['is_in_library']);
              ?>
              
              <article class="maps-card" 
                       data-id="<?= $map['id'] ?>"
                       data-name="<?= htmlspecialchars(strtolower($map['map_name'])) ?>"
                       data-creator="<?= htmlspecialchars(strtolower($map['creator_name'])) ?>"
                       data-downloads="<?= (int)$map['downloads'] ?>"
                       data-date="<?= !empty($map['created_at']) ? strtotime($map['created_at']) : 0 ?>">
                       
                <div class="maps-image relative group overflow-hidden border-4 <?= $cardBorderClass ?> rounded-sm mb-2">
                  <img src="<?= htmlspecialchars($map['map_picture']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                  

                </div>

                <div class="maps-info">
                  <p class="map-name font-bold <?= $nameClass ?>"><?= htmlspecialchars($map['map_name']) ?></p>
                  <p class="maps-creator-name text-sm">
                    <?php if ($isCreatorEngineer): ?>🛠️<?php endif; ?> By: <?= htmlspecialchars($map['creator_name']) ?>
                  </p>
                  
                  <div class="maps-btns-stats mt-2 flex justify-between items-center w-full">
                    
                    <?php if (!$isMyMap): ?>
                      <button class="maps-add-btn <?= $isInLibrary ? 'bg-gray-500 hover:bg-gray-400 border-gray-950' : 'bg-green-600 hover:bg-green-500 border-green-950' ?> text-white px-3 py-1 font-extrabold text-xs border-2 rounded-sm shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all uppercase tracking-wider cursor-pointer z-10 relative" data-mapid="<?= $map['id'] ?>" data-added="<?= $isInLibrary ? 'true' : 'false' ?>"><?= $isInLibrary ? 'Added ✔️' : '+ Add' ?></button>
                    <?php else: ?>
                        <span class="text-[10px] font-bold text-orange-900/50 uppercase border border-orange-900/30 px-2 py-1 rounded-sm bg-orange-900/10">Own</span>
                    <?php endif; ?>
                    
                    <div class="flex gap-2 items-center ml-auto">
                        <?php if ($canEditOrDelete): ?>
                          <button class="maps-delete text-xl hover:scale-110 transition-transform cursor-pointer" data-mapid="<?= $map['id'] ?>" title="Delete">❌</button>
                        <?php elseif ($isCreatorEngineer && $isStaff): ?>
                          <span class="text-xl opacity-50 cursor-not-allowed" title="Engineer által védett!">🔒</span>
                        <?php endif; ?>
                        <span class="maps-download-count font-bold text-gray-700 ml-2">⬇ <span class="dl-number"><?= number_format($map['downloads']) ?></span></span>
                    </div>
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

<div id="maps-help-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div class="maps-modal-backdrop absolute inset-0 bg-black/80"></div>
  <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-md">
    <button class="maps-close-modal absolute top-2 right-4 text-3xl font-bold text-orange-950 hover:text-red-500">×</button>
    <h2 class="text-2xl font-bold mb-4 text-orange-950 border-b-2 border-orange-200 pb-2">Help</h2>
    <ul class="space-y-3 text-lg text-gray-800 font-medium">
      <li>⚔️ WASD: Move</li>
      <li>🛡️ Q: Defend</li>
      <li>📥 + Add: Add map to your library</li>
    </ul>
  </div>
</div>

<?php if ($isStaff): ?>
<div id="maps-trash-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div class="maps-modal-backdrop absolute inset-0 bg-black/80"></div>
  <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-4xl max-h-[80vh] flex flex-col">
    <button class="maps-close-modal absolute top-2 right-4 text-3xl font-bold text-orange-950 hover:text-red-500">×</button>
    
    <div class="flex flex-col mb-4 border-b-2 border-orange-200 pb-4 gap-4 mt-4 sm:mt-0">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-3xl font-bold text-orange-950 flex items-center gap-2 m-0">🗑️ Admin Trash</h2>
            <input type="text" id="trash-search-input" placeholder="Search creator..." class="w-full sm:w-64 p-2 border-4 border-orange-950 rounded-sm bg-white text-orange-950 font-bold focus:outline-none focus:border-yellow-500 shadow-[inset_0_4px_4px_rgba(0,0,0,0.05)]">
        </div>
        
        <div class="flex flex-wrap gap-4 text-orange-950 font-bold bg-orange-200/50 p-2 rounded border-2 border-orange-950/20">
            <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="trash-filter-cb w-4 h-4 accent-orange-600" value="5" checked> Scrapped (User Draft)</label>
            <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="trash-filter-cb w-4 h-4 accent-orange-600" value="3" checked> Deleted (User Pub)</label>
            <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="trash-filter-cb w-4 h-4 accent-orange-600" value="4" checked> Banned (Admin)</label>
        </div>
    </div>
    
    <div class="overflow-y-auto flex-1 pr-2">
      <table class="w-full text-left border-collapse" id="trash-table">
        <thead>
          <tr class="bg-orange-900 text-yellow-400 uppercase text-xs">
            <th class="p-3 border-2 border-orange-950 sticky top-0 bg-orange-900 z-10">Map Name</th>
            <th class="p-3 border-2 border-orange-950 sticky top-0 bg-orange-900 z-10">Creator</th>
            <th class="p-3 border-2 border-orange-950 sticky top-0 bg-orange-900 z-10">Status</th>
            <th class="p-3 border-2 border-orange-950 sticky top-0 bg-orange-900 z-10">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($trash_maps as $tmap): ?>
            <?php 
                $isTrashCreatorEngineer = ($tmap['creator_role'] === 'Engineer');
                $canRestore = true;
                if ($isTrashCreatorEngineer && !$iAmEngineer && $myUserId != $tmap['creator_user_id']) {
                    $canRestore = false;
                }
            ?>
            <tr class="border-b border-orange-950/20 text-orange-950 font-bold hover:bg-orange-200/50 trash-row transition-colors <?= $isTrashCreatorEngineer ? 'bg-cyan-50' : '' ?>" data-status="<?= $tmap['status'] ?>">
              <td class="p-3"><?= htmlspecialchars($tmap['map_name']) ?></td>
              <td class="p-3 trash-creator">
                <?php if ($isTrashCreatorEngineer): ?>🛠️<?php endif; ?> <?= htmlspecialchars($tmap['creator_name']) ?>
              </td>
              <td class="p-3 uppercase text-xs tracking-wider">
                <?php 
                  if ($tmap['status'] == 4) echo '<span class="text-red-600 bg-red-100 px-2 py-1 border border-red-600 rounded">Banned</span>';
                  elseif ($tmap['status'] == 3) echo '<span class="text-orange-600 bg-orange-100 px-2 py-1 border border-orange-600 rounded">Deleted</span>';
                  elseif ($tmap['status'] == 5) echo '<span class="text-gray-600 bg-gray-200 px-2 py-1 border border-gray-600 rounded">Scrapped</span>';
                  else echo '<span class="text-gray-600">Unknown</span>';
                ?>
              </td>
              <td class="p-3 flex gap-2">
                <?php if ($canRestore): ?>
                  <button class="maps-restore-btn bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded-sm shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_rgba(0,0,0,1)] transition-all uppercase text-xs tracking-wider" data-mapid="<?= $tmap['id'] ?>">Restore</button>
                <?php else: ?>
                  <span class="text-xs uppercase font-bold text-cyan-800 px-2 py-1 border border-cyan-800 rounded bg-cyan-100">🔒 Védett</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (empty($trash_maps)): ?>
        <p class="text-center text-orange-900/50 font-bold p-8">A kuka jelenleg üres.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="basesite-alert-modal" class="hidden fixed inset-0 z-[9998] flex items-center justify-center">
  <div class="maps-modal-backdrop absolute inset-0 bg-black/80"></div>
  <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-sm text-center flex flex-col items-center">
    <div id="basesite-alert-header" class="border-b-4 border-orange-950 w-full pb-2 mb-4">
      <h2 id="basesite-alert-title" class="text-xl font-bold text-orange-950">Notice</h2>
    </div>
    <p id="basesite-alert-message" class="text-lg font-bold text-gray-800 my-4">Üzenet helye</p>
    <button id="basesite-alert-ok-btn" class="bg-yellow-500 px-6 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] mt-4">OK</button>
  </div>
</div>

<div id="basesite-confirm-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
  <div class="maps-modal-backdrop absolute inset-0 bg-black/80"></div>
  <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-sm text-center flex flex-col items-center">
    <div id="basesite-confirm-header" class="border-b-4 border-red-950 w-full pb-2 mb-4">
      <h2 id="basesite-confirm-title" class="text-xl font-bold text-red-600">Megerősítés</h2>
    </div>
    <p id="basesite-confirm-message" class="text-lg font-bold text-gray-800 my-4">Biztos vagy benne?</p>
    <div class="flex justify-center w-full gap-4 mt-6">
      <button id="basesite-confirm-cancel-btn" class="bg-gray-400 px-6 py-2 font-bold text-white border-2 border-gray-600 rounded shadow-[2px_2px_0px_#000]">Mégse</button>
      <button id="basesite-confirm-ok-btn" class="bg-red-600 px-6 py-2 font-bold text-white border-2 border-red-900 rounded shadow-[2px_2px_0px_#000]">Igen</button>
    </div>
  </div>
</div>