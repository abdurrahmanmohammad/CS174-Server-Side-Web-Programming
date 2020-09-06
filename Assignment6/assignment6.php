<?php
// Advisor 30 chars
// Student 30 chars
// StudentID 9 chars/digits
// ClassCode 5 chars/digits
// Errors are not printed out
define(TABLE, "AdvisingTable"); // Define the name of the table as a constant
require_once 'login.php'; // Load the user login
require_once 'databaseMethods.php'; // Load methods in other file
buildDB(new mysqli($hn, $un, $pw), $db); // Connect to MySQL and build the database if doesn't exist
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection
buildTable($conn, TABLE); // Build the table if not have already done so
if ($conn->connect_error) // Check connection
    die(mysql_fatal_error($conn->connect_error));
echo <<<_END
<form action="assignment6.php" method="post" enctype='multipart/form-data'><pre>
Advisor <input type="text" name="Advisor">
Student <input type="text" name="Student">
StudentID <input type="text" name="StudentID">
Class code <input type="text" name="ClassCode">
<input type="submit" value="ADD RECORD">
</pre></form>
_END;

echo <<<_END
<form action="assignment6.php" method="post" enctype='multipart/form-data'><pre>
Search <input type="text" name="SearchData">
<input type="submit" value="Search">
</pre></form>
_END;
if (isset($_POST['SearchData'])) // Search and print the record with given ID
    search($conn, TABLE);

if (isset($_POST['Advisor']) && isset($_POST['Student']) && isset($_POST['StudentID']) && isset($_POST['ClassCode']))
    insert($conn, TABLE);

$conn->close();


// HTML Entities


