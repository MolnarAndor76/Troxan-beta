<div class="leaderboard-site">
  <div id="leaderboard-section">
    
    <div class="leaderboard-header">
      <h1 id="leaderboard-title">The best warriors in TROXAN</h1>
      </div>

    <div class="leaderboard-table-shell">
      <div class="leaderboard-head-bar">
        <span class="leaderboard-head-rank">Rank</span>
        <span class="leaderboard-head-user">Username</span>
        <span class="leaderboard-head-score">Score</span>
      </div>

      <div class="leaderboard-table-container">
        <table id="leaderboard-table">
          <tbody>
          
          <?php if (!empty($top_10)): ?>
              <?php foreach ($top_10 as $player): ?>
                  <?php 
                      // Osztályok beállítása az első három helyezettnek
                      $topClass = "";
                      if ($player['rank'] == 1) $topClass = "leaderboard-top-1";
                      elseif ($player['rank'] == 2) $topClass = "leaderboard-top-2";
                      elseif ($player['rank'] == 3) $topClass = "leaderboard-top-3";
                  ?>
                  <tr class="leaderboard-tr <?= $topClass ?>">
                    <td class="leaderboard-td-rank"><?= $player['rank'] ?></td>
                    <td class="leaderboard-td-user"><?= htmlspecialchars($player['username']) ?></td>
                    <td class="leaderboard-td-score"><?= number_format($player['score'], 0, '', '_') ?></td>
                  </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr class="leaderboard-tr">
                <td colspan="3" class="leaderboard-td-user" style="text-align: center; padding: 20px;">No warriors found.</td>
              </tr>
          <?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>

    <?php if (!empty($current_user_data)): ?>
      <div class="leaderboard-current-user-bar">
        <span class="leaderboard-current-rank">#<?= $current_user_data['rank'] ?></span>
        <span class="leaderboard-current-name"><?= htmlspecialchars($current_user_data['username']) ?> (You)</span>
        <span class="leaderboard-current-score"><?= number_format($current_user_data['score'], 0, '', '_') ?></span>
      </div>
    <?php endif; ?>

    <div class="leaderboard-last-updated">
        Last time updated: <span id="leaderboard-last-updated-time"></span>
    </div>

  </div>
</div>