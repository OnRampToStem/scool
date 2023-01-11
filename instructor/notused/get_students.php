<?php
/*
    This php script will run a query on the PGSQL DB to get the entire list of students. 
    It will then send that list back to where it was requested.
*/

// initialize the session (access to user: loggedIn, first_name, email, type)
session_start();

// connect to the db
require_once "../register_login/config.php";

// globals
$db_string = '';
$counter = 0;

// query to run
$query = "SELECT * FROM users where instructor = '" . $_SESSION['email'] . "'";
$result = pg_query($con, $query) or die("Cannot execute query: $query\n");
$arr = pg_fetch_all($result);

// if contains data
if($arr){
    while ($row = pg_fetch_row($result)) {
        // $row[3] contains the students' email

        if($counter < count($arr) - 1){
            $db_string .= $row[3] . ',';
        }
        else{
            $db_string .= $row[3];
        }
        $counter++;
    }
    echo $db_string;
}
else{
    echo 'No users in the PGSQL DB';
    exit;
}

?>