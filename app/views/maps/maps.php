<div class="maps-site">
  <div id="maps-main">
    <section id="maps-wrapper" class="content-box">
      <header class="maps-header">
        <h2 id="maps-title">MAPS</h2>

        <div class="maps-controls-row">
          <input type="text" id="maps-search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          
          <div class="maps-sort-box relative">
            <button id="maps-sort-trigger" class="maps-sort-btn">
              <span id="maps-selected-sort"><?= htmlspecialchars($_GET['sort'] ?? 'Downloads') ?></span>
              <svg class="w-4 h-4 ml-2 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
            </button>
            <ul id="maps-sort-dropdown" class="maps-sort-dropdown hidden">
              <li><button class="maps-dropdown-item" type="button">Downloads</button></li>
              <li><button class="maps-dropdown-item" type="button">Alphabetical</button></li>
              <li><button class="maps-dropdown-item" type="button">Most recent</button></li>
            </ul>
          </div>

          <?php 
          $roleName = strtolower($_SESSION['role_name'] ?? 'player');
          $roleId = $_SESSION['role_id'] ?? 0;
          $isStaff = (in_array($roleName, ['admin', 'moderator', 'owner']) || in_array($roleId, [1, 2]));
          
          if ($isStaff): ?>
            <button id="maps-trash-open-btn" class="maps-help-btn" title="Admin Trash">🗑️</button>
          <?php endif; ?>
          
          <button id="maps-help-btn" class="maps-help-btn">?</button>
        </div>
      </header>

      <div class="maps-scroll-area">
        <div class="maps-grid">
          <?php if (empty($active_maps)): ?>
             <p class="text-orange-900 font-bold text-xl col-span-full mt-10">No maps found. 🏝️</p>
          <?php else: ?>
            <?php foreach ($active_maps as $map): ?>
              <article class="maps-card">
                
                <div class="maps-image relative group overflow-hidden border-4 border-orange-950 rounded-sm mb-2 shadow-[2px_2px_0px_#000]">
                  <img src="<?= htmlspecialchars($map['map_picture']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                  
                  <?php if ($isStaff || $_SESSION['user_id'] == $map['creator_user_id']): ?>
                  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <button class="maps-edit-btn bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-2 px-6 border-2 border-orange-950 shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all uppercase tracking-wider" data-mapid="<?= $map['id'] ?>">Edit</button>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="maps-info">
                  <p class="map-name"><?= htmlspecialchars($map['map_name']) ?></p>
                  <p class="maps-creator-name">By: <?= htmlspecialchars($map['creator_name']) ?></p>
                  <div class="maps-btns-stats">
                    <button class="maps-download-overlay" data-mapfile='<?= htmlspecialchars($map['map_file'], ENT_QUOTES, 'UTF-8') ?>' data-mapname="<?= htmlspecialchars($map['map_name']) ?>" data-mapid="<?= $map['id'] ?>">Download</button>
                    
                    <?php if ($isStaff || $_SESSION['user_id'] == $map['creator_user_id']): ?>
                      <button class="maps-delete" data-mapid="<?= $map['id'] ?>">❌</button>
                    <?php endif; ?>
                    
                    <span class="maps-download-count">⬇ <?= number_format($map['downloads']) ?></span>
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
    <h2 class="text-2xl font-bold mb-4 text-orange-950 border-b-2 border-orange-200 pb-2">Segítség</h2>
    <ul class="space-y-3 text-lg text-gray-800 font-medium">
      <li>⚔️ WASD: Mozgás</li>
      <li>🛡️ Q: Védekezés</li>
      <li>📥 Download: Pálya letöltése</li>
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
            <input type="text" id="trash-search-input" placeholder="Keresés készítőre..." class="w-full sm:w-64 p-2 border-4 border-orange-950 rounded-sm bg-white text-orange-950 font-bold focus:outline-none focus:border-yellow-500 shadow-[inset_0_4px_4px_rgba(0,0,0,0.05)]">
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
            <tr class="border-b border-orange-950/20 text-orange-950 font-bold hover:bg-orange-200/50 trash-row transition-colors" data-status="<?= $tmap['status'] ?>">
              <td class="p-3"><?= htmlspecialchars($tmap['map_name']) ?></td>
              <td class="p-3 trash-creator"><?= htmlspecialchars($tmap['creator_name']) ?></td>
              <td class="p-3 uppercase text-xs tracking-wider">
                <?php 
                  if ($tmap['status'] == 4) echo '<span class="text-red-600 bg-red-100 px-2 py-1 border border-red-600 rounded">Banned</span>';
                  elseif ($tmap['status'] == 3) echo '<span class="text-orange-600 bg-orange-100 px-2 py-1 border border-orange-600 rounded">Deleted</span>';
                  elseif ($tmap['status'] == 5) echo '<span class="text-gray-600 bg-gray-200 px-2 py-1 border border-gray-600 rounded">Scrapped</span>';
                  else echo '<span class="text-gray-600">Unknown</span>';
                ?>
              </td>
              <td class="p-3 flex gap-2">
                <button class="maps-restore-btn bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded-sm shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_rgba(0,0,0,1)] transition-all uppercase text-xs tracking-wider" data-mapid="<?= $tmap['id'] ?>">Restore</button>
                <button class="maps-edit-btn bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded-sm shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_rgba(0,0,0,1)] transition-all uppercase text-xs tracking-wider" data-mapid="<?= $tmap['id'] ?>">Edit</button>
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