<?php
session_start(); // session keys: loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // receive data
    $assessment_name = $_POST["assessment_name"];

    // local variables to hold data
    $students = [];
    $student_scores = [];

    // connect to the db
    require_once "../../register_login/config.php";

    // 1.
    // get all the students in the class corresponding to the instructor
    $query = "SELECT name, email FROM users 
              WHERE instructor='{$_SESSION["email"]}' AND course_name='{$_SESSION["selected_course_name"]}'
              AND course_id='{$_SESSION["selected_course_id"]}'";

    $res = pg_query($con, $query) or die(pg_last_error($con));

    while ($row = pg_fetch_assoc($res)) {
        $student = new stdClass();
        $student->name = $row["name"];
        $student->email = $row["email"];
        array_push($students, $student);
    }

    // 2.
    // get the score and content for each student's assessment submission
    for ($i = 0; $i < count($students); $i++) {
        $query = "SELECT score, max_score, content FROM assessments_results 
                  WHERE assessment_name='$assessment_name' AND instructor_email='{$_SESSION["email"]}' AND
                  student_email='{$students[$i]->email}' AND student_name='{$students[$i]->name}' AND
                  course_name='{$_SESSION["selected_course_name"]}' AND course_id='{$_SESSION["selected_course_id"]}'
                  ORDER BY student_name";

        $res = pg_query($con, $query) or die(pg_last_error($con));

        // student exists in class but hasn't taken the assessment
        if (pg_num_rows($res) === 0) {
            $obj = new stdClass();
            $obj->name = $students[$i]->name;
            $obj->email = $students[$i]->email;
            $obj->status = "incomplete";
            array_push($student_scores, $obj);
        }
        // student exists in class and has taken the assessment
        else {
            while ($row = pg_fetch_assoc($res)) {
                $obj = new stdClass();
                $obj->name = $students[$i]->name;
                $obj->email = $students[$i]->email;
                $obj->content = $row["content"];
                $obj->score = $row["score"];
                $obj->max_score = $row["max_score"];
                $obj->status = "complete";
                array_push($student_scores, $obj);
            }
        }
    }

    // send back data
    echo (json_encode($student_scores));
}
