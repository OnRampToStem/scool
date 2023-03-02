<?php
// for display purposes
header('Content-type: text/plain');

// connect to the db
require_once "../register_login/config.php";

// creating the 'assessments' table, if it does not exist in the PostgreSQL database
$query = "CREATE TABLE IF NOT EXISTS assessments (
    pkey SERIAL PRIMARY KEY,
    instructor TEXT NOT NULL,
    name TEXT NOT NULL,
    public TEXT NOT NULL,
    duration INT NOT NULL,
    open_date DATE NOT NULL,
    open_time TIME NOT NULL,
    close_date DATE NOT NULL,
    close_time TIME NOT NULL,
    content JSON NOT NULL,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL
)";
pg_query($con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($con) . "\n");
echo "The 'assessments' table has been successfully created or was already there!\n";

echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>