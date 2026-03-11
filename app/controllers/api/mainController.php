<?php
function getContent() {
    ob_start();
    
    require VIEWS . 'main/main.php';
    
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