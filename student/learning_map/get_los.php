<?php
// for display purposes
header("Content-type: text/plain");
/*
    This PHP script is being used to 
*/

// initialize the session
// (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id)
session_start();
  
// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// receiving inputs
$user = $_POST["user"];
//$user = "christinewayte@mail.fresnostate.edu";
$ch_num = (int)$_POST["chapter"];
//$ch_num = 1;
$sec_num = (int)$_POST["section"];
//$sec_num = 1;


// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "-" . $_SESSION['section_id'] . "/openStax/" . $user . ".json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

$los_data = [];


foreach($json_openStax as $chapter){

    if($chapter["Index"] === $ch_num && $chapter["Access"] === "True"){

        for($i = 0; $i < count($chapter["Sections"]); $i++){

            if($chapter["Sections"][$i]["Index"] === $sec_num && $chapter["Sections"][$i]["Access"] === "True"){

                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){

                    if($chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] === "True"){
                        $los_data[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]] = "A" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"];
                    }
                    else {
                        $los_data[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]] = "N" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"];
                    }
                }
            }
        }
    }
}

//print_r($los_data);

// send back sections_data 
$json_encoded_sections_data = json_encode($los_data);
echo $json_encoded_sections_data;

?>