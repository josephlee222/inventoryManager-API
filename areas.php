<?php
// API for areas

// Content-Type: application/json
header('Content-Type: application/json');

// Import database connection and auth functions
include_once "includes/connect.php";
include_once "includes/authFunctions.php";

// Check if token is set and vaild
$token = getBearerToken();
if (isset($token) && checkJWT($token)) {

    // Query for areas
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "SELECT * FROM areas";

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

        // Check if there is a place query
        if (isset($_GET["place"])) {
            $place = $_GET["place"];
            $sql .= " WHERE place_id = '$place'";
        }

        // Execute query
        $result = mysqli_query($db_connect, $sql);

        // Check if query failed
        if (!$result) {
            http_response_code(500);
            $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
            echo json_encode($error);
        } else {
            // Display areas
            $areas = array();
            while ($area = mysqli_fetch_assoc($result)) {
                // Get machines based on area id
                $sql = "SELECT * FROM machines WHERE area_id = '$area[id]'";
                $result_machines = mysqli_query($db_connect, $sql);
                if (!$result_machines) {
                    http_response_code(500);
                    $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
                    echo json_encode($error);
                } else {
                    $machines = array();
                    foreach ($result_machines as $machine) {
                        $machines[] = $machine;
                    }
                    $area["machines"] = $machines;
                }
                $areas[] = $area;
            }
            $areas = array("status" => 200, "result" => $areas);
            echo json_encode($areas, JSON_NUMERIC_CHECK);
        }
    }

    // Create a new area
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if the user has admin access
        if (checkAdminAccess($token)) {
            $name = $_POST["name"];
            $description = $_POST["description"];
            $place_id = $_POST["place_id"];
            $sql = "INSERT INTO areas (name, description, place_id) VALUES ('$name', '$description', '$place_id')";
            $result = mysqli_query($db_connect, $sql);

            if (!$result) {
                http_response_code(500);
                $error = array("status" => 500, "result" => "mySQL query failed: " . mysqli_error($db_connect));
                echo json_encode($error);
            } else {
                http_response_code(201);
                $area = array("status" => 201, "result" => "Area created");
                echo json_encode($area);
            }
        }
    }

    // Delete an area
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        // Check if the user has admin access
        if (checkAdminAccess($token)) {
            $id = $_GET["id"];
            $sql = "DELETE FROM areas WHERE id = '$id'";
            $result = mysqli_query($db_connect, $sql);

            if (mysqli_affected_rows($db_connect) == 0) {
                http_response_code(404);
                $error = array("status" => 404, "result" => "Area not found");
                echo json_encode($error);
            } else {
                echo json_encode(array("status" => 200, "result" => "Area deleted"));
            }
        } else {
            http_response_code(401);
            $error = array("status" => 401, "result" => "You do not have permission to delete areas");
            echo json_encode($error);
        }
    }

    // Update an area
    if ($_SERVER["REQUEST_METHOD"] == "PUT") {
        // Check if the user has admin access
        if (checkAdminAccess($token)) {
            parse_str(file_get_contents('php://input', 'r'), $PUT);
            $id = $PUT["id"];
            $name = $PUT["name"];
            $description = $PUT["description"];
            $place_id = $PUT["place_id"];
            $sql = "UPDATE areas SET name = '$name', description = '$description', place_id = '$place_id' WHERE id = '$id'";
            $result = mysqli_query($db_connect, $sql);

            if (mysqli_affected_rows($db_connect) == 0) {
                http_response_code(404);
                $error = array("status" => 404, "result" => "Area not found");
                echo json_encode($error);
            } else {
                echo json_encode(array("status" => 200, "result" => "Area updated"));
            }
        } else {
            http_response_code(401);
            $error = array("status" => 401, "result" => "You do not have permission to update areas");
            echo json_encode($error);
        }
    }
} else {
    http_response_code(401);
    $error = array("status" => 401, "result" => "Invalid token");
    echo json_encode($error);
}
?>