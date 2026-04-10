<div class="maps-site">
  <div id="maps-main" class="maps-main-wrap">
    <section id="maps-wrapper" class="content-box maps-wrapper-section">
      <header class="maps-header">
        <div class="maps-header-top">
          <h2 id="maps-title">MAPS</h2>
          <button id="maps-mobile-menu-btn" class="maps-ham-btn maps-ham-btn-mobile" type="button" aria-label="Open maps menu">☰</button>
        </div>

        <div id="maps-controls-row" class="maps-controls-row maps-controls-row-collapsed">
          <input type="text" id="maps-search" placeholder="Search...">
          
          <div class="maps-sort-box">
            <button id="maps-sort-trigger" class="maps-sort-btn">
              <span id="maps-selected-sort">Downloads</span>
              <svg class="maps-sort-icon" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
            </button>
            <ul id="maps-sort-dropdown" class="maps-sort-dropdown maps-hidden">
              <li><button class="maps-dropdown-item" type="button">Downloads</button></li>
              <li><button class="maps-dropdown-item" type="button">Alphabetical</button></li>
              <li><button class="maps-dropdown-item" type="button">Most recent</button></li>
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
          
          <button id="maps-go-mymaps-btn" class="maps-help-btn" title="Go to my library!">🗺️ My Maps</button>
          
          <button id="maps-help-btn" class="maps-help-btn">?</button>
        </div>
      </header>

      <div class="maps-scroll-area">
        <div class="maps-grid">
         <?php if (empty($active_maps)): ?>
             <p id="live-maps-empty-msg" class="maps-empty-msg">No maps found. 🏝️</p>
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

                $cardBorderClass = $isCreatorEngineer ? 'maps-image-engineer' : 'maps-image-default';
                $nameClass = $isCreatorEngineer ? 'maps-map-name-engineer' : 'maps-map-name-default';
                $isInLibrary = !empty($map['is_in_library']);
              ?>
              
              <article class="maps-card" 
                       data-id="<?= $map['id'] ?>"
                       data-name="<?= htmlspecialchars(strtolower($map['map_name'])) ?>"
                       data-creator="<?= htmlspecialchars(strtolower($map['creator_name'])) ?>"
                       data-downloads="<?= (int)$map['downloads'] ?>"
                       data-date="<?= !empty($map['created_at']) ? strtotime($map['created_at']) : 0 ?>">
                       
                <div class="maps-image <?= $cardBorderClass ?>">
                  <img src="<?= htmlspecialchars($map['map_picture']) ?>" class="maps-image-img">
                  

                </div>

                <div class="maps-info">
                  <p class="map-name <?= $nameClass ?>"><?= htmlspecialchars($map['map_name']) ?></p>
                  <p class="maps-creator-name">
                    <?php if ($isCreatorEngineer): ?>🛠️<?php endif; ?> By: <?= htmlspecialchars($map['creator_name']) ?>
                  </p>
                  
                  <div class="maps-btns-stats">
                    
                    <?php if (!$isMyMap): ?>
                      <button class="maps-add-btn <?= $isInLibrary ? 'maps-add-btn-added' : 'maps-add-btn-available' ?>" data-mapid="<?= $map['id'] ?>" data-added="<?= $isInLibrary ? 'true' : 'false' ?>"><?= $isInLibrary ? 'Added ✔️' : '+ Add' ?></button>
                    <?php else: ?>
                        <span class="maps-own-badge">Own</span>
                    <?php endif; ?>
                    
                    <div class="maps-card-actions">
                        <?php if ($canEditOrDelete): ?>
                          <button class="maps-delete maps-delete-btn" data-mapid="<?= $map['id'] ?>" title="Delete">❌</button>
                        <?php elseif ($isCreatorEngineer && $isStaff): ?>
                          <span class="maps-protected-lock" title="Engineer által védett!">🔒</span>
                        <?php endif; ?>
                        <span class="maps-download-count">⬇ <span class="dl-number"><?= number_format($map['downloads']) ?></span></span>
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

<div id="maps-help-modal" class="maps-modal-overlay maps-hidden maps-flex-overlay">
  <div class="maps-modal-backdrop"></div>
  <div class="maps-modal-box maps-modal-box-help">
    <button class="maps-close-modal maps-modal-close">×</button>
    <h2 class="maps-modal-title-help">Help 🧭</h2>
    <ul class="maps-help-list">
      <li>🗺️ Add custom maps to your library!</li>
      <li>🔎 Use the sort buttons to see the most recent maps in the community library.</li>
      <li>🚧 Important: The editor is currently being developed! To create custom maps please contact us at: troxangame@gmail.com</li>
    </ul>
  </div>
