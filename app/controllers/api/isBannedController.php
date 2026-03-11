<?php

function getContent() {
    // Szigorúan csak annak mutatjuk meg, aki be van lépve (mert vendéget nem lehet bannolni)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        json_response(["status" => "error", "message" => "Nem vagy bejelentkezve!"], 401);
        return;
    }

    ob_start();
    // Az isBanned mappa és fájl meghívása a views mappából
    require VIEWS . 'isBanned/isBanned.php';
    $buffer = ob_get_clean();

    json_response(["html" => $buffer, "status" => "success"], 200);
}

// Router logika
switch ($data["method"]) {
    case 'GET': 
        getContent(); 
        break;
    default: 
        json_response(["status" => "error", "message" => "Method not allowed"], 405); 
        break;
}
?>