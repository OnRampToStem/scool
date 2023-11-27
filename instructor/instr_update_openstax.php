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
    header("location: ../register_login/logout.php");
    exit;
}

// globals //
$question = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // connect to the db //
    require_once "../register_login/config.php";

    // get question by id //
    if (isset($_POST['questionId'])) {
        // input data //
        $question_id = $_POST['questionId'];

        // get question //
        $query = "SELECT pkey, title, text, pic, numtries, options, rightanswer, isimage, tags, difficulty
                  FROM questions
                  WHERE pkey = $1;";
        $result = pg_query_params($con, $query, [$question_id]);
        if ($result) {
            $question = pg_fetch_assoc($result);
        } else {
            die(pg_last_error($con));
        }
    }
    // update question by id //
    else if (isset($_POST['qId'])) {
        // input data //
        $q_id           = $_POST['qId'];
        $q_tags         = trim($_POST['qTags']);
        $q_title        = trim($_POST['qTitle']);
        $q_text         = trim($_POST['qText']);
        $q_pic          = (strlen(trim($_POST['qPic'])) === 0 ? '' : trim($_POST['qPic']));
        $q_num_tries    = trim($_POST['qNumTries']);
        $q_options      = trim($_POST['qOptions']);
        $q_right_answer = trim($_POST['qRightAnswer']);
        $q_is_image     = trim($_POST['qIsImage']);
        $q_difficulty   = (strlen(trim($_POST['qDifficulty'])) === 0 ? '' : trim($_POST['$qDifficulty']));

        // update question //
        $query = "UPDATE questions
                  SET title = $1, text = $2, pic = $3, numtries = $4, options = $5, rightanswer = $6, isimage = $7, tags = $8, difficulty = $9
                  WHERE pkey = $10;";
        $result = pg_query_params($con, $query, [$q_title, $q_text, $q_pic, $q_num_tries, $q_options, $q_right_answer, $q_is_image, $q_tags, $q_difficulty, $q_id]);
        if ($result) {

        } else {
            die(pg_last_error($con));
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update OpenStax Question</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/global/global.css" />
    <link id="css-header" rel="stylesheet" type="text/css" href="" />
    <link id="css-mode" rel="stylesheet" type="text/css" href="" />
    <script type="text/javascript">
        const toggleBanner = () => {
            const cssHeader = document.getElementById("css-header");
            cssHeader.setAttribute("href", `../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
        }

        const toggleCSS = () => {
            const cssLink = document.getElementById("css-mode");
            cssLink.setAttribute("href", `../assets/css/instructor/instr_update_openstax-${window.localStorage.getItem("mode")}-mode.css`);
        }

        // mode
        let item = localStorage.getItem("mode");
        const cssLink = document.getElementById("css-mode");
        if (item === null) {
            window.localStorage.setItem('mode', 'OR2STEM');
            toggleCSS();
        } else {
            toggleCSS();
        }

        // banner
        item = localStorage.getItem("banner");
        const cssHeader = document.getElementById("css-header");
        if (item === null) {
            window.localStorage.setItem('banner', 'OR2STEM');
            toggleBanner();
        } else {
            toggleBanner();
        }
    </script>
</head>

<body onload="displayUpdateForm();">
    <div id="app">
        <header>
            <nav class="container">
                <div id="userProfile" class="dropdown">
                    <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                    <div id="myDropdown" class="dropdown-content">
                        <a href="../navigation/settings/settings.php">Settings</a>
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

        <div>
            <form id='getQForm' method='POST'>
                <div>
                    <label for='questionId'>Question ID:</label>
                    <input id='questionId' name='questionId' type='number' required />
                </div>
                <input type='submit' value='Enter' />
            </form>

            <form id='updateQForm' method='POST'>
                <h1>Open Stax Question</h1>
                <div>
                    <label for='qId'>ID:</label>
                    <input id='qId' name='qId' type='number' readonly />
                </div>
                <div>
                    <label for='qTags'>Tags:</label>
                    <input id='qTags' name='qTags' type='text' required />
                </div>
                <div>
                    <label for='qTitle'>Title:</label>
                    <input id='qTitle' name='qTitle' type='text' required />
                </div>
                <div>
                    <label for='qText'>Text:</label>
                    <input id='qText' name='qText' type='text' required />
                </div>
                <div>
                    <label for='qPic'>Picture:</label>
                    <input id='qPic' name='qPic' type='text' />
                </div>
                <div>
                    <label for='qNumTries'>Number of Tries:</label>
                    <input id='qNumTries' name='qNumTries' type='text' required />
                </div>
                <div>
                    <label for='qOptions'>Options:</label>
                    <input id='qOptions' name='qOptions' type='text' required />
                </div>
                <div>
                    <label for='qRightAnswer'>Right Answer:</label>
                    <input id='qRightAnswer' name='qRightAnswer' type='text' required />
                </div>
                <div>
                    <label for='qIsImage'>Is Image:</label>
                    <input id='qIsImage' name='qIsImage' type='text' required />
                </div>
                <div>
                    <label for='qDifficulty'>Difficulty:</label>
                    <input id='qDifficulty' name='qDifficulty' type='text' />
                </div>
                <input type='submit' value='Update' />
            </form>
        </div>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="instr_index1.php" class="router-link-active">
                            <p>On-Ramp to STEM</p>
                        </a>
                    </div>
                    <div class="navigation">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="instr_index1.php">Home</a></li>
                            <li><a href="../navigation/about-us/about-us.php">About Us</a></li>
                            <li><a href="../navigation/faq/faq.php">FAQ</a></li>
                            <li><a href="../navigation/contact-us/contact-us.php">Contact Us</a></li>
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
                    <p>© 2021-2023 OR2STEM Team</p>
                </div>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        const displayUpdateForm = () => {
            let question = <? json_encode($question); ?>;
            if (question !== null) {
                document.getElementById('qId').value = question.pkey;
                document.getElementById('qTags').value = question.tags;
                document.getElementById('qTitle').value = question.title;
                document.getElementById('qText').value = question.text;
                document.getElementById('qPic').value = question.pic;
                document.getElementById('qNumTries').value = question.numtries;
                document.getElementById('qOptions').value = question.options;
                document.getElementById('qRightAnswer').value = question.rightanswer;
                document.getElementById('qIsImage').value = question.isimage;
                document.getElementById('qDifficulty').value = question.difficulty;
                
                document.getElementById('updateForm').style.display = 'block';
            }
            else {
                alert('Error');
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