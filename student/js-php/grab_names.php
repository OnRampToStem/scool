<?php
// for display purposes
header('Content-type: text/plain');

// globals
$names = [];

// receiving $_POST inputs
$ch = (int)$_POST["ch"]; // holds single chapter digit
$sec = (int)$_POST["sec"]; // holds single section digit
$lo = (int)$_POST["lo"]; // holds single lo digit

//
$json_filename = "../../assets/json_data/openStax.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// loop through openStax to check access
foreach ($json_data as $chapter) {

    if ($chapter["Index"] === $ch) {

        array_push($names, $chapter["Index"] . ". " . $chapter["Name"]);

        foreach ($chapter["Sections"] as $section) {

            if ($section["Index"] === $sec) {

                array_push($names, $chapter["Index"] . "." . $section["Index"] . ". " . $section["Name"]);

                foreach ($section["LearningOutcomes"] as $learningoutcome) {

                    if ($learningoutcome["Index"] === $lo) {
                        array_push($names, $chapter["Index"] . "." . $section["Index"] . "." . $learningoutcome["Index"] . ". " . $learningoutcome["Name"]);
                    }
                }
            }
        }
    }
}

// send back result
echo json_encode($names);

?>