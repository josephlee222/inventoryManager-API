<?php

// API for places

// Content-Type: application/json
header('Content-Type: application/json');

//import the database connection and auth functions
include_once "includes/connect.php";
include_once "includes/authFunctions.php";
include_once "includes/standardFunctions.php";

// Get token
$token = getBearerToken();

if (isset($token) && checkJWT($token)) {

    // Query for places
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "SELECT * FROM places";

        // Check if there is a search query
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
            $places = array();
            while ($place = mysqli_fetch_assoc($result)) {
                // Get areas based on place id
                $sql = "SELECT * FROM areas WHERE place_id = '$place[id]'";
                $result_areas = mysqli_query($db_connect, $sql);
                if (!$result_areas) {
                    echoSimpleResponse(500, "Error: " . mysqli_error($db_connect));
                } else {
                    $areas = array();
                    while ($area = mysqli_fetch_assoc($result_areas)) {
                        $areas[] = $area;
                    }
                    $place["areas"] = $areas;
                }
                $places[] = $place;
            }
            echo json_encode(array("status" => 200, "result" => $places), JSON_NUMERIC_CHECK);
        }
    }

    // Delete a place
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        if (isset($_GET["id"])) {
            if (checkAdminAccess($token)) {
                $id = $_GET["id"];
                $sql = "DELETE FROM places WHERE id = '$id'";
                $result = mysqli_query($db_connect, $sql);
                if (!$result) {
                    echoSimpleResponse(500, "mySQL query failed: " . mysqli_error($db_connect));
                } else {
                    echo json_encode(array("status" => 200, "message" => "Place deleted"));
                }
            } else {
                echoSimpleResponse(403, "You do not have permission to delete places");
            }
        } else {
            echoSimpleResponse(400, "ID is missing");
        }
    }

    //create a place
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Check if the user is admin
        if (!checkAdminAccess($token)) {
            echoSimpleResponse(401, "You do not have permission to create a place");
        }

        // Check if the data is valid
        if (!isset($_POST["name"]) || !isset($_POST["description"]) || !isset($_POST["latitude"]) || !isset($_POST["longitude"])) {
            echoSimpleResponse(400, "Missing data");
        }

        $name = $_POST["name"];
        $description = $_POST["description"];
        $sql = "INSERT INTO places (name, description) VALUES ('$name', '$description')";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            echoSimpleResponse(500, "mySQL query failed: " . mysqli_error($db_connect));
        } else {
            echoSimpleResponse(201, "Place created");
        }
    }

    //update a place
    if ($_SERVER["REQUEST_METHOD"] == "PUT") {

        // Check if the user is admin
        if (!checkAdminAccess($token)) {
            echoSimpleResponse(403, "You do not have permission to update a place");
        }

        // parse input
        parse_str(file_get_contents('php://input', 'r'), $PUT);

        // Check if all required fields are set
        if (!isset($PUT["id"]) || !isset($PUT["name"]) || !isset($PUT["description"])) {
            echoSimpleResponse(400, "Missing data");
        }

        // Get the data
        $id = $PUT["id"];
        $name = $PUT["name"];
        $description = $PUT["description"];

        // Update the place
        $sql = "UPDATE places SET name = '$name', description = '$description' WHERE id = '$id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            echoSimpleResponse(500, "mySQL query failed: " . mysqli_error($db_connect));
        } else {
            echoSimpleResponse(200, "Place updated");
        }
    }
} else {
    echoSimpleResponse(401, "Invaid token");
}

?>