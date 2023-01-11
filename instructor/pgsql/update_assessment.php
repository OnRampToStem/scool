<?php
// start the session (access to user: loggedIn, first_name, email, type)
session_start();

// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// if user account type is not 'instructor' then force logout
if($_SESSION["type"] !== "instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

// receive POST inputs
$pkey = $_POST["pkey"];
$name = $_POST["name"];
$public = $_POST["public"];
$duration = $_POST["duration"];
$open_date = $_POST["open_date"];
$open_time = $_POST["open_time"];
$close_date = $_POST["close_date"];
$close_time = $_POST["close_time"];
$los = json_decode($_POST["los"]);
$questions = json_decode($_POST["questions"]);
$points = json_decode($_POST["points"]);
$num_of_selected_los = $_POST["num_of_selected_los"];

// create the json content string
$json_content = "[";
for($i = 0; $i < $num_of_selected_los; $i++){
    // first entries
    if($i !== $num_of_selected_los - 1){
        $json_content .= "{";
        $json_content .= "\"LearningOutcomeNumber\": \"" . $los[$i] . "\",";
        $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
        $json_content .= "\"NumberPoints\": " . $points[$i];
        $json_content .= "},";
    }
    // last entry
    else{
        $json_content .= "{";
        $json_content .= "\"LearningOutcomeNumber\": \"" . $los[$i] . "\",";
        $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
        $json_content .= "\"NumberPoints\": " . $points[$i];
        $json_content .= "}";
    }
}
$json_content .= "]";

/*
echo $pkey, "\n";
echo $name, "\n";
echo $public, "\n";
echo $duration, "\n";
echo $open_date, "\n";
echo $open_time, "\n";
echo $close_date, "\n";
echo $close_time, "\n";
print_r($los);
print_r($questions);
print_r($points);
echo $num_of_selected_los, "\n";
echo $json_content, "\n";
*/

// connect to the db
require_once "../../register_login/config.php";

// query
$query = "UPDATE assessments SET name = '{$name}', public = '{$public}', duration = '{$duration}', open_date = '{$open_date}',
          open_time = '{$open_time}', close_date = '{$close_date}', close_time = '{$close_time}', content = '{$json_content}'
          WHERE pkey = '{$pkey}'";
$res = pg_query($con, $query);

// check for errors
if(!$res) {
    // error
    echo "There was an error with the request for update.";
} else {
    // no error
    echo "Successfully updated assessment.";
}

?>