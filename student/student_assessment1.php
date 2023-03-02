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
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
$instr_email = pg_fetch_result($res, 0);


// 2
// grab all past & open assessments that belong to the Learner (instructor, course_name, and course_id)
$past_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND close_date <= '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
if (pg_num_rows($res) !== 0) {
    // loop through possible rows
    while($row = pg_fetch_row($res)){
        array_push($past_assessments, $row[2]);
    }
}
//print_r($past_assessments);


// 3
// before getting and displaying all current assessments, get the list of all assessments completed by the student
$complete_assessments_arr = array();
$complete_assessments_data = array();
$query = "SELECT pkey, assessment_name, score, max_score, date_time_submitted FROM assessments_results WHERE instructor_email = '{$instr_email}' AND student_email = '{$_SESSION['email']}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
if (pg_num_rows($res) !== 0) {
    // loop through possible rows
    while ($row = pg_fetch_row($res)) {
        array_push($complete_assessments_arr, $row[1]);
        if (!isset($complete_assessments_data[$row[0]])) {
            $complete_assessments_data[$row[0]] = [];
            array_push($complete_assessments_data[$row[0]], $row[1], $row[2], $row[3], $row[4]);
        }
    }
}
//print_r($complete_assessments_arr);


// 4
// use complete assessments_arr to filter out the assessments that have been completed from the past assessments
// PHP function to check for even elements in an array
function check($value){
    global $complete_assessments_arr;
    if (!in_array($value, $complete_assessments_arr)) return true;
    else return false;
}
$past_assessments = array_filter($past_assessments, 'check');
//print_r($past_assessments);


/*
// 5
// run a query for each past assessment
$past_assessments_data = array();
for ($i = 0; $i < count($past_assessments); $i++) {
    $query = "SELECT pkey, close_date, close_time FROM assessments WHERE name = '{$past_assessments[$i]}' AND instructor = '{$instr_email}' AND course_name = '{$_SESSION['course_name']}'
              AND course_id = '{$_SESSION['course_id']}'";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
    if (pg_num_rows($res) !== 0) {
        while ($row = pg_fetch_row($res)) {
            if (!isset($past_assessments_data[$row[0]])) {
                $pst_assessments_data[$row[0]] = [];
                array_push($past_assessments_data[$row[0]], $past_assessments[$i], $row[1], $row[2]);
            }
        }
    }
}
print_r($past_assessments_data);
*/


