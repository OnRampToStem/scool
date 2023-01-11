<?php
header('Content-type: text/plain');

// connect to the db
require_once "../../register_login/config.php";

// creating the 'assessments' table, if it does not exist in the PostgreSQL database
$query = "CREATE TABLE IF NOT EXISTS assessments (
    pkey serial primary key,
    instructor varchar(64) NOT NULL,
    name varchar(60) NOT NULL,
    public varchar(3) NOT NULL,
    duration int NOT NULL,
    open_date date NOT NULL,
    open_time time NOT NULL,
    close_date date NOT NULL,
    close_time time NOT NULL,
    content json NOT NULL,
    course_name varchar(60) NOT NULL,
    course_id varchar(60) NOT NULL,
    section_id varchar(60) NOT NULL
)";
pg_query($con, $query) or die("Cannot execute query: $query \n");
echo "The questions table has been successfully created or was already there!\n";

echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>