<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$students = array(); // php assoc arr holding "name" => "email" of students belonging to instr
$students_data = array(); // php assoc arr holding "email" => [los complete, los incomplete] of students belonging to instr

// connect to the PGSQL db
require_once "../register_login/config.php";

if ($_SESSION["type"] === "Instructor") {
    // get all students that belong to the instructor logged in, but are also in the current selected
    // course name and course id
    $query = "SELECT * FROM users WHERE instructor = '{$_SESSION["email"]}' AND course_name = '{$_SESSION["selected_course_name"]}'
              AND course_id = '{$_SESSION["selected_course_id"]}'";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

    while($row = pg_fetch_row($res)){
        $students[$row[1]] = $row[2];
    }
}
elseif ($_SESSION["type"] === "Mentor") {
    // query to get the email of the instructor that corresponds to the student (based on course_name & course_id)
    $query = "SELECT email FROM users WHERE type = 'Instructor' AND course_name LIKE '%{$_SESSION["selected_course_name"]}%'
              AND course_id LIKE '%{$_SESSION["selected_course_id"]}%'";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

    // get the instructor's email
    $instr_email = pg_fetch_result($res, 0, 0);

    // get all students that belong to the instructor logged in, but are also in the current selected
    // course name and course id
    $query = "SELECT * FROM users WHERE instructor = '{$instr_email}' AND course_name = '{$_SESSION["selected_course_name"]}'
              AND course_id = '{$_SESSION["selected_course_id"]}'";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

    while($row = pg_fetch_row($res)){
        $students[$row[1]] = $row[2];
    }
}

// close connection to PGSQL db
pg_close($con);

