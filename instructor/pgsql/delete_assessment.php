<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

// receive POST input
$pkey = $_POST["pkey"];

// connect to the db
require_once "../../register_login/config.php";

// query
$query = "DELETE FROM assessments WHERE pkey = '{$pkey}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

echo "Successfully deleted assessment.";

?>