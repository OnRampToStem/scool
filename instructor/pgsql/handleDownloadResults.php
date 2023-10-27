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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // connect to the db //
    require_once "../../register_login/config.php";

    $assessment_name = $_POST["assessment_name"];

    // get all the students in the class corresponding to the instructor //
    $students = [];
    $query = "SELECT name, email FROM users 
              WHERE instructor='" . $_SESSION["email"] . "' AND course_name='" . $_SESSION["selected_course_name"] . "'
              AND course_id='" . $_SESSION["selected_course_id"] . "';";
    $res = pg_query($con, $query) or die(pg_last_error($con));
    if ($res) {
        if (pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $students[] = [
                    "name"  => $row["name"],
                    "email" => $row["email"]
                ];
            }
        }
    }

    // get the assessment data for each student's assessment submission //
    $assessment_data = [];
    foreach ($students as $student) {
        $query = "SELECT score, max_score, content, date_time_submitted FROM assessments_results 
                  WHERE assessment_name='" . $assessment_name . "' AND instructor_email='" . $_SESSION["email"] . "' 
                    AND student_email='" . $student["email"] . "' AND student_name='" . $student["name"] . "' 
                    AND course_name='" . $_SESSION["selected_course_name"] . "' AND course_id='" . $_SESSION["selected_course_id"] . "'
                  ORDER BY student_name;";
        $res = pg_query($con, $query) or die(pg_last_error($con));
        if ($res) {
            // student has taken assessment //
            if (pg_num_rows($res) > 0) {
                while ($row = pg_fetch_assoc($res)) {
                    // set the static data //
                    $data = [
                        "name"                => $student["name"],
                        "email"               => $student["email"],
                        "status"              => "complete",
                        "score"               => $row["score"],
                        "max_score"           => $row["max_score"],
                        "date_time_submitted" => $row["date_time_submitted"]
                    ];

                    // set the dynamic data //
                    $content = json_decode($row["content"], true);
                    for ($i = 0; $i < count($content); $i++) {
                        $data["Q" . $i + 1 . " - Link"] = "https://imathas.libretexts.org/imathas/embedq2.php?id=" . $content[$i]["id"];
                        $data["Q" . $i + 1 . " - LO"] = $content[$i]["lo"];
                        $data["Q" . $i + 1 . " - Score"] = $content[$i]["result"];
                        $data["Q" . $i + 1 . " - Max Score"] = $content[$i]["max_score"];
                    }

                    // push //
                    $assessment_data[] = $data;
                }
            }
            // student has not taken assessment //
            else if (pg_num_rows($res) === 0) {
                $assessment_data[] = [
                    "name"   => $student["name"],
                    "email"  => $student["email"],
                    "status" => "incomplete"
                ];
            }
        }
    }

    // send back data
    echo (json_encode($assessment_data));
}
