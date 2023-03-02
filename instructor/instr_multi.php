<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$instr_assessments = array();
$public_assessments = array();

// connect to the db
require_once "../register_login/config.php";

// first query - instructor's assessments that were created for the selected_course_name and selected_course_id
$query = "SELECT * FROM assessments WHERE instructor = '{$_SESSION["email"]}' AND course_name = '{$_SESSION['selected_course_name']}' 
          AND course_id = '{$_SESSION['selected_course_id']}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

while($row = pg_fetch_row($res)){
    $instr_assessments[$row[0]] = $row[2];
}

// second query - public assessments that do not belong to current logged in instructor, but are public
$query = "SELECT * FROM assessments WHERE instructor != '{$_SESSION["email"]}' AND public = 'Yes'";
// AND course_name = '{$_SESSION["selected_course_name"]}' AND course_id = '{$_SESSION["selected_course_id"]}'";
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

while($row = pg_fetch_row($res)){
    $public_assessments[$row[0]] = $row[2];
}

pg_close($con);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Assessments</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_multi.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
        <!--<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>-->
    </head>
    <body onload="loadJSON1();loadJSON2();displayAssessments();getChapterOptions();">
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

            <div>
                <h1><?= $_SESSION["selected_course_name"]; ?></h1>
            </div>

            <div id="assessments_div">
                <div id="instr_div"></div>
                <div id="public_div"></div>
            </div>

            <main id="main" style="display:none">

                <h1><u id="header">Assessment View</u></h1>

                <br>

                <div id="group1">
                    <div id="group1_1">
                        <div class="group1_1_1">
                            <label for="name"><strong>Name:</strong></label>
                            <input type="text" id="name" name="name" placeholder="Assessment #1" readonly required>
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a name for your assessment.">
                        </div>
                        <br><br>
                        <div class="group1_1_1">
                            <label><strong>Public:</strong></label>
                            <input type="radio" id="public_yes" name="public" value="Yes" disabled>
                            <label id="public_yes_label" for="public_yes">Yes</label>
                            <input type="radio" id="public_no" name="public" value="No" disabled>
                            <label id="public_no_label" for="public_no">No</label>
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Would you like your assessment to be accessible to other instructors/students?">
                        </div>
                        <br><br>
                        <div class="group1_1_1">
                            <label for="duration"><strong>Duration:</strong></label>
                            <input type="number" id="duration" name="duration" min="1" placeholder="30" readonly required>   
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a number (representing minutes), for a student to take the assessment.">                             
                        </div>
                        <!-- hidden input -->
                        <input type="number" id="num_of_selected_los" name="num_of_selected_los" value="" hidden readonly required>
                    </div>
                    <div id="group1_2">
                        <div class="group1_2_1">
                            <label for="open_date"><strong>Open Date:</strong></label>
                            <input type="date" id="open_date" name="open_date" readonly required>
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a date in which assessment will be open for students to take.">
                        </div>
                        <br><br>
                        <div class="group1_2_1">
                            <label for="open_time"><strong>Open Time:</strong></label>
                            <input type="time" id="open_time" name="open_time" readonly required>
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a time in which assessment will be open for students to take.">
                        </div>
                        <br><br>
                        <div class="group1_2_1">
                            <label for="close_date"><strong>Close Date:</strong></label>
                            <input type="date" id="close_date" name="close_date" readonly required>
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a date in which assessment will be closed for students.">
                        </div>
                        <br><br>
                        <div class="group1_2_1">
                            <label for="close_time"><strong>Close Time:</strong></label>
                            <input type="time" id="close_time" name="close_time" readonly required> 
                            <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                title="Input a time in which assessment will be closed for students."> 
                        </div>
                    </div>
                </div>

                <br><br>

                <div id="group2" style="display:none">
                    <p><strong>Please select at least 1 learning outcome to insert into the assessment:</strong></p>
                    <div id="group2_1">
                        <div id="group2_1_1">
                            <p><strong>Chapter</strong></p>
                            <select id="ch_select" onchange="getSectionOptions();getLoOptions();">
                                <option value="">Select a Chapter</option>
                            </select>
                        </div>
                        <div id="group2_1_2">
                            <p><strong>Section</strong></p>
                            <select id="sec_select" onchange="getLoOptions();">
                                <option value="">Select a Section</option>
                            </select>                                
                        </div>
                        <div id="group2_1_3">
                            <p><strong>Learning Outcome</strong></p>
                            <select id="lo_select" onchange="insertSelection();">
                                <option value="">Select a Learning Outcome</option>
                            </select>
                        </div>
                    </div>
                </div>

                <br><br>

                <div id="group3">
                    <table id="sel_table_res">
                        <thead>
                            <tr>
                                <th id="th1" scope="col">Chapter Name<br>Section Name<br>Learning Outcome Name</th>
                                <th id="th2" scope="col">Learning Outcome Number</th>
                                <th id="th3" scope="col">Questions Per Learning Outcome</th>
                                <th id="th4" scope="col">Points Per Question</th>
                                <th id="th5" scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="sel_table_res_body"></tbody>
                    </table>
                </div>

                <br>

                <div id="group4">
                    <p id="error_p" style="color:red"></p>
                    <button class="action_btn" onclick="editAssessment()">Edit Assessment</button>
                    <button class="action_btn" onclick="deleteAssessment()">Delete Assessment</button>
                    <br>
                    <button class="action_btn" onclick="viewResults()">View Student's Results</button>
                    <button class="action_btn" onclick="beginAssessmentProcess()">Preview Assessment</button>
                    <br>
                    <button class="action_btn" onclick="cancel()">Return</button>
                </div>

                <div id="group5" style="display:none">
                    <button id="submit_button" onclick="updateAssessment()">Update Assessment</button>
                    <br>
                    <button class="cancel_btn" onclick="cancel()">Cancel</button>
                </div>

                <br>

            </main>

            <br>

            <div id="students_results_div" style="display:none"></div>
            <div id="assessment-info-div" style="display:none"></div>
            <div id="content-div" style="display:none">
                <div id="controls-div">
                    <h2 id="questionCount"></h2>
                    <div id="ul-div">
                        <ul>
                            <li><b>Please note that the questions are displayed in order, from what is displayed on the table from top to bottom.</b></li>
                            <li><b>For example, row 1 might have learning outcome 1.2.3 & 2 questions total.</b></li>
                            <li><b>For example, row 2 might have learning outcome 1.2.4 & 3 questions total.</b></li>
                            <li><b>This means you will first see 2 questions of learning outcome 1.2.3, followed by 3 questions of learning outcome 1.2.4</b></li>
                        </ul>
                    </div>
                    <div id="buttons-div">
                        <button id="btn2" onclick="previous()">Previous Question</button>
                        <button id="btn2" onclick="next()">Next Question</button>
                    </div>
                    <div class="timer-div">
                        <h4 class="timer">Timer:</h4>
                        <h4 class="timer" id="minutes">00</h4> : <h4 class="timer" id="seconds">00</h4>
                    </div>
                </div>
            </div>

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
            /* GLOBALS */
            let row_num = 1;
            const instr_assessments = <?= json_encode($instr_assessments); ?>;
            const public_assessments = <?= json_encode($public_assessments); ?>;
            let assessment; // used to hold entire row from pgsql db
            let assessment_content; // used to hold content json for row

            let counter = 0; // used as index for each question
            let timerID; // holds the ID of the timer, used to stop the timer


            let displayAssessments = () => {
                // instructor assessments
                let str = '<h1>Your Assessments</h1>';
                str += '<table id="instr_table">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in instr_assessments) {
                    str += '<tr class="tr_ele" onclick="openAssessment('+key+')"><td>' + instr_assessments[key] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("instr_div").innerHTML = str;

                // public assessments
                str = '<h1>Public Assessments</h1>';
                str += '<table id="public_table">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in public_assessments) {
                    str += '<tr class="tr_ele" onclick="openAssessment('+key+')"><td>' + public_assessments[key] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("public_div").innerHTML = str;
            }


            // get and display, clicked on assessment data
            let req1;
            let openAssessment = (pkey) => {
                req1 = new XMLHttpRequest();
                req1.open('POST', 'get/assessment_data.php', true);
                req1.onreadystatechange = openAssessmentResponse;
                req1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req1.send("pkey="+pkey);
            }
            let openAssessmentResponse = () => {
                if(req1.readyState == 4 && req1.status == 200){
                    //console.log("PHP sent back: " + req1.responseText);

                    // parse the data
                    assessment = JSON.parse(req1.responseText);
                    assessment_content = JSON.parse(assessment[9]);

                    // unhide the main if it is hidden
                    if(document.getElementById("main").style.display === "none"){
                        document.getElementById("main").setAttribute('style', 'display:""');
                    }

                    // hide the assessments list display if not hidden
                    if(document.getElementById("assessments_div").style.display !== "none") {
                        document.getElementById("assessments_div").setAttribute('style', 'display:none');
                    }

                    // make all input fields not editable (initial view mode)
                    document.getElementById("name").readOnly = true;
                    document.getElementById("public_yes").disabled = true;
                    document.getElementById("public_no").disabled = true;
                    document.getElementById("duration").readOnly = true;
                    document.getElementById("open_date").readOnly = true;
                    document.getElementById("open_time").readOnly = true;
                    document.getElementById("close_date").readOnly = true;
                    document.getElementById("open_time").readOnly = true;

                    // unhide and hide key divs
                    document.getElementById("group2").setAttribute("style", 'display:none');
                    document.getElementById("group4").setAttribute("style", 'display:""');
                    document.getElementById("group5").setAttribute("style", 'display:none');

                    // reset header
                    document.getElementById("header").innerHTML = "Assessment View";

                    // reset row num
                    row_num = 1;

                    // reset dynamic table section
                    document.getElementById("sel_table_res_body").innerHTML = "";

                    // reset error field
                    document.getElementById("error_p").innerHTML = "";

                    // inserting data for the top static inputs
                    document.getElementById("name").value = assessment[2];
                    if(assessment[3] === "Yes") {
                        document.getElementById("public_yes").setAttribute('checked', '""');
                    } else {
                        document.getElementById("public_no").setAttribute('checked', '""');
                    }
                    document.getElementById("duration").value = assessment[4];
                    document.getElementById("open_date").value = assessment[5];
                    document.getElementById("open_time").value = assessment[6];
                    document.getElementById("close_date").value = assessment[7];
                    document.getElementById("close_time").value = assessment[8];
                    
                    // inserting rows into dynamic table
                    for(let i = 0; i < assessment_content.length; i++) {
                        // start the table row
                        let str = '<tr>';

                        // 1st td -> insert the ch, sec, lo name selected
                        str += '<td class="td1">';
                        str += '<span id="ch_name_' + row_num + '"></span><br>';
                        str += '<span id="sec_name_' + row_num + '"></span><br>';
                        str += '<span id="lo_name_' + row_num + '"></span>';
                        str += '</td>';

                        // 2nd td -> insert input for the numerical lo
                        str += '<td class="td2">';
                        str += '<input id="lonum_' + row_num + '" name="lonum_' + row_num + '" class="lonum_inputs" type="text" ';
                        str += 'value="' + assessment_content[i].LearningOutcomeNumber + '" readonly required>';
                        str += '</td>';

                        // 3rd td -> insert input for number of questions
                        str += '<td class="td3">';
                        str += '<input id="questions_' + row_num + '" name="questions_' + row_num + '" class="questions_inputs" type="number" max="' + dynamic_json[assessment_content[i].LearningOutcomeNumber] + '" ';
                        str += 'placeholder="0 - ' + dynamic_json[assessment_content[i].LearningOutcomeNumber] + '" value="' + assessment_content[i].NumberQuestions + '" readonly required>';
                        str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of assigned learning outcome questions for this assessment.">';
                        str += '</td>';

                        // 4th td -> insert input for number of points
                        str += '<td class="td4">';
                        str += '<input id="points_' + row_num + '" name="points_' + row_num + '" class="points_inputs" type="number" ';
                        str += 'placeholder="1" value="' + assessment_content[i].NumberPoints + '" readonly required>';
                        str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of points assigned per question for this selected learning outcome.">';
                        str += '</td>';

                        // 5th td -> insert delete button
                        str += '<td class="td5">';
                        str += '<button id="delete_button_' + row_num + '" class="delete_button">x</button>';
                        str += '</td>';

                        // complete the table row
                        str += "</tr>";

                        // append table row before the end of the table body
                        document.getElementById("sel_table_res_body").insertAdjacentHTML("beforeend", str);

                        // send updated row_num value to hidden input in form
                        document.getElementById("num_of_selected_los").value = row_num;

                        // update
                        row_num++;
                    }

                    // since chapter name, section name, lo name data was not stored, we will retrieve it using the 
                    // stored lo number, to then display on the respective dynamic table row
                    displayNames();

                }
            }


            // function to display names of ch, sec, lo 
            let displayNames = () => {
                for(let i = 0; i < assessment_content.length; i++){
                    // chapters
                    let idx = assessment_content[i].LearningOutcomeNumber.indexOf(".");
                    let ch = assessment_content[i].LearningOutcomeNumber.slice(0, idx);
                    document.getElementById("ch_name_"+(i+1)).innerHTML = ch + ". " + json_data_1[ch];
                    // sections
                    let idx1 = assessment_content[i].LearningOutcomeNumber.indexOf(".");
                    ch = assessment_content[i].LearningOutcomeNumber.slice(0, idx1);
                    let idx2 = assessment_content[i].LearningOutcomeNumber.indexOf(".", idx1 + 1);
                    let sec = assessment_content[i].LearningOutcomeNumber.slice(idx1 + 1, idx2);
                    document.getElementById("sec_name_"+(i+1)).innerHTML = ch + "." + sec + ". " + json_data_1[ch+"."+sec];
                    // los 
                    document.getElementById("lo_name_"+(i+1)).innerHTML = assessment_content[i].LearningOutcomeNumber + ". " + json_data_1[assessment_content[i].LearningOutcomeNumber];
                }
            } 


            // function to get the complete selected chapter name (ex: 1. Functions)
            let getFullChapter = () =>{
                let select = document.getElementById("ch_select");
                return select.options[select.selectedIndex].text;
            }
            // function to get the complete selected section name (ex: 1.1. Functions and Function)
            let getFullSection = () =>{
                let select = document.getElementById("sec_select");
                return select.options[select.selectedIndex].text;
            }
            // function to get the complete selected learning outcome name
            // (ex: 1.1.1. Determining Whether a Relation Represents a Function)
            let getFullLearningOutcome = () =>{
                let select = document.getElementById("lo_select");
                return select.options[select.selectedIndex].text;
            }


            // function to insert a row of new data into the table
            let insertSelection = () =>{
                // start the table row
                let str = '<tr>';

                // 1st td -> insert the ch, sec, lo name selected
                str += '<td class="td1">';
                str += getFullChapter() + '<br>';
                str += getFullSection() + '<br>';
                str += getFullLearningOutcome()
                str += '</td>';
    
                // 2nd td -> insert input for the numerical lo
                str += '<td class="td2">';
                str += '<input id="lonum_' + row_num + '" name="lonum_' + row_num + '" class="lonum_inputs" type="text" ';
                str += 'value="' + getCompleteTag() + '" readonly required>';
                str += '</td>';

                // 3rd td -> insert input for number of questions
                str += '<td class="td3">';
                str += '<input id="questions_' + row_num + '" name="questions_' + row_num + '" class="questions_inputs" type="number" ';
                str += 'max="' + dynamic_json[getCompleteTag()]  + '" placeholder="0 - ' + dynamic_json[getCompleteTag()] + '" required>';
                str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of assigned learning outcome questions for this assessment.">';
                str += '</td>';

                // 4th td -> insert input for number of points
                str += '<td class="td4">';
                str += '<input id="points_' + row_num + '" name="points_' + row_num + '" class="points_inputs" type="number" placeholder="1" required>';
                str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of points assigned per question for this selected learning outcome.">';
                str += '</td>';

                // 5th td -> insert delete button
                str += '<td class="td5">';
                str += '<button id="delete_button_' + row_num + '" class="delete_button" onclick="deleteRow(this.parentElement.parentElement);">x</button>';
                str += '</td>';

                // complete the table row
                str += "</tr>";

                // append table row before the end of the table body
                document.getElementById("sel_table_res_body").insertAdjacentHTML("beforeend", str);

                // send updated row_num value to hidden input
                document.getElementById("num_of_selected_los").value = row_num;

                // update row num
                row_num++;
            }


            // function to delete actual row from the table
            let deleteRow = (ele) =>{
                // get the parent before deleting
                let parent = ele.parentElement;
                // delete the table row
                ele.remove();
                // update the total number of rows
                row_num--;
                // send row num to hidden input
                document.getElementById("num_of_selected_los").value = row_num - 1;
                // now update the table's ids
                renameInputIds(parent);
            }
            // function to loop through remaining table rows in table (if possible) and rename 
            // attributes of input 'lonum_', 'questions_', and 'points_'
            let renameInputIds = (ele) =>{
                // counter for modifying id value
                let count = 1;

                // grab every child element of the <tbody> element (list of <tr> elements)
                let tableRowList = ele.children;
                //console.log(tableRowList);

                // loop through each <tr> element
                for(let i = 0; i < tableRowList.length; i++){

                    // grab every child element of the <tr> element (list of <td> elements)
                    let tableDataList = tableRowList[i].children;
                    //console.log(tableDataList);

                    // loop through select <td> elements
                    for(let j = 1; j < 5; j++){

                        // grab every child element of the <td> element (list of <input> elements)
                        let inputList = tableDataList[j].children;
                        //console.log(inputList);

                        // loop through each <input> element (should just be 1)
                        for(let k = 0; k < inputList.length; k++){
                            // grab input instance
                            let input = inputList[k];
                            // grab input id value
                            let id = input.id;
                            //console.log(id);
                            // modifying id value
                            let idx1 = id.indexOf("_");
                            id = id.slice(0, idx1 + 1);
                            id = id + count;
                            //console.log(id);
                            // setting input element attributes
                            input.setAttribute("id", id);
                            input.setAttribute("name", id);
                        }
                    }
                    // update count
                    count++;
                }
            }


            // enabling edit mode
            let editAssessment = () => {
                // check that current displayed assessment belongs to current logged-in instructor
                if(assessment[1] === "<?= $_SESSION["email"]; ?>") {
                    // change header
                    document.getElementById("header").innerHTML = "Assessment Edit";

                    // make all input fields editable
                    document.getElementById("name").readOnly = false;
                    document.getElementById("public_yes").disabled = false;
                    document.getElementById("public_no").disabled = false;
                    document.getElementById("duration").readOnly = false;
                    document.getElementById("open_date").readOnly = false;
                    document.getElementById("open_time").readOnly = false;
                    document.getElementById("close_date").readOnly = false;
                    document.getElementById("open_time").readOnly = false;
                    for(let i = 1; i < row_num; i++){
                        document.getElementById(`questions_${i}`).readOnly = false;
                        document.getElementById(`points_${i}`).readOnly = false;
                        // add button functionality
                        document.getElementById(`delete_button_${i}`).setAttribute("onclick", "deleteRow(this.parentElement.parentElement);");
                    }

                    // unhide and hide key elements
                    document.getElementById("group2").setAttribute("style", 'display:""');
                    document.getElementById("group4").setAttribute("style", 'display:none');
                    document.getElementById("group5").setAttribute("style", 'display:""');

                }
                else {
                    document.getElementById("error_p").innerHTML = "You do not own this assessment.";
                }
            }


            /* UPDATING ASSESSMENT */
            let req3;
            let updateAssessment = () => {
                // XMLHttpRequest
                req3 = new XMLHttpRequest();
                req3.open('POST', 'pgsql/update_assessment.php', true);
                req3.onreadystatechange = updateAssessmentResponse;
                req3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                // grabbing public data
                let ele = document.getElementsByName('public');
                let public;
                for(let i = 0; i < ele.length; i++) {
                    if(ele[i].checked) {
                        public = ele[i].value;
                    }
                }
                // grabbing dynamic questions and points data
                let los = [];
                let questions = [];
                let points = [];
                for(let i = 1; i < row_num; i++) {
                    los.push(document.getElementById(`lonum_${i}`).value);
                    questions.push(document.getElementById(`questions_${i}`).value);
                    points.push(document.getElementById(`points_${i}`).value);
                }
                // send all data
                req3.send("pkey=" + assessment[0] + "&name=" + document.getElementById("name").value + "&public=" + public +
                          "&duration=" + document.getElementById("duration").value + "&open_date=" + document.getElementById("open_date").value +
                          "&open_time=" + document.getElementById("open_time").value + "&close_date=" + document.getElementById("close_date").value + 
                          "&close_time=" + document.getElementById("close_time").value + "&questions=" + JSON.stringify(questions) + "&points=" + JSON.stringify(points) +
                          "&num_of_selected_los=" + document.getElementById("num_of_selected_los").value + "&los=" + JSON.stringify(los));
            }
            let updateAssessmentResponse = () => {
                if(req3.readyState == 4 && req3.status == 200) {
                    alert(req3.responseText);
                    window.location.reload();
                }
            }


            /* DELETING ASSESSMENT */
            let req2;
            let deleteAssessmentXML = () => {
                // XMLHttpRequest
                req2 = new XMLHttpRequest();
                req2.open('POST', 'pgsql/delete_assessment.php', true);
                req2.onreadystatechange = deleteAssessmentResponse;
                req2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req2.send("pkey="+assessment[0]);
            }
            let deleteAssessmentResponse = () => {
                if(req2.readyState == 4 && req2.status == 200){
                    alert(req2.responseText);
                    window.location.reload();
                }
            }
            let deleteAssessment = () => {
                // check that current displayed assessment belongs to current logged-in instructor
                if(assessment[1] === "<?= $_SESSION["email"]; ?>") {
                    deleteAssessmentXML();
                }
                else {
                    document.getElementById("error_p").innerHTML = "You do not own this assessment.";
                }
            }


            /* GET AND DISPLAY STUDENT ASSESSMENT RESULTS */
            let req4;
            let resultsObj;
            let viewResults = () => {
                // check that current displayed assessment belongs to current logged-in instructor
                if(assessment[1] === "<?= $_SESSION["email"]; ?>") {
                    // XMLHttpRequest
                    req4 = new XMLHttpRequest();
                    req4.open('POST', 'pgsql/get_assessment_results.php', true);
                    req4.onreadystatechange = viewResultsResponse;
                    req4.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    req4.send(`assessment_name=${assessment[2]}`);
                }
                else {
                    document.getElementById("error_p").innerHTML = "You do not own this assessment.";
                }
            }
            let viewResultsResponse = () => {
                if (req4.readyState == 4 && req4.status == 200) {
                    // receive response
                    //console.log(req4.responseText);
                    resultsObj = JSON.parse(req4.responseText);

                    let str;

                    // if no one has completed the assessment yet
                    if (resultsObj.length === 0) {
                        str = '<h1>No Student has Completed the Assessment</h1>';

                    }
                    // at least 1 student has completed the assessment
                    else {
                        // creating student results table to display data
                        str = '<h1>Student Results</h1>';
                        str += '<table id="student_results_table">';
                        str += '<thead><tr>';
                        str += '<th class="th_res_1" scope="col">Name</th>';
                        str += '<th class="th_res_2" scope="col">Email</th>';
                        str += '<th class="th_res_3" scope="col">Score</th>';
                        str += '<th class="th_res_4" scope="col">Score Breakdown</th>';
                        str += '<th class="th_res_5" scope="col">Date & Time Submitted</th>';
                        str += '</tr></thead>';
                        str += '<tbody>';
                        // loop through the array of objects
                        resultsObj.forEach(function(element) {
                            str += '<tr>';
                            str += `<td class="td_res_1">${element.student_name}</td>`;
                            str += `<td class="td_res_2">${element.student_email}</td>`;
                            str += `<td class="td_res_3" title="${Math.round((element.score / element.max_score) * 100)}%"> ${element.score} / ${element.max_score} </td>`;
                            str += '<td class="td_res_4">';

                            str += '<div id="score-breakdown-div">';
                            const content = JSON.parse(element.content);
                            for (let i = 0; i < content.length; i++) {
                                str += '<div class="score-breakdown-inner-div">';
                                str += `<a href="https://imathas.libretexts.org/imathas/embedq2.php?id=${content[i].id}" target="_blank">`;
                                str += `LO: ${content[i].lo} &nbsp; Result: ${content[i].result} &nbsp; Max Score: ${content[i].max_score}`;
                                str += '</a>';
                                str += '</div>';
                            }
                            str += '</div>'

                            str += '</td>';
                            str += `<td class="td_res_5">${element.date_time_submitted}</td></tr>`;
                        });

                        str += '</tbody>'
                        str += '</table>';
                    }

                    // display data
                    document.getElementById("students_results_div").innerHTML = str;
                    document.getElementById("students_results_div").style.display = "";
                }
            }


            /* GET AND DISPLAY STUDENT ASSESSMENT RESULTS */
            let req5;
            let dynamic_ids;
            const sequence_question = []; // list of problem numbers fed to the imathas
            let beginAssessmentProcess = () => {
                // check that current displayed assessment belongs to current logged-in instructor
                if(assessment[1] === "<?= $_SESSION["email"]; ?>") {
                    let str = `<h1><u>${assessment[2]}</u></h1>`;
                    str += '<div id="ul-list-div">';
                    str += '<ul>';
                    str += `<li><h3>You have a total duration of ${assessment[4]} minutes to complete this assessment.</h3></li>`;
                    str += `<li><h3>This assessment closes on: ${assessment[7]} ${assessment[8]}</h3></li>`;
                    str += '<li><h3>The table below contains the learning outcomes that you will be assessed on. As well as the break-down on number of questions and points per question for each learning outcome.</h3></li>';
                    str += '</ul>';
                    str += '</div>';
                    str += '<div id="assessment-info-lo"></div>';
                    str += '<h3>To preview the test, click on the \'Preview\' button.</h3>';
                    str += '<button id="btn1" onclick="previewAssessment()">Preview</button>';
                    document.getElementById("assessment-info-div").innerHTML = str;
                    document.getElementById("assessment-info-div").style.display = "";

                    getDynamicIds();
                }
                else {
                    document.getElementById("error_p").innerHTML = "You do not own this assessment.";
                }
            }
            let getDynamicIds = () => {
                    req5 = new XMLHttpRequest();
                    req5.open('POST', 'pgsql/dynamic_ids.php', true);
                    req5.onreadystatechange = getDynamicIdsResponse;
                    req5.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    req5.send(`assessment_json=${JSON.stringify(assessment_content)}`);
            }
            let getDynamicIdsResponse = () => {
                if (req5.readyState == 4 && req5.status == 200) {
                    //console.log(req5.responseText);
                    dynamic_ids = JSON.parse(req5.responseText);
                    //console.log(dynamic_ids);
                    initListQuestions();
                    displayLoInfo();
                    buildiFrame();
                }
            }
            let initListQuestions = () => {
                // loop through each key value pair in dynamic_ids
                // each key represents a learning outcome
                // each value contains an array containing the numPoints value, and ids of each question for the lo
                for (const [key, value] of Object.entries(dynamic_ids)) {
                    // loop through each id in value arr
                    for (let i = 1; i < value.length; i++) {
                        // push just the id into sequence_question (arr of only the ids)
                        sequence_question.push(value[i]);
                    }
                }
            }
            const displayLoInfo = () => {
                let str = '<table id="assessment-preview-tbl"><thead><tr>';
                str += '<th scope="col">Chapter<br>Section<br>Learning Outcome</th>';
                str += '<th scope="col">Number of Questions</th>';
                str += '<th scope="col">Points per Question</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                let sums = [0, 0];
                assessment_content.forEach(function(arrItem) {
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
                    str += `${ch}. ` + json_data_1[ch] + '<br>';
                    str += `${ch}.${sec}. ` + json_data_1[ch + "." + sec] + '<br>';
                    str += `${arrItem.LearningOutcomeNumber}. ${json_data_1[arrItem.LearningOutcomeNumber]}`;
                    str += '</td>';
                    str += `<td>${arrItem.NumberQuestions}</td>`;
                    str += `<td>${arrItem.NumberPoints}</td>`;
                    str += '<tr>';
                });
                str += '</tbody>';
                str += '<tfoot><tr>';
                str += '<td><b>Totals</b></td>';
                str += `<td>${sums[0]}</td>`;
                str += `<td>${sums[1]}</td>`;
                str += '</tr></tfoot>';
                str += '</table>';
                str += `<h3 id="q-info-count">This assessment contains a total of ${sums[0]} questions and ${sums[1]} points possible.</h3>`;
                document.getElementById("assessment-info-lo").innerHTML = str;
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
            let previewAssessment = () => {
                // start timer
                timerID = startTimer();	
                // clear and hide assessment-info display
                document.getElementById("assessment-info-div").innerHTML= "";
                document.getElementById("assessment-info-div").style.display = "none";
                // unhide next btn & iframe
                document.getElementById("content-div").style.display = "";
                // display question number
                document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
            }

            const previous = () => {
                // making sure we are in the valid range and that the question has been answered before moving on
                if (counter > 0) {
                    // update counter
                    counter--;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + sequence_question[counter]);
                    // update question number
                    document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
                }
            }
            const next = () => {
                // making sure we are in the valid range and that the question has been answered before moving on
                if (counter + 1 < sequence_question.length) {
                    // update counter
                    counter++;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + sequence_question[counter]);
                    // update question number
                    document.getElementById("questionCount").innerHTML = `Question ${counter + 1} / ${sequence_question.length}`;
                }
            }


            // cancelling action
            let cancel = () => {
                window.location.reload();
            }


            // function to get just the numerical value of the chapter (ex: 1)
            let getChapterNumber = () =>{
                let select = document.getElementById("ch_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get just the numerical value of the section (ex: 2)
            let getSectionNumber = () =>{
                let select = document.getElementById("sec_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get just the numerical value of the learning outcome (ex: 3)
            let getLearningOutcomeNumber = () =>{
                let select = document.getElementById("lo_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get a complete learning outcome (ex: 1.2.3)
            let getCompleteTag = () =>{
                return getChapterNumber() + "." + getSectionNumber() + "." + getLearningOutcomeNumber();
            }


            // obtaining the complete list of chapters (on page load)
            // then adding the data to the ch_select dropdown element
            let ch_options_req;              
            let ch_options_obj;            
            let getChapterOptions = () =>{
                ch_options_req = new XMLHttpRequest();
                ch_options_req.open('POST', 'get/ch_names_1.php', true);
                ch_options_req.onreadystatechange = getChapterOptionsResponse;
                ch_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_options_req.send();
            }
            let getChapterOptionsResponse = () =>{
                if (ch_options_req.readyState == 4 && ch_options_req.status == 200) {
                    //console.log("PHP sent back: " + ch_options_req.responseText);
                    ch_options_obj = JSON.parse(ch_options_req.responseText);
                    // now display the chapter options
                    let str = '<option value="">Select a Chapter</option>';
                    let i = 2;
                    for(const [key, value] of Object.entries(ch_options_obj)){
                        str += '<option value="' + key + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("ch_select").innerHTML = str;
                }
            }   

            // obtaining the complete list of sections (given a selected chapter)
            // then adding the data to the sec_select dropdown element
            let sec_options_req;
            let sec_options_obj;
            let getSectionOptions = () =>{
                // we need to grab the options from the PGSQL DB
                sec_options_req = new XMLHttpRequest();
                sec_options_req.open('POST', 'get/sec_names_1.php', true);
                sec_options_req.onreadystatechange = getSectionOptionsResponse;
                sec_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                sec_options_req.send("chapter=" + getChapterNumber());
            }
            let getSectionOptionsResponse = () =>{
                if (sec_options_req.readyState == 4 && sec_options_req.status == 200) {
                    //console.log("PHP sent back: " + sec_options_req.responseText);
                    sec_options_obj = JSON.parse(sec_options_req.responseText);
                    // now display the section options
                    let str = '<option value="">Select a Section</option>';
                    let i = 2;
                    for(const [key, value] of Object.entries(sec_options_obj)){
                        let index = key.indexOf('.');
                        let sec_num = key.slice(index + 1, key.length);
                        str += '<option value="' + sec_num + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("sec_select").innerHTML = str;
                }
            }   

            // obtaining the complete list of learning outcomes (given a selected chapter and selected section)
            // then adding the data to the lo_select dropdown element
            let lo_options_req;
            let lo_options_obj;
            let getLoOptions = () =>{
                // we need to grab the options from the PGSQL DB
                lo_options_req = new XMLHttpRequest();
                lo_options_req.open('POST', 'get/lo_names_1.php', true);
                lo_options_req.onreadystatechange = getLoOptionsResponse;
                lo_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                lo_options_req.send("chapter=" + getChapterNumber() + "&section=" + getSectionNumber());
            }
            let getLoOptionsResponse = () =>{
                if (lo_options_req.readyState == 4 && lo_options_req.status == 200) {
                    //console.log("PHP sent back: " + lo_options_req.responseText);
                    lo_options_obj = JSON.parse(lo_options_req.responseText);
                    // now display the lo options
                    let str = '<option value="">Select a Learning Outcome</option>';
                    i = 2;
                    for(const [key, value] of Object.entries(lo_options_obj)){
                        let pos1 = key.indexOf('.');
                        let pos2 = key.indexOf('.', pos1 + 1);
                        let lo_num = key.slice(pos2 + 1, key.length);
                        str += '<option value="' + lo_num + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("lo_select").innerHTML = str;
                }
            }


            // loading data.json file as js obj
            let load_json_req_1;
            let json_data_1;
            let loadJSON1 = () => {
                load_json_req_1 = new XMLHttpRequest();
                load_json_req_1.onreadystatechange = function() {
                    if(load_json_req_1.readyState == 4 && load_json_req_1.status == 200){
                        json_data_1 = JSON.parse(load_json_req_1.responseText);
                        //console.log(dynamic_json);
                        //console.log(dynamic_json["1.2.3"]);
                    }
                }
                load_json_req_1.open("GET", "get/data.json", true);
                load_json_req_1.send();  
            }

            // loading dynamic info to variable on page load
            let load_json_req_2;
            let dynamic_json; // holds assoc arr of lo => num of lo ("1.2.3" => 15)
            let loadJSON2 = () => {
                load_json_req_2 = new XMLHttpRequest();
                load_json_req_2.onreadystatechange = function() {
                    if(load_json_req_2.readyState == 4 && load_json_req_2.status == 200){
                        dynamic_json = JSON.parse(load_json_req_2.responseText);
                        //console.log(dynamic_json);
                        //console.log(dynamic_json["1.2.3"]);
                    }
                }
                load_json_req_2.open("GET", "get/dynamic.json", true);
                load_json_req_2.send();  
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