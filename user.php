<?php

// API for users

// Content-Type: application/json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Import connection and auth functions
include_once "includes/connect.php";
include_once "includes/options.php";
include_once "includes/authFunctions.php";
include_once "includes/standardFunctions.php";

// Get token
$token = getBearerToken();

// Check if token is set and valid and if the user is a admin
if (isset($token) && checkJWT($token) && checkAdminAccess($token)) {
    // Query for users
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // SQL query
        $sql = "SELECT id, username, email, admin FROM users";

        // Check if there is a search query or id query
        if (isset($_GET["search"])) {
            $search = $_GET["search"];
            $sql .= " WHERE name LIKE '%$search%'";
        } else if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $sql .= " WHERE id = '$id'";
        }

        // Check if there is a sort query
        if (isset($_GET["sort"])) {
            $sort = $_GET["sort"];
            if ($sort == "desc") {
                $sql .= " ORDER BY name DESC";
            } else if ($sort == "asc") {
                $sql .= " ORDER BY name ASC";
            }
        }

        // Check if there is a limit query
        if (isset($_GET["limit"])) {
            $limit = $_GET["limit"];
            $sql .= " LIMIT $limit";
        } else {
            $sql .= " LIMIT 10";
        }

        // Check if there is a offset query
        if (isset($_GET["offset"])) {
            $offset = $_GET["offset"];
            $sql .= " OFFSET $offset";
        }

        $result = mysqli_query($db_connect, $sql);

        if (!$result) {
            echoSimpleResponse(500, "Error: " . mysqli_error($db_connect));
        } else {
            $users = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
            echo json_encode(array("status" => 200, "result" => $users));
        }
    }

    // Edit user
    if ($_SERVER["REQUEST_METHOD"] == "PUT") {
        // parse input
        parse_str(file_get_contents('php://input', 'r'), $PUT);

        // Check if data is set
        if (!isset($PUT["id"]) || !isset($PUT["username"]) || !isset($PUT["email"]) || !isset($PUT["admin"])) {
            echoSimpleResponse(400, "Error: Missing data");
        }

        // Check if data is valid
        if (!is_numeric($PUT["id"]) || !is_string($PUT["username"]) || !is_string($PUT["email"]) || !is_numeric($PUT["admin"]) || $PUT["admin"] != 0 && $PUT["admin"] != 1) {
            echoSimpleResponse(400, "Error: Invalid data");
        }

        // SQL query
        $sql = "UPDATE users SET username = '$PUT[username]', email = '$PUT[email]', admin = '$PUT[admin]' WHERE id = '$PUT[id]'";

        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            echoSimpleResponse(500, "Error: " . mysqli_error($db_connect));
        } else {
            echoSimpleResponse(200, "User updated");
        }
    }

    // Delete user
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {

        // Check if data is set
        if (!isset($_GET["id"])) {
            echoSimpleResponse(400, "Error: Missing data");
        }

        // Check if data is valid
        if (!is_numeric($_GET["id"])) {
            echoSimpleResponse(400, "Error: Invalid data");
        }

        // SQL query
        $sql = "DELETE FROM users WHERE id = '$_GET[id]'";

        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            echoSimpleResponse(500, "Error: " . mysqli_error($db_connect));
        } else {
            echoSimpleResponse(200, "User deleted");
        }
    }
} else {
    echoSimpleResponse(401, "Invaild token or unauthorized access");
}

?>