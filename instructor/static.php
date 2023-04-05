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

/* PHP GLOBALS */
$lo = "";
$chapter = "Select a Chapter";
$section = "Select a Section";
$learningoutcome = "Select a Learning Outcome";
$selected_questions = "temp";
$ready = false;

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // receiving POST inputs from user
    $lo = $_POST["lo"];                           // holds the lo selected (1.2.3)
    $chapter = $_POST["chapter"];                 // holds the chapter text selected (1. Functions)
    $section = $_POST["section"];                 // holds the section text selected (1.2. Domain and Range)
    $learningoutcome = $_POST["learningoutcome"]; // holds the lo text selected (1.2.3. Finding Domain and Range from Graphs)

    // filepath
    $json_filename = "../assets/json_data/new_final.json";
    // read the file to text
    $json = file_get_contents($json_filename);
    // decode the text into a PHP assoc arr
    $json_data = json_decode($json, true);

    // starting the json response string
    $selected_questions = "[";

    // loop through each static question
    foreach ($json_data as $question) {

        if ($question["tags"] === $lo) {

            // inserting basic data
            $selected_questions .= '{"pkey":' . $question["pkey"] . ', "title":"' . $question["title"] . '", "text":"' . $question["text"] . '", "pic":"' . $question["pic"] . '", "numTries":"' . $question["numTries"] . '", ';

            // inserting options
            $selected_questions .= '"options":[';
            for ($i = 0; $i < count($question["options"]); $i++) {
                // last element -> do not add comma to the option
                if ($i === count($question["options"]) - 1) $selected_questions .= '"' . $question["options"][$i] . '"], ';
                // any other element -> add comma to the option
                else $selected_questions .= '"' . $question["options"][$i] . '",';
            }

            // inserting rightAnswer
            $selected_questions .= '"rightAnswer":[';
            for ($i = 0; $i < count($question["rightAnswer"]); $i++) {
                // last element -> do not add comma to the option
                if ($i === count($question["rightAnswer"]) - 1) {
                    if ($question["rightAnswer"][$i] == 1) $selected_questions .= 'true], ';
                    else $selected_questions .= 'false], ';
                }
                // any other element -> add comma to the option
                else {
                    if ($question["rightAnswer"][$i] == 1) $selected_questions .= 'true,';
                    else $selected_questions .= 'false,';
                }
            }

            // inserting isImage
            $selected_questions .= '"isImage":[';
            for ($i = 0; $i < count($question["isImage"]); $i++) {
                // last element -> do not add comma to the option
                if ($i === count($question["isImage"]) - 1) {
                    if($question["isImage"][$i] == 1) $selected_questions .= 'true], ';
                    else $selected_questions .= 'false], ';
                }
                // any other element -> add comma to the option
                else {
                    if ($question["isImage"][$i] == 1) $selected_questions .= 'true,';
                    else $selected_questions .= 'false,';
                }
            }

            // inserting basic data
            $selected_questions .= '"tags":"' . $question["tags"] . '", "difficulty":"' . $question["difficulty"] . '"},';

        }

    }

    // removing last comma from the string
    $selected_questions = substr($selected_questions, 0, -1);
    // completing the json response string
    $selected_questions .= "]";
    // setting ready to true as a client-side indicator
    $ready = true;

}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OpenStax Questions</title>
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
                cssLink.setAttribute("href", `../assets/css/instructor/static-${window.localStorage.getItem("mode")}-mode.css`);
            }

            // mode
            let item = localStorage.getItem("mode");
            const cssLink = document.getElementById("css-mode");
            if (item === null) {
                window.localStorage.setItem('mode', 'OR2STEM');
                toggleCSS();
            }
            else {
                toggleCSS();
            }

            // banner
            item = localStorage.getItem("banner");
            const cssHeader = document.getElementById("css-header");
            if (item === null) {
                window.localStorage.setItem('banner', 'OR2STEM');
                toggleBanner();
            }
            else {
                toggleBanner();
            }
        </script>
        <script>
            MathJax = {
                loader: { load: ["input/asciimath", "output/chtml"] },
            };
        </script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
        <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/startup.js"></script>
    </head>
    <body onload="initialize();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown();">Hello <?= $_SESSION["name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../navigation/settings/settings.php">Settings</a>
                            <a href="../register_login/logout.php">Logout</a>
                        </div>
                        <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A" href="./instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <main>
                <div id="group1">
                    <h1>OpenStax Questions</h1>
                    <div id="loading-div">
                        LOADING...
                    </div>
                    <h3 id="group1h3" style="display:none;">Please select a Chapter, a Section, and a Learning Outcome.</h3>
                    <div id="group1_1" style="display:none;">
                        <div id="group1_1_1">
                            <h3>Chapter</h3>
                            <select id="chapter_options" onchange="chapterHelper1();getSectionOptions();">
                                <option selected="selected" disabled><?= $chapter; ?></option>
                            </select>
                        </div>
                        <div id="group1_1_2">
                            <h3>Section</h3>
                            <select id="section_options" onchange="sectionHelper1();getLoOptions();">
                                <option selected="selected" disabled><?= $section; ?></option>
                            </select>                                
                        </div>
                        <div id="group1_1_3">
                            <h3>Learning Outcome</h3>
                            <select id="learningoutcome_options">
                                <option selected="selected" disabled><?= $learningoutcome; ?></option>
                            </select>
                        </div>
                    </div>
                    <!-- hidden form used to transfer data to php code above -->
                    <div id="form_div">
                        <form id="main_form" action="" method="post">
                            <input id="lo" name="lo" type="text" style="display:none;">
                            <input id="chapter" name="chapter" type="text" style="display:none;">
                            <input id="section" name="section" type="text" style="display:none;">
                            <input id="learningoutcome" name="learningoutcome" type="text" style="display:none;">
                            <input id="go_btn" type="submit" value="Go" onclick="setFormInputs();" style="display:none;">
                        </form>
                    </div>
                </div>

                <hr id="hr" style="border: 1px dashed black; display: none;">

                <div id="selected-lo-header-div" style="display: none;">
                    <p><?= $learningoutcome; ?></p>
                </div>

                <div id="question-display-div" style="display: none;">
                    <div id="prev-btn-div">
                        <button id="prev-btn" onclick="prev();">Previous Question</button>
                    </div>

                    <div id="question-content-div">
                        <h3 id="questionHeader" style="text-decoration: underline;"></h3>
                        <h3 id="outcome" style="display:none;"></h3>
                        <div id="quiz">
                            <p id="text"></p>
                            <p id="numTries"></p>
                            <img id="mainImg" src="" alt="" />
                            <div id="optionsDiv"></div>
                        </div>
                    </div>

                    <div id="next-btn-div">
                        <button id="next-btn" onclick="next();">Next Question</button>
                    </div>
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
                                <li><a href="./instr_index1.php">Home</a></li>
                                <li><a href="../navigation/about-us/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us/contact-us.php">Contact Us</a></li>
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
            ////////////////
            // JS GLOBALS //
            ////////////////
            let index = 0;          // index of the current displayed static question
            let selectedQuestions;  // array containing all of the static questions for a selected learning outcome
            let totalQuestions;     // total questions in selectedQuestions
            let correctAnswer;      // correct answer for the given index of the static question
            let chBool = false;     // used to distinguish between section calls
            let secBool = false;    // used to distinguish between learning outcome calls



            //////////////////////
            // HELPER FUNCTIONS //
            //////////////////////

            const readChapterDigit = () => {
                let select = document.getElementById("chapter_options");
                let chapter = select.options[select.selectedIndex].text;
                let idx = chapter.indexOf(".");
                return chapter.slice(0, idx);
            }

            const readSectionDigit = () => {
                let select = document.getElementById("section_options");
                let sectionText = select.options[select.selectedIndex].text;
                let idx1 = sectionText.indexOf(".");
                let idx2 = sectionText.indexOf(".", idx1 + 1);
                return sectionText.slice(idx1 + 1, idx2);
            }

            const chapterHelper1 = () => {
                chBool = true;
            }
            const chapterHelper2 = () => {
                document.getElementById("mainSectionOption").innerHTML = "Select a Section";
                if (document.getElementById("mainLoOption") !== null) {
                    document.getElementById("mainLoOption").innerHTML = "Select a Learning Outcome";
                }
            }

            const sectionHelper1 = () => {
                secBool = true;
            }
            const sectionHelper2 = () => {
                document.getElementById("mainLoOption").innerHTML = "Select a Learning Outcome";
            }



            /////////////////
            // MAIN DRIVER //   
            /////////////////



            const initialize = () => {
                // always get the chapter options on each page load
                getChapterOptions();

                document.getElementById("group1h3").style.display = "";
                document.getElementById("group1_1").style.display = "";
                document.getElementById("go_btn").style.display = "";
                document.getElementById("loading-div").style.display = "none";

                // only run this code if Go button was pressed
                if (<?= json_encode($ready); ?>) {
                    // extract the selected questions from php
                    selectedQuestions = <?= $selected_questions; ?>;

                    document.getElementById("question-display-div").style.display = "";
                    document.getElementById("selected-lo-header-div").style.display = "";
                    document.getElementById("hr").style.display = "";

                    // get the chapter digit
                    let chapterDigit = readChapterDigit();
                    //console.log(`Chapter digit: ${chapterDigit}`);
                    getSectionOptions(chapterDigit);

                    // get the section digit
                    let sectionDigit = readSectionDigit();
                    //console.log(`Section digit: ${sectionDigit}`);
                    getLoOptions(chapterDigit, sectionDigit);

                    // display the data
                    displayData();
                }
            }



            ////////////////////////////////////////////////////
            // CHAPTER, SECTION, & LEARNING OUTCOME SELECTION //
            ////////////////////////////////////////////////////

            // getting all chapters from openStax.json
            let ch_req;                         
            let getChapterOptions = () => {
                console.log("Getting all chapter options...");
                ch_req = new XMLHttpRequest();
                ch_req.open('POST', './get/ch_names_2.php', true);
                ch_req.onreadystatechange = getChapterOptionsResponse;
                ch_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_req.send();
            }
            let getChapterOptionsResponse = () => {
                if (ch_req.readyState == 4 && ch_req.status == 200) {
                    //console.log("PHP sent back: " + ch_req.responseText);
                    let ch_obj = JSON.parse(ch_req.responseText);
                    
                    // display the chapters data options
                    let str = '<option selected="selected" disabled>' + "<?= $chapter; ?>" + '</option>';
                    for (const [key, value] of Object.entries(ch_obj)) {
                        str += `<option>${key}. ${value}</option>`; //value="${key}"
                    }
                    document.getElementById("chapter_options").innerHTML = str;
                }
            }   

            // getting all sections from selected chapter from openStax.json
            let sec_req_1;  
            let getSectionOptions = (chapterDigit) => {
                console.log("Getting all section options...");
                sec_req_1 = new XMLHttpRequest();
                sec_req_1.open('POST', './get/sec_names_2.php', true);
                sec_req_1.onreadystatechange = getSectionOptionsResponse;
                sec_req_1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                if (chapterDigit === undefined) {
                    sec_req_1.send("chapter=" + readChapterDigit());
                }
                else {
                    sec_req_1.send("chapter=" + chapterDigit);
                }
                
            }
            let getSectionOptionsResponse = () =>{
                if (sec_req_1.readyState == 4 && sec_req_1.status == 200) {
                    //console.log("PHP sent back: " + sec_req_1.responseText);
                    let sec_obj_1 = JSON.parse(sec_req_1.responseText);
                    
                    // now display the sections data
                    let str = '<option id="mainSectionOption" selected="selected" disabled>' + "<?= $section; ?>" + '</option>';
                    for(const [key, value] of Object.entries(sec_obj_1)){
                        //let sec_num = key.slice(key.indexOf('.') + 1, key.length);
                        str += `<option>${key}. ${value}</option>`; //value="${sec_num}"
                    }
                    document.getElementById("section_options").innerHTML = str;

                    if (chBool) {
                        chBool = false;
                        chapterHelper2();
                    }
                }
            }  

            // getting all los from selected section from openStax.json
            let lo_req_1;              
            let getLoOptions = (chapterDigit, sectionDigit) =>{
                console.log("Getting all learning outcome options...");
                lo_req_1 = new XMLHttpRequest();
                lo_req_1.open('POST', './get/lo_names_2.php', true);
                lo_req_1.onreadystatechange = getLoOptionsResponse;
                lo_req_1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                if (chapterDigit === undefined && sectionDigit === undefined) {
                    lo_req_1.send("chapter=" + readChapterDigit() + "&section=" + readSectionDigit());
                }
                else {
                    lo_req_1.send("chapter=" + chapterDigit + "&section=" + sectionDigit);
                }
            }
            let getLoOptionsResponse = () =>{
                if (lo_req_1.readyState == 4 && lo_req_1.status == 200) {
                    //console.log("PHP sent back: " + lo_req_1.responseText);
                    let lo_obj_1 = JSON.parse(lo_req_1.responseText);
                    
                    // now display the lo data
                    let str = '<option id="mainLoOption" selected="selected" disabled>' + "<?= $learningoutcome; ?>" + '</option>';
                    for(const [key, value] of Object.entries(lo_obj_1)){
                        //let lo_num = key.slice(key.indexOf('.', key.indexOf('.') + 1) + 1, key.length);
                        str += `<option>${key}. ${value}</option>`; //value="${lo_num}"
                    }
                    document.getElementById("learningoutcome_options").innerHTML = str;

                    if (secBool) {
                        secBool = false;
                        sectionHelper2();
                    }
                }
            }

            let setFormInputs = () => {
                // lo number
                let select = document.getElementById("learningoutcome_options");
                select = select.options[select.selectedIndex].text;
                let pos1 = select.indexOf(".");
                let pos2 = select.indexOf(".", pos1 + 1);
                let pos3 = select.indexOf(".", pos2 + 1);
                var learningoutcomeNumber = select.slice(0, pos3);
                document.getElementById("lo").value = learningoutcomeNumber;

                // chapter name
                select = document.getElementById("chapter_options");
                document.getElementById("chapter").value = select.options[select.selectedIndex].text;

                // section name
                select = document.getElementById("section_options");
                document.getElementById("section").value = select.options[select.selectedIndex].text;

                // learning outcome name
                select = document.getElementById("learningoutcome_options");
                document.getElementById("learningoutcome").value = select.options[select.selectedIndex].text;
            }



            //////////////////////////////////////////////////
            // MAIN FUNCTIONALITIES OF THE STATIC QUESTIONS //
            //////////////////////////////////////////////////

            // imported from https://stackoverflow.com/questions/2450954/how-to-randomize-shuffle-a-javascript-array
            // will randomly shuffle options array to use for display
            function shuffle(array) {
                let currentIndex = array.length,  randomIndex;
                // While there remain elements to shuffle.
                while (currentIndex != 0) {
                    // Pick a remaining element.
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex--;
                    // And swap it with the current element.
                    [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
                }
                return array;
            }


            let checkQuestion = () => {
                // reveal outcome element
                document.getElementById("outcome").style.display = "";

                // grab the selected option
                let selectedOption = document.querySelector('input[name="dynamic_option"]:checked').value;
                let correct;  // will hold 'yes' or 'no', for if user was correct or not

                // compare the selected option to the correct answer
                if(selectedOption == correctAnswer) {
                    //console.log("You got it right!");
                    document.getElementById("outcome").style.color = "green";
                    document.getElementById("outcome").innerHTML = "Correct!";
                    correct = "Yes";

                    // only modify the color of a label if it exists
                    if(selectedQuestions[index]["isImage"][0] === false) {
                        // grabbing input for attribute that is checked by user
                        let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                        // selecting associated label to the input selected changing to green
                        document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "green";                   
                    }
                }
                else {
                    //console.log("You got it wrong!");
                    document.getElementById("outcome").style.color = "red";
                    document.getElementById("outcome").innerHTML = "Incorrect!";
                    correct = "No";

                    // only modify the color of a label if it exists
                    if(selectedQuestions[index]["isImage"][0] === false) {
                        // grabbing input for attribute that is checked by user
                        let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                        // selecting associated label to the input selected changing to green
                        document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "red";                   
                    }   
                }
            }


            // display data from PHP, one question at a time according to index
            let displayData = () => {
                // count the total number of questions in the learning objective selectedQuestions
                totalQuestions = selectedQuestions.length;

                // display question number out of total number of questions along with specific title
                document.getElementById("questionHeader").innerHTML = "Question " + (index + 1) + " / " + totalQuestions; // + selectedQuestions[index]["title"];

                // display question text but first convert BR back to \n before displaying text 
                if(selectedQuestions[index]["text"].includes("BR")) {
                    selectedQuestions[index]["text"] = selectedQuestions[index]["text"].replaceAll("BR", "\n");
                }
                document.getElementById("text").innerHTML = selectedQuestions[index]["text"];

                // check that question does not contain images for options (regular presentation of question)
                if(selectedQuestions[index]["isImage"][0] === false) {

                    // display pic, only if pic file is present
                    if(selectedQuestions[index]["pic"] === "") {
                        document.getElementById("mainImg").style.display = "none";
                    }
                    else {
                        document.getElementById("mainImg").src = "../assets/img/" + selectedQuestions[index]["pic"];
                        document.getElementById("mainImg").alt = "main math picture";
                    }

                    // before displaying options first get the correct answer, then shuffle the options
                    let correctIndex = 0;
                    for(let i = 0; i < selectedQuestions[index]["rightAnswer"].length; i++) {
                        if(selectedQuestions[index]["rightAnswer"][i] == true) {
                            break;
                        }
                        else {
                            correctIndex++;
                        }
                    }
                    correctAnswer = selectedQuestions[index]["options"][correctIndex];
                    selectedQuestions[index]["options"] = shuffle(selectedQuestions[index]["options"]);

                    // always display options
                    let optionsLength = selectedQuestions[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++) {
                        str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + selectedQuestions[index]["options"][i] + '"><label for="option' + i + '" id="label' + i + '">' + selectedQuestions[index]["options"][i] + '</label><br>';
                    }
                    str += '<button id="checkAnswerButton" type="button" onclick="checkQuestion()">Submit Answer</button></form>';
                    document.getElementById("optionsDiv").innerHTML=str;
                }
                else {
                    // mainImg will be hidden bc images will be present in options
                    document.getElementById("mainImg").style.display = "none";

                    // before displaying options first get the correct answer, then shuffle the options
                    let correctIndex = 0;
                    for(let i = 0; i < selectedQuestions[index]["rightAnswer"].length; i++) {
                        if(selectedQuestions[index]["rightAnswer"][i] !== true) {
                            correctIndex++;
                        }
                        else {
                            break;
                        }
                    }
                    correctAnswer = selectedQuestions[index]["options"][correctIndex];
                    selectedQuestions[index]["options"] = shuffle(selectedQuestions[index]["options"]);

                    // always display options
                    let optionsLength = selectedQuestions[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++) {
                        // some options have ` in them, remove them if found
                        if(selectedQuestions[index]["options"][i].includes("`")) {
                            selectedQuestions[index]["options"][i] = selectedQuestions[index]["options"][i].replaceAll("`", "");
                        }
                        if(i !== 2) {
                            str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + selectedQuestions[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + selectedQuestions[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + selectedQuestions[index]["options"][i] + '</label>
                        }
                        else {
                            str += '<br><input id="option' + i + '" type="radio" name="dynamic_option" value="' + selectedQuestions[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + selectedQuestions[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + selectedQuestions[index]["options"][i] + '</label>
                        }

                    }
                    str += '<br><button id="checkAnswerButton" type="button" onclick="checkQuestion()">Submit Answer</button></form>';
                    document.getElementById("optionsDiv").innerHTML = str;
                }

                // To use at the end to refresh the presentation of the equations to account for dynamic data
                MathJax.typeset();
            }


            // fully clears data from the necessary fields
            let clearData = () => {
                // clearing data from questionDisplay div (necessary because some questions might have more complete fields than others)
                document.getElementById("outcome").innerHTML = "";
                document.getElementById("text").innerHTML = "";
                document.getElementById("numTries").innerHTML = "";

                // if new image is empty
                if(selectedQuestions[index]["pic"] === "") {
                    document.getElementById("mainImg").src = "";
                    document.getElementById("mainImg").alt = "";
                    document.getElementById("mainImg").style.display = "none";
                }
                else {
                document.getElementById("mainImg").style.display = "";
                }

                // clearing label color that may have been assigned
                if(selectedQuestions[index]["isImage"][0] === false) {
                    let optionsLength = selectedQuestions[index]["options"].length;
                    for (let i = 0; i < optionsLength; i++) {
                        document.getElementById("label" + i).style.color = "";
                    }              
                }

                document.getElementById("optionsDiv").innerHTML = "";
            }


            let next = () =>{
                // making sure we are in legal index bound
                if(index !== totalQuestions - 1){
                    // clear previous question data
                    clearData();
  
                    // update index to go forward
                    index++;
                    // hide outcome element
                    document.getElementById("outcome").style.display = "none";
                    // display new question data
                    displayData();
                }
            }

            let prev = () =>{
                // making sure we are in legal index bound
                if(index !== 0){
                    // clear previous question data
                    clearData();
                    // update index to go back
                    index--;
                    // hide outcome element
                    document.getElementById("outcome").style.display = "none";
                    // display new question data
                    displayData();
                }
            }


            ////////////////////////////////
            // BACKGROUND FUNCTIONALITIES //
            ///////////////////////////////.
   
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