<?php

function echoSimpleResponse($status, $message) {
    http_response_code($status);
    $error = array("status" => $status, "message" => $message);
    echo json_encode($error);
    exit();
}

?>