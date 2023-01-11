<?php
header('Content-type: text/plain');

$json_filename = "../data/new_openStax.json";
$json = file_get_contents($json_filename);
$openStax = json_decode($json, true);

$json_filename = "../data/final.json";
$json = file_get_contents($json_filename);
$questions = json_decode($json, true);


$totals = [];

foreach($openStax as $chapter){

    for($i = 0; $i < count($chapter["Sections"]); $i++){

        for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){

            if(!isset($totals[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]])){

                $totals[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]] = 0;

            }
        }  
    }
}

$unknown_los = [];

foreach($questions as $question){

    if(isset($totals[$question["tags"]])){
        $totals[$question["tags"]]++;
    }
    else{
        //if(!isset($unknown_los[$question["tags"]])){
            $unknown_los[$question["tags"]] = 0;
        //}
        //else{
            //$unknown_los[$question["tags"]]++;
        //}
    }
}

$sum = 0;
foreach($totals as $key => $value){
    $sum += $value;
}

echo "Listing all of the possible learning outcomes provided by the OpenStax json file\n";
echo "With the values on the right side pertaining to the number of questions with that\n";
echo "learning outcome in the static questions json file:\n";

print_r($totals);

print_r($unknown_los);


echo "Total number of questions in static questions json file: ", $sum, "\n";


?>