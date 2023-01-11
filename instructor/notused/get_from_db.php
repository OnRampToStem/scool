<?php
/*
    This PHP script is used to run a query on the PostgreSQL database and then send back the data
    to index.js The query will select and group all questions in the database that have the existing 
    $tag in their "tags" attribute. It will also modify the attribute "options" string, to ensure 
    it is 100% able to be parsed using JavaScript with no errors.
*/

// POST sent from index.js
$tag = $_POST['tag'];

// connect to the db
require_once "../register_login/config.php";

// query
$query = "SELECT * FROM questions WHERE tags = '$tag'"; 
$result = pg_query($con, $query) or die("Cannot execute query: $query\n");

// fetch all results
$arr = pg_fetch_all($result);

// will contain questions from the database
$db_string = "";

// if contains data
if($arr){

    // grab row by row until null
    $counter = 0;
    while ($row = pg_fetch_row($result)) {

        /* MODIFICATION OF OPTIONS STRING TO AVOID PARSE ERRORS */

        // first remove { from options string $row[5]
        $row[5] = substr($row[5], 1);
        // then remove } from options string $row[5]
        $row[5] = substr($row[5], 0, -1);
        // then remove all double quotes from options string $row[5]
        $row[5] = str_replace('"', '', $row[5]);
        //echo $row[5], "\n";

        // convert options string $row[5] => to an array (based on commas)
        $options_arr = explode(",", $row[5]);
        //var_dump($options_arr);

        /* END OF MODIFICATION OF OPTIONS STRING TO AVOID PARSE ERRORS */

        // get options_arr length
        $options_length = count($options_arr);

        if($counter == count($arr) - 1){

            $db_string .= "&*\"pkey\": $row[0], \"title\": \"$row[1]\", \"text\": \"$row[2]\", \"pic\": \"$row[3]\", \"numTries\": \"$row[4]\", \"options\": [";
            
            // insert each option into $db_string
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    $db_string .= "\"$options_arr[$i]\"], ";
                }
                else{
                    $db_string .= "\"$options_arr[$i]\",";
                }
            }
            
            // insert the rest into $db_string
            $db_string .= "\"rightAnswer\": $row[6], \"isImage\": $row[7], \"tags\": \"$row[8]\", \"difficulty\": \"$row[9]\", \"numCurrentTries\": $row[10], \"correct\": \"$row[11]\", \"datetime_answered\": \"$row[12]\", \"createdOn\": \"$row[13]\"*&";

        }
        else{

            $db_string .= "&*\"pkey\": $row[0], \"title\": \"$row[1]\", \"text\": \"$row[2]\", \"pic\": \"$row[3]\", \"numTries\": \"$row[4]\", \"options\": [";
            
            // insert each option into $db_string
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    $db_string .= "\"$options_arr[$i]\"], ";
                }
                else{
                    $db_string .= "\"$options_arr[$i]\",";
                }
            }
            
            // insert the rest into $db_string
            $db_string .= "\"rightAnswer\": $row[6], \"isImage\": $row[7], \"tags\": \"$row[8]\", \"difficulty\": \"$row[9]\", \"numCurrentTries\": $row[10], \"correct\": \"$row[11]\", \"datetime_answered\": \"$row[12]\", \"createdOn\": \"$row[13]\"*&,";

        }

        $counter++;

    }

    // replacing unwanted { and } characters used in PostgreSQL with [ and ] used in JSON
    $str1 = str_replace('{', '[', $db_string);
    $str2 = "[" . str_replace('}', ']', $str1) . "]";
    $str3 = str_replace('&*', '{', $str2);
    $str4 = str_replace('*&', '}', $str3);

    // sending back finalized data
    echo $str4;

}
// contains no data
else{
    echo 'No questions associated with ' . $tag . ' in the db.';
    exit;
}

//echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>