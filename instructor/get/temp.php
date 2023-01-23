<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

//for display purposes
header("Content-type: text/plain");

$data;

// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);
// loop through each chapter
foreach($json_openStax as $chapter){

    // insert chapters
    $data[$chapter["Index"]] = $chapter["Name"];

    foreach($chapter["Sections"] as $section){

        // insert sections
        $data[$chapter["Index"] . "." . $section["Index"]] = $section["Name"];

        foreach($section["LearningOutcomes"] as $lo){

            // insert los
            $data[$chapter["Index"] . "." . $section["Index"] . "." . $lo["Index"]] = $lo["Name"];
        }
    }
}

print_r($data);

/* now write data into a file*/
/*
$dynamic_file = fopen("/Applications/MAMP/htdocs/hub_v1/instructor/get/data.json", "w") or die("Unable to open file!");
fwrite($dynamic_file, "{");
$str = "";
foreach ($data as $key => $value) {
    $str .= "\n\t\"${key}\": \"$value\",";
}
// removing last comma
$str = substr($str, 0, -1);
// more append
$str .= "\n}";
// write
fwrite($dynamic_file, $str);
*/


?>