<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // receive data
    $instructor = $_POST["instructor"];
    $course_name = $_POST["course_name"];
    $course_id = $_POST["course_id"];
    $assessment = json_decode($_POST["assessment"]);

    // connect to the db
    require_once "../../register_login/config.php";

    // query
    $query = "INSERT INTO assessments(instructor, name, public, duration, open_date, open_time, close_date, close_time, content, course_name, course_id)
              VALUES('$instructor', '$assessment[2]', '$assessment[3]', '$assessment[4]', '$assessment[5]', '$assessment[6]', '$assessment[7]', '$assessment[8]', '$assessment[9]', '$course_name', '$course_id')";
    $res = pg_query($con, $query) or die(pg_last_error($con));

    echo "Successfully copied assessment.";
}

?>