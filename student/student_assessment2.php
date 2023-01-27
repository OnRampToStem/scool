<?php
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
$query; $res;
$pkey;
$assessment = []; // will hold the data from the selected assessment
$assessment_json; // will hold the json content data from the selected assessment
$dynamic_ids = []; // list of all dynamic question ids extracted from db

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
        // get rows at random with selected lo
        $query = "SELECT problem_number FROM dynamic_questions WHERE lo_tag = '{$assessment_json[$i]["LearningOutcomeNumber"]}'
                  order by random() limit '{$assessment_json[$i]["NumberQuestions"]}';";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");

        // push data into array
        while ($row = pg_fetch_row($res)) {
            // add 0s to the front of the problem number if the length of the problem number is not 8
            if (strlen($row[0]) !== 8) {
                switch (strlen($row[0])) {
                    case 1:
                        $row[0] = "0000000" . $row[0];
                        break;
                    case 2:
                        $row[0] = "000000" . $row[0];
                        break;
                    case 3:
                        $row[0] = "00000" . $row[0];
                        break;
                    case 4:
                        $row[0] = "0000" . $row[0];
                        break;
                    case 5:
                        $row[0] = "000" . $row[0];
                        break;
                    case 6:
                        $row[0] = "00" . $row[0];
                        break;
                    case 7:
                        $row[0] = "0" . $row[0];
                        break;
                }
            }
            array_push($dynamic_ids, $row[0]);
        }
    }

    // shuffle dynamic_ids once more to mix the los
    shuffle($dynamic_ids);

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
        <!-- for dynamic questions -->
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    </head>
    <body onload="initialize();">
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

                <br>

                <div id="controls">
                    <button id="btn1" onclick="startTest()">Start Assessment</button>
                    <button id="btn2" onclick="next()">Next Question</button>
                    <button id="btn3" onclick="saveResults()">Submit Assessment</button>
                </div>

                <br>

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
            const assessment = <?= json_encode($assessment); ?>; console.log(assessment);
            const sequence_question = <?= json_encode($dynamic_ids); ?>; console.log(sequence_question);
            let src, response;
            let counter = 0;
            let timerID; // holds the ID of the timer, used to stop the timer
            let questionsObjectList = []; // sequence of questions with answers  


            const initialize = () => {
                buildiFrame();
                hideElements();
                initListQuestions();
            }

            const buildiFrame = () => {
                let p = document.createElement('p');
                p.id = "questionCount";
                document.getElementById('contentDiv').appendChild(p);
                let iframe = document.createElement('iframe');
                iframe.id = "frame";
                iframe.title = "LibreTexts";
                iframe.src = "https://imathas.libretexts.org/imathas/embedq2.php?id=000" + sequence_question[counter];
                iframe.width = "100%";
                iframe.height = "900px";
                iframe.scrolling = "yes";
                document.getElementById('contentDiv').appendChild(iframe);
            }

            const hideElements = () => {
                // hide next and submit button
                document.getElementById("btn2").style.display = "none";
                document.getElementById("btn3").style.display = "none";
                // hide i frame
                document.getElementById("contentDiv").style.display = "none";
            }

            const initListQuestions = () => {
                for (let i = 0; i < sequence_question.length; i++) {
                    let questionObject = {
                        id: sequence_question[i], // to be extracted from the assessment
                        lo: "1.1.1", // to be extracted from the assessment
                        time_submit: 0,
                        result: -1,
                        max_score: 1, // to be extracted from the assessment
                        timeStamp: "",
                    };	
                    questionsObjectList.push(questionObject);	
                }
            }

            const startTest = () => {
                // start timer
                timerID = startTimer();	
                // hide start btn
                document.getElementById("btn1").style.display = "none";
                // unhide next and submit btn & iframe
                document.getElementById("btn2").style.display = "";
                document.getElementById("btn3").style.display = "";
                document.getElementById("contentDiv").style.display = "";
                // display question number
                document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
            }   

            const next = () => {
                if (counter + 1 < sequence_question.length) {
                    // update counter
                    counter++;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=000" + sequence_question[counter]);
                    // update question number
                    document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
                }
                else {
                    alert("No more questions.");
                }
            }
       
            const saveResults = () => {
                alert("Submit the results");
                let str_results=JSON.stringify(questionsObjectList);	   
                console.log(str_results);
            }
            

            /////////////////////////////////////////////////////
            /////////////////////////////////////////////////////
            

            function getSrc() {
                axios
                    .get("/imathas-api/imathas")
                    .then((response) => {
                        const data = JSON.stringify(response.data);
                        if (data.type !== "success") {
                            // this.$noty.message(data.message);
                            return false;
                        }
                        src = data.src;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }

            
            /*
            window.onload = (event) => {
                getSrc();
                //start();
            };
            window.addEventListener("message", this.receiveMessage, false);
            
            
            // Callback funtion to receive the value of the score
            function receiveMessage(event) {
                event = JSON.stringify(event.data);
                event = JSON.parse(event);

                if (JSON.parse(event).subject === "lti.ext.imathas.result") 
                {
                    //response = JSON.parse(event);
                    var iMathResult = JSON.parse(parseJwt(JSON.parse(event).jwt));
                    // console.log("iMathResult: " + iMathResult);
                    var score = JSON.parse(iMathResult).score;		
                    // To remove for the final version
                    document.getElementById("response").innerHTML = score;     
                    pushObj(score);
                }
            }

            // Add the information when the student has answered a question
            function pushObj(score) {
                // Object that contains the information about the answer	  
                old_score=questionsObjectList[counter].result;
                if (old_score==-1) // not answered yet
                {	  
                    questionsObjectList[counter].result=score;
                }
                questionsObjectList[counter].time_submit= minute * 60 + second;
                questionsObjectList[counter].timeStamp=Date.now();
                //ResetTime();
            }

            // Parse the JWT
            function parseJwt(token) {
                console.log("Token", token);
                var base64Url = token.split(".")[1];
                var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
                var jsonPayload = decodeURIComponent(
                    window
                    .atob(base64)
                    .split("")
                    .map(function (c) {
                        return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
                    })
                    .join("")
                );
                return JSON.stringify(jsonPayload);
            }
            */
            
            
            /*
            function getId() {
                var i = document.getElementById("qID").value;
                console.log(i);
                var result = "0000000" + i.toString();
                document.getElementById("frame").src =
                    "https://imathas.libretexts.org/imathas/embedq2.php?id=" + result;
            }
            */
           

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
