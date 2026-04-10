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

                <button id="profile-avatar-button" class="profile-avatar-trigger">
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

<p class="profile-last-updated-text">
    Last updated: 
    <span class="profile-last-updated-value" id="profile-last-updated-time"></span>
</p>

            <div id="profile-settings-modal-id" class="profile-modal-overlay profile-hidden">
                <div id="profile-settings-modal-backdrop" class="profile-modal-backdrop profile-backdrop-hidden"></div>
                <div id="profile-settings-modal-box-id" class="profile-modal-box profile-modal-box-hidden">
                    <button class="profile-close-btn" id="settings-close-btn">×</button>
                    <h2 class="profile-modal-title">Settings</h2>
                    <ul class="profile-settings-list">
                        <li><button id="btn-change-username" class="profile-list-btn">Change your username</button></li>
                        <li><button id="btn-change-password" class="profile-list-btn">Change your password</button></li>
                        <li class="profile-hidden"><button id="btn-change-email" class="profile-list-btn">Change your email</button></li>
                    </ul>
                </div>
            </div>

            <div id="profile-logout-modal-id" class="profile-modal-overlay profile-hidden">
                <div id="logout-modal-backdrop-id" class="profile-modal-backdrop profile-backdrop-hidden"></div>
                <div id="profile-logout-modal-box-id" class="profile-modal-box profile-modal-box-hidden">
                    <button class="profile-close-btn" id="logout-close-btn">×</button>
                    <h2 class="profile-modal-title profile-modal-title-danger">Logout Alert</h2>
                    <h3 class="profile-logout-message">You have been logged out!<br>The site will refresh in 3 seconds.</h3>
                </div>
            </div>

            <div id="profile-avatar-modal" class="profile-modal-overlay profile-hidden">
                <div id="avatar-modal-backdrop" class="profile-modal-backdrop profile-backdrop-hidden profile-transition-all"></div>
                <div id="profile-avatar-box" class="profile-modal-box profile-modal-box-hidden profile-transition-all">
                    <button class="profile-close-btn" id="avatar-close-btn">×</button>
                    <h2 class="profile-modal-title">Choose your Hero!</h2>
                    <div class="profile-avatar-grid">
                        <?php if (!empty($all_avatars)): ?>
                            <?php foreach ($all_avatars as $ava): ?>
                                <img data-avatar-id="<?= $ava['id'] ?>" src="data:image/jpeg;base64,<?= base64_encode($ava['avatar_picture']) ?>" class="profile-avatar-option profile-avatar-option-style" alt="<?= htmlspecialchars($ava['avatar_name']) ?>" title="<?= htmlspecialchars($ava['avatar_name']) ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="profile-avatar-empty">No avatars found in the database!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="basesite-alert-modal" class="profile-popup-overlay profile-hidden">
                <div class="profile-popup-backdrop"></div>
                <div class="profile-popup-box">
                    <div id="basesite-alert-header" class="profile-popup-head">
                    <h2 id="basesite-alert-title" class="profile-popup-title">Notice</h2>
                    </div>
                    <p id="basesite-alert-message" class="profile-popup-message">Message goes here</p>
                    <button id="basesite-alert-ok-btn" class="profile-popup-btn">OK</button>
                </div>
            </div>

            <div id="basesite-prompt-modal" class="profile-popup-overlay profile-hidden">
                <div class="profile-popup-backdrop"></div>
                <div class="profile-popup-box">
                    <div id="basesite-prompt-header" class="profile-popup-head">
                        <h2 id="basesite-prompt-title" class="profile-popup-title">Change Username</h2>
                    </div>
                    <input type="text" id="basesite-prompt-input" class="profile-popup-input profile-popup-input-spaced" placeholder="4-12 characters">
                    
                    <div class="profile-popup-actions">
                        <button id="basesite-prompt-cancel-btn" class="profile-popup-btn-cancel">Cancel</button>
                        <button id="basesite-prompt-ok-btn" class="profile-popup-btn-save">Save</button>
                    </div>
                </div>
            </div>

<div id="basesite-password-modal" class="profile-popup-overlay profile-hidden">
                <div id="basesite-password-backdrop" class="profile-popup-backdrop"></div>
                <div class="profile-popup-box profile-popup-box-left">
                    <div class="profile-popup-head profile-popup-head-center">
                        <h2 class="profile-popup-title">Change Password</h2>
                    </div>
                    
                    <p id="password-error-msg" class="profile-password-error profile-hidden"></p>
                    
                    <div class="profile-password-inputs">
                        <input type="password" id="pass-old" class="profile-popup-input" placeholder="Current Password">
                        <input type="password" id="pass-new" class="profile-popup-input" placeholder="New Password (min. 8 chars)">
                        <input type="password" id="pass-confirm" class="profile-popup-input" placeholder="Confirm New Password">
                    </div>
                    
                    <div class="profile-popup-actions">
                        <button id="basesite-password-cancel-btn" class="profile-popup-btn-cancel">Cancel</button>
                        <button id="basesite-password-save-btn" class="profile-popup-btn-save">Save</button>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>