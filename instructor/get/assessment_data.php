<?php
// start the session (access to user: loggedIn, first_name, email, type)
session_start();

// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// if user account type is not 'instructor' then force logout
if($_SESSION["type"] !== "instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

// receive POST input
$pkey = $_POST["pkey"];

// connect to the db
require_once "../../register_login/config.php";

$query = "SELECT * FROM assessments WHERE pkey = '{$pkey}'";
$res = pg_query($con, $query);

// check for errors
if(!$res) {
    // error
    exit;
} else {
    // no error
    $data = pg_fetch_row($res);
    echo json_encode($data);
}

//echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>