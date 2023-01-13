<?php
/* GLOBALS */
$token;
$obj;
$query;
$res;

// checking for POST
if (isset($_POST['token'])) {
	$token = $_POST['token'];
	echo "Token from POST <br>";
}
// checking for GET
elseif (isset($_GET['token'])) {
	$token = $_GET['token'];
	echo "Token from GET <br>";
}
// default, if not POST or GET
else {	
	$token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIyYjkzZDllMy0yYmM4LTRkZWQtYTVhMC04MTIwMjcwNGU4ZjdAN2YyMzA4YWI5MDkyNDExYWFmZTdmNDcyNzliNDdkZmEiLCJ1bmlxdWVfbmFtZSI6ImpvaG53YUBjc3VmcmVzbm8uZWR1IiwiZW1haWwiOiJqb2hud2FAY3N1ZnJlc25vLmVkdSIsIm5hbWUiOiJKb2huIE0gV2FnZW5sZWl0bmVyIiwicm9sZXMiOlsiSW5zdHJ1Y3RvciJdLCJjb250ZXh0Ijp7ImlkIjoiY2ZkNzBiNWRhM2NlOTAxODQwMmI2NmMxZDRlY2ZkYzZiOWQ2ZWVlZiIsInRpdGxlIjoiRGV2ZWxvcG1lbnQgTUFUSDYgUGlsb3QifSwicGljdHVyZSI6Imh0dHBzOi8vY2FudmFzLmluc3RydWN0dXJlLmNvbS9pbWFnZXMvbWVzc2FnZXMvYXZhdGFyLTUwLnBuZyIsImlhdCI6MTY3MzQ1OTI1NywiZXhwIjoxNjczNDg4MDYyLCJpc3MiOiJodHRwczovL3NjYWxlLmZyZXNub3N0YXRlLmVkdSIsImF1ZCI6Imh0dHBzOi8vc2NhbGUuZnJlc25vc3RhdGUuZWR1In0.9oeFShsN_br5HSsQhssIu88H647c_S-MWwXEiZUnXSY";
	//echo "Default token<br>";
}

function DisplayObj1($obj) {
	echo "Name: " . $obj->name . "<br>";
	echo "Email: " . $obj->email . "<br>";
	echo "Role: " . $obj->roles[0] . "<br>";
	echo "Course id: " . $obj->context->id . "<br>";
	echo "Course title: " . $obj->context->title . "<br>";
}

function DecodeToken($token) {
	return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
}

$obj = DecodeToken($token);
DisplayObj1($obj);

// connect to the db
require_once "../register_login/config.php";

/* STEP 1: check to see if user is 'Instructor' or 'Student' */
if ($obj->roles[0] === "Instructor") {

	/* STEP 2: check to see if the Instructor exists in the 'users' table */
	$query = "SELECT * FROM users WHERE email = '{$obj->email}'";
	$res = pg_query($con, $query);

	if (!$res) { // error with query
		echo "Error with DB query.";
		exit;
	} 
	else { // no error with query

		if (pg_num_rows($res) === 0) {
			// Instructor is not in 'users' table, so insert them in
			
		}
		else {
			// Instructor is in 'users' table
			// HOWEVER, Instructor may be coming in from another class that has a different section id
			// so we will have to modify the row of the Instructor to include the current section id


			// After the above is complete, we will need to create the class folder directory inside of 
			// the 'user_data' directory on the server (only if it does not exist)
			// root folder: 'user_data'
			// inside root folder (class folder): 'Class Name - Class Section ID'
			// inside class folder (2 main folders): 'openStax' & 'questions'

		}

	}
}
elseif ($obj->roles[0] === "Student") {

	/* STEP 2: check to see if Student exists in the 'users' table */
	$query = "SELECT * FROM users WHERE email = '{$obj->email}'";
	$res = pg_query($con, $query);

	if (!$res) { // error with query
		echo "Error with DB query.";
		exit;
	} 
	else { // no error with query
		
		if (pg_num_rows($res) === 0) {
			// Student is not in 'users' table, so insert them in


			// After Student is inserted into the 'users' table, we need to create their own
			// respective JSON files in the folder according to their class name - class section id
			// (this directory should already be created because it is created when instructor first is created)

			
		}
		else {
			// Student is in 'users' table
			// start session and set all required session variables
			// redirect to 'student/student_index.php'


		}

	}

}
else {
	echo "Dealing with unknown user type.\n";
	exit;
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>On Ramp To STEM</title>
	</head>
	<body>

	<h1>Test the reception of the payload</h1>

	</body>
</html>