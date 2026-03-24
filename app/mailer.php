<?php


// 1. Kőkemény ellenőrzés: Ott vannak egyáltalán a fájlok?
$phpmailer_path = __DIR__ . '/PHPMailer/PHPMailer.php';

if (!file_exists($phpmailer_path)) {
    // Ha nincs ott a fájl, megállítjuk a futást, és kiírjuk a pontos útvonalat, amit a szerver keres!
    die("<h1 style='color:red;'>❌ VÉGZETES HIBA: Nem találom a PHPMailer fájlokat!</h1>
         <p>A szerver itt keresi őket, de nincsenek itt:</p>
         <b>{$phpmailer_path}</b><br>
         <p>Kérlek, ellenőrizd, hogy a PHPMailer mappa pontosan ezen a helyen van-e, és a kis/nagybetűk is stimmelnek!</p>");
}

// Ha idáig eljutott, akkor megvannak a fájlok, tölthetjük is be!
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendTroxanMail($toEmail, $subject, $htmlBody) {
// ... innen folytatódik a régi kód a $mail = new PHPMailer(true); résszel ...
    // Létrehozzuk a PHPMailer példányt (true = kivételek bekapcsolása hiba esetén)
    $mail = new PHPMailer(true);

    try {
// --- DEBUG MÓD ÉS IDŐKORLÁT (Hogy lássuk, min akad fenn) ---
        $mail->SMTPDebug  = 0;                            // Részletes SMTP log kiírása a képernyőre!
        $mail->Timeout    = 10;                           // Ne tekerjen a végtelenségig, 10 mp után adja fel!

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';             
        $mail->SMTPAuth   = true;                     // Autentikáció bekapcsolása
        
        // IDE ÍRD BE A SAJÁT ADATAIDAT:
        $mail->Username   = 'troxangame@gmail.com';   // A te Gmail címed
        $mail->Password   = 'ombmkvnfsjzerwdm'; // A generált App Password (szóközök nélkül!)
        
// --- PORT ÉS TITKOSÍTÁS CSERE ---
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // SMTPS helyett STARTTLS
        $mail->Port       = 587;                            // 465 helyett 587-es port!
        $mail->CharSet    = 'UTF-8';                      

        $mail->setFrom('te.email.cimed@gmail.com', 'Troxan Game'); 
        $mail->addAddress($toEmail);                               

        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);               // Sima szöveges verzió, ha a levelező nem támogatja a HTML-t

        // KÜLDÉS! 🚀
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Ha valamiért besül a dolog, a háttérben logolhatjuk a hibát, de a frontendnek csak false-t adunk vissza
        error_log("Troxan Mailer Hiba: {$mail->ErrorInfo}");
        return false;
    }
}
?>