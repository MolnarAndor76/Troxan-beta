<?php
function getContent() {
    
    // ======================================================
    // BIZTONSÁGI ELLENŐRZÉS: Ha nincs belépve, a Guest oldalt kapja!
    // ======================================================
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        ob_start();
        
        // Itt a maps.php helyett a guest.php-t húzzuk be!
        // (Ellenőrizd, hogy a te mappaszerkezetedben pontosan mi a guest fájl útvonala)
        require VIEWS . 'guest/guest.php'; 
        
        $buffer = ob_get_clean();

        // Sikeres "200"-as státuszt küldünk, hogy a JS szó nélkül kirajzolja a Guest HTML-t
        json_response([
            "html"    => $buffer,
            "status"  => "success",
            "message" => "Redirected to guest"
        ], 200);
        
        return; // Itt megállítjuk a futást, a Maps beolvasása elmarad!
    }

    // ======================================================
    // HA BE VAN LÉPVE, MEHET A NORMÁL MAPS OLDAL
    // ======================================================

    ob_start();
    
    require VIEWS . 'editor/editor.php';
    
    $buffer = ob_get_clean();

    $status = "success";
    $message = "";

    $data = [
        "html"    => $buffer,
        "status"  => $status,
        "message" => $message
    ];

    json_response($data, 200);
}

switch ($data["method"]) {
    case 'GET': 
        getContent();                 
        break;
    default:
        // method_not_allowed();
        break;
}
?>