<?php
$lastSession   = troxan_format_db_datetime($user['last_time_online'] ?? null, 'Y-m-d H:i', 'Never');
$storyFinishes = troxan_get_stat_int($playerStats, ['num_of_story_finished', 'Story finished'], 0);
$enemiesKilled = troxan_get_stat_int($playerStats, ['num_of_enemies_killed', 'Mobs killed'], 0);
$deaths        = troxan_get_stat_int($playerStats, ['num_of_deaths', 'Deaths'], 0);
$score         = troxan_get_stat_score($playerStats);
$accountCreated = troxan_format_db_datetime($user['created_at'] ?? null, 'Y-m-d', '-');
$lastUpdatedText = troxan_format_db_datetime($lastUpdated, 'Y.m.d H:i', 'Never');
$hasAdminAccess = in_array($user['role_name'], ['Admin', 'Engineer']);
?>

<div class="profile-site">
    <div class="profile-main">
        <section id="profile-section">

            <h2 id="profile-title">Hello, <?= htmlspecialchars($user['username']) ?>!</h2>

            <div class="profile-stats-wrapper">

                <table id="profile-stats-table">
                    <thead>
                        <tr>
                            <th>Statistic</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Account created</td><td class="stat-value"><?= htmlspecialchars($accountCreated) ?></td></tr>
                        <tr><td>Last session</td><td class="stat-value"><?= htmlspecialchars($lastSession) ?></td></tr>
                        <tr><td>Story finishes</td><td class="stat-value"><?= $storyFinishes ?></td></tr>
                        <tr><td>Enemies killed</td><td class="stat-value"><?= $enemiesKilled ?></td></tr>
                        <tr><td>Deaths</td><td class="stat-value"><?= $deaths ?></td></tr>
                        <tr><td>Score</td><td class="stat-value"><?= $score ?></td></tr>
                        <tr><td>Leaderboard rank</td><td class="stat-value"><?= htmlspecialchars($leaderboardRank) ?></td></tr>
                    </tbody>
                </table>

                <button id="profile-avatar-button" class="group">
                    <?php if (!empty($user['avatar_picture'])): ?>
                        <img id="profile-avatar" src="data:image/jpeg;base64,<?= base64_encode($user['avatar_picture']) ?>" alt="Avatar">
                    <?php else: ?>
                        <img id="profile-avatar" src="https://picsum.photos/id/1025/600/400" alt="Avatar">
                    <?php endif; ?>
                    <span class="avatar-hover-text">Change Avatar</span>
                </button>

            </div>

            <div id="profile-buttons-container">
                <button class="profile-button btn-gray" id="profile-settings-button">⚙️ Settings</button>
                <button class="profile-button btn-yellow" id="profile-my-maps">🗺️ My Maps</button>

                <?php if ($hasAdminAccess): ?>
                    <button class="profile-button btn-blue" id="profile-admin-button">🛡️ Admin Area</button>
                <?php else: ?>
                    <button class="profile-button btn-blue" id="profile-avatar-button-alt">👤 Change Avatar</button>
                <?php endif; ?>

                <button class="profile-button btn-red" id="profile-log-out">🚪 Logout</button>
            </div>

<p class="text-sm text-gray-600 font-bold mt-2 text-center">
    Last updated: 
    <span class="text-orange-950" id="profile-last-updated-time"></span>
