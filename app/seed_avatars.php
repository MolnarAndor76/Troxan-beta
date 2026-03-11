<?php
/**
 * AVATAR FELTÖLTŐ SCRIPT (cURL verzió)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mivel a fájl az 'app' mappában van, egyenesen a 'core' mappába nyúlunk!
require 'core/config.php';
require 'core/connect.php'; 

echo "<h1>🚀 Avatarok feltöltése indul (cURL mód)...</h1>";

$avatars_to_insert = [
    1 => ['name' => 'Warrior', 'url' => 'https://picsum.photos/id/1011/200/200'],
    2 => ['name' => 'Ranger',  'url' => 'https://picsum.photos/id/1012/200/200'],
    3 => ['name' => 'Mage',    'url' => 'https://picsum.photos/id/1025/200/200'],
    4 => ['name' => 'Paladin', 'url' => 'https://picsum.photos/id/1062/200/200'],
    5 => ['name' => 'Rogue',   'url' => 'https://picsum.photos/id/1074/200/200'],
    6 => ['name' => 'Cleric',  'url' => 'https://picsum.photos/id/1084/200/200']
];

global $pdo;

// Golyóálló letöltő függvény cURL-el
function downloadImage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Követi az átirányításokat
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Localhoston ne akadjon fent az SSL-en
    // Úgy teszünk, mintha egy Chrome böngésző lennénk:
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
    
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($http_code == 200) ? $data : false;
}

foreach ($avatars_to_insert as $id => $data) {
    echo "Próbálom letölteni a <b>{$data['name']}</b> avatart... ";
    
    // Kép letöltése a netről cURL segítségével
    $image_data = downloadImage($data['url']);
    
    if ($image_data === false) {
        echo "<span style='color:red;'>❌ Hiba a letöltésnél!</span><br>";
        continue;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Avatars (id, avatar_name, avatar_picture) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            avatar_name = VALUES(avatar_name), 
            avatar_picture = VALUES(avatar_picture)
        ");
        
        $stmt->execute([$id, $data['name'], $image_data]);
        
        echo "<span style='color:green;'>✅ Sikeresen mentve az adatbázisba! (ID: $id)</span><br>";
        
    } catch (PDOException $e) {
        echo "<span style='color:red;'>❌ Adatbázis hiba: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h2>🎉 Feltöltés befejezve! Ezt a fájlt most már törölheted.</h2>";
?>