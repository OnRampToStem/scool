<?php

/*
 * Student Centered Open Online Learning (SCOOL) LTI Integration
 * Copyright (c) 2021-2024  Fresno State University, SCOOL Project Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

require_once "../bootstrap.php";

// start PHP session //
// loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id //
session_start();

// user not logged in => redirect to FS Canvas //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// user not 'Instructor' => force logout //
if ($_SESSION["type"] !== "Instructor") {
    header("location: /register_login/logout.php");
    exit;
}

// debugging //
//ini_set("display_errors", 1);
//ini_set("display_startup_errors", 1);
//error_reporting(E_ALL & ~E_DEPRECATED);

// set memory limit higher due to high amount of data //
ini_set('memory_limit', '256M');

$main = []; // holds course names & students' filenames in those courses //

// GETTING ALL COURSE DIRECTORIES INSIDE USER DATA DIRECTORY //
if ($handle = opendir(USER_DATA_DIR)) { // open the user data directory //
    // loop through the course directories inside the user data directory //
    while (false !== ($course_dir_name = readdir($handle))) {
        // exclude special directories //
        if ($course_dir_name !== ".DS_Store" && $course_dir_name !== "." && $course_dir_name !== "..") {
            $main[$course_dir_name] = [];
        }
    }
}

// GETTING ALL STUDENTS QUESTIONS FILENAMES //
foreach ($main as $course_dir_name => $arr) {
    $dynamic_dir_name = USER_DATA_DIR . "/$course_dir_name/questions";
    if ($handle = opendir($dynamic_dir_name)) {
        // loop through files inside directory //
        while (false !== ($filename = readdir($handle))) {
            // only include files ending with .json //
            if (strpos($filename, ".json") !== false) {
                array_push($main[$course_dir_name], $filename);
            }
        }
    }
}

$data = []; // holds all students' questions data //

foreach ($main as $course_name => $student_filename_arr) {
    $course = []; // holds sub-arrays containing questions answered by students //

    for ($i = 0; $i < count($student_filename_arr); $i++) {
        $filepath = USER_DATA_DIR . "/$course_name/questions/$student_filename_arr[$i]";
        $json_text = file_get_contents($filepath); // read txt from file //
        $json_data = json_decode($json_text, true); // text => PHP assoc arr //

        $arr = []; // local array //

        // loop through each question in the file //
        for ($j = 0; $j < count($json_data); $j++) {
            if ($json_data[$j]["datetime_answered"] !== "") {
                $q = [
                    'course'            => $course_name,
                    'email'             => $student_filename_arr[$i],
                    'tags'              => $json_data[$j]["tags"],
                    'text'              => $json_data[$j]["text"],
                    'numCurrentTries'   => $json_data[$j]["numCurrentTries"],
                    'numTries'          => $json_data[$j]["numTries"],
                    'correct'           => $json_data[$j]["correct"],
                    'datetime_started'  => $json_data[$j]["datetime_started"],
                    'datetime_answered' => $json_data[$j]["datetime_answered"]
                ];
                /*
                $q->pkey = $json_data[$j]["pkey"];
                $q->title = $json_data[$j]["title"];
                $q->pic = $json_data[$j]["pic"];
                $q->options = $json_data[$j]["options"];
                $q->rightAnswer = $json_data[$j]["rightAnswer"];
                $q->isImage = $json_data[$j]["isImage"];
                $q->difficulty = $json_data[$j]["difficulty"];
                $q->selected = $json_data[$j]["selected"];
                $q->createdOn = $json_data[$j]["createdOn"];
                */
                array_push($arr, $q);
            }
        }
        array_push($course, $arr);
    }
    array_push($data, $course);
}
unset($main);
//$json = json_encode($data, JSON_PRETTY_PRINT);
//echo "<pre>" . $json . "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Download All</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script type="text/javascript">
        const main = <?= json_encode($data); ?>; // converting php array to js array
        let counter = 1;

        // loop through courses array
        for (let i = 0; i < main.length; i++) {

            // ignore the empty courses
            if (main[i].length !== 0) {

                // Set a timeout to introduce a delay between each iteration
                setTimeout(() => {

                    // Setting the column headers of the CSV file //
                    let csvContent = 'Course, Student Email, Learning Outcome Number, Text, Student Attempts, Maximum Allowed Attempts, Correct, Date Time Started, Date Time Answered \r\n';

                    main[i].forEach((student) => {

                        let row = [];

                        // loop through each question in the array
                        for (let j = 0; j < student.length; j++) {

                            // removing comma and BR from text if applicable
                            if (student[j]["text"].includes(',')) {
                                // regex: match all instances of the comma globally and remove them by replacing them with an empty string
                                student[j]["text"] = student[j]["text"].replace(/,/g, '');
                            }
                            if (student[j]["text"].includes('BR')) {
                                // regex: match all instances of BR globally and remove them by replacing them with an empty string
                                student[j]["text"] = student[j]["text"].replace(/BR/g, '');
                            }

                            row.push(
                                student[j]["course"], student[j]["email"], student[j]["tags"], student[j]["text"],
                                student[j]["numCurrentTries"], student[j]["numTries"], student[j]["correct"],
                                student[j]["datetime_started"], student[j]["datetime_answered"]
                            );
                            row = row.join(',');
                            csvContent += row + '\r\n';
                            row = [];
                        }
                    });

                    //console.log(csvContent);

                    if (csvContent !== 'Course, Student Email, Learning Outcome Number, Text, Student Attempts, Maximum Allowed Attempts, Correct, Date Time Started, Date Time Answered \r\n') {
                        // create a Blob object from the CSV data
                        const blob = new Blob([csvContent], {
                            type: 'text/csv'
                        });

                        // generate a URL for the Blob object
                        const url = URL.createObjectURL(blob);

                        // create a link element
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = `Students-OpenStax-Data-${counter}.csv`;

                        // simulate a click on the link to initiate the download
                        link.click();

                        // clean up by revoking the generated URL
                        URL.revokeObjectURL(url);

                        // update counter
                        counter++;
                    }
                }, i * 3250);
            }
        }
    </script>
</head>

<body>
    <div>
        <h1>Download All</h1>
    </div>
</body>

</html>
