<?php   
/*
    This PHP script will first connect to the PostgreSQL database and then check if the accordingly
    HTML form using POST was submitted. If so, email, password, and confirmation password will go
    through validation using PHP and the validation basics on the HTML form as well. If all user input
    values are valid, it will hash their password using BCRYPT and then store all their input data into
    the users table. Finally, they will be redirected to a login page, where they can use their newly
    created credentials.
*/

// connect to the DB
require_once "config.php";

// global variables and init with empty values
$fname = $lname = "";
$email = $password = $confirm_password = "";
$email_err = $password_err = $confirm_password_err = "";
$instructor_email = "";
$instructor_secret_code = "";
$INSTRUCTOR_SECRET_CODE = "CSUF_559_Math";
$instructor_secret_code_err = "";
$type = "Select one";

// processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // if register came from a student
    if(isset($_POST['instructor_email'])){

        // assigning global PHP vars, no validation assigned yet 
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];
        $type = $_POST['type'];
        $instructor_email = $_POST['instructor_email'];

        /* EMAIL VALIDATION */
        if(empty(trim($_POST["email"]))){
            // email is empty
            $email_err = "Please enter an email address.";
        } 
        else{
            // if is valid email input so far, then check to see if email already exists in the db
            $query = "SELECT email FROM users WHERE email = '" . pg_escape_string(trim($_POST['email'])) . "'";
            $result = pg_query($con, $query) or die("Cannot execute query: $query \n");

            if(pg_num_rows($result) >= 1){
                $email_err = "This email is already taken.";
            }
            else{
                // email is valid, assign global PHP var $email
                $email = pg_escape_string(trim($_POST['email']));
            }
        }

        /* PASSWORD VALIDATION */
        if(empty(trim($_POST["pwd"]))){
            // password is empty
            $password_err = "Please enter a password.";     
        } 
        elseif((strlen(trim($_POST["pwd"])) < 6) || (strlen(trim($_POST["pwd"])) > 15)){
            // password is less than 6 characters or greater than 15 characters
            $password_err = "Password must have at least 6 characters and no more than 15 characters.";
        } 
        else{
            // password is valid, assign global php $password
            $password = trim($_POST["pwd"]);
        }
        
        /* CONFIRM PASSWORD VALIDATION */
        if(empty(trim($_POST["confirm_pwd"]))){
            // confirm password is empty
            $confirm_password_err = "Please confirm password.";     
        } 
        else{
            // no initial password errors and the passwords match => valid confirmation password & valid password
            if(empty($password_err) && ($password == trim($_POST["confirm_pwd"]))){
                $confirm_password = trim($_POST["confirm_pwd"]);
            }
            else{
                // error with first password input
                if(!empty($password_err)){
                    $confirm_password_err = "Fix first password input.";
                }
                // confirm password did not match the first password input
                elseif($password != trim($_POST["confirm_pwd"])){
                    $confirm_password_err = "Passwords do not match.";
                }
            }
        }

        /* CHECK USER INPUT ERRORS BEFORE SUBMIT TO DB */
        if(empty($email_err) && empty($password_err) && empty($confirm_password_err)){

            // hash the user input password using the current default algorithm (BCRYPT)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // create timestamp to be inserted for created_on attribute
            $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
            $timestamp = $date->format('Y-m-d H:i:s');
        
            $query = "INSERT INTO users(first_name, last_name, email, password, type, instructor, created_on) VALUES ('" . $fname . "','" . $lname . "','" . $email . "','" . $hashed_password . "','" . $type . "','" . $instructor_email ."','" . $timestamp . "')";
            $result = pg_query($con, $query) or die("Cannot execute query: $query \n");

            // the query was successful
            if($result){

                // we need to create each user their own copy of the JSON questions in the database
                // selecting all rows in the questions database
                $query = "SELECT * FROM questions"; 
                $rs = pg_query($con, $query) or die("Cannot execute query: $query\n");

                $result = pg_query($con, $query);
                $rows = pg_num_rows($result);
                //echo $rows . " row(s) returned.\n";

                // Begin writing to personalized file
                $myfile = fopen("/Applications/MAMP/htdocs/hub/user_data/questions/" . $email . ".json", "w") or die("Unable to open file!");

                fwrite($myfile, "[\n");

                // loop to write to file
                $counter = 1;
                while ($row = pg_fetch_row($rs)) {

                    /* OPTIONS DATA MODIFICATIONS */
                    // first remove { from options string $row[5]
                    $row[5] = substr($row[5], 1);
                    // then remove } from options string $row[5]
                    $row[5] = substr($row[5], 0, -1);
                    // then remove all double quotes from options string $row[5]
                    $row[5] = str_replace('"', '', $row[5]);
                    // convert options string $row[5] => to an array (based on commas)
                    $options_arr = explode(",", $row[5]);
                    // get options_arr length
                    $options_length = count($options_arr);
                    /* END OPTIONS DATA MODIFICATIONS */

                    // rightAnswer array modification
                    $row[6] = str_replace('{', '[', $row[6]);
                    $row[6] = str_replace('}', ']', $row[6]);

                    // isImage array modification
                    $row[7] = str_replace('{', '[', $row[7]);
                    $row[7] = str_replace('}', ']', $row[7]);

                    if($counter == $rows){
                        // no comma, because it is the last math question
                        $db_string = "{\n\"pkey\": $row[0], \n\"title\": \"$row[1]\", \n\"text\": \"$row[2]\", \n\"pic\": \"$row[3]\", \n\"numTries\": \"$row[4]\", \n\"options\": [";
                            
                        // insert each option into $db_string
                        for($i = 0; $i < $options_length; $i++){
                            if($i == $options_length - 1){
                                $db_string .= "\"$options_arr[$i]\"], ";
                            }
                            else{
                                $db_string .= "\"$options_arr[$i]\",";
                            }
                        }
                            
                        $db_string .= "\n\"rightAnswer\": $row[6], \n\"isImage\": $row[7], \n\"tags\": \"$row[8]\", \n\"difficulty\": \"$row[9]\", \n\"selected\": \"$row[10]\", \n\"numCurrentTries\": \"$row[11]\", \n\"correct\": \"$row[12]\", \n\"datetime_started\": \"$row[13]\", \n\"datetime_answered\": \"$row[14]\", \n\"createdOn\": \"$row[15]\"\n}\n";
        
                        // replacing the commas back in the options array
                        $db_string = str_replace('*%', ',', $db_string);
        
                        fwrite($myfile, $db_string);
                    }
                    else{
                        // normal write
                        $db_string = "{\n\"pkey\": $row[0], \n\"title\": \"$row[1]\", \n\"text\": \"$row[2]\", \n\"pic\": \"$row[3]\", \n\"numTries\": \"$row[4]\", \n\"options\": [";
                            
                        // insert each option into $db_string
                        for($i = 0; $i < $options_length; $i++){
                            if($i == $options_length - 1){
                                $db_string .= "\"$options_arr[$i]\"], ";
                            }
                            else{
                                $db_string .= "\"$options_arr[$i]\",";
                            }
                        }
                            
                        $db_string .= "\n\"rightAnswer\": $row[6], \n\"isImage\": $row[7], \n\"tags\": \"$row[8]\", \n\"difficulty\": \"$row[9]\", \n\"selected\": \"$row[10]\", \n\"numCurrentTries\": \"$row[11]\", \n\"correct\": \"$row[12]\", \n\"datetime_started\": \"$row[13]\", \n\"datetime_answered\": \"$row[14]\", \n\"createdOn\": \"$row[15]\"\n},\n";
        
                        // replacing the commas back in the options array
                        $db_string = str_replace('*%', ',', $db_string);
        
                        fwrite($myfile, $db_string);
                    }
        
                    $counter++;
                }

                fwrite($myfile, "]\n");

                fclose($myfile);

                echo "Successfully wrote to file.";

                // redirect to login page
                header("location: login.php");

            }
            else{
                // notify user of error
                echo '<script> alert("Oops! Something went wrong. Please try again later."); </script>';
            }
            
        }
    }
    // register came from an instructor
    else{
        // assigning global PHP vars, no validation assigned yet 
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];
        $type = $_POST['type'];

        /* EMAIL VALIDATION */
        if(empty(trim($_POST["email"]))){
            // email is empty
            $email_err = "Please enter an email address.";
        } 
        else{
            // if is valid email input so far, then check to see if email already exists in the db
            $query = "SELECT email FROM users WHERE email = '" . pg_escape_string(trim($_POST['email'])) . "'";
            $result = pg_query($con, $query) or die("Cannot execute query: $query \n");

            if(pg_num_rows($result) >= 1){
                $email_err = "This email is already taken.";
            }
            else{
                // email is valid, assign global PHP var $email
                $email = pg_escape_string(trim($_POST['email']));
            }
        }

        /* PASSWORD VALIDATION */
        if(empty(trim($_POST["pwd"]))){
            // password is empty
            $password_err = "Please enter a password.";     
        } 
        elseif((strlen(trim($_POST["pwd"])) < 6) || (strlen(trim($_POST["pwd"])) > 15)){
            // password is less than 6 characters or greater than 15 characters
            $password_err = "Password must have at least 6 characters and no more than 15 characters.";
        } 
        else{
            // password is valid, assign global php $password
            $password = trim($_POST["pwd"]);
        }
        
        /* CONFIRM PASSWORD VALIDATION */
        if(empty(trim($_POST["confirm_pwd"]))){
            // confirm password is empty
            $confirm_password_err = "Please confirm password.";     
        } 
        else{
            // no initial password errors and the passwords match => valid confirmation password & valid password
            if(empty($password_err) && ($password == trim($_POST["confirm_pwd"]))){
                $confirm_password = trim($_POST["confirm_pwd"]);
            }
            else{
                // error with first password input
                if(!empty($password_err)){
                    $confirm_password_err = "Fix first password input.";
                }
                // confirm password did not match the first password input
                elseif($password != trim($_POST["confirm_pwd"])){
                    $confirm_password_err = "Passwords do not match.";
                }
            }
        }

        /* INSTRUCTOR SECRET CODE VALIDATION */
        if(empty(trim($_POST['instructor_secret_code']))){
            $instructor_secret_code_err = "Please enter the instructor secret code.";
        }
        if(trim($_POST['instructor_secret_code']) != $INSTRUCTOR_SECRET_CODE){
            $instructor_secret_code_err = "That is not the secret code!";
        }
        else{
            $instructor_secret_code = $_POST['instructor_secret_code'];
        }

        /* CHECK USER INPUT ERRORS BEFORE SUBMIT TO DB */
        if(empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($instructor_secret_code_err)){

            // hash the user input password using the current default algorithm (BCRYPT)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // create timestamp to be inserted for created_on attribute
            $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
            $timestamp = $date->format('Y-m-d H:i:s');
        
            $query = "INSERT INTO users(first_name, last_name, email, password, type, instructor, created_on) VALUES ('" . $fname . "','" . $lname . "','" . $email . "','" . $hashed_password . "','" . $type . "','" . $instructor_email ."','" . $timestamp . "')";
            $result = pg_query($con, $query) or die("Cannot execute query: $query \n");

            // the query was successful
            if($result){

                // redirect to login page
                header("location: login.php");

            }
            else{
                // notify user of error
                echo '<script> alert("Oops! Something went wrong. Please try again later."); </script>';
            }
        }
    }
}
//echo "Closing connection to PostgreSQL database.\n";
pg_close($con);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Register Page</title>
        <meta charset="UTF-8">
        <style>
            .container{
                margin: 0 auto;
                text-align: center;
                border: solid 6px navy;
                width: 35%;
                height: auto; /*700px*/
                padding-top: 20px;
                margin-top: 50px;
            }
            #image_header{
                width: 237.6px;
                height: 120px;
                padding: 15px;
            }
            .form-group{
                margin: 25px;
            }
            .input-error{
                color: red;
                font-size: 16px;
                font-weight: 600;
            }
            #firstName_label, #lastName_label, #email_label, #pwd_label, #confirm_pwd_label, #type_label, #instructor_email_label, #instructor_secret_code_label{
                color: navy;
                font-size: 16px;
                font-weight: 600;
                text-align: left;
            }
            #firstName, #lastName, #email, #pwd, #confirm_pwd, #type, #instructor_email, #instructor_secret_code{
                background-color: #F3FFFF;
                border: 1px solid navy;
                width: 250px;
                height: 30px;
                font-size: 14px;
            }
            input[type="submit"]{
                transition-duration: 0.5s;
                width: 200px;
                height: 30px;
                color: white;
                background-color: navy;
                font-size: 16px;
                font-weight: 600;
            }
            input[type="submit"]:hover { 
                background-color: red;
            }
            #log_in_link{
                transition-duration: 0.5s;
            }
            #log_in_link:hover{
                color: red;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="container">

            <img id="image_header" src="../assets/img/or2stem.jpg" alt="Fresno State OR2STEM Logo"/>

            <form action="" method="post">
            
                <div class="form-group">
                    <label id="firstName_label" for="firstName">First Name</label>
                    <br>
                    <input type="text" id="firstName" name="firstName" value="<?= $fname; ?>" required>
                </div>

                <div class="form-group">
                    <label id="lastName_label" for="lastName">Last Name</label>
                    <br>
                    <input type="text" id="lastName" name="lastName" value="<?= $lname; ?>" required>
                </div>
                
                <div class="form-group">
                    <label id="email_label" for="email">Email</label>
                    <br>
                    <input type="email" id="email" name="email" value="<?= $email; ?>" required>
                    <!-- Will display error here -->
                    <p class="input-error"><?= $email_err; ?></p>
                </div>
                
                <div class="form-group">
                    <label id="pwd_label" for="pwd">Password</label>
                    <br>
                    <input type="password" id="pwd" name="pwd" value="<?= $password; ?>" required>
                    <!-- Will display error here -->
                    <p class="input-error"><?= $password_err; ?></p>
                </div>

                <div class="form-group">
                    <label id="confirm_pwd_label" for="confirm_pwd">Confirm Password</label>
                    <br>
                    <input type="password" id="confirm_pwd" name="confirm_pwd" value="<?= $confirm_password; ?>" required>
                    <!-- Will display error here -->
                    <p class="input-error"><?= $confirm_password_err; ?></p>
                </div>

                <div class="form-group">
                    <label id="type_label" for="type">Type</label>
                    <br>
                    <select name="type" id="type" onchange="if (this.selectedIndex) doSomething();" required>
                        <option value="">Select one</option>
                        <option value="instructor">instructor</option>
                        <option value="student">student</option>
                    </select>
                </div>

                <div class="form-group" id="studentOption"></div>
                
                <input type="submit" name="submit" value="Register">

            </form>

            <br>

            <p>Already have an account? <a id="log_in_link" href="login.php">Log in!</a></p>

            <br><br><br>
        </div>
        <script type="text/javascript">
            let doSomething = () =>{
                // grab the selected option
                let select = document.getElementById("type");
                let select_option = select.options[select.selectedIndex].text;
                if(select_option === "student"){
                    let str = '<label id="instructor_email_label" for="instructor_email">Instructor Email</label>';
                    str += '<br>';
                    str += '<input type="email" id="instructor_email" name="instructor_email" value="<?= $instructor_email; ?>" required>';
                    document.getElementById("studentOption").innerHTML = str;
                }
                else{
                    let str = '<label id="instructor_secret_code_label" for="instructor_secret_code">Instructor Secret Code</label>';
                    str += '<br>';
                    str += '<input type="text" id="instructor_secret_code" name="instructor_secret_code" value="<?= $instructor_secret_code; ?>" required>';
                    str += '<p class="input-error"><?= $instructor_secret_code_err; ?></p>';
                    document.getElementById("studentOption").innerHTML = str;
                }
            }
        </script>
    </body>
</html>