<?php

// content-type: application/json
header('Content-Type: application/json');

// Connect function
include_once "includes/connect.php";

// Import configuration file
include_once "includes/config.php";

// JWT Library
include_once "includes/jwt/src/JWT.php";
include_once "includes/jwt/src/BeforeValidException.php";
include_once "includes/jwt/src/ExpiredException.php";
include_once "includes/jwt/src/SignatureInvalidException.php";

// JWT Configuration
use \Firebase\JWT\JWT;

// Check if type is set
if (isset($_GET["type"])) {
    $type = $_GET["type"];

    // Check if type is valid
    if ($type == "login") {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Query the user
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
            echo json_encode($error);
        } else {
            // Check if user exists
            if (mysqli_num_rows($result) == 0) {
                http_response_code(401);
                $error = array("status" => 401, "message" => "Username or password is incorrect");
                echo json_encode($error);
            } else {
                $user = mysqli_fetch_assoc($result);
                // Check if password is correct
                if (password_verify($password, $user["password"])) {

                    // Create JWT
                    $token = array(
                        "iss" => "localhost/inventory_manager",
                        "aud" => "localhost/inventory_manager",
                        "iat" => time(),
                        "nbf" => time(),
                        "exp" => time() + (60 * 60),
                        "data" => array(
                            "username" => $username,
                            "user_id" => $user["id"],
                        )
                    );

                    // Encode JWT and send it
                    $user["token"] = JWT::encode($token, $key, "HS256");
                    unset($user["password"]);
                    http_response_code(200);
                    echo json_encode(array("status" => 200, "result" => $user),JSON_NUMERIC_CHECK);
                } else {
                    http_response_code(401);
                    $error = array("status" => 401, "message" => "Username or password is incorrect");
                    echo json_encode($error);
                }
            }
        }
    } else if ($type == "register") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];


        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            $error = array("status" => 500, "message" => "mySQL query failed: " . mysqli_error($db_connect));
        } else {
            // Check if username is taken
            if (mysqli_num_rows($result) > 0) {
                http_response_code(409);
                $error = array("status" => 409, "message" => "Username already exists");
                echo json_encode($error);
            } else {
                // Hash password
                $password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO Users (username, password, email) VALUES ('$username', '$password', '$email')";
                $result = mysqli_query($db_connect, $sql);
                if (!$result) {
                    http_response_code(500);
                    $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
                } else {
                    $user = array("username" => $username, "password" => $password, "email" => $email);
                    http_response_code(201);
                    echo json_encode(array("status" => 201, "result" => $user),JSON_NUMERIC_CHECK);
                }
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => 400, "result" => "Invalid type"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => 400, "result" => "Type not specified"));
}

?>