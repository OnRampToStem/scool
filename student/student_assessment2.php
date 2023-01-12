<?php
// start the session (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id)
session_start();

// if user is not logged in then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../index.html");
    exit;
}

// if user account type is not 'student' then force logout
if($_SESSION["type"] !== "student"){
    header("location: ../register_login/logout.php");
    exit;
}

/* GLOBALS */
$query;
$res;
$instr_email;
$curr_date;
$past_assessments = array();
$open_assessments = array();
$future_assessments = array();

// connect to the db
require_once "../register_login/config.php";

// first query - grab instructor email
$query = "SELECT instructor FROM users WHERE email = '{$_SESSION["email"]}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    $instr_email = pg_fetch_result($res, 0);
}

// setting to CA timezone
date_default_timezone_set('America/Los_Angeles');

$curr_date = date_create();
$curr_date = date_format($curr_date, "Y-m-d");
//echo $curr_date, "\n";

// second query - grab all past assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND close_date < '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($past_assessments[$row[0]])) {
            $past_assessments[$row[0]] = [];
            array_push($past_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// third query - grab all current assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date <= '{$curr_date}' AND close_date >= '{$curr_date}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($open_assessments[$row[0]])) {
            $open_assessments[$row[0]] = [];
            array_push($open_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// fourth query - grab all future assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date > '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($future_assessments[$row[0]])) {
            $future_assessments[$row[0]] = [];
            array_push($future_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}

/*
print_r($past_assessments);
echo "\n";
print_r($open_assessments);
echo "\n";
print_r($future_assessments);
echo "\n";
*/

?>

<!DOCTYPE html>
<html lang="en">
    <head>

    </head>
    <body>

    </body>
</html>