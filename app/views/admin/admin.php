<div class="admin-page-shell">
    <div class="admin-site-wrapper">

        <div class="admin-header">
            <button id="admin-back-btn" class="admin-action-btn admin-btn-gray admin-back-btn">Back</button>
            <h1 class="admin-title">🛡️ ADMIN AREA</h1>

            <div class="admin-search-wrapper">
                <input type="text" id="admin-search-input" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Search player..." class="admin-search-input">
                <button id="admin-search-btn" class="admin-search-btn">Search</button>
            </div>
        </div>

        <div class="admin-list">

            <?php if (empty($users)): ?>
                <p class="admin-empty-msg">No players found.</p>
            <?php else: ?>
                <?php 
                    $myRole = $_SESSION['role_name'] ?? 'Admin'; 
                    $iAmEngineer = ($myRole === 'Engineer');
                    $myUserId = $_SESSION['user_id'] ?? 0;
                ?>
                
                <?php foreach ($users as $player): ?>
                    <?php
                    $stats = !empty($player['statistics_file']) ? json_decode($player['statistics_file'], true) : [];
                    $playtime = troxan_get_stat_playtime($stats);

                    $avatarSrc = 'https://picsum.photos/id/1025/100/100';
                    if (!empty($player['avatar_picture'])) {
                        $avatarSrc = 'data:image/jpeg;base64,' . base64_encode($player['avatar_picture']);
                    }

                    $roleName = $player['role_name'];
                    $isTargetEngineer = ($roleName === 'Engineer');
                    $isTargetAdmin = ($roleName === 'Admin');
                    $isTargetMe = ($player['user_id'] == $myUserId);
                    
                    // LÁTHATÓSÁG (Logok, Kattintható név)
                    $canViewDetails = true;
                    if ($isTargetEngineer && !$iAmEngineer && !$isTargetMe) $canViewDetails = false;
                    if ($isTargetAdmin && !$iAmEngineer && !$isTargetMe) $canViewDetails = false;

                    // MÓDOSÍTHATÓSÁG (Ban, Promote, Demote)
                    $canModify = true;
                    if ($isTargetMe) $canModify = false; // Magadat nem
                    if ($isTargetEngineer) $canModify = false; // Engineert senki se
                    if ($isTargetAdmin && !$iAmEngineer) $canModify = false; // Admint csak Engineer

                    // Színek
                    $roleBadgeClass = 'admin-role-badge-player';
                    if ($roleName === 'Engineer') {
                        $avatarClass = 'admin-avatar-engineer'; $nameClass = 'admin-username-engineer'; $roleBadgeClass = 'admin-role-badge-engineer';
                    } elseif ($roleName === 'Admin') {
                        $avatarClass = 'admin-avatar-admin'; $nameClass = 'admin-username-admin';
                    } elseif ($roleName === 'Moderator') {
                        $avatarClass = 'admin-avatar-moderator'; $nameClass = 'admin-username-moderator';
                    } else {
                        $avatarClass = 'admin-avatar-player'; $nameClass = 'admin-username-player';
                    }

                    $isBanned = (int)$player['is_banned'] === 1;
                    $cardOpacity = $isBanned ? 'admin-user-card-banned' : '';
                    ?>

                    <div class="admin-user-card <?= $cardOpacity ?>">
                        <div class="admin-card-profile">
                            <img src="<?= $avatarSrc ?>" class="admin-avatar <?= $avatarClass ?>" alt="Avatar">
                            <div class="admin-profile-details">
                                <?php if ($canViewDetails): ?>
                                    <button class="admin-username-btn <?= $nameClass ?>" data-userid="<?= $player['user_id'] ?>">
                                        <?= htmlspecialchars($player['username']) ?>
                                    </button>
                                <?php else: ?>
                                    <span class="admin-username-protected <?= $nameClass ?>" title="Protected profile!">
                                        <?= htmlspecialchars($player['username']) ?> 🔒
                                    </span>
                                <?php endif; ?>
                                <span class="admin-profile-role <?= $roleBadgeClass ?>"><?= htmlspecialchars($roleName) ?></span>
                                <?php if ($isBanned): ?><span class="admin-banned-badge">⚠️ BANNED</span><?php endif; ?>
                            </div>
                        </div>

                        <button class="admin-hamburger-btn">☰</button>

                        <div id="actions-<?= $player['user_id'] ?>" class="admin-card-actions">
                            <button class="admin-close-menu-btn">✖</button>
                            
                            <?php if ($canViewDetails): ?>
                                <button class="admin-action-btn admin-btn-yellow admin-maps-open-btn" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>" title="Maps">🗺️ Maps</button>
                            <?php endif; ?>

                            <?php if ($canModify): ?>
                                <?php if ($roleName === 'Player'): ?>
                                    <button class="admin-action-btn admin-btn-blue admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="promote" title="Promote to Moderator">⬆️ Promote</button>
                                
                                <?php elseif ($roleName === 'Moderator'): ?>
                                    <?php if ($iAmEngineer): ?>
                                        <button class="admin-action-btn admin-btn-blue admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="promote" title="Promote to Admin">⬆️ Promote</button>
                                    <?php endif; ?>
                                    <button class="admin-action-btn admin-btn-gray admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="demote" title="Demote to Player">⬇️ Demote</button>
                                
                                <?php elseif ($roleName === 'Admin'): ?>
                                    <button class="admin-action-btn admin-btn-gray admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="demote" title="Demote to Moderator">⬇️ Demote</button>
                                <?php endif; ?>

                                <?php if ($isBanned): ?>
                                    <button class="admin-action-btn admin-btn-green admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>" data-action="unban" title="Unban Player">🕊️ Unban</button>
                                <?php else: ?>
                                    <button class="admin-action-btn admin-btn-red admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>" data-action="ban" title="Ban Player">🔨 Ban</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($canViewDetails): ?>
                    <div id="details-modal-<?= $player['user_id'] ?>" class="admin-details-modal admin-hidden">
                        <div class="admin-details-content">
                            <button class="admin-close-details-btn">✖</button>
                            <h2 class="admin-details-title">
                                <?= htmlspecialchars($player['username']) ?>
                            </h2>
                            <div class="admin-details-info-list">
                                <p>📧 Email: <span class="admin-details-info-value"><?= htmlspecialchars($player['email']) ?></span></p>
                                <p>🗓️ Reg: <span class="admin-details-info-value"><?= troxan_format_db_datetime($player['created_at'], 'Y-m-d', '-') ?></span></p>
                                <p>🟢 Last: <span class="admin-details-info-value"><?= troxan_format_db_datetime($player['last_time_online'], 'Y-m-d H:i', 'Never') ?></span></p>
                                <p>⏱️ Play: <span class="admin-details-info-value"><?= $playtime ?></span></p>
                                <p>🔁 Last username change: <span class="admin-details-info-value"><?= troxan_format_db_datetime($player['last_username_change'], 'Y-m-d H:i', 'N/A') ?></span></p>
                                <p>🔐 Last password change: <span class="admin-details-info-value"><?= troxan_format_db_datetime($player['last_password_change'], 'Y-m-d H:i', 'N/A') ?></span></p>
                            </div>

                            <div class="admin-change-name-section admin-section-top-divider">
                                <button class="admin-action-btn admin-btn-blue admin-action-full admin-action-medium admin-change-name-open-btn" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>">📝 Change username</button>
                            </div>

                            <button class="admin-action-btn admin-btn-gray admin-action-full admin-action-large admin-view-logs-btn admin-mt-8" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>">📜 View Logs</button>

                            <?php if ($canModify): ?>
                                <button class="admin-action-btn admin-btn-red admin-action-full admin-action-large admin-hard-delete-open-btn admin-mt-3" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>">🗑️ DELETE PROFILE</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<div id="admin-change-username-modal" class="admin-details-modal admin-hidden">
    <div class="admin-details-content admin-details-content-sm">
        <button class="admin-close-details-btn admin-close-details-btn-fixed">✖</button>
        <h2 class="admin-modal-subtitle">Change Username</h2>
        <p class="admin-modal-target">Target: <span id="change-username-target"></span></p>
        <input id="change-username-input" type="text" class="admin-form-input" placeholder="New username (4-12 chars)">
        <textarea id="change-username-reason-input" class="admin-form-input admin-form-textarea" placeholder="Reason (required)"></textarea>
        <button id="change-username-confirm-btn" class="admin-action-btn admin-btn-blue admin-action-full admin-action-medium">Change Name</button>
    </div>
