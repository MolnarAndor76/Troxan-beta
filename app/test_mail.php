<?php
// BEKAPCSOLJUK A HIBAJELENTÉST, HOGY LÁSSUK MI A BAJ!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>A hibakereső elindult...</h2><br>";

// Behúzzuk a mailert
require_once 'mailer.php'; 

echo "A mailer.php sikeresen betöltve!<br>";

$to = "nemfogokcsalni5@gmail.com"; // Írd át a címedre!
$subject = "Troxan Rendszer Teszt!";
$body = "<h1 style='color: orange;'>Működik a Troxan Mailer!</h1>";

if (sendTroxanMail($to, $subject, $body)) {
    echo "<h1>🔥 SIKER! A levél elment!</h1>";
} else {
    echo "<h1>❌ HIBA! Valami nem jó a küldésnél!</h1>";
}
?>