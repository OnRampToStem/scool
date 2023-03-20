# OR2STEM

### How it works:

This directory contains the necessary directories and files to allow either a user of type 'Instructor'
or 'Learner' to navigate.

In order to first establish the connection, the user must be coming from Canvas, which is then transferred
to the 'payload.php' page. The 'payload.php' page is what determines whether the user is of what type and
where that user will be redirected. (See ./misc/payload.php)
(Keep in mind, this 'payload.php' is in a different directory from what you will see here)