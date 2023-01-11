<?php
/*
    This php script will return a php associate array, the key being the [chapter number]
    and the value being the [chapter name]. However, it will only extract non-empty
    chapters, using the static questions json file as reference to this.
*/

// for display purposes
//header("Content-type: text/plain");

// initialize the session (access to user: loggedIn, first_name, email, type)
session_start();
  
// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// globals
$chapters = []; // $chapters will be an assoc array holding: "chapter number" => number of questions in that specific ch
$chapters_data = []; // $chapters_data will be an assoc array holding: "chapter number" => "chapter name"


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// first get all possible chapters
foreach($json_openStax as $chapter){
    $chapters[strval($chapter["Index"])] = 0;
}


// filepath
$json_filename = "../../assets/json_data/final.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$questions = json_decode($json, true);

// summing number of chapters
foreach($questions as $question){
    if(isset($chapters[strtok($question["tags"], ".")])){
        $chapters[strtok($question["tags"], ".")]++;
    }
}


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// inserting chapters that have questions in them
foreach($json_openStax as $chapter){
    if($chapters[strval($chapter["Index"])] !== 0){
        $chapters_data[$chapter["Index"]] = $chapter["Name"];
    }
}

//print_r($chapters_data);

// send back chapters_data 
echo json_encode($chapters_data);

?>