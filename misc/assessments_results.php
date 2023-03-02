<?php
// for display purposes
header('Content-type: text/plain');

echo "Connecting to PostgreSQL database.\n";
require_once "../register_login/config.php";

$query = "CREATE TABLE IF NOT EXISTS assessments_results (
    pkey SERIAL PRIMARY KEY,
    assessment_name TEXT NOT NULL,
    instructor_email TEXT NOT NULL,
    student_email TEXT NOT NULL,
    student_name TEXT NOT NULL,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL,
    score DECIMAL NOT NULL,
    max_score DECIMAL NOT NULL,
    content JSON NOT NULL,
    date_time_submitted TIMESTAMP NOT NULL
)";
pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");
echo "The 'assessments_results' table has been successfully created or was already there!\n";

echo "Disconnecting from PostgreSQL database.\n";
pg_close($con);

?>