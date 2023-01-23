<?php   
//This PHP script will be used to log out a user from the system.

// initialize the session
session_start();

// unset all of the session variables
$_SESSION = array();

// destroy the session.
session_destroy();

// send user back to Fresno State Canvas
header("location: https://fresnostate.instructure.com");
?>  