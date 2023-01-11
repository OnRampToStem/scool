<?php
/*
    This PHP script will do the following:
    1. Create "dynamic_questions" PGSQL table if it does not already exist.
    2. Take an input JSON file of dynamic math questions and will insert the data directly
       into the PostgreSQL database table "dynamic_questions".
*/

header('Content-type: text/plain');

/* GLOBALS */
$query = "";

// connect to the db
require_once "../register_login/config.php";

// create the "dynamic_questions" table if it does not exist in the PostgreSQL database
// Note: "lo_tag" and "difficulty" are do not have constraint "not null" because some entries in
// the json file are empty
$query = "CREATE TABLE IF NOT EXISTS dynamic_questions (
    id varchar(10) not null,
    author varchar(100) not null,
    title_topic varchar(100) not null,
    title varchar(100) not null,
    title_number varchar(10) not null,
    problem_number varchar(10) not null,
    lo_tag varchar(10),
    difficulty varchar(10)
)";
pg_query($con, $query) or die("Cannot execute query: $query \n");
echo "The questions table has been successfully created or was already there!\n";


// read and decode the JSON file (text => PHP assoc array)
$json_filename = "dynamic_questions_info.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// now insert the data into dynamic_questions by looping through each dynamic question
foreach ($json_data as $question){

    // inserting values into dynamic_questions table
    // (manually adding ' ' needed for PostgreSQL query strings / text)
    $query = "INSERT INTO dynamic_questions(id, author, title_topic, title, title_number, problem_number, lo_tag, difficulty) 
              VALUES ('" . $question['id'] . "', '" . $question['author'] . "', '" . $question['title_topic'] . "', '" . $question['title'] . "', '" . $question['title_number'] . "', '" . $question['problem_number']  . "', '" . $question['LOTag']  . "', '" . $question['DifficultyLevel'] . "')";
    pg_query($con, $query)or die("Cannot execute query: $query \n");

}

echo "Inserted values into dynamic_questions table successfully!\n";

echo "Closing connection to PostgreSQL database.\n";
pg_close($con);

?>