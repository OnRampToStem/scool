<?php
/*
    This PHP script will be used to take an input JSON file of math questions and 
    insert them directly into the PostgreSQL database, "questions" table.
*/

header('Content-type: text/plain');

// read and decode the JSON file (text => PHP assoc array)
$json_filename = "../assets/json_data/new_final.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// connect to the db
require_once "../register_login/config.php";

// create the table questions if it does not exist in the PostgreSQL database
//echo "Attempting to create table questions.\n";
$query = "CREATE TABLE IF NOT EXISTS questions (
    pkey serial primary key,
    title TEXT NOT NULL,
    text TEXT NOT NULL,
    pic TEXT,
    numtries VARCHAR(3) NOT NULL,
    options TEXT[] NOT NULL,
    rightanswer TEXT[] NOT NULL,
    isimage TEXT[] NOT NULL,
    tags TEXT NOT NULL,
    difficulty TEXT,
    selected TEXT,
    numcurrenttries INT,
    correct TEXT,
    datetime_started TIMESTAMP,
    datetime_answered TIMESTAMP,
    createdon TIMESTAMP
)";
pg_query($con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($con) . ".\n");
echo "The questions table has been successfully created or was already there!\n";

// create timestamp to be inserted for datetime_answered attribute
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$timestamp = $date->format('Y-m-d H:i:s');

// looping through each question
foreach ($json_data as $element){
    
    // replacing all '\n' to BR, to fix error upon displaying math question from database to client side
    if(strpos($element['text'], "\n")){
        $element['text'] = str_replace("\n", " BR ", $element['text']);
    }

    $title = $element['title'];

    $text = $element['text'];

    $pic = $element['pic'];

    $num_tries = $element['numTries'];

    // using {} instead of [] for psql array insert into table for options, rightAnswer, and isImage
    $arr_count = count($element['options']);
    // options
    $options = "{ ";
    for($i = 0; $i < $arr_count; $i++){
        // replacing inner commas from the options with *%
        // when accessing in the data we will replace the *% with the commas 
        if(strpos($element['options'][$i], ",")){
            $element['options'][$i] = str_replace(",", "*%", $element['options'][$i]);
        }
        // removing {
        if(strpos($element['options'][$i], "{")){
            $element['options'][$i] = str_replace("{", "", $element['options'][$i]);
        }
        // removing }
        if(strpos($element['options'][$i], "}")){
            $element['options'][$i] = str_replace("}", "", $element['options'][$i]);
        }
        if($i !== $arr_count - 1){
            $options .= $element['options'][$i] . ", ";
        }
        else{
            $options .= $element['options'][$i];
        }
    }
    $options .= " }";

    // rightAnswer
    $right_answer = "{ ";
    for($i = 0; $i < $arr_count; $i++){
        if($i !== $arr_count - 1){
            if($element['rightAnswer'][$i] == 1){
                $right_answer .= "true, ";
            }
            else{
                $right_answer .= "false, ";
            }
        }
        else{
            if($element['rightAnswer'][$i] == 1){
                $right_answer .= "true";
            }
            else{
                $right_answer .= "false";
            }
        }
    }
    $right_answer .= " }";

    // isImage
    $is_image = "{ ";
    for($i = 0; $i < $arr_count; $i++){
        if($i !== $arr_count - 1){
            if($element['isImage'][$i] == 1){
                $is_image .= "true, ";
            }
            else{
                $is_image .= "false, ";
            }
        }
        else{
            if($element['isImage'][$i] == 1){
                $is_image .= "true";
            }
            else{
                $is_image .= "false";
            }
        }
    }
    $is_image .= "} ";

    $tags = $element['tags'];

    $difficulty = $element['difficulty'];

    // dealing with empty or null values in the JSON file
    $num_current_tries;
    $correct;
    $datetime_started;
    $datetime_answered;
    if($element['numCurrentTries'] == null){
        $num_current_tries = 0;
    }
    if($element['correct'] == null){
        $correct = "null";
    }
    if($element['datetime_started'] == ""){
        $datetime_started = "null";
    }
    if($element['datetime_answered'] == ""){
        $datetime_answered = "null";
    }

    $selected = "false";

    // inserting user values into table, (manually adding ' ' needed for PostgreSQL query strings / text)
    $query = "INSERT INTO questions(title, text, pic, numtries, options, rightanswer, isimage, tags, difficulty, selected, numcurrenttries, correct, datetime_started, datetime_answered, createdon) VALUES ('" . $title . "', '" . $text . "', '" . $pic . "', '" . $num_tries . "', '" . $options  . "', '" . $right_answer  . "', '" . $is_image  . "', '" . $tags . "', '" . $difficulty . "', '" . $selected . "', " . $num_current_tries . ", " . $correct . ", " . $datetime_started . ", " . $datetime_answered . ", '" . $timestamp . "')";
    //echo "Your insert query is: " . $query . "\n";
    pg_query($con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($con) . ".\n");

}
echo "Inserted values into questions table successfully!\n";

echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>