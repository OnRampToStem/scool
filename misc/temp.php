<?php

// testing for observers

// update users set course_name = '["Development MATH6 Pilot"]', course_id = '["cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef"]' where pkey = 3;

// INSERT INTO users(name, email, unique_name, sub, type, pic, instructor, course_name, course_id, iat, exp, iss, aud, created_on, last_signed_in)
// VALUES('Temp Observer', 't@gmail.com', 't@gmail.com', 't@gmail.com', 'Observer', 'https://canvas.instructure.com/images/messages/avatar-50.png',
//      '', '["Math 3"]', '["123"]', '1674496961', '1674540166', 'https://scale.fresnostate.edu', 'https://scale.fresnostate.edu', '2023-01-23 11:01:42', '2023-01-23 11:01:42');

$name = 'Temp Observer';
$email = 't@gmail.com';
$type = 'Observer';
$pic = 'https://canvas.instructure.com/images/messages/avatar-50.png';
$course_name = ["MATH 6 (03) - Precalculus"];
$course_id = ["2d32d562af7298330c3c5b20d3a2b88506e0fc0b"];

//MATH 6 (03) - Precalculus-2d32d562af7298330c3c5b20d3a2b88506e0fc0b

echo "Starting Session. <br>";
session_start();

echo "Setting required session variables. <br>";
$_SESSION["loggedIn"] = true;
$_SESSION["name"] = $name;
$_SESSION["email"] = $email;
$_SESSION["type"] = $type;
$_SESSION["pic"] = $pic;
$_SESSION["course_name"] = json_encode($course_name);
$_SESSION["course_id"] = json_encode($course_id);

echo "Redirecting to Instructor Home Page. <br>";
header("location: instructor/instr_index1.php");


?>