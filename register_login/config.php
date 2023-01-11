<?php
// PostgreSQL Database credentials
define('HOST', 'localhost');
define('PORT', '5432');
define('DB', 'math_db');
define('USER', 'postgres');
define('PASS', 'pass');

// Attempt to connect to the PostgreSQL database 
$con = pg_connect("host=" . HOST . " port=" . PORT . " dbname=" . DB . " user=" . USER . " password=" . PASS)
       or die ("Could not connect to the database.\n");
?>
    