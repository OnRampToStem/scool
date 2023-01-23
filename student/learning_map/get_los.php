<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if($_SESSION["type"] !== "Learner"){
    header("location: ../register_login/logout.php");
    exit;
}

// for display purposes
//header("Content-type: text/plain");

// receiving inputs
$ch_num = (int)$_POST["chapter"];
//$ch_num = 1;
$sec_num = (int)$_POST["section"];
//$sec_num = 1;


// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
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