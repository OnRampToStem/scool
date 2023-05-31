<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

$obj = new stdClass();

$root_dir = "../user_data";

// open the root directory
if ($handle = opendir($root_dir)) {

    // loop through the sub-directories inside the root directory
    while (false !== ($sub_dir = readdir($handle))) {

        // exclude special directories
        if ($sub_dir != "." && $sub_dir != ".." && $sub_dir != ".DS_Store") {

            $obj->$sub_dir = [];
        }
    }
}


// Loop through the object's properties
foreach ($obj as $key => $value) {
    $dyna_dir = "../user_data/$key/questions";

    // open the dynamic directory
    if ($handle = opendir($dyna_dir)) {

        // loop through the sub-directories inside the root directory
        while (false !== ($filename = readdir($handle))) {

            // exclude special directories
            if ($filename != "." && $filename != ".." && $filename != ".DS_Store") {

                array_push($obj->$key, $filename);
            }
        }
    }
}

//print_r($obj);


$main = [];


// loop through the PHP object
foreach ($obj as $key => $value) {
    // $key is the name of the course as a string
    // $value is an array containing the student questions json files as strings

    $course = []; // holds subarrays containing questions answered by students

    // loop through the array of the student questions json files
    for ($i = 0; $i < count($value); $i++) {

        // initialize the file path (student's static questions json file)
        $filepath = "../user_data/$key/questions/$value[$i]";
        // read the text from the file
        $json_text = file_get_contents($filepath);
        // convert text to PHP assoc array
        $json_data = json_decode($json_text, true);

        // local array
        $arr = [];

        // loop through each question in the file
        for ($j = 0; $j < count($json_data); $j++) {
            if ($json_data[$j]["datetime_answered"] !== "") {
                // create PHP object containing student name & email + question data
                $q = new stdClass();
                $q->course = $key;
                $q->email = $value[$i];
                $q->tags = $json_data[$j]["tags"];
                $q->text = $json_data[$j]["text"];
                $q->numCurrentTries = $json_data[$j]["numCurrentTries"];
                $q->numTries = $json_data[$j]["numTries"];
                $q->correct = $json_data[$j]["correct"];
                $q->datetime_started = $json_data[$j]["datetime_started"];
                $q->datetime_answered = $json_data[$j]["datetime_answered"];
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

    array_push($main, $course);
}

/*
echo "<pre>";
print_r($main);
echo "</pre>";
*/

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Download All</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script type="text/javascript">
        const main = <?= json_encode($main); ?>; // converting php array to js array      
        console.log(main);

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
                }, i * 2500);
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