<?php
// 1. Behúzzuk a beállításokat (a konstansokat)
require_once 'app/core/config.php';

// 2. Behúzzuk magát a csatlakozást (itt jön létre a $pdo)
require_once 'app/core/connect.php';

// 3. Csekkoljuk, hogy él-e a kapcsolat
if (isset($pdo)) {
    echo "<h1 style='color: green; font-family: monospace;'>🎮 RENDSZER ÜZENET: A Troxan adatbázis kapcsolat TÖKÉLETES! 🚀</h1>";
    echo "<p>A PDO motor dorombol. Jöhet a Login!</p>";
} else {
    echo "<h1 style='color: red; font-family: monospace;'>💀 KÓD PIROS: Valami eltört, nincs kapcsolat!</h1>";
}
?>