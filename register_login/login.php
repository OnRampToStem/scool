<?php
/*
    This PHP script will first connect to the PostgreSQL database and then check if the accordingly
    HTML form using POST was submitted. If so, it will make sure that the user input email is in
    the database and it will also check that the user input password matches the password stored
    in the database. If login is successful, user will be redirected to login_success.php, otherwise
    they will remain in the login.html page.
*/

// initialize the session
session_start();

// if user is already logged in and is of type 'student', then redirect page
if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true
   && isset($_SESSION["type"]) && $_SESSION["type"] === "student")
{
    header("location: ../student/student_index.php");
    exit;
}

// if user is already logged in and is of type 'instructor', then redirect page
if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true
   && isset($_SESSION["type"]) && $_SESSION["type"] === "instructor")
{
    header("location: ../instructor/instr_index1.php");
    exit;
}

// connect to the DB
require_once "config.php";

// global variables and init with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    /*  EMAIL VALIDATION */
    // check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } 
    else{
        $email = trim($_POST["email"]);
    }

    /* PASSWORD VALIDATION */
    // check if password is empty
    if(empty(trim($_POST["pwd"]))){
        $password_err = "Please enter your password.";
    } 
    else{
        $password = trim($_POST["pwd"]);
    }

    /* VALIDATE CREDENTIALS */
    // if no input errors then continue
    if(empty($email_err) && empty($password_err)){

        // retrieve email and hashed password from the database, where email exists
        $query = "SELECT first_name, email, password, type, course_name, course_id, section_id FROM users WHERE email = '" . pg_escape_string($email) . "'";
        $result = pg_query($con, $query) or die("Cannot execute query: $query \n");

        // email exists in the database, continue to password verification
        if(pg_num_rows($result) >= 1){

            // using pg_fetch_row, we now have access to database table data retrieved
            $row = pg_fetch_row($result);

            // checking user login input password and stored hashed password match (BCRYPT)
            if(password_verify($password, $row[2])){

                // create timestamp to be updated for last_signed_in attribute
                $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
                $timestamp = $date->format('Y-m-d H:i:s');
                
                // query
                $query = "UPDATE users SET last_signed_in = '" . $timestamp . "' WHERE email = '" . $row[1] . "'";
                pg_query($con, $query) or die("Cannot execute query: $query \n");

                // email and password are correct, so start a new session
                session_start();

                // store data in session variables
                $_SESSION["loggedIn"] = true;
                $_SESSION["first_name"] = $row[0];
                $_SESSION["email"] = $row[1];
                $_SESSION["type"] = $row[3]; 
                $_SESSION["course_name"] = $row[4];
                $_SESSION["course_id"] = $row[5];
                $_SESSION["section_id"] = $row[6];               
                
                // Redirect user to welcome page
                if($_SESSION["type"] === "student"){
                    header("location: ../student/student_index.php");
                }
                else if($_SESSION["type"] === "instructor"){
                    header("location: ../instructor/instr_index1.php");
                }
                
            }
            else{
                // password is not valid
                $login_err = "The password you entered is incorrect.";
            }
        }
        else{
            // email does not exist
            $login_err = "The email you entered does not exist.";
        }
    }
}

//echo "Closing connection to PostgreSQL database.\n";
pg_close($con);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login Page</title>
        <meta charset="UTF-8">
        <style>
            .container{
                margin: 0 auto;
                text-align: center;
                border: solid 6px navy;
                width: 35%;
                height: 450px;
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
            #email_label, #pwd_label{
                color: navy;
                font-size: 16px;
                font-weight: 600;
                text-align: left;
            }
            #email, #pwd{
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
                cursor: pointer;
            }
            input[type="submit"]:hover { 
                background-color: red;
            }
            #sign_up_link{
                transition-duration: 0.5s;
            }
            #sign_up_link:hover{
                color: red;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="container">

            <img id="image_header" src="../assets/img/or2stem.jpg" alt="Fresno State OR2STEM Logo"/>

            <!-- display login error here -->
            <?php 
                if(!empty($login_err)){
                    echo '<div class="input-error">' . $login_err . '</div>';
                }        
            ?>

            <form action="" method="post">
            
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
                    <input type="password" id="pwd" name="pwd" required>
                    <!-- Will display error here -->
                    <p class="input-error"><?= $password_err; ?></p>                 
                </div>
                
                <input type="submit" name="submit" value="Login">

            </form>

            <!--<p>Don't have an account? <a id="sign_up_link" href="register.php">Sign up!</a>.</p> -->
        </div>
    </body>
</html>