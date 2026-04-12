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
        <iframe id="basesite-trailer-iframe" src="<?= htmlspecialchars($siteSettings['trailer_url']) ?>" class="basesite-video-iframe" title="YouTube video player" frameborder="0" allow="autoplay; encrypted-media" loading="lazy"></iframe>
      </div>
      <div class="basesite-dl-layout">
        <button class="basesite-dl-btn" id="basesite-download-game-btn" data-loggedin="<?= $isLoggedIn ?>" data-download-url="<?= htmlspecialchars($siteSettings['download_url']) ?>">Download Game</button>
        <button id="basesite-open-req-btn" class="basesite-link">View System requirements</button>
      </div>
    </section>

    <section id="basesite-tab-patchnotes" class="basesite-tab-content basesite-hidden">
      <div class="basesite-patchnotes-head">
        <h2 class="basesite-patchnotes-title">What's new?</h2>
        <?php if ($canEditPatchNotes): ?>
          <div class="basesite-patchnotes-actions">
            <button id="patch-recycle-btn" class="basesite-patchnotes-btn basesite-patchnotes-btn-recycle">🗑️ Recycle Bin</button>
            <button id="patch-new-btn" class="basesite-patchnotes-btn basesite-patchnotes-btn-new">➕ New Patch</button>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($patchNotes)): ?>
        <div class="basesite-patchnotes-list">
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

              $cardClass = $isEngineerPatch ? 'basesite-patch-card-engineer' : 'basesite-patch-card-admin';
              $titleClass = $isEngineerPatch ? 'basesite-patch-title-engineer' : 'basesite-patch-title-admin';
              $timeClass = $isEngineerPatch ? 'basesite-patch-meta-engineer' : 'basesite-patch-meta-admin';
              $borderClass = $isEngineerPatch ? 'basesite-patch-head-engineer' : 'basesite-patch-head-admin';
          ?>

            <div class="basesite-patch-card <?= $cardClass ?>" data-id="<?= $patch['id'] ?>">

              <div class="basesite-patch-card-head <?= $borderClass ?>">
                <div>
                    <h3 class="basesite-patch-card-title <?= $titleClass ?> patch-title"><?= htmlspecialchars($patch['name']) ?></h3>
                    <?php if ($patch['author_name']): ?>
                        <div class="basesite-patch-author <?= $timeClass ?>">
                            <?= $isEngineerPatch ? '🛠️ Engineer:' : '👤 Admin:' ?> <?= htmlspecialchars($patch['author_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <time class="basesite-patch-time <?= $timeClass ?>" datetime="<?= $patch['created_at'] ?>">
                  <?= date('Y.m.d H:i', strtotime($patch['created_at'])) ?>
                </time>
              </div>

              <div class="basesite-patch-desc patch-desc">
                <?= nl2br(htmlspecialchars($patch['description'])) ?>
              </div>

              <?php if (!empty($patch['updated_by']) && !empty($patch['updater_name'])): ?>
                  <div class="basesite-patch-updated <?= $timeClass ?>">
                    Last updated by <?= htmlspecialchars($patch['updater_name']) ?> (<?= date('Y.m.d H:i', strtotime($patch['updated_at'])) ?>)
                  </div>
              <?php endif; ?>

              <div class="basesite-patch-actions">
                
                <?php if ($iAmEngineer): ?>
                    <button class="patch-lock-btn basesite-icon-action-btn" title="<?= $isLocked ? 'Unlock patch' : 'Lock patch' ?>">
                        <?= $isLocked ? '🔒' : '🔓' ?>
                    </button>
                <?php elseif ($isLocked && $canEditPatchNotes): ?>
                    <span class="basesite-icon-locked" title="Engineer által lezárva!">🔒</span>
                <?php endif; ?>

                <?php if ($canEditThisPatch): ?>
                  <button class="patch-edit-btn basesite-icon-action-btn" title="Edit Patch">✏️</button>
                  <button class="patch-delete-btn basesite-icon-action-btn" title="Move to Recycle Bin">🗑️</button>
                <?php endif; ?>

              </div>

            </div>

          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="basesite-placeholder-box basesite-patch-empty">
          There are no patch notes available yet. Check back later!
        </div>
      <?php endif; ?>
    </section>

    <section id="basesite-tab-lore" class="basesite-tab-content basesite-hidden">
      <h2 class="basesite-title-sub">The fate of troxan</h2>
      <p class="basesite-text" id="basesite-lore-text"><?= nl2br(htmlspecialchars($siteSettings['lore_text'])) ?></p>
      <textarea id="site-settings-lore-source" class="basesite-hidden"><?= htmlspecialchars($siteSettings['lore_text']) ?></textarea>
    </section>

    <section id="basesite-tab-about" class="basesite-tab-content basesite-hidden">
      <div class="basesite-about-head">
        <h2 class="basesite-title-sub">About Troxan and us</h2>
        <?php if ($canEditSiteSettings): ?>
          <button id="site-settings-edit-btn" class="basesite-icon-action-btn" title="Edit Main Page Settings">✏️</button>
        <?php endif; ?>
      </div>
      <p class="basesite-text" id="basesite-about-us-text"><?= nl2br(htmlspecialchars($siteSettings['about_us_text'])) ?></p>
      <p class="basesite-text">Feel free to email us at <a href="mailto:troxangame@email.com" class="basesite-link">troxangame@email.com</a></p>
      <div class="basesite-about-box">
        <h3 class="basesite-about-title">Special thanks to our artists:</h3>
        <ul class="basesite-about-list" id="basesite-special-thanks-list">
          <?php foreach (array_filter(array_map('trim', explode("\n", $siteSettings['special_thanks_text']))) as $line): ?>
            <li><?= nl2br(htmlspecialchars($line)) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <textarea id="site-settings-system-req-source" class="basesite-hidden"><?= htmlspecialchars($siteSettings['system_requirements_text']) ?></textarea>
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
              <?php if (!empty($systemRequirementsRows)): ?>
                <?php foreach ($systemRequirementsRows as $row): ?>
                  <tr class="basesite-tr-split">
                    <td class="basesite-td-hide-mobile"><?= htmlspecialchars($row['component']) ?></td>
                    <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top"><?= htmlspecialchars($row['component']) ?></span><span class="basesite-mobile-label-inline">Min: </span><?= htmlspecialchars($row['minimum']) ?></td>
                    <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span><?= htmlspecialchars($row['recommended']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr class="basesite-tr-split">
                  <td class="basesite-td-hide-mobile">N/A</td>
                  <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-top">N/A</span><span class="basesite-mobile-label-inline">Min: </span>N/A</td>
                  <td class="basesite-td-block-mobile"><span class="basesite-mobile-label-yellow">Ajánlott: </span>N/A</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="patch-new-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window basesite-modal-window-new-patch">
        <div class="basesite-modal-header basesite-modal-header-new-patch">
          <h2 class="basesite-modal-title basesite-modal-title-new-patch">➕ Create New Patch</h2>
          <button class="basesite-modal-close patch-close-btn">&times;</button>
        </div>
        <div class="basesite-modal-body basesite-modal-body-orange">
          <input type="text" id="new-patch-title" class="basesite-form-input-patch" placeholder="Patch Name (e.g. Patch 1.2 - The Awakening)">
          <textarea id="new-patch-desc" class="basesite-form-textarea-patch" placeholder="Write the patch notes here..."></textarea>
          <div class="basesite-modal-action-row">
            <button id="patch-discard-btn" class="basesite-btn-danger">Discard</button>
            <button id="patch-publish-btn" class="basesite-btn-success">Publish</button>
          </div>
        </div>
      </div>
    </div>

    <div id="patch-recycle-modal" class="basesite-modal-overlay basesite-hidden">
      <div class="basesite-modal-window basesite-modal-window-recycle">
        <div class="basesite-modal-header basesite-modal-header-recycle">
          <h2 class="basesite-modal-title basesite-modal-title-recycle">🗑️ Recycle Bin</h2>
          <button class="basesite-modal-close patch-close-btn">&times;</button>
        </div>
        <div class="basesite-modal-body basesite-modal-body-recycle" id="recycle-bin-content">
          <div class="basesite-empty-state">Loading deleted patches...</div>
        </div>
      </div>
    </div>

    <div id="basesite-alert-modal" class="basesite-modal-overlay basesite-hidden basesite-modal-z-alert">
      <div class="basesite-modal-window basesite-modal-window-sm">
        <div id="basesite-alert-header" class="basesite-modal-header basesite-modal-header-alert">
          <h2 id="basesite-alert-title" class="basesite-modal-title basesite-modal-title-white">Notice</h2>
          <button id="basesite-alert-close-btn" class="basesite-modal-close">&times;</button>
        </div>
        <div class="basesite-modal-body basesite-modal-body-center">
          <p id="basesite-alert-message" class="basesite-message-alert">Message goes here</p>
          <button id="basesite-alert-ok-btn" class="basesite-btn-warning">OK</button>
        </div>
      </div>
    </div>

    <div id="basesite-confirm-modal" class="basesite-modal-overlay basesite-hidden basesite-modal-z-confirm">
      <div class="basesite-modal-window basesite-modal-window-sm">
        <div id="basesite-confirm-header" class="basesite-modal-header basesite-modal-header-confirm">
          <h2 id="basesite-confirm-title" class="basesite-modal-title basesite-modal-title-white">Confirmation</h2>
          <button id="basesite-confirm-close-btn" class="basesite-modal-close">&times;</button>
        </div>
        <div class="basesite-modal-body basesite-modal-body-center">
          <p id="basesite-confirm-message" class="basesite-message-alert">Are you sure?</p>
          <div class="basesite-confirm-actions">
            <button id="basesite-confirm-cancel-btn" class="basesite-btn-neutral">Cancel</button>
            <button id="basesite-confirm-ok-btn" class="basesite-btn-danger">Confirm</button>
          </div>
        </div>
      </div>
    </div>

  </div> 
</div>