</div>

<?php if ($isStaff): ?>
<div id="maps-trash-modal" class="maps-modal-overlay maps-hidden maps-flex-overlay">
  <div class="maps-modal-backdrop"></div>
  <div class="maps-modal-box maps-modal-box-trash">
    <button class="maps-close-modal maps-modal-close">×</button>
    
    <div class="maps-trash-head">
        <div class="maps-trash-toprow">
            <h2 class="maps-trash-title">🗑️ Admin Trash</h2>
            <input type="text" id="trash-search-input" placeholder="Search creator..." class="maps-trash-search-input">
        </div>
        
        <div class="maps-trash-filters">
            <label class="maps-trash-filter-label"><input type="checkbox" class="trash-filter-cb maps-trash-filter-cb" value="5" checked> Scrapped (User Draft)</label>
            <label class="maps-trash-filter-label"><input type="checkbox" class="trash-filter-cb maps-trash-filter-cb" value="3" checked> Deleted (User Pub)</label>
            <label class="maps-trash-filter-label"><input type="checkbox" class="trash-filter-cb maps-trash-filter-cb" value="4" checked> Banned (Admin)</label>
        </div>
    </div>
    
    <div class="maps-trash-scroll">
      <table class="maps-trash-table" id="trash-table">
        <thead>
          <tr class="maps-trash-head-row">
            <th class="maps-trash-th">Map Name</th>
            <th class="maps-trash-th">Creator</th>
            <th class="maps-trash-th">Status</th>
            <th class="maps-trash-th">Action</th>
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
            <tr class="maps-trash-row <?= $isTrashCreatorEngineer ? 'maps-trash-row-engineer' : '' ?>" data-status="<?= $tmap['status'] ?>">
              <td class="maps-trash-td"><?= htmlspecialchars($tmap['map_name']) ?></td>
              <td class="maps-trash-td trash-creator">
                <?php if ($isTrashCreatorEngineer): ?>🛠️<?php endif; ?> <?= htmlspecialchars($tmap['creator_name']) ?>
              </td>
              <td class="maps-trash-status-cell">
                <?php 
                  if ($tmap['status'] == 4) echo '<span class="maps-trash-status maps-trash-status-banned">Banned</span>';
                  elseif ($tmap['status'] == 3) echo '<span class="maps-trash-status maps-trash-status-deleted">Deleted</span>';
                  elseif ($tmap['status'] == 5) echo '<span class="maps-trash-status maps-trash-status-scrapped">Scrapped</span>';
                  else echo '<span class="maps-trash-status-unknown">Unknown</span>';
                ?>
              </td>
              <td class="maps-trash-action-cell">
                <?php if ($canRestore): ?>
                  <button class="maps-restore-btn maps-restore-btn-style" data-mapid="<?= $tmap['id'] ?>">Restore</button>
                <?php else: ?>
                  <span class="maps-trash-protected">🔒 Protected</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (empty($trash_maps)): ?>
        <p class="maps-trash-empty">The trash is currently empty.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="basesite-alert-modal" class="maps-modal-overlay maps-hidden maps-flex-overlay maps-modal-z-alert">
  <div class="maps-modal-backdrop"></div>
  <div class="maps-modal-box maps-modal-box-alert-msg">
    <div id="basesite-alert-header" class="maps-modal-alert-head">
      <h2 id="basesite-alert-title" class="maps-modal-alert-title">Notice</h2>
    </div>
    <p id="basesite-alert-message" class="maps-modal-alert-message">Message goes here</p>
    <button id="basesite-alert-ok-btn" class="maps-modal-alert-btn">OK</button>
  </div>
</div>

<div id="basesite-confirm-modal" class="maps-modal-overlay maps-hidden maps-flex-overlay maps-modal-z-confirm">
  <div class="maps-modal-backdrop"></div>
  <div class="maps-modal-box maps-modal-box-alert-msg">
    <div id="basesite-confirm-header" class="maps-modal-alert-head maps-modal-alert-head-danger">
      <h2 id="basesite-confirm-title" class="maps-modal-alert-title maps-modal-alert-title-danger">Confirmation</h2>
    </div>
    <p id="basesite-confirm-message" class="maps-modal-alert-message">Are you sure?</p>
    <div class="maps-confirm-actions">
      <button id="basesite-confirm-cancel-btn" class="maps-confirm-btn-cancel">Cancel</button>
      <button id="basesite-confirm-ok-btn" class="maps-confirm-btn-ok">Yes</button>
    </div>
  </div>
</div>