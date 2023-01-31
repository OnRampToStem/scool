<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

$data = [];

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // receive POST inputs
    $assessment_name = $_POST['assessment_name'];

    // connect to the db
    require_once "../../register_login/config.php";

    // get all assessments
    $query = "SELECT * FROM assessments_results WHERE assessment_name = '{$assessment_name}' AND instructor_email = '{$_SESSION['email']}'
              AND course_name = '{$_SESSION['selected_course_name']}' AND course_id = '{$_SESSION['selected_course_id']}'";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");

    while($row = pg_fetch_row($res)){
        $assoc_arr = array(
            "student_email" => $row[3],
            "date_time_submitted" => $row[6],
            "score" => $row[7],
            "content" => $row[8]
        );
        array_push($data, $assoc_arr);
    }
    
    pg_close($con);

    echo json_encode($data);

}

?>