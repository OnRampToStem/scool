<?php
/*
    This php script will return a php associate array, the key being the [learning outcome number]
    and the value being the [learning outcome name]. However, it will only extract non-empty
    learning outcomes, using the static questions json file as reference to this.
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
$ch_index = (int)$_POST["chapter"]; // holds single chapter digit (ex: 1)
$sec_index = (int)$_POST["section"]; // holds single section digit (ex: 2)

// globals
$los = []; // $los will be an assoc array holding: "lo number" => number of questions in that specific lo
$los_data = []; // $los_data will be an assoc array holding: "lo number" => "lo name"


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// first get all possible los from the selected chapter and selected section
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        foreach($chapter["Sections"] as $section){
            if($section["Index"] === $sec_index){
                foreach($section["LearningOutcomes"] as $lo){
                    $los[$chapter["Index"] . "." . $section["Index"] . "." . $lo["Index"]] = 0;
                }
            }
        }
    }
}


// filepath
$json_filename = "../../assets/json_data/final.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$questions = json_decode($json, true);

// summing number of questions per los
foreach($questions as $question){
    if(isset($los[$question["tags"]])){
        $los[$question["tags"]]++;
    }
}


// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// inserting loss that have questions in them
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        foreach($chapter["Sections"] as $section){
            if($section["Index"] === $sec_index){
                foreach($section["LearningOutcomes"] as $lo){
                    if($los[$chapter["Index"] . "." . $section["Index"] . "." . $lo["Index"]] !== 0){
                        $los_data[$chapter["Index"] . "." . $section["Index"] . "." . $lo["Index"]] = $lo["Name"];
                    }
                }
            }
        }
    }
}

//print_r($los);
//print_r($los_data);

// send back json_encode los_data
echo json_encode($los_data);

?>