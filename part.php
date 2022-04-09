<?php

// API for parts

// Content-Type: application/json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Import database connection and auth functions
include_once "includes/connect.php";
include_once "includes/options.php";
include_once "includes/authFunctions.php";

// Get token
$token = getBearerToken();

// Check if token is set and valid
if (isset($token) && checkJWT($token)) {
    // Query for parts
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // SQL query
        $sql = "SELECT * FROM parts";

        // Check if there is a search query or id query
        if (isset($_GET["search"])) {
            $search = $_GET["search"];
            $sql .= " WHERE name LIKE '%$search%'";
        } else if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $sql .= " WHERE id = '$id'";
        }

        // Check whether there is a machine query
        if (isset($_GET["machine"])) {
            $machine = $_GET["machine"];
            $sql .= " WHERE machine_id = '$machine'";
        }

        // Fliter by expired time
        if (isset($_GET["expired"])) {
            $time = $_GET["expired"];
            $sql .= " WHERE best_before_date < '$time'";
        }

        // Fliter by changed time
        if (isset($_GET["changed"])) {
            $time = $_GET["changed"];
            $sql .= " WHERE changed_date < '$time'";
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

        // Execute query
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("message" => "Error querying parts"));
            exit();
        } else {
            // Display parts
            $parts = array();
            while ($part = mysqli_fetch_assoc($result)) {
                // Get machine based on machine id
                $sql = "SELECT * FROM machines WHERE id = '$part[machine_id]'";
                $result_machine = mysqli_query($db_connect, $sql);
                if (!$result_machine) {
                    http_response_code(500);
                    echo json_encode(array("status" => 500, "message" => "Error querying machines"));
                    exit();
                } else {
                    $machine = mysqli_fetch_assoc($result_machine);
                    $part["machine"] = $machine;
                }

                // Get part type based on part type id
                $sql = "SELECT * FROM types WHERE id = '$part[part_type_id]'";
                $result_type = mysqli_query($db_connect, $sql);
                if (!$result_type) {
                    http_response_code(500);
                    echo json_encode(array("status" => 500, "message" => "Error querying types"));
                    exit();
                } else {
                    $type = mysqli_fetch_assoc($result_type);
                    $part["part_type"] = $type;
                }

                $current_time = time();
                $best_before_date = strtotime($part["best_before_date"]);

                // Count days until best before date and round to nearest day
                $days_until_best_before = ceil(($best_before_date - $current_time) / 86400);
                $part["days_until_best_before"] = $days_until_best_before;
                array_push($parts, $part);
            }
            echo json_encode(array("status" => 200, "result" => $parts), JSON_NUMERIC_CHECK);
        }
    }

    // Create part
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get data from request
        parse_str(file_get_contents('php://input', 'r'), $PUT);

        // Check if all required fields are set
        if (!isset($PUT["name"]) || !isset($PUT["machine_id"]) || !isset($PUT["part_type_id"]) || !isset($PUT["description"])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required fields"));
            exit();
        } else {
            $name = $PUT["name"];
            $machine_id = $PUT["machine_id"];
            $part_type_id = $PUT["part_type_id"];
            $description = $PUT["description"];
        }
        
        // Get part type information
        $sql = "SELECT * FROM types WHERE id = '$part_type_id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error querying types"));
            exit();
        } else {
            if (mysqli_num_rows($result) == 0) {
                http_response_code(400);
                echo json_encode(array("status" => 400, "message" => "Part type does not exist"));
                exit();
            } else {
                $type = mysqli_fetch_assoc($result);
                $validity = $type["validity"];
            }
        }

        // Check if machine exists
        $sql = "SELECT * FROM machines WHERE id = '$machine_id'";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error querying machines"));
            exit();
        } else {
            if (mysqli_num_rows($result) == 0) {
                http_response_code(400);
                echo json_encode(array("status" => 400, "message" => "Machine does not exist"));
                exit();
            }
        }

        // Set best before timestamp and changed timestamp
        $best_before_date = date("Y-m-d H:i:s", strtotime("+$validity days"));
        $changed_date = date("Y-m-d H:i:s");

        // Insert part into database
        $sql = "INSERT INTO parts (name, machine_id, part_type_id, description, best_before_date, changed_date) VALUES ('$name', '$machine_id', '$part_type_id', '$description', '$best_before_date', '$changed_date')";
        $result = mysqli_query($db_connect, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(array("status" => 500, "message" => "Error inserting part. Error: " . mysqli_error($db_connect)));
            exit();
        } else {
            http_response_code(201);
            echo json_encode(array("status" => 201, "message" => "Part created"));
        }
    }
} else {
    http_response_code(401);
    echo json_encode(array("status" => 401, "message" => "Invaild token"));
}


?>