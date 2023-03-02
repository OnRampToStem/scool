<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$course_names = json_decode($_SESSION['course_name']);
$course_ids = json_decode($_SESSION['course_id']);

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") { 
    // extract POST data
    $idx = $_POST['number'];
    
    // set new session variables for instructor
    $_SESSION['selected_course_name'] = $course_names[$idx];
    $_SESSION['selected_course_id'] = $course_ids[$idx];

    // redirect to instr_index2.php
    header("Location: instr_index2.php");
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Instructor Home Page</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_index1.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
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
                        <h1 id="OR2STEM-HEADER">On-Ramp to STEM</h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <main>
                <div id="header-div">
                    <h1>Instructor Home Page</h1>
                    <hr style="border: 1px solid black;">
                </div>

                <div id="loading-div">
                    LOADING...
                </div>

                <div id="class-list-div" style="display:none;">
                    <h2>Inspect one of your courses.</h2>
                </div>

                <div id="static-dynamic-div" style="display:none;">
                    <h2>Browse through Static or Dynamic questions.</h2>
                    <button class="q-btn" onclick="redirect(0)">Static Questions</button>
                    <button class="q-btn" onclick="redirect(1)">Dynamic Questions</button>
                </div>
            </main>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href=""><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="">Home</a></li>
                                <li><a href="../navigation/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us.php">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a href=""> CSU SCALE </a></li>
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
            /* JS GLOBALS */
            // converting php array to js array
            const course_names = <?= json_encode($course_names); ?>;
            const course_ids = <?= json_encode($course_ids); ?>;

            let initialize = () => {
                displayClasses();
                document.getElementById("class-list-div").style.display = "";
                document.getElementById("static-dynamic-div").style.display = "";
                document.getElementById("loading-div").style.display = "none";
            }

            let displayClasses = () => {
                let str = '<form id="myForm" action="" method="POST">';
                str += '<input type="number" id="number" name="number" style="display: none;" />';
                for (let i = 0; i < course_names.length; i++) {
                    str += `<button type="button" class="q-btn" onclick="submitForm(${i})">${course_names[i]}</button>`;
                }
                str += '</form>';
                document.getElementById("class-list-div").insertAdjacentHTML('beforeend', str);
            }

            let submitForm = (int) => {
                // set chosen index value
                document.getElementById("number").value = int;
                // submit form
                document.getElementById("myForm").submit();
            }

            let redirect = (idx) => {
                if (idx) window.location.href = "./dynamic.php";
                else window.location.href = "./static.php";
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