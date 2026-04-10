<div class="mymaps-site">
  <div id="mymaps-main" class="mymaps-main-wrap">
    <section id="mymaps-wrapper" class="content-box mymaps-wrapper-section">
      <header class="mymaps-header">
        <div class="mymaps-header-top">
          <h2 id="mymaps-title">MY MAPS</h2>
          <button id="mymaps-mobile-menu-btn" class="mymaps-ham-btn mymaps-ham-btn-mobile" type="button" aria-label="Open my maps menu">☰</button>
        </div>

        <div id="mymaps-controls-row" class="mymaps-controls-row mymaps-controls-row-collapsed">
          <input type="text" id="mymaps-search" placeholder="Search my library...">
          
          <div class="mymaps-sort-box">
            <button id="mymaps-sort-trigger" class="mymaps-sort-btn">
              <span id="mymaps-selected-sort">Newest Added</span>
              <svg class="mymaps-sort-icon" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
            </button>
            <ul id="mymaps-sort-dropdown" class="mymaps-sort-dropdown mymaps-hidden">
              <li><button class="mymaps-dropdown-item" type="button">Newest Added</button></li>
              <li><button class="mymaps-dropdown-item" type="button">Oldest Added</button></li>
              <li><button class="mymaps-dropdown-item" type="button">Alphabetical</button></li>
            </ul>
          </div>

          <div class="mymaps-nav-buttons">
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
             <p id="live-mymaps-empty-msg" class="mymaps-empty-msg">Könyvtárad jelenleg üres! Adj hozzá pályákat a Maps menüből! 🏝️</p>
          <?php else: ?>
            <?php foreach ($my_maps as $map): ?>
              <?php 
                $isCreatorEngineer = ($map['creator_role'] === 'Engineer');
                $isMyMap = ($myUserId == $map['creator_user_id']);
                $canRenameMap = $isMyMap || $isStaff;
                $cardBorderClass = $isCreatorEngineer ? 'mymaps-image-engineer' : 'mymaps-image-default';
                $nameClass = $isCreatorEngineer ? 'mymaps-map-name-engineer' : 'mymaps-map-name-default';

                // Státusz jelvény (Badge) generálása
                $statusBadge = '';
                if ($isMyMap) {
                    if ($map['status'] == 1) $statusBadge = '<span class="mymaps-status-badge mymaps-status-published">Published</span>';
                    elseif ($map['status'] == 0) $statusBadge = '<span class="mymaps-status-badge mymaps-status-draft">Draft</span>';
                    elseif ($map['status'] == 3) $statusBadge = '<span class="mymaps-status-badge mymaps-status-unpublished">Unpublished</span>';
                }
              ?>
              
              <article class="mymaps-card" 
                       data-id="<?= $map['id'] ?>"
                       data-name="<?= htmlspecialchars(strtolower($map['map_name'])) ?>"
                       data-creator="<?= htmlspecialchars(strtolower($map['creator_name'])) ?>"
                       data-date="<?= !empty($map['added_at']) ? strtotime($map['added_at']) : 0 ?>">
                       
                <div class="mymaps-image <?= $cardBorderClass ?>">
                  <?= $statusBadge ?>
                  <img src="<?= htmlspecialchars($map['map_picture']) ?>" class="mymaps-image-img">
                </div>

                <div class="mymaps-info">
                  <p class="map-name <?= $nameClass ?>"><?= htmlspecialchars($map['map_name']) ?></p>
                  <p class="mymaps-creator-name">
                    <?php if ($isCreatorEngineer): ?>🛠️<?php endif; ?> By: <?= $isMyMap ? 'You' : htmlspecialchars($map['creator_name']) ?>
                  </p>
                  
                  <div class="mymaps-btns-stats">
                    <?php if ($canRenameMap): ?>
                        <button class="mymaps-edit-btn mymaps-btn-edit" data-mapid="<?= $map['id'] ?>">Edit</button>
                    <?php else: ?>
                        <button class="mymaps-edit-locked-btn mymaps-btn-edit-locked" data-mapid="<?= $map['id'] ?>" title="Only the creator or staff can rename this map">🔒</button>
                    <?php endif; ?>
                    
                    <?php if ($isMyMap): ?>
                        <?php if ($map['status'] == 1): ?>
                            <button class="mymaps-publish-btn mymaps-btn-unpublish" data-mapid="<?= $map['id'] ?>">Unpublish</button>
                        <?php else: ?>
                            <button class="mymaps-publish-btn mymaps-btn-publish" data-mapid="<?= $map['id'] ?>">Publish</button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <button class="mymaps-remove-btn mymaps-btn-remove" data-mapid="<?= $map['id'] ?>" title="Eltávolítás a könyvtárból">🗑️</button>
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

<div id="mymaps-alert-modal" class="mymaps-modal-overlay mymaps-hidden">
  <div class="mymaps-modal-backdrop"></div>
  <div class="mymaps-modal-box mymaps-modal-box-alert">
    <button id="mymaps-alert-close-btn" class="mymaps-modal-close">✖</button>
    <div id="mymaps-alert-header" class="mymaps-modal-header">
      <h2 id="mymaps-alert-title" class="mymaps-modal-title">Notice</h2>
    </div>
    <p id="mymaps-alert-message" class="mymaps-modal-message">Message</p>
    <button id="mymaps-alert-ok-btn" class="mymaps-modal-btn-ok">OK</button>
  </div>
</div>

<div id="mymaps-confirm-modal" class="mymaps-modal-overlay mymaps-hidden">
  <div class="mymaps-modal-backdrop"></div>
  <div class="mymaps-modal-box mymaps-modal-box-confirm">
    <button id="mymaps-confirm-close-btn" class="mymaps-modal-close">✖</button>
    <div id="mymaps-confirm-header" class="mymaps-modal-header mymaps-modal-header-danger">
      <h2 id="mymaps-confirm-title" class="mymaps-modal-title mymaps-modal-title-danger">Confirmation</h2>
    </div>
    <p id="mymaps-confirm-message" class="mymaps-modal-message">Are you sure?</p>
    <div class="mymaps-modal-actions">
      <button id="mymaps-confirm-cancel-btn" class="mymaps-modal-btn-cancel">Cancel</button>
      <button id="mymaps-confirm-ok-btn" class="mymaps-modal-btn-confirm">Confirm</button>
    </div>
  </div>
</div>

<div id="mymaps-rename-modal" class="mymaps-modal-overlay mymaps-hidden">
  <div class="mymaps-modal-backdrop"></div>
  <div class="mymaps-modal-box mymaps-modal-box-rename">
    <button id="mymaps-rename-close-btn" class="mymaps-modal-close">✖</button>
    <h2 class="mymaps-rename-title">Rename Map</h2>
    <p class="mymaps-rename-current">Current name: <span id="mymaps-rename-old-name" class="mymaps-rename-old-name"></span></p>
    <input id="mymaps-rename-input" type="text" class="mymaps-rename-input" placeholder="New map name">
    <div class="mymaps-rename-actions">
      <button id="mymaps-rename-cancel-btn" class="mymaps-modal-btn-cancel">Cancel</button>
      <button id="mymaps-rename-save-btn" class="mymaps-modal-btn-save">Save</button>
    </div>
  </div>
</div>