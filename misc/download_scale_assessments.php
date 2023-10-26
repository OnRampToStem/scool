<?php
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
    header("location: ../../register_login/logout.php");
    exit;
}

// set memory limit higher due to high amount of data //
ini_set('memory_limit', '256M');

// debugging //
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL & ~E_DEPRECATED);

// db connection //
require_once "../register_login/config.php";

$data = [];

// get all courses in SCALE //
$query = "SELECT DISTINCT course_name, course_id 
          FROM assessments_results";
$res = pg_query($con, $query) or die(pg_last_error($con));
while ($row = pg_fetch_assoc($res)) {
    $data[] = [
        "course_name" => $row["course_name"],
        "course_id"   => $row["course_id"],
        "data"        => []
    ];
}

// get all students' assessments results per course //
foreach ($data as &$course) {
    $query = "SELECT student_name, student_email, instructor_email, assessment_name, score, max_score, content, date_time_submitted
              FROM assessments_results
              WHERE course_name='" . pg_escape_string($course["course_name"]) . "' AND course_id='" . pg_escape_string($course["course_id"]) . "';";
    $res = pg_query($con, $query) or die(pg_last_error($con));
    $idx = 0;
    while ($row = pg_fetch_assoc($res)) {
        // set the static data //
        $course["data"][] = [
            "student_name" => $row["student_name"],
            "student_email" => $row["student_email"],
            "instructor_email" => $row["instructor_email"],
            "assessment_name" => $row["assessment_name"],
            "score" => $row["score"],
            "max_score" => $row["max_score"],
            "date_time_submitted" => $row["date_time_submitted"]
        ];

        // set the dynamic data //
        $content = json_decode($row["content"], true);
        for ($i = 0; $i < count($content); $i++) {
            $course["data"][$idx]["Q" . $i + 1 . " - Link"] = "https://imathas.libretexts.org/imathas/embedq2.php?id=" . $content[$i]["id"];
            $course["data"][$idx]["Q" . $i + 1 . " - LO"] = $content[$i]["lo"];
            $course["data"][$idx]["Q" . $i + 1 . " - Score"] = $content[$i]["result"];
            $course["data"][$idx]["Q" . $i + 1 . " - Max Score"] = $content[$i]["max_score"];
        }

        // update index //
        $idx++;
    }
}

//$data = json_encode($data, JSON_PRETTY_PRINT);
//echo "<pre>" . $data . "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SCALE Assessments Download</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body onload="downloadAssessmentData();">
    <div>
        <h1>SCALE Assessments Download</h1>
    </div>

    <script type="text/javascript">
        const downloadAssessmentData = () => {
            const data = <?= json_encode($data); ?>;

            // looping through each SCALE course //
            for (let i = 0; i < data.length; i++) {
                // timeout to introduce delay => allows for multiple downloads of CSV files //
                setTimeout(() => {
                    // setting the column headers of the CSV file //
                    let csvContent = 'Course Name, Course ID, Instructor Email, Student Name, Student Email, Assessment Name, Assessment Score, Assessment Max Score, Assessment Question LO, Assessment Question Link, Assessment Question Score, Assessment Question Max Score, Assessment Date Time Submitted \r\n';

                    // looping through //
                    for (let j = 0; j < data[i]["data"].length; j++) {
                        // calculating the number of questions //
                        let numKeys = Object.keys(data[i]["data"][j]).length;
                        numKeys = numKeys - 7;
                        numKeys = numKeys / 4;

                        for (let k = 0; k < numKeys; k++) {
                            let row = [];

                            row.push(
                                data[i]["course_name"],
                                data[i]["course_id"],
                                data[i]["data"][j]["instructor_email"],
                                data[i]["data"][j]["student_name"],
                                data[i]["data"][j]["student_email"],
                                data[i]["data"][j]["assessment_name"],
                                data[i]["data"][j]["score"],
                                data[i]["data"][j]["max_score"]
                            );

                            if (("Q" + (k + 1) + " - LO") in data[i]["data"][j]) {
                                row.push(data[i]["data"][j]["Q" + (k + 1) + " - LO"]);
                            }
                            if (("Q" + (k + 1) + " - Link") in data[i]["data"][j]) {
                                row.push(data[i]["data"][j]["Q" + (k + 1) + " - Link"]);
                            }
                            if (("Q" + (k + 1) + " - Score") in data[i]["data"][j]) {
                                row.push(data[i]["data"][j]["Q" + (k + 1) + " - Score"]);
                            }
                            if (("Q" + (k + 1) + " - Max Score") in data[i]["data"][j]) {
                                row.push(data[i]["data"][j]["Q" + (k + 1) + " - Max Score"]);
                            }

                            row.push(data[i]["data"][j]["date_time_submitted"]);

                            row = row.join(',');
                            csvContent += row + '\r\n';
                        }
                    }

                    if (csvContent !== 'Course Name, Course ID, Instructor Email, Student Name, Student Email, Assessment Name, Assessment Score, Assessment Max Score, Assessment Question LO, Assessment Question Link, Assessment Question Score, Assessment Question Max Score, Assessment Date Time Submitted \r\n') {
                        // create a Blob object from the CSV data
                        const blob = new Blob([csvContent], {
                            type: 'text/csv'
                        });

                        // generate a URL for the Blob object
                        const url = URL.createObjectURL(blob);

                        // create a link element
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = `Assessments-Data-${data[i]["course_name"]}-${data[i]["course_id"]}.csv`;

                        // simulate a click on the link to initiate the download
                        link.click();

                        // clean up by revoking the generated URL
                        URL.revokeObjectURL(url);
                    }
                }, i * 3250);
            }
        }
    </script>
</body>

</html>