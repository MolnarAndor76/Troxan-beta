<?php

function getContent() {
    ob_start();
    // Behívjuk a vendég nézetet
    require VIEWS . 'guest/guest.php';
    $buffer = ob_get_clean();

    json_response([
        "html"    => $buffer,
        "status"  => "success",
        "message" => "Guest page loaded"
    ], 200);
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