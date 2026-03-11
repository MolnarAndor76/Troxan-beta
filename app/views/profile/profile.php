<?php
// JSON adatok kimentése változókba (Ha nincs még statisztika, alapértékeket adunk)
$joinTime      = $playerStats['join_time'] ?? '-';
$storyFinishes = $playerStats['num_of_story_finished'] ?? 0;
$enemiesKilled = $playerStats['num_of_enemies_killed'] ?? 0;
$deaths        = $playerStats['num_of_deaths'] ?? 0;
$score         = $playerStats['score'] ?? 0;

// Adatbázisból jövő regisztrációs dátum
$accountCreated = !empty($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '-';

// Dátum formázása az alsó sarokhoz (last_updated oszlop)
$lastUpdatedText = !empty($lastUpdated) ? date('Y.m.d H:i', strtotime($lastUpdated)) : 'Never';
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
                        <tr>
                            <td>Account created</td>
                            <td class="stat-value"><?= htmlspecialchars($accountCreated) ?></td>
                        </tr>
                        <tr>
                            <td>Last session</td>
                            <td class="stat-value"><?= htmlspecialchars($joinTime) ?></td>
                        </tr>
                        <tr>
                            <td>Story finishes</td>
                            <td class="stat-value"><?= $storyFinishes ?></td>
                        </tr>
                        <tr>
                            <td>Enemies killed</td>
                            <td class="stat-value"><?= $enemiesKilled ?></td>
                        </tr>
                        <tr>
                            <td>Deaths</td>
                            <td class="stat-value"><?= $deaths ?></td>
                        </tr>
                        <tr>
                            <td>Score</td>
                            <td class="stat-value"><?= $score ?></td>
                        </tr>
                        <tr>
                            <td>Leaderboard rank</td>
                            <td class="stat-value"><?= htmlspecialchars($leaderboardRank) ?></td>
                        </tr>
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

                <?php if ($user['role_name'] === 'Admin'): ?>
                    <button class="profile-button btn-blue" id="profile-admin-button">🛡️ Admin Area</button>
                <?php else: ?>
                    <button class="profile-button btn-blue" id="profile-avatar-button-alt">👤 Change Avatar</button>
                <?php endif; ?>

                <button class="profile-button btn-red" id="profile-log-out">🚪 Logout</button>
            </div>

            <h2 id="profile-last-updated">Last time updated: <?= $lastUpdatedText ?></h2>

            <div id="profile-settings-modal-id" class="hidden fixed inset-0 z-50 items-center justify-center">
                <div id="profile-settings-modal-backdrop" class="absolute inset-0 bg-black/80 opacity-0 transition-opacity"></div>
                <div id="profile-settings-modal-box-id" class="profile-modal-box opacity-0 scale-95 translate-y-4">
                    <button class="profile-close-btn" id="settings-close-btn">×</button>
                    <h2 class="profile-modal-title">Settings</h2>
                    <ul class="profile-settings-list">
                        <li><button class="profile-list-btn">Change your alias</button></li>
                        <li><button class="profile-list-btn">Change your password</button></li>
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
                                <img
                                    data-avatar-id="<?= $ava['id'] ?>"
                                    src="data:image/jpeg;base64,<?= base64_encode($ava['avatar_picture']) ?>"
                                    class="profile-avatar-option w-full aspect-square object-cover border-4 border-transparent hover:border-yellow-400 cursor-pointer rounded-sm shadow-md hover:scale-105 transition-all"
                                    alt="<?= htmlspecialchars($ava['avatar_name']) ?>"
                                    title="<?= htmlspecialchars($ava['avatar_name']) ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: white; grid-column: span 3; text-align: center;">No avatars found in the database!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>