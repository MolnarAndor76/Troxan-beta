<div class="p-2 md:p-6">
    <div class="admin-site-wrapper">

        <div class="admin-header">
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
                    $playtime = $stats['time_played'] ?? '0h 0m';

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
                    $roleBadgeClass = 'bg-orange-200 text-orange-900 border-orange-950';
                    if ($roleName === 'Engineer') {
                        $avatarClass = 'admin-avatar-engineer'; $nameClass = 'admin-username-engineer'; $roleBadgeClass = 'bg-cyan-100 text-cyan-900 border-cyan-950';
                    } elseif ($roleName === 'Admin') {
                        $avatarClass = 'admin-avatar-admin'; $nameClass = 'admin-username-admin';
                    } elseif ($roleName === 'Moderator') {
                        $avatarClass = 'border-purple-500'; $nameClass = 'bg-purple-200 text-purple-900 border-purple-900';
                    } else {
                        $avatarClass = 'admin-avatar-player'; $nameClass = 'admin-username-player';
                    }

                    $isBanned = (int)$player['is_banned'] === 1;
                    $cardOpacity = $isBanned ? 'opacity-50 grayscale' : '';
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
                                    <span class="text-lg font-extrabold tracking-wide text-left px-3 py-1 rounded-md border-2 opacity-80 cursor-not-allowed <?= $nameClass ?>" title="Védett profil!">
                                        <?= htmlspecialchars($player['username']) ?> 🔒
                                    </span>
                                <?php endif; ?>
                                <span class="admin-profile-role <?= $roleBadgeClass ?>"><?= htmlspecialchars($roleName) ?></span>
                                <?php if ($isBanned): ?><span class="text-xs font-bold text-red-600 mt-1 uppercase tracking-widest">⚠️ BANNED</span><?php endif; ?>
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
                                    <button class="admin-action-btn bg-green-500 text-white admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-action="unban" title="Unban Player">🕊️ Unban</button>
                                <?php else: ?>
                                    <button class="admin-action-btn admin-btn-red admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-action="ban" title="Ban Player">🔨 Ban</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($canViewDetails): ?>
                    <div id="details-modal-<?= $player['user_id'] ?>" class="admin-details-modal hidden">
                        <div class="admin-details-content">
                            <button class="admin-close-details-btn">✖</button>
                            <h2 class="text-xl md:text-2xl font-['Press_Start_2P',_monospace] text-center border-b-4 border-orange-950 pb-4 mb-4 text-orange-950">
                                <?= htmlspecialchars($player['username']) ?>
                            </h2>
                            <div class="flex flex-col gap-3 text-orange-950 font-bold text-base md:text-lg">
                                <p>📧 Email: <span class="font-normal"><?= htmlspecialchars($player['email']) ?></span></p>
                                <p>🗓️ Reg: <span class="font-normal"><?= date('Y-m-d', strtotime($player['created_at'])) ?></span></p>
                                <p>🟢 Last: <span class="font-normal"><?= $player['last_time_online'] ? date('Y-m-d H:i', strtotime($player['last_time_online'])) : 'Never' ?></span></p>
                                <p>⏱️ Play: <span class="font-normal"><?= $playtime ?></span></p>
                            </div>
                            <button class="admin-action-btn admin-btn-gray mt-8 w-full py-3 admin-view-logs-btn" data-userid="<?= $player['user_id'] ?>" data-username="<?= htmlspecialchars($player['username']) ?>">📜 View Logs</button>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<div id="global-logs-modal" class="admin-details-modal hidden">
    <div class="admin-details-content w-[95%] max-w-[600px] max-h-[90vh] flex flex-col">
        <button class="admin-close-logs-btn absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
        <h2 id="logs-modal-title" class="text-xl md:text-2xl font-['Press_Start_2P',_monospace] text-center border-b-4 border-orange-950 pb-4 mb-4 text-orange-950 shrink-0">Logs</h2>
        <div id="logs-container" class="flex flex-col gap-3 overflow-y-auto pr-2 pb-4"></div>
    </div>
</div>

<div id="basesite-alert-modal" class="admin-details-modal hidden" style="z-index: 9998;">
  <div class="admin-details-content !max-w-sm text-center">
    <div id="basesite-alert-header" class="border-b-4 border-orange-950 pb-2 mb-4">
      <h2 id="basesite-alert-title" class="text-xl font-bold text-orange-950">Notice</h2>
    </div>
    <button id="basesite-alert-close-btn" class="admin-close-details-btn">&times;</button>
    <p id="basesite-alert-message" class="text-lg font-bold text-gray-800 my-4">Üzenet helye</p>
    <button id="basesite-alert-ok-btn" class="admin-action-btn admin-btn-yellow mt-4 py-2 px-8">OK</button>
  </div>
</div>

<div id="basesite-confirm-modal" class="admin-details-modal hidden" style="z-index: 9999;">
  <div class="admin-details-content !max-w-sm text-center">
    <div id="basesite-confirm-header" class="border-b-4 border-red-950 pb-2 mb-4">
      <h2 id="basesite-confirm-title" class="text-xl font-bold text-red-600">Megerősítés</h2>
    </div>
    <button id="basesite-confirm-close-btn" class="admin-close-details-btn">&times;</button>
    <p id="basesite-confirm-message" class="text-lg font-bold text-gray-800 my-4">Biztos vagy benne?</p>
    <div class="flex justify-center gap-4 mt-6">
      <button id="basesite-confirm-cancel-btn" class="admin-action-btn admin-btn-gray py-2 px-6">Mégse</button>
      <button id="basesite-confirm-ok-btn" class="admin-action-btn admin-btn-red py-2 px-6">Igen</button>
    </div>
  </div>
</div>

<div id="admin-maps-modal" class="admin-details-modal hidden" style="z-index: 9995;">
    <div class="admin-details-content w-[95%] max-w-[1000px] h-[85vh] flex flex-col p-4 md:p-6 bg-orange-50 border-4 border-orange-950 rounded-xl shadow-[8px_8px_0px_rgba(0,0,0,1)]">
        
        <button class="admin-close-maps-btn absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
        
        <div class="flex flex-col md:flex-row justify-between items-center border-b-4 border-orange-950 pb-4 mb-4 shrink-0 gap-4 mt-6 md:mt-0">
            <h2 id="admin-maps-title" class="text-xl md:text-3xl font-['Press_Start_2P',_monospace] text-orange-950 truncate max-w-full">
                Player's Library
            </h2>
            
            <div class="flex items-center gap-2 bg-orange-200/50 p-2 rounded border-2 border-orange-950/20 font-bold text-orange-950 shadow-inner">
                <input type="checkbox" id="admin-maps-own-filter" class="w-5 h-5 accent-orange-600 cursor-pointer">
                <label for="admin-maps-own-filter" class="cursor-pointer tracking-wide uppercase text-xs md:text-sm">Only Created By Player</label>
            </div>
        </div>
        
        <div id="admin-maps-grid" class="flex-1 overflow-y-auto pr-2 pb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center auto-rows-max">
            </div>
        
    </div>
</div>