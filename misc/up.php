<?php
// for display purposes
header('Content-type: text/plain');


// filepath to new static questions json file
$filepath = "../assets/json_data/new_questions.json";
// read the json file to text
$json_text = file_get_contents($filepath) or die('Unable to open file.');
// decode the text into a PHP assoc array
$new_qs = json_decode($json_text, true);
//print_r($new_qs);


// loop through the directory that contains all of the students static questions json files
// insert the file names into the array
$json_files = array();
$dir = '../user_data/MATH 6 (13) - Precalculus-0966909fb1140dd434a160fe7d7d743e70c897e3/questions';
if (!is_dir($dir)) {
    exit('Invalid diretory path');
}
else {
    foreach (scandir($dir) as $file) {
        if ($file !== '.' && $file !== '..') {
            $json_files[] = $file;
        }
    }
    print_r($json_files);
}


// loop for each student file
for ($h = 0; $h < count($json_files); $h++) {

    // filepath to old static questions json file
    $filepath = "../user_data/MATH 6 (13) - Precalculus-0966909fb1140dd434a160fe7d7d743e70c897e3/questions/{$json_files[$h]}";
    // read the json file to text
    $json_text = file_get_contents($filepath) or die('Unable to open file.');
    // decode the text into a PHP assoc array
    $old_qs = json_decode($json_text, true);

    // rewrite the file keeping the old data the same, but appending the new data
    // now write the updated data to another json file
    $c = 1;
    $filepath = "../user_data/MATH 6 (13) - Precalculus-0966909fb1140dd434a160fe7d7d743e70c897e3/questions/{$json_files[$h]}";
    $q_file = fopen($filepath, "w") or die("Unable to open file!");
    fwrite($q_file, "[\n");

    // loop through the old questions
    for ($i = 0; $i < count($old_qs); $i++) {
        $str = "\t{\n";
        $str .= "\t\t\"pkey\": {$c},\n";
        $str .= "\t\t\"title\": \"{$old_qs[$i]["title"]}\",\n";
        $str .= "\t\t\"text\": \"{$old_qs[$i]["text"]}\",\n";
        $str .= "\t\t\"pic\": \"{$old_qs[$i]["pic"]}\",\n";
        $str .= "\t\t\"numTries\": \"{$old_qs[$i]["numTries"]}\",\n";
        $str .= "\t\t\"options\": [";
        // options
        for($j = 0; $j < count($old_qs[$i]["options"]); $j++) {
            if ($j === count($old_qs[$i]["options"]) - 1) {
                $str .= "\"" . $old_qs[$i]["options"][$j] . "\"],\n";
            }
            else {
                $str .= "\"" . $old_qs[$i]["options"][$j] . "\", ";
            }
        }
        // rightAnswer
        $str .= "\t\t\"rightAnswer\": [";
        for ($j = 0; $j < count($old_qs[$i]["rightAnswer"]); $j++) {
            if ($j === count($old_qs[$i]["rightAnswer"]) - 1) {
                if ($old_qs[$i]["rightAnswer"][$j] == 1) {
                    $str .= "true],\n";
                }
                else {
                    $str .= "false],\n";
                }
            }
            else{
                if ($old_qs[$i]["rightAnswer"][$j] == 1) {
                    $str .= "true, ";
                }
                else {
                    $str .= "false, ";
                }
            }
        }
        // isImage
        $str .= "\t\t\"isImage\": [";
        for ($j = 0; $j < count($old_qs[$i]["isImage"]); $j++) {
            if ($j === count($old_qs[$i]["isImage"]) - 1) {
                if ($old_qs[$i]["isImage"][$j] == 1) {
                    $str .= "true],\n";
                }
                else {
                    $str .= "false],\n";
                }
            }
            else{
                if ($old_qs[$i]["isImage"][$j] == 1) {
                    $str .= "true, ";
                }
                else {
                    $str .= "false, ";
                }
            }
        }
        $str .= "\t\t\"tags\": \"{$old_qs[$i]["tags"]}\",\n";
        $str .= "\t\t\"difficulty\": \"{$old_qs[$i]["difficulty"]}\",\n";
        $str .= "\t\t\"selected\": \"{$old_qs[$i]["selected"]}\",\n";
        $str .= "\t\t\"numCurrentTries\": \"{$old_qs[$i]["numCurrentTries"]}\",\n";
        $str .= "\t\t\"correct\": \"{$old_qs[$i]["correct"]}\",\n";
        $str .= "\t\t\"datetime_started\": \"{$old_qs[$i]["datetime_started"]}\",\n";
        $str .= "\t\t\"datetime_answered\": \"{$old_qs[$i]["datetime_answered"]}\",\n";
        $str .= "\t\t\"createdOn\": \"{$old_qs[$i]["createdOn"]},\"\n";
        $str .= "\t},\n";
        fwrite($q_file, $str);
        $c++;
    }

    // loop through the new questions now
    for ($i = 0; $i < count($new_qs); $i++) {
        $str = "\t{\n";
        $str .= "\t\t\"pkey\": {$c},\n";
        $str .= "\t\t\"title\": \"{$new_qs[$i]["title"]}\",\n";
        $str .= "\t\t\"text\": \"{$new_qs[$i]["text"]}\",\n";
        $str .= "\t\t\"pic\": \"{$new_qs[$i]["pic"]}\",\n";
        $str .= "\t\t\"numTries\": \"{$new_qs[$i]["numTries"]}\",\n";
        $str .= "\t\t\"options\": [";
        // options
        for($j = 0; $j < count($new_qs[$i]["options"]); $j++) {
            if ($j === count($new_qs[$i]["options"]) - 1) {
                $str .= "\"" . $new_qs[$i]["options"][$j] . "\"],\n";
            }
            else {
                $str .= "\"" . $new_qs[$i]["options"][$j] . "\", ";
            }
        }
        // rightAnswer
        $str .= "\t\t\"rightAnswer\": [";
        for ($j = 0; $j < count($new_qs[$i]["rightAnswer"]); $j++) {
            if ($j === count($new_qs[$i]["rightAnswer"]) - 1) {
                if ($new_qs[$i]["rightAnswer"][$j] == 1) {
                    $str .= "true],\n";
                }
                else {
                    $str .= "false],\n";
                }
            }
            else{
                if ($new_qs[$i]["rightAnswer"][$j] == 1) {
                    $str .= "true, ";
                }
                else {
                    $str .= "false, ";
                }
            }
        }
        // isImage
        $str .= "\t\t\"isImage\": [";
        for ($j = 0; $j < count($new_qs[$i]["isImage"]); $j++) {
            if ($j === count($new_qs[$i]["isImage"]) - 1) {
                if ($new_qs[$i]["isImage"][$j] == 1) {
                    $str .= "true],\n";
                }
                else {
                    $str .= "false],\n";
                }
            }
            else{
                if ($new_qs[$i]["isImage"][$j] == 1) {
                    $str .= "true, ";
                }
                else {
                    $str .= "false, ";
                }
            }
        }
        $str .= "\t\t\"tags\": \"{$new_qs[$i]["tags"]}\",\n";
        $str .= "\t\t\"difficulty\": \"{$new_qs[$i]["difficulty"]}\",\n";
        $str .= "\t\t\"selected\": \"{$new_qs[$i]["selected"]}\",\n";
        $str .= "\t\t\"numCurrentTries\": \"{$new_qs[$i]["numCurrentTries"]}\",\n";
        $str .= "\t\t\"correct\": \"{$new_qs[$i]["correct"]}\",\n";
        $str .= "\t\t\"datetime_started\": \"{$new_qs[$i]["datetime_started"]}\",\n";
        $str .= "\t\t\"datetime_answered\": \"{$new_qs[$i]["datetime_answered"]}\",\n";
        $str .= "\t\t\"createdOn\": \"{$new_qs[$i]["createdOn"]}\"\n";
        if ($i !== count($new_qs) - 1) $str .= "\t},\n";
        else $str .= "\t}\n";
        fwrite($q_file, $str);
        $c++;
    }

    fwrite($q_file, "]");
    fclose($q_file);
    chmod("../user_data/MATH 6 (13) - Precalculus-0966909fb1140dd434a160fe7d7d743e70c897e3/questions/{$json_files[$h]}", 0777) or die("Could not modify perms.\n");

}




?>