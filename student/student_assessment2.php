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
    $json_data = json_decode($_POST['json_data']); //print_r($json_data);

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

            if (!isset($dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]])) {
                $dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]] = [$assessment_json[$i]["NumberPoints"], $row[0]];
            }
            else {
                array_push($dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]], $row[0]);
            }
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
        <!-- for dynamic questions -->
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    </head>
    <body>
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
                    <h1><u><?= $assessment[2]; ?></u></h1>
                    <div id="ul-list-div">
                        <ul>
                            <li><h3>You have a total duration of <?= $assessment[4]; ?> minutes to complete this assessment.</h3></li>
                            <li><h3>This assessment closes on: <?= $assessment[7]; ?> <?= $assessment[8]; ?></h3></li>
                            <li><h3 id="q-info-count"></h3></li>
                            <li><h3>The table below contains the learning outcomes that you will be assessed on. As well as the break-down on number of questions and points per question for each learning outcome.</h3></li>
                        </ul>
                    </div>
                    <div id="assessment-info-lo"></div>
                    <h3>To start the test, click on the 'Start' button.</h3>
                    <button id="btn1" onclick="startTest()">Start</button>
                </div>

                <div id="content-div">
                    <div id="controls-div">
                        <h2 id="questionCount"></h2>
                        <div id="ul-div">
                            <ul>
                                <li><b>Please make sure to 'Submit' your answer before clicking on the 'Next Question' button.</b></li>
                                <li><b>Do not click on the 'Try a similar question' button.</b></li>
                                <li><b>After submitting your last question, you must click on the 'Submit Assessment' button.</b></li>
                            </ul>
                        </div>
                        <button id="btn2" onclick="next()">Next Question</button>
                        <button id="btn3" onclick="saveResults()">Submit Assessment</button>
                        <div class="timer-div">
                            <h4 class="timer">Timer:</h4>
                            <h4 class="timer" id="minutes">00</h4> : <h4 class="timer" id="seconds">00</h4>
                        </div>
                    </div>
                </div>

                <div id="results-div"></div>
            </main>

            <br>

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
            let src, response;
            let counter = 0; // used as index for each question
            let timerID; // holds the ID of the timer, used to stop the timer
            const assessment = <?= json_encode($assessment); ?>; // contains main assessment data
            const dynamic_ids = <?= json_encode($dynamic_ids); ?>; // contains los with data for each lo, produced from assessment
            const sequence_question = []; // list of problem numbers fed to the imathas
            let questionsObjectList = []; // sequence of questions with answers  
            const json_data = <?= json_encode($json_data); ?>


            //////////////////////////////////
            // initialization functionalities
            //////////////////////////////////

            const initialize = () => {
                hideElements();
                initListQuestions();
                buildiFrame();
                displayLoInfo();
            }

            const hideElements = () => {
                // hide content-div & results-div
                document.getElementById("content-div").style.display = "none";
                document.getElementById("results-div").style.display = "none";
                // hide btn3
                document.getElementById("btn3").style.display = "none";
            }

            const initListQuestions = () => {
                // loop through each key value pair in dynamic_ids
                // each key represents a learning outcome
                // each value contains an array containing the numPoints value, and ids of each question for the lo
                for (const [key, value] of Object.entries(dynamic_ids)) {
                    // get the numPoints
                    const numPoints = value[0];

                    // loop through each id in value arr
                    for (let i = 1; i < value.length; i++) {
                        // create the obj
                        let questionObject = {
                            id: value[i], // to be extracted from the assessment
                            lo: key, // to be extracted from the assessment
                            result: -1,
                            max_score: numPoints // to be extracted from the assessment
                        };	
                        // push obj into main arr
                        questionsObjectList.push(questionObject);

                        // push just the id into sequence_question (arr of only the ids)
                        sequence_question.push(value[i]);
                    }
                }
            }

            const buildiFrame = () => {
                let iframe = document.createElement('iframe');
                iframe.id = "frame";
                iframe.title = "LibreTexts";
                iframe.src = "https://imathas.libretexts.org/imathas/embedq2.php?id=" + sequence_question[counter];
                iframe.width = "100%";
                iframe.height = "1600px"; // was 900pc
                document.getElementById('content-div').appendChild(iframe);
            }

            const displayLoInfo = () => {
                let str = '<table><thead><tr>';
                str += '<th scope="col">Chapter<br>Section<br>Learning Outcome</th>';
                str += '<th scope="col">Number of Questions</th>';
                str += '<th scope="col">Points per Question</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                let lo_info = JSON.parse(assessment[9]);
                let sums = [0, 0];
                lo_info.forEach(function(arrItem) {
                    // sums
                    sums[0] += arrItem.NumberQuestions;
                    sums[1] += arrItem.NumberQuestions * arrItem.NumberPoints;

                    str += '<tr>';
                    // chapter mod
                    let idx = arrItem.LearningOutcomeNumber.indexOf(".");
                    let ch = parseInt(arrItem.LearningOutcomeNumber.slice(0, idx));
                    // section mod
                    let idx2 = arrItem.LearningOutcomeNumber.indexOf(".", idx + 1);
                    let sec = parseInt(arrItem.LearningOutcomeNumber.slice(idx + 1, idx2));
                    str += '<td>';
                    str += `${ch}. ` + json_data[ch] + '<br>';
                    str += `${ch}.${sec}. ` + json_data[ch + "." + sec] + '<br>';
                    str += `${arrItem.LearningOutcomeNumber}. ${json_data[arrItem.LearningOutcomeNumber]}`;
                    str += '</td>';
                    str += `<td>${arrItem.NumberQuestions}</td>`;
                    str += `<td>${arrItem.NumberPoints}</td>`;
                    str += '<tr>';
                });
                document.getElementById("q-info-count").innerHTML = `This assessment contains a total of ${sums[0]} questions and ${sums[1]} points possible.`;
                str += '</tbody>';
                str += '<tfoot><tr>';
                str += '<td><b>Totals</b></td>';
                str += `<td>${sums[0]}</td>`;
                str += `<td>${sums[1]}</td>`;
                str += '</tr></tfoot>';
                str += '</table>';
                document.getElementById("assessment-info-lo").innerHTML = str;
            }



            //////////////////////////
            // button functionalities
            //////////////////////////

            const startTest = () => {
                // start timer
                timerID = startTimer();	
                // hide assessment-info display
                document.getElementById("assessment-info").style.display = "none";
                // unhide next btn & iframe
                document.getElementById("content-div").style.display = "";
                // display question number
                document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
            }   

            const next = () => {
                // making sure we are in the valid range and that the question has been answered before moving on
                if (counter + 1 < sequence_question.length && questionsObjectList[counter].result !== -1) {
                    // hide next button and unhide submit button if user is on last question
                    if (counter + 1 === sequence_question.length - 1) {
                        document.getElementById("btn2").style.display = "none";
                        document.getElementById("btn3").style.display = "";
                    }
                    // update counter
                    counter++;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + sequence_question[counter]);
                    // update question number
                    document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
                }
                else {
                    alert("You must submit the current question, before moving on.");
                }
            }
       
            const saveResults = () => {
                if (questionsObjectList[counter].result !== -1) {
                    // stop the timer
                    stopTimer();

                    // get the sum of all individual scores
                    let score_sum = 0;
                    for (let i = 0; i < questionsObjectList.length; i++) {
                        score_sum += questionsObjectList[i].result;
                    }

                    // get the max score possible
                    let max_sum = 0;
                    for (let i = 0; i < questionsObjectList.length; i++) {
                        max_sum += questionsObjectList[i].max_score;
                    }

                    // append data into the results div
                    let str = '<div><h1>You have completed <?= $assessment[2]; ?>!</h1>';
                    str += `<h3>You scored: ${score_sum} / ${max_sum}</h3>`;
                    str += '<a href="student_index.php">Click here to go Home</a></div>';
                    document.getElementById("results-div").innerHTML = str;

                    // stringify the array of objects
                    let str_results = JSON.stringify(questionsObjectList); //console.log(str_results);

                    // create new date
                    let date = new Date();
                    // submit date will be in format (yyyy-mm-dd hh:mm:ss)
                    let submit_date_time = date.getFullYear() + "-" +  ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2) + " " + ("0" + date.getHours() ).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2) + ":" + ("0" + date.getSeconds()).slice(-2);
                    //console.log(submit_date_time);

                    // start XMLHttpRequest
                    let req = new XMLHttpRequest();
                    req.onreadystatechange = function() {
                        if(req.readyState == 4 && req.status == 200){
                            // log the response
                            console.log(req.responseText);
                            // hide content div
                            document.getElementById("content-div").style.display = "none";
                            // unhide results div
                            document.getElementById("results-div").style.display = "";
                        }
                    }
                    req.open('POST', 'js-php/submit_assessment_results.php', true);
                    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    req.send(`assessment_name=${assessment[2]}&instructor_email=${assessment[1]}&date_time_submitted=${submit_date_time}&score=${score_sum}&max_score=${max_sum}&content=${str_results}`);
                }
                else {
                    alert("You must submit the current question, before submitting the assessment.");
                }
            }
            

            /////////////////////////////////////////////////////
            // axios functionality - to get api response
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

            window.onload = (event) => {
                getSrc();
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
                    //document.getElementById("response").innerHTML = score;     
                    pushObj(score);
                }
            }

            // Add the information when the student has answered a question
            function pushObj(score) {
                // Object that contains the information about the answer	  
                let old_score = questionsObjectList[counter].result;
                // not answered yet
                if (old_score == -1) {	  
                    // score is mult by the stored max score to get the accurate score
                    questionsObjectList[counter].result = score * questionsObjectList[counter].max_score;
                }

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
            
        
            ///////////////////////////////
            // background functionalities 
            ///////////////////////////////

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
            let clearTimer = () => {
                document.getElementById("seconds").innerHTML= "00";
                document.getElementById("minutes").innerHTML= "00";
                clearInterval(timerID);
            } 
            // stopTimer just stops the timer
            let stopTimer = () => {
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

            ///////////
            // DRIVER
            initialize();
            ///////////
        </script>
    </body>
</html>