</div>

<div id="admin-ban-reason-modal" class="admin-details-modal admin-hidden admin-modal-z-ban">
    <div class="admin-details-content admin-details-content-sm">
        <button class="admin-close-details-btn admin-close-details-btn-fixed">✖</button>
        <h2 class="admin-modal-subtitle" id="ban-reason-title">Ban Player</h2>
        <p class="admin-modal-target">Target: <span id="ban-reason-target"></span></p>
        <textarea id="ban-reason-input" class="admin-form-input admin-form-textarea" placeholder="Reason (required)"></textarea>
        <button id="ban-reason-confirm-btn" class="admin-action-btn admin-btn-red admin-action-full admin-action-medium">Confirm Ban</button>
    </div>
</div>
<div id="admin-rename-map-modal" class="admin-details-modal admin-hidden admin-modal-z-rename">
    <div class="admin-details-content admin-details-content-sm">
        <button class="admin-close-details-btn admin-close-details-btn-fixed">✖</button>
        <h2 class="admin-modal-subtitle">Rename Map</h2>
        <p class="admin-modal-target">Old name: <span id="admin-rename-map-old-name"></span></p>
        <input id="admin-rename-map-input" type="text" class="admin-form-input" placeholder="New map name (1-64 chars)">
        <button id="admin-rename-map-confirm-btn" class="admin-action-btn admin-btn-blue admin-action-full admin-action-medium">Rename</button>
    </div>
