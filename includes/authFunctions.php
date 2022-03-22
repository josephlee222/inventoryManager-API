<?php

// Path: includes\authFunctions.php

// Import database connection
include "connect.php";

// Import configuration file
include "config.php";

// Import JWT
include_once "includes/jwt/src/BeforeValidException.php";
include_once "includes/jwt/src/ExpiredException.php";
include_once "includes/jwt/src/SignatureInvalidException.php";
include_once "includes/jwt/src/JWT.php";
include_once "includes/jwt/src/Key.php";

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Function to check the JWT
function checkJWT($jwt) {
    global $key;
    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'), array('HS256'));
        // Check if the token is expired
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return false;
        } else {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
}

function checkAdminAccess($jwt) {
    global $db_connect;
    global $key;
    if (checkJWT($jwt)) {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'), array('HS256'));
        $user_id = $decoded->data->user_id;
        $sql = "SELECT * FROM users WHERE id = '$user_id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
            echo json_encode($error);
        } else {
            $user = mysqli_fetch_assoc($result);
            if ($user["admin"] == 1) {
                return true;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}

// Get header Authorization
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// get access token from header
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Function to return the JWT decoded data
function getJWTData($jwt) {
    global $key;
    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'), array('HS256'));
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}
?>