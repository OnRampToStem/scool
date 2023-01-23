<?php

// for deleting directories and files on the Fresno State server
// first delete the files, then delete the directory
echo "Deleting file<br>";
unlink("../scale/user_data/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/openStax/test_student@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Deleting file<br>";
unlink("../scale/user_data/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/questions/test_student@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Removing directory<br>";
rmdir("../scale/user_data/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/openStax")
    or die("Could not remove dir<br>");

echo "Removing directory<br>";
rmdir("../scale/user_data/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/questions")
    or die("Could not remove dir<br>");

echo "Removing directory<br>";
rmdir("../scale/user_data/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef")
    or die("Could not remove dir<br>");

    /*
    echo "Removing directory<br>";
    rmdir("../scale/user_data/MATH6 Dev-20d4c62c24a96e1f3afb75776a253004109a1e22/openStax")
        or die("Could not remove dir<br>");
    
    echo "Removing directory<br>";
    rmdir("../scale/user_data/MATH6 Dev-20d4c62c24a96e1f3afb75776a253004109a1e22/questions")
        or die("Could not remove dir<br>");

    echo "Deleting file<br>";
    rmdir("../scale/user_data/MATH6 Dev-20d4c62c24a96e1f3afb75776a253004109a1e22")
        or die("Could not remove file<br>");
    */

?>