</div>

<div id="admin-hard-delete-modal" class="admin-details-modal admin-hidden admin-modal-z-hard-delete">
    <div class="admin-details-content admin-details-content-sm">
        <button class="admin-close-details-btn admin-close-details-btn-fixed">✖</button>
        <h2 class="admin-modal-subtitle admin-modal-subtitle-danger">Hard Delete Profile</h2>
        <p class="admin-modal-target">Target: <span id="admin-hard-delete-target"></span></p>
        <p class="admin-modal-warning">Type CONFIRM to permanently delete this account.</p>
        <input id="admin-hard-delete-input" type="text" class="admin-form-input admin-form-input-danger" placeholder="CONFIRM">
        <button id="admin-hard-delete-confirm-btn" class="admin-action-btn admin-btn-red admin-action-full admin-action-medium">Permanently Delete</button>
    </div>
</div>
<div id="global-logs-modal" class="admin-details-modal admin-hidden">
    <div class="admin-details-content admin-logs-content">
        <button class="admin-close-logs-btn">✖</button>
        <h2 id="logs-modal-title" class="admin-details-title admin-details-title-shrink">Logs</h2>
        <div class="admin-logs-filters">
            <label class="admin-logs-filter-label">From:</label>
            <input id="logs-date-from" type="date" class="admin-logs-date-input">
            <label class="admin-logs-filter-label">To:</label>
            <input id="logs-date-to" type="date" class="admin-logs-date-input">
            <button id="logs-date-filter-btn" class="admin-action-btn admin-btn-yellow admin-action-tight">Filter</button>
            <button id="logs-date-clear-btn" class="admin-action-btn admin-btn-gray admin-action-tight">Clear</button>
        </div>
        <div id="logs-container" class="admin-logs-container"></div>
    </div>
</div>

<div id="basesite-alert-modal" class="admin-details-modal admin-hidden admin-modal-z-alert">
  <div class="admin-details-content admin-details-content-alert">
    <div id="basesite-alert-header" class="admin-modal-alert-header">
      <h2 id="basesite-alert-title" class="admin-modal-alert-title">Notice</h2>
    </div>
    <button id="basesite-alert-close-btn" class="admin-close-details-btn">&times;</button>
    <p id="basesite-alert-message" class="admin-modal-alert-message">Message goes here</p>
    <button id="basesite-alert-ok-btn" class="admin-action-btn admin-btn-yellow admin-alert-btn">OK</button>
  </div>
</div>

<div id="basesite-confirm-modal" class="admin-details-modal admin-hidden admin-modal-z-confirm">
  <div class="admin-details-content admin-details-content-alert">
    <div id="basesite-confirm-header" class="admin-modal-alert-header admin-modal-alert-header-danger">
      <h2 id="basesite-confirm-title" class="admin-modal-alert-title admin-modal-alert-title-danger">Confirmation</h2>
    </div>
    <button id="basesite-confirm-close-btn" class="admin-close-details-btn">&times;</button>
    <p id="basesite-confirm-message" class="admin-modal-alert-message">Are you sure?</p>
    <div class="admin-confirm-actions">
      <button id="basesite-confirm-cancel-btn" class="admin-action-btn admin-btn-gray admin-confirm-btn">Cancel</button>
      <button id="basesite-confirm-ok-btn" class="admin-action-btn admin-btn-red admin-confirm-btn">Confirm</button>
    </div>
  </div>
</div>

<div id="admin-maps-modal" class="admin-details-modal admin-hidden admin-modal-z-maps">
    <div class="admin-details-content admin-maps-content">
        
        <button class="admin-close-maps-btn">✖</button>
        
        <div class="admin-maps-head">
            <h2 id="admin-maps-title" class="admin-maps-title">
                Player's Library
            </h2>
            
            <div class="admin-maps-filter-box">
                <input type="checkbox" id="admin-maps-own-filter" class="admin-maps-own-filter-input">
                <label for="admin-maps-own-filter" class="admin-maps-own-filter-label">Only Created By Player</label>
            </div>
        </div>
        
        <div id="admin-maps-grid" class="admin-maps-grid">
            </div>
        
    </div>
</div>