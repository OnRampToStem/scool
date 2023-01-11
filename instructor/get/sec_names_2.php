<?php
/*
    This php script will return a php associate array, the key being the [section number]
    and the value being the [section name]. However, it will only extract non-empty
    sections, using the static questions json file as reference to this.
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

// receiving $_POST inputs
$ch_index = (int)$_POST["chapter"]; // holds single chapter digit

// globals
$sections = []; // $sections will be an assoc array holding: "section number" => number of questions in that specific sec
$sections_data = []; // $sections_data will be an assoc array holding: "section number" => "section name"


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// first get all possible sections from the selected chapter
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        foreach($chapter["Sections"] as $section){
            $sections[$chapter["Index"] . "." . $section["Index"]] = 0;
        }
    }
}


// filepath
$json_filename = "../../assets/json_data/final.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$questions = json_decode($json, true);

// summing number of sections
foreach($questions as $question){
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    if(isset($sections[substr($question["tags"], 0, $pos)])){
        $sections[substr($question["tags"], 0, $pos)]++;
    }
}


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// inserting sections that have questions in them
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        foreach($chapter["Sections"] as $section){
            if($sections[$chapter["Index"] . "." . $section["Index"]] !== 0){
                $sections_data[$chapter["Index"] . "." . $section["Index"]] = $section["Name"];
            }
        }
    }
}

//print_r($sections);
//print_r($sections_data);

// send back sections_data
echo json_encode($sections_data);

?>