// loop through students php assoc arr
foreach($students as $key => $value){

    // read student's email static questions json filename and
    // decode the student email JSON file (text => PHP assoc array)
    $json_filename = "../user_data/{$_SESSION['selected_course_name']}-{$_SESSION['selected_course_id']}/questions/" . $value . ".json";
    $json = file_get_contents($json_filename);
    $json_data = json_decode($json, true);

    $data1 = array(); // php assoc arr holding "lo num" => "total number of complete static questions in rel to that lo num"
    $data2 = array(); // php assoc arr holding "lo num" => "total number of static questions in rel to that lo num"
    $maxNumberAssessment = 5; // assuming data from openStax.json file remains the same, each lo should have max of 5
    $complete = 0; // counter for complete static questions
    $incomplete = 0; // counter for incomplete static questions

    // loop through each student's respective static questions
    foreach($json_data as $question){
        /* summing total number of complete static questions per learning outcome */
        // setting key for php assoc arr if not set already
        if(!isset($data1[$question["tags"]])){
            // if question not complete
            if($question["datetime_answered"] === "") {
                $data1[$question["tags"]] = 0;
            } 
            // if question complete
            else {
                $data1[$question["tags"]] = 1;
            }
        } 
        else {
            // if question complete
            if($question["datetime_answered"] !== "") {
                $data1[$question["tags"]]++;
            }
        }

        /* summing total number of static questions per learning outcome */
        // setting key for php assoc arr if not set already and initializing
        if(!isset($data2[$question["tags"]])){
            $data2[$question["tags"]] = 1;
        } 
        // key already set, so just increment value
        else {
            $data2[$question["tags"]]++;
        }
    }

    // loop through data2 array
    foreach($data2 as $k => $v){
        // using total num of static questions ($v), for given lo ($k), to determine whether or not that lo is over or equal
        // to $maxNumberAssessment
        if($v >= $maxNumberAssessment){
            // if the count of complete questions for that lo is greater or equal to $maxNumberAssessment then increment
            // count of $complete
            if($data1[$k] >= $maxNumberAssessment){
                $complete++;
            } 
            // if the count of complete questions for that lo is less than $maxNumberAssessment then increment count of
            // $incomplete
            else {
                $incomplete++;
            }
        } 
        // if the total num of static questions ($v) is less than $maxNumberAssessment
        else {
            // if count of complete questions for that lo is greater than or equal to total num of static questions
            // for that given lo, then increment $complete
            if($data1[$k] >= $v){
                $complete++;
            } 
            // if the count of complete questions for that lo is less than the total num of static questions for that
            // given lo, then increment $incomplete
            else {
                $incomplete++;
            }
        }
    }

    // once loop is done, save the data in $students_data, to be used in JS
    $students_data[$value] = [$complete, $incomplete];

}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Assess Students</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_assess1.css" />
        <link rel="stylesheet" href="../assets/css/global/or2stem.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    </head>
    <body onload="displayStudents();drawAllCharts();">
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
                            <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main id="main"></main>

            <br><br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="" class="router-link-active"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="instr_index1.php" class="router-link-active">Home</a></li>
                                <li><a href="" class="">About Us</a></li>
                                <li><a href="" class="">FAQ</a></li>
                                <li><a href="" class="">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a href="instr_index1.php"> CSU SCALE </a></li>
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
            let rows = 1;
            const students = <?= json_encode($students); ?>; // converting php array to js array
            const students_data = <?= json_encode($students_data); ?>;


            // table creation of all students, displaying learning outcome progress
            let displayStudents = () => {
                let str = '<h1>Students Overview</h1>';
                str += '<table id="ss_tab">';
                str += '<thead><tr>';
                str += '<th class="th1" scope="col">#</th>';
                str += '<th class="th2" scope="col">Email</th>';
                str += '<th class="th3" scope="col">Name</th>';
                str += '<th class="th4" scope="col">Progress</th>';
                str += '<th class="th5" scope="col">Details</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                for (const key in students) {
                    str += '<tr>';
                    str += `<td class="td1">${rows}</td>`;
                    str += `<td class="td2">${students[key]}</td>`;
                    str += `<td class="td3">${key}</td>`;
                    str += `<td class="td4"><div id="myChart${rows}" class="myCharts"></div></td>`;
                    str += '<td class="td5">';
                    str += '<form action="instr_assess2.php" method="POST">';
                    str += '<input class="amt_students" name="amt_students" type="number" style="display:none" required>';
                    str += `<input id="student_email_${rows}" name="student_email_${rows}" type="text" style="display:none" required>`;
                    str += `<input id="student_name_${rows}" name="student_name_${rows}" type="text" style="display:none" required>`;
                    str += `<input id="student_complete_${rows}" name="student_complete_${rows}" type="number" style="display:none" required>`;
                    str += `<input id="student_incomplete_${rows}" name="student_incomplete_${rows}" type="number" style="display:none" required>`;
                    str += '<input class="open_btn_1" type="submit" name="submit" value="Open" onclick="setFormData(this.parentElement.parentElement.parentElement);">';
                    str += '</form>';
                    str += '</td>';
                    str += '</tr>';
                    rows++;
                }
                str += '</tbody></table>';
                document.getElementById("main").innerHTML = str;
            }


            let setFormData = (ele) => {
                // grab list of <td> elements from input <tr> element
                let tdList = ele.children;
                const idx = tdList[0].innerHTML;
                const student_email = tdList[1].innerHTML;
                const student_name = tdList[2].innerHTML;
                // set form data
                for(let i = 0; i < rows - 1; i++){
                    // set all input html elements with a class of 'amt_students' to correct amount of students in the table
                    document.getElementsByClassName("amt_students")[i].value = rows - 1;
                }
                document.getElementById(`student_email_${idx}`).value = student_email;
                document.getElementById(`student_name_${idx}`).value = student_name;
                document.getElementById(`student_complete_${idx}`).value = students_data[student_email][0];
                document.getElementById(`student_incomplete_${idx}`).value = students_data[student_email][1];
            }


            // using Google Pie Charts to display each student's learning outcome progress
            let drawAllCharts = () => {
                let student_emails = [];
                for (const key in students) {
                    student_emails.push(students[key]);
                }
                for(let i = 1; i < rows; i++) {
                    drawChart(i, student_emails);
                }
            }
            let drawChart = (num, student_emails) => {
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['Status', 'Learning Outcomes'],
                        ['Complete', students_data[student_emails[num - 1]][0]],
                        ['Incomplete', students_data[student_emails[num - 1]][1]],
                    ]);

                    var options = {
                        colors: ['green', 'red'],
                        legend: 'none'
                    };

                    var chart = new google.visualization.PieChart(document.getElementById(`myChart${num}`));

                    chart.draw(data, options);
                }
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