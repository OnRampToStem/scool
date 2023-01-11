<?php
// for echo display purposes
header('Content-type: text/plain');

/* GLOBALS */
$los = array();
$total = 0;


// read and decode the JSON file (text => PHP assoc array)
$json_filename = "../assets/json_data/new_openStax.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

foreach($json_data as $chapter){

    for($i = 0; $i < count($chapter["Sections"]); $i++){

        for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){

            $los[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]] = 0;

        }
    }
}


// read and decode the JSON file (text => PHP assoc array)
$json_filename = "../assets/json_data/final.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// first loop through each dynamic question
foreach($json_data as $question){

    $total++;

    // if that learning outcome has been set in the $los array, then increment value by 1
    if(isset($los[$question['tags']])){
        $los[$question['tags']]++;
    }

}

// print results
echo 'Total number of static questions: ' . $total . "\n";
print_r($los);


/* now write data into a file*/
/*
$dynamic_file = fopen("/Applications/MAMP/htdocs/hub_v1/dynamic_questions/dynamic.json", "w") or die("Unable to open file!");
fwrite($dynamic_file, "{");
$str = "";
foreach ($los as $key => $value) {
    $str .= "\n\t\"${key}\": $value,";
}
// removing last comma
$str = substr($str, 0, -1);
// more append
$str .= "\n}";
// write
fwrite($dynamic_file, $str);
*/

?>