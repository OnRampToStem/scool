<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if($_SESSION["type"] !== "Learner"){
    header("location: ../register_login/logout.php");
    exit;
}

// setting to CA timezone
date_default_timezone_set('America/Los_Angeles');
$curr_date = date_create();
$curr_date = date_format($curr_date, "Y-m-d"); //echo $curr_date, "\n";

// connect to the db
require_once "../register_login/config.php";


// 1
// grab instructor's email
$query = "SELECT instructor FROM users WHERE email = '{$_SESSION["email"]}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");
$instr_email = pg_fetch_result($res, 0);


// 2
// grab all past assessments that belong to the Learner's instructor, course_name, and course_id
$past_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND close_date < '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

while($row = pg_fetch_row($res)){
    if(!isset($past_assessments[$row[0]])) {
        $past_assessments[$row[0]] = [];
        array_push($past_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
    }
}


// 3
// before getting and displaying all current assessments, get the list of all assessments completed by the student
$complete_assessments = array();
$query = "SELECT assessment_name FROM assessments_results WHERE instructor_email = '{$instr_email}' AND student_email = '{$_SESSION['email']}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
if (pg_num_rows($res) !== 0) {
    // loop through possible rows
    while ($row = pg_fetch_row($res)) {
        array_push($complete_assessments, $row[0]);
    }
}

// 4
// grab all current assessments that belong to the Learner's instructor, course_name, and course_id
$open_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date <= '{$curr_date}' AND close_date >= '{$curr_date}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
// filter out the complete current assessments
while($row = pg_fetch_row($res)){
    // filter by assessment_name
    if (!in_array($row[2], $complete_assessments)) {
        if(!isset($open_assessments[$row[0]])) {
            $open_assessments[$row[0]] = [];
            array_push($open_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// 5
// grab all future assessments that belong to the Learner's instructor, course_name, and course_id
$future_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date > '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

while($row = pg_fetch_row($res)){
    if(!isset($future_assessments[$row[0]])) {
        $future_assessments[$row[0]] = [];
        array_push($future_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
    }
}

/*
print_r($past_assessments);
echo "\n";
print_r($complete_assessments);
echo "\n";
print_r($open_assessments);
echo "\n";
print_r($future_assessments);
echo "\n";
*/

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OR2STEM - Student Assessments</title>
        <link rel="stylesheet" href="../assets/css/student/student_assessment1.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body onload="displayAssessments();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../register_login/logout.php">Logout</a>
                        </div>
                        <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A" href="student_index.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main>
                <div id="assessments_display">
                    <div id="past_assessments"></div>
                    <div id="open_assessments"></div>
                    <div id="future_assessments"></div>
                </div>
                <div id="form_div" hidden>
                    <form id="my_form" action="student_assessment2.php" method="POST">
                        <input id="pkey" name="pkey" type="number" required>
                    </form>
                </div>
            </main>

            <br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="" class="router-link-active"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="student_index.php" class="router-link-active">Home</a></li>
                                <li><a href="" class="">About Us</a></li>
                                <li><a href="" class="">FAQ</a></li>
                                <li><a href="" class="">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a href="student_index.php"> CSU SCALE </a></li>
                                <li><a href="http://fresnostate.edu/" target="_blank"> CSU Fresno Homepage </a></li>
                                <li><a href="http://www.fresnostate.edu/csm/csci/" target="_blank"> Department of Computer Science </a></li>
                                <li><a href="http://www.fresnostate.edu/csm/math/" target="_blank"> Department of Mathematics </a></li>
                            </ul>
                        </div>
                        <div class="contact">
                            <h4>Contact Us</h4>
                            <p> 5241 N. Maple Ave. <br /> Fresno, CA 93740 <br /> Phone: 559-278-4240 <br /></p>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>© 2021-2022 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>
        
        <script type="text/javascript">
            /* GLOBALS */
            const past_assessments = <?= json_encode($past_assessments); ?>;
            const open_assessments = <?= json_encode($open_assessments); ?>;
            const future_assessments = <?= json_encode($future_assessments); ?>;


            // function to display past, open, and future assessments that were created by the student's 
            // instructor for the student's 'course_name', 'course_id', and 'section_id'
            let displayAssessments = () => {
                let str;

                // past assessments
                str = '<h1>Past Assessments</h1>';
                str += '<table class="assessments">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in past_assessments) {
                    str += '<tr><td>' + past_assessments[key][0] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("past_assessments").innerHTML = str;

                // open assessments
                if ('<?= $_SESSION['email']; ?>' === 'test_student@canvas.instructure.com') {
                    str = '<h1>Open Assessments</h1>';
                    str += '<table class="assessments">';
                    str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                    str += '<tbody>';
                    for (const key in open_assessments) {
                        str += '<tr class="tr_ele" onclick="checkIfOpen('+key+')"><td>' + open_assessments[key][0] + '</td></tr>'; 
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("open_assessments").innerHTML = str;
                }
                else {
                    str = '<h1>Open Assessments</h1>';
                    str += '<table class="assessments">';
                    str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                    str += '<tbody>';
                    for (const key in open_assessments) {
                        str += '<tr class="tr_ele"><td>' + open_assessments[key][0] + '</td></tr>';
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("open_assessments").innerHTML = str;
                }

                // future assessments
                str = '<h1>Future Assessments</h1>';
                str += '<table class="assessments">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in future_assessments) {
                    str += '<tr><td>' + future_assessments[key][0] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("future_assessments").innerHTML = str;
            }


            let checkIfOpen = (pkey) => {

                // assessment that is clicked on must be under the 'Open Assessments' list
                if(open_assessments.hasOwnProperty(pkey)) {

                    // create Date Object
                    const DATE = new Date();
                    console.log(`DATE: ${DATE}`);

                    // get current date from Date object in format: YYYY-MM-DD
                    let year = DATE.toLocaleString('en-US', {
                        timeZone: 'America/Los_Angeles',
                        year: "numeric"
                    });
                    let month = DATE.toLocaleString('en-US', {
                        timeZone: 'America/Los_Angeles',
                        month: "2-digit"
                    });
                    let day = DATE.toLocaleString('en-US', {
                        timeZone: 'America/Los_Angeles',
                        day: "2-digit"
                    });
                    let currentDate = `${year}-${month}-${day}`;
                    console.log(`Current Date: ${currentDate}`);
                    console.log(`Open Date: ${open_assessments[pkey][3]}`);
                    console.log(`Close Date: ${open_assessments[pkey][5]}`);

                    // time only matters if we are on the opening or closing date, anything in between does not matter
                    if(currentDate === open_assessments[pkey][3]) {

                        // get the current time
                        let currentTime = DATE.toLocaleTimeString('en-US', { 
                            timeZone: 'America/Los_Angeles',
                            hour12: false
                        });
                        console.log(`Current Time: ${currentTime}`);
                        console.log(`Open Time: ${open_assessments[pkey][4]}`);

                        // student can only start the assessment if the current time is greater than or equal to 
                        // the open time of the assessment
                        if(currentTime >= open_assessments[pkey][4]) {
                            console.log("You are eligible to start the assessment.");
                            goToAssessment(pkey);
                        }
                        else {
                            console.log("You are not eligible to start the assessment due to the time.");
                        }

                    }
                    else if(currentDate === open_assessments[pkey][5]) {

                        // get the current time
                        let currentTime = DATE.toLocaleTimeString('en-US', { 
                            timeZone: 'America/Los_Angeles',
                            hour12: false
                        });
                        console.log(`Current Time: ${currentTime}`);
                        console.log(`Open Time: ${open_assessments[pkey][4]}`);

                        // student can only start the assessment if the current time is less than
                        // the close time of the assessment
                        if(currentTime < open_assessments[pkey][6]) {
                            console.log("You are eligible to start the assessment.");
                            goToAssessment(pkey);
                        }
                        else {
                            console.log("You are not eligible to start the assessment due to the time.");
                        }

                    }
                    else {
                        console.log("You are eligible to start the assessment.");
                        goToAssessment(pkey);
                    }

                }
                else {
                    console.log("Error");
                }

            }


            // function to send the pkey of the clicked on open assessment and submit the form, so
            // student will be redirected to another page where they will take the assessment
            let goToAssessment = (pkey) => {
                document.getElementById("pkey").value = pkey;
                document.getElementById("my_form").submit();
            }


            // controlling the user profile dropdown
            /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
            let showDropdown = () => {
                document.getElementById("myDropdown").classList.toggle("show");
            }
            // Close the dropdown if the user clicks outside of it
            window.onclick = function(event) {
                if (!event.target.matches('.dropbtn')) {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    var i;
                    for (i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        </script>
    </body>
</html>
