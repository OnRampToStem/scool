<?php

// create the directories that will keep track of the student progress in 'user_data'
echo "Creating directory in: ../scale/user_data/Temporary Course-123/questions <br>";
$directory_path = "../scale/user_data/Temporary Course-123/questions";
mkdir($directory_path, 0777, true) or die("Failed to create directory.");

echo "Creating directory in: ../scale/user_data/Temporary Course-123/openStax <br>";
$directory_path = "../scale/user_data/Temporary Course-123/openStax";
mkdir($directory_path, 0777, true) or die("Failed to create directory.");


?>