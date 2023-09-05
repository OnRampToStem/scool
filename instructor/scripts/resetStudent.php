<?php
// start the session //
// (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id) //
session_start();

// redirect users if not logged in //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// force logout for non-instructors //
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

$result = null;

// handle GET request //
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // get data //
    $student_email = $_GET["student_email"];

    // data validation //
    if ($student_email === "" || !$student_email) {
        $result = "Student Email is not valid. Try again.";
        exit;
    }

    // delete the student //
    require_once "../../register_login/config.php";
    $query = "DELETE FROM users WHERE email='" . pg_escape_string($student_email) . "' AND type='Learner';";
    $res = pg_query($con, $query) or die(pg_last_error($con));
    if (pg_affected_rows($res) > 0) {
        $result = $student_email . " deleted successfully.";
    } else {
        $result = $student_email .  " not found or deletion failed.";
    }
    pg_close($con);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Student</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body>
    <div style="margin: 0 auto; text-align: center;">
        <h1><?= ($result !== null ? $result : "Error.") ?></h1>
        <a href="../instr_index1.php">Click here to go Home</a>
    </div>
</body>

</html>