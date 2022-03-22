<?php

// API for machines

// Content-Type: application/json
header('Content-Type: application/json');

// Import database connection and auth functions
include_once "includes/connect.php";
include_once "includes/authFunctions.php";

// Token variable
$token = getBearerToken();

//check if token is set and valid
if (isset($token) && checkJWT($token)) {

    // Query for machines
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "SELECT * FROM machines";

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

        // Check if there is a area query
        if (isset($_GET["area"])) {
            $area = $_GET["area"];
            $sql .= " WHERE area_id = '$area'";
        }

        // Execute query
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("message" => "Error querying machines"));
            exit();
        } else {
            // Display machines
            $machines = array();
            while ($machine = mysqli_fetch_assoc($result)) {
                // Get parts based on machine id
                $sql = "SELECT * FROM parts WHERE machine_id = '$machine[id]'";
                $result_parts = mysqli_query($db_connect, $sql);
                if (!$result_parts) {
                    http_response_code(500);
                    echo json_encode(array("message" => "Error querying parts"));
                    exit();
                } else {
                    // Display parts
                    $parts = array();
                    while ($part = mysqli_fetch_assoc($result_parts)) {
                        $parts[] = $part;
                    }
                    $machine["parts"] = $parts;
                }
                $machines[] = $machine;
            }
            echo json_encode(array("status" => 200, "result" => $machines), JSON_NUMERIC_CHECK);
        }

    }

    // Add machine
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $description = $_POST["description"];
        $area_id = $_POST["area_id"];

        // Check if name, description and area_id are set
        if (!isset($name) || !isset($description) || !isset($area_id)) {
            http_response_code(400);
            echo json_encode(array("status" => 400, "message" => "Missing parameters"));
            exit();
        } else {
            // Check if area_id is valid
            $sql = "SELECT * FROM areas WHERE id = '$area_id'";
            $result = mysqli_query($db_connect, $sql);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("message" => "Error querying areas"));
                exit();
            } else {
                $areas = mysqli_fetch_all($result, MYSQLI_ASSOC);
                if (count($areas) == 0) {
                    http_response_code(400);
                    echo json_encode(array("status" => 400, "message" => "Invalid area_id"));
                    exit();
                }
            }

            // Insert machine
            $sql = "INSERT INTO machines (name, description, area_id) VALUES ('$name', '$description', '$area_id')";
            $result = mysqli_query($db_connect, $sql);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("status" => 500, "message" => "Error inserting machine"));
                exit();
            } else {
                http_response_code(201);
                echo json_encode(array("status" => 201, "message" => "Machine added"));
            }
        }
    }

    // Delete machine
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        $id = $_GET["id"];

        // Check if id is set
        if (!isset($id)) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing parameters"));
            exit();
        } else {
            // Check if id is valid
            $sql = "SELECT * FROM machines WHERE id = '$id'";
            $result = mysqli_query($db_connect, $sql);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("status" => 500,"message" => "Error querying machines"));
                exit();
            } else {
                $machines = mysqli_fetch_all($result, MYSQLI_ASSOC);
                if (count($machines) == 0) {
                    http_response_code(400);
                    echo json_encode(array("status" => 400, "message" => "Invalid id"));
                    exit();
                }
            }

            // Delete machine
            $sql = "DELETE FROM machines WHERE id = '$id'";
            $result = mysqli_query($db_connect, $sql);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("status" => 500, "message" => "Error deleting machine"));
                exit();
            } else {
                http_response_code(200);
                echo json_encode(array("status" => 200, "message" => "Machine deleted"));
            }
        }
    }

    // Update machine
    if ($_SERVER["REQUEST_METHOD"] == "PUT") {

        // Parse input
        parse_str(file_get_contents('php://input', 'r'), $PUT);

        // Check if fields are set
        if (isset($PUT["id"]) && isset($PUT["name"]) && isset($PUT["description"]) && isset($PUT["area_id"])) {
            $id = $PUT["id"];
            $name = $PUT["name"];
            $description = $PUT["description"];
            $area_id = $PUT["area_id"];
        } else {
            http_response_code(400);
            echo json_encode(array("status" => 400, "message" => "Missing parameters"));
            exit();
        }
        
        // Check if id is valid
        $sql = "SELECT * FROM machines WHERE id = '$id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error querying machines"));
            exit();
        } else {
            $machines = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if (count($machines) == 0) {
                http_response_code(400);
                echo json_encode(array("status" => 400, "message" => "Invalid id"));
                exit();
            }
        }

        // Check if area_id is valid
        $sql = "SELECT * FROM areas WHERE id = '$area_id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error querying areas"));
            exit();
        } else {
            $areas = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if (count($areas) == 0) {
                http_response_code(400);
                echo json_encode(array("status" => 400, "message" => "Invalid area_id"));
                exit();
            }
        }

        // Update machine
        $sql = "UPDATE machines SET name = '$name', description = '$description', area_id = '$area_id' WHERE id = '$id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error updating machine"));
            exit();
        } else {
            http_response_code(200);
            echo json_encode(array("status" => 200, "message" => "Machine updated"));
        }
    }

} else {
    http_response_code(401);
    $error = array("status" => 401, "result" => "Invalid token");
    echo json_encode($error);
}

?>