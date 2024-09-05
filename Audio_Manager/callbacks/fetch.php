<?php
// Ensure the file starts with a PHP session and includes necessary configurations
session_start();
require("./config.php"); // Adjust the path to your actual configuration file

// config.php
$mysqli = new mysqli('localhost', 'root', '', 'timebase_sys');

// Check connection
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the action from the AJAX request
    $action = $_POST['action'] ?? '';

    // Define a function to handle the ESC action and fetch the closest schedule
    function fetchClosestSchedule($mysqli)
    {
        // SQL query to fetch the closest event to the current date and time
        $sql = "SELECT `id`, `event_id`, `message`, `startdate`, `enddate`, `notallowed`, `timing`, `colour`, `days`, `audio`, `audioname`
                FROM `taskmanager`
                WHERE `startdate` >= NOW()
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, `startdate`, NOW()))
                LIMIT 1";

        // Execute the query
        $result = $mysqli->query($sql);

        // Check if any event is found
        if ($result && $result->num_rows > 0) {
            // Fetch the closest event
            $row = $result->fetch_assoc();

            // Format dates as 'YYYY-MM-DD HH:MM:SS'
            $row['startdate'] = date('Y-m-d H:i:s', strtotime($row['startdate']));
            $row['enddate'] = date('Y-m-d H:i:s', strtotime($row['enddate']));
            $audioName = $row["audioname"];

            // Return the event data as JSON
            echo json_encode([
                'status' => 'success',
                'message' => 'Closest schedule fetched successfully.',
                'data' => $row,
                'audioName' => $audioName
            ]);
        } else {
            // Return an error response if no event is found
            echo json_encode([
                'status' => 'error',
                'message' => 'No schedules found close to the current date and time.'
            ]);
        }
        exit; // Terminate the script after sending the response
    }

    // Define a function to fetch all schedules
    function fetchAllSchedules($mysqli)
    {
        // SQL query to fetch all schedules
        $sql = "SELECT `id`, `event_id`, `message`, `startdate`, `enddate`, `notallowed`, `timing`, `colour`, `days`, `audio`, `audioname`
                FROM `taskmanager`
                ORDER BY `startdate` ASC";

        // Execute the query
        $result = $mysqli->query($sql);

        // Check if any schedules are found
        if ($result && $result->num_rows > 0) {
            // Fetch all schedules
            $schedules = $result->fetch_all(MYSQLI_ASSOC);

            // Format dates and return the data
            foreach ($schedules as &$row) {
                $row['startdate'] = date('Y-m-d H:i:s', strtotime($row['startdate']));
                $row['enddate'] = date('Y-m-d H:i:s', strtotime($row['enddate']));
            }

            // Return all schedules as JSON
            echo json_encode([
                'status' => 'success',
                'message' => 'All schedules fetched successfully.',
                'data' => $schedules
            ]);
        } else {
            // Return an error response if no schedules are found
            echo json_encode([
                'status' => 'error',
                'message' => 'No schedules found.'
            ]);
        }
        exit; // Terminate the script after sending the response
    }

    // Handle the action based on the provided action type
    switch ($action) {
        case 'esc':
            fetchClosestSchedule($mysqli);
            break;

        case 'fetchAll':
            fetchAllSchedules($mysqli);
            break;

        // Add more cases here if handling other actions (e.g., 'left', 'right', etc.)

        default:
            // Default response for an unrecognized action
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action received.'
            ]);
            exit;
    }
} else {
    // Response for invalid request methods
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}
?>
