<?php
// for display purposes
//header('Content-type: text/plain');

// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if ($_SESSION["type"] !== "Learner") {
    header("location: ../register_login/logout.php");
    exit;
}

/* GLOBALS */
$query;
$res;
$pkey;
$assessment = [];
$assessment_json;
$dynamic_ids = [];

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // accept $_POST input
    $pkey = $_POST['pkey'];

    // connect to the db
    require_once "../register_login/config.php";

    // grab the assessment from 'assessments' table
    $query = "SELECT * FROM assessments WHERE pkey = {$pkey}";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");
    $row = pg_fetch_row($res);
    array_push($assessment, $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12]);

    // get assessment json content
    $assessment_json = json_decode($row[9], TRUE); //print_r($assessment_json);

    // create list of randomly chosen dynamic questions
    for ($i = 0; $i < count($assessment_json); $i++) {
        // set key in array
        //$dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]] = [];

        // get rows at random with selected lo
        $query = "SELECT id FROM dynamic_questions WHERE lo_tag = '{$assessment_json[$i]["LearningOutcomeNumber"]}'
                  order by random() limit '{$assessment_json[$i]["NumberQuestions"]}';";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");

        // push data into array
        while ($row = pg_fetch_row($res)) {
            array_push($dynamic_ids, $row[0]);
            //$dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]]
        }
    }
    //print_r($dynamic_ids);
    
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?= $assessment[2]; ?></title>
        <link rel="stylesheet" href="../assets/css/student/student_assessment2.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body onload="buildiFrame();">
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
                <div id="assessment-info">
                    <div class="assessment-info-row">
                        <h2><?= $assessment[2]; ?></h2>
                    </div>
                    <div class="assessment-info-row">
                        <h4>Minutes allowed: <?= $assessment[4]; ?></h4>
                    </div>
                    <div class="assessment-info-row">
                        <h4>Closes (auto submit) on: <?= $assessment[8]; ?> <?= $assessment[7]; ?> </h4>
                    </div>
                    <div class="assessment-info-row">
                        <h4 class="timer">Timer:</h4>
                        <h4 class="timer" id="minutes">00</h4> : <h4 class="timer" id="seconds">00</h4>
                    </div>
                </div>

                <div id="controls">
                    <button onclick="next()">Next Question</button>
                </div>

                <div id="contentDiv"></div>
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
            let idx = 0;
            const assessment = <?= json_encode($assessment); ?>;
            console.log(assessment);
            const dynamic_ids = <?= json_encode($dynamic_ids); ?>;
            console.log(dynamic_ids);
            let timerID; // holds the ID of the timer, used to stop the timer


            const buildiFrame = () => {
                /*
                <iframe
                            id = "frame"
                            src = "https://imathas.libretexts.org/imathas/embedq2.php?id=00000001"
                            title = "LibreTexts"
                            scrolling = "yes"
                            style = "overflow: visible;
                                     width: 100%;
                                     height: 900px;
                                     border: 1px solid black;"
                                     
                />
                */
                let iframe = document.createElement('iframe');
                iframe.id = "frame";
                iframe.title = "LibreTexts";
                iframe.src = "https://imathas.libretexts.org/imathas/embedq2.php?id=000" + dynamic_ids[idx];
                iframe.width = "100%";
                iframe.height = "900px";
                iframe.scrolling = "yes";
                document.getElementById('contentDiv').appendChild(iframe);

                // start timer
                timerID = startTimer();
            }


            let next = () => {
                idx++;
                document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + dynamic_ids[idx]);
            }




            /* TIMER PORTION */
            let startTimer = () => {
                var sec = 0;
                let pad = (val) => {
                    return val > 9 ? val : "0" + val;
                }
                var timer = setInterval( function() {
                    document.getElementById("seconds").innerHTML=pad(++sec%60);
                    document.getElementById("minutes").innerHTML=pad(parseInt(sec/60,10));
                }, 1000);
                return timer;
            }
            // clearTimer stops the timer and resets the clock back to 0
            let clearTimer = (timerID) => {
                document.getElementById("seconds").innerHTML= "00";
                document.getElementById("minutes").innerHTML= "00";
                clearInterval(timerID);
            } 
            // stopTimer just stops the timer
            let stopTimer = (timerID) => {
                clearInterval(timerID);
            }

            // initialize assessment
            //document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + dynamic_ids[idx]);

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