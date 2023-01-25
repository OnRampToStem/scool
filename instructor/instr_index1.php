<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor"){
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
    <body onload="displayClasses()">
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

            <br>

            <main>
                <p><strong>Welcome to the On-Ramp to STEM Instructor Home Page!</strong></p>
                <p><strong>Please select one of your courses below to continue.</strong></p>

                <div id="classList"></div>
            </main>

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
                                <li><a href="" class="router-link-active">Home</a></li>
                                <li><a href="" class="">About Us</a></li>
                                <li><a href="" class="">FAQ</a></li>
                                <li><a href="" class="">Contact Us</a></li>
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
                        <p>© 2021-2022 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>
        
        <script type="text/javascript">
            /* JS GLOBALS */
            // converting php array to js array
            const course_names = <?= json_encode($course_names); ?>;
            const course_ids = <?= json_encode($course_ids); ?>;

            let displayClasses = () => {
                let str = '<form id="myForm" action="" method="POST">';
                str += '<input id="number" name="number" type="number" value="" hidden required>';
                str += '<table id="classListTable"><thead><tr>';
                str += '<th scope="col">Courses</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                for (let i = 0; i < course_names.length; i++) {
                    str += '<tr>'
                    str += `<td onclick="submitForm(${i})">${course_names[i]} - ${course_ids[i]}</td>`;
                    str += '</tr>';
                }
                str += '</tbody></table>';
                str += '</form>';
                document.getElementById("classList").innerHTML = str;
            }

            let submitForm = (int) => {
                // set chosen index value
                document.getElementById("number").value = int;
                // submit form
                document.getElementById("myForm").submit();
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