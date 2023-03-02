<?php
// Fresno State PostgreSQL Database credentials
define('HOST', 'stem-scale-db.priv.fresnostate.edu');
define('PORT', '5432');
define('DB', 'swa');
define('USER', 'scale_dyna');
define('PASS', 'ZAKh55Mxxafz7jBqwhy_SG23C8_WkXm8_6');

// Attempt to connect to the PostgreSQL database 
$con = pg_connect("host=" . HOST . " port=" . PORT . " dbname=" . DB . " user=" . USER . " password=" . PASS)
       or die ("Could not connect to the database.\n");
?>
    