</p>

            <div id="profile-settings-modal-id" class="hidden fixed inset-0 z-50 items-center justify-center">
                <div id="profile-settings-modal-backdrop" class="absolute inset-0 bg-black/80 opacity-0 transition-opacity"></div>
                <div id="profile-settings-modal-box-id" class="profile-modal-box opacity-0 scale-95 translate-y-4">
                    <button class="profile-close-btn" id="settings-close-btn">×</button>
                    <h2 class="profile-modal-title">Settings</h2>
                    <ul class="profile-settings-list">
                        <li><button id="btn-change-username" class="profile-list-btn">Change your username</button></li>
                        <li><button id="btn-change-password" class="profile-list-btn">Change your password</button></li>
                        <li class="hidden"><button id="btn-change-email" class="profile-list-btn">Change your email</button></li>
                    </ul>
                </div>
            </div>

            <div id="profile-logout-modal-id" class="hidden fixed inset-0 z-50 items-center justify-center">
                <div id="logout-modal-backdrop-id" class="absolute inset-0 bg-black/80 opacity-0 transition-opacity"></div>
                <div id="profile-logout-modal-box-id" class="profile-modal-box opacity-0 scale-95 translate-y-4">
                    <button class="profile-close-btn" id="logout-close-btn">×</button>
                    <h2 class="profile-modal-title text-red-600 border-red-200">Logout Alert</h2>
                    <h3 class="text-lg font-bold text-gray-800 text-center mt-4">You have been logged out!<br>The site will refresh in 3 seconds.</h3>
                </div>
            </div>

            <div id="profile-avatar-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
                <div id="avatar-modal-backdrop" class="absolute inset-0 bg-black/80 opacity-0 transition-opacity duration-300"></div>
                <div id="profile-avatar-box" class="profile-modal-box opacity-0 scale-95 translate-y-4 transition-all duration-300">
                    <button class="profile-close-btn" id="avatar-close-btn">×</button>
                    <h2 class="profile-modal-title">Choose your Hero!</h2>
                    <div class="grid grid-cols-3 gap-4 mt-6 max-h-[40vh] overflow-y-auto p-2">
                        <?php if (!empty($all_avatars)): ?>
                            <?php foreach ($all_avatars as $ava): ?>
                                <img data-avatar-id="<?= $ava['id'] ?>" src="data:image/jpeg;base64,<?= base64_encode($ava['avatar_picture']) ?>" class="profile-avatar-option w-full aspect-square object-cover border-4 border-transparent hover:border-yellow-400 cursor-pointer rounded-sm shadow-md hover:scale-105 transition-all" alt="<?= htmlspecialchars($ava['avatar_name']) ?>" title="<?= htmlspecialchars($ava['avatar_name']) ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: white; grid-column: span 3; text-align: center;">No avatars found in the database!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="basesite-alert-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/80"></div>
                <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-sm text-center flex flex-col items-center">
                    <div id="basesite-alert-header" class="border-b-4 border-orange-950 w-full pb-2 mb-4">
                    <h2 id="basesite-alert-title" class="text-xl font-bold text-orange-950">Notice</h2>
                    </div>
                    <p id="basesite-alert-message" class="text-lg font-bold text-gray-800 my-4">Message goes here</p>
                    <button id="basesite-alert-ok-btn" class="bg-yellow-500 px-6 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] mt-4 hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all">OK</button>
                </div>
            </div>

            <div id="basesite-prompt-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/80"></div>
                <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-sm text-center flex flex-col items-center">
                    <div id="basesite-prompt-header" class="border-b-4 border-orange-950 w-full pb-2 mb-4">
                        <h2 id="basesite-prompt-title" class="text-xl font-bold text-orange-950">Change Username</h2>
                    </div>
                    <input type="text" id="basesite-prompt-input" class="w-full mt-4 p-3 border-4 border-orange-950 rounded bg-white text-gray-800 font-bold text-center focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/30 transition-all shadow-inner" placeholder="4-12 characters">
                    
                    <div class="flex justify-center gap-4 mt-6 w-full">
                        <button id="basesite-prompt-cancel-btn" class="flex-1 bg-gray-300 px-4 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all">Cancel</button>
                        <button id="basesite-prompt-ok-btn" class="flex-1 bg-yellow-500 px-4 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all">Save</button>
                    </div>
                </div>
            </div>

<div id="basesite-password-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
                <div id="basesite-password-backdrop" class="absolute inset-0 bg-black/80"></div>
                <div class="relative bg-orange-50 border-4 border-orange-950 p-6 rounded-xl shadow-2xl z-10 w-[90%] max-w-sm flex flex-col items-center">
                    <div class="border-b-4 border-orange-950 w-full pb-2 mb-4 text-center">
                        <h2 class="text-xl font-bold text-orange-950">Change Password</h2>
                    </div>
                    
                    <p id="password-error-msg" class="text-red-600 font-bold text-sm mb-2 text-center hidden"></p>
                    
                    <div class="w-full flex flex-col gap-3 mt-2">
                        <input type="password" id="pass-old" class="w-full p-3 border-4 border-orange-950 rounded bg-white text-gray-800 font-bold text-center focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/30 transition-all shadow-inner" placeholder="Current Password">
                        <input type="password" id="pass-new" class="w-full p-3 border-4 border-orange-950 rounded bg-white text-gray-800 font-bold text-center focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/30 transition-all shadow-inner" placeholder="New Password (min. 8 chars)">
                        <input type="password" id="pass-confirm" class="w-full p-3 border-4 border-orange-950 rounded bg-white text-gray-800 font-bold text-center focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/30 transition-all shadow-inner" placeholder="Confirm New Password">
                    </div>
                    
                    <div class="flex justify-center gap-4 mt-6 w-full">
                        <button id="basesite-password-cancel-btn" class="flex-1 bg-gray-300 px-4 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all">Cancel</button>
                        <button id="basesite-password-save-btn" class="flex-1 bg-yellow-500 px-4 py-2 font-bold text-orange-950 border-2 border-orange-950 rounded shadow-[2px_2px_0px_#000] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_#000] transition-all">Save</button>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>