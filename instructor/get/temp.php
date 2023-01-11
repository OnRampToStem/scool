<?php
//for display purposes
header("Content-type: text/plain");

// initialize the session (access to user: loggedIn, first_name, email, type)
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