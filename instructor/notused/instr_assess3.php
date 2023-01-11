<?php
// start the session (access to user: loggedIn, first_name, email, type)
session_start();

// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../index.html");
    exit;
}

// if user account type is not 'instructor' then redirect then force logout
if($_SESSION["type"] !== "instructor"){
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$email = "Please select an email.";
$questions_answered = ""; // represents the json response string for all questions a user has answered
$questions_answered_bool = ""; // used to keep track if json response string $questions_answered is empty or contains data

// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // obtain the email the instructor wants to perform a search on
    $email = $_POST["email"];

    // starting json response string for answered questions
    $questions_answered .= "[";

    // read email static questions json filename
    $json_filename = "../user_data/questions/" . $email . ".json";
    $json = file_get_contents($json_filename);

    // if email does not exist
    if($json === false){
        echo($email . " is not in the system.");
        exit;
    }
    else{
        // decode the email JSON file (text => PHP assoc array)
        $json_data = json_decode($json, true);

        // loop through the PHP assoc array
        foreach($json_data as $question){
            // if a question has been answered, add it to $questions_answered
            if($question["datetime_started"] !== ""){
                // just insert the pkey of the question
                $questions_answered .= '{"pkey": ' . $question["pkey"] . '},';
            }
        }

        // if no answered questions found set bool to false
        // if answered questions found set bool to true
        if($questions_answered === "["){
            $questions_answered_bool = "false";
        }
        else{
            $questions_answered_bool = "true";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Instructor - Student Assess Page</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_assess.css" />
        <link rel="stylesheet" href="../assets/css/global/or2stem.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
        <script>
            MathJax = {
                loader: {
                load: ["input/asciimath", "output/chtml"]
                },
            };
        </script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
        <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/startup.js"></script>
    </head>
    <body>
        <div id="app">
            <!-- HEADER / NAV-->
            <header>
                <nav class="container">
                    <!-- FLOATING USER PROFILE -->
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["first_name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                        <a href="../register_login/logout.php">Logout</a>
                        </div>
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A" href="instr_index.php">
                                On-Ramp to STEM
                            </a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <!-- GREET DIV -->
            <div id="student_selection_div">
                <p>Welcome to the On-Ramp to STEM Instructor - Student Assess Mode!</p>
                <p>With this website you will be able to thoroughly inspect your students' performance.</p>
                <p>Begin by selecting a student's email from the drop down selection below.</p>
                <p id="student_error_p"></p>
                <!-- Form where instructor selects one of their student's email -->
                <div id="formDiv"></div>
            </div>

            <br><br>

            <!-- CHAPTER, SECTION, LEARNING OUTCOME SELECTION DIV -->
            <div id="content_selection_div_1">
                <hr><br>
                <p id="chSecLoHeader">Now select a Chapter, Section, and Learning Outcome!</p>
                <div id="content_selection_div_2">
                    <div id="ch_sec_lo_sel_div">
                        <table id="selection_table">
                            <!-- CHAPTER -->
                            <tr>
                                <td>Chapter:</td>
                                <td>
                                    <select id="chapter" onchange="getSectionOptions();">
                                        <option>Select a Chapter</option>
                                    </select>
                                </td>
                            </tr>
                            <!-- SECTION -->
                            <tr>
                                <td>Section:</td>
                                <td>
                                    <select id="section" onchange="getLoOptions();">
                                        <option>Select a Section</option>
                                    </select>
                                </td>
                            </tr>
                            <!-- LEARNING OUTCOME -->
                            <tr>
                                <td>Learning Outcome:</td>
                                <td>
                                    <select id="learningoutcome">
                                        <option>Select a Learning Outcome</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="ch_sec_lo_sel_button_div">
                        <button id="chSecLoButton" class="btn btn-fsblue" onclick="getChSecLoData()">OK</button>
                    </div>
                </div>
            </div>

            <br>

            <!-- MAIN CONTENT DIV -->
            <div id="mainDiv">
                <hr><br>
                <!-- DISPLAY TOTALS -->
                <p><b>Total Time Spent:</b> <span id="total_time_spent_span"></span>&emsp;&emsp;<b>Total Questions Correct:</b> <span id="total_questions_correct_span"></span>&emsp;&emsp;<b>Total Questions Incorrect: </b> <span id="total_questions_incorrect_span"></span></p>
                <br>
                <!-- DISPLAY HEADER BUTTON -->
                <div id="selectionInfoHeader" onclick="ToggleSelectionInformation()">
                    <p id="selectionInfoHeaderContent" class="blue_long_content"></p>
                    <p id="selectionInfoHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <!-- DISPLAY OF SELECTION INFO -->
                <div id="selection_results_div">
                    <table id="selection_results_table">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col" id="chosen_chapter"></th>
                            <th scope="col" id="chosen_section"></th>
                            <th scope="col" id="chosen_learningoutcome"></th>
                        </tr>
                        <tr>
                            <th scope="row">Time Spent</th>
                            <td id="chapter_time_td"></td>
                            <td id="section_time_td"></td>
                            <td id="learningoutcome_time_td"></td>
                        </tr>
                        <tr>
                        <th scope="row">Questions Correct</th>
                            <td id="chapter_correct_td"></td>
                            <td id="section_correct_td"></td>
                            <td id="learningoutcome_correct_td"></td>
                        </tr>
                        <tr>
                            <th scope="row">Questions Incorrect</th>
                            <td id="chapter_incorrect_td"></td>
                            <td id="section_incorrect_td"></td>
                            <td id="learningoutcome_incorrect_td"></td>
                        </tr>
                    </table>
                </div>

                <br>

                <!-- DISPLAY CH HEADER BUTTON -->
                <div class="blue_long_btn" onclick="getChapterData()">
                    <p class="blue_long_content">Chapter Progress</p>
                    <p id="chapterProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <!-- DISPLAY CHAPTER PROGRESS -->
                <div id="chapterProgressDiv"></div>

                <br>

                <!-- DISPLAY SEC HEADER BUTTON -->
                <div class="blue_long_btn" onclick="getSectionData()">
                    <p class="blue_long_content">Section Progress</p>
                    <p id="sectionProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <!-- DISPLAY SECTION PROGRESS -->
                <div id="sectionProgressDiv"></div>

                <br>

                <!-- DISPLAY LO HEADER BUTTON -->
                <div class="blue_long_btn" onclick="getLoData()">
                    <p class="blue_long_content">Learning Outcome Progress</p>
                    <p id="loProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <!-- DISPLAY LO PROGRESS -->
                <div id="loProgressDiv"></div>

                <br><br>

                <!-- MAIN QUESTION + BUTTONS + OPTIONS DISPLAY -->
                <div id="questionMain">
                    <div id="leftButtonsDiv">
                        <button class="btn btn-fsblue" id="goFirstButton" onclick="goFirst()">Go to First Question</button>
                        <button class="btn btn-fsblue" id="previousButton" onclick="previous()">Previous Question</button>
                    </div>

                    <div id="questionDisplay">
                        <h3 id="questionHeader" style="text-decoration: underline;"></h3>
                        <div id="quiz">
                            <p id="text"></p>
                            <p id="numTries"></p>
                            <img id="mainImg" src="" alt="" />
                            <div id="optionsDiv"></div>
                        </div>
                    </div>

                    <div id="rightButtonsDiv">
                        <button class="btn btn-fsblue" id="goLastButton" onclick="goLast()">Go to Last Question</button>
                        <button class="btn btn-fsblue" id="nextButton" onclick="next()">Next Question</button>
                    </div>
                </div>

                <br>

                <!-- DISPLAY QUESTION RESULT -->
                <div id="resultsDiv">
                    <table id="resultsTable">
                        <tr>
                            <th>Number of Current Attempts</th>
                            <th>Correct</th>
                            <th>Correct Answer</th>
                            <th>Date/Time Started</th>
                            <th>Date/Time Answered</th>
                            <th>Question Created On</th>
                        </tr>
                        <tr>
                            <td>
                                <p id="numCurrentTries"></p>
                            </td>
                            <td>
                                <p id="correct"></p>
                            </td>
                            <td>
                                <p id="correctAnswer"></p>
                            </td>
                            <td>
                                <p id="datetime_started"></p>
                            </td>
                            <td>
                                <p id="datetime_answered"></p>
                            </td>
                            <td>
                                <p id="createdOn"></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <br><br>
            </div>

            <!-- FOOTER -->
            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="" class="router-link-active">
                                <p>On-Ramp to STEM</p>
                            </a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li>
                                <a href="../index.html" class="router-link-active">Home</a>
                                </li>
                                <li>
                                <a href="" class="">About Us</a>
                                </li>
                                <li>
                                <a href="" class="">FAQ</a>
                                </li>
                                <li>
                                <a href="" class="">Contact Us</a>
                                </li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li>
                                <a href="https://scale.fresnostate.edu/scale/" target="_blank"> CSU SCALE </a>
                                </li>
                                <li>
                                <a href="http://fresnostate.edu/" target="_blank"> CSU Fresno Homepage </a>
                                </li>
                                <li>
                                <a href="http://www.fresnostate.edu/csm/csci/" target="_blank"> Department of Computer Science </a>
                                </li>
                                <li>
                                <a href="http://www.fresnostate.edu/csm/math/" target="_blank"> Department of Mathematics </a>
                                </li>
                            </ul>
                        </div>
                        <div class="contact">
                            <h4>Contact Us</h4>
                            <p> 5241 N. Maple Ave. <br /> Fresno, CA 93740 <br /> Phone: 559-278-4240 <br />
                            </p>
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
            var questions_answered_obj; // will hold the questions that have been answered by the selected student email
            var ch_sec_lo_obj;          // will hold at least 1 math question from db where tags match
            var ch_sec_lo_info_obj;     // will hold info on time spent, correct questions, incorrect questions, totals, from selected ch, sec, and lo
            var index = 0;              // keeps track of next and prev button / questions
            var totalQuestions;         // holds total amount of questions currently in ch_sec_lo_obj
            var correctAnswer;          // used to display the correct Answer on questions that have been answered
            

            // hide or display HTML elements
            let hideDisplay = () =>{
                document.getElementById("selection_results_div").style.display = "none";
                document.getElementById("content_selection_div_1").style.display = "none";
                document.getElementById("mainDiv").style.display = "none";
                var x = document.getElementById("chapterProgressDiv");
                x.style.display = "none";
                x = document.getElementById("sectionProgressDiv");
                x.style.display = "none";
                x = document.getElementById("loProgressDiv");
                x.style.display = "none";
            }
            let showQuestionForm = () =>{
                document.getElementById("content_selection_div_1").style.display = "";
                getChapterOptions();
            }
            let showMainDiv = () =>{
                document.getElementById("mainDiv").style.display = "";
            }
            let showSelectDiv = () =>{
                document.getElementById("button1").style.display = "none";
                document.getElementById("button2").style.display = "none";
                document.getElementById("button3").style.display = "none";
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


            // fxn to pad a number for display purposes (time spent)
            function str_pad_left(string,pad,length) {
                return (new Array(length+1).join(pad)+string).slice(-length);
            }

            
            // display data from PHP, one question at a time according to index
            let displayData = () =>{
                
                // count the total number of questions in the learning objective ch_sec_lo_obj
                totalQuestions = ch_sec_lo_obj.length;

                // display question number out of total number of questions along with specific title
                document.getElementById("questionHeader").innerHTML = "Question (" + (index + 1) + "/" + totalQuestions + "): " + ch_sec_lo_obj[index]["title"];

                // display question text but first convert BR back to \n before displaying text 
                if(ch_sec_lo_obj[index]["text"].includes("BR")){
                    ch_sec_lo_obj[index]["text"] = ch_sec_lo_obj[index]["text"].replaceAll("BR", "\n");
                }
                document.getElementById("text").innerHTML = ch_sec_lo_obj[index]["text"];

                // always display numTries in professor mode
                document.getElementById("numTries").innerHTML = "Allowed attempts: " + ch_sec_lo_obj[index]["numTries"];

                // check that question does not contain images for options (regular presentation of question)
                if(ch_sec_lo_obj[index]["isImage"][0] === false){

                    // display pic, only if pic file is present
                    if(ch_sec_lo_obj[index]["pic"] === ""){
                        document.getElementById("mainImg").style.display = "none";
                    }
                    else{
                        document.getElementById("mainImg").src = "../assets/img/" + ch_sec_lo_obj[index]["pic"];
                        document.getElementById("mainImg").alt = "main math picture";
                    }

                    // before displaying options first get the correct answer
                    let correctIndex = 0;
                    for(let i = 0; i < ch_sec_lo_obj[index]["rightAnswer"].length; i++){
                        if(ch_sec_lo_obj[index]["rightAnswer"][i] == true){
                            break;
                        }
                        else{
                            correctIndex++;
                        }
                    }
                    correctAnswer = ch_sec_lo_obj[index]["options"][correctIndex];

                    // always display options always in professor mode
                    let optionsLength = ch_sec_lo_obj[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++){
                        str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + ch_sec_lo_obj[index]["options"][i] + '"><label for="option' + i + '" id="label' + i + '">' + ch_sec_lo_obj[index]["options"][i] + '</label><br>';
                    }
                    str += '</form>';
                    document.getElementById("optionsDiv").innerHTML=str;

                    if(ch_sec_lo_obj[index]["datetime_answered"] === ""){
                        document.getElementById("datetime_answered").innerHTML = "N/A";
                        document.getElementById("datetime_started").innerHTML = "N/A";
                        document.getElementById("correctAnswer").innerHTML = "N/A";
                    }
                    else{
                        document.getElementById("datetime_answered").innerHTML = ch_sec_lo_obj[index]["datetime_answered"];
                        document.getElementById("datetime_started").innerHTML = ch_sec_lo_obj[index]["datetime_started"];
                        document.getElementById("correctAnswer").innerHTML = correctAnswer;
                    }

                }
                else{

                    // mainImg will be hidden bc images will be present in options
                    document.getElementById("mainImg").style.display = "none";

                    // before displaying options first get the correct answer, then shuffle the options
                    let correctIndex = 0;
                    for(let i = 0; i < ch_sec_lo_obj[index]["rightAnswer"].length; i++){
                        if(ch_sec_lo_obj[index]["rightAnswer"][i] == true){
                            break;
                        }
                        else{
                            correctIndex++;
                        }
                    }
                    correctAnswer = ch_sec_lo_obj[index]["options"][correctIndex];

                    // always display options
                    let optionsLength = ch_sec_lo_obj[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++){
                        // some options have ` in them, remove them if found
                        if(ch_sec_lo_obj[index]["options"][i].includes("`")){
                            ch_sec_lo_obj[index]["options"][i] = ch_sec_lo_obj[index]["options"][i].replaceAll("`", "");
                        }
                        if(i !== 2){
                            str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + ch_sec_lo_obj[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + ch_sec_lo_obj[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + obj[index]["options"][i] + '</label>
                        }
                        else{
                            str += '<br><input id="option' + i + '" type="radio" name="dynamic_option" value="' + ch_sec_lo_obj[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + ch_sec_lo_obj[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + obj[index]["options"][i] + '</label>
                        }

                    }
                    str += '</form>';
                    document.getElementById("optionsDiv").innerHTML=str;

                    // display results data
                    if(ch_sec_lo_obj[index]["datetime_answered"] === ""){
                        document.getElementById("datetime_answered").innerHTML = "N/A";
                        document.getElementById("datetime_started").innerHTML = "N/A";
                        document.getElementById("correctAnswer").innerHTML = "N/A";
                    }
                    else{
                        document.getElementById("datetime_answered").innerHTML = ch_sec_lo_obj[index]["datetime_answered"];
                        document.getElementById("datetime_started").innerHTML = ch_sec_lo_obj[index]["datetime_started"];
                        document.getElementById("correctAnswer").innerHTML = '<img src="../assets/img/"' + correctAnswer + '" alt="correct image option" style="width:150px; height:150px"/>';
                    }
                }
                
                // display results data
                if(ch_sec_lo_obj[index]["numCurrentTries"] === "0"){
                    document.getElementById("numCurrentTries").innerHTML = "N/A";
                }
                else{
                    document.getElementById("numCurrentTries").innerHTML = ch_sec_lo_obj[index]["numCurrentTries"];
                }
                
                if(ch_sec_lo_obj[index]["correct"] === ""){
                    document.getElementById("correct").innerHTML = "N/A";
                }
                else{
                    document.getElementById("correct").innerHTML = ch_sec_lo_obj[index]["correct"];
                }
                
                document.getElementById("createdOn").innerHTML = ch_sec_lo_obj[index]["createdOn"];

                // To use at the end to refresh the presentation of the equations to account for dynamic data
                MathJax.typeset();
            }


            // fully clears data from the necessary fields
            let clearData = () =>{
                // clearing data from questionDisplay div (necessary because some questions might have more complete fields than others)
                document.getElementById("text").innerHTML = "";
                document.getElementById("numTries").innerHTML = "";

                // if new image is empty
                if(ch_sec_lo_obj[index]["pic"] === ""){
                    document.getElementById("mainImg").src = "";
                    document.getElementById("mainImg").alt = "";
                    document.getElementById("mainImg").style.display = "none";
                }
                else{
                document.getElementById("mainImg").style.display = "";
                }

                // clear optionsDiv
                document.getElementById("optionsDiv").innerHTML = "";
                
                document.getElementById("numCurrentTries").innerHTML = "";
                document.getElementById("correct").innerHTML = "";
                document.getElementById("correctAnswer").innerHTML = "";
                document.getElementById("datetime_started").innerHTML = "";
                document.getElementById("datetime_answered").innerHTML = "";
                document.getElementById("createdOn").innerHTML = "";
            }


            /* MOVEMENT */
            let next = () =>{
                // making sure we are in legal index bound
                if(index !== totalQuestions - 1){
                    // update index to go forward
                    index++;
                    // clear previous question data
                    clearData();
                    // display new question data
                    displayData();
                }
            }
            let previous = () =>{
                // making sure we are in legal index bound
                if(index !== 0){
                    // update index to go back
                    index--;
                    // clear previous question data
                    clearData();
                    // display new question data
                    displayData();
                }
            }
            let goFirst = () =>{
                // setting the index to the start
                index = 0;
                // clear previous question data
                clearData();
                // display new question data
                displayData();
            }
            let goLast = () =>{
                // setting the index to the last
                index = totalQuestions - 1;
                // clear previous question data
                clearData();
                // display new question data
                displayData();
            }


            /* FUNCTIONS TO GET CH SELECTION, SEC SELECTION, LO SELECTION */
            let getChapterInfo = () =>{
                // we will first access the chapter HTML element and then its text
                let select = document.getElementById("chapter");
                let complete = select.options[select.selectedIndex].text
                let firstPeriodIndex = complete.indexOf(".");
                var chapterNumber = complete.slice(0, firstPeriodIndex);
                return chapterNumber;
                // chapterNumber = 1
            }
            let getSectionInfo = () =>{
                let select = document.getElementById("section");
                let complete = select.options[select.selectedIndex].text;
                let pos1 = complete.indexOf(".");
                let pos2 = complete.indexOf(".", pos1 + 1);
                var sectionNumber = complete.slice(0, pos2);
                return sectionNumber;
                // sectionNumber = 1.2
            }
            let getLearningOutcomeInfo = () =>{
                let select = document.getElementById("learningoutcome");
                let complete = select.options[select.selectedIndex].text;
                let pos1 = complete.indexOf(".");
                let pos2 = complete.indexOf(".", pos1 + 1);
                let pos3 = complete.indexOf(".", pos2 + 1);
                var learningoutcomeNumber = complete.slice(0, pos3);
                return learningoutcomeNumber;
                // learningoutcomeNumber = 1.2.3
            }


            // used to show and hide the contents of the selection information
            var selection_clicked = false;
            let ToggleSelectionInformation = () =>{
                if(selection_clicked){
                    document.getElementById("selectionInfoHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("selection_results_div").style.display = "none";
                    selection_clicked = false;
                } 
                else{
                    document.getElementById("selectionInfoHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("selection_results_div").style.display = "";
                    selection_clicked = true;
                }
            }


            function GetTag(){
                // setting hidden form variables
                var select = document.getElementById("learningoutcome");
                document.getElementById("search_tags").value = select.options[select.selectedIndex].text;
                document.getElementById("learningoutcome_selected").value = select.options[select.selectedIndex].text;

                select = document.getElementById("chapter");
                document.getElementById("chapter_selected").value = select.options[select.selectedIndex].text;

                select = document.getElementById("section");
                document.getElementById("section_selected").value = select.options[select.selectedIndex].text;

                document.getElementById("chapter_info_form").value = getChapterInfo();

                document.getElementById("section_info_form").value = getSectionInfo();

                document.getElementById("learningoutcome_info_form").value = getLearningOutcomeInfo();
            }

            

            /* OBTAINING LIST OF STUDENTS THAT BELONG TO THE INSTRUCTOR */
            var request;                // used for the XML request
            var studentsArr;            // will contain the students
            let getUserOptions = () =>{
                // we need to grab the options from the PGSQL DB
                request = new XMLHttpRequest();
                request.open('POST', '../pgsql/get_students.php', true);
                request.onreadystatechange = respond;
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.send();
            }
            let respond = () =>{
                if (request.readyState == 4 && request.status == 200) {
                    //console.log("PHP sent back: " + request.responseText);
                    studentsArr = request.responseText.split(",");
                    // clear spaces
                    for(let i = 0; i < studentsArr.length; i++){
                        studentsArr[i] = studentsArr[i].trim();
                    }
                    displayUserOptions();
                }
            }   
            let displayUserOptions = () =>{
                let str = '<form id="mainForm" action="" method="post">';
                str += '<select id="email_input" name="email" required>';
                str += '<option value="<?= $email; ?>" selected="selected"><?= $email; ?></option>';
                for(let i = 0; i < studentsArr.length; i++){
                    str += '<option value="' + studentsArr[i] + '">' + studentsArr[i] + '</option>';
                }
                str += '</select>';
                str += '<br><br>';
                str += '<input class="btn btn-fsblue" type="submit" name="submit" value="OK" onclick="GetTag()">';
                str += '</form>';

                document.getElementById("formDiv").innerHTML = str;
            }


    
            /* GET AND DISPLAY QUESTIONS WHERE TAGS MATCH */
            var request_ch_sec_lo;
            let getChSecLoData = () =>{
                // start XMLHttpRequest
                request_ch_sec_lo = new XMLHttpRequest();
                request_ch_sec_lo.open('POST', '../pgsql/get_ch_sec_lo_data.php', true);
                request_ch_sec_lo.onreadystatechange = getChSecLoDataResponse;
                request_ch_sec_lo.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                // getting lo
                let lo = getLearningOutcomeInfo(); // 1.2.3
                request_ch_sec_lo.send("email=<?= $email; ?>&search_tags=" + lo);
            }
            let getChSecLoDataResponse = () =>{
                if (request_ch_sec_lo.readyState == 4 && request_ch_sec_lo.status == 200) {
                    //console.log("PHP sent back: " + request_ch_sec_lo.responseText);
                    ch_sec_lo_obj = JSON.parse(request_ch_sec_lo.responseText);
                    showChSecLoData();
                }
            }
            let showChSecLoData = () =>{
                showMainDiv();
                displayData();
                // another xml request
                getChSecLoData2();
            }



            /* GET AND DISPLAY CH, SEC, LO INFORMATION */
            var request_ch_sec_lo_2;
            let getChSecLoData2 = () =>{
                // start XMLHttpRequest
                request_ch_sec_lo_2 = new XMLHttpRequest();
                request_ch_sec_lo_2.open('POST', '../pgsql/get_ch_sec_lo_data2.php', true);
                request_ch_sec_lo_2.onreadystatechange = getChSecLoData2Response;
                request_ch_sec_lo_2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                // creating local variables with data that will be sent to php
                let chapter = getChapterInfo(); // 1
                let section = getSectionInfo(); // 1.2
                let lo = getLearningOutcomeInfo(); // 1.2.3
                var select = document.getElementById("chapter");
                let chapter_digit= select.options[select.selectedIndex].text;
                select = document.getElementById("section");
                let section_digit = select.options[select.selectedIndex].text;
                select = document.getElementById("learningoutcome");
                let lo_digit = select.options[select.selectedIndex].text;

                request_ch_sec_lo_2.send("email=<?= $email; ?>&search_tags=" + lo + "&chapter_selected=" + chapter_digit + "&section_selected=" + section_digit + "&learningoutcome_selected=" + lo_digit + "&chapter_info_form=" + chapter + "&section_info_form=" + section + "&learningoutcome_info_form=" + lo);
            }
            let getChSecLoData2Response = () =>{
                if (request_ch_sec_lo_2.readyState == 4 && request_ch_sec_lo_2.status == 200) {
                    //console.log("PHP sent back: " + request_ch_sec_lo_2.responseText);
                    ch_sec_lo_info_obj = JSON.parse(request_ch_sec_lo_2.responseText);
                    displayChSecLoData2();
                }
            }
            let displayChSecLoData2 = () =>{
                // display totals
                document.getElementById("total_time_spent_span").innerHTML = ch_sec_lo_info_obj[0]["total_time"];
                document.getElementById("total_questions_correct_span").innerHTML = ch_sec_lo_info_obj[0]["total_correct"];
                document.getElementById("total_questions_incorrect_span").innerHTML = ch_sec_lo_info_obj[0]["total_incorrect"];
                // display learning outcome header (button)
                let select = document.getElementById("learningoutcome");
                document.getElementById("selectionInfoHeaderContent").innerHTML = select.options[select.selectedIndex].text;
                // display table headers
                document.getElementById("chosen_learningoutcome").innerHTML = select.options[select.selectedIndex].text;
                select = document.getElementById("chapter");
                document.getElementById("chosen_chapter").innerHTML = select.options[select.selectedIndex].text;
                select = document.getElementById("section");
                document.getElementById("chosen_section").innerHTML = select.options[select.selectedIndex].text;
                // display table rows
                document.getElementById("chapter_time_td").innerHTML = ch_sec_lo_info_obj[0]["chapter_time"];
                document.getElementById("section_time_td").innerHTML = ch_sec_lo_info_obj[0]["section_time"];
                document.getElementById("learningoutcome_time_td").innerHTML = ch_sec_lo_info_obj[0]["learningoutcome_time"];
                document.getElementById("chapter_correct_td").innerHTML = ch_sec_lo_info_obj[0]["chapter_correct"];
                document.getElementById("section_correct_td").innerHTML = ch_sec_lo_info_obj[0]["section_correct"];
                document.getElementById("learningoutcome_correct_td").innerHTML = ch_sec_lo_info_obj[0]["learningoutcome_correct"];
                document.getElementById("chapter_incorrect_td").innerHTML = ch_sec_lo_info_obj[0]["chapter_incorrect"];
                document.getElementById("section_incorrect_td").innerHTML = ch_sec_lo_info_obj[0]["section_incorrect"];
                document.getElementById("learningoutcome_incorrect_td").innerHTML = ch_sec_lo_info_obj[0]["learningoutcome_incorrect"];
            }



            /* GET AND DISPLAY CHAPTER PROGRESS DATA */
            var chapter_clicked = false;
            var request_ch;
            var chapter_obj;
            let getChapterData = () =>{
                // toggle chapterProgressHeader innerHTML & display
                if(chapter_clicked){
                    document.getElementById("chapterProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("chapterProgressDiv").style.display = "none";
                    chapter_clicked = false;
                } 
                else{
                    document.getElementById("chapterProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("chapterProgressDiv").style.display = "";
                    chapter_clicked = true;
                    // start XMLHttpRequest
                    let select = document.getElementById("email_input");
                    let user = select.options[select.selectedIndex].text;
                    request_ch = new XMLHttpRequest();
                    request_ch.open('POST', '../pgsql/get_ch_prog.php', true);
                    request_ch.onreadystatechange = getChapterDataResponse;
                    request_ch.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_ch.send("user=<?= $email; ?>");
                }
            }
            let getChapterDataResponse = () =>{
                if (request_ch.readyState == 4 && request_ch.status == 200) {
                    //console.log("PHP sent back: " + request_ch.responseText);
                    chapter_obj = JSON.parse(request_ch.responseText);
                    showChapterProgress();
                }
            }
            let showChapterProgress = () =>{
                // create a table with all the chapter data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Ch</th><th>Chapter Name</th><th>Number of Questions</th><th>Number Correct</th><th>Number Completed</th><th>Time Spent</th></tr>';
                for(const key in chapter_obj){
                    const value = chapter_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td><p>' + key + '</p></td><td><p>' + value["Name"] + '</p></td><td><p>' + value["TotalQuestions"] + '</p></td><td><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td><td><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td><td><p>' + finalTime + '</p></td></tr>';
                }
                str += '</table>';
                document.getElementById("chapterProgressDiv").innerHTML = str;            
            }



            /* GET AND DISPLAY SECTION PROGRESS DATA */
            var section_clicked = false;
            var request_sec;
            var section_obj;
            let getSectionData = () =>{
                // toggle sectionProgressButton innerHTML & display
                if(section_clicked){
                    document.getElementById("sectionProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("sectionProgressDiv").style.display = "none";
                    section_clicked = false;
                } 
                else{
                    document.getElementById("sectionProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("sectionProgressDiv").style.display = "";
                    section_clicked = true;
                    // start XMLHttpRequest
                    let chapter = getChapterInfo();
                    request_sec = new XMLHttpRequest();
                    request_sec.open('POST', '../pgsql/get_sec_prog.php', true);
                    request_sec.onreadystatechange = getSectionDataResponse;
                    request_sec.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_sec.send("user=<?= $email; ?>&chapter=" + chapter);
                }
            }
            let getSectionDataResponse = () =>{
                if (request_sec.readyState == 4 && request_sec.status == 200) {
                    //console.log("PHP sent back: " + request_sec.responseText);
                    section_obj = JSON.parse(request_sec.responseText);
                    showSectionProgress();
                }
            }
            let showSectionProgress = () =>{
                // create a table with all the section data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Ch.Sec</th><th>Section Name</th><th>Number of Questions</th><th>Number Correct</th><th>Number Completed</th><th>Time Spent</th></tr>';
                for(const key in section_obj){
                    const value = section_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td><p>' + key + '</p></td><td><p>' + value["Name"] + '</p></td><td><p>' + value["TotalQuestions"] + '</p></td><td><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td><td><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td><td><p>' + finalTime + '</p></td></tr>';
                }
                str += '</table>';
                document.getElementById("sectionProgressDiv").innerHTML = str;    
            }



            /* GET AND DISPLAY LO PROGRESS DATA */
            var lo_clicked = false;
            var request_lo;
            var lo_obj;
            let getLoData = () =>{
                // toggle loProgressButton innerHTML & display
                if(lo_clicked){
                    document.getElementById("loProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("loProgressDiv").style.display = "none";
                    lo_clicked = false;
                } 
                else{
                    document.getElementById("loProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("loProgressDiv").style.display = "";
                    lo_clicked = true;
                    // start XMLHttpRequest
                    let chapter = getChapterInfo();
                    let section = getSectionInfo();
                    request_lo = new XMLHttpRequest();
                    request_lo.open('POST', '../pgsql/get_lo_prog.php', true);
                    request_lo.onreadystatechange = getLoDataResponse;
                    request_lo.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_lo.send("user=<?= $email; ?>&chapter=" + chapter + "&section=" + section);
                }
            }
            let getLoDataResponse = () =>{
                if (request_lo.readyState == 4 && request_lo.status == 200) {
                    //console.log("PHP sent back: " + request_lo.responseText);
                    lo_obj = JSON.parse(request_lo.responseText);
                    showLoProgress();
                }
            }
            let showLoProgress = () =>{
                // create a table with all the lo data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Ch.Sec.Lo</th><th>Learning Outcome Name</th><th>Number of Questions</th><th>Number Correct</th><th>Number Completed</th><th>Time Spent</th></tr>';
                for(const key in lo_obj){
                    const value = lo_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td><p>' + key + '</p></td><td><p>' + value["Name"] + '</p></td><td><p>' + value["TotalQuestions"] + '</p></td><td><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td><td><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td><td><p>' + finalTime + '</p></td></tr>';
                }
                str += '</table>';
                document.getElementById("loProgressDiv").innerHTML = str;   
            }



            /* GET AND DISPLAY CHAPTER OPTIONS UNIQUE TO STUDENT SELECTED */
            var ch_req;              
            var ch_obj;            
            let getChapterOptions = () =>{
                // we need to grab the options from the PGSQL DB
                ch_req = new XMLHttpRequest();
                ch_req.open('POST', '../learning_map/get_chapters.php', true);
                ch_req.onreadystatechange = getChapterOptionsResponse;
                ch_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_req.send("user=<?= $email; ?>");
            }
            let getChapterOptionsResponse = () =>{
                if (ch_req.readyState == 4 && ch_req.status == 200) {
                    //console.log("PHP sent back: " + ch_req.responseText);
                    ch_obj = JSON.parse(ch_req.responseText);
                    displayChapterOptions();
                }
            }   
            let displayChapterOptions = () =>{
                let str = '<option id="ch_option_1">Select a Chapter</option>';
                let i = 2;
                for(const [key, value] of Object.entries(ch_obj)){
                    str += '<option id="ch_option_' + i + '" value="' + key + '">' + key + ". " + value + '</option>';
                    i++;
                }
                document.getElementById("chapter").innerHTML = str;
            }



            /* GET AND DISPLAY SECTION OPTIONS UNIQUE TO STUDENT SELECTED */
            var sec_req;                // used for the XML request
            var sec_obj;            // will contain the students
            let getSectionOptions = () =>{
                // we need to grab the options from the PGSQL DB
                sec_req = new XMLHttpRequest();
                sec_req.open('POST', '../learning_map/get_sections.php', true);
                sec_req.onreadystatechange = getSectionOptionsResponse;
                sec_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                let select = document.getElementById("chapter");
                let chapter = select.options[select.selectedIndex].value;
                sec_req.send("user=<?= $email; ?>&chapter=" + chapter);
            }
            let getSectionOptionsResponse = () =>{
                if (sec_req.readyState == 4 && sec_req.status == 200) {
                    //console.log("PHP sent back: " + sec_req.responseText);
                    sec_obj = JSON.parse(sec_req.responseText);
                    displaySectionOptions();
                }
            }   
            let displaySectionOptions = () =>{
                let str = '<option id="sec_option_1">Select a Section</option>';
                let i = 2;
                for(const [key, value] of Object.entries(sec_obj)){
                    let index = key.indexOf('.');
                    let sec_num = key.slice(index + 1, key.length);
                    str += '<option id="sec_option_' + i + '" value="' + sec_num + '">' + key + ". " + value + '</option>';
                    i++;
                }
                document.getElementById("section").innerHTML = str;
            }



            /* GET AND DISPLAY LEARNING OUTCOME OPTIONS UNIQUE TO STUDENT SELECTED */
            var lo_req;                // used for the XML request
            var lo_obj;            // will contain the students
            let getLoOptions = () =>{
                // we need to grab the options from the PGSQL DB
                lo_req = new XMLHttpRequest();
                lo_req.open('POST', '../learning_map/get_los.php', true);
                lo_req.onreadystatechange = getLoOptionsResponse;
                lo_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                let select = document.getElementById("chapter");
                let chapter = select.options[select.selectedIndex].value;
                select = document.getElementById("section");
                let section = select.options[select.selectedIndex].value;
                lo_req.send("user=<?= $email; ?>&chapter=" + chapter + "&section=" + section);
            }
            let getLoOptionsResponse = () =>{
                if (lo_req.readyState == 4 && lo_req.status == 200) {
                    //console.log("PHP sent back: " + lo_req.responseText);
                    lo_obj = JSON.parse(lo_req.responseText);
                    displayLoOptions();
                }
            }   
            let displayLoOptions = () =>{
                let str = '<option id="lo_option_1">Select a Learning Outcome</option>';
                i = 2;
                for(const [key, value] of Object.entries(lo_obj)){
                    let pos1 = key.indexOf('.');
                    let pos2 = key.indexOf('.', pos1 + 1);
                    let lo_num = key.slice(pos2 + 1, key.length);
                    str += '<option id="lo_option_' + i + '" value="' + lo_num + '">' + key + ". " + value + '</option>';
                    i++;
                }
                document.getElementById("learningoutcome").innerHTML = str;
            }



            /* DRIVER */
            hideDisplay();          // hiding some divs at page load

            getUserOptions();       // getting the list of users assigned to the instructor

            // if the user hasn't answered any questions, can't do anything else for that user
            if("<?= $questions_answered_bool; ?>" === "false"){
                console.log("Student has not answered any questions yet, please select a different student.");
                document.getElementById("student_error_p").innerHTML = "Student has not answered any questions yet, please select a different student.";
            }
            // if the user has answered some questions, proceed to show the questionsAnsweredDiv and parse the data returned by php
            else if ("<?= $questions_answered_bool; ?>" === "true"){
                document.getElementById("student_error_p").innerHTML = "";
                showQuestionForm();
            }

        </script>
    </body>
</html>