// 6
// grab all current assessments that belong to the Learner's instructor, course_name, and course_id
$open_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date <= '{$curr_date}' AND close_date >= '{$curr_date}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
// filter out the complete current assessments
while($row = pg_fetch_row($res)){
    // filter by assessment_name
    if (!in_array($row[2], $complete_assessments_arr)) {
        if(!isset($open_assessments[$row[0]])) {
            $open_assessments[$row[0]] = [];
            array_push($open_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// 7
// grab all future assessments that belong to the Learner's instructor, course_name, and course_id
$future_assessments = array();
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date > '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
while($row = pg_fetch_row($res)){
    if(!isset($future_assessments[$row[0]])) {
        $future_assessments[$row[0]] = [];
        array_push($future_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Assessments</title>
        <link rel="stylesheet" href="../assets/css/student/student_assessment1.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body onload="loadJSON();displayAssessments();">
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

            <main>
                <div id="header-div">
                    <h1>Assessments</h1>
                    <hr style="border: 1px solid black;">
                </div>

                <div class="container-div">
                    <div class="blue_long_btn" onclick="toggleOpenAssessments()">
                        <p class="blue_long_content">Open Assessments</p>
                        <p id="openHeaderArrow" class="blue_long_arrow">&#708;</p>
                    </div>
                    <div id="open_assessments"></div>
                </div>

                <div class="container-div">
                    <div class="blue_long_btn" onclick="toggleFutureAssessments()">
                        <p class="blue_long_content">Future Assessments</p>
                        <p id="futureHeaderArrow" class="blue_long_arrow">&#709;</p>
                    </div>
                    <div id="future_assessments" style="display:none"></div>
                </div>

                <div class="container-div">
                    <div class="blue_long_btn" onclick="toggleCompleteAssessments()">
                        <p class="blue_long_content">Complete Assessments</p>
                        <p id="completeHeaderArrow" class="blue_long_arrow">&#709;</p>
                    </div>
                    <div id="complete_assessments" style="display:none"></div>
                </div>

                <div class="container-div">
                    <div class="blue_long_btn" onclick="toggleIncompleteAssessments()">
                        <p class="blue_long_content">Incomplete Assessments</p>
                        <p id="incompleteHeaderArrow" class="blue_long_arrow">&#709;</p>
                    </div>
                    <div id="incomplete_assessments" style="display:none"></div>
                </div>

                <div id="form_div" hidden>
                    <form id="my_form" action="student_assessment2.php" method="POST">
                        <input id="pkey" name="pkey" type="number" required>
                        <input id="json_data" name="json_data" type="text" required>
                    </form>
                </div>
            </main>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="student_index.php"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="student_index.php">Home</a></li>
                                <li><a href="../navigation/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us.php">Contact Us</a></li>
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
                        <p>© 2021-2023 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>
        
        <script type="text/javascript">
            /* GLOBALS */
            const open_assessments = <?= json_encode($open_assessments); ?>;
            const incomplete_assessments = <?= json_encode($past_assessments); ?>;
            const complete_assessments = <?= json_encode($complete_assessments_data); ?>;
            const future_assessments = <?= json_encode($future_assessments); ?>;
            let open_clicked = false;
            let future_clicked = false;
            let complete_clicked = false;
            let incomplete_clicked = false;


            let toggleOpenAssessments = () => {
                if (open_clicked) {
                    document.getElementById("openHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("open_assessments").style.display = "";
                    open_clicked = false;
                }
                else {
                    document.getElementById("openHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("open_assessments").style.display = "none";
                    open_clicked = true;
                }
            }

            let toggleFutureAssessments = () => {
                if (future_clicked) {
                    document.getElementById("futureHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("future_assessments").style.display = "none";
                    future_clicked = false;
                }
                else {
                    document.getElementById("futureHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("future_assessments").style.display = "";
                    future_clicked = true;
                }
            }

            let toggleCompleteAssessments = () => {
                if (complete_clicked) {
                    document.getElementById("completeHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("complete_assessments").style.display = "none";
                    complete_clicked = false;
                }
                else {
                    document.getElementById("completeHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("complete_assessments").style.display = "";
                    complete_clicked = true;
                }
            }

            let toggleIncompleteAssessments = () => {
                if (incomplete_clicked) {
                    document.getElementById("incompleteHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("incomplete_assessments").style.display = "none";
                    incomplete_clicked = false;
                }
                else {
                    document.getElementById("incompleteHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("incomplete_assessments").style.display = "";
                    incomplete_clicked = true;
                }
            }


            // function to display past, open, and future assessments that were created by the student's 
            // instructor for the student's 'course_name', 'course_id', and 'section_id'
            let displayAssessments = () => {
                // open assessments
                if (Object.keys(open_assessments).length > 0) {
                    let str = '<table class="open-future-assessments-table">';
                    str += '<thead><tr>';
                    str += '<th class="open-future-assessments-table-col-1" scope="col">Name</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Start Date</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Start Time</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Close Date</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Close Time</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Mins Allowed</th>';
                    str += '</tr></thead>';
                    str += '<tbody>';
                    for (const key in open_assessments) {
                        str += `<tr class="tr_ele" onclick="checkIfOpen('${key}')">`;
                        str += `<td class="open-future-assessments-table-col-1">${open_assessments[key][0]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${open_assessments[key][3]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${open_assessments[key][4]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${open_assessments[key][5]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${open_assessments[key][6]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${open_assessments[key][2]}</td>`;
                        str += '</tr>'; 
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("open_assessments").innerHTML = str;
                }
                else {
                    let str = '<h3>No Open Assessments Yet!</h3>';
                    document.getElementById("open_assessments").innerHTML = str;
                }

                // future assessments
                if (Object.keys(future_assessments).length > 0) {
                    str = '<table class="open-future-assessments-table">';
                    str += '<thead><tr>';
                    str += '<th class="open-future-assessments-table-col-1" scope="col">Name</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Start Date</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Start Time</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Close Date</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Close Time</th>';
                    str += '<th class="open-future-assessments-table-col-rest" scope="col">Mins Allowed</th>';
                    str += '</tr></thead>';
                    str += '<tbody>';
                    for (const key in future_assessments) {
                        str += `<tr>`;
                        str += `<td class="open-future-assessments-table-col-1">${future_assessments[key][0]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${future_assessments[key][3]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${future_assessments[key][4]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${future_assessments[key][5]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${future_assessments[key][6]}</td>`;
                        str += `<td class="open-future-assessments-table-col-rest">${future_assessments[key][2]}</td>`;
                        str += '</tr>'; 
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("future_assessments").innerHTML = str;
                }
                else {
                    let str = '<h3>No Future Assessments Yet!</h3>';
                    document.getElementById("future_assessments").innerHTML = str;
                }

                // complete assessments
                if (Object.keys(complete_assessments).length > 0) {
                    str = '<table id="complete-assessments-table">';
                    str += '<thead><tr>';
                    str += '<th class="complete-assessments-table-col-1" scope="col">Name</th>';
                    str += '<th class="complete-assessments-table-col-2" scope="col">Score</th>';
                    str += '<th class="complete-assessments-table-col-3" scope="col">Date Time Submitted</th>';
                    str += '</tr></thead>';
                    str += '<tbody>';
                    for (const key in complete_assessments) {
                        str += '<tr>';
                        str += `<td class="complete-assessments-table-col-1">${complete_assessments[key][0]}</td>`;
                        str += `<td class="complete-assessments-table-col-2">${complete_assessments[key][1]} / ${complete_assessments[key][2]}</td>`;
                        str += `<td class="complete-assessments-table-col-3">${complete_assessments[key][3]}</td>`;
                        str += '</tr>';
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("complete_assessments").innerHTML = str;
                }
                else {
                    let str = '<h3>No Complete Assessments Yet!</h3>';
                    document.getElementById("complete_assessments").innerHTML = str;
                }

                // incomplete assessments
                if (incomplete_assessments.length > 0) {
                    str = '<table id="incomplete-assessments-table">';
                    str += '<thead><tr>';
                    str += '<th class="incomplete-assessments-table-col-1" scope="col">Name</th>';
                    str += '</tr></thead>';
                    str += '<tbody>';
                    for (let i = 0; i < incomplete_assessments.length; i++) {
                        str += '<tr>';
                        str += `<td class="incomplete-assessments-table-col-1">${incomplete_assessments[i]}</td>`;
                        str += '</tr>';
                    }
                    str += '</tbody>';
                    str += '</table>';
                    document.getElementById("incomplete_assessments").innerHTML = str;
                }
                else {
                    let str = '<h3>No Incomplete Assessments Yet!</h3>';
                    document.getElementById("incomplete_assessments").innerHTML = str;
                }
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
                document.getElementById("json_data").value = JSON.stringify(json_data);
                document.getElementById("my_form").submit();
            }


            let json_data;
            const loadJSON = () => {
                // load json data
                let req = new XMLHttpRequest();
                req.onreadystatechange = function() {
                    if(req.readyState == 4 && req.status == 200){
                        json_data = JSON.parse(req.responseText);
                    }
                }
                req.open("GET", "../instructor/get/data.json", true);
                req.send(); 
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