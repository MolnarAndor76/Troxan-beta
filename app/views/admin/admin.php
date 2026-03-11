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
                <?php foreach ($users as $player): ?>
                    <?php
                    $stats = !empty($player['statistics_file']) ? json_decode($player['statistics_file'], true) : [];
                    $playtime = $stats['time_played'] ?? '0h 0m';

                    $avatarSrc = 'https://picsum.photos/id/1025/100/100';
                    if (!empty($player['avatar_picture'])) {
                        $avatarSrc = 'data:image/jpeg;base64,' . base64_encode($player['avatar_picture']);
                    }

                    $roleName = $player['role_name'];

                    if ($roleName === 'Admin') {
                        $avatarClass = 'admin-avatar-admin';
                        $nameClass   = 'admin-username-admin';
                    } elseif ($roleName === 'Moderator') {
                        $avatarClass = 'border-purple-500';
                        $nameClass   = 'bg-purple-200 text-purple-900 border-purple-900';
                    } else {
                        $avatarClass = 'admin-avatar-player';
                        $nameClass   = 'admin-username-player';
                    }

                    $isBanned = (int)$player['is_banned'] === 1;
                    $cardOpacity = $isBanned ? 'opacity-50 grayscale' : '';
                    ?>

                    <div class="admin-user-card <?= $cardOpacity ?>">

                        <div class="admin-card-profile">
                            <img src="<?= $avatarSrc ?>" class="admin-avatar <?= $avatarClass ?>" alt="Avatar">
                            <div class="admin-profile-details">
                                <button class="admin-username-btn <?= $nameClass ?>" data-userid="<?= $player['user_id'] ?>">
                                    <?= htmlspecialchars($player['username']) ?>
                                </button>
                                <span class="admin-profile-role"><?= htmlspecialchars($roleName) ?></span>
                                
                                <?php if ($isBanned): ?>
                                    <span class="text-xs font-bold text-red-600 mt-1 uppercase tracking-widest">⚠️ BANNED</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button class="admin-hamburger-btn">☰</button>

                        <div id="actions-<?= $player['user_id'] ?>" class="admin-card-actions">
                            <button class="admin-close-menu-btn">✖</button>
                            <button class="admin-action-btn admin-btn-yellow" title="Maps">🗺️ Maps</button>

                            <?php if ($roleName === 'Player'): ?>
                                <button class="admin-action-btn admin-btn-blue admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="promote" title="Promote to Moderator">⬆️ Promote</button>
                            <?php elseif ($roleName === 'Moderator'): ?>
                                <button class="admin-action-btn admin-btn-blue admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="promote" title="Promote to Admin">⬆️ Promote</button>
                                <button class="admin-action-btn admin-btn-gray admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="demote" title="Demote to Player">⬇️ Demote</button>
                            <?php elseif ($roleName === 'Admin'): ?>
                                <button class="admin-action-btn admin-btn-gray admin-role-btn" data-userid="<?= $player['user_id'] ?>" data-action="demote" title="Demote to Moderator">⬇️ Demote</button>
                            <?php endif; ?>

                            <?php if ($isBanned): ?>
                                <button class="admin-action-btn bg-green-500 text-white admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-action="unban" title="Unban Player">🕊️ Unban</button>
                            <?php else: ?>
                                <button class="admin-action-btn admin-btn-red admin-ban-toggle-btn" data-userid="<?= $player['user_id'] ?>" data-action="ban" title="Ban Player">🔨 Ban</button>
                            <?php endif; ?>
                        </div>

                    </div>

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

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<div id="global-logs-modal" class="admin-details-modal hidden">
    <div class="admin-details-content w-[95%] max-w-[600px] max-h-[90vh] flex flex-col">
        <button class="admin-close-logs-btn absolute top-3 right-4 text-3xl text-orange-950 font-black cursor-pointer hover:text-red-600 transition-colors z-20">✖</button>
        
        <h2 id="logs-modal-title" class="text-xl md:text-2xl font-['Press_Start_2P',_monospace] text-center border-b-4 border-orange-950 pb-4 mb-4 text-orange-950 shrink-0">
            Logs
        </h2>
        
        <div id="logs-container" class="flex flex-col gap-3 overflow-y-auto pr-2 pb-4">
            </div>
    </div>
</div>