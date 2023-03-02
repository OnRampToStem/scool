<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is 'Mentor' redirect to main page
if ($_SESSION["type"] === "Mentor") {
    header("location: instr_index1.php");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$name;
$public;
$duration;
$open_date; $open_time; $close_date; $close_time;
$num_of_selected_los;
$lo_num = [];
$questions = [];
$points = [];
$result;

// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] === "POST"){

    // extract POST data
    $name = $_POST["name"];
    $public = $_POST["public"];
    $duration = $_POST["duration"];
    $open_date = $_POST["open_date"]; $open_time = $_POST["open_time"];
    $close_date = $_POST["close_date"]; $close_time = $_POST["close_time"];
    $num_of_selected_los = $_POST["num_of_selected_los"];
    for($i = 1; $i <= $num_of_selected_los; $i++){
        array_push($lo_num, $_POST["lonum_${i}"]);
        array_push($questions, $_POST["questions_${i}"]);
        array_push($points, $_POST["points_${i}"]);
    }

    /* NOW SAVE JSON DATA INTO 'Assessments' TABLE IN PGSQL DB */

    // connect to the db
    require_once "../register_login/config.php";

    // create the json content string
    $json_content = "[";
    for($i = 0; $i < $num_of_selected_los; $i++){
        // first entries
        if($i !== $num_of_selected_los - 1){
            $json_content .= "{";
            $json_content .= "\"LearningOutcomeNumber\": \"" . $lo_num[$i] . "\",";
            $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
            $json_content .= "\"NumberPoints\": " . $points[$i];
            $json_content .= "},";
        }
        // last entry
        else{
            $json_content .= "{";
            $json_content .= "\"LearningOutcomeNumber\": \"" . $lo_num[$i] . "\",";
            $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
            $json_content .= "\"NumberPoints\": " . $points[$i];
            $json_content .= "}";
        }
    }
    $json_content .= "]";

    // inserting values into table, (manually adding ' ' needed for PostgreSQL query strings / text)
    $query = "INSERT INTO assessments(instructor, name, public, duration, open_date, open_time, close_date, close_time, content, course_name, course_id)
              VALUES ('" . $_SESSION["email"] . "', '" . $name . "', '" . $public . "', " . $duration . ", '" . $open_date  . "', '" . $open_time . "', '" . $close_date  . "', '" . $close_time . "', '" . $json_content
              . "', '" . $_SESSION['selected_course_name'] . "', '" . $_SESSION['selected_course_id'] . "')";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

    $result = "Assessment creation was successful.";

    pg_close($con);

    /* ONLY USED TO SAVE POST DATA AS A JSON FILE IN A SERVER DIRECTORY */
    /*
    // perform actions with the data
    if(file_exists("/Applications/MAMP/htdocs/hub_v1/assessments/" . $name . ".json")){
        $result = "Could not create your assessment because there already exists an assessment
                   with ${name} as the name of the assessment in the directory. \n
                   Please try again with a unique assessment name.";
    }
    else{
        // begin writing to personalized questions file
        $assessment_file = fopen("/Applications/MAMP/htdocs/hub_v1/assessments/" . $name . ".json", "w") or die("Unable to open file!");
        fwrite($assessment_file, "{");
        $json_content = "\n\t\"Name\": \"${name}\",";
        $json_content .= "\n\t\"Public\": \"${public}\",";
        $json_content .= "\n\t\"Duration\": \"${duration}\",";
        $json_content .= "\n\t\"OpenDate\": \"${open_date}\",";
        $json_content .= "\n\t\"OpenTime\": \"${open_time}\",";
        $json_content .= "\n\t\"CloseDate\": \"${close_date}\",";
        $json_content .= "\n\t\"CloseTime\": \"${close_time}\",";
        $json_content .= "\n\t\"LearningOutcomes\": [";
        for($i = 0; $i < $num_of_selected_los; $i++){
            // first entries
            if($i !== $num_of_selected_los - 1){
                $json_content .= "\n\t\t{";
                $json_content .= "\n\t\t\t\"LearningOutcomeNumber\": \"" . $lo_num[$i] . "\",";
                $json_content .= "\n\t\t\t\"NumberQuestions\": \"" . $questions[$i] . "\",";
                $json_content .= "\n\t\t\t\"NumberPoints\": \"" . $points[$i] . "\"";
                $json_content .= "\n\t\t},";
            }
            // last entry
            else{
                $json_content .= "\n\t\t{";
                $json_content .= "\n\t\t\t\"LearningOutcomeNumber\": \"" . $lo_num[$i] . "\",";
                $json_content .= "\n\t\t\t\"NumberQuestions\": \"" . $questions[$i] . "\",";
                $json_content .= "\n\t\t\t\"NumberPoints\": \"" . $points[$i] . "\"";
                $json_content .= "\n\t\t}";
            }
        }
        $json_content .= "\n\t]";
        $json_content .= "\n}";
        fwrite($assessment_file, $json_content);
        // output to client side
        $result = "Successfully created assessment: ${name}.";
    }
    */
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Assessment Creation</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_create2.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
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
                            <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main>
                <h1><?= $result ?></h1>
                <p><a href="instr_multi.php">Click here to view Assessments</a></p>
                <p><a href="instr_index1.php">Click Here to go to Instructor Home Page</a></p>
            </main>

            <br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="instr_index1.php"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="instr_index1.php">Home</a></li>
                                <li><a href="../navigation/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us.php">Contact Us</a></li>
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

        <!-- START OF JAVASCRIPT -->
        <script type="text/javascript">
            // controlling the user profile dropdown
            /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
            let showDropdown = () => {
                document.getElementById("myDropdown").classList.toggle("show");
            }
            // Close the dropdown if the user clicks outside of it
            window.onclick = function(event) {
                if(!event.target.matches('.dropbtn')) {
                    let dropdowns = document.getElementsByClassName("dropdown-content");
                    for (let i = 0; i < dropdowns.length; i++) {
                        let openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        </script>
    </